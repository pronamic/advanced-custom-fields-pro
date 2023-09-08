<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'acf_pro_updates' ) ) :

	class acf_pro_updates {


		/**
		 *  __construct
		 *
		 *  Initialize filters, action, variables and includes
		 *
		 *  @type    function
		 *  @date    23/06/12
		 *  @since   5.0.0
		 */

		function __construct() {

			// actions
			add_action( 'init', array( $this, 'init' ), 20 );

		}


		/**
		 *  init
		 *
		 *  description
		 *
		 *  @type    function
		 *  @date    10/4/17
		 *  @since   5.5.10
		 */

		function init() {

			// bail early if no show_updates.
			if ( ! acf_get_setting( 'show_updates' ) ) {
				return;
			}

			// bail early if not a plugin (included in theme).
			if ( ! acf_is_plugin_active() ) {
				return;
			}

			// register update
			acf_register_plugin_update(
				array(
					'id'       => 'pro',
					'key'      => acf_pro_get_license_key(),
					'slug'     => acf_get_setting( 'slug' ),
					'basename' => acf_get_setting( 'basename' ),
					'version'  => acf_get_setting( 'version' ),
				)
			);

			add_action( 'admin_init', 'acf_pro_check_defined_license', 20 );
			add_action( 'current_screen', 'acf_pro_display_activation_error', 30 );

			// admin
			if ( is_admin() ) {

				add_action( 'in_plugin_update_message-' . acf_get_setting( 'basename' ), array( $this, 'modify_plugin_update_message' ), 10, 2 );

			}

		}


		/*
		*  modify_plugin_update_message
		*
		*  Displays an update message for plugin list screens.
		*
		*  @type    function
		*  @date    14/06/2016
		*  @since   5.3.8
		*
		*  @param   $message (string)
		*  @param   $plugin_data (array)
		*  @param   $r (object)
		*  @return  $message
		*/

		function modify_plugin_update_message( $plugin_data, $response ) {

			// bail early if has key
			if ( acf_pro_get_license_key() ) {
				return;
			}

			// display message
			echo '<br />' . sprintf( __( 'To enable updates, please enter your license key on the <a href="%1$s">Updates</a> page. If you don\'t have a licence key, please see <a href="%2$s" target="_blank">details & pricing</a>.', 'acf' ), admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates' ), acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'updates' ) );

		}

	}


	// initialize
	new acf_pro_updates();

endif; // class_exists check

/**
 * Check if a license is defined in wp-config.php and requires activation.
 * Also checks if the license key has been changed and reactivates.
 *
 * @date 29/09/2021
 * @since 5.11.0
 */
function acf_pro_check_defined_license() {

	// Bail early if the license is not defined in wp-config.
	if ( ! defined( 'ACF_PRO_LICENSE' ) || empty( ACF_PRO_LICENSE ) || ! is_string( ACF_PRO_LICENSE ) ) {
		return;
	}

	// Bail early if no show_admin.
	if ( ! acf_get_setting( 'show_admin' ) ) {
		return;
	}

	// Check if we've been asked to clear the transient to retry activation.
	if ( acf_verify_nonce( 'acf_delete_activation_transient' ) || ( isset( $_REQUEST['acf_retry_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['acf_retry_nonce'] ) ), 'acf_retry_activation' ) ) ) {
		delete_transient( 'acf_activation_error' );
	} else {
		// If we've failed activation recently, check if the key has been changed, otherwise return.
		$activation_data = acf_pro_get_activation_failure_transient();
		if ( $activation_data && $activation_data['license'] === ACF_PRO_LICENSE ) {
			return;
		}
	}

	// If we're already activated, check if the defined license key has changed.
	$license = acf_pro_get_license();
	if ( $license ) {

		// Check the saved license key against the defined key.
		if ( acf_pro_get_license_key() !== ACF_PRO_LICENSE ) {

			// Deactivate if the key has changed.
			$deactivation_response = acf_pro_deactivate_license( true );

			// A connection error occurred while trying to deactivate.
			if ( is_wp_error( $deactivation_response ) ) {

				return acf_pro_set_activation_failure_transient( __( '<b>ACF Activation Error</b>. Your defined license key has changed, but an error occurred when connecting to activation server', 'acf' ) . ' <span class="description">(' . esc_html( $deactivation_response->get_error_message() ) . ').</span>', ACF_PRO_LICENSE );

				// A deactivation error occurred. Display the message returned by our API.
			} elseif ( ! $deactivation_response['success'] ) {

				return acf_pro_set_activation_failure_transient( __( '<b>ACF Activation Error</b>. Your defined license key has changed, but an error occurred when deactivating your old licence', 'acf' ) . ' <span class="description">(' . $deactivation_response['message'] . ').</span>', ACF_PRO_LICENSE );

			}
		} else {

			// Check if the licence has been marked as invalid during the update check.
			$basename = acf_get_setting( 'basename' );
			$update   = acf_updates()->get_plugin_update( $basename );
			if ( isset( $update['license_valid'] ) && ! $update['license_valid'] ) {

				// Our site is not activated, so remove the license.
				acf_pro_update_license( '' );
			} else {

				// License key hasn't changed, we are activated and licence is still valid, return.
				return;
			}
		}
	}

	// Activate the defined key license.
	$activation_response = acf_pro_activate_license( ACF_PRO_LICENSE, true );

	$error_text = false;

	// A connection error occurred during activation
	if ( is_wp_error( $activation_response ) ) {

		$error_text = __( '<b>ACF Activation Error</b>. An error occurred when connecting to activation server', 'acf' ) . ' <span class="description">(' . esc_html( $activation_response->get_error_message() ) . ').</span>';

		// A deactivation error occurred. Display the message returned by our API.
	} elseif ( ! $activation_response['success'] ) {

		$error_text = __( '<b>ACF Activation Error</b>', 'acf' ) . ': <span class="description">' . $activation_response['message'] . '.</span>';

	} else {

		// Delete any previously saved activation error transient.
		delete_transient( 'acf_activation_error' );

		// Prefix connect API success message with ACF as we could be outside of the ACF admin and display message.
		acf_add_admin_notice( '<b>ACF </b>' . acf_esc_html( $activation_response['message'] ), 'success' );
		return;

	}

	return acf_pro_set_activation_failure_transient( $error_text, ACF_PRO_LICENSE );

}

/**
 *  Set the automatic activation failure transient
 *
 *  @date    11/10/2021
 *  @since   5.11.0
 *
 *  @param   string $error_text string containing the error text message.
 *  @param   string $license_key the license key that was used during the failed activation.
 *
 *  @return void
 */
function acf_pro_set_activation_failure_transient( $error_text, $license_key ) {
	set_transient(
		'acf_activation_error',
		array(
			'error'   => $error_text,
			'license' => $license_key,
		),
		HOUR_IN_SECONDS
	);
}

/**
 *  Get the automatic activation failure transient
 *
 *  @date    11/10/2021
 *  @since   5.11.0
 *
 *  @return array|false Activation failure transient array, or false if it's not set.
 */
function acf_pro_get_activation_failure_transient() {
	return get_transient( 'acf_activation_error' );
}

/**
 * Display the stored activation error
 *
 * @date    11/10/2021
 * @since   5.11.0
 */
function acf_pro_display_activation_error() {
	// Return if we're not in admin.
	if ( ! is_admin() ) {
		return;
	}

	// Return if the current user cannot view ACF settings.
	if ( ! acf_current_user_can_admin() ) {
		return;
	}

	// Check if the transient exists.
	$activation_data = acf_pro_get_activation_failure_transient();

	// Return if the transient does not exist.
	if ( ! $activation_data ) {
		return;
	}

	// Check if the license key is defined. If not, delete the transient.
	if ( ! defined( 'ACF_PRO_LICENSE' ) || empty( ACF_PRO_LICENSE ) || ! is_string( ACF_PRO_LICENSE ) ) {
		delete_transient( 'acf_activation_error' );
		return;
	}

	// Append a retry link if we're not already on the settings page.
	global $plugin_page;
	if ( ! $plugin_page || 'acf-settings-updates' !== $plugin_page ) {
		$nonce                    = wp_create_nonce( 'acf_retry_activation' );
		$check_again_url          = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates&acf_retry_nonce=' . $nonce );
		$activation_data['error'] = $activation_data['error'] . ' <a href="' . $check_again_url . '">' . __( 'Check Again', 'acf' ) . '</a>';
	}

	// Add a non-dismissible error message with the activation error.
	acf_add_admin_notice( acf_esc_html( $activation_data['error'] ), 'error', false );
}

/**
 *  This function will return the license
 *
 *  @type    function
 *  @date    20/09/2016
 *  @since   5.4.0
 *
 *  @return  $license    Activated license array
 */
function acf_pro_get_license() {

	// get option
	$license = get_option( 'acf_pro_license' );

	// bail early if no value
	if ( ! $license ) {
		return false;
	}

	// decode
	$license = acf_maybe_unserialize( base64_decode( $license ) );

	// bail early if corrupt
	if ( ! is_array( $license ) ) {
		return false;
	}

	// return
	return $license;

}

/**
 * An ACF specific getter to replace `home_url` in our licence checks to ensure we can avoid third party filters.
 *
 * @since 6.0.1
 *
 * @return string $home_url The output from home_url, sans known third party filters which cause licence activation issues.
 */
function acf_get_home_url() {
	// Disable WPML's home url overrides for our license check.
	add_filter( 'wpml_get_home_url', 'acf_licence_wpml_intercept', 99, 2 );

	$home_url = home_url();

	// Re-enable WPML's home url overrides.
	remove_filter( 'wpml_get_home_url', 'acf_licence_wpml_intercept', 99 );

	return $home_url;
}

/**
 * Return the original home url inside ACF's home url getter.
 *
 * @since 6.0.1
 *
 * @param string $home_url the WPML converted home URL.
 * @param string $url the original home URL.
 *
 * @return string $url
 */
function acf_licence_wpml_intercept( $home_url, $url ) {
	return $url;
}


/**
 *  This function will return the license key
 *
 *  @type    function
 *  @date    20/09/2016
 *  @since   5.4.0
 *
 *  @param   boolean $skip_url_check Skip the check of the current site url.
 *  @return  string $license_key
 */
function acf_pro_get_license_key( $skip_url_check = false ) {

	$license  = acf_pro_get_license();
	$home_url = acf_get_home_url();

	// bail early if empty
	if ( ! $license || ! $license['key'] ) {
		return false;
	}

	// bail early if url has changed
	if ( ! $skip_url_check && acf_strip_protocol( $license['url'] ) !== acf_strip_protocol( $home_url ) ) {
		return false;
	}

	// return
	return $license['key'];

}


/**
 *  This function will update the DB license
 *
 *  @type    function
 *  @date    20/09/2016
 *  @since   5.4.0
 *
 *  @param   string $key    The license key
 *  @return  bool           The result of the update_option call
 */
function acf_pro_update_license( $key = '' ) {

	// vars
	$value = '';

	// key
	if ( $key ) {

		// vars
		$data = array(
			'key' => $key,
			'url' => acf_get_home_url(),
		);

		// encode
		$value = base64_encode( maybe_serialize( $data ) );

	}

	// re-register update (key has changed)
	acf_register_plugin_update(
		array(
			'id'       => 'pro',
			'key'      => $key,
			'slug'     => acf_get_setting( 'slug' ),
			'basename' => acf_get_setting( 'basename' ),
			'version'  => acf_get_setting( 'version' ),
		)
	);

	// update
	return update_option( 'acf_pro_license', $value );

}

/**
 * Get count of registered ACF Blocks
 *
 * @return int
 */
function acf_pro_get_registered_block_count() {
	return acf_get_store( 'block-types' )->count();
}

/**
 * Activates the submitted license key
 * Formally ACF_Admin_Updates::activate_pro_licence since 5.0.0
 *
 * @date    30/09/2021
 * @since   5.11.0
 *
 * @param   string  $license_key    License key to activate
 * @param   boolean $silent         Return errors rather than displaying them
 * @return  mixed   $response       A wp-error instance, or an array with a boolean success key, and string message key
 */
function acf_pro_activate_license( $license_key, $silent = false ) {

	// Connect to API.
	$post = array(
		'acf_license'  => trim( $license_key ),
		'acf_version'  => acf_get_setting( 'version' ),
		'wp_name'      => get_bloginfo( 'name' ),
		'wp_url'       => acf_get_home_url(),
		'wp_version'   => get_bloginfo( 'version' ),
		'wp_language'  => get_bloginfo( 'language' ),
		'wp_timezone'  => get_option( 'timezone_string' ),
		'wp_multisite' => (int) is_multisite(),
		'php_version'  => PHP_VERSION,
		'block_count'  => acf_pro_get_registered_block_count(),
	);

	$response = acf_updates()->request( 'v2/plugins/activate?p=pro', $post );

	// Check response is expected JSON array (not string).
	if ( is_string( $response ) ) {
		$response = new WP_Error( 'server_error', esc_html( $response ) );
	}

	// Display error.
	if ( is_wp_error( $response ) ) {
		if ( ! $silent ) {
			display_wp_activation_error( $response );
		}
		return $response;
	}

	$success = false;

	// On success.
	if ( $response['status'] == 1 ) {

		// Update license.
		acf_pro_update_license( $response['license'] );

		// Refresh plugins transient to fetch new update data.
		acf_updates()->refresh_plugins_transient();

		// Show notice.
		if ( ! $silent ) {
			acf_add_admin_notice( acf_esc_html( $response['message'] ), 'success' );
		}

		$success = true;

		// On failure.
	} else {

		// Show notice.
		if ( ! $silent ) {
			acf_add_admin_notice( acf_esc_html( $response['message'] ), 'warning' );
		}
	}

	// Return status array for automated activation error notices
	return array(
		'success' => $success,
		'message' => $response['message'],
	);

}

/**
 * Deactivates the registered license key.
 * Formally ACF_Admin_Updates::deactivate_pro_licence since 5.0.0
 *
 * @date    30/09/2021
 * @since   5.11.0
 *
 * @param   bool $silent     Return errors rather than displaying them
 * @return  mixed   $response   A wp-error instance, or an array with a boolean success key, and string message key
 */
function acf_pro_deactivate_license( $silent = false ) {

	// Get license key.
	$license = acf_pro_get_license_key( true );

	// Bail early if no key.
	if ( ! $license ) {
		return false;
	}

	// Connect to API.
	$post     = array(
		'acf_license' => $license,
		'wp_url'      => acf_get_home_url(),
	);
	$response = acf_updates()->request( 'v2/plugins/deactivate?p=pro', $post );

	// Check response is expected JSON array (not string).
	if ( is_string( $response ) ) {
		$response = new WP_Error( 'server_error', esc_html( $response ) );
	}

	// Display error.
	if ( is_wp_error( $response ) ) {
		if ( ! $silent ) {
			display_wp_activation_error( $response );
		}
		return $response;
	}

	// Remove license key from DB.
	acf_pro_update_license( '' );

	// Refresh plugins transient to fetch new update data.
	acf_updates()->refresh_plugins_transient();

	$success = $response['status'] == 1;

	if ( ! $silent ) {
		$notice_class = $success ? 'info' : 'warning';
		acf_add_admin_notice( acf_esc_html( $response['message'] ), $notice_class );
	}

	// Return status array for automated activation error notices
	return array(
		'success' => $success,
		'message' => $response['message'],
	);

}


/**
 * Adds an admin notice using the provided WP_Error.
 *
 * @date    14/1/19
 * @since   5.7.10
 *
 * @param   WP_Error $wp_error The error to display.
 */
function display_wp_activation_error( $wp_error ) {

	// Only show one error on page.
	if ( acf_has_done( 'display_wp_error' ) ) {
		return;
	}

	// Create new notice.
	acf_new_admin_notice(
		array(
			'text' => __( '<b>ACF Activation Error</b>. Could not connect to activation server', 'acf' ) . ' <span class="description">(' . esc_html( $wp_error->get_error_message() ) . ').</span>',
			'type' => 'error',
		)
	);
}
