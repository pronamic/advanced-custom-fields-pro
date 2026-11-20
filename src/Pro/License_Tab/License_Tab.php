<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * License Tab for the ACF Settings Page.
 *
 * Registers the License tab via the 'acf/settings_page/tabs' filter
 * and provides a custom AJAX handler for license activation and
 * deactivation.
 *
 * @package acf
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adds the License tab to the settings page.
 *
 * @since 6.8.2
 *
 * @param array[] $tabs Existing tab definitions.
 * @return array[]
 */
function acf_license_tab_register( $tabs ) {
	if ( function_exists( 'acf_pro_is_updates_page_visible' ) && ! acf_pro_is_updates_page_visible() ) {
		return $tabs;
	}

	$tabs[] = array(
		'key'           => 'license',
		'_builtin'      => true, // Internal: allows ACF's reserved "license" key.
		'label'         => __( 'License', 'acf' ),
		'order'         => 20,
		'ajax_callback' => 'acf_license_tab_ajax_response',
		'strings'       => array(
			'licenseKey'             => __( 'License Key', 'acf' ),
			'enterLicenseKey'        => __( 'Enter your license key', 'acf' ),
			'activateLicense'        => __( 'Activate License', 'acf' ),
			'deactivateLicense'      => __( 'Deactivate License', 'acf' ),
			'licenseDefinedInConfig' => apply_filters( 'acf/admin/license_key_constant_message', __( 'Your license key is defined in wp-config.php.', 'acf' ) ),
			'licenseStatus'          => __( 'License Status', 'acf' ),
			'subscriptionType'       => __( 'Subscription Type', 'acf' ),
			'licenseType'            => __( 'License Type', 'acf' ),
			'subscriptionExpires'    => __( 'Subscription Expires', 'acf' ),
			'subscriptionExpired'    => __( 'Subscription Expired', 'acf' ),
			'manageLicense'          => __( 'Manage License', 'acf' ),
			'renewSubscription'      => __( 'Renew Subscription', 'acf' ),
			'recheckLicense'         => __( 'Recheck License', 'acf' ),
			'recheckError'           => __( 'An error occurred while rechecking your license. Please try again.', 'acf' ),
			'viewPricingPurchase'    => __( 'View pricing & purchase', 'acf' ),
			'noPricingMessage'       => __( "Don't have an ACF license?", 'acf' ),
			'lifetime'               => __( 'Lifetime', 'acf' ),
			'activateError'          => __( 'An error occurred while activating your license. Please try again.', 'acf' ),
			'deactivateError'        => __( 'An error occurred while deactivating your license. Please try again.', 'acf' ),
		),
		'get_tab_data'  => 'acf_license_tab_get_license_data',
	);

	return $tabs;
}
add_filter( 'acf/settings_page/tabs', 'acf_license_tab_register' );

/**
 * Handles AJAX requests for the License tab.
 *
 * Supports 'activate', 'deactivate', and 'recheck' commands via the
 * 'license_action' request parameter.
 *
 * @since 6.8.2
 *
 * @return array|WP_Error The response data or WP_Error.
 */
function acf_license_tab_ajax_response() {
	$license_action = acf_maybe_get_POST( 'license_action', '' );

	switch ( $license_action ) {
		case 'activate':
			if ( ! acf_verify_nonce( 'activate_pro_license' ) ) {
				return new WP_Error( 'acf_invalid_nonce', __( 'Invalid nonce.', 'acf' ), array( 'status' => 403 ) );
			}

			$license_key = sanitize_text_field( acf_maybe_get_POST( 'license_key', '' ) );

			if ( empty( $license_key ) ) {
				return new WP_Error( 'acf_missing_license', __( 'Please enter a license key.', 'acf' ), array( 'status' => 400 ) );
			}

			$result = acf_pro_activate_license( $license_key, true );

			if ( is_wp_error( $result ) ) {
				return acf_license_tab_connection_error( $result );
			}

			$result['message'] = acf_esc_html( acf_pro_get_translated_connect_message( $result['message'] ?? '' ) );

			return array_merge(
				$result,
				array( 'license' => acf_license_tab_get_license_data() )
			);

		case 'deactivate':
			if ( ! acf_verify_nonce( 'deactivate_pro_license' ) ) {
				return new WP_Error( 'acf_invalid_nonce', __( 'Invalid nonce.', 'acf' ), array( 'status' => 403 ) );
			}

			$result = acf_pro_deactivate_license( true );

			// The local key is removed even when the server can't be reached, so return fresh license data with the error.
			if ( is_wp_error( $result ) ) {
				return array(
					'success' => false,
					'message' => acf_license_tab_connection_error( $result )->get_error_message(),
					'license' => acf_license_tab_get_license_data(),
				);
			}

			// No key was stored, so there was nothing to deactivate.
			if ( ! is_array( $result ) ) {
				return new WP_Error( 'acf_missing_license', __( 'No license key to deactivate.', 'acf' ), array( 'status' => 400 ) );
			}

			$result['message'] = acf_esc_html( acf_pro_get_translated_connect_message( $result['message'] ?? '' ) );

			return array_merge(
				$result,
				array( 'license' => acf_license_tab_get_license_data() )
			);

		case 'recheck':
			if ( ! acf_verify_nonce( 'recheck_pro_license' ) ) {
				return new WP_Error( 'acf_invalid_nonce', __( 'Invalid nonce.', 'acf' ), array( 'status' => 403 ) );
			}

			$result = acf_license_tab_recheck_license();

			if ( is_wp_error( $result ) ) {
				return acf_license_tab_connection_error( $result );
			}

			$result['message'] = acf_esc_html( $result['message'] ?? '' );

			return array_merge(
				$result,
				array( 'license' => acf_license_tab_get_license_data() )
			);

		default:
			return new WP_Error( 'acf_invalid_action', __( 'Invalid license action.', 'acf' ), array( 'status' => 400 ) );
	}
}

/**
 * Wraps an activation server connection error with context, matching acf_pro_display_wp_activation_error().
 *
 * @since 6.8.9
 *
 * @param WP_Error $wp_error The connection error.
 * @return WP_Error
 */
function acf_license_tab_connection_error( $wp_error ) {
	return new WP_Error(
		$wp_error->get_error_code(),
		acf_esc_html( __( 'Could not connect to the activation server', 'acf' ) . ' (' . $wp_error->get_error_message() . ').' ),
		$wp_error->get_error_data()
	);
}

/**
 * Rechecks the license. Defined licenses retry activation, others force a fresh license status check.
 *
 * @since 6.8.9
 *
 * @return array|WP_Error An array with a boolean success key and translated message key, or a WP_Error on connection failure.
 */
function acf_license_tab_recheck_license() {
	if ( ! defined( 'ACF_PRO_LICENSE' ) || empty( ACF_PRO_LICENSE ) || ! is_string( ACF_PRO_LICENSE ) ) {
		$license_status = acf_pro_get_license_status( true );

		// Report an inactive license (e.g. expired) as unsuccessful so the UI shows an error notice.
		return array(
			'success' => acf_pro_is_license_active( $license_status ),
			'message' => sprintf(
				/* translators: %s - the license status, e.g. "Active" or "Expired" */
				__( 'License checked. Status: %s.', 'acf' ),
				acf_license_tab_get_status_text( $license_status['status'] ?? '' )
			),
		);
	}

	$result = acf_pro_activate_license( ACF_PRO_LICENSE, true, true );

	// Activation was prevented by the 'acf/automatic_license_reactivation' filter.
	if ( false === $result ) {
		return array(
			'success' => false,
			'message' => __( 'Automatic license activation is disabled.', 'acf' ),
		);
	}

	// Keep the activation failure transient in sync so admin notices match, as in acf_pro_check_defined_license().
	if ( is_wp_error( $result ) ) {
		acf_pro_set_activation_failure_transient( __( 'An error occurred when connecting to activation server', 'acf' ) . ' <span class="description">(' . esc_html( $result->get_error_message() ) . ').</span>', ACF_PRO_LICENSE );
		return $result;
	}

	$result['message'] = acf_pro_get_translated_connect_message( $result['message'] ?? '' );

	if ( empty( $result['success'] ) ) {
		acf_pro_remove_license_status();
		acf_pro_set_activation_failure_transient( __( 'There was an issue activating your license key.', 'acf' ) . ' ' . $result['message'], ACF_PRO_LICENSE );
	}

	return $result;
}

/**
 * Returns the current license state data for the React UI.
 *
 * @since 6.8.2
 *
 * @return array
 */
function acf_license_tab_get_license_data() {
	$license_key = acf_pro_get_license_key();
	$is_defined  = defined( 'ACF_PRO_LICENSE' ) && ! empty( ACF_PRO_LICENSE ) && is_string( ACF_PRO_LICENSE );

	// Run a plugin update check so that
	// license status transients are refreshed,
	// and we can show an accurate license status.
	$info = acf_updates()->get_plugin_info( 'pro' );

	if ( is_array( $info ) && ! empty( $info['version'] ) ) {
		$version = acf_get_setting( 'version' );

		if ( version_compare( $info['version'], $version, '>' ) && $license_key ) {
			$basename = acf_get_setting( 'basename' );
			$update   = acf_updates()->get_plugin_update( $basename );

			if ( ! $update || $update['new_version'] !== $info['version'] ) {
				acf_updates()->refresh_plugins_transient();
			}
		}
	}

	// Read the status after the update check, which may have refreshed it.
	// Links to the old Updates page used "force-license-check" to skip the cached status.
	$force_license_check = ! empty( $_GET['force-license-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only skips the cache.
	$license_status      = acf_pro_get_license_status( $force_license_check );

	// Defined licenses can retry activation, others can refresh an expired or refunded status.
	$is_active   = acf_pro_is_license_active( $license_status );
	$can_recheck = $is_defined
		? ! $is_active
		: empty( $license_status['wpe'] ) && ( acf_pro_is_license_expired( $license_status ) || acf_pro_was_license_refunded( $license_status ) );

	return array(
		'activateNonce'    => wp_create_nonce( 'activate_pro_license' ),
		'deactivateNonce'  => wp_create_nonce( 'deactivate_pro_license' ),
		'recheckNonce'     => wp_create_nonce( 'recheck_pro_license' ),
		'key'              => $license_key ? str_repeat( '*', strlen( $license_key ) ) : '',
		'isActive'         => $is_active,
		'isDefined'        => $is_defined,
		'canRecheck'       => $can_recheck,
		'isWpe'            => ! empty( $license_status['wpe'] ),
		'status'           => $license_status['status'] ?? '',
		'statusText'       => acf_license_tab_get_status_text( $license_status['status'] ?? '' ),
		'name'             => $license_status['name'] ?? '',
		'isLifetime'       => ! empty( $license_status['lifetime'] ),
		'isExpired'        => acf_pro_is_license_expired( $license_status ),
		'isRefunded'       => acf_pro_was_license_refunded( $license_status ),
		'expiryDate'       => ! empty( $license_status['expiry'] ) && is_numeric( $license_status['expiry'] )
			? date_i18n( get_option( 'date_format', 'F j, Y' ), $license_status['expiry'] )
			: '',
		'manageLicenseUrl' => acf_add_url_utm_tags( acf_pro_get_manage_license_url( $license_status ), 'settings page', 'manage license button' ),
		'pricingUrl'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'license activations' ),
	);
}

/**
 * Returns the translated status text for a license status string.
 *
 * @since 6.8.2
 *
 * @param string $status The license status key.
 * @return string
 */
function acf_license_tab_get_status_text( $status ) {
	switch ( $status ) {
		case 'active':
			return _x( 'Active', 'license status', 'acf' );
		case 'expired':
			return _x( 'Expired', 'license status', 'acf' );
		case 'cancelled':
			return _x( 'Cancelled', 'license status', 'acf' );
		default:
			return _x( 'Inactive', 'license status', 'acf' );
	}
}
