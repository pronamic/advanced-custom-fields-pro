<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if( ! class_exists('ACF_Data') ) :

class ACF_Data {
	
	/** @var string Unique identifier. */
	var $cid = '';
	
	/** @var array Storage for data. */
	var $data = array();
	
	/**
	 * __construct
	 *
	 * Sets up the class functionality.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	array $data Optional data to set.
	 * @return	void
	 */
	function __construct( $data = false ) {
		
		// Set cid.
		$this->cid = acf_uniqid();
		
		// Set data.
		if( $data && is_array($data) ) {
			$this->data = array_merge($this->data, $data);
		}
		
		// Initialize.
		$this->initialize();
	}
	
	/**
	 * initialize
	 *
	 * Called during constructor to setup class functionality.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	void
	 * @return	void
	 */
	function initialize() {
		
	}
	
	/**
	 * has
	 *
	 * Returns true if this has data for the given name.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	string $name The data name.
	 * @return	boolean
	 */
	function has( $name = '' ) {
		return isset($this->data[ $name ]);
	}
	
	/**
	 * get
	 *
	 * Returns data for the given name of null if doesn't exist.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	string $name The data name.
	 * @return	mixed
	 */
	function get( $name = '' ) {
		return isset($this->data[ $name ]) ? $this->data[ $name ] : null;
	}
	
	/**
	 * get_data
	 *
	 * Returns an array of all data.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	void
	 * @return	array
	 */
	function get_data() {
		return $this->data;
	}
	
	/**
	 * set
	 *
	 * Sets data for the given name and returns $this for chaining.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	(string|array) $name The data name or an array of data.
	 * @param	mixed $value The data value.
	 * @return	ACF_Data
	 */
	function set( $name = '', $value ) {
		
		// Set multiple.
		if( is_array($name) ) {
			$this->data = array_merge($this->data, $name);
			
		// Set single.	
		} else {
			$this->data[ $name ] = $value;
		}
		
		// Return this for chaining.
		return $this;
	}
	
	/**
	 * remove
	 *
	 * Removes data for the given name.
	 *
	 * @date	9/1/19
	 * @since	5.7.10
	 *
	 * @param	string $name The data name.
	 * @return	ACF_Data
	 */
	function remove( $name = '' ) {
		
		// Remove data.
		unset( $this->data[ $name ] );
		
		// Return this for chaining.
		return $this;
	}
}

endif; // class_exists check
