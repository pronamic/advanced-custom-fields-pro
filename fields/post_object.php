<?php

/*
*  ACF Post Object Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_post_object
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_post_object') ) :

class acf_field_post_object extends acf_field {
	
	
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
		$this->name = 'post_object';
		$this->label = __("Post Object",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 0,
			'return_format'	=> 'object',
			'ui'			=> 1,
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/post_object/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/post_object/query',	array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
		
	}
	
	
	/*
	*  get_choices
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_choices( $options = array() ) {
		
		// defaults
   		$options = acf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1
		));
		
		
		// vars
   		$r = array();
   		$args = array();
   		
		
		// paged
   		$args['posts_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
   		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		
		// bail early if no field
		if( !$field ) return false;
		
		
		// update $args
		if( !empty($field['post_type']) ) {
		
			$args['post_type'] = acf_get_array( $field['post_type'] );
			
		} else {
			
			$args['post_type'] = acf_get_post_types();
			
		}

		
		// create tax queries
		if( !empty($field['taxonomy']) ) {
			
			// append to $args
			$args['tax_query'] = array();
			
			
			// decode terms
			$taxonomies = acf_decode_taxonomy_terms( $field['taxonomy'] );
			
			
			// now create the tax queries
			foreach( $taxonomies as $taxonomy => $terms ) {
			
				$args['tax_query'][] = array(
					'taxonomy'	=> $taxonomy,
					'field'		=> 'slug',
					'terms'		=> $terms,
				);
				
			}
			
		}
		
		
		// search
		if( $options['s'] ) {
		
			$args['s'] = $options['s'];
			
		}
		
		
		// filters
		$args = apply_filters('acf/fields/post_object/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/post_object/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/post_object/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// get posts grouped by post type
		$groups = acf_get_grouped_posts( $args );
		
		if( !empty($groups) ) {
			
			foreach( array_keys($groups) as $group_title ) {
				
				// vars
				$posts = acf_extract_var( $groups, $group_title );
				$titles = array();
				
				
				// data
				$data = array(
					'text'		=> $group_title,
					'children'	=> array()
				);
				
				
				foreach( array_keys($posts) as $post_id ) {
					
					// override data
					$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'] );
					
				};
				
				
				// order by search
				if( !empty($args['s']) ) {
					
					$posts = acf_order_by_search( $posts, $args['s'] );
					
				}
				
				
				// append to $data
				foreach( array_keys($posts) as $post_id ) {
					
					$data['children'][] = array(
						'id'	=> $post_id,
						'text'	=> $posts[ $post_id ]
					);
					
				}
				
				
				// append to $r
				$r[] = $data;
				
			}
			
			
			// optgroup or single
			if( count($args['post_type']) == 1 ) {
				
				$r = $r[0]['children'];
				
			}
			
		}
		
		
		// return
		return $r;
		
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) {
		
			die();
			
		}
		
		
		// get choices
		$choices = $this->get_choices( $_POST );
		
		
		// validate
		if( !$choices ) {
			
			die();
			
		}
		
		
		// return JSON
		echo json_encode( $choices );
		die();
			
	}
	
	
	/*
	*  get_post_title
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_post_title( $post, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id ) {
			
			$form_data = acf_get_setting('form_data');
			
			if( !empty($form_data['post_id']) ) {
				
				$post_id = $form_data['post_id'];
				
			} else {
				
				$post_id = get_the_ID();
				
			}
			
		}
		
		
		// vars
		$title = acf_get_post_title( $post );
			
		
		// filters
		$title = apply_filters('acf/fields/post_object/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/post_object/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/post_object/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
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
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices if value exists
		if( !empty($field['value']) ) {
			
			// get posts
			$posts = acf_get_posts(array(
				'post__in' => $field['value'],
				'post_type'	=> $field['post_type']
			));
			
			
			// set choices
			if( !empty($posts) ) {
				
				foreach( array_keys($posts) as $i ) {
					
					// vars
					$post = acf_extract_var( $posts, $i );
					
					
					// append to choices
					$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );
					
				}
				
			}
			
		}

		
		// render
		acf_render_field( $field );
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
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
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
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("Post Object",'acf'),
				'id'			=> __("Post ID",'acf'),
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
		if( $value === 'null' ) {
		
			return false;
			
		}
		
		
		// return
		return $value;
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
		
		// bail early if no value
		if( empty($value) ) {
			
			return $value;
		
		}
		
		
		// force value to array
		$value = acf_get_array( $value );
		
		
		// convert values to int
		$value = array_map('intval', $value);
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = acf_get_posts(array(
				'post__in' => $value,
				'post_type'	=> $field['post_type']
			));
		
		}
		
		
		// convert back from array if neccessary
		if( !$field['multiple'] ) {
		
			$value = array_shift($value);
			
		}
		
		
		// return value
		return $value;
		
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
		
		
		// format
		if( is_array($value) ) {
			
			// array
			foreach( $value as $k => $v ){
			
				// object?
				if( is_object($v) && isset($v->ID) ) {
					
					$value[ $k ] = $v->ID;
				
				}
			
			}
			
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		} elseif( is_object($value) && isset($value->ID) ) {
			
			// object
			$value = $value->ID;
			
		}
		
		
		// return
		return $value;
		
	}
	
}

new acf_field_post_object();

endif;

?>
