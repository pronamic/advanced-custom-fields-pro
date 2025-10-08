<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Taxonomy' ) ) {
	class ACF_Taxonomy extends ACF_Internal_Post_Type {

		/**
		 * The ACF internal post type name.
		 *
		 * @var string
		 */
		public $post_type = 'acf-taxonomy';

		/**
		 * The prefix for the key used in the main post array.
		 *
		 * @var string
		 */
		public $post_key_prefix = 'taxonomy_';

		/**
		 * The cache key for a singular post.
		 *
		 * @var string
		 */
		public $cache_key = 'acf_get_taxonomy_post:key:';

		/**
		 * The cache key for a collection of posts.
		 *
		 * @var string
		 */
		public $cache_key_plural = 'acf_get_taxonomy_posts';

		/**
		 * The hook name for a singular post.
		 *
		 * @var string
		 */
		public $hook_name = 'taxonomy';

		/**
		 * The hook name for a collection of posts.
		 *
		 * @var string
		 */
		public $hook_name_plural = 'taxonomies';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = 'taxonomies';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			$this->register_post_type();

			// Include admin classes in admin.
			if ( is_admin() ) {
				acf_include( 'includes/admin/admin-internal-post-type-list.php' );
				acf_include( 'includes/admin/admin-internal-post-type.php' );
				acf_include( 'includes/admin/post-types/admin-taxonomy.php' );
				acf_include( 'includes/admin/post-types/admin-taxonomies.php' );
			}

			parent::__construct();

			add_action( 'acf/init', array( $this, 'register_taxonomies' ), 6 );
		}

		/**
		 * Registers the acf-taxonomy custom post type with WordPress.
		 *
		 * @since 6.1
		 */
		public function register_post_type() {
			$cap = acf_get_setting( 'capability' );

			register_post_type(
				'acf-taxonomy',
				array(
					'labels'          => array(
						'name'               => __( 'Taxonomies', 'acf' ),
						'singular_name'      => __( 'Taxonomies', 'acf' ),
						'add_new'            => __( 'Add New', 'acf' ),
						'add_new_item'       => __( 'Add New Taxonomy', 'acf' ),
						'edit_item'          => __( 'Edit Taxonomy', 'acf' ),
						'new_item'           => __( 'New Taxonomy', 'acf' ),
						'view_item'          => __( 'View Taxonomy', 'acf' ),
						'search_items'       => __( 'Search Taxonomies', 'acf' ),
						'not_found'          => __( 'No Taxonomies found', 'acf' ),
						'not_found_in_trash' => __( 'No Taxonomies found in Trash', 'acf' ),
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
		 * Register activated taxonomies with WordPress
		 *
		 * @since 6.1
		 */
		public function register_taxonomies() {
			$taxonomies = $this->get_posts( array( 'active' => true ) );
			foreach ( $taxonomies as $taxonomy ) {
				$args = $this->get_taxonomy_args( $taxonomy );
				if ( ! taxonomy_exists( $taxonomy['taxonomy'] ) ) {
					register_taxonomy( $taxonomy['taxonomy'], (array) $taxonomy['object_type'], $args );
				} else {
						// Flag on the store we bailed on registering this as it already exists.
						$store                         = acf_get_store( $this->store );
						$store_value                   = $store->get( $taxonomy['key'] );
						$store_value['not_registered'] = true;
						$store->set( $taxonomy['key'], $store_value );
				}
			}
		}

		/**
		 * Gets the default settings array for an ACF taxonomy.
		 *
		 * @return array
		 */
		public function get_settings_array() {
			return array(
				// ACF-specific settings.
				'ID'                     => 0,
				'key'                    => '',
				'title'                  => '',
				'menu_order'             => 0,
				'active'                 => true,
				'taxonomy'               => '', // Taxonomy key passed as first param to register_taxonomy().
				'object_type'            => array(), // Converted to objects array passed as second parameter.
				'advanced_configuration' => 0,
				'import_source'          => '',
				'import_date'            => '',
				// Settings passed to register_taxonomy().
				'labels'                 => array(
					'singular_name'              => '',
					'name'                       => '',
					'menu_name'                  => '',
					'search_items'               => '',
					'popular_items'              => '',
					'all_items'                  => '',
					'parent_item'                => '',
					'parent_item_colon'          => '',
					'name_field_description'     => '',
					'slug_field_description'     => '',
					'parent_field_description'   => '',
					'desc_field_description'     => '',
					'edit_item'                  => '',
					'view_item'                  => '',
					'update_item'                => '',
					'add_new_item'               => '',
					'new_item_name'              => '',
					'separate_items_with_commas' => '',
					'add_or_remove_items'        => '',
					'choose_from_most_used'      => '',
					'not_found'                  => '',
					'no_terms'                   => '',
					'filter_by_item'             => '',
					'items_list_navigation'      => '',
					'items_list'                 => '',
					'most_used'                  => '',
					'back_to_items'              => '',
					'item_link'                  => '',
					'item_link_description'      => '',
				),
				'description'            => '',
				'capabilities'           => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'edit_posts',
				),
				'public'                 => true,
				'publicly_queryable'     => true,
				'hierarchical'           => false,
				'show_ui'                => true,
				'show_in_menu'           => true,
				'show_in_nav_menus'      => true,
				'show_in_rest'           => true,
				'rest_base'              => '',
				'rest_namespace'         => 'wp/v2',
				'rest_controller_class'  => 'WP_REST_Terms_Controller',
				'show_tagcloud'          => true,
				'show_in_quick_edit'     => true,
				'show_admin_column'      => false,
				'rewrite'                => array(
					'permalink_rewrite'    => 'taxonomy_key', // ACF-specific option.
					'slug'                 => '',
					'with_front'           => true,
					'rewrite_hierarchical' => false,
				),
				'query_var'              => 'taxonomy_key',
				'query_var_name'         => '',
				'default_term'           => array(
					'default_term_enabled'     => false,
					'default_term_name'        => '',
					'default_term_slug'        => '',
					'default_term_description' => '',
				),
				'sort'                   => null,
				'meta_box'               => 'default',
				'meta_box_cb'            => '',
				'meta_box_sanitize_cb'   => '',
			);
		}

		/**
		 * Validates post type values before allowing save from the global $_POST object.
		 * Errors are added to the form using acf_add_internal_post_type_validation_error().
		 *
		 * @since 6.1
		 *
		 * @return boolean validity status
		 */
		public function ajax_validate_values() {
			if ( empty( $_POST['acf_taxonomy'] ) || empty( $_POST['acf_taxonomy']['taxonomy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				return false;
			}

			$taxonomy_key = acf_sanitize_request_args( wp_unslash( $_POST['acf_taxonomy']['taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
			$taxonomy_key = is_string( $taxonomy_key ) ? $taxonomy_key : '';
			$valid        = true;

			if ( strlen( $taxonomy_key ) > 32 ) {
				$valid = false;
				acf_add_internal_post_type_validation_error( 'taxonomy', __( 'The taxonomy key must be under 32 characters.', 'acf' ) );
			}

			if ( preg_match( '/^[a-z0-9_-]*$/', $taxonomy_key ) !== 1 ) {
				$valid = false;
				acf_add_internal_post_type_validation_error( 'taxonomy', __( 'The taxonomy key must only contain lower case alphanumeric characters, underscores or dashes.', 'acf' ) );
			}

			if ( in_array( $taxonomy_key, acf_get_wp_reserved_terms(), true ) ) {
				$valid = false;
				/* translators: %s a link to WordPress.org's Reserved Terms page */
				$message = sprintf( __( 'This field must not be a WordPress <a href="%s" target="_blank">reserved term</a>.', 'acf' ), 'https://codex.wordpress.org/Reserved_Terms' );
				acf_add_internal_post_type_validation_error( 'taxonomy', $message );
			} else {
				// Check if this post key exists in the ACF store for registered post types, excluding those which failed registration.
				$store   = acf_get_store( $this->store );
				$post_id = (int) acf_maybe_get_POST( 'post_id', 0 );

				$matches    = array_filter(
					$store->get_data(),
					function ( $item ) use ( $taxonomy_key ) {
						return $item['taxonomy'] === $taxonomy_key && empty( $item['not_registered'] );
					}
				);
				$duplicates = array_filter(
					$matches,
					function ( $item ) use ( $post_id ) {
						return $item['ID'] !== $post_id;
					}
				);

				if ( $duplicates ) {
					$valid = false;
					acf_add_internal_post_type_validation_error( 'taxonomy', __( 'This taxonomy key is already in use by another taxonomy in ACF and cannot be used.', 'acf' ) );
					// If we're not already in use with another ACF taxonomy, check if we're registered, but not by ACF.
				} elseif ( empty( $matches ) && taxonomy_exists( $taxonomy_key ) ) {
					$valid = false;
					acf_add_internal_post_type_validation_error( 'taxonomy', __( 'This taxonomy key is already in use by another taxonomy registered outside of ACF and cannot be used.', 'acf' ) );
				}
			}

			$valid = apply_filters( "acf/{$this->hook_name}/ajax_validate_values", $valid, $_POST['acf_taxonomy'] ); // phpcs:ignore WordPress.Security -- Raw input send to hook for validation.

			return $valid;
		}

		/**
		 * Parses ACF taxonomy settings and returns an array of taxonomy
		 * args that can be easily handled by `register_taxonomy()`.
		 *
		 * Omits settings that line up with the WordPress defaults to reduce the size
		 * of the array passed to `register_taxonomy()`, which might be exported.
		 *
		 * @since 6.1
		 *
		 * @param  array   $post          The main ACF taxonomy settings array.
		 * @param  boolean $escape_labels Determines if the label values should be escaped.
		 * @return array
		 */
		public function get_taxonomy_args( $post, $escape_labels = true ) {
			$args = array();

			// Make sure any provided labels are escaped strings and not empty.
			$labels = array_filter( $post['labels'] );
			$labels = array_map( 'strval', $labels );
			if ( $escape_labels ) {
				$labels = array_map( 'esc_html', $labels );
			}
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

			// WordPress defaults to the same as $args['show_ui'].
			$show_in_menu = $post['show_in_menu'];
			if ( $show_in_menu !== $show_ui ) {
				$args['show_in_menu'] = (bool) $show_in_menu;
			}

			// WordPress defaults to the same as $args['public'].
			$show_in_nav_menus = (bool) $post['show_in_nav_menus'];
			if ( $show_in_nav_menus !== $args['public'] ) {
				$args['show_in_nav_menus'] = $show_in_nav_menus;
			}

			// ACF defaults to true, but can be overridden.
			$show_in_rest         = (bool) $post['show_in_rest'];
			$args['show_in_rest'] = $show_in_rest;

			// WordPress defaults to `$taxonomy`.
			$rest_base = (string) $post['rest_base'];
			if ( ! empty( $rest_base ) && $rest_base !== $post['taxonomy'] ) {
				$args['rest_base'] = $rest_base;
			}

			// WordPress defaults to "wp/v2".
			$rest_namespace = (string) $post['rest_namespace'];
			if ( ! empty( $rest_namespace ) && 'wp/v2' !== $rest_namespace ) {
				$args['rest_namespace'] = $post['rest_namespace'];
			}

			// WordPress defaults to `WP_REST_Terms_Controller`.
			$rest_controller_class = (string) $post['rest_controller_class'];
			if ( ! empty( $rest_controller_class ) && 'WP_REST_Terms_Controller' !== $rest_controller_class ) {
				$args['rest_controller_class'] = $rest_controller_class;
			}

			// WordPress defaults to the same as `$args['show_ui']`.
			$show_tagcloud = (bool) $post['show_tagcloud'];
			if ( $show_tagcloud !== $show_ui ) {
				$args['show_tagcloud'] = $show_tagcloud;
			}

			// WordPress defaults to the same as `$args['show_ui']`.
			$show_in_quick_edit = (bool) $post['show_in_quick_edit'];
			if ( $show_in_quick_edit !== $show_ui ) {
				$args['show_in_quick_edit'] = $show_tagcloud;
			}

			// WordPress defaults to false.
			$show_admin_column = (bool) $post['show_admin_column'];
			if ( $show_admin_column ) {
				$args['show_admin_column'] = true;
			}

			$capabilities = array();

			if ( ! empty( $post['capabilities']['manage_terms'] ) && 'manage_categories' !== $post['capabilities']['manage_terms'] ) {
				$capabilities['manage_terms'] = (string) $post['capabilities']['manage_terms'];
			}

			if ( ! empty( $post['capabilities']['edit_terms'] ) && 'manage_categories' !== $post['capabilities']['edit_terms'] ) {
				$capabilities['edit_terms'] = (string) $post['capabilities']['edit_terms'];
			}

			if ( ! empty( $post['capabilities']['delete_terms'] ) && 'manage_categories' !== $post['capabilities']['delete_terms'] ) {
				$capabilities['delete_terms'] = (string) $post['capabilities']['delete_terms'];
			}

			if ( ! empty( $post['capabilities']['assign_terms'] ) && 'edit_posts' !== $post['capabilities']['assign_terms'] ) {
				$capabilities['assign_terms'] = (string) $post['capabilities']['assign_terms'];
			}

			if ( ! empty( $capabilities ) ) {
				$args['capabilities'] = $capabilities;
			}

			// WordPress defaults to the tags/categories metabox, but a custom callback or `false` is also supported.
			$meta_box = isset( $post['meta_box'] ) ? (string) $post['meta_box'] : 'default';

			if ( 'custom' === $meta_box && ! empty( $post['meta_box_cb'] ) ) {
				$args['meta_box_cb'] = array( $this, 'build_safe_context_for_metabox_cb' );

				if ( ! empty( $post['meta_box_sanitize_cb'] ) ) {
					$args['meta_box_sanitize_cb'] = (string) $post['meta_box_sanitize_cb'];
				}
			} elseif ( 'disabled' === $meta_box ) {
				$args['meta_box_cb'] = false;
			}

			// The rewrite arg can be a boolean or array of further settings. WordPress and ACF default to true.
			$rewrite         = (array) $post['rewrite'];
			$rewrite_enabled = true;
			$rewrite_args    = array();

			// Value of ACF select (not passed to `register_taxonomy()`).
			$rewrite['permalink_rewrite'] = isset( $rewrite['permalink_rewrite'] ) ? (string) $rewrite['permalink_rewrite'] : 'taxonomy_key';

			if ( 'no_permalink' === $rewrite['permalink_rewrite'] ) {
				$rewrite_enabled = false;
			}

			// Rewrite slug defaults to $taxonomy key.
			if ( ! empty( $rewrite['slug'] ) && $rewrite['slug'] !== $post['taxonomy'] && 'custom_permalink' === $rewrite['permalink_rewrite'] ) {
				$rewrite_args['slug'] = (string) $rewrite['slug'];
			}

			// WordPress defaults to true.
			if ( isset( $rewrite['with_front'] ) && ! $rewrite['with_front'] && $rewrite_enabled ) {
				$rewrite_args['with_front'] = false;
			}

			// WordPress defaults to false.
			if ( isset( $rewrite['rewrite_hierarchical'] ) && $rewrite['rewrite_hierarchical'] && $rewrite_enabled ) {
				$rewrite_args['hierarchical'] = true;
			}

			if ( $rewrite_enabled && ! empty( $rewrite_args ) ) {
				$args['rewrite'] = $rewrite_args;
			} elseif ( ! $rewrite_enabled ) {
				$args['rewrite'] = false;
			}

			// WordPress and ACF default to $taxonomy key, a boolean can also be used.
			$query_var = (string) $post['query_var'];
			if ( 'custom_query_var' === $query_var ) {
				$query_var_name = (string) $post['query_var_name'];

				if ( ! empty( $query_var_name ) && $query_var_name !== $post['taxonomy'] ) {
					$args['query_var'] = $query_var_name;
				}
			} elseif ( 'none' === $query_var ) {
				$args['query_var'] = false;
			}

			// WordPress accepts a string or an array of term info, but always converts into an array.
			$default_term = (array) $post['default_term'];
			if ( isset( $default_term['default_term_enabled'] ) && $default_term['default_term_enabled'] ) {
				$args['default_term'] = array();

				if ( isset( $default_term['default_term_name'] ) && ! empty( $default_term['default_term_name'] ) ) {
					$args['default_term']['name'] = (string) $default_term['default_term_name'];
				}

				if ( isset( $default_term['default_term_slug'] ) && ! empty( $default_term['default_term_slug'] ) ) {
					$args['default_term']['slug'] = (string) $default_term['default_term_slug'];
				}

				if ( isset( $default_term['default_term_description'] ) && ! empty( $default_term['default_term_description'] ) ) {
					$args['default_term']['description'] = (string) $default_term['default_term_description'];
				}
			}

			// WordPress defaults to null, equivalent to false.
			$sort = (bool) $post['sort'];
			if ( $sort ) {
				$args['sort'] = true;
			}

			return apply_filters( 'acf/taxonomy/registration_args', $args, $post );
		}

		/**
		 * Ensure the metabox being called does not perform any unsafe operations.
		 *
		 * @since 6.3.8
		 *
		 * @param WP_Post $post The post being rendered.
		 * @param array   $tax  The provided taxonomy information required for callback render.
		 * @return mixed The callback result.
		 */
		public function build_safe_context_for_metabox_cb( $post, $tax ) {
			$taxonomies = $this->get_posts();
			$this_tax   = array_filter(
				$taxonomies,
				function ( $taxonomy ) use ( $tax ) {
					return $taxonomy['taxonomy'] === $tax['args']['taxonomy'];
				}
			);
			if ( empty( $this_tax ) || ! is_array( $this_tax ) ) {
				// Unable to find the ACF taxonomy. Don't do anything.
				return;
			}
			$acf_taxonomy = array_shift( $this_tax );
			$original_cb  = isset( $acf_taxonomy['meta_box_cb'] ) ? $acf_taxonomy['meta_box_cb'] : false;

			// Prevent access to any wp_ prefixed functions in a callback.
			if ( apply_filters( 'acf/taxonomy/prevent_access_to_wp_functions_in_meta_box_cb', true ) && substr( strtolower( $original_cb ), 0, 3 ) === 'wp_' ) {
				// Don't execute register meta box callbacks if an internal wp function by default.
				return;
			}

			$unset     = array( '_POST', '_GET', '_REQUEST', '_COOKIE', '_SESSION', '_FILES', '_ENV', '_SERVER' );
			$originals = array();

			foreach ( $unset as $var ) {
				if ( isset( $GLOBALS[ $var ] ) ) {
					$originals[ $var ] = $GLOBALS[ $var ];
					$GLOBALS[ $var ]   = array(); //phpcs:ignore -- used for building a safe context
				}
			}

			$return = false;
			if ( is_callable( $original_cb ) ) {
				$return = call_user_func( $original_cb, $post, $tax );
			}

			foreach ( $unset as $var ) {
				if ( isset( $originals[ $var ] ) ) {
					$GLOBALS[ $var ] = $originals[ $var ]; //phpcs:ignore -- used for restoring the original context
				}
			}

			return $return;
		}

		/**
		 * Returns a string that can be used to create a taxonomy in PHP.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main taxonomy array.
		 * @return string
		 */
		public function export_post_as_php( $post = array() ) {
			$return = '';
			if ( empty( $post ) ) {
				return $return;
			}

			$post         = $this->validate_post( $post );
			$taxonomy_key = $post['taxonomy'];
			$objects      = (array) $post['object_type'];
			$objects      = var_export( $objects, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Used for PHP export.
			$args         = $this->get_taxonomy_args( $post, false );

			// Restore original metabox callback.
			if ( ! empty( $args['meta_box_cb'] ) && ! empty( $post['meta_box_cb'] ) ) {
				$args['meta_box_cb'] = $post['meta_box_cb'];
			}

			$args = var_export( $args, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Used for PHP export.

			if ( ! $args ) {
				return $return;
			}

			$args    = $this->format_code_for_export( $args );
			$objects = $this->format_code_for_export( $objects );

			$return .= "register_taxonomy( '{$taxonomy_key}', {$objects}, {$args} );\r\n";

			return esc_textarea( $return );
		}

		/**
		 * Flush rewrite rules whenever anything changes about a taxonomy.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post type array.
		 */
		public function flush_post_cache( $post ) {
			// Bail early if we won't be able to register/unregister the taxonomy.
			if ( ! isset( $post['taxonomy'] ) ) {
				return;
			}

			// Temporarily unregister the taxonomy so that we can re-register with the latest args below.
			if ( empty( $post['active'] ) || taxonomy_exists( $post['taxonomy'] ) ) {
				unregister_taxonomy( $post['taxonomy'] );
			}

			// When this is being called, the post type may not have been registered yet, so we do it now.
			if ( ! empty( $post['active'] ) ) {
				$post['object_type'] = isset( $post['object_type'] ) ? (array) $post['object_type'] : array();
				register_taxonomy( $post['taxonomy'], $post['object_type'], $this->get_taxonomy_args( $post ) );
			}

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
		 * Prepares an ACF taxonomy for import.
		 *
		 * @since 6.3.10
		 *
		 * @param array $post The ACF post array.
		 * @return array
		 */
		public function prepare_post_for_import( $post ) {
			if ( ! acf_get_setting( 'enable_meta_box_cb_edit' ) && ( ! empty( $post['meta_box_cb'] ) || ! empty( $post['meta_box_sanitize_cb'] ) ) ) {
				$post['meta_box_cb']          = '';
				$post['meta_box_sanitize_cb'] = '';

				if ( ! empty( $post['meta_box'] ) && 'custom' === $post['meta_box'] ) {
					$post['meta_box'] = 'default';
				}

				if ( ! empty( $post['ID'] ) ) {
					$existing_post = $this->get_post( $post['ID'] );

					if ( is_array( $existing_post ) ) {
						$post['meta_box']             = ! empty( $existing_post['meta_box'] ) ? (string) $existing_post['meta_box'] : '';
						$post['meta_box_cb']          = ! empty( $existing_post['meta_box_cb'] ) ? (string) $existing_post['meta_box_cb'] : '';
						$post['meta_box_sanitize_cb'] = ! empty( $existing_post['meta_box_sanitize_cb'] ) ? (string) $existing_post['meta_box_sanitize_cb'] : '';
					}
				}
			}

			return parent::prepare_post_for_import( $post );
		}

		/**
		 * Imports a taxonomy from CPTUI.
		 *
		 * @since 6.1
		 *
		 * @param array $args Arguments from CPTUI.
		 * @return array
		 */
		public function import_cptui_taxonomy( $args ) {
			$acf_args = $this->get_settings_array();

			// Convert string boolean values to proper booleans.
			foreach ( $args as $key => $value ) {
				if ( in_array( $value, array( 'true', 'false' ), true ) ) {
					$args[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				}
			}

			if ( isset( $args['name'] ) ) {
				$acf_args['taxonomy'] = (string) $args['name'];
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

			// ACF handles the meta_box_cb with two different settings.
			if ( isset( $args['meta_box_cb'] ) ) {
				if ( false === $args['meta_box_cb'] ) {
					$acf_args['meta_box'] = 'disabled';
				} elseif ( ! empty( $args['meta_box_cb'] ) ) {
					$acf_args['meta_box']    = 'custom';
					$acf_args['meta_box_cb'] = $args['meta_box_cb'];
				}

				unset( $args['meta_box_cb'] );
			}

			// ACF handles the query var and query var slug/name differently.
			if ( isset( $args['query_var'] ) ) {
				if ( ! $args['query_var'] ) {
					$acf_args['query_var'] = 'none';
				} elseif ( ! empty( $args['query_var_slug'] ) ) {
					$acf_args['query_var'] = 'custom_query_var';
				} else {
					$acf_args['query_var'] = 'taxonomy_key';
				}

				unset( $args['query_var'] );
			}

			if ( isset( $args['query_var_slug'] ) ) {
				$acf_args['query_var_name'] = (string) $args['query_var_slug'];
				unset( $args['query_var_slug'] );
			}

			if ( isset( $args['rewrite'] ) ) {
				$rewrite = (bool) $args['rewrite'];

				if ( ! $rewrite ) {
					$acf_args['rewrite']['permalink_rewrite'] = 'no_permalink';
				} elseif ( ! empty( $args['rewrite_slug'] ) ) {
					$acf_args['rewrite']['permalink_rewrite'] = 'custom_permalink';
				} else {
					$acf_args['rewrite']['permalink_rewrite'] = 'taxonomy_key';
				}

				unset( $args['rewrite'] );
			}

			if ( isset( $args['rewrite_slug'] ) ) {
				$acf_args['rewrite']['slug'] = (string) $args['rewrite_slug'];
				unset( $args['rewrite_slug'] );
			}

			if ( isset( $args['rewrite_withfront'] ) ) {
				$acf_args['rewrite']['with_front'] = $args['rewrite_withfront'] === '1';
				unset( $args['rewrite_withfront'] );
			}

			if ( isset( $args['rewrite_hierarchical'] ) ) {
				$acf_args['rewrite']['rewrite_hierarchical'] = $args['rewrite_hierarchical'] === '1';
				unset( $args['rewrite_hierarchical'] );
			}

			// CPTUI stores all default_term settings as comma separate values.
			if ( isset( $args['default_term'] ) ) {
				if ( '' !== $args['default_term'] ) {
					$default_term = explode( ',', $args['default_term'] );
					$default_term = array_map( 'trim', $default_term );

					if ( isset( $default_term[0] ) ) {
						$acf_args['default_term']['default_term_enabled'] = true;
						$acf_args['default_term']['default_term_name']    = (string) $default_term[0];
					}

					if ( isset( $default_term[1] ) ) {
						$acf_args['default_term']['default_term_slug'] = (string) $default_term[1];
					}

					if ( isset( $default_term[2] ) ) {
						$acf_args['default_term']['default_term_description'] = (string) $default_term[2];
					}
				}

				unset( $args['default_term'] );
			}

			if ( isset( $args['object_types'] ) ) {
				$acf_args['object_type'] = $args['object_types'];
				unset( $args['object_types'] );
			}

			$acf_args                           = wp_parse_args( $args, $acf_args );
			$acf_args['key']                    = uniqid( 'taxonomy_' );
			$acf_args['advanced_configuration'] = true;
			$acf_args['import_source']          = 'cptui';
			$acf_args['import_date']            = time();

			$existing_taxonomies = acf_get_acf_taxonomies();

			foreach ( $existing_taxonomies as $existing_taxonomy ) {
				// Taxonomy already exists, so we need to update rather than import.
				if ( $acf_args['taxonomy'] === $existing_taxonomy['taxonomy'] ) {
					$acf_args        = $this->prepare_post_for_import( $acf_args );
					$acf_args['ID']  = $existing_taxonomy['ID'];
					$acf_args['key'] = $existing_taxonomy['key'];
					return $this->update_post( $acf_args );
				}
			}

			return $this->import_post( $acf_args );
		}
	}

}

acf_new_instance( 'ACF_Taxonomy' );
