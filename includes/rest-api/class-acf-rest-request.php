<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is already defined, return.
if ( class_exists( 'ACF_Rest_Request' ) ) {
	return;
}

/**
 * Class ACF_Rest_Request
 *
 * @property-read string $object_sub_type
 * @property-read string $object_type
 * @property-read string $http_method
 */
class ACF_Rest_Request {

	/**
	 * Define which private/protected class properties are allowed read access. Access to these is controlled in
	 * \ACF_Rest_Request::__get();
	 *
	 * @var string[]
	 */
	private $readonly_props = array( 'object_type', 'object_sub_type', 'child_object_type', 'http_method' );

	/** @var string The HTTP request method for the current request. i.e; GET, POST, PATCH, PUT, DELETE, OPTIONS, HEAD */
	private $http_method;

	/** @var string The current route being requested. */
	private $current_route;

	/** @var array Route URL patterns we support. */
	private $supported_routes = array();

	/** @var array Parameters matched from the URL. e.g; object IDs. */
	private $url_params = array();

	/** @var string The underlying object type. e.g; post, term, user, etc. */
	private $object_type;

	/** @var string The requested object type. */
	private $object_sub_type;

	/** @var string The object type for a child object. e.g. post-revision, autosaves, etc. */
	private $child_object_type;

	/**
	 * Determine all required information from the current request.
	 */
	public function parse_request( $request ) {
		$this->set_http_method();
		$this->set_current_route( $request );
		$this->build_supported_routes();
		$this->set_url_params();
		$this->set_object_types();
	}

	/**
	 * Magic getter for accessing read-only properties. Should we ever need to enforce a getter method, we can do so here.
	 *
	 * @param string $name The desired property name.
	 * @return string|null
	 */
	public function __get( $name ) {
		if ( in_array( $name, $this->readonly_props ) ) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * Get a URL parameter if found on the request URL.
	 *
	 * @param $param
	 * @return mixed|null
	 */
	public function get_url_param( $param ) {
		return isset( $this->url_params[ $param ] ) ? $this->url_params[ $param ] : null;
	}

	/**
	 * Determine the HTTP method of the current request.
	 */
	private function set_http_method() {
		$this->http_method = strtoupper( $_SERVER['REQUEST_METHOD'] );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
		// HTTP method override for clients that can't use PUT/PATCH/DELETE. This is identical to WordPress'
		// handling in \WP_REST_Server::serve_request(). This block of code should always be identical to that
		// in core.
		if ( isset( $_GET['_method'] ) ) {
			$this->http_method = strtoupper( $_GET['_method'] );
		} elseif ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ) {
			$this->http_method = strtoupper( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get the current REST route as determined by WordPress.
	 */
	private function set_current_route( $request ) {
		if ( $request ) {
			$this->current_route = $request->get_route();
		} else {
			$this->current_route = empty( $GLOBALS['wp']->query_vars['rest_route'] ) ? null : $GLOBALS['wp']->query_vars['rest_route'];
		}
	}

	/**
	 * Build an array of route match patterns that we handle. These are the same as WordPress' core patterns except
	 * we are also matching the object type here as well.
	 */
	private function build_supported_routes() {
		// Add post type routes for all post types configured to show in REST.
		/** @var WP_Post_Type $post_type */
		foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
			$rest_base                = acf_get_object_type_rest_base( $post_type );
			$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})";
			$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)";

			if ( post_type_supports( $post_type->name, 'revisions' ) ) {
				$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)/(?P<child_rest_base>revisions)";
				$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)/(?P<child_rest_base>revisions)/(?P<child_id>[\d]+)";
			}

			if ( 'attachment' !== $post_type->name ) {
				$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)/(?P<child_rest_base>autosaves)";
				$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)/(?P<child_rest_base>autosaves)/(?P<child_id>[\d]+)";
			}
		}

		// Add taxonomy routes all taxonomies configured to show in REST.
		/** @var WP_Taxonomy $taxonomy */
		foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'object' ) as $taxonomy ) {
			$rest_base                = acf_get_object_type_rest_base( $taxonomy );
			$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})";
			$this->supported_routes[] = "/wp/v2/(?P<rest_base>{$rest_base})/(?P<id>[\d]+)";
		}

		// Add user routes.
		$this->supported_routes[] = '/wp/v2/(?P<rest_base>users)';
		$this->supported_routes[] = '/wp/v2/(?P<rest_base>users)/(?P<id>[\d]+)';
		$this->supported_routes[] = '/wp/v2/(?P<rest_base>users)/me';

		// Add comment routes.
		$this->supported_routes[] = '/wp/v2/(?P<rest_base>comments)';
		$this->supported_routes[] = '/wp/v2/(?P<rest_base>comments)/(?P<id>[\d]+)';
	}

	/**
	 * Loop through supported routes to find matching pattern. Use matching pattern to determine any URL parameters.
	 */
	private function set_url_params() {
		if ( ! $this->supported_routes || ! is_string( $this->current_route ) ) {
			return;
		}

		// Determine query args passed within the URL.
		foreach ( $this->supported_routes as $route ) {
			$match = preg_match( '@^' . $route . '$@i', $this->current_route, $matches );
			if ( ! $match ) {
				continue;
			}

			foreach ( $matches as $param => $value ) {
				if ( ! is_int( $param ) ) {
					$this->url_params[ $param ] = $value;
				}
			}
		}
	}

	/**
	 * Determine the object type and sub type from the requested route. We need to know both the underlying WordPress
	 * object type as well as post type or taxonomy in order to provide the right context when getting/updating fields.
	 */
	private function set_object_types() {
		$base       = $this->get_url_param( 'rest_base' );
		$child_base = $this->get_url_param( 'child_rest_base' );

		// We need a matched rest base to proceed here. If we haven't matched one while parsing the request, bail.
		if ( is_null( $base ) ) {
			return;
		}

		// Determine the matching object type from the rest base. Start with users as that is simple. From there,
		// check post types then check taxonomies if a matching post type cannot be found.
		if ( $base === 'users' ) {
			$this->object_type = $this->object_sub_type = 'user';
		} elseif ( $base === 'comments' ) {
			$this->object_type = $this->object_sub_type = 'comment';
		} elseif ( $post_type = $this->get_post_type_by_rest_base( $base ) ) {
			$this->object_type     = 'post';
			$this->object_sub_type = $post_type->name;

			// Autosaves and revisions are mostly handled the same by WP, and share the same schema.
			if ( in_array( $this->get_url_param( 'child_rest_base' ), array( 'revisions', 'autosaves' ) ) ) {
				$this->child_object_type = $this->object_sub_type . '-revision';
			}
		} elseif ( $taxonomy = $this->get_taxonomy_by_rest_base( $base ) ) {
			$this->object_type     = 'term';
			$this->object_sub_type = $taxonomy->name;
		}
	}

	/**
	 * Find the REST enabled post type object that matches the given REST base.
	 *
	 * @param string $rest_base
	 * @return WP_Post_Type|null
	 */
	private function get_post_type_by_rest_base( $rest_base ) {
		$types = get_post_types( array( 'show_in_rest' => true ), 'objects' );

		foreach ( $types as $type ) {
			if ( acf_get_object_type_rest_base( $type ) === $rest_base ) {
				return $type;
			}
		}

		return null;
	}

	/**
	 * Find the REST enabled taxonomy object that matches the given REST base.
	 *
	 * @param $rest_base
	 * @return WP_Taxonomy|null
	 */
	private function get_taxonomy_by_rest_base( $rest_base ) {
		$taxonomies = get_taxonomies( array( 'show_in_rest' => true ), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( acf_get_object_type_rest_base( $taxonomy ) === $rest_base ) {
				return $taxonomy;
			}
		}

		return null;
	}

}
