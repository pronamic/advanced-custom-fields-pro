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
