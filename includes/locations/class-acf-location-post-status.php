<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Location_Post_Status') ) :

class ACF_Location_Post_Status extends ACF_Location {
	
	/**
	 * Initializes props.
	 *
	 * @date	5/03/2014
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
	public function initialize() {
		$this->name = 'post_status';
		$this->label = __( "Post Status", 'acf' );
		$this->category = 'post';
    	$this->object_type = 'post';
	}
	
	/**
	 * Matches the provided rule against the screen args returning a bool result.
	 *
	 * @date	9/4/20
	 * @since	5.9.0
	 *
	 * @param	array $rule The location rule.
	 * @param	array $screen The screen args.
	 * @param	array $field_group The field group settings.
	 * @return	bool
	 */
	public function match( $rule, $screen, $field_group ) {
		
		// Check screen args.
		if( isset($screen['post_status']) ) {
			$post_status = $screen['post_status'];
		} elseif( isset($screen['post_id']) ) {
			$post_status = get_post_status( $screen['post_id'] );
		} else {
			return false;
		}
		
		 // Treat "auto-draft" as "draft".
	    if( $post_status === 'auto-draft' )  {
		    $post_status = 'draft';
	    }
	    
	    // Compare rule against $post_status.
		return $this->compare_to_rule( $post_status, $rule );
	}
	
	/**
	 * Returns an array of possible values for this rule type.
	 *
	 * @date	9/4/20
	 * @since	5.9.0
	 *
	 * @param	array $rule A location rule.
	 * @return	array
	 */
	public function get_values( $rule ) {
		global $wp_post_statuses;
		
		// Append to choices.
		$choices = array();
		if( $wp_post_statuses ) {
			foreach( $wp_post_statuses as $status ) {
				$choices[ $status->name ] = $status->label;
			}
		}
		return $choices;
	}
}

// initialize
acf_register_location_type( 'ACF_Location_Post_Status' );

endif; // class_exists check
