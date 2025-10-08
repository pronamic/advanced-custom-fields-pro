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

namespace ACF;

use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Updater' ) ) {

	/**
	 * class for handling API services.
	 */
	class Updater {

		/**
		 * The Updater version
		 *
		 * @var string
		 */
		public $version = '3.0';

		/**
		 * The array of registered plugins
		 *
		 * @var array
		 */
		public $plugins = array();

		/**
		 * Counts the number of plugin update checks
		 *
		 * @var integer
		 */
		public $checked = 0;

		/**
		 * Sets up the class functionality.
		 *
		 * @since   5.0.0
		 */
		public function __construct() {

			// disable showing PRO updates if show updates is hidden.
			if ( acf_is_pro() && ! acf_pro_is_updates_page_visible() ) {
				return;
			}

			// append update information to transient on both save and get.
			add_filter( 'site_transient_update_plugins', array( $this, 'modify_plugins_transient' ), 10, 1 );

			// clear ACF transient when updates complete.
			add_filter( 'upgrader_process_complete', array( $this, 'clear_transients_on_upgrade' ), 5, 2 );

			// modify plugin data visible in the 'View details' popup.
			add_filter( 'plugins_api', array( $this, 'modify_plugin_details' ), 10, 3 );
		}

		/**
		 * Clears ACF plugin update transients when ACF is updated.
		 *
		 * This method is hooked to the 'upgrader_process_complete' action and will
		 * delete the 'acf_plugin_updates' transient when the ACF plugin is updated,
		 * ensuring fresh update data is fetched on the next check.
		 *
		 * @since 6.5.1
		 *
		 * @param WP_Upgrader $upgrader_object The upgrader instance.
		 * @param array       $options         Array of update details including:
		 *                                     - 'action' (string) The action performed (e.g., 'update').
		 *                                     - 'type' (string) The type of update (e.g., 'plugin').
		 *                                     - 'plugins' (array) Array of plugin basenames that were updated.
		 */
		public function clear_transients_on_upgrade( $upgrader_object, $options ) {
			// Check if this was a plugin update
			if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) {
				// Check if we were one of the updated plugins
				if ( isset( $options['plugins'] ) ) {
					$acf_basename = acf_get_setting( 'basename' );
					if ( in_array( $acf_basename, $options['plugins'], true ) ) {
						$plugin    = $this->get_plugin_by( 'basename', $acf_basename );
						$plugin_id = $plugin ? $plugin['id'] : false;
						$this->refresh_plugins_transient( $plugin_id );
					}
				}
			}
		}

		/**
		 * Registeres a plugin for updates.
		 *
		 * @since   5.5.10
		 *
		 * @param   array $plugin The plugin array.
		 * @return  void
		 */
		public function add_plugin( $plugin ) {

			// validate.
			$plugin = wp_parse_args(
				$plugin,
				array(
					'id'       => '',
					'key'      => '',
					'slug'     => '',
					'basename' => '',
					'version'  => '',
				)
			);

			// Check if is_plugin_active() function exists. This is required on the front end of the
			// site, since it is in a file that is normally only loaded in the admin.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// add if is active plugin (not included in theme).
			if ( is_plugin_active( $plugin['basename'] ) ) {
				$this->plugins[ $plugin['basename'] ] = $plugin;
			}
		}

		/**
		 * Returns a registered plugin for the give key and value.
		 *
		 * @since   5.7.2
		 *
		 * @param   string $key   The array key to compare.
		 * @param   string $value The value to compare against.
		 * @return  array|false
		 */
		public function get_plugin_by( $key = '', $value = null ) {
			foreach ( $this->plugins as $plugin ) {
				if ( $plugin[ $key ] === $value ) {
					return $plugin;
				}
			}
			return false;
		}

		/**
		 * Makes a request to the ACF connect server.
		 *
		 * @since   5.5.10
		 *
		 * @param   string $endpoint The API endpoint.
		 * @param   array  $body     The body to post.
		 * @return  (array|string|WP_Error)
		 */
		public function request( $endpoint = '', $body = null ) {

			$site_url = acf_get_home_url();
			if ( empty( $site_url ) || ! is_string( $site_url ) ) {
				$site_url = '';
			}

			$headers = array(
				'X-ACF-Version' => ACF_VERSION,
				'X-ACF-URL'     => $site_url,
			);

			// Add update channel header if defined.
			if ( defined( 'ACF_UPDATE_CHANNEL' ) && ACF_UPDATE_CHANNEL ) {
				$headers['X-ACF-Update-Channel'] = ACF_UPDATE_CHANNEL;
			}

			// Add plugin override header if defined.
			if ( defined( 'ACF_RELEASE_ACCESS_KEY' ) && ACF_RELEASE_ACCESS_KEY ) {
				$headers['X-ACF-Release-Access-Key'] = ACF_RELEASE_ACCESS_KEY;
			}

			$url = "https://connect.advancedcustomfields.com/$endpoint";

			// Staging environment.
			if ( defined( 'ACF_DEV_API' ) && ACF_DEV_API ) {
				$url = trailingslashit( ACF_DEV_API ) . $endpoint;
				acf_log( $url, $body );
			}

			// If we're posting an ACF license key, set it as the header.
			if ( is_array( $body ) && isset( $body['acf_license'] ) ) {
				$headers['X-ACF-License'] = $body['acf_license'];
			}

			// Determine URL.
			if ( acf_is_pro() ) {
				if ( empty( $headers['X-ACF-License'] ) ) {
					$license_key = acf_pro_get_license_key();
					if ( empty( $license_key ) || ! is_string( $license_key ) ) {
						$license_key = '';
					}
					$headers['X-ACF-License'] = $license_key;
				}
				$headers['X-ACF-Plugin'] = 'pro';
			} else {
				$headers['X-ACF-Plugin'] = 'acf';
			}

			// Make request.
			$raw_response = wp_remote_post(
				$url,
				array(
					'timeout' => 20,
					'body'    => $body,
					'headers' => $headers,
				)
			);

			// Handle response error.
			if ( is_wp_error( $raw_response ) ) {
				return $raw_response;

				// Handle http error.
			} elseif ( wp_remote_retrieve_response_code( $raw_response ) !== 200 ) {
				return new WP_Error( 'server_error', wp_remote_retrieve_response_message( $raw_response ) );
			}

			// Decode JSON response.
			$json = json_decode( wp_remote_retrieve_body( $raw_response ), true );

			// Allow non json value.
			if ( $json === null ) {
				return wp_remote_retrieve_body( $raw_response );
			}

			return $json;
		}

		/**
		 * Returns update information for the given plugin id.
		 *
		 * @since   5.5.10
		 *
		 * @param   string  $id          The plugin id such as 'pro'.
		 * @param   boolean $force_check Bypasses cached result. Defaults to false.
		 * @return  array|WP_Error
		 */
		public function get_plugin_info( $id = '', $force_check = false ) {
			$transient_name = 'acf_plugin_info_' . $id;

			// check cache but allow for $force_check override.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );
				if ( $transient !== false ) {
					return $transient;
				}
			}

			$response = $this->request( 'v2/plugins/get-info?p=' . $id );

			// convert string (misc error) to WP_Error object.
			if ( is_string( $response ) ) {
				$response = new WP_Error( 'server_error', esc_html( $response ) );
			}

			// allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS );

			// update transient.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		/**
		 * Returns specific data from the 'update-check' response.
		 *
		 * @since   5.7.2
		 *
		 * @param string  $basename    The plugin basename.
		 * @param boolean $force_check Bypasses cached result. Defaults to false.
		 * @return array|false
		 */
		public function get_plugin_update( $basename = '', $force_check = false ) {
			// get updates.
			$updates = $this->get_plugin_updates( $force_check );

			// check for and return update.
			if ( is_array( $updates ) && isset( $updates['plugins'][ $basename ] ) ) {
				return $updates['plugins'][ $basename ];
			}

			return false;
		}

		/**
		 * Checks if an update is available, but can't be updated to.
		 *
		 * @since   6.2.1
		 *
		 * @param string  $basename    The plugin basename.
		 * @param boolean $force_check Bypasses cached result. Defaults to false.
		 * @return array|false
		 */
		public function get_no_update( $basename = '', $force_check = false ) {
			// get updates.
			$updates = $this->get_plugin_updates( $force_check );

			// check for and return update.
			if ( is_array( $updates ) && isset( $updates['no_update'][ $basename ] ) ) {
				return $updates['no_update'][ $basename ];
			}

			return false;
		}


		/**
		 * Checks for plugin updates.
		 *
		 * @since   5.6.9
		 * @since   5.7.2 Added 'checked' comparison
		 *
		 * @param   boolean $force_check Bypasses cached result. Defaults to false.
		 * @return  array|WP_Error.
		 */
		public function get_plugin_updates( $force_check = false ) {
			$transient_name = 'acf_plugin_updates';

			// Don't call our site if no plugins have registered updates.
			if ( empty( $this->plugins ) ) {
				return array();
			}

			// Construct array of 'checked' plugins.
			// Sort by key to avoid detecting change due to "include order".
			$checked = array();
			foreach ( $this->plugins as $basename => $plugin ) {
				$checked[ $basename ] = $plugin['version'];
			}
			ksort( $checked );

			// $force_check prevents transient lookup.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );

				// If cached response was found, compare $transient['checked'] against $checked and ignore if they don't match (plugins/versions have changed).
				if ( is_array( $transient ) ) {
					$transient_checked = isset( $transient['checked'] ) ? $transient['checked'] : array();
					if ( wp_json_encode( $checked ) !== wp_json_encode( $transient_checked ) ) {
						$transient = false;
					}
				}
				if ( $transient !== false ) {
					return $transient;
				}
			}

			$post = array(
				'plugins' => wp_json_encode( $this->plugins ),
				'wp'      => wp_json_encode(
					array(
						'wp_name'      => get_bloginfo( 'name' ),
						'wp_url'       => acf_get_home_url(),
						'wp_version'   => get_bloginfo( 'version' ),
						'wp_language'  => get_bloginfo( 'language' ),
						'wp_timezone'  => get_option( 'timezone_string' ),
						'wp_multisite' => (int) is_multisite(),
						'php_version'  => PHP_VERSION,
					)
				),
				'acf'     => wp_json_encode(
					array(
						'acf_version' => get_option( 'acf_version' ),
						'acf_pro'     => acf_is_pro(),
						'block_count' => function_exists( 'acf_pro_get_registered_block_count' ) ? acf_pro_get_registered_block_count() : 0,
					)
				),
			);

			// Check update from connect.
			$response = $this->request( 'v2/plugins/update-check', $post );

			// Append checked reference.
			if ( is_array( $response ) ) {
				$response['checked'] = $checked;

				if ( isset( $response['license_status'] ) && function_exists( 'acf_pro_update_license_status' ) ) {
					acf_pro_update_license_status( $response['license_status'] );
					unset( $response['license_status'] );
				}
			}

			// Allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response );

			// Update transient and return.
			set_transient( $transient_name, $response, $expiration );
			return $response;
		}

		/**
		 * This function safely gets the expiration value from a response.
		 *
		 * @since   5.6.9
		 *
		 * @param   mixed   $response The response from the server. Default false.
		 * @param   integer $min      The minimum expiration limit. Default 3 hours.
		 * @param   integer $max      The maximum expiration limit. Default 7 days.
		 * @return  integer
		 */
		public function get_expiration( $response = false, $min = 10800, $max = 604800 ) {
			$expiration = 0;

			// Check possible error conditions.
			if ( is_wp_error( $response ) || is_string( $response ) ) {
				return 15 * MINUTE_IN_SECONDS;
			}

			// Use the server requested expiration if present.
			if ( is_array( $response ) && isset( $response['expiration'] ) ) {
				$expiration = (int) $response['expiration'];
			}

			// Use the minimum if neither check matches, or ensure the server expiration isn't lower than our minimum.
			if ( $expiration < $min ) {
				return $min;
			}

			// Ensure the server expiration isn't higher than our max.
			if ( $expiration > $max ) {
				return $max;
			}

			return $expiration;
		}

		/**
		 * Deletes cached ACF plugin update transients and allows a fresh lookup.
		 *
		 * @since   5.5.10
		 *
		 * @param   string|false $id Optional. The plugin ID to clear specific plugin info transient.
		 *                           If provided, will delete the 'acf_plugin_info_{id}' transient.
		 *                           Defaults to false.
		 */
		public function refresh_plugins_transient( $id = false ) {
			delete_transient( 'acf_plugin_updates' );
			if ( ! empty( $id ) && is_string( $id ) ) {
				delete_transient( 'acf_plugin_info_' . $id );
			}
		}

		/**
		 * Called when WP updates the 'update_plugins' site transient. Used to inject ACF plugin update info.
		 *
		 * @since   5.0.0
		 *
		 * @param object $transient The current transient value.
		 * @return object $transient The modified transient value.
		 */
		public function modify_plugins_transient( $transient ) {
			// Bail early if no response (error).
			if ( ! isset( $transient->response ) ) {
				return $transient;
			}

			// Ensure no_update is set for back compat.
			if ( ! isset( $transient->no_update ) ) {
				$transient->no_update = array();
			}

			// Force-check (only once).
			$force_check = ( $this->checked == 0 ) ? ! empty( $_GET['force-check'] ) : false; // phpcs:ignore -- False positive, value not used.

			// Fetch updates (this filter is called multiple times during a single page load).
			$updates = $this->get_plugin_updates( $force_check );

			// Append ACF pro plugins.
			if ( is_array( $updates ) ) {
				if ( ! empty( $updates['plugins'] ) ) {
					foreach ( $updates['plugins'] as $basename => $update ) {
						$transient->response[ $basename ] = (object) $update;
					}
				}
				if ( ! empty( $updates['no_update'] ) ) {
					foreach ( $updates['no_update'] as $basename => $update ) {
						$transient->no_update[ $basename ] = (object) $update;
					}
				}
			}

			++$this->checked;

			return $transient;
		}

		/**
		 * Returns the plugin data visible in the 'View details' popup
		 *
		 * @since   5.0.0
		 *
		 * @param   object $result The current result of plugin data.
		 * @param   string $action The action being performed.
		 * @param   object $args   Data about the plugin being retried.
		 * @return  $result
		 */
		public function modify_plugin_details( $result, $action = null, $args = null ) {

			$plugin = false;

			// Only for 'plugin_information' action.
			if ( $action !== 'plugin_information' ) {
				return $result;
			}

			// Find plugin via slug.
			$plugin = $this->get_plugin_by( 'slug', $args->slug );
			if ( ! $plugin ) {
				return $result;
			}

			// Get data from connect or cache.
			$response = $this->get_plugin_info( $plugin['id'] );

			// Bail early if no response.
			if ( ! is_array( $response ) ) {
				return $result;
			}

			// Remove tags (different context).
			unset( $response['tags'] );

			// Convert to object.
			$response = (object) $response;

			$sections = array(
				'description'    => '',
				'installation'   => '',
				'changelog'      => '',
				'upgrade_notice' => '',
			);
			foreach ( $sections as $k => $v ) {
				$sections[ $k ] = $response->$k;
			}
			$response->sections = $sections;

			return $response;
		}
	}

}
