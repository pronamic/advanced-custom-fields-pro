<?php

/*
*  ACF True / False Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_true_false
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_true_false') ) :

class acf_field_true_false extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->name = 'true_false';
		$this->label = __("True / False",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'default_value'	=> 0,
			'message'		=> '',
		);
		
		
		// do not delete!
    	parent::__construct();
  
	}
		
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$atts = array(
			'type'		=> 'checkbox',
			'id'		=> "{$field['id']}-1",
			'name'		=> $field['name'],
			'value'		=> '1',
		);
		
		
		// checked
		if( !empty($field['value']) ) {
		
			$atts['checked'] = 'checked';
			
		}
		
		
		// html
		echo '<ul class="acf-checkbox-list acf-bl ' . acf_esc_attr($field['class']) . '">';
			echo '<input type="hidden" name="' . acf_esc_attr($field['name']) . '" value="0" />';
			echo '<li><label><input ' . acf_esc_attr($atts) . '/>' . $field['message'] . '</label></li>';
		echo '</ul>';
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// message
		acf_render_field_setting( $field, array(
			'label'			=> __('Message','acf'),
			'instructions'	=> __('eg. Show extra content','acf'),
			'type'			=> 'text',
			'name'			=> 'message',
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'default_value',
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		return empty($value) ? false : true;
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// bail early if not required
		if( ! $field['required'] ) {
			
			return $valid;
			
		}
		
		
		// value may be '0'
		if( !$value ) {
			
			return false;
			
		}
		
		
		// return
		return $valid;
				
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @type	function
	*  @date	8/03/2016
	*  @since	5.3.2
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		// translate
		$field['message'] = acf_translate( $field['message'] );
		
		
		// return
		return $field;
		
	}
	
}


// initialize
acf_register_field_type( new acf_field_true_false() );

endif; // class_exists check

?>
