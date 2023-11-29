<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ACF_Admin_Options_Preview' ) ) :
	class ACF_Admin_Options_Preview {

		/**
		 * Constructor.
		 *
		 * @since   6.2.2
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		}

		/**
		 * Adds the Options Pages menu item to the admin menu.
		 *
		 * @since   6.2.2
		 */
		public function admin_menu() {
			if ( ! acf_get_setting( 'show_admin' ) ) {
				return;
			}
			$page = add_submenu_page( 'edit.php?post_type=acf-field-group', __( 'Options Pages', 'acf' ), __( 'Options Pages', 'acf' ), acf_get_setting( 'capability' ), 'acf_options_preview', array( $this, 'render' ) );
			add_action( 'load-' . $page, array( $this, 'load' ) );
		}

		/**
		 * Load the body class and scripts.
		 *
		 * @since 6.2.2
		 */
		public function load() {
			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
			acf_enqueue_scripts();
		}

		/**
		 * Modifies the admin body class.
		 *
		 * @since 6.2.2
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			$classes .= ' acf-admin-page acf-internal-post-type acf-options-preview acf-no-options-pages';
			return $classes;
		}

		/**
		 * The render for the options page preview view.
		 *
		 * @since 6.2.2
		 */
		public function render() {
			$screen = get_current_screen();
			$view   = array( 'screen_id' => $screen->id );
			acf_get_view( 'options-page-preview', $view );
		}
	}

	new ACF_Admin_Options_Preview();
endif;
