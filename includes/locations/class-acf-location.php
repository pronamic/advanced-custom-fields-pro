<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Location') ) :

class ACF_Location {
	
	/** @var string The location rule name. */
	public $name = '';
	
	/** @var string The location rule label. */
	public $label = '';
	
	/** @var string The location rule category. */
	public $category = 'post';
	
	/** @var string The location rule visibility. */
	public $public = true;
	
	/**
	 * __construct
	 *
	 * Sets up the class functionality.
	 *
	 * @date	5/03/2014
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
	function __construct() {
		
		// Call initialize to setup props.
		$this->initialize();
		
		// Add filters.
		$this->add_filter( 'acf/location/rule_match/' . $this->name, array($this, 'rule_match'), 5, 3 );
		$this->add_filter( 'acf/location/rule_operators/' . $this->name, array($this, 'rule_operators'), 5, 2 );
		$this->add_filter( 'acf/location/rule_values/' . $this->name, array($this, 'rule_values'), 5, 2 );
	}
	
	/**
	 * add_filter
	 *
	 * Maybe adds a filter callback.
	 *
	 * @date	17/9/19
	 * @since	5.8.1
	 *
	 * @param	string $tag The filter name.
	 * @param	callable $function_to_add The callback function.
	 * @param	int $priority The filter priority.
	 * @param	int $accepted_args The number of args to accept.
	 * @return	void
	 */
	function add_filter( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		if( is_callable($function_to_add) ) {
			add_filter( $tag, $function_to_add, $priority, $accepted_args );
		}
	}
	
	/**
	 * initialize
	 *
	 * Sets up the class functionality.
	 *
	 * @date	5/03/2014
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
	function initialize() {
		// Do nothing.
	}
	
	/**
	 * compare
	 *
	 * Compares the given value and rule params returning true when they match.
	 *
	 * @date	17/9/19
	 * @since	5.8.1
	 *
	 * @param	mixed $value The value to compare against.
	 * @param	array $rule The locatio rule data.
	 * @return	bool
	 */
	function compare( $value, $rule ) {
		
		// Allow "all" to match any value.
        if( $rule['value'] === 'all' ) {
	        $match = true;
	        
        // Compare all other values.
        } else {
	        $match = ( $value == $rule['value'] );
        }
		
		// Allow for "!=" operator.
        if( $rule['operator'] == '!=' ) {
        	$match = !$match;
        }
		
		// Return.
		return $match;
	}
}

endif; // class_exists check
