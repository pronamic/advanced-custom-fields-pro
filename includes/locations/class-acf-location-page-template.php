<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_page_template') ) :

class acf_location_page_template extends acf_location {
	
	
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
		$this->name = 'page_template';
		$this->label = __("Page Template",'acf');
		$this->category = 'page';
    	
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
		
		// Check if this rule is relevant to the current screen.
		// Find $post_id in the process.
		if( isset($screen['post_type']) ) {
			$post_type = $screen['post_type'];
		} elseif( isset($screen['post_id']) ) {
			$post_type = get_post_type( $screen['post_id'] );
		} else {
			return false;
		}
		
		// If this rule is set to "default" template, avoid matching on non "page" post types.
		// Fixes issue where post templates were added in WP 4.7 and field groups appeared on all post type edit screens.
		if( $rule['value'] === 'default' && $post_type !== 'page' ) {
			return false;
		}
		
		// Return.
		return acf_get_location_rule('post_template')->rule_match( $result, $rule, $screen );
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
		
		// Default choices.
		$choices = array(
			'default' => apply_filters( 'default_page_template_title',  __('Default Template', 'acf') )
		);
		
		// Load all templates, and merge in 'page' templates.
		$post_templates = acf_get_post_templates();
		if( isset($post_templates['page']) ) {
			$choices = array_merge($choices, $post_templates['page']);
		}
		
		// Return choices.
		return $choices;
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_page_template' );

endif; // class_exists check

?>