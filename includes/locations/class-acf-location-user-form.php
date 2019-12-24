<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Location_User_Form') ) :

class ACF_Location_User_Form extends ACF_Location {
	
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
		$this->name = 'user_form';
		$this->label = __("User Form", 'acf');
		$this->category = 'user';
	}
	
	/**
	 * rule_match
	 *
	 * Determines if the given location $rule is a match for the current $screen.
	 *
	 * @date	17/9/19
	 * @since	5.8.1
	 *
	 * @param	bool $result Whether or not this location rule is a match.
	 * @param	array $rule The locatio rule data.
	 * @param	array $screen The current screen data.
	 * @return	bool
	 */
	function rule_match( $result, $rule, $screen ) {
		
		// Extract vars.
		$user_form = acf_maybe_get($screen, 'user_form');
		
		// Return false if no user_form data.
		if( !$user_form ) {
			return false;
		}
		
		// The "Add / Edit" choice (foolishly valued "edit") should match true for either "add" or "edit".
		if( $rule['value'] === 'edit' && $user_form === 'add' ) {
			$user_form = 'edit';
		}
				
		// Compare and return.
		return $this->compare( $user_form, $rule );
	}
	
	/**
	 * rule_values
	 *
	 * Returns an array of values for this location rule.
	 *
	 * @date	17/9/19
	 * @since	5.8.1
	 *
	 * @param	array $choices An empty array.
	 * @param	array $rule The locatio rule data.
	 * @return	type Description.
	 */
	function rule_values( $choices, $rule ) {
		return array(
			'all' 		=> __('All', 'acf'),
			'add' 		=> __('Add', 'acf'),
			'edit' 		=> __('Add / Edit', 'acf'),
			'register' 	=> __('Register', 'acf')
		);		
	}
}

// Register.
acf_register_location_rule( 'ACF_Location_User_Form' );

endif; // class_exists check
