<?php 

// Globals.
global $acf_stores, $acf_instances;

// Initialize plaeholders.
$acf_stores = array();
$acf_instances = array();

/**
 * acf_new_instance
 *
 * Creates a new instance of the given class and stores it in the instances data store.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	string $class The class name.
 * @return	object The instance.
 */
function acf_new_instance( $class = '' ) {
	global $acf_instances;
	return $acf_instances[ $class ] = new $class();
}

/**
 * acf_get_instance
 *
 * Returns an instance for the given class.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	string $class The class name.
 * @return	object The instance.
 */
function acf_get_instance( $class = '' ) {
	global $acf_instances;
	if( !isset($acf_instances[ $class ]) ) {
		$acf_instances[ $class ] = new $class();
	}
	return $acf_instances[ $class ];
}

/**
 * acf_register_store
 *
 * Registers a data store.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	string $name The store name.
 * @param	array $data Array of data to start the store with.
 * @return	ACF_Data
 */
function acf_register_store( $name = '', $data = false ) {
	 
	// Create store.
	$store = new ACF_Data( $data );
	
	// Register store.
	global $acf_stores;
	$acf_stores[ $name ] = $store;
	
	// Return store.
	return $store;
 }
 
/**
 * acf_get_store
 *
 * Returns a data store.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	string $name The store name.
 * @return	ACF_Data
 */
function acf_get_store( $name = '' ) {
	global $acf_stores;
	return isset( $acf_stores[ $name ] ) ? $acf_stores[ $name ] : false;
}

/**
 * acf_switch_stores
 *
 * Triggered when switching between sites on a multisite installation.
 *
 * @date	13/2/19
 * @since	5.7.11
 *
 * @param	int $site_id New blog ID.
 * @param	int prev_blog_id Prev blog ID.
 * @return	void
 */
function acf_switch_stores( $site_id, $prev_site_id ) {
	
	// Loop over stores and call switch_site().
	global $acf_stores;
	foreach( $acf_stores as $store ) {
		$store->switch_site( $site_id, $prev_site_id );
	}
}
add_action( 'switch_blog', 'acf_switch_stores', 10, 2 );
