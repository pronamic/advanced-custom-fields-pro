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
 * Backwards compatibility for the standalone ACF > Updates page, which was replaced by
 * the License and Updates tabs on the Settings page in 6.8.12.
 *
 * Add-ons such as ACF Extended Pro render their own license settings on the old page,
 * so it is kept, with a notice pointing to the new tabs, whenever another plugin renders on it.
 *
 * @package acf
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACF_Admin_Updates' ) ) :

	/**
	 * Deprecated since 6.8.12. ACF PRO's license and updates UI is now on the Settings page's License and Updates tabs.
	 *
	 * Kept so add-ons that call its methods, such as get_changelog_changes(), keep working.
	 */
	class ACF_Admin_Updates {

		/**
		 * Data used in the view.
		 *
		 * @deprecated 6.8.12
		 * @var array
		 */
		public $view = array();

		/**
		 * Sets up the class. No longer registers any hooks.
		 *
		 * @since 5.0.0
		 * @deprecated 6.8.12
		 */
		public function __construct() {}

		/**
		 * No longer does anything.
		 *
		 * @since 5.7.10
		 * @deprecated 6.8.12
		 *
		 * @param WP_Error $wp_error The error to display.
		 * @return void
		 */
		public function display_wp_error( $wp_error ) {}

		/**
		 * Finds the specific changes for a given version from the provided changelog snippet.
		 *
		 * @since 5.7.10
		 * @deprecated 6.8.12
		 *
		 * @param string $changelog The changelog text.
		 * @param string $version   The version to find.
		 * @return string
		 */
		public function get_changelog_changes( $changelog = '', $version = '' ) {

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
		 * No longer does anything. See acf_legacy_updates_page_register().
		 *
		 * @since 5.0.0
		 * @deprecated 6.8.12
		 *
		 * @return void
		 */
		public function admin_menu() {}

		/**
		 * No longer does anything.
		 *
		 * @since 5.0.0
		 * @deprecated 6.8.12
		 *
		 * @return void
		 */
		public function load() {}

		/**
		 * No longer modifies the admin body class.
		 *
		 * @since 6.0.0
		 * @deprecated 6.8.12
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			return $classes;
		}

		/**
		 * No longer does anything.
		 *
		 * @since 5.0.0
		 * @deprecated 6.8.12
		 *
		 * @return void
		 */
		public function html() {}
	}

	acf_new_instance( 'ACF_Admin_Updates' );
endif; // class_exists check

/**
 * Returns the hook name of the legacy "acf-settings-updates" admin page.
 *
 * @since 6.8.12
 *
 * @return string
 */
function acf_legacy_updates_page_hookname() {
	return get_plugin_page_hookname( 'acf-settings-updates', 'edit.php?post_type=acf-field-group' );
}

/**
 * Registers the legacy Updates page when another plugin renders on it, as ACF Extended Pro does.
 *
 * Runs late on admin_menu so plugins hooking the page at the default priority are already hooked.
 * Without a render callback the page isn't registered and the Settings page redirects its URL to the License tab.
 *
 * @since 6.8.12
 *
 * @return void
 */
function acf_legacy_updates_page_register() {
	if ( ! acf_get_setting( 'show_admin' ) || ! acf_pro_is_updates_page_visible() ) {
		return;
	}

	$hook = acf_legacy_updates_page_hookname();

	// Only the render hook counts: some plugins hook "load-{$hook}" just to style the old page.
	if ( ! has_action( $hook ) ) {
		return;
	}

	// No callback here: add_submenu_page() would hook it at priority 10, after other plugins' callbacks at that priority.
	$page = add_submenu_page(
		'edit.php?post_type=acf-field-group',
		__( 'Updates', 'acf' ),
		__( 'Updates', 'acf' ),
		acf_get_setting( 'capability' ),
		'acf-settings-updates'
	);

	// False when the current user lacks the capability.
	if ( $page !== $hook ) {
		return;
	}

	add_action( "load-{$hook}", 'acf_legacy_updates_page_load' );

	// Wrap every other plugin's output, whatever its priority.
	add_action( $hook, 'acf_legacy_updates_page_open', PHP_INT_MIN );
	add_action( $hook, 'acf_legacy_updates_page_close', PHP_INT_MAX );
}
add_action( 'admin_menu', 'acf_legacy_updates_page_register', 99 );

/**
 * Runs when the legacy Updates page loads, before other plugins' load callbacks at later priorities, such as ACF Extended Pro's at 20.
 *
 * @since 6.8.12
 *
 * @return void
 */
function acf_legacy_updates_page_load() {
	// ACF PRO's own update and license checks now run on the tabs.
	if ( ! empty( $_GET['force-check'] ) || ! empty( $_GET['force-license-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Redirect only.
		acf_get_instance( 'ACF\Settings_Page\Settings_Page' )->redirect_legacy_pages();
	}

	add_filter( 'admin_body_class', 'acf_legacy_updates_page_body_class' );
}

/**
 * Adds ACF's admin page body class to the legacy Updates page.
 *
 * @since 6.8.12
 *
 * @param string $classes Space-separated list of CSS classes.
 * @return string
 */
function acf_legacy_updates_page_body_class( $classes ) {
	return $classes . ' acf-admin-page';
}

/**
 * Opens the legacy Updates page wrapper and explains where ACF PRO's license and updates moved.
 * Other plugins' output follows.
 *
 * @since 6.8.12
 *
 * @return void
 */
function acf_legacy_updates_page_open() {
	$license_url = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-tab-license' );
	$updates_url = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-tab-updates' );
	?>
	<div class="wrap acf-settings-wrap acf-updates">
		<div class="notice notice-info inline acf-legacy-updates-notice">
			<p>
				<?php
				echo acf_esc_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by acf_esc_html().
					sprintf(
						/* translators: 1: URL of the License tab, 2: URL of the Updates tab */
						__( 'ACF PRO\'s license and update details are now on the Settings page, under the <a href="%1$s">License</a> and <a href="%2$s">Updates</a> tabs. This page remains for other plugins that add their own license settings here.', 'acf' ),
						esc_url( $license_url ),
						esc_url( $updates_url )
					)
				);
				?>
			</p>
		</div>
	<?php
}

/**
 * Closes the legacy Updates page wrapper after other plugins' output.
 *
 * @since 6.8.12
 *
 * @return void
 */
function acf_legacy_updates_page_close() {
	echo '</div>';
}
