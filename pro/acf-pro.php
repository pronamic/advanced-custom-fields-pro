<?php

if ( ! class_exists( 'acf_pro' ) ) :

	class acf_pro {

		/*
		*  __construct
		*
		*
		*
		*  @type    function
		*  @date    23/06/12
		*  @since   5.0.0
		*
		*  @param   N/A
		*  @return  N/A
		*/

		function __construct() {

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

			// Add filters.
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		}

		/**
		 * Registers the `acf-ui-options-page` post type and initializes the UI.
		 *
		 * @since 6.2
		 *
		 * @return void
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
		 * @date  21/10/2015
		 * @since 5.2.3
		 */
		function include_field_types() {
			acf_include( 'pro/fields/class-acf-repeater-table.php' );
			acf_include( 'pro/fields/class-acf-field-repeater.php' );
			acf_include( 'pro/fields/class-acf-field-flexible-content.php' );
			acf_include( 'pro/fields/class-acf-field-gallery.php' );
			acf_include( 'pro/fields/class-acf-field-clone.php' );
		}

		/*
		*  include_location_rules
		*
		*  description
		*
		*  @type    function
		*  @date    10/6/17
		*  @since   5.6.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function include_location_rules() {

			acf_include( 'pro/locations/class-acf-location-block.php' );
			acf_include( 'pro/locations/class-acf-location-options-page.php' );
		}

		/**
		 * Registers styles and scripts used by ACF PRO.
		 *
		 * @since 5.0.0
		 *
		 * @return void
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
		}

		/*
		*  input_admin_enqueue_scripts
		*
		*  description
		*
		*  @type    function
		*  @date    4/11/2013
		*  @since   5.0.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function input_admin_enqueue_scripts() {

			wp_enqueue_script( 'acf-pro-input' );
			wp_enqueue_script( 'acf-pro-ui-options-page' );
			wp_enqueue_style( 'acf-pro-input' );
		}


		/*
		*  field_group_admin_enqueue_scripts
		*
		*  description
		*
		*  @type    function
		*  @date    4/11/2013
		*  @since   5.0.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function field_group_admin_enqueue_scripts() {

			wp_enqueue_script( 'acf-pro-field-group' );
			wp_enqueue_style( 'acf-pro-field-group' );
		}

		/**
		 * Checks for a license status error and renders it if necessary.
		 *
		 * @since 6.2.1
		 *
		 * @return void
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
			} elseif ( ! empty( $defined_license_errors ) ) {
				$error_msg = $defined_license_errors['error'];
			} elseif ( ! empty( $license_status['error_msg'] ) ) {
				$error_msg = $license_status['error_msg'];
			} else {
				// No errors to show.
				return;
			}

			if ( acf_is_updates_page_visible() && ! empty( $manage_url ) && 'acf-settings-updates' !== acf_request_arg( 'page' ) ) {
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
	}


	// instantiate
	new acf_pro();


	// end class
endif;
