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
 * The datastore requires WordPress 6.7+ and can be enabled via the
 * `acf/settings/enable_datastore` filter.
 *
 * @since 6.8.1
 *
 * @return boolean
 */
function acf_is_using_datastore() {
	// Bail if not on WordPress 6.7+.
	if ( ! version_compare( get_bloginfo( 'version' ), '6.7', '>=' ) ) {
		return false;
	}

	/**
	 * Filters whether the ACF datastore is enabled.
	 *
	 * @since 6.8.1
	 *
	 * @param boolean $enabled Whether the datastore is enabled. Default false.
	 */
	return (bool) apply_filters( 'acf/settings/enable_datastore', false );
}
