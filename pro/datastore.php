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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Whether the ACF datastore is enabled.
 *
 * Requires WordPress 6.7+. Controlled by the "Datastore" setting, or the
 * `acf/settings/enable_datastore` filter (applied by acf_get_setting()).
 *
 * @since 6.8.1
 *
 * @return boolean
 */
function acf_is_using_datastore() {
	if ( ! version_compare( get_bloginfo( 'version' ), '6.7', '>=' ) ) {
		return false;
	}

	return (bool) acf_get_setting( 'enable_datastore' );
}
