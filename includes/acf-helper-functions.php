<?php 

/**
 * acf_uniqid
 *
 * Returns a unique numeric based id.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	string $prefix The id prefix. Defaults to 'acf'.
 * @return	string
 */
function acf_uniqid( $prefix = 'acf' ) {
	
	// Instantiate global counter.
	global $acf_uniqid;
	if( !isset($acf_uniqid) ) {
		$acf_uniqid = 1;
	}
	
	// Return id.
	return $prefix . '-' . $acf_uniqid++;
}

/**
 * acf_merge_attributes
 *
 * Merges together two arrays but with extra functionality to append class names.
 *
 * @date	22/1/19
 * @since	5.7.10
 *
 * @param	array $array1 An array of attributes.
 * @param	array $array2 An array of attributes.
 * @return	array
 */
function acf_merge_attributes( $array1, $array2 ) {
	
	// Merge together attributes.
	$array3 = array_merge( $array1, $array2 );
	
	// Append together special attributes.
	foreach( array('class', 'style') as $key ) {
		if( isset($array1[$key]) && isset($array2[$key]) ) {
			$array3[$key] = trim($array1[$key]) . ' ' . trim($array2[$key]);
		}
	}
	
	// Return
	return $array3;
}

/**
 * acf_cache_key
 *
 * Returns a filtered cache key.
 *
 * @date	25/1/19
 * @since	5.7.11
 *
 * @param	string $key The cache key.
 * @return	string
 */
function acf_cache_key( $key = '' ) {
	
	/**
	 * Filters the cache key.
	 *
	 * @date	25/1/19
	 * @since	5.7.11
	 *
	 * @param	string $key The cache key.
	 * @param	string $original_key The original cache key.
	 */
	return apply_filters( "acf/get_cache_key", $key, $key );
}

/**
 * acf_request_args
 *
 * Returns an array of $_REQUEST values using the provided defaults.
 *
 * @date	28/2/19
 * @since	5.7.13
 *
 * @param	array $args An array of args.
 * @return	array
 */
function acf_request_args( $args = array() ) {
	foreach( $args as $k => $v ) {
		$args[ $k ] = isset($_REQUEST[ $k ]) ? $_REQUEST[ $k ] : $args[ $k ];
	}
	return $args;
}

// Register store.
acf_register_store( 'filters' );

/**
 * acf_enable_filter
 *
 * Enables a filter with the given name.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	string name The modifer name.
 * @return	void
 */
function acf_enable_filter( $name = '' ) {
	acf_get_store( 'filters' )->set( $name, true );
}

/**
 * acf_disable_filter
 *
 * Disables a filter with the given name.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	string name The modifer name.
 * @return	void
 */
function acf_disable_filter( $name = '' ) {
	acf_get_store( 'filters' )->set( $name, false );
}

/**
 * acf_is_filter_enabled
 *
 * Returns the state of a filter for the given name.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	string name The modifer name.
 * @return	array
 */
function acf_is_filter_enabled( $name = '' ) {
	return acf_get_store( 'filters' )->get( $name );
}

/**
 * acf_get_filters
 *
 * Returns an array of filters in their current state.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	void
 * @return	array
 */
function acf_get_filters() {
	return acf_get_store( 'filters' )->get();
}

/**
 * acf_set_filters
 *
 * Sets an array of filter states.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	array $filters An Array of modifers
 * @return	array
 */
function acf_set_filters( $filters = array() ) {
	acf_get_store( 'filters' )->set( $filters );
}

/**
 * acf_disable_filters
 *
 * Disables all filters and returns the previous state.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	void
 * @return	array
 */
function acf_disable_filters() {
	
	// Get state.
	$prev_state = acf_get_filters();
	
	// Set all modifers as false.
	acf_set_filters( array_map('__return_false', $prev_state) );
	
	// Return prev state.
	return $prev_state;
}

/**
 * acf_enable_filters
 *
 * Enables all or an array of specific filters and returns the previous state.
 *
 * @date	14/7/16
 * @since	5.4.0
 *
 * @param	array $filters An Array of modifers
 * @return	array
 */
function acf_enable_filters( $filters = array() ) {
	
	// Get state.
	$prev_state = acf_get_filters();
	
	// Allow specific filters to be enabled.
	if( $filters ) {
		acf_set_filters( $filters );
		
	// Set all modifers as true.	
	} else {
		acf_set_filters( array_map('__return_true', $prev_state) );
	}
	
	// Return prev state.
	return $prev_state;
}
