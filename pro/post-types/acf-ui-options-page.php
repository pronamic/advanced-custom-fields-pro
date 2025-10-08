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

if ( ! class_exists( 'ACF_UI_Options_Page' ) ) {

	class ACF_UI_Options_Page extends ACF_Internal_Post_Type {

		/**
		 * The ACF internal post type name.
		 *
		 * @var string
		 */
		public $post_type = 'acf-ui-options-page';

		/**
		 * The prefix for the key used in the main post array.
		 *
		 * @var string
		 */
		public $post_key_prefix = 'ui_options_page_';

		/**
		 * The cache key for a singular post.
		 *
		 * @var string
		 */
		public $cache_key = 'acf_get_ui_options_page_post:key:';

		/**
		 * The cache key for a collection of posts.
		 *
		 * @var string
		 */
		public $cache_key_plural = 'acf_get_ui_options_page_posts';

		/**
		 * The hook name for a singular post.
		 *
		 * @var string
		 */
		public $hook_name = 'ui_options_page';

		/**
		 * The hook name for a collection of posts.
		 *
		 * @var string
		 */
		public $hook_name_plural = 'ui_options_pages';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = 'ui-options-pages';

		/**
		 * Constructs the class and any parent classes.
		 *
		 * @since 6.2
		 */
		public function __construct() {
			$this->register_post_type();

			// Include admin classes in admin.
			if ( is_admin() ) {
				acf_include( 'includes/admin/admin-internal-post-type-list.php' );
				acf_include( 'includes/admin/admin-internal-post-type.php' );
				acf_include( 'pro/admin/post-types/admin-ui-options-page.php' );
				acf_include( 'pro/admin/post-types/admin-ui-options-pages.php' );
			}

			$this->setup_local_json();

			parent::__construct();

			add_action( 'acf/init', array( $this, 'register_ui_options_pages' ), 6 );
			add_action( 'acf/include_options_pages', array( $this, 'include_json_options_pages' ) );
		}

		/**
		 * Registers the acf-ui-options-page custom post type with WordPress.
		 *
		 * @since 6.2
		 */
		public function register_post_type() {
			$cap = acf_get_setting( 'capability' );

			register_post_type(
				'acf-ui-options-page',
				array(
					'labels'          => array(
						'name'               => __( 'Options Pages', 'acf' ),
						'singular_name'      => __( 'Options Pages', 'acf' ),
						'add_new'            => __( 'Add New', 'acf' ),
						'add_new_item'       => __( 'Add New Options Page', 'acf' ),
						'edit_item'          => __( 'Edit Options Page', 'acf' ),
						'new_item'           => __( 'New Options Page', 'acf' ),
						'view_item'          => __( 'View Options Page', 'acf' ),
						'search_items'       => __( 'Search Options Pages', 'acf' ),
						'not_found'          => __( 'No Options Pages found', 'acf' ),
						'not_found_in_trash' => __( 'No Options Pages found in Trash', 'acf' ),
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
		 * Register activated options pages.
		 *
		 * @since 6.2
		 */
		public function register_ui_options_pages() {
			$child_pages = array();

			// Register parent pages first so that child pages can be registered properly.
			foreach ( $this->get_posts( array( 'active' => true ) ) as $options_page ) {
				$options_page = $this->get_options_page_args( $options_page );

				if ( empty( $options_page['parent_slug'] ) || 'none' === $options_page['parent_slug'] ) {
					$options_page['parent_slug'] = '';
					acf_add_options_page( $options_page );
				} else {
					$child_pages[] = $options_page;
				}
			}

			foreach ( $child_pages as $child_page ) {
				acf_add_options_sub_page( $child_page );
			}
		}

		/**
		 * Gets the default settings array for an ACF options page.
		 *
		 * @return array
		 */
		public function get_settings_array() {
			return array(
				// ACF internal settings.
				'ID'                     => 0,
				'key'                    => '',
				'title'                  => '',
				'active'                 => true,
				'menu_order'             => 0,
				// Basic settings.
				'page_title'             => '',
				'menu_slug'              => '',
				'parent_slug'            => '',
				'advanced_configuration' => false,
				// Visibility tab.
				'icon_url'               => '',
				'menu_title'             => '',
				'position'               => null,
				'redirect'               => false,
				'description'            => '',
				'menu_icon'              => array(),
				// Labels tab.
				'update_button'          => __( 'Update', 'acf' ),
				'updated_message'        => __( 'Options Updated', 'acf' ),
				// Permissions tab.
				'capability'             => 'edit_posts',
				'data_storage'           => 'options',
				'post_id'                => '',
				'autoload'               => false,
			);
		}

		/**
		 * Validates options page values before allowing save from the global $_POST object.
		 * Errors are added to the form using acf_add_internal_post_type_validation_error().
		 *
		 * @since 6.2
		 *
		 * @return boolean validity status
		 */
		public function ajax_validate_values() {
			if ( empty( $_POST['acf_ui_options_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				return false;
			}

			$to_validate = acf_sanitize_request_args( wp_unslash( $_POST['acf_ui_options_page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
			$post_id     = acf_request_arg( 'post_id' );
			$valid       = true;
			$menu_slug   = (string) $to_validate['menu_slug'];

			if ( preg_match( '/^[a-z0-9_-]*$/', $menu_slug ) !== 1 ) {
				$valid = false;
				acf_add_internal_post_type_validation_error( 'menu_slug', __( 'The menu slug must only contain lower case alphanumeric characters, underscores or dashes.', 'acf' ) );
			}

			// Check for duplicate menu_slug.
			$options_pages = acf_get_options_pages();
			$options_pages = is_array( $options_pages ) ? $options_pages : array();
			$duplicates    = array_filter(
				$options_pages,
				function ( $options_page ) use ( $post_id, $menu_slug ) {
					// Current post is not a duplicate.
					if ( isset( $options_page['ID'] ) && (int) $post_id === (int) $options_page['ID'] ) {
						return false;
					}

					// Menu slugs match, could be a duplicate.
					if ( $menu_slug === $options_page['menu_slug'] ) {
						// Unless the matching slug is a parent page redirecting to the child page.
						if ( isset( $options_page['_menu_slug'] ) && $options_page['_menu_slug'] !== $menu_slug ) {
							return false;
						}

						return true;
					}

					return false;
				}
			);

			if ( ! empty( $duplicates ) ) {
				$valid = false;
				acf_add_internal_post_type_validation_error(
					'menu_slug',
					__( 'This Menu Slug is already in use by another ACF Options Page.', 'acf' ),
					'acf-ui-options-page'
				);
			}

			return apply_filters( "acf/{$this->hook_name}/ajax_validate_values", $valid, $_POST['acf_ui_options_page'] ); // phpcs:ignore WordPress.Security -- Raw input send to hook for validation.
		}

		/**
		 * Updates the settings for ACF UI options pages.
		 *
		 * @since 6.2
		 *
		 * @param array $post The ACF post to update.
		 * @return array
		 */
		public function update_post( $post ) {
			if ( isset( $post['parent_slug'] ) && 'none' !== $post['parent_slug'] ) {
				$ui_options_pages = $this->get_posts();

				foreach ( $ui_options_pages as $options_page ) {
					if ( $options_page['menu_slug'] === $post['parent_slug'] ) {
						$post['_parent'] = $options_page['ID'];
						break;
					}
				}
			}

			return parent::update_post( $post );
		}

		/**
		 * Sets up the local JSON functionality for options pages.
		 *
		 * @since 6.2
		 *
		 * @param ACF_Local_JSON $local_json The ACF_Local_JSON object.
		 * @return void
		 */
		public function setup_local_json() {
			$local_json = acf_get_instance( 'ACF_Local_JSON' );

			// Event listeners.
			add_action( 'acf/update_ui_options_page', array( $local_json, 'update_internal_post_type' ) );
			add_action( 'acf/untrash_ui_options_page', array( $local_json, 'update_internal_post_type' ) );
			add_action( 'acf/trash_ui_options_page', array( $local_json, 'delete_internal_post_type' ) );
			add_action( 'acf/delete_ui_options_page', array( $local_json, 'delete_internal_post_type' ) );
		}

		/**
		 * Includes all local JSON options pages.
		 *
		 * @since 6.1
		 */
		public function include_json_options_pages() {
			$local_json = acf_get_instance( 'ACF_Local_JSON' );

			// Bail early if disabled.
			if ( ! $local_json->is_enabled() ) {
				return;
			}

			// Get load paths.
			$files = $local_json->scan_files( 'acf-ui-options-page' );
			foreach ( $files as $key => $file ) {
				$json               = json_decode( file_get_contents( $file ), true );
				$json['local']      = 'json';
				$json['local_file'] = $file;
				acf_add_local_internal_post_type( $json, 'acf-ui-options-page' );
			}
		}

		/**
		 * Returns a string that can be used to create an options page with PHP.
		 *
		 * @since 6.2
		 *
		 * @param array $post The main options page array.
		 * @return string
		 */
		public function export_post_as_php( $post = array() ) {
			$return = '';
			if ( empty( $post ) ) {
				return $return;
			}

			// Validate and prepare the post for export.
			$post = $this->validate_post( $post );
			$args = $this->get_options_page_args( $post );

			unset( $args['ID'] );

			$code = var_export( $args, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Used for PHP export.

			if ( ! $code ) {
				return $return;
			}

			$code    = $this->format_code_for_export( $code );
			$return .= "acf_add_options_page( {$code} );\r\n";

			return esc_textarea( $return );
		}

		/**
		 * This function returns whether the value was saved prior to the icon picker field or not.
		 *
		 * @since 6.3
		 *
		 * @param mixed $args The args for the icon field.
		 * @return boolean
		 */
		public function value_was_saved_prior_to_icon_picker_field( $args ) {
			if (
				! empty( $args['menu_icon'] ) &&
				is_array( $args['menu_icon'] ) &&
				! empty( $args['menu_icon']['type'] ) &&
				! empty( $args['menu_icon']['value'] )
			) {
				return false;
			}

			return true;
		}

		/**
		 * Parses ACF options page settings and returns an array of args
		 * to be handled by `acf_add_options_page()`.
		 *
		 * Omits settings that line up with the defaults to reduce the size
		 * of the array passed to `acf_add_options_page()`, which might be exported.
		 *
		 * @since 6.2
		 *
		 * @param array $post The main ACF options page settings array.
		 * @return array
		 */
		public function get_options_page_args( $post ) {
			$args     = array();
			$defaults = $this->get_settings_array();

			// UI-specific params that don't need to be passed in.
			$ui_specific = array(
				'key',
				'title',
				'active',
				'menu_order',
				'advanced_configuration',
				'data_storage',
			);

			foreach ( $post as $setting => $value ) {
				// Don't pass in UI specific or unknown settings.
				if ( in_array( $setting, $ui_specific, true ) || ! array_key_exists( $setting, $defaults ) ) {
					continue;
				}

				// Convert types.
				$default_type = gettype( $defaults[ $setting ] );
				if ( 'boolean' === $default_type ) {
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				}

				// Escape HTML.
				if ( in_array( $setting, array( 'page_title', 'menu_title' ), true ) ) {
					$value = esc_html( $value );
				}

				// A `parent_slug` value of "none" is only used in the UI.
				if ( 'parent_slug' === $setting && 'none' === $value ) {
					continue;
				}

				// UI does not default redirect to child to true, but code does.
				if ( 'redirect' === $setting && ! $value ) {
					$args[ $setting ] = $value;
					continue;
				}

				// Don't need to include if it's the same as a default.
				if ( $value === $defaults[ $setting ] ) {
					continue;
				}

				$args[ $setting ] = $value;
			}

			// Override the icon_url if the value was saved after the icon picker was added to ACF in 6.3.
			if ( ! $this->value_was_saved_prior_to_icon_picker_field( $args ) ) {
				if ( $args['menu_icon']['type'] === 'url' ) {
					$args['icon_url'] = $args['menu_icon']['value'];
				}
				if ( $args['menu_icon']['type'] === 'media_library' ) {
					$image_url        = wp_get_attachment_image_url($args['menu_icon']['value']);
					$args['icon_url'] = $image_url;
				}
				if ( $args['menu_icon']['type'] === 'dashicons' ) {
					$args['icon_url'] = $args['menu_icon']['value'];
				}
			}

			return apply_filters( 'acf/ui_options_page/registration_args', $args, $post );
		}
	}

}

acf_new_instance( 'ACF_UI_Options_Page' );
