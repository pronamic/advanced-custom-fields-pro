<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Location_Block') ) :

class ACF_Location_Block extends acf_location {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'block';
		$this->label = __("Block",'acf');
		$this->category = 'forms';
	}
	
	
	/*
	*  rule_match
	*
	*  This function is used to match this location $rule to the current $screen
	*
	*  @type	function
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match( $result, $rule, $screen ) {
		
		// vars
		$block = acf_maybe_get( $screen, 'block' );
		
		// bail early if not block
		if( !$block ) return false;
				
        // compare
        return $this->compare( $block, $rule );
	}
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available values for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function rule_values( $choices, $rule ) {
		
		// vars
		$blocks = acf_get_block_types();
		
		// loop
		if( $blocks ) {
			$choices['all'] = __('All', 'acf');
			foreach( $blocks as $block ) {
				$choices[ $block['name'] ] = $block['title'];
			}
		}	
		
		// return
		return $choices;
	}
	
}

// initialize
acf_register_location_rule( 'ACF_Location_Block' );

endif; // class_exists check

?>