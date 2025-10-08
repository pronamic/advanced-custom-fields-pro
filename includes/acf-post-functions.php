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

/**
 * Returns available templates for each post type.
 *
 * @date    29/8/17
 * @since   5.6.2
 *
 * @param   void
 * @return  array
 */
function acf_get_post_templates() {

	// Check store.
	$cache = acf_get_data( 'post_templates' );
	if ( $cache !== null ) {
		return $cache;
	}

	// Initialize templates with default placeholder for pages.
	$post_templates         = array();
	$post_templates['page'] = array();

	// Loop over post types and append their templates.
	if ( method_exists( 'WP_Theme', 'get_page_templates' ) ) {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			$templates = wp_get_theme()->get_page_templates( null, $post_type );
			if ( $templates ) {
				$post_templates[ $post_type ] = $templates;
			}
		}
	}

	// Update store.
	acf_set_data( 'post_templates', $post_templates );

	// Return templates.
	return $post_templates;
}
