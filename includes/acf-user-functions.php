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
 * acf_get_users
 *
 * Similar to the get_users() function but with extra functionality.
 *
 * @date    9/1/19
 * @since   5.7.10
 *
 * @param   array $args The query args.
 * @return  array
 */
function acf_get_users( $args = array() ) {

	// Get users.
	$users = get_users( $args );

	// Maintain order.
	if ( $users && $args['include'] ) {

		// Generate order array.
		$order = array();
		foreach ( $users as $i => $user ) {
			$order[ $i ] = array_search( $user->ID, $args['include'] );
		}

		// Sort results.
		array_multisort( $order, $users );
	}

	// Return
	return $users;
}

/**
 * acf_get_user_result
 *
 * Returns a result containing "id" and "text" for the given user.
 *
 * @date    21/5/19
 * @since   5.8.1
 *
 * @param   WP_User $user The user object.
 * @return  array
 */
function acf_get_user_result( $user ) {

	// Vars.
	$id   = $user->ID;
	$text = $user->user_login;

	// Add name.
	if ( $user->first_name && $user->last_name ) {
		$text .= " ({$user->first_name} {$user->last_name})";
	} elseif ( $user->first_name ) {
		$text .= " ({$user->first_name})";
	}
	return compact( 'id', 'text' );
}


/**
 * acf_get_user_role_labels
 *
 * Returns an array of user roles in the format "name => label".
 *
 * @date    20/5/19
 * @since   5.8.1
 *
 * @param   array $roles A specific array of roles.
 * @return  array
 */
function acf_get_user_role_labels( $roles = array() ) {
	$all_roles = wp_roles()->get_names();

	// Load all roles if none provided.
	if ( empty( $roles ) ) {
		$roles = array_keys( $all_roles );
	}

	// Loop over roles and populare labels.
	$lables = array();
	foreach ( $roles as $role ) {
		if ( isset( $all_roles[ $role ] ) ) {
			$lables[ $role ] = translate_user_role( $all_roles[ $role ] );
		}
	}

	// Return labels.
	return $lables;
}

/**
 * acf_allow_unfiltered_html
 *
 * Returns true if the current user is allowed to save unfiltered HTML.
 *
 * @date    9/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  boolean
 */
function acf_allow_unfiltered_html() {

	// Check capability.
	$allow_unfiltered_html = current_user_can( 'unfiltered_html' );

	/**
	 * Filters whether the current user is allowed to save unfiltered HTML.
	 *
	 * @date    9/1/19
	 * @since   5.7.10
	 *
	 * @param   bool allow_unfiltered_html The result.
	 */
	return apply_filters( 'acf/allow_unfiltered_html', $allow_unfiltered_html );
}
