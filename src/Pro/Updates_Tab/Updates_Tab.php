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
 * Updates Tab for the ACF Settings Page.
 *
 * Registers the Updates tab via the 'acf/settings_page/tabs' filter
 * and provides settings for API, scripts, and update-related options.
 *
 * @package acf
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adds the Updates tab to the settings page.
 *
 * @since 6.8.2
 *
 * @param array[] $tabs Existing tab definitions.
 * @return array[]
 */
function acf_updates_tab_register( $tabs ) {
	if ( function_exists( 'acf_pro_is_updates_page_visible' ) && ! acf_pro_is_updates_page_visible() ) {
		return $tabs;
	}

	$tabs[] = array(
		'key'          => 'updates',
		'_builtin'     => true, // Internal: allows ACF's reserved "updates" key.
		'label'        => __( 'Updates', 'acf' ),
		'order'        => 30,
		'count'        => 'acf_updates_tab_get_count',
		'strings'      => array(
			'installedVersion'        => __( 'Installed Version', 'acf' ),
			'latestVersion'           => __( 'Latest Version', 'acf' ),
			'updateStatus'            => __( 'Update Status', 'acf' ),
			'noUpdateAvailable'       => __( 'No update available', 'acf' ),
			'newVersionAvailable'     => __( 'A new version of ACF is available', 'acf' ),
			/* translators: %s: the latest ACF version number */
			'viewVersionDetails'      => __( 'View version %s details', 'acf' ),
			'checkForUpdates'         => __( 'Check For Updates', 'acf' ),
			'updateNow'               => __( 'Update Now', 'acf' ),
			'seeAllUpdates'           => __( 'See All Updates', 'acf' ),
			'checkingForUpdates'      => __( 'Checking for updates', 'acf' ),
			'currentVersionChangelog' => __( 'Current Version', 'acf' ),
			'noChangelogAvailable'    => __( 'No changelog available.', 'acf' ),
			'notAvailable'            => __( 'N/A', 'acf' ),
			'licenseErrorMessage'     => __( 'Your ACF PRO license is no longer active.', 'acf' ),
			'licenseErrorSubtext'     => __( 'Please renew to continue to have access to updates, support & PRO features', 'acf' ),
			'serverErrorTitle'        => __( 'Error', 'acf' ),
			'serverErrorMessage'      => __( 'Could not connect to the update server', 'acf' ),
			'opensInNewTab'           => __( 'Opens in new tab', 'acf' ),
			'wpIncompatibleTitle'     => __( 'WordPress is not compatible with the latest version of ACF.', 'acf' ),
			'checkForUpdatesError'    => __( 'Something went wrong on our end. Please try again.', 'acf' ),
		),
		'get_tab_data' => 'acf_updates_tab_get_data',
		'enqueue'      => 'acf_updates_tab_enqueue_scripts',
	);

	return $tabs;
}
add_filter( 'acf/settings_page/tabs', 'acf_updates_tab_register' );

/**
 * Enqueues Thickbox for the version details modal.
 *
 * @since 6.8.12
 *
 * @return void
 */
function acf_updates_tab_enqueue_scripts() {
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
}

/**
 * Extracts changes for a specific version from a changelog string.
 *
 * @since 6.8.2
 *
 * @param string $changelog The changelog text containing multiple version sections.
 * @param string $version   The version to extract changes for.
 * @return string The HTML for the given version's changes, or an empty string.
 */
function acf_updates_tab_get_changelog_changes( $changelog = '', $version = '' ) {
	$bits = array_filter( explode( '<h4>', $changelog ) );

	foreach ( $bits as $bit ) {
		$bit         = explode( '</h4>', $bit );
		$bit_version = trim( $bit[0] );
		$bit_text    = isset( $bit[1] ) ? trim( $bit[1] ) : '';

		if ( ! acf_version_compare( $bit_version, '==', $version ) ) {
			continue;
		}

		// Extract optional release date from a leading <p> (with or without <em>) before the <ul>.
		$release_date = '';
		if ( preg_match( '/<p[^>]*>(?:<em>)?(Release[^<]*)(?:<\/em>)?<\/p>/i', $bit_text, $date_match ) ) {
			$release_date = trim( $date_match[1] );
			$bit_text     = str_replace( $date_match[0], '', $bit_text );
		}

		// Convert <li> items to <p> tags to match the plain-text monospace layout.
		preg_match_all( '/<li>(.*?)<\/li>/s', $bit_text, $item_matches );
		$items = array();
		foreach ( $item_matches[1] as $item ) {
			$items[] = '<p>' . esc_html( wp_strip_all_tags( $item ) ) . '</p>';
		}

		$html  = '<p>ACF PRO ' . esc_html( $bit_version ) . '</p>';
		$html .= $release_date ? '<p>' . esc_html( $release_date ) . '</p>' : '';
		$html .= '<p>----------------------------------------</p>';
		$html .= implode( '', $items );

		return $html;
	}

	return '';
}

/**
 * Extracts changes for a specific version from a raw readme.txt content string.
 *
 * @since 6.8.6
 *
 * @param string $content Raw readme.txt content.
 * @param string $version The version to extract changes for.
 * @return string The HTML for the given version's changes, or an empty string.
 */
function acf_updates_tab_parse_readme_changelog( $content, $version ) {
	// Extract the Changelog section only.
	if ( ! preg_match( '/^== Changelog ==$(.*?)(?:^== [A-Z]|\z)/msi', $content, $section_match ) ) {
		return '';
	}

	// Split into per-version blocks on "= X.Y.Z =" headings.
	$bits = preg_split( '/^= /m', $section_match[1] );

	foreach ( $bits as $bit ) {
		// Each bit starts with "VERSION =" followed by the content.
		if ( ! preg_match( '/^([^\n=]+)\s*=\s*\n(.*)/s', $bit, $version_matches ) ) {
			continue;
		}

		$bit_version = trim( $version_matches[1] );
		$bit_text    = trim( $version_matches[2] );

		if ( ! acf_version_compare( $bit_version, '==', $version ) ) {
			continue;
		}

		// Convert the plain-text changelog lines to match the Figma layout.
		$lines        = array_filter( array_map( 'trim', explode( "\n", $bit_text ) ) );
		$release_date = '';
		$items        = array();

		foreach ( $lines as $line ) {
			// Italicised release-date lines are wrapped in asterisks e.g. *Release Date 14th July 2026*.
			if ( 0 === strncmp( $line, '*', 1 ) && substr( $line, -1 ) === '*' ) {
				$release_date = trim( $line, '*' );
				continue;
			}
			if ( 0 === strncmp( $line, '*', 1 ) ) {
				$items[] = '<p>' . esc_html( ltrim( $line, '* ' ) ) . '</p>';
			}
		}

		$html  = '<p>ACF PRO ' . esc_html( $bit_version ) . '</p>';
		$html .= $release_date ? '<p>' . esc_html( $release_date ) . '</p>' : '';
		$html .= '<p>----------------------------------------</p>';
		$html .= implode( '', $items );
		return $html;
	}

	return '';
}

/**
 * Extracts changes for a specific version from the local readme.txt.
 *
 * Used as a fallback when the remote changelog is unavailable or does not
 * contain an entry for the current installed version (e.g. a dev build, or
 * a version older than the last 3 releases returned by the API).
 *
 * @since 6.8.6
 *
 * @param string $version     The version to extract changes for.
 * @param string $readme_path Optional path to a readme.txt file. Defaults to
 *                            the installed plugin readme.
 * @return string The HTML for the given version's changes, or an empty string.
 */
function acf_updates_tab_get_changelog_changes_from_readme( $version = '', $readme_path = '' ) {
	if ( ! $readme_path ) {
		$readme_path = acf_get_path( 'readme.txt' );
	}

	if ( ! $readme_path || ! file_exists( $readme_path ) ) {
		return '';
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = file_get_contents( $readme_path );

	if ( ! $content ) {
		return '';
	}

	return acf_updates_tab_parse_readme_changelog( $content, $version );
}


/**
 * Returns the tab data for the Updates tab.
 *
 * This function is called only when the Updates tab is active,
 * avoiding unnecessary data preparation on other admin pages.
 *
 * @since 6.8.2
 *
 * @return array
 */
function acf_updates_tab_get_data() {
	$current_version          = acf_get_setting( 'version' );
	$remote_version           = '';
	$update_available         = false;
	$license_error            = false;
	$server_error             = false;
	$server_error_message     = '';
	$info_changelog           = '';
	$view_version_details_url = '';
	$update_url               = '';
	$update_blocked_message   = '';

	$wp_compatible           = true;
	$wp_incompatible_message = '';

	// Links to the old Updates page used "force-check" to skip the cached plugin info.
	$force_check = ! empty( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only skips the cache.
	$info        = acf_updates()->get_plugin_info( 'pro', $force_check );

	if ( is_wp_error( $info ) ) {
		$server_error         = true;
		$server_error_message = $info->get_error_message();
	} elseif ( is_array( $info ) && ! empty( $info['version'] ) ) {
		$remote_version = $info['version'];
		$info_changelog = $info['changelog'] ?? '';

		if ( version_compare( $remote_version, $current_version, '>' ) ) {
			$update_available = true;

			$basename    = acf_get_setting( 'basename' );
			$update      = acf_updates()->get_plugin_update( $basename );
			$license_key = acf_pro_get_license_key();

			// Check for license errors that would block the update. Only sites with a license key can have one,
			// matching the legacy Updates page.
			if ( $license_key && $update ) {
				if ( isset( $update['license_valid'] ) && ! $update['license_valid'] ) {
					$license_error = true;
				} elseif ( isset( $update['package'] ) && ! $update['package'] ) {
					$license_error = true;
				}
			}

			// Refresh the updates transient if it has no entry for ACF or its 'new_version' is stale,
			// so WordPress picks up the new version on its next update check.
			if ( $license_key && ( ! $update || ( $update['new_version'] ?? '' ) !== $remote_version ) ) {
				acf_updates()->refresh_plugins_transient();
			}

			// Check if the available update requires a higher WordPress version.
			$required_wp = '';
			if ( $update && ! empty( $update['requires'] ) ) {
				$required_wp = $update['requires'];
			} elseif ( ! empty( $info['requires'] ) ) {
				$required_wp = $info['requires'];
			}

			if ( $required_wp && version_compare( get_bloginfo( 'version' ), $required_wp, '<' ) ) {
				$wp_compatible           = false;
				$wp_incompatible_message = sprintf(
					/* translators: %s: Minimum required WordPress version number */
					__( 'An update to ACF is available, but it is not compatible with your version of WordPress. Please upgrade to WordPress %s or newer to update ACF.', 'acf' ),
					esc_html( $required_wp )
				);
			}

			// Thickbox URL to display the plugin details modal. This is passed to JS as data, not
			// printed as HTML, so use esc_url_raw() to avoid encoding & as &#038;.
			$view_version_details_url = esc_url_raw(
				add_query_arg(
					array(
						'tab'       => 'plugin-information',
						'plugin'    => 'advanced-custom-fields-pro',
						'section'   => 'changelog',
						'TB_iframe' => 'true',
						'width'     => '772',
						'height'    => '800',
					),
					admin_url( 'plugin-install.php' )
				)
			);

			// WordPress plugins page filtered to ACF, matching the legacy update flow. Withheld when WordPress
			// is too old, or when the plugins page can't install the update, so the Update Now button stays
			// disabled, as the legacy page did.
			if ( $wp_compatible ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( ! $license_key ) {
					$update_blocked_message = __( 'Enter your license key to unlock updates', 'acf' );
				} elseif ( is_multisite() ) {
					$update_blocked_message = __( 'Update ACF in Network Admin', 'acf' );
				} elseif ( ! is_plugin_active( $basename ) ) {
					$update_blocked_message = __( 'Updates must be manually installed in this configuration', 'acf' );
				} else {
					$update_url = esc_url_raw( admin_url( 'plugins.php?s=Advanced+Custom+Fields+Pro' ) );
				}
			}
		}
	}

	// Try remote changelog first (covers last 3 released versions).
	// Fall back to local readme.txt for dev builds or older versions.
	$changelog = acf_updates_tab_get_changelog_changes( $info_changelog, $current_version );

	if ( ! $changelog ) {
		$changelog = acf_updates_tab_get_changelog_changes_from_readme( $current_version );
	}

	return array(
		'currentVersion'        => $current_version,
		'remoteVersion'         => $remote_version,
		'updateAvailable'       => $update_available,
		'isChecking'            => false,
		'licenseError'          => $license_error,
		'serverError'           => $server_error,
		'serverErrorMessage'    => $server_error_message,
		'wpCompatible'          => $wp_compatible,
		'wpIncompatibleMessage' => $wp_incompatible_message,
		'changelog'             => $changelog,
		'viewVersionDetailsUrl' => $view_version_details_url,
		'updateUrl'             => $update_url,
		'updateBlockedMessage'  => $update_blocked_message,
		'seeAllUpdatesUrl'      => 'https://www.advancedcustomfields.com/changelog/',
		'count'                 => acf_updates_tab_get_count(),
	);
}

/**
 * Returns the badge count for the Updates tab: 1 if a newer version is available, otherwise 0.
 *
 * Only uses cached plugin info: this runs on every settings page load, so it must not
 * make a remote request. The Updates tab itself refreshes the cache.
 *
 * @since 6.8.12
 *
 * @return integer
 */
function acf_updates_tab_get_count() {
	$info = get_transient( 'acf_plugin_info_pro' );
	return ( ! is_wp_error( $info ) && is_array( $info ) && ! empty( $info['version'] )
		&& version_compare( $info['version'], acf_get_setting( 'version' ), '>' ) ) ? 1 : 0;
}

/**
 * AJAX handler for the "Check For Updates" button.
 *
 * Clears cached update data and returns fresh update status.
 *
 * @since 6.8.6
 *
 * @return void
 */
function acf_updates_tab_check_for_updates_ajax() {
	// Send errors as JSON messages, matching the settings page's own AJAX handlers, so the tab can show them.
	if ( ! acf_verify_ajax() ) {
		wp_send_json_error( __( 'Invalid nonce.', 'acf' ), 403 );
	}

	if ( ! acf_current_user_can_admin() ) {
		wp_send_json_error( __( 'Sorry, you do not have permission to do that.', 'acf' ), 403 );
	}

	// Clear cached update and info transients so a fresh remote check is made.
	acf_updates()->refresh_plugins_transient( 'pro' );

	wp_send_json_success( acf_updates_tab_get_data() );
}
add_action( 'wp_ajax_acf_check_for_updates', 'acf_updates_tab_check_for_updates_ajax' );
