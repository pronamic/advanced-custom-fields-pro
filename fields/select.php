<?php

/*
*  ACF Select Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_select
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_select') ) :

class acf_field_select extends acf_field {
	
	
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
		$this->name = 'select';
		$this->label = __("Select",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'choices'		=> array(),
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'disabled'		=> 0,
			'readonly'		=> 0,
		);
		$this->l10n = array(
			'matches_1'				=> _x('One result is available, press enter to select it.',	'Select2 JS matches_1',	'acf'),
			'matches_n'				=> _x('%d results are available, use up and down arrow keys to navigate.',	'Select2 JS matches_n',	'acf'),
			'matches_0'				=> _x('No matches found',	'Select2 JS matches_0',	'acf'),
			'input_too_short_1'		=> _x('Please enter 1 or more characters', 'Select2 JS input_too_short_1', 'acf' ),
			'input_too_short_n'		=> _x('Please enter %d or more characters', 'Select2 JS input_too_short_n', 'acf' ),
			'input_too_long_1'		=> _x('Please delete 1 character', 'Select2 JS input_too_long_1', 'acf' ),
			'input_too_long_n'		=> _x('Please delete %d characters', 'Select2 JS input_too_long_n', 'acf' ),
			'selection_too_long_1'	=> _x('You can only select 1 item', 'Select2 JS selection_too_long_1', 'acf' ),
			'selection_too_long_n'	=> _x('You can only select %d items', 'Select2 JS selection_too_long_n', 'acf' ),
			'load_more'				=> _x('Loading more results&hellip;', 'Select2 JS load_more', 'acf' ),
			'searching'				=> _x('Searching&hellip;', 'Select2 JS searching', 'acf' ),
			'load_fail'           	=> _x('Loading failed', 'Select2 JS load_fail', 'acf' ),
		);
		
		
		// ajax
		add_action('wp_ajax_acf/fields/select/query',				array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/select/query',		array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// globals
		global $wp_scripts, $wp_styles;
		
		
		// vars
		$version = '3.5.2';
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		
		// script
		wp_enqueue_script('select2', acf_get_dir("assets/inc/select2/select2{$min}.js"), array('jquery'), $version );
		
		
		// style
		wp_enqueue_style('select2', acf_get_dir('assets/inc/select2/select2.css'), '', $version );


		// v4
		//wp_enqueue_script('select2', acf_get_dir("assets/inc/select2/dist/js/select2.full.js"), array('jquery'), '4.0', true );
		//wp_enqueue_style('select2', acf_get_dir("assets/inc/select2/dist/css/select2{$min}.css"), '', '4.0' );
				
	}
	
	
	/*
	*  query_posts
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_query() {
		
   		// options
   		$options = acf_parse_args( $_POST, array(
			'post_id'					=>	0,
			's'							=>	'',
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field ) {
		
			die();
			
		}
		
		
		// vars
		$r = array();
		$s = false;
		
		
		// search
		if( $options['s'] !== '' ) {
			
			// search may be integer
			$s = strval($options['s']);
			
			
			// strip slashes
			$s = wp_unslash($s);
			
		}		
		
		
		// loop through choices
		if( !empty($field['choices']) ) {
		
			foreach( $field['choices'] as $k => $v ) {
				
				// if searching, but doesn't exist
				if( $s !== false && stripos($v, $s) === false ) {
				
					continue;
					
				}
				
				
				// append
				$r[] = array(
					'id'	=> $k,
					'text'	=> strval( $v )
				);
				
			}
			
		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
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
	
		// convert
		$field['value'] = acf_get_array($field['value'], false);
		$field['choices'] = acf_get_array($field['choices']);
		
		
		// placeholder
		if( empty($field['placeholder']) ) {
		
			$field['placeholder'] = __("Select",'acf');
			
		}
		
		
		// add empty value (allows '' to be selected)
		if( !count($field['value']) ) {
			
			$field['value'][''] = '';
			
		}
		
		
		// null
		if( $field['allow_null'] && !$field['multiple'] ) {
			
			$prepend = array(''	=> '- ' . $field['placeholder'] . ' -');
			$field['choices'] = $prepend + $field['choices'];
			
		}
		
		
		// vars
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> $field['class'],
			'name'				=> $field['name'],
			'data-ui'			=> $field['ui'],
			'data-ajax'			=> $field['ajax'],
			'data-multiple'		=> $field['multiple'],
			'data-placeholder'	=> $field['placeholder'],
			'data-allow_null'	=> $field['allow_null']
		);
		
		
		// multiple
		if( $field['multiple'] ) {
		
			$atts['multiple'] = 'multiple';
			$atts['size'] = 5;
			$atts['name'] .= '[]';
			
		} 
		
		
		// special atts
		foreach( array( 'readonly', 'disabled' ) as $k ) {
		
			if( !empty($field[ $k ]) ) $atts[ $k ] = $k;
			
		}
		
		
		// custom  ajax action
		if( !empty($field['ajax_action']) ) {
			
			$atts['data-ajax_action'] = $field['ajax_action'];
			
		}
		
		
		// hidden input
		if( $field['ui'] ) {
			
			$v = $field['value'];
			
			if( $field['multiple'] ) {
				
				$v = implode('||', $v);
				
			} else {
				
				$v = acf_maybe_get($v, 0, '');
				
			}
			
			acf_hidden_input(array(
				'id'	=> $field['id'] . '-input',
				'name'	=> $field['name'],
				'value'	=> $v
			));
			
		} elseif( $field['multiple'] ) {
			
			acf_hidden_input(array(
				'id'	=> $field['id'] . '-input',
				'name'	=> $field['name']
			));
			
		}
		
		
		
		// open
		echo '<select ' . acf_esc_attr($atts) . '>';	
		
		
		// walk
		$this->walk( $field['choices'], $field['value'] );
		
		
		// close
		echo '</select>';
		
	}
	
	
	/*
	*  walk
	*
	*  description
	*
	*  @type	function
	*  @date	22/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function walk( $choices, $values ) {
		
		// bail ealry if no choices
		if( empty($choices) ) return;
		
		
		// loop
		foreach( $choices as $k => $v ) {
			
			// optgroup
			if( is_array($v) ){
				
				// optgroup
				echo '<optgroup label="' . esc_attr($k) . '">';
				
				
				// walk
				$this->walk( $v, $values );
				
				
				// close optgroup
				echo '</optgroup>';
				
				
				// break
				continue;
				
			}
			
			
			// vars
			$search = html_entity_decode($k);
			$pos = array_search($search, $values);
			$atts = array( 'value' => $k );
			
			
			// validate selected
			if( $pos !== false ) {
				
				$atts['selected'] = 'selected';
				$atts['data-i'] = $pos;
				
			}
			
			
			// option
			echo '<option ' . acf_esc_attr($atts) . '>' . $v . '</option>';
			
		}
		
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
		
		// encode choices (convert from array)
		$field['choices'] = acf_encode_choices($field['choices']);
		$field['default_value'] = acf_encode_choices($field['default_value'], false);
		
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));	
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'allow_null',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'multiple',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ui',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
				
		
		// ajax
		acf_render_field_setting( $field, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ajax',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
			
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) return false;
		
		
		// return
		return $value;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field ) {
		
		// decode choices (convert to array)
		$field['choices'] = acf_decode_choices($field['choices']);
		$field['default_value'] = acf_decode_choices($field['default_value'], true);
		
		
		// return
		return $field;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// validate
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// array
		if( is_array($value) ) {
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		}
		
		
		// return
		return $value;
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
		$field['choices'] = acf_translate( $field['choices'] );
		
		
		// return
		return $field;
		
	}
	
}

new acf_field_select();

endif;

?>
