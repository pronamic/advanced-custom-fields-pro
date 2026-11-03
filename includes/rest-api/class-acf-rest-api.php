<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is already defined, return.
if ( class_exists( 'ACF_Rest_Api' ) ) {
	return;
}

class ACF_Rest_Api {

	/** @var ACF_Rest_Request */
	private $request;

	/**
	 * The WP_REST_Request object.
	 *
	 * @var WP_REST_Request|null
	 */
	private $wp_request;

	/**
	 * Per-object schema set by maybe_apply_per_object_schema().
	 *
	 * @var array|null
	 */
	private $per_object_schema;

	/** @var ACF_Rest_Embed_Links */
	private $embed_links;

	/**
	 * Cached controllers WP core does not lazy-load.
	 *
	 * @var array<string, WP_REST_Controller>
	 */
	private static $core_controllers = array();

	public function __construct() {
		// Priority 9 runs before rest_handle_options_request (priority 10).
		add_filter( 'rest_pre_dispatch', array( $this, 'initialize' ), 9, 3 );
		add_action( 'rest_api_init', array( $this, 'register_field' ) );
		add_filter( 'rest_endpoints', array( $this, 'enrich_options_endpoint_args' ) );
	}

	public function initialize( $response, $handler, $request ) {
		if ( ! acf_get_setting( 'rest_api_enabled' ) ) {
			return $response;
		}

		// Clear per-request state so a prior dispatch's per-object properties can't leak into enrich_options_endpoint_args().
		$this->per_object_schema = null;
		$this->wp_request        = $request;

		// Parse request and set the object for local access.
		$this->request = new ACF_Rest_Request();
		$this->request->parse_request( $request );

		$this->register_field();
		$this->maybe_apply_per_object_schema();

		// If embed links are enabled in ACF's global settings, init the handler and set for local access.
		if ( acf_get_setting( 'rest_api_embed_links' ) ) {
			$this->embed_links = new ACF_Rest_Embed_Links();
			$this->embed_links->initialize();
		}

		return $response;
	}

	/**
	 * Upgrade both OPTIONS surfaces (schema.properties.acf and
	 * endpoints[].args.acf) from the type-level union to per-object precision
	 * when the caller can read the target object.
	 *
	 * @since 6.8.10
	 *
	 * @return void
	 */
	private function maybe_apply_per_object_schema() {
		// Both outputs are only read during OPTIONS response building.
		if ( ! ( $this->wp_request instanceof WP_REST_Request ) || 'OPTIONS' !== $this->wp_request->get_method() ) {
			return;
		}

		if ( ! $this->request instanceof ACF_Rest_Request || ! $this->request->object_type ) {
			return;
		}

		$child_id  = $this->request->get_url_param( 'child_id' );
		$object_id = $child_id ? $child_id : $this->request->get_url_param( 'id' );

		// Collection routes are already correct at the type-level union.
		if ( ! $object_id ) {
			return;
		}

		if ( ! $this->current_user_can_read_request_object( $object_id, $child_id ) ) {
			return;
		}

		$per_object = $this->build_per_object_schema( $object_id );

		if ( empty( $per_object['properties'] ) ) {
			return;
		}

		$this->per_object_schema = $per_object;

		$base = $this->request->child_object_type
			? $this->request->child_object_type
			: $this->request->object_sub_type;
		if ( ! $base ) {
			return;
		}

		global $wp_rest_additional_fields;
		if ( isset( $wp_rest_additional_fields[ $base ]['acf'] ) ) {
			$wp_rest_additional_fields[ $base ]['acf']['schema'] = $per_object;
		}
	}

	/**
	 * Build the per-object ACF schema for the current request's target object.
	 *
	 * @since 6.8.10
	 *
	 * @param integer|string $object_id The target object ID (may be a child id for revisions/autosaves).
	 * @return array
	 */
	private function build_per_object_schema( $object_id ) {
		$field_groups = $this->get_field_groups_by_id(
			$object_id,
			$this->request->object_type,
			$this->request->object_sub_type
		);

		return $this->build_schema_from_field_groups( $field_groups, $object_id );
	}

	/**
	 * Build the ACF schema scaffold and populate its `properties` from the
	 * given field groups. Shared between the type-level get_schema() and the
	 * per-object build_per_object_schema() so both surfaces stay in lockstep.
	 *
	 * @since 6.8.10
	 *
	 * @param array               $field_groups Field groups to walk.
	 * @param integer|string|null $object_id    Object ID passed to get_fields() so field-level filters see the target.
	 * @return array
	 */
	private function build_schema_from_field_groups( array $field_groups, $object_id = null ) {
		$schema = array(
			'description' => 'ACF field data',
			'type'        => 'object',
			'properties'  => array(),
			'arg_options' => array(
				'validate_callback' => array( $this, 'validate_rest_arg' ),
			),
		);

		foreach ( $field_groups as $field_group ) {
			foreach ( $this->get_fields( $field_group, $object_id ) as $field ) {
				$schema['properties'][ $field['name'] ] = acf_get_field_rest_schema( $field );
			}
		}

		return $schema;
	}

	/**
	 * Filter callback for `rest_endpoints`. Swaps the per-object properties
	 * into the `acf` entry of each matching endpoint's args. Only runs for
	 * OPTIONS requests on routes matching the current URL, so write
	 * validation is left untouched.
	 *
	 * @since 6.8.10
	 *
	 * @param array $endpoints Endpoints as passed by WP_REST_Server::get_routes().
	 * @return array
	 */
	public function enrich_options_endpoint_args( $endpoints ) {
		if (
			empty( $this->per_object_schema )
			|| ! ( $this->wp_request instanceof WP_REST_Request )
			|| 'OPTIONS' !== $this->wp_request->get_method()
		) {
			return $endpoints;
		}

		$current_route = $this->wp_request->get_route();
		if ( empty( $current_route ) ) {
			return $endpoints;
		}

		$per_object_properties = $this->per_object_schema['properties'];

		foreach ( $endpoints as $route_pattern => &$handlers ) {
			if ( ! preg_match( '@^' . $route_pattern . '$@i', $current_route ) ) {
				continue;
			}

			foreach ( $handlers as &$handler ) {
				if ( ! is_array( $handler ) || ! isset( $handler['args']['acf'] ) || ! is_array( $handler['args']['acf'] ) ) {
					continue;
				}
				// Preserve validate_callback, sanitize_callback, etc — only swap properties.
				$handler['args']['acf']['properties'] = $per_object_properties;
			}
			unset( $handler );
		}
		unset( $handlers );

		return $endpoints;
	}

	/**
	 * Whether the current caller can read the given object. Fails closed
	 * (returns false) when permission cannot be conclusively verified.
	 * Callers guarantee that $this->wp_request is a WP_REST_Request.
	 *
	 * @since 6.8.10
	 *
	 * @param integer|string $object_id The effective target ID (child ID if present, otherwise the URL id).
	 * @param integer|string $child_id  The URL child_id, if any — signals a revision/autosave context.
	 * @return boolean
	 */
	private function current_user_can_read_request_object( $object_id, $child_id ) {
		// Revisions and autosaves inherit edit_post on the parent.
		if ( $child_id ) {
			$parent_id = (int) $this->request->get_url_param( 'id' );
			return $parent_id > 0 && acf_current_user_can_edit_post( $parent_id );
		}

		$controller = $this->resolve_rest_controller();
		if ( ! $controller instanceof WP_REST_Controller ) {
			return false;
		}

		// untrailingslashit so trailing-slash URLs still match core's route registrations.
		$route = untrailingslashit( $this->wp_request->get_route() );
		if ( '' === $route ) {
			return false;
		}

		$check = new WP_REST_Request( 'GET', $route );
		$check->set_url_params( array( 'id' => $object_id ) );
		$check->set_query_params( array( 'context' => 'view' ) );

		// Strict === so WP_Error is treated as unauthorized.
		return true === $controller->get_item_permissions_check( $check );
	}

	/**
	 * Resolve the WP REST controller for the current request's object type.
	 *
	 * @since 6.8.10
	 *
	 * @return WP_REST_Controller|null
	 */
	private function resolve_rest_controller() {
		switch ( $this->request->object_type ) {
			case 'post':
				$type = get_post_type_object( $this->request->object_sub_type );
				break;
			case 'term':
				$type = get_taxonomy( $this->request->object_sub_type );
				break;
			case 'user':
			case 'comment':
				return self::get_core_controller( $this->request->object_type );
			default:
				return null;
		}

		if ( ! $type || ! $type->show_in_rest ) {
			return null;
		}

		$controller = $type->get_rest_controller();

		return $controller instanceof WP_REST_Controller ? $controller : null;
	}

	/**
	 * Cache and return a WP core REST controller for 'user' or 'comment'.
	 *
	 * @since 6.8.10
	 *
	 * @param string $object_type One of 'user' or 'comment'.
	 * @return WP_REST_Controller|null
	 */
	private static function get_core_controller( $object_type ) {
		if ( isset( self::$core_controllers[ $object_type ] ) ) {
			return self::$core_controllers[ $object_type ];
		}

		switch ( $object_type ) {
			case 'user':
				self::$core_controllers[ $object_type ] = new WP_REST_Users_Controller();
				break;
			case 'comment':
				self::$core_controllers[ $object_type ] = new WP_REST_Comments_Controller();
				break;
			default:
				return null;
		}

		return self::$core_controllers[ $object_type ];
	}

	/**
	 * Register our custom property as a REST field.
	 */
	public function register_field() {
		if ( ! acf_get_setting( 'rest_api_enabled' ) ) {
			return;
		}

		if ( ! $this->request instanceof ACF_Rest_Request ) {
			$this->request = new ACF_Rest_Request();
			$this->request->parse_request( null );
		}

		$base = $this->request->object_sub_type;

		// If the object sub type ($post_type, $taxonomy, 'user') cannot be determined from the current request,
		// we don't know what endpoint to register the field against. Bail if that is the case.
		if ( ! $base ) {
			return;
		}

		if ( $this->request->child_object_type ) {
			$base = $this->request->child_object_type;
		}

		// If we've already registered this route, no need to do it again.
		if ( acf_did( 'acf/register_rest_field' ) ) {
			global $wp_rest_additional_fields;

			if ( isset( $wp_rest_additional_fields[ $base ], $wp_rest_additional_fields[ $base ]['acf'] ) ) {
				return;
			}
		}

		register_rest_field(
			$base,
			'acf',
			array(
				'schema'          => $this->get_schema(),
				'get_callback'    => array( $this, 'load_fields' ),
				'update_callback' => array( $this, 'update_fields' ),
			)
		);
	}

	/**
	 * Generate the type-level schema for the current request's object type.
	 * Gets baked into $wp_rest_additional_fields and endpoint args at
	 * rest_api_init. Per-object precision is layered on top in
	 * maybe_apply_per_object_schema() and enrich_options_endpoint_args().
	 *
	 * @return array
	 */
	private function get_schema() {
		$object_type = $this->request->object_type;

		$field_groups = $object_type
			? $this->get_field_groups_by_object_type( $object_type )
			: array();

		return $this->build_schema_from_field_groups( $field_groups );
	}

	/**
	 * Validate the request args. Mostly a wrapper for `rest_validate_request_arg()`, but also
	 * fires off a filter, so we can add some custom validation for specific fields.
	 *
	 * This will likely no longer be needed once WordPress implements something like `validate_callback`
	 * and `sanitize_callback` for nested schema properties, see:
	 * https://core.trac.wordpress.org/ticket/49960
	 *
	 * @param mixed            $value
	 * @param \WP_REST_Request $request
	 * @param string           $param
	 *
	 * @return boolean|WP_Error
	 */
	public function validate_rest_arg( $value, $request, $param ) {
		// Validate all fields with default WordPress validation first.
		$valid = rest_validate_request_arg( $value, $request, $param );

		if ( true !== $valid ) {
			return $valid;
		}

		foreach ( $value as $field_name => $field_value ) {
			$field = acf_get_field( $field_name );

			if ( ! $field ) {
				continue;
			}

			/**
			 * Filters whether a value passed via REST is valid.
			 *
			 * @since   5.11
			 *
			 * @param bool  $valid True if the value is valid, false or WP_Error if not.
			 * @param mixed $value The value to check.
			 * @param array $field An array of information about the field.
			 */
			$valid = apply_filters( 'acf/validate_rest_value/type=' . $field['type'], true, $field_value, $field );

			if ( true !== $valid ) {
				return $valid;
			}
		}

		return true;
	}

	/**
	 * Checks whether a post's field values must stay out of the response because the
	 * post is password protected and the caller has neither unlocked it nor can edit it.
	 *
	 * @since 6.8.11
	 *
	 * @param integer         $post_id The post ID.
	 * @param WP_REST_Request $request The current request.
	 * @return boolean
	 */
	private function is_password_protected_content_hidden( $post_id, $request ) {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || ! post_password_required( $post ) || current_user_can( 'edit_post', $post->ID ) ) {
			return false;
		}

		$password = $request instanceof WP_REST_Request ? $request->get_param( 'password' ) : '';

		return ! is_string( $password ) || '' === $password || ! hash_equals( $post->post_password, $password );
	}

	/**
	 * Load field values into the requested object. This method is not a part of any public API and is only public as
	 * it is required by WordPress.
	 *
	 * @param array           $object          An array representation of the post, term, or user object.
	 * @param string          $field_name
	 * @param WP_REST_Request $request
	 * @param string          $object_sub_type Note that this isn't the same as $this->object_type. This variable is
	 *                                          more specific and can be a post type or taxonomy.
	 * @return array
	 */
	public function load_fields( $object, $field_name, $request, $object_sub_type ) {
		// The fields loaded for display on the REST API in the form of {$field_name}=>{$field_value} pairs.
		$fields = array();

		// Determine the object ID from the given object.
		$object_id = acf_get_object_id( $object );

		// Use this object type parsed from the request.
		$object_type = $this->request->object_type;

		// Object ID and type are essential to determining which fields to load. Return if we don't have both.
		if ( ! $object_id or ! $object_type ) {
			return $fields;
		}

		$object_sub_type = str_replace( '-revision', '', $object_sub_type );

		// Don't expose field values of a password-protected post until the password is provided.
		if ( 'post' === $object_type && $this->is_password_protected_content_hidden( $object_id, $request ) ) {
			return $fields;
		}

		// Get all field groups for the current object.
		$field_groups = $this->get_field_groups_by_id( $object_id, $object_type, $object_sub_type );
		if ( empty( $field_groups ) ) {
			return $fields;
		}

		// Determine the ACF ID string for the current object.
		$post_id = $this->make_identifier( $object_id, $object_type );

		// Loop through the fields within all applicable field groups and add the fields to the response.
		foreach ( $field_groups as $field_group ) {
			foreach ( $this->get_fields( $field_group, $object_id ) as $field ) {
				$value = acf_get_value( $post_id, $field );

				if ( $this->embed_links ) {
					$this->embed_links->prepare_links( $post_id, $field );
				}

				// Format the field value according to the request params.
				$format = $request->get_param( 'acf_format' ) ?: acf_get_setting( 'rest_api_format' );
				$value  = acf_format_value_for_rest( $value, $post_id, $field, $format );

				$fields[ $field['name'] ] = $value;
			}
		}

		/**
		 * Reset the store so that REST API values (which may be preloaded
		 * by WP core and have different values than standard values) aren't
		 * saved to the store.
		 */
		acf_get_store( 'values' )->reset();

		return $fields;
	}

	/**
	 * Update any incoming field values for the given object. This method is not a part of any public API and is only
	 * public as it is required by WordPress.
	 *
	 * @param array                   $data
	 * @param WP_Post|WP_Term|WP_User $object
	 * @param string                  $property        'acf'
	 * @param WP_REST_Request         $request
	 * @param string                  $object_sub_type This will be the post type, the taxonomy, or 'user'.
	 * @return boolean|WP_Error
	 */
	public function update_fields( $data, $object, $property, $request, $object_sub_type ) {
		// If 'acf' data object is empty, don't do anything.
		if ( empty( $data ) ) {
			return true;
		}

		// Determine the object context (type & ID). If the context can't be determined from the current request, throw an
		// error as the fields are not updateable. This handles in line with WordPress' \WP_REST_Request::sanitize_params().
		$object_id   = acf_get_object_id( $object );
		$object_type = $this->request->object_type;
		if ( ! $object_id or ! $object_type ) {
			return new WP_Error(
				'acf_rest_object_unknown',
				__( sprintf( 'Unable to determine the %s object ID or type. The %s property cannot be updated.', get_class( $object ), $property ), 'acf' ),
				array( 'status' => 400 )
			);
		}

		// Determine the ACF selector for the current object.
		$post_id = $this->make_identifier( $object_id, $object_type );

		// Allow unrestricted update of fields by field key when saving via the WordPress admin. Admin mode will
		// update fields using their field keys to lookup the field. The field lookup is not scoped to field groups
		// located on the given object so any field can be updated. Given the field keys are not defined in the
		// schema, core validation/sanitisation are also bypassed.
		// if ( $this->is_admin_mode( $data ) ) {
		// Loop through payload and save fields using field keys.
		// foreach ( $data as $field_key => $value ) {
		// if ( $field = acf_get_field( $field_key ) ) {
		// acf_update_value( $value, $post_id, $field );
		// }
		// }
		//
		// return true;
		// }
		// todo - consider/discuss handling this in the request object instead
		// If the incoming data defines field group keys, extract it from the data. This is used to scope the
		// field lookup in \ACF_Rest_Api::get_field_groups_by_id();
		$field_group_scope = acf_extract_var( $data, '_acf_field_group_scope', array() );

		// Get all field groups for the current object.
		$field_groups = $this->get_field_groups_by_id( $object_id, $object_type, $object_sub_type, $field_group_scope );
		if ( empty( $field_groups ) ) {
			return true;
		}

		// Collect all fields from matching field groups.
		$all_fields = array();
		foreach ( $field_groups as $field_group ) {
			if ( $fields = $this->get_fields( $field_group, $object_id ) ) {
				$all_fields = array_merge( $fields, $all_fields );
			}
		}

		if ( $all_fields ) {
			// todo - consider/discuss handling this in the request object instead.
			// If the incoming request has a map of field names to keys, extract it for use in the subsequent
			// field search.
			$field_key_map = acf_extract_var( $data, '_acf_field_key_map', array() );

			// Sanitize field data for users without unfiltered_html capability.
			if ( ! acf_allow_unfiltered_html() ) {
				$data = wp_kses_post_deep( $data );
			}

			_acf_save_submitted_values(
				function () use ( $data, $field_key_map, $all_fields, $post_id ) {
					// Loop through the inbound data payload, find the field matching the incoming field name, and
					// update the field.
					foreach ( $data as $field_name => $value ) {

						// If the field name has a key explicitly mapped to it, use the field key to find the field.
						if ( isset( $field_key_map[ $field_name ] ) ) {
							$field_name = $field_key_map[ $field_name ];
						}

						if ( $field = acf_search_fields( $field_name, $all_fields ) ) {
							acf_update_value( $value, $post_id, $field );
						}
					}
				}
			);
		}

		return true;
	}

	// todo - this should check for a flag and validate a nonce to ensure we are in admin mode.
	// todo - consider/discuss handling this in the request object instead.
	private function is_admin_mode( $data ) {
		return isset( $data['_acf_admin_mode'] ) && $data['_acf_admin_mode'];
	}

	/**
	 * Make the ACF identifier string for the given object.
	 *
	 * @param integer $object_id
	 * @param string  $object_type 'user', 'term', or 'post'
	 * @return string
	 */
	private function make_identifier( $object_id, $object_type ) {
		$formats = array(
			'user'    => 'user_%s',
			'term'    => 'term_%s',
			'comment' => 'comment_%s',
		);

		return isset( $formats[ $object_type ] )
			? sprintf( $formats[ $object_type ], $object_id )
			: $object_id;
	}

	/**
	 * Gets an array of the location types that a field group is configured to use.
	 *
	 * @param string $object_type    'user', 'term', or 'post'
	 * @param array  $field_group    The field group to check.
	 * @param array  $location_types An array of location types.
	 *
	 * @return boolean
	 */
	private function object_type_has_field_group( $object_type, $field_group, $location_types = array() ) {
		if ( ! isset( $field_group['location'] ) || ! is_array( $field_group['location'] ) ) {
			return false;
		}

		$location_types = empty( $location_types ) ? acf_get_location_types() : $location_types;

		foreach ( $field_group['location'] as $rule_group ) {
			$match = false;
			foreach ( $rule_group as $rule ) {
				$rule = acf_validate_location_rule( $rule );

				if ( ! isset( $location_types[ $rule['param'] ] ) ) {
					continue;
				}

				// Make sure the main object type matches.
				$location_type = $location_types[ $rule['param'] ];
				if ( ! isset( $location_type->object_type ) || $location_type->object_type !== (string) $object_type ) {
					continue;
				}

				/**
				 * For posts/pages, we can only be sure that fields will show up if
				 * the field group is configured to show up for all items of the current
				 * post type.
				 */
				if ( 'post' === $object_type && 'post_type' === $rule['param'] ) {
					if ( $rule['operator'] === '==' && $this->request->object_sub_type !== $rule['value'] ) {
						continue;
					}
					if ( $rule['operator'] === '!=' && $this->request->object_sub_type === $rule['value'] ) {
						continue;
					}

					$match = true;
				}

				if ( 'term' === $object_type && 'taxonomy' === $rule['param'] ) {
					if ( $rule['operator'] === '==' && $this->request->object_sub_type !== $rule['value'] ) {
						continue;
					}
					if ( $rule['operator'] === '!=' && $this->request->object_sub_type === $rule['value'] ) {
						continue;
					}

					$match = true;
				}

				if ( in_array( $object_type, array( 'user', 'comment' ) ) ) {
					$match = true;
				}
			}

			if ( $match ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all field groups for the provided object type.
	 *
	 * @param string $object_type 'user', 'term', or 'post'
	 *
	 * @return array An array of field groups that display for that location type.
	 */
	private function get_field_groups_by_object_type( $object_type ) {
		$field_groups       = acf_get_field_groups();
		$location_types     = acf_get_location_types();
		$object_type_groups = array();

		foreach ( $field_groups as $field_group ) {
			if ( empty( $field_group['show_in_rest'] ) ) {
				continue;
			}

			if ( $this->object_type_has_field_group( $object_type, $field_group, $location_types ) ) {
				$object_type_groups[] = $field_group;
			}
		}

		return $object_type_groups;
	}

	/**
	 * Get all field groups for a given object.
	 *
	 * @param integer     $object_id
	 * @param string      $object_type     'user', 'term', or 'post'
	 * @param string|null $object_sub_type The post type or taxonomy. When an $object_type of 'user' is in play, this can be ignored.
	 * @param array       $scope           Field group keys to limit the returned set of field groups to. This is used to scope field lookups to specific groups.
	 * @return array An array of matching field groups.
	 */
	private function get_field_groups_by_id( $object_id, $object_type, $object_sub_type = null, $scope = array() ) {
		// When dealing with a term, we need the taxonomy in order to look up the relevant field groups. The taxonomy is expected
		// in the $object_sub_type variable but when building our schema, this isn't readily available. This block ensures the
		// taxonomy is set when not passed in.
		if ( $object_type === 'term' && $object_sub_type === null ) {
			$term = get_term( $object_id );
			if ( ! $term instanceof WP_Term ) {
				return array();
			}
			$object_sub_type = $term->taxonomy;
		}

		switch ( $object_type ) {
			case 'user':
				$args = array(
					'user_id' => $object_id,
					'rest'    => true,
				);
				break;
			case 'term':
				$args = array( 'taxonomy' => $object_sub_type );
				break;
			case 'comment':
				$comment   = get_comment( $object_id );
				$post_type = get_post_type( $comment->comment_post_ID );
				$args      = array( 'comment' => $post_type );
				break;
			case 'post':
			default:
				$args            = array( 'post_id' => $object_id );
				$child_rest_base = $this->request->get_url_param( 'child_rest_base' );
				if ( $child_rest_base && 'post' === $object_type ) {
					$args['post_type'] = $object_sub_type;
				}
		}

		// Only return field groups that are configured to show in REST.
		return array_filter(
			acf_get_field_groups( $args ),
			function ( $group ) use ( $scope ) {
				if ( $scope and ! in_array( $group['key'], $scope ) ) {
					return false;
				}

				return $group['show_in_rest'];
			}
		);
	}

	/**
	 * Get all ACF fields for a given field group and allow third party filtering.
	 *
	 * @param array        $field_group This could technically be other possible values supported by acf_get_fields() but in this
	 *                              context, we're only using the field group arrays.
	 * @param null|integer $object_id   The ID of the object being prepared.
	 * @return array
	 */
	private function get_fields( $field_group, $object_id = null ) {
		// Get all fields for this field group that are rest enabled.
		$fields = array_filter(
			acf_get_fields( $field_group ),
			function ( $field ) {
				$field_type = acf_get_field_type( $field['type'] );
				return isset( $field_type->show_in_rest ) && $field_type->show_in_rest;
			}
		);

		// Set up context array for use in the filter below.
		$resource = array(
			'type'     => $this->request->object_type,
			'sub_type' => $this->request->object_sub_type,
			'id'       => $object_id,
		);

		$http_method = $this->request->http_method;

		/**
		 * Filter the fields available to the REST API.
		 *
		 * @param array  $fields The ACF fields for this field group.
		 * @param array  $resource Contextual information about the current resource request.
		 * @param string $http_method The HTTP method of the current request (GET, POST, PUT, PATCH, DELETE, OPTION, HEAD).
		 */
		return (array) apply_filters( 'acf/rest/get_fields', $fields, $resource, $http_method );
	}
}
