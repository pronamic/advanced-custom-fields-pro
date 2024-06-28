<?php

if ( ! class_exists( 'acf_pro' ) ) :

	/**
	 * The main ACF PRO class.
	 */
	class acf_pro {

		/**
		 * Main ACF PRO constructor
		 *
		 * @since 5.0.0
		 */
		public function __construct() {
			// constants
			acf()->define( 'ACF_PRO', true );

			// update setting
			acf_update_setting( 'pro', true );
			acf_update_setting( 'name', __( 'Advanced Custom Fields PRO', 'acf' ) );

			// includes
			acf_include( 'pro/blocks.php' );
			acf_include( 'pro/options-page.php' );
			acf_include( 'pro/acf-ui-options-page-functions.php' );
			acf_include( 'pro/class-acf-updates.php' );
			acf_include( 'pro/updates.php' );

			if ( is_admin() ) {
				acf_include( 'pro/admin/admin-options-page.php' );
				acf_include( 'pro/admin/admin-updates.php' );
			}

			// actions
			add_action( 'init', array( $this, 'register_assets' ) );
			add_action( 'acf/init_internal_post_types', array( $this, 'register_ui_options_pages' ) );
			add_action( 'acf/include_fields', array( $this, 'include_options_pages' ) );
			add_action( 'acf/include_field_types', array( $this, 'include_field_types' ), 5 );
			add_action( 'acf/include_location_rules', array( $this, 'include_location_rules' ), 5 );
			add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'input_admin_enqueue_scripts' ) );
			add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'field_group_admin_enqueue_scripts' ) );
			add_action( 'acf/in_admin_header', array( $this, 'maybe_show_license_status_error' ) );
			add_action( 'acf/internal_post_type/current_screen', array( $this, 'invalid_license_redirect' ) );
			add_action( 'acf/internal_post_type_list/current_screen', array( $this, 'invalid_license_redirect_notice' ) );

			// Add filters.
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
			add_filter( 'acf/internal_post_type/admin_body_classes', array( $this, 'admin_body_classes' ) );
			add_filter( 'acf/internal_post_type_list/admin_body_classes', array( $this, 'admin_body_classes' ) );
		}

		/**
		 * Registers the `acf-ui-options-page` post type and initializes the UI.
		 *
		 * @since 6.2
		 */
		public function register_ui_options_pages() {
			if ( ! acf_get_setting( 'enable_options_pages_ui' ) ) {
				return;
			}

			acf_include( 'pro/post-types/acf-ui-options-page.php' );
		}

		/**
		 * Action to include JSON options pages.
		 *
		 * @since 6.2
		 */
		public function include_options_pages() {
			/**
			 * Fires during initialization. Used to add JSON options pages.
			 *
			 * @since 6.2
			 *
			 * @param int ACF_MAJOR_VERSION The major version of ACF.
			 */
			do_action( 'acf/include_options_pages', ACF_MAJOR_VERSION );
		}

		/**
		 * Includes any files necessary for field types.
		 *
		 * @since 5.2.3
		 *
		 * @return void
		 */
		public function include_field_types() {
			acf_include( 'pro/fields/class-acf-repeater-table.php' );
			acf_include( 'pro/fields/class-acf-field-repeater.php' );
			acf_include( 'pro/fields/class-acf-field-flexible-content.php' );
			acf_include( 'pro/fields/class-acf-field-gallery.php' );
			acf_include( 'pro/fields/class-acf-field-clone.php' );
		}

		/**
		 * Includes location rules for ACF PRO.
		 *
		 * @since 5.6.0
		 *
		 * @return void
		 */
		public function include_location_rules() {
			acf_include( 'pro/locations/class-acf-location-block.php' );
			acf_include( 'pro/locations/class-acf-location-options-page.php' );
		}

		/**
		 * Registers styles and scripts used by ACF PRO.
		 *
		 * @since 5.0.0
		 */
		public function register_assets() {
			$version = acf_get_setting( 'version' );
			$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register scripts.
			wp_register_script( 'acf-pro-input', acf_get_url( "assets/build/js/pro/acf-pro-input{$min}.js" ), array( 'acf-input' ), $version );
			wp_register_script( 'acf-pro-field-group', acf_get_url( "assets/build/js/pro/acf-pro-field-group{$min}.js" ), array( 'acf-field-group' ), $version );
			wp_register_script( 'acf-pro-ui-options-page', acf_get_url( "assets/build/js/pro/acf-pro-ui-options-page{$min}.js" ), array( 'acf-input' ), $version );

			// Register styles.
			wp_register_style( 'acf-pro-input', acf_get_url( 'assets/build/css/pro/acf-pro-input.css' ), array( 'acf-input' ), $version );
			wp_register_style( 'acf-pro-field-group', acf_get_url( 'assets/build/css/pro/acf-pro-field-group.css' ), array( 'acf-input' ), $version );

			if ( is_admin() ) {
				$to_localize = array(
					'isLicenseActive'  => acf_pro_is_license_active(),
					'isLicenseExpired' => acf_pro_is_license_expired(),
				);

				acf_localize_data( $to_localize );
			}
		}

		/**
		 * Enqueue the PRO admin screen scripts and styles
		 *
		 * @since 5.0.0
		 */
		public function input_admin_enqueue_scripts() {
			wp_enqueue_script( 'acf-pro-input' );
			wp_enqueue_script( 'acf-pro-ui-options-page' );
			wp_enqueue_style( 'acf-pro-input' );
		}

		/**
		 * Enqueue the PRO field group scripts and styles
		 *
		 * @since 5.0.0
		 */
		public function field_group_admin_enqueue_scripts() {
			wp_enqueue_script( 'acf-pro-field-group' );
			wp_enqueue_style( 'acf-pro-field-group' );
		}

		/**
		 * Checks for a license status error and renders it if necessary.
		 *
		 * @since 6.2.1
		 */
		public function maybe_show_license_status_error() {
			$license_status         = acf_pro_get_license_status();
			$defined_license_errors = acf_pro_get_activation_failure_transient();
			$manage_url             = false;

			if ( ! acf_pro_get_license_key( true ) && ! defined( 'ACF_PRO_LICENSE' ) ) {
				$error_msg  = __( 'Activate your license to enable access to updates, support &amp; PRO features.', 'acf' );
				$manage_url = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates#acf_pro_license' );
			} elseif ( acf_pro_is_license_expired( $license_status ) ) {
				$error_msg  = __( 'Your license has expired. Please renew to continue to have access to updates, support &amp; PRO features.', 'acf' );
				$manage_url = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates' );
			} elseif ( acf_pro_was_license_refunded( $license_status ) ) {
				$error_msg  = __( 'Your ACF PRO license is no longer active. Please renew to continue to have access to updates, support, & PRO features.', 'acf' );
				$manage_url = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates' );
			} elseif ( ! empty( $defined_license_errors ) ) {
				$error_msg = $defined_license_errors['error'];
			} elseif ( ! empty( $license_status['error_msg'] ) ) {
				$error_msg = $license_status['error_msg'];
			} else {
				// No errors to show.
				return;
			}

			if ( acf_pro_is_updates_page_visible() && ! empty( $manage_url ) && 'acf-settings-updates' !== acf_request_arg( 'page' ) ) {
				$manage_link = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( $manage_url ),
					__( 'Manage License', 'acf' )
				);

				$error_msg .= ' ' . $manage_link;
			}

			acf_add_admin_notice( $error_msg, 'warning', false );
		}

		/**
		 * Redirects back to the list table when editing an unauthorized item with an invalid license.
		 *
		 * @since 6.2.8
		 *
		 * @param string $post_type The post type being edited.
		 * @return void
		 */
		public function invalid_license_redirect( string $post_type ) {
			if ( ! in_array( $post_type, array( 'acf-field-group', 'acf-ui-options-page' ), true ) ) {
				return;
			}

			// Active licenses have no restrictions.
			if ( acf_pro_is_license_active() ) {
				return;
			}

			// The post being edited.
			$current_post = (int) acf_request_arg( 'post', 0 );

			if ( 'acf-ui-options-page' === $post_type ) {
				// Only existing options pages can be edited with an expired license.
				if ( $current_post && acf_pro_is_license_expired() ) {
					return;
				}
			} elseif ( 'acf-field-group' === $post_type ) {
				// Expired licenses can edit new/existing field groups regardless of block locations.
				if ( acf_pro_is_license_expired() ) {
					return;
				}

				// No block locations, should still be able to edit.
				if ( ! acf_field_group_has_location_type( $current_post, 'block' ) ) {
					return;
				}
			}

			// Redirect back to field groups list table.
			wp_safe_redirect( admin_url( 'edit.php?acf_invalid_license=true&post_type=' . $post_type ) );
			exit;
		}

		/**
		 * Adds a notice if a user has attempted to edit an ACF item without a valid license.
		 *
		 * @since 6.2.8
		 *
		 * @param string $post_type The post type being edited.
		 * @return void
		 */
		public function invalid_license_redirect_notice( string $post_type ) {
			if ( ! acf_request_arg( 'acf_invalid_license', false ) ) {
				return;
			}

			if ( 'acf-field-group' === $post_type ) {
				acf_add_admin_notice( __( 'A valid license is required to edit field groups assigned to a block.', 'acf' ), 'error' );
			} elseif ( 'acf-ui-options-page' === $post_type ) {
				acf_add_admin_notice( __( 'A valid license is required to edit options pages.', 'acf' ), 'error' );
			}
		}

		/**
		 * Filters the $where clause allowing for custom WP_Query args.
		 *
		 * @since 6.2
		 *
		 * @param  string   $where    The WHERE clause.
		 * @param  WP_Query $wp_query The query object.
		 * @return string
		 */
		public function posts_where( $where, $wp_query ) {
			global $wpdb;

			$options_page_key = $wp_query->get( 'acf_ui_options_page_key' );

			// Add custom "acf_options_page_key" arg.
			if ( $options_page_key ) {
				$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_name = %s", $options_page_key );
			}

			return $where;
		}

		/**
		 * Adds admin body classes to ACF post types and post type list pages.
		 *
		 * @since 6.2.5
		 *
		 * @param string $classes The existing body classes.
		 * @return string
		 */
		public function admin_body_classes( $classes ) {
			if ( acf_pro_is_license_expired() ) {
				$classes .= ' acf-pro-expired-license';
			} elseif ( ! acf_pro_is_license_active() ) {
				$classes .= ' acf-pro-inactive-license';
			}

			return $classes;
		}
	}


	// instantiate
	new acf_pro();


	// end class
endif;
