<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Admin_Updates' ) ) :

	class ACF_Admin_Updates {

		/** @var array Data used in the view. */
		var $view = array();

		/**
		 * __construct
		 *
		 * Sets up the class functionality.
		 *
		 * @date    23/06/12
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function __construct() {

			// Add actions.
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		}

		/**
		 * display_wp_error
		 *
		 * Adds an admin notice using the provided WP_Error.
		 *
		 * @date    14/1/19
		 * @since   5.7.10
		 *
		 * @param   WP_Error $wp_error The error to display.
		 * @return  void
		 */
		function display_wp_error( $wp_error ) {

			// Only show one error on page.
			if ( acf_has_done( 'display_wp_error' ) ) {
				return;
			}

			// Create new notice.
			acf_new_admin_notice(
				array(
					'text' => __( '<b>Error</b>. Could not connect to update server', 'acf' ) . ' <span class="description">(' . esc_html( $wp_error->get_error_message() ) . ').</span>',
					'type' => 'error',
				)
			);
		}

		/**
		 * get_changelog_changes
		 *
		 * Finds the specific changes for a given version from the provided changelog snippet.
		 *
		 * @date    14/1/19
		 * @since   5.7.10
		 *
		 * @param   string $changelog The changelog text.
		 * @param   string $version The version to find.
		 * @return  string
		 */
		function get_changelog_changes( $changelog = '', $version = '' ) {

			// Explode changelog into sections.
			$bits = array_filter( explode( '<h4>', $changelog ) );

			// Loop over each version chunk.
			foreach ( $bits as $bit ) {

				// Find the version number for this chunk.
				$bit         = explode( '</h4>', $bit );
				$bit_version = trim( $bit[0] );
				$bit_text    = trim( $bit[1] );

				// Compare the chunk version number against param and return HTML.
				if ( acf_version_compare( $bit_version, '==', $version ) ) {
					return '<h4>' . esc_html( $bit_version ) . '</h4>' . acf_esc_html( $bit_text );
				}
			}

			// Return.
			return '';
		}

		/**
		 * admin_menu
		 *
		 * Adds the admin menu subpage.
		 *
		 * @date    28/09/13
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function admin_menu() {

			// Bail early if no show_admin.
			if ( ! acf_get_setting( 'show_admin' ) ) {
				return;
			}

			// Bail early if no show_updates.
			if ( ! acf_get_setting( 'show_updates' ) ) {
				return;
			}

			// Bail early if not a plugin (included in theme).
			if ( ! acf_is_plugin_active() ) {
				return;
			}

			// Add submenu.
			$page = add_submenu_page( 'edit.php?post_type=acf-field-group', __( 'Updates', 'acf' ), __( 'Updates', 'acf' ), acf_get_setting( 'capability' ), 'acf-settings-updates', array( $this, 'html' ) );

			// Add actions to page.
			add_action( "load-$page", array( $this, 'load' ) );
		}

		/**
		 * load
		 *
		 * Runs when loading the submenu page.
		 *
		 * @date    7/01/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function load() {
			// Check activate.
			if ( acf_verify_nonce( 'activate_pro_license' ) ) {
				acf_pro_activate_license( $_POST['acf_pro_license'] );

				// Check deactivate.
			} elseif ( acf_verify_nonce( 'deactivate_pro_license' ) ) {
				acf_pro_deactivate_license();
			}

			// vars
			$license    = acf_pro_get_license_key();
			$this->view = array(
				'license'            => $license,
				'active'             => $license ? 1 : 0,
				'current_version'    => acf_get_setting( 'version' ),
				'remote_version'     => '',
				'update_available'   => false,
				'changelog'          => '',
				'upgrade_notice'     => '',
				'is_defined_license' => defined( 'ACF_PRO_LICENSE' ) && ! empty( ACF_PRO_LICENSE ) && is_string( ACF_PRO_LICENSE ),
				'license_error'      => false,
			);

			// get plugin updates
			$force_check = ! empty( $_GET['force-check'] );
			$info        = acf_updates()->get_plugin_info( 'pro', $force_check );

			// Display error.
			if ( is_wp_error( $info ) ) {
				return $this->display_wp_error( $info );
			}

			// add info to view
			$this->view['remote_version'] = $info['version'];

			// add changelog if the remote version is '>' than the current version
			$version = acf_get_setting( 'version' );

			// check if remote version is higher than current version
			if ( version_compare( $info['version'], $version, '>' ) ) {

				// update view
				$this->view['update_available'] = true;
				$this->view['changelog']        = $this->get_changelog_changes( $info['changelog'], $info['version'] );
				$this->view['upgrade_notice']   = $this->get_changelog_changes( $info['upgrade_notice'], $info['version'] );

				// perform update checks if license is active
				$basename = acf_get_setting( 'basename' );
				$update   = acf_updates()->get_plugin_update( $basename );
				if ( $license ) {

					if ( isset( $update['license_valid'] ) && ! $update['license_valid'] ) {

						$this->view['license_error'] = true;
						acf_new_admin_notice(
							array(
								'text' => __( '<b>Error</b>. Your license for this site has expired or been deactivated. Please reactivate your ACF PRO license.', 'acf' ),
								'type' => 'error',
							)
						);

					} else {

						// display error if no package url
						// - possible if license key has been modified
						if ( $update && ! $update['package'] ) {
							$this->view['license_error'] = true;
							acf_new_admin_notice(
								array(
									'text' => __( '<b>Error</b>. Could not authenticate update package. Please check again or deactivate and reactivate your ACF PRO license.', 'acf' ),
									'type' => 'error',
								)
							);
						}
					}

					// refresh transient
					// - if no update exists in the transient
					// - or if the transient 'new_version' is stale
					if ( ! $update || $update['new_version'] !== $info['version'] ) {
						acf_updates()->refresh_plugins_transient();
					}
				}
			}
		}



		/**
		 * html
		 *
		 * Displays the submenu page's HTML.
		 *
		 * @date    7/01/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function html() {
			acf_get_view( dirname( __FILE__ ) . '/views/html-settings-updates.php', $this->view );
		}
	}

	// Initialize.
	acf_new_instance( 'ACF_Admin_Updates' );

endif; // class_exists check
