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
	exit;
}

// Include updater.
acf_include( 'includes/Updater/Updater.php' );

if ( ! class_exists( 'ACF_Updates' ) ) {
	/**
	 * The main function responsible for returning the acf_updates singleton.
	 * Use this function like you would a global variable, except without needing to declare the global.
	 *
	 * Example: <?php $acf_updates = acf_updates(); ?>
	 *
	 * @since   5.5.12
	 *
	 * @return ACF\Updater The singleton instance of Updater.
	 */
	function acf_updates() {
		global $acf_updates;
		if ( ! isset( $acf_updates ) ) {
			$acf_updates = new ACF\Updater();
		}
		return $acf_updates;
	}

	/**
	 * Alias of acf_updates()->add_plugin().
	 *
	 * @since   5.5.10
	 *
	 * @param   array $plugin Plugin data array.
	 */
	function acf_register_plugin_update( $plugin ) {
		acf_updates()->add_plugin( $plugin );
	}

	/**
	 * Register a dummy ACF_Updates class for back compat.
	 */
	class ACF_Updates {} //phpcs:ignore -- Back compat.
}

/**
 * Registers updates for the free version of ACF hosted on connect.
 *
 * @return void
 */
function acf_register_free_updates() {
	// If we're ACF free, register the updater.
	if ( function_exists( 'acf_is_pro' ) && ! acf_is_pro() ) {
		acf_register_plugin_update(
			array(
				'id'       => 'acf',
				'slug'     => acf_get_setting( 'slug' ),
				'basename' => acf_get_setting( 'basename' ),
				'version'  => acf_get_setting( 'version' ),
			)
		);
	}
}
add_action( 'acf/init', 'acf_register_free_updates' );

/**
 * Filters the "Update Source" param in the ACF site health.
 *
 * @since 6.3.11.1
 *
 * @param string $update_source The original update source.
 * @return string
 */
function acf_direct_update_source( $update_source ) {
	return __( 'ACF Direct', 'acf' );
}
add_filter( 'acf/site_health/update_source', 'acf_direct_update_source' );

/**
 * Unsets ACF from reporting back to the WP.org API.
 *
 * @param array  $args An array of HTTP request arguments.
 * @param string $url  The request URL.
 * @return array|mixed
 */
function acf_unset_plugin_from_org_reporting( $args, $url ) {
	// Bail if not a plugins request.
	if ( empty( $args['body']['plugins'] ) ) {
		return $args;
	}

	// Bail if not a request to the wp.org API.
	$parsed_url = wp_parse_url( $url );
	if ( empty( $parsed_url['host'] ) || 'api.wordpress.org' !== $parsed_url['host'] ) {
		return $args;
	}

	$plugins = json_decode( $args['body']['plugins'], true );
	if ( empty( $plugins ) ) {
		return $args;
	}

	// Remove ACF from reporting.
	if ( ! empty( $plugins['plugins'][ ACF_BASENAME ] ) ) {
		unset( $plugins['plugins'][ ACF_BASENAME ] );
	}

	if ( ! empty( $plugins['active'] ) && is_array( $plugins['active'] ) ) {
		$is_active = array_search( ACF_BASENAME, $plugins['active'], true );
		if ( $is_active !== false ) {
			unset( $plugins['active'][ $is_active ] );
			$plugins['active'] = array_values( $plugins['active'] );
		}
	}

	// Add the plugins list (minus ACF) back to $args.
	$args['body']['plugins'] = wp_json_encode( $plugins );

	return $args;
}
add_filter( 'http_request_args', 'acf_unset_plugin_from_org_reporting', 10, 2 );
