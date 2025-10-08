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
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'acf_admin_tools' ) ) :
	class acf_admin_tools {


		/**
		 * Contains an array of admin tool instances.
		 *
		 * @var array
		 */
		public $tools = array();

		/**
		 * The active tool.
		 *
		 * @var string
		 */
		public $active = '';

		/**
		 * __construct
		 *
		 * This function will setup the class functionality
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function __construct() {

			// actions
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );
		}

		/**
		 * register_tool
		 *
		 * This function will store a tool tool class
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   string $class
		 * @return  n/a
		 */
		function register_tool( $class ) {

			$instance                       = new $class();
			$this->tools[ $instance->name ] = $instance;
		}


		/**
		 * get_tool
		 *
		 * This function will return a tool tool class
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   string $name
		 * @return  n/a
		 */
		function get_tool( $name ) {

			return isset( $this->tools[ $name ] ) ? $this->tools[ $name ] : null;
		}


		/**
		 * get_tools
		 *
		 * This function will return an array of all tools
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  array
		 */
		function get_tools() {

			return $this->tools;
		}


		/**
		 * This function will add the ACF menu item to the WP admin
		 *
		 * @type    action (admin_menu)
		 * @date    28/09/13
		 * @since   5.0.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function admin_menu() {

			// bail early if no show_admin
			if ( ! acf_get_setting( 'show_admin' ) ) {
				return;
			}

			// add page
			$page = add_submenu_page( 'edit.php?post_type=acf-field-group', __( 'Tools', 'acf' ), __( 'Tools', 'acf' ), acf_get_setting( 'capability' ), 'acf-tools', array( $this, 'html' ) );

			// actions
			add_action( 'load-' . $page, array( $this, 'load' ) );
		}


		/**
		 * load
		 *
		 * description
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function load() {

			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );

			// disable filters (default to raw data)
			acf_disable_filters();

			// include tools
			$this->include_tools();

			// check submit
			$this->check_submit();

			// load acf scripts
			acf_enqueue_scripts();
		}

		/**
		 * Modifies the admin body class.
		 *
		 * @since 6.0.0
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			$classes .= ' acf-admin-page';
			return $classes;
		}

		/**
		 * include_tools
		 *
		 * description
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function include_tools() {

			// include
			acf_include( 'includes/admin/tools/class-acf-admin-tool.php' );
			acf_include( 'includes/admin/tools/class-acf-admin-tool-export.php' );
			acf_include( 'includes/admin/tools/class-acf-admin-tool-import.php' );

			// action
			do_action( 'acf/include_admin_tools' );
		}


		/**
		 * check_submit
		 *
		 * description
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function check_submit() {

			// loop
			foreach ( $this->get_tools() as $tool ) {

				// load
				$tool->load();

				// submit
				if ( acf_verify_nonce( $tool->name ) ) {
					$tool->submit();
				}
			}
		}


		/**
		 * html
		 *
		 * description
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function html() {

			// vars
			$screen = get_current_screen();
			$active = acf_maybe_get_GET( 'tool' );

			// view
			$view = array(
				'screen_id' => $screen->id,
				'active'    => $active,
			);

			// register metaboxes
			foreach ( $this->get_tools() as $tool ) {

				// check active
				if ( $active && $active !== $tool->name ) {
					continue;
				}

				// add metabox
				add_meta_box( 'acf-admin-tool-' . $tool->name, acf_esc_html( $tool->title ), array( $this, 'metabox_html' ), $screen->id, 'normal', 'default', array( 'tool' => $tool->name ) );
			}

			// view
			acf_get_view( 'tools/tools', $view );
		}


		/**
		 * Output the metabox HTML for specific tools
		 *
		 * @since 5.6.3
		 *
		 * @param mixed $post    The post this metabox is being displayed on, should be an empty string always for us on a tools page.
		 * @param array $metabox An array of the metabox attributes.
		 */
		public function metabox_html( $post, $metabox ) {
			$tool       = $this->get_tool( $metabox['args']['tool'] );
			$form_attrs = array( 'method' => 'post' );

			if ( $metabox['args']['tool'] === 'import' ) {
				$form_attrs['onsubmit'] = 'acf.disableForm(event)';
			}

			printf( '<form %s>', acf_esc_attrs( $form_attrs ) );
			$tool->html();
			acf_nonce_input( $tool->name );
			echo '</form>';
		}
	}

	// initialize
	acf()->admin_tools = new acf_admin_tools();
endif; // class_exists check


/**
 * alias of acf()->admin_tools->register_tool()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_register_admin_tool( $class ) {

	return acf()->admin_tools->register_tool( $class );
}


/**
 * This function will return the admin URL to the tools page
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_get_admin_tools_url() {

	return admin_url( 'edit.php?post_type=acf-field-group&page=acf-tools' );
}


/**
 * This function will return the admin URL to the tools page
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_get_admin_tool_url( $tool = '' ) {

	return acf_get_admin_tools_url() . '&tool=' . $tool;
}
