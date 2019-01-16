<?php 

/**
 * acf_get_users
 *
 * Similar to the get_users() function but with extra functionality.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	array $args The query args.
 * @return	array
 */
function acf_get_users( $args = array() ) {
	
	// Get users.
	$users = get_users( $args );
	
	// Maintain order.
	if( $users && $args['include'] ) {
		
		// Generate order array.
		$order = array();
		foreach( $users as $i => $user ) {
			$order[ $i ] = array_search($user->ID, $args['include']);
		}
		
		// Sort results.
		array_multisort($order, $users);	
	}
	
	// Return
	return $users;
}

/**
 * acf_allow_unfiltered_html
 *
 * Returns true if the current user is allowed to save unfiltered HTML.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	void
 * @return	bool
 */
function acf_allow_unfiltered_html() {
	
	// Check capability.
	$allow_unfiltered_html = current_user_can('unfiltered_html');
	
	/**
	 * Filters whether the current user is allowed to save unfiltered HTML.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	bool allow_unfiltered_html The result.
	 */
	return apply_filters( 'acf/allow_unfiltered_html', $allow_unfiltered_html );
}