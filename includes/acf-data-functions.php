<?php 

/**
 * acf_instances
 *
 * Initialize $acf_instances if it has not been set.
 *
 * @date	9/1/19
 * @since	5.7.10
 *
 * @param	void
 * @return	ACF_Data
 */
function acf_instances() {
	
	// Globals.
	global $acf_instances;
	
	// Initialize only once.
	if( !isset($acf_instances) ) {
		$acf_instances = new ACF_Data();
	}
	
	// Return.
	return $acf_instances;
}

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
	
	// Create instance.
	$instance = new $class();
	
	// Register instance.
	acf_instances()->set( $class, $instance );
	
	// Return instance.
	return $instance;
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
	return acf_instances()->get( $class );
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
	 acf_instances()->set( "ACF_Store_$name", $store );
	 
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
	return acf_instances()->get( "ACF_Store_$name" );
}
 