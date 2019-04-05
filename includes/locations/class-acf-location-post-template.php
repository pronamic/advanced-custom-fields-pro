<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post_template') ) :

class acf_location_post_template extends acf_location {
	
	
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
		$this->name = 'post_template';
		$this->label = __("Post Template",'acf');
		$this->category = 'post';
		$this->public = acf_version_compare('wp', '>=', '4.7');
    	
	}
	
	
	/*
	*  get_post_type
	*
	*  This function will return the current post_type
	*
	*  @type	function
	*  @date	25/11/16
	*  @since	5.5.0
	*
	*  @param	$options (int)
	*  @return	(mixed)
	*/
	
	function get_post_type( $screen ) {
		
		// vars
		$post_id = acf_maybe_get( $screen, 'post_id' );
		$post_type = acf_maybe_get( $screen, 'post_type' );
		
		
		// post_type
		if( $post_type ) return $post_type;
		
		
		// $post_id
		if( $post_id ) return get_post_type( $post_id );
		
		
		// return
		return false;
		
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
		
		// Check if this post type has templates.
		$post_templates = acf_get_post_templates();
		if( !isset($post_templates[ $post_type ]) ) {
			return false;
		}
		
		// get page template allowing for screen or database value.
		$page_template = acf_maybe_get( $screen, 'page_template' );
		$post_id = acf_maybe_get( $screen, 'post_id' );
		if( $page_template === null ) {
			$page_template = get_post_meta( $post_id, '_wp_page_template', true );
		}
		
		// Treat empty value as default template.
		if( $page_template === '' ) {
			$page_template = 'default';
		}
		
		// Compare.
		return $this->compare( $page_template, $rule );
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
		
		// Merge in all post templates.
		$post_templates = acf_get_post_templates();
		$choices = array_merge($choices, $post_templates);
				
		// Return choices.
		return $choices;
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post_template' );

endif; // class_exists check

?>