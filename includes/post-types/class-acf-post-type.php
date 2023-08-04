<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Post_Type' ) ) {
	class ACF_Post_Type extends ACF_Internal_Post_Type {

		/**
		 * The ACF internal post type name.
		 *
		 * @var string
		 */
		public $post_type = 'acf-post-type';

		/**
		 * The prefix for the key used in the main post array.
		 *
		 * @var string
		 */
		public $post_key_prefix = 'post_type_';

		/**
		 * The cache key for a singular post.
		 *
		 * @var string
		 */
		public $cache_key = 'acf_get_post_type_post:key:';

		/**
		 * The cache key for a collection of posts.
		 *
		 * @var string
		 */
		public $cache_key_plural = 'acf_get_post_type_posts';

		/**
		 * The hook name for a singular post.
		 *
		 * @var string
		 */
		public $hook_name = 'post_type';

		/**
		 * The hook name for a collection of posts.
		 *
		 * @var string
		 */
		public $hook_name_plural = 'post_types';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = 'post-types';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			$this->register_post_type();

			// Include admin classes in admin.
			if ( is_admin() ) {
				acf_include( 'includes/admin/admin-internal-post-type-list.php' );
				acf_include( 'includes/admin/admin-internal-post-type.php' );
				acf_include( 'includes/admin/post-types/admin-post-type.php' );
				acf_include( 'includes/admin/post-types/admin-post-types.php' );
			}

			parent::__construct();

			add_action( 'acf/init', array( $this, 'register_post_types' ), 6 );
		}

		/**
		 * Registers the acf-post-type custom post type with WordPress.
		 *
		 * @since 6.1
		 */
		public function register_post_type() {
			$cap = acf_get_setting( 'capability' );

			register_post_type(
				'acf-post-type',
				array(
					'labels'          => array(
						'name'               => __( 'Post Types', 'acf' ),
						'singular_name'      => __( 'Post Type', 'acf' ),
						'add_new'            => __( 'Add New', 'acf' ),
						'add_new_item'       => __( 'Add New Post Type', 'acf' ),
						'edit_item'          => __( 'Edit Post Type', 'acf' ),
						'new_item'           => __( 'New Post Type', 'acf' ),
						'view_item'          => __( 'View Post Type', 'acf' ),
						'search_items'       => __( 'Search Post Types', 'acf' ),
						'not_found'          => __( 'No Post Types found', 'acf' ),
						'not_found_in_trash' => __( 'No Post Types found in Trash', 'acf' ),
					),
					'public'          => false,
					'hierarchical'    => true,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'_builtin'        => false,
					'capability_type' => 'post',
					'capabilities'    => array(
						'edit_post'    => $cap,
						'delete_post'  => $cap,
						'edit_posts'   => $cap,
						'delete_posts' => $cap,
					),
					'supports'        => false,
					'rewrite'         => false,
					'query_var'       => false,
				)
			);
		}

		/**
		 * Register activated post types with WordPress
		 *
		 * @since 6.1
		 */
		public function register_post_types() {
			foreach ( $this->get_posts( array( 'active' => true ) ) as $post_type ) {
				$post_type_key  = $post_type['post_type'];
				$post_type_args = $this->get_post_type_args( $post_type );

				if ( ! post_type_exists( $post_type_key ) ) {
					register_post_type( $post_type_key, $post_type_args );
				} else {
					// Flag on the store we bailed on registering this as it already exists.
					$store                         = acf_get_store( $this->store );
					$store_value                   = $store->get( $post_type['key'] );
					$store_value['not_registered'] = true;
					$store->set( $post_type['key'], $store_value );
				}
			}
		}

		/**
		 * Gets the default settings array for an ACF post type.
		 *
		 * @return array
		 */
		public function get_settings_array() {
			return array(
				// ACF-specific settings.
				'ID'                       => 0,
				'key'                      => '',
				'title'                    => '',
				'menu_order'               => 0,
				'active'                   => true,
				'post_type'                => '', // First $post_type param passed to register_post_type().
				'advanced_configuration'   => false,
				'import_source'            => '',
				'import_date'              => '',
				// Settings passed to register_post_type().
				'labels'                   => array(
					'name'                     => '',
					'singular_name'            => '',
					'menu_name'                => '',
					'all_items'                => '',
					'add_new'                  => '',
					'add_new_item'             => '',
					'edit_item'                => '',
					'new_item'                 => '',
					'view_item'                => '',
					'view_items'               => '',
					'search_items'             => '',
					'not_found'                => '',
					'not_found_in_trash'       => '',
					'parent_item_colon'        => '',
					'archives'                 => '',
					'attributes'               => '',
					'featured_image'           => '',
					'set_featured_image'       => '',
					'remove_featured_image'    => '',
					'use_featured_image'       => '',
					'insert_into_item'         => '',
					'uploaded_to_this_item'    => '',
					'filter_items_list'        => '',
					'filter_by_date'           => '',
					'items_list_navigation'    => '',
					'items_list'               => '',
					'item_published'           => '',
					'item_published_privately' => '',
					'item_reverted_to_draft'   => '',
					'item_scheduled'           => '',
					'item_updated'             => '',
					'item_link'                => '',
					'item_link_description'    => '',
				),
				'description'              => '',
				'public'                   => true, // WP defaults false, ACF defaults true.
				'hierarchical'             => false,
				'exclude_from_search'      => false,
				'publicly_queryable'       => true,
				'show_ui'                  => true,
				'show_in_menu'             => true,
				'admin_menu_parent'        => '',
				'show_in_admin_bar'        => true,
				'show_in_nav_menus'        => true,
				'show_in_rest'             => true,
				'rest_base'                => '',
				'rest_namespace'           => 'wp/v2',
				'rest_controller_class'    => 'WP_REST_Posts_Controller',
				'menu_position'            => null,
				'menu_icon'                => '',
				'rename_capabilities'      => false,
				'singular_capability_name' => 'post',
				'plural_capability_name'   => 'posts',
				'supports'                 => array( 'title', 'editor', 'thumbnail' ),
				'taxonomies'               => array(),
				'has_archive'              => false,
				'has_archive_slug'         => '',
				'rewrite'                  => array(
					'permalink_rewrite' => 'post_type_key', // ACF-specific option.
					'slug'              => '',
					'feeds'             => false,
					'pages'             => true,
					'with_front'        => true,
				),
				'query_var'                => 'post_type_key',
				'query_var_name'           => '', // ACF-specific option.
				'can_export'               => true,
				'delete_with_user'         => false,
				'register_meta_box_cb'     => '',
			);
		}

		/**
		 * Validates an ACF internal post type.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post array.
		 * @return array
		 */
		public function validate_post( $post = array() ) {
			// Bail early if already valid.
			if ( is_array( $post ) && ! empty( $post['_valid'] ) ) {
				return $post;
			}

			$defaults = $this->get_settings_array();
			$post     = wp_parse_args(
				$post,
				$defaults
			);

			// Convert types.
			$post['ID']         = (int) $post['ID'];
			$post['menu_order'] = (int) $post['menu_order'];

			foreach ( $post as $setting => $value ) {
				if ( isset( $defaults[ $setting ] ) ) {
					$default_type = gettype( $defaults[ $setting ] );

					// register_post_type() needs proper booleans.
					if ( 'boolean' === $default_type && in_array( $value, array( '0', '1' ), true ) ) {
						$post[ $setting ] = (bool) $value;
					}

					if ( 'boolean' === $default_type && in_array( $value, array( 'false', 'true' ), true ) ) {
						$post[ $setting ] = ! ( 'false' === $value );
					}
				}
			}

			// Post is now valid.
			$post['_valid'] = true;

			/**
			 * Filters the ACF post array to validate settings.
			 *
			 * @date    12/02/2014
			 * @since   5.0.0
			 *
			 * @param   array $post The post array.
			 */
			return apply_filters( "acf/validate_{$this->hook_name}", $post );
		}

		/**
		 * Validates post type values before allowing save from the global $_POST object.
		 * Errors are added to the form using acf_add_internal_post_type_validation_error().
		 *
		 * @since 6.1
		 *
		 * @return bool validity status
		 */
		public function ajax_validate_values() {
			$post_type_key = acf_sanitize_request_args( $_POST['acf_post_type']['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
			$post_type_key = is_string( $post_type_key ) ? $post_type_key : '';
			$valid         = true;

			if ( strlen( $post_type_key ) > 20 ) {
				$valid = false;
				acf_add_internal_post_type_validation_error( 'post_type', __( 'The post type key must be under 20 characters.', 'acf' ) );
			}

			if ( preg_match( '/^[a-z0-9_-]*$/', $post_type_key ) !== 1 ) {
				$valid = false;
				acf_add_internal_post_type_validation_error( 'post_type', __( 'The post type key must only contain lower case alphanumeric characters, underscores or dashes.', 'acf' ) );
			}

			if ( in_array( $post_type_key, acf_get_wp_reserved_terms(), true ) ) {
				$valid = false;
				/* translators: %s a link to WordPress.org's Reserved Terms page */
				$message = sprintf( __( 'This field must not be a WordPress <a href="%s" target="_blank">reserved term</a>.', 'acf' ), 'https://codex.wordpress.org/Reserved_Terms' );
				acf_add_internal_post_type_validation_error( 'post_type', $message );
			} else {
				// Check if this post key exists in the ACF store for registered post types, excluding those which failed registration.
				$store      = acf_get_store( $this->store );
				$post_id    = (int) acf_sanitize_request_args( $_POST['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				$matches    = array_filter(
					$store->get_data(),
					function( $item ) use ( $post_type_key ) {
						return $item['post_type'] === $post_type_key && empty( $item['not_registered'] );
					}
				);
				$duplicates = array_filter(
					$matches,
					function( $item ) use ( $post_id ) {
						return $item['ID'] !== $post_id;
					}
				);

				if ( $duplicates ) {
					$valid = false;
					acf_add_internal_post_type_validation_error( 'post_type', __( 'This post type key is already in use by another post type in ACF and cannot be used.', 'acf' ) );
				} else {
					// If we're not already in use with another ACF post type, check if we're registered, but not by ACF.
					if ( empty( $matches ) && post_type_exists( $post_type_key ) ) {
						$valid = false;
						acf_add_internal_post_type_validation_error( 'post_type', __( 'This post type key is already in use by another post type registered outside of ACF and cannot be used.', 'acf' ) );
					}
				}
			}

			$valid = apply_filters( "acf/{$this->hook_name}/ajax_validate_values", $valid, $_POST['acf_post_type'] ); // phpcs:ignore WordPress.Security -- Raw input send to hook for validation.

			return $valid;
		}

		/**
		 * Parses ACF post type settings and returns an array of post type
		 * args that can be easily handled by `register_post_type()`.
		 *
		 * Omits settings that line up with the WordPress defaults to reduce the size
		 * of the array passed to `register_post_type()`, which might be exported.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main ACF post type settings array.
		 * @return array
		 */
		public function get_post_type_args( $post ) {
			$args = array();

			// Make sure any provided labels are strings and not empty.
			$labels = array_filter( $post['labels'] );
			$labels = array_map( 'strval', $labels );
			$labels = array_map( 'esc_html', $labels );

			if ( ! empty( $labels ) ) {
				$args['labels'] = $labels;
			}

			// Description is an optional string.
			if ( ! empty( $post['description'] ) ) {
				$args['description'] = (string) $post['description'];
			}

			// ACF requires the public setting to decide other settings.
			$args['public'] = ! empty( $post['public'] );

			// WordPress and ACF both default to false, so this can be omitted if still false.
			if ( ! empty( $post['hierarchical'] ) ) {
				$args['hierarchical'] = true;
			}

			// WordPress defaults to the opposite of $args['public'].
			$exclude_from_search = (bool) $post['exclude_from_search'];
			if ( $exclude_from_search === $args['public'] ) {
				$args['exclude_from_search'] = $exclude_from_search;
			}

			// WordPress defaults to the same as $args['public'].
			$publicly_queryable = (bool) $post['publicly_queryable'];
			if ( $publicly_queryable !== $args['public'] ) {
				$args['publicly_queryable'] = $publicly_queryable;
			}

			// WordPress defaults to the same as $args['public'].
			$show_ui = (bool) $post['show_ui'];
			if ( $show_ui !== $args['public'] ) {
				$args['show_ui'] = $show_ui;
			}

			// WordPress defaults to the same as $args['show_ui'], can be string or boolean.
			$show_in_menu = (bool) $post['show_in_menu'];
			if ( $show_in_menu !== $show_ui ) {
				$args['show_in_menu'] = $show_in_menu;
			}

			// WordPress also accepts a string for $args['show_in_menu'] to change the menu parent.
			if ( $show_in_menu && ! empty( $post['admin_menu_parent'] ) ) {
				$args['show_in_menu'] = (string) $post['admin_menu_parent'];
			}

			// WordPress defaults to the same as $args['public'].
			$show_in_nav_menus = (bool) $post['show_in_nav_menus'];
			if ( $show_in_nav_menus !== $args['public'] ) {
				$args['show_in_nav_menus'] = $show_in_nav_menus;
			}

			// WordPress defaults to the same as $show_in_menu.
			$show_in_admin_bar = (bool) $post['show_in_admin_bar'];
			if ( $show_in_admin_bar !== $show_in_menu ) {
				$args['show_in_admin_bar'] = $show_in_admin_bar;
			}

			// ACF defaults to true, but can be overridden.
			$show_in_rest         = (bool) $post['show_in_rest'];
			$args['show_in_rest'] = $show_in_rest;

			// WordPress defaults to $post_type.
			$rest_base = (string) $post['rest_base'];
			if ( ! empty( $rest_base ) && $rest_base !== $post['post_type'] ) {
				$args['rest_base'] = $rest_base;
			}

			// WordPress defaults to "wp/v2".
			$rest_namespace = (string) $post['rest_namespace'];
			if ( ! empty( $rest_namespace ) && 'wp/v2' !== $rest_namespace ) {
				$args['rest_namespace'] = $post['rest_namespace'];
			}

			// WordPress defaults to "WP_REST_Posts_Controller".
			$rest_controller_class = (string) $post['rest_controller_class'];
			if ( ! empty( $rest_controller_class ) && 'WP_REST_Posts_Controller' !== $rest_controller_class ) {
				$args['rest_controller_class'] = $rest_controller_class;
			}

			// WordPress defaults to `null` (below the comments menu item).
			$menu_position = (int) $post['menu_position'];
			if ( $menu_position ) {
				$args['menu_position'] = $menu_position;
			}

			// WordPress defaults to the same icon as the posts icon.
			$menu_icon = (string) $post['menu_icon'];
			if ( ! empty( $menu_icon ) ) {
				$args['menu_icon'] = $menu_icon;
			}

			// WordPress defaults to "post" for `$args['capability_type']`, but can also take an array.
			$rename_capabilities = (bool) $post['rename_capabilities'];
			if ( $rename_capabilities ) {
				$singular_capability_name = (string) $post['singular_capability_name'];
				$plural_capability_name   = (string) $post['plural_capability_name'];
				$capability_type          = 'post';

				if ( ! empty( $singular_capability_name ) && ! empty( $plural_capability_name ) ) {
					$capability_type = array( $singular_capability_name, $plural_capability_name );
				} elseif ( ! empty( $singular_capability_name ) ) {
					$capability_type = $singular_capability_name;
				}

				if ( $capability_type !== 'post' && $capability_type !== array( 'post', 'posts' ) ) {
					$args['capability_type'] = $capability_type;
					$args['map_meta_cap']    = true;
				}
			}

			// TODO: We don't handle the `capabilities` arg at the moment, but may in the future.

			// WordPress defaults to the "title" and "editor" supports, but none can be provided by passing false (WP 3.5+).
			$supports = is_array( $post['supports'] ) ? $post['supports'] : array();
			$supports = array_unique( array_filter( array_map( 'strval', $supports ) ) );

			if ( empty( $supports ) ) {
				$args['supports'] = false;
			} else {
				$args['supports'] = $supports;
			}

			// Handle register meta box callbacks if set from an import.
			if ( ! empty( $post['register_meta_box_cb'] ) ) {
				$args['register_meta_box_cb'] = (string) $post['register_meta_box_cb'];
			}

			// WordPress doesn't register any default taxonomies.
			$taxonomies = $post['taxonomies'];
			if ( ! is_array( $taxonomies ) ) {
				$taxonomies = (array) $taxonomies;
			}

			$taxonomies = array_filter( $taxonomies );
			if ( ! empty( $taxonomies ) ) {
				$args['taxonomies'] = $taxonomies;
			}

			// WordPress and ACF default to false, true or a string can also be provided.
			$has_archive = (bool) $post['has_archive'];
			if ( $has_archive ) {
				$has_archive_slug = (string) $post['has_archive_slug'];

				if ( ! empty( $has_archive_slug ) ) {
					$args['has_archive'] = $has_archive_slug;
				} else {
					$args['has_archive'] = true;
				}
			}

			// The rewrite arg can be a boolean or array of further settings. WordPress and ACF default to true.
			$rewrite         = (array) $post['rewrite'];
			$rewrite_enabled = true;
			$rewrite_args    = array();

			// ACF-specific select, defaults to "post_type_key".
			$rewrite['permalink_rewrite'] = isset( $rewrite['permalink_rewrite'] ) ? (string) $rewrite['permalink_rewrite'] : 'post_type_key';

			if ( 'no_permalink' === $rewrite['permalink_rewrite'] ) {
				$rewrite_enabled = false;
			}

			// Rewrite slug defaults to $post_type key if custom rewrites are not enabled.
			if ( ! empty( $rewrite['slug'] ) && $rewrite['slug'] !== $post['post_type'] && 'custom_permalink' === $rewrite['permalink_rewrite'] ) {
				$rewrite_args['slug'] = (string) $rewrite['slug'];
			}

			// WordPress defaults to true.
			if ( isset( $rewrite['with_front'] ) && ! $rewrite['with_front'] && $rewrite_enabled ) {
				$rewrite_args['with_front'] = false;
			}

			// WordPress defaults to value of `$args['has_archive']`.
			if ( isset( $rewrite['feeds'] ) && (bool) $rewrite['feeds'] !== $has_archive && $rewrite_enabled ) {
				$rewrite_args['feeds'] = (bool) $rewrite['feeds'];
			}

			// WordPress defaults to true.
			if ( isset( $rewrite['pages'] ) && ! $rewrite['pages'] && $rewrite_enabled ) {
				$rewrite_args['pages'] = false;
			}

			// Assemble rewrite args.
			if ( ! empty( $rewrite_args ) ) {
				$args['rewrite'] = $rewrite_args;
			} elseif ( ! $rewrite_enabled ) {
				$args['rewrite'] = false;
			}

			// WordPress and ACF default to $post_type key, a boolean can also be used.
			$query_var = (string) $post['query_var'];
			if ( 'custom_query_var' === $query_var ) {
				$query_var_name = (string) $post['query_var_name'];

				if ( ! empty( $query_var_name ) && $query_var_name !== $post['post_type'] ) {
					$args['query_var'] = $query_var_name;
				}
			} elseif ( 'none' === $query_var ) {
				$args['query_var'] = false;
			}

			// WordPress and ACF default to true.
			$can_export = (bool) $post['can_export'];
			if ( ! $can_export ) {
				$args['can_export'] = false;
			}

			// ACF defaults to false, while WordPress defaults to omitting (deletes only if author support is added).
			$args['delete_with_user'] = (bool) $post['delete_with_user'];

			return apply_filters( 'acf/post_type/registration_args', $args, $post );
		}

		/**
		 * Returns a string that can be used to create a post type in PHP.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post type array.
		 * @return string
		 */
		public function export_post_as_php( $post = array() ) {
			$return = '';
			if ( empty( $post ) ) {
				return $return;
			}

			$post_type_key = $post['post_type'];

			// Validate and prepare the post for export.
			$post = $this->validate_post( $post );
			$args = $this->get_post_type_args( $post );
			$code = var_export( $args, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Used for PHP export.

			if ( ! $code ) {
				return $return;
			}

			$code = $this->format_code_for_export( $code );

			$return .= "register_post_type( '{$post_type_key}', {$code} );\r\n";

			return esc_textarea( $return );
		}

		/**
		 * Flush rewrite rules whenever anything changes about a post type.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post type array.
		 */
		public function flush_post_cache( $post ) {
			// Bail early if we won't be able to register/unregister the post type.
			if ( empty( $post['post_type'] ) ) {
				return;
			}

			// Temporarily unregister the post type so that we can potentially re-register with the latest args below.
			if ( empty( $post['active'] ) || post_type_exists( $post['post_type'] ) ) {
				unregister_post_type( $post['post_type'] );
			}

			// When this is being called, the post type may not have been registered yet, so we do it now.
			if ( ! empty( $post['active'] ) ) {
				register_post_type( $post['post_type'], $this->get_post_type_args( $post ) );
			}

			// Flush our internal cache and the WordPress rewrite rules.
			parent::flush_post_cache( $post );
			flush_rewrite_rules();
		}

		/**
		 * Translates an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param array $post The field group array.
		 * @return array
		 */
		public function translate_post( $post = array() ) {
			// Get settings.
			$l10n            = acf_get_setting( 'l10n' );
			$l10n_textdomain = acf_get_setting( 'l10n_textdomain' );

			// Translate field settings if textdomain is set.
			if ( $l10n && $l10n_textdomain ) {
				$post['title']       = acf_translate( $post['title'] );
				$post['description'] = acf_translate( $post['description'] );
				foreach ( $post['labels'] as $key => $label ) {
					$post['labels'][ $key ] = acf_translate( $label );
				}

				/**
				 * Filters the post array to translate strings.
				 *
				 * @date    12/02/2014
				 * @since   5.0.0
				 *
				 * @param   array $post The post array.
				 */
				$post = apply_filters( "acf/translate_{$this->hook_name}", $post );
			}

			return $post;
		}

		/**
		 * Imports a post type from CPTUI.
		 *
		 * @since 6.1
		 *
		 * @param array $args Arguments from CPTUI.
		 * @return array
		 */
		public function import_cptui_post_type( $args ) {
			$acf_args = $this->get_settings_array();

			// Convert string boolean values to proper booleans.
			foreach ( $args as $key => $value ) {
				if ( in_array( $value, array( 'true', 'false' ), true ) ) {
					$args[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				}
			}

			if ( isset( $args['name'] ) ) {
				$acf_args['post_type'] = (string) $args['name'];
				unset( $args['name'] );
			}

			if ( isset( $args['labels'] ) ) {
				$acf_args['labels'] = array_merge( $acf_args['labels'], $args['labels'] );
				unset( $args['labels'] );
			}

			// ACF uses "name" as title, and stores in labels array.
			if ( isset( $args['label'] ) ) {
				$acf_args['title']          = (string) $args['label'];
				$acf_args['labels']['name'] = (string) $args['label'];
				unset( $args['label'] );
			}

			if ( isset( $args['singular_label'] ) ) {
				$acf_args['labels']['singular_name'] = (string) $args['singular_label'];
				unset( $args['singular_label'] );
			}

			if ( isset( $args['show_in_menu_string'] ) ) {
				$acf_args['admin_menu_parent'] = (string) $args['show_in_menu_string'];
				unset( $args['show_in_menu_string'] );
			}

			if ( isset( $args['rewrite'] ) ) {
				$rewrite = (bool) $args['rewrite'];

				if ( ! $rewrite ) {
					$acf_args['rewrite']['permalink_rewrite'] = 'no_permalink';
				} elseif ( ! empty( $args['rewrite_slug'] ) ) {
					$acf_args['rewrite']['permalink_rewrite'] = 'custom_permalink';
				} else {
					$acf_args['rewrite']['permalink_rewrite'] = 'post_type_key';
				}

				unset( $args['rewrite'] );
			}

			if ( isset( $args['rewrite_slug'] ) ) {
				$acf_args['rewrite']['slug'] = (string) $args['rewrite_slug'];
				unset( $args['rewrite_slug'] );
			}

			if ( isset( $args['rewrite_withfront'] ) ) {
				$acf_args['rewrite']['with_front'] = (bool) $args['rewrite_withfront'];
				unset( $args['rewrite_withfront'] );
			}

			// TODO: Investigate CPTUI usage of with_feeds, pages settings.

			// ACF handles capability type differently.
			if ( isset( $args['capability_type'] ) ) {
				if ( 'post' !== trim( $args['capability_type'] ) ) {
					$acf_args['rename_capabilities'] = true;

					$capabilities = explode( ',', $args['capability_type'] );
					$capabilities = array_map( 'trim', $capabilities );

					$acf_args['singular_capability_name'] = $capabilities[0];

					if ( count( $capabilities ) > 1 ) {
						$acf_args['plural_capability_name'] = $capabilities[1];
					}
				}

				unset( $args['capability_type'] );
			}

			// ACF names the has_archive slug differently.
			if ( isset( $args['has_archive_string'] ) ) {
				$acf_args['has_archive_slug'] = (string) $args['has_archive_string'];
				unset( $args['has_archive_string'] );
			}

			// ACF handles the query var and query var slug/name differently.
			if ( isset( $args['query_var'] ) ) {
				if ( ! $args['query_var'] ) {
					$acf_args['query_var'] = 'none';
				} elseif ( ! empty( $args['query_var_slug'] ) ) {
					$acf_args['query_var'] = 'custom_query_var';
				} else {
					$acf_args['query_var'] = 'post_type_key';
				}

				unset( $args['query_var'] );
			}

			if ( isset( $args['query_var_slug'] ) ) {
				$acf_args['query_var_name'] = (string) $args['query_var_slug'];
				unset( $args['query_var_slug'] );
			}

			// ACF doesn't support custom "Enter title here" text.
			if ( isset( $args['enter_title_here'] ) ) {
				unset( $args['enter_title_here'] );
			}

			$acf_args = wp_parse_args( $args, $acf_args );

			// ACF doesn't yet handle custom supports, so we're tacking onto the regular supports.
			if ( isset( $args['custom_supports'] ) ) {
				$custom_supports = explode( ',', (string) $args['custom_supports'] );
				$custom_supports = array_filter( array_map( 'trim', $custom_supports ) );

				if ( ! empty( $custom_supports ) ) {
					$acf_args['supports'] = array_merge( $acf_args['supports'], $custom_supports );
				}

				unset( $acf_args['custom_supports'] );
			}

			$acf_args['key']                    = uniqid( 'post_type_' );
			$acf_args['advanced_configuration'] = true;
			$acf_args['import_source']          = 'cptui';
			$acf_args['import_date']            = time();

			$existing_post_types = acf_get_acf_post_types();

			foreach ( $existing_post_types as $existing_post_type ) {
				// Post type already exists, so we need to update rather than import.
				if ( $acf_args['post_type'] === $existing_post_type['post_type'] ) {
					$acf_args        = $this->prepare_post_for_import( $acf_args );
					$acf_args['ID']  = $existing_post_type['ID'];
					$acf_args['key'] = $existing_post_type['key'];
					return $this->update_post( $acf_args );
				}
			}

			// Import the post normally.
			return $this->import_post( $acf_args );
		}

	}

}

acf_new_instance( 'ACF_Post_Type' );
