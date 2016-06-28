<?php

/*
*  ACF Page Link Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_page_link
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_page_link') ) :

class acf_field_page_link extends acf_field {
	
	
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
		$this->name = 'page_link';
		$this->label = __("Page Link",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 0,
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/page_link/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/page_link/query',		array($this, 'ajax_query'));
		
		
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
			'lang'			=> false,
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
		$args = apply_filters('acf/fields/page_link/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/page_link/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/page_link/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// is search
		$is_search = !empty( $args['s'] );
		
		
		// add archives to $r
		if( $args['paged'] == 1 ) {
			
			$archives = array();
			$archives[] = array(
				'id'	=> home_url(),
				'text'	=> home_url()
			);
			
			foreach( $args['post_type'] as $post_type ) {
				
				$archive_link = get_post_type_archive_link( $post_type );
				
				if( $archive_link ) {
				
					$archives[] = array(
						'id'	=> $archive_link,
						'text'	=> $archive_link
					);
					
				}
				
			}
			
			
			// search
			if( $is_search ) {
				
				foreach( array_keys($archives) as $i ) {
					
					if( strpos( $archives[$i]['text'], $args['s'] ) === false ) {
						
						unset($archives[$i]);
						
					}
					
				}
				
				$archives = array_values($archives);
				
			}
			
			
			if( !empty($archives) ) {
				
				$r[] = array(
					'text'		=> __('Archives', 'acf'),
					'children'	=> $archives
				);
				
			}
			
		}
		
		
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
				if( $is_search ) {
					
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
	
	function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {
		
		// get post_id
		if( !$post_id ) $post_id = acf_get_form_data('post_id');
		
		
		// vars
		$title = acf_get_post_title( $post, $is_search );
			
		
		// filters
		$title = apply_filters('acf/fields/page_link/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/page_link/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/page_link/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  get_posts
	*
	*  This function will return an array of posts for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/
	
	function get_posts( $value, $field ) {
		
		// force value to array
		$value = acf_get_array( $value );
		
		
		// get selected post ID's
		$post__in = array();
		
		foreach( $value as $k => $v ) {
			
			if( is_numeric($v) ) {
				
				// append to $post__in
				$post__in[] = (int) $v;
				
			}
			
		}
		
		
		// bail early if no posts
		if( empty($post__in) ) {
			
			return $value;
			
		}
		
		
		// get posts
		$posts = acf_get_posts(array(
			'post__in' => $post__in,
			'post_type'	=> $field['post_type']
		));
		
		
		// override value with post
		$return = array();
		
		
		// append to $return
		foreach( $value as $k => $v ) {
			
			if( is_numeric($v) ) {
				
				// extract first post
				$post = array_shift( $posts );
				
				
				// append
				if( $post ) {
					
					$return[] = $post;
					
				}
				
			} else {
				
				$return[] = $v;
				
			}
			
		}
		
		
		// return
		return $return;
		
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
	
	function render_field( $field ){
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices if value exists
		if( !empty($field['value']) ) {
			
			// get posts
			$posts = $this->get_posts( $field['value'], $field );
			
			
			// set choices
			if( !empty($posts) ) {
				
				foreach( array_keys($posts) as $i ) {
					
					// vars
					$post = acf_extract_var( $posts, $i );
					
					
					if( is_object($post) ) {
						
						// append to choices
						$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );
					
					} else {
						
						// append to choices
						$field['choices'][ $post ] = $post;
												
					}
					
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
		
		// post_type
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
		
		
		// taxonomy
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
		
		// ACF4 null
		if( $value === 'null' ) {
		
			return false;
			
		}
		
		
		// bail early if no value
		if( empty($value) ) {
			
			return $value;
		
		}
		
		
		// get posts
		$value = $this->get_posts( $value, $field );
		
		
		// set choices
		foreach( array_keys($value) as $i ) {
			
			// vars
			$post = acf_extract_var( $value, $i );
			
			
			// convert $post to permalink
			if( is_object($post) ) {
				
				$post = get_permalink( $post );
			
			}
			
			
			// append back to $value
			$value[ $i ] = $post;
			
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
				if( is_object($v) && isset($v->ID) )
				{
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

new acf_field_page_link();

endif;

?>
