<?php 

/*
*  acf_get_field_reference()
*
*  This function will find the $field_key that is related to the $field_name.
*  This is know as the field value reference
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$field_name (mixed) the name of the field. eg 'sub_heading'
*  @param	$post_id (int) the post_id of which the value is saved against
*  @return	$reference (string)	a string containing the field_key
*/

function acf_get_field_reference( $field_name, $post_id ) {
	
	return acf_get_metadata( $post_id, $field_name, true );
	
}


/*
*  the_field()
*
*  This function is the same as echo get_field().
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	n/a
*/

function the_field( $selector, $post_id = false, $format_value = true ) {
	
	$value = get_field($selector, $post_id, $format_value);
	
	if( is_array($value) ) {
		
		$value = @implode( ', ', $value );
		
	}
	
	echo $value;
	
}


/*
*  get_field()
*
*  This function will return a custom field value for a specific field name/key + post_id.
*  There is a 3rd parameter to turn on/off formating. This means that an image field will not use 
*  its 'return option' to format the value but return only what was saved in the database
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the value as described above
*  @return	(mixed)
*/
 
function get_field( $selector, $post_id = false, $format_value = true ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// create dummy field
	if( !$field ) {
		
		$field = acf_get_valid_field(array(
			'name'	=> $selector,
			'key'	=> '',
			'type'	=> '',
		));
		
		
		// prevent formatting
		$format_value = false;
		
	}
	
	
	// get value for field
	$value = acf_get_value( $post_id, $field );
	
	
	// format value
	if( $format_value ) {
		
		// get value for field
		$value = acf_format_value( $value, $post_id, $field );
		
	}
	
	
	// return
	return $value;
	 
}


/*
*  get_field_object()
*
*  This function will return an array containing all the field data for a given field_name
*
*  @type	function
*  @since	3.6
*  @date	3/02/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	$field (array)
*/

function get_field_object( $selector, $post_id = false, $format_value = true, $load_value = true ) {
	
	// compatibilty
	if( is_array($format_value) ) {
		
		extract( $format_value );
		
	}
	
	
	// get valid post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field key
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field found
	if( !$field ) {
		
		return false;
		
	}
	
	
	// load value
	if( $load_value ) {
	
		$field['value'] = acf_get_value( $post_id, $field );
		
	}
	
	
	// format value
	if( $format_value ) {
		
		// get value for field
		$field['value'] = acf_format_value( $field['value'], $post_id, $field );
		
	}
	
	
	// return
	return $field;
	
}


/*
*  get_fields()
*
*  This function will return an array containing all the custom field values for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(array)	associative array where field name => field value
*/

function get_fields( $post_id = false, $format_value = true ) {
	
	// vars
	$fields = get_field_objects( $post_id, $format_value );
	$return = array();
	
	
	// populate
	if( is_array($fields) ) {
		
		foreach( $fields as $k => $field ) {
		
			$return[ $k ] = $field['value'];
			
		}
		
	}
	
	
	// return
	return $return;	
}


/*
*  get_field_objects()
*
*  This function will return an array containing all the custom field objects for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the fields / values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	(array)	associative array where field name => field
*/

function get_field_objects( $post_id = false, $format_value = true, $load_value = true ) {
	
	// global
	global $wpdb;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );


	// vars
	$meta = array();
	$fields = array();
	
				
	// get field_names
	if( is_numeric($post_id) ) {
		
		$meta = get_post_meta( $post_id );
	
	} elseif( strpos($post_id, 'user_') !== false ) {
		
		$user_id = (int) str_replace('user_', '', $post_id);
		
		$meta = get_user_meta( $user_id );
		
	} elseif( strpos($post_id, 'comment_') !== false ) {
		
		$comment_id = (int) str_replace('comment_', '', $post_id);
		
		$meta = get_comment_meta( $comment_id );
		
	} else {
		
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			$post_id . '_%' ,
			'_' . $post_id . '_%' 
		), ARRAY_A);
		
		if( !empty($rows) ) {
			
			foreach( $rows as $row ) {
				
				$meta[ $row['option_name'] ][] = $row['option_value'];
				
			}
			
		}
		
	}
	
	
	// bail early if no meta
	if( empty($meta) ) {
		
		return false;
		
	}
	
	
	// populate vars
	foreach( $meta as $k => $v ) {
		
		// Hopefuly improve efficiency: bail early if $k does start with an '_'
		if( $k[0] === '_' ) {
			
			continue;
			
		}
		
		
		// does a field key exist for this value?
		if( !array_key_exists("_{$k}", $meta) ) {
			
			continue;
			
		}
		
		
		// get field
		$field_key = $meta["_{$k}"][0];
		$field = acf_get_field( $field_key );
		
		
		// bail early if not a parent field
		if( !$field || acf_is_sub_field($field) ) {
			
			continue;
			
		}
		
		
		// load value
		if( $load_value ) {
		
			$field['value'] = acf_get_value( $post_id, $field );
			
		}
		
		
		// format value
		if( $format_value ) {
			
			// get value for field
			$field['value'] = acf_format_value( $field['value'], $post_id, $field );
			
		}
		
					
		// append to $value
		$fields[ $field['name'] ] = $field;
		
	}
 	
 	 	
	// no value
	if( empty($fields) ) {
	
		return false;
	
	}
	
	
	// return
	return $fields;
}


/*
*  have_rows
*
*  This function will instantiate a global variable containing the rows of a repeater or flexible content field,
*  afterwhich, it will determine if another row exists to loop through
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function have_rows( $selector, $post_id = false ) {
	
	// vars
	$row = array();
	$new_parent_loop = false;
	$new_child_loop = false;
	$sub_field = false;
	$sub_exists = false;
	
	
	// reference
	$_post_id = $post_id;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// empty?
	if( empty($GLOBALS['acf_field']) ) {
		
		// reset
		reset_rows( true );
		
		
		// create a new loop
		$new_parent_loop = true;
	
	} else {
		
		// vars
		$row = end( $GLOBALS['acf_field'] );
		$prev = prev( $GLOBALS['acf_field'] );
		$change = false;
		
		
		// detect change
		if( $post_id != $row['post_id'] ) {
			
			$change = 'post_id';
				
		} elseif( $selector != $row['selector'] ) {
			
			$change = 'selector';
				
		}
		
		
		// attempt to find sub field
		if( $change ) {
			
			$sub_field = acf_get_sub_field($selector, $row['field']);
			
			if( $sub_field ) {
				
				$sub_exists = isset($row['value'][ $row['i'] ][ $sub_field['key'] ]);
				
			}
			
		}
		
		
		// If post_id has changed, this is most likely an archive loop
		if( $change == 'post_id' ) {
			
			if( empty($_post_id) && $sub_exists ) {
				
				// case: Change in $post_id was due to this being a nested loop and not specifying the $post_id
				// action: move down one level into a new loop
				$new_child_loop = true;
			
			} elseif( $prev && $prev['post_id'] == $post_id ) {
				
				// case: Change in $post_id was due to a nested loop ending
				// action: move up one level through the loops
				reset_rows();
			
			} else {
				
				// case: Chang in $post_id is the most obvious, used in an WP_Query loop with multiple $post objects
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
				
			}
			
		} elseif( $change == 'selector' ) {
			
			if( $prev && $prev['selector'] == $selector && $prev['post_id'] == $post_id ) {
				
				// case: Change in $field_name was due to a nested loop ending
				// action: move up one level through the loops
				reset_rows();
				
			} elseif( $sub_exists ) {
				
				// case: Change in $field_name was due to this being a nested loop
				// action: move down one level into a new loop
				$new_child_loop = true;
				
			} else {
				
				// case: Chang in $field_name is the most obvious, this is a new loop for a different field within the $post
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
				
			}
			
		}
		
	}
	
	
	if( $new_parent_loop ) {
		
		// vars
		$field = get_field_object( $selector, $post_id, false );
		$value = acf_extract_var( $field, 'value' );
		
		
		// add row
		$GLOBALS['acf_field'][] = array(
			'selector'	=> $selector,
			'name'		=> $field['name'], // used by update_sub_field
			'value'		=> $value,
			'field'		=> $field,
			'i'			=> -1,
			'post_id'	=> $post_id,
		);
		
	} elseif( $new_child_loop ) {
		
		// vars
		$value = $row['value'][ $row['i'] ][ $sub_field['key'] ];
		
		$GLOBALS['acf_field'][] = array(
			'selector'	=> $selector,
			'name'		=> $row['name'] . '_' . $row['i'], // used by update_sub_field
			'value'		=> $value,
			'field'		=> $sub_field,
			'i'			=> -1,
			'post_id'	=> $post_id,
		);
		
	}	
	
	
	// update vars
	$row = end( $GLOBALS['acf_field'] );
	
	
	
	// return true if next row exists
	if( is_array($row['value']) && array_key_exists($row['i']+1, $row['value']) ) {
		
		return true;
		
	}
	
	
	// no next row!
	reset_rows();
	
	
	// return
	return false;
  
}


/*
*  the_row
*
*  This function will progress the global repeater or flexible content value 1 row
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(array) the current row data
*/

function the_row( $format = false ) {
	
	// vars
	$depth = count($GLOBALS['acf_field']) - 1;

	
	// increase i of current row
	$GLOBALS['acf_field'][ $depth ]['i']++;
	
	
	// return
	return get_row( $format );
	
}

function get_row( $format = false ) {
	
	// vars
	$row = acf_get_row();
	
	
	// bail early if no row
	if( !$row ) {
		
		return false;
		
	}
	
	
	// get value
	$value = $row['value'][ $row['i'] ];
	
	
	// format
	if( $format ) {
		
		// temp wrap value in array
		$value = array( $value );
		
		// format the value (1 row of data)
		$value = acf_format_value( $value, $row['post_id'], $row['field'] );
		
		// extract value from array
		$value = $value[0];
		
	}
	
	
	// return
	return $value;
	
}

function acf_get_row() {
	
	// check and return row
	if( !empty($GLOBALS['acf_field']) ) {
		
		return end( $GLOBALS['acf_field'] );
		
	}
	
	
	// return
	return false;
	
}

function get_row_index() {
	
	// vars
	$row = acf_get_row();
	
	
	// bail early if no row
	if( !$row ) return 0;
	
	
	// return
	return $row['i'] + 1;
	
}


/*
*  reset_rows
*
*  This function will find the current loop and unset it from the global array.
*  To bo used when loop finishes or a break is used
*
*  @type	function
*  @date	26/10/13
*  @since	5.0.0
*
*  @param	$hard_reset (boolean) completely wipe the global variable, or just unset the active row
*  @return	(boolean)
*/

function reset_rows( $hard_reset = false ) {
	
	// completely destroy?
	if( $hard_reset ) {
		
		$GLOBALS['acf_field'] = array();
	
	
	// reset current row	
	} else {
		
		// vars
		$depth = count( $GLOBALS['acf_field'] ) - 1;
		
		
		// remove
		unset( $GLOBALS['acf_field'][$depth] );
		
		
		// refresh index
		$GLOBALS['acf_field'] = array_values($GLOBALS['acf_field']);
	}
	
	
	// return
	return true;
	
	
}


/*
*  has_sub_field()
*
*  This function is used inside a while loop to return either true or false (loop again or stop).
*  When using a repeater or flexible content field, it will loop through the rows until 
*  there are none left or a break is detected
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function has_sub_field( $field_name, $post_id = false ) {
	
	// vars
	$r = have_rows( $field_name, $post_id );
	
	
	// if has rows, progress through 1 row for the while loop to work
	if( $r ) {
		
		the_row();
		
	}
	
	
	// return
	return $r;
	
}

function has_sub_fields( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}


/*
*  get_sub_field()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field value
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	(mixed)
*/

function get_sub_field( $selector, $format_value = true ) {
	
	// vars
	$row = acf_get_row();
	
	
	// bail early if no row
	if( !$row ) {
		
		return false;
		
	}
	
	
	// attempt to find sub field
	$sub_field = acf_get_sub_field($selector, $row['field']);
	
	
	// update selector
	if( $sub_field ) {
		
		$selector = $sub_field['key'];
		
	} else {
		
		$format_value = false;
		
	}
	
	
	// return value
	if( isset($row['value'][ $row['i'] ][ $selector ]) ) {
		
		// get
		$value = $row['value'][ $row['i'] ][ $selector ];
		
		
		// format
		if( $format_value ) {
			
			$value = acf_format_value( $value, $row['post_id'], $sub_field );
			
		}
		
		
		// return 
		return $value;
		
	}
	
	
	// return false
	return false;
}


/*
*  the_sub_field()
*
*  This function is the same as echo get_sub_field
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	n/a
*/

function the_sub_field( $field_name, $format_value = true ) {
	
	$value = get_sub_field( $field_name, $format_value );
	
	if( is_array($value) ) {
		
		$value = implode(', ',$value);
		
	}
	
	echo $value;
}


/*
*  get_sub_field_object()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field object
*
*  @type	function
*  @since	3.5.8.1
*  @date	29/01/13
*
*  @param	$child_name (string) the field name
*  @return	(array)	
*/

function get_sub_field_object( $selector, $format_value = true, $load_value = true ) {
	
	// vars
	$row = acf_get_row();
	
	
	// bail early if no row
	if( !$row ) {
		
		return false;
		
	}

	
	// vars
	$parent = $row['field'];

	
	// get sub field
	$sub_field = acf_get_sub_field( $selector, $parent );
	
	
	// bail early if no sub field
	if( !$sub_field ) {
		
		return false;
		
	}
	
	
	// load value
	if( $load_value ) {
	
		$sub_field['value'] = get_sub_field( $sub_field['name'], $format_value );
		
	}
	
	
	// return
	return $sub_field;
	
}


/*
*  get_row_layout()
*
*  This function will return a string representation of the current row layout within a 'have_rows' loop
*
*  @type	function
*  @since	3.0.6
*  @date	29/01/13
*
*  @param	n/a
*  @return	(string)
*/

function get_row_layout() {
	
	// vars
	$row = get_row();
	
	
	// return
	if( isset($row['acf_fc_layout']) ) {
		
		return $row['acf_fc_layout'];
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_shortcode()
*
*  This function is used to add basic shortcode support for the ACF plugin
*  eg. [acf field="heading" post_id="123" format_value="1"]
*
*  @type	function
*  @since	1.1.1
*  @date	29/01/13
*
*  @param	$field (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(string)
*/

function acf_shortcode( $atts )
{
	// extract attributs
	extract( shortcode_atts( array(
		'field'			=> '',
		'post_id'		=> false,
		'format_value'	=> true
	), $atts ) );
	
	
	// get value and return it
	$value = get_field( $field, $post_id, $format_value );
	
	
	if( is_array($value) )
	{
		$value = @implode( ', ', $value );
	}
	
	
	return $value;
}
add_shortcode( 'acf', 'acf_shortcode' );


/*
*  acf_form_head()
*
*  This function is placed at the very top of a template (before any HTML is rendered) and saves the $_POST data sent by acf_form.
*
*  @type	function
*  @since	1.1.4
*  @date	29/01/13
*
*  @param	n/a
*  @return	n/a
*/

function acf_form_head() {
	
	// verify nonce
	if( acf_verify_nonce('acf_form') ) {
		
		// validate data
	    if( acf_validate_save_post(true) ) {
	    	
	    	// form
	    	$GLOBALS['acf_form'] = acf_extract_var($_POST, '_acf_form');
	    	$GLOBALS['acf_form'] = @json_decode(base64_decode($GLOBALS['acf_form']), true);
	    	
	    	
	    	// validate
	    	if( empty($GLOBALS['acf_form']) ) return;
	    	
	    	
	    	// vars
	    	$post_id = acf_maybe_get( $GLOBALS['acf_form'], 'post_id', 0 );
			
			
			// allow for custom save
			$post_id = apply_filters('acf/pre_save_post', $post_id, $GLOBALS['acf_form']);
			
			
			// save
			acf_save_post( $post_id );
			
			
			// vars
			$return = acf_maybe_get( $GLOBALS['acf_form'], 'return', '' );
			
			
			// redirect
			if( $return ) {
				
				// update %placeholders%
				$return = str_replace('%post_url%', get_permalink($post_id), $return);
				
				
				// redirect
				wp_redirect( $return );
				exit;
			}
			
		}
		// if
		
	}
	// if
	
	
	// load acf scripts
	acf_enqueue_scripts();
	
}


/*
*  _validate_save_post
*
*  description
*
*  @type	function
*  @date	16/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

add_action('acf/validate_save_post', '_validate_save_post');

function _validate_save_post() {
	
	// save post_title
	if( isset($_POST['acf']['_post_title']) ) {
		
		// get field
		$field = acf_get_valid_field(array(
			'name'		=> '_post_title',
			'label'		=> 'Title',
			'type'		=> 'text',
			'required'	=> true
		));
		
		
		// validate
		acf_validate_value( $_POST['acf']['_post_title'], $field, "acf[_post_title]" );
	
	}
	
	
	// honeypot
	if( !empty($_POST['acf']['_validate_email']) ) {
		
		acf_add_validation_error( '', __('Spam Detected', 'acf') );
		
	}
	
}


/*
*  _acf_pre_save_post
*
*  This filter will save post data for the acf_form function
*
*  @type	filter
*  @date	17/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

add_filter('acf/pre_save_post', '_acf_pre_save_post', 5, 2);

function _acf_pre_save_post( $post_id, $form ) {
	
	// vars
	$save = array(
		'ID' => 0
	);
	
	
	// determine save data
	if( is_numeric($post_id) ) {
		
		// update post
		$save['ID'] = $post_id;
		
	} elseif( $post_id == 'new_post' ) {
		
		// new post
		$form['new_post'] = acf_parse_args( $form['new_post'], array(
			'post_type' 	=> 'post',
			'post_status'	=> 'draft',
		));
		
		
		// merge in new post data
		$save = array_merge($save, $form['new_post']);
				
	} else {
		
		// not post
		return $post_id;
		
	}
	
	
	// save post_title
	if( isset($_POST['acf']['_post_title']) ) {
		
		$save['post_title'] = acf_extract_var($_POST['acf'], '_post_title');
	
	}
	
	
	// save post_content
	if( isset($_POST['acf']['_post_content']) ) {
		
		$save['post_content'] = acf_extract_var($_POST['acf'], '_post_content');
		
	}
	
	
	// honeypot
	if( !empty($_POST['acf']['_validate_email']) ) return;
	
	
	// validate
	if( count($save) == 1 ) {
		
		return $post_id;
		
	}
	
	
	if( $save['ID'] ) {
		
		wp_update_post( $save );
		
	} else {
		
		$post_id = wp_insert_post( $save );
		
	}
		
	
	// return
	return $post_id;
	
}


/*
*  acf_form()
*
*  This function is used to create an ACF form.
*
*  @type	function
*  @since	1.1.4
*  @date	29/01/13
*
*  @param	array		$options: an array containing many options to customize the form
*			string		+ post_id: post id to get field groups from and save data to. Default is false
*			array		+ field_groups: an array containing field group ID's. If this option is set, 
*						  the post_id will not be used to dynamically find the field groups
*			boolean		+ form: display the form tag or not. Defaults to true
*			array		+ form_attributes: an array containg attributes which will be added into the form tag
*			string		+ return: the return URL
*			string		+ html_before_fields: html inside form before fields
*			string		+ html_after_fields: html inside form after fields
*			string		+ submit_value: value of submit button
*			string		+ updated_message: default updated message. Can be false					 
*
*  @return	N/A
*/

function acf_form( $args = array() ) {
	
	// vars
	$url = acf_get_current_url();
	
	
	// defaults
	$args = wp_parse_args( $args, array(
		'id'					=> 'acf-form',
		'post_id'				=> false,
		'new_post'				=> false,
		'field_groups'			=> false,
		'fields'				=> false,
		'post_title'			=> false,
		'post_content'			=> false,
		'form'					=> true,
		'form_attributes'		=> array(),
		'return'				=> add_query_arg( 'updated', 'true', $url ),
		'html_before_fields'	=> '',
		'html_after_fields'		=> '',
		'submit_value'			=> __("Update", 'acf'),
		'updated_message'		=> __("Post updated", 'acf'),
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label',
		'field_el'				=> 'div',
		'uploader'				=> 'wp',
		'honeypot'				=> true
	));
	
	$args['form_attributes'] = wp_parse_args( $args['form_attributes'], array(
		'id'					=> 'post',
		'class'					=> '',
		'action'				=> '',
		'method'				=> 'post',
	));
	
	
	// filter post_id
	$args['post_id'] = acf_get_valid_post_id( $args['post_id'] );
	
	
	// load values from this post
	$post_id = $args['post_id'];
	
	
	// new post?
	if( $post_id == 'new_post' ) {
		
		// dont load values
		$post_id = false;
		
		
		// new post defaults
		$args['new_post'] = acf_parse_args( $args['new_post'], array(
			'post_type' 	=> 'post',
			'post_status'	=> 'draft',
		));
		
	}
	
	
	// attributes
	$args['form_attributes']['class'] .= ' acf-form';
	
	
	// vars
	$field_groups = array();
	$fields = array();
	
	
	// post_title
	if( $args['post_title'] ) {
		
		$fields[] = acf_get_valid_field(array(
			'name'		=> '_post_title',
			'label'		=> 'Title',
			'type'		=> 'text',
			'value'		=> $post_id ? get_post_field('post_title', $post_id) : '',
			'required'	=> true
		));
		
	}
	
	
	// post_content
	if( $args['post_content'] ) {
		
		$fields[] = acf_get_valid_field(array(
			'name'		=> '_post_content',
			'label'		=> 'Content',
			'type'		=> 'wysiwyg',
			'value'		=> $post_id ? get_post_field('post_content', $post_id) : ''
		));
		
	}
	
	
	// specific fields
	if( $args['fields'] ) {
		
		foreach( $args['fields'] as $selector ) {
			
			// append field ($strict = false to allow for better compatibility with field names)
			$fields[] = acf_maybe_get_field( $selector, $post_id, false );
			
		}
		
	} elseif( $args['field_groups'] ) {
		
		foreach( $args['field_groups'] as $selector ) {
		
			$field_groups[] = acf_get_field_group( $selector );
			
		}
		
	} elseif( $args['post_id'] == 'new_post' ) {
		
		$field_groups = acf_get_field_groups( $args['new_post'] );
	
	} else {
		
		$field_groups = acf_get_field_groups(array(
			'post_id' => $args['post_id']
		));
		
	}
	
	
	//load fields based on field groups
	if( !empty($field_groups) ) {
		
		foreach( $field_groups as $field_group ) {
			
			$field_group_fields = acf_get_fields( $field_group );
			
			if( !empty($field_group_fields) ) {
				
				foreach( array_keys($field_group_fields) as $i ) {
					
					$fields[] = acf_extract_var($field_group_fields, $i);
				}
				
			}
		
		}
	
	}
	
	
	// honeypot
	if( $args['honeypot'] ) {
		
		$fields[] = acf_get_valid_field(array(
			'name'		=> '_validate_email',
			'label'		=> 'Validate Email',
			'type'		=> 'text',
			'value'		=> '',
			'wrapper'	=> array(
				'style'	=> 'display:none;'
			)
		));
		
	}
	
	
	// updated message
	if( !empty($_GET['updated']) && $args['updated_message'] ) {
	
		echo '<div id="message" class="updated"><p>' . $args['updated_message'] . '</p></div>';
		
	}
	
	
	// uploader (always set incase of multiple forms on the page)
	acf_update_setting('uploader', $args['uploader']);
	
	
	// display form
	if( $args['form'] ): ?>
	
	<form <?php acf_esc_attr_e( $args['form_attributes']); ?>>
	
	<?php endif; 
		
		
	// render post data
	acf_form_data(array( 
		'post_id'	=> $args['post_id'], 
		'nonce'		=> 'acf_form' 
	));
	
	?>
	<div class="acf-hidden">
		<?php acf_hidden_input(array( 'name' => '_acf_form', 'value' => base64_encode(json_encode($args)) )); ?>
	</div>
	<div class="acf-fields acf-form-fields -<?php echo $args['label_placement']; ?>">
	
		<?php
		
		// html before fields
		echo $args['html_before_fields'];
		
		
		// render
		acf_render_fields( $post_id, $fields, $args['field_el'], $args['instruction_placement'] );
		
		
		// html after fields
		echo $args['html_after_fields'];
		
		?>
	
	</div><!-- acf-form-fields -->
	<?php if( $args['form'] ): ?>
	
	<!-- Submit -->
	<div class="acf-form-submit">
	
		<input type="submit" class="acf-button button button-primary button-large" value="<?php echo $args['submit_value']; ?>" />
		<span class="acf-spinner"></span>
		
	</div>
	<!-- / Submit -->
	
	</form>
	<?php endif;
}


/*
*  update_field()
*
*  This function will update a value in the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function update_field( $selector, $value, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// create dummy field
	if( !$field )
	{
		$field = acf_get_valid_field(array(
			'name'	=> $selector,
			'key'	=> '',
			'type'	=> '',
		));
	}
	
	
	// save
	return acf_update_value( $value, $post_id, $field );
		
}


/*
*  update_sub_field
*
*  This function will update a value of a sub field in the database
*
*  @type	function
*  @date	2/04/2014
*  @since	5.0.0
*
*  @param	$selector (mixed) the sub field name or key, or an array of ancestors
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function update_sub_field( $selector, $value, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// vars
	$field = false;
	
	
	// within a have_rows loop
	if( is_string($selector) ) {
		
		// get current row
		$row = acf_get_row();
		
		
		// override $post_id
		$post_id = $row['post_id'];
		
		
		// get sub field
		$field = get_sub_field_object( $selector, false, false );
		
		
		// create dummy field
		if( !$field ) {
		
			$field = acf_get_valid_field(array(
				'name'	=> $selector,
				'key'	=> '',
				'type'	=> '',
			));
			
		}
		
		
		// update name
		$field['name'] = "{$row['name']}_{$row['i']}_{$field['name']}";
		
		
	} elseif( is_array($selector) ) {
		
		// validate
		if( count($selector) < 3 ) {
			
			return false;
			
		}
		
		
		// vars
		$parent_name = acf_extract_var( $selector, 0 );
		
		
		// load parent
		$field = acf_maybe_get_field( $parent_name, $post_id );
		
		
		// add to name
		$name = $field['name'];
		
		
		// sub fields
		foreach( $selector as $s ) {
				
			if( is_numeric($s) ) {
				
				// get row index
				$row_i = intval($s) - 1;
				
				// add to name
				$name .= "_{$row_i}";
				
			} else {
				
				// update parent
				$field = acf_get_sub_field( $s, $field );
				
				
				// create dummy field
				if( !$field ) {
				
					$field = acf_get_valid_field(array(
						'name'	=> $s,
						'key'	=> '',
						'type'	=> '',
					));
					
				}
				
				
				// add to name
				$name .= "_{$field['name']}";
				
			}
			// if
			
		}
		// foreach
		
		
		// update name
		$field['name'] = $name;
				
				
	}
	
	
	// delete
	if( $value === null ) {
		
		return acf_delete_value( $post_id, $field );
		
	}
	
	
	// update
	return acf_update_value( $value, $post_id, $field );
		
}


/*
*  delete_field()
*
*  This function will remove a value from the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function delete_field( $selector, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// delete
	return acf_delete_value( $post_id, $field );
	
}


/*
*  delete_sub_field
*
*  This function will delete a value of a sub field in the database
*
*  @type	function
*  @date	2/04/2014
*  @since	5.0.0
*
*  @param	$selector (mixed) the sub field name or key, or an array of ancestors
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function delete_sub_field( $selector, $post_id = false ) {
	
	return update_sub_field( $selector, null, $post_id );
		
}


/*
*  add_row
*
*  description
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function add_row( $selector, $value, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field
	if( !$field ) {
		
		return false;
		
	}
	
	
	// get row count
	$i = (int) acf_get_metadata( $post_id, $field['name'] );
	
	
	// if no rows, save this field via update_field() so that the reference field is created
	if( !$i ) {
		
		// acf_update_value will return boolean, simply convert this to int for 1 | 0 (the number of rows!)
		return (int) acf_update_value( array( $value ), $post_id, $field );
		
	}
	
	
	// increase $i
	$i++;
	
	
	// update meta
	$result = acf_update_metadata( $post_id, $field['name'], $i );
	
	
	// update sub fields
	if( $value ) {
		
		foreach( $value as $k => $v ) {
		
			update_sub_field( array( $field['key'], $i, $k ), $v, $post_id );
			
		}
	
	}
	
	
	// return
	return $i;
	
}


/*
*  update_row
*
*  description
*
*  @type	function
*  @date	19/10/2015
*  @since	5.2.3
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function update_row( $selector, $row = 1, $value = false, $post_id = false ) {
	
	// bail early if no value
	if( empty($value) ) {
		
		return false;
		
	}
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field
	if( !$field ) return false;
	
	
	// update sub fields
	foreach( $value as $k => $v ) {
		
		update_sub_field( array( $field['key'], $row, $k ), $v, $post_id );
		
	}
	
	
	// return
	return true;
	
}


/*
*  delete_row
*
*  description
*
*  @type	function
*  @date	19/10/2015
*  @since	5.2.3
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function delete_row( $selector, $row = 1, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field
	if( !$field ) return false;
	
	
	// get value
	$rows = acf_get_value( $post_id, $field );
	
	
	// bail early if no value
	if( empty($rows) ) return false;
	
	
	// deincrement
	if( $row == count($rows) ) {
		
		acf_update_metadata( $post_id, $field['name'], $row-1 );
		
	}
	
	
	// update sub field values
	foreach( $rows[0] as $k => $v ) {
		
		update_sub_field( array( $field['key'], $row, $k ), null, $post_id );
		
	}
	
	
	// return
	return true;
	
}


/*
*  create_field()
*
*  This function will creat the HTML for a field
*
*  @type	function
*  @since	4.0.0
*  @date	17/03/13
*
*  @param	array	$field - an array containing all the field attributes
*
*  @return	N/A
*/

function create_field( $field ) {

	acf_render_field( $field );
}

function render_field( $field ) {

	acf_render_field( $field );
}


/*
*  acf_convert_field_names_to_keys()
*
*  Helper for the update_field function
*
*  @type	function
*  @since	4.0.0
*  @date	17/03/13
*
*  @param	array	$value: the value returned via get_field
*  @param	array	$field: the field or layout to find sub fields from
*
*  @return	N/A
*/

function acf_convert_field_names_to_keys( $value, $field ) {
	
	// only if $field has sub fields
	if( !isset($field['sub_fields']) ) {
		
		return $value;
		
	}
	

	// define sub field keys
	$sub_fields = array();
	if( $field['sub_fields'] ) {
		
		foreach( $field['sub_fields'] as $sub_field ) {
			
			$sub_fields[ $sub_field['name'] ] = $sub_field;
			
		}
		
	}
	
	
	// loop through the values and format the array to use sub field keys
	if( is_array($value) ) {
		
		foreach( $value as $row_i => $row) {
			
			if( $row ) {
				
				foreach( $row as $sub_field_name => $sub_field_value ) {
					
					// sub field must exist!
					if( !isset($sub_fields[ $sub_field_name ]) ) {
						
						continue;
						
					}
					
					
					// vars
					$sub_field = $sub_fields[ $sub_field_name ];
					$sub_field_value = acf_convert_field_names_to_keys( $sub_field_value, $sub_field );
					
					
					// set new value
					$value[$row_i][ $sub_field['key'] ] = $sub_field_value;
					
					
					// unset old value
					unset( $value[$row_i][$sub_field_name] );
						
					
				}
				// foreach( $row as $sub_field_name => $sub_field_value )
				
			}
			// if( $row )
			
		}
		// foreach( $value as $row_i => $row)
		
	}
	// if( $value )
	
	
	// return
	return $value;

}


/*
*  register_field_group
*
*  description
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function register_field_group( $field_group ) {
	
	// add local
	acf_add_local_field_group( $field_group );
	
}


/*
*  Depreceated Functions
*
*  These functions are outdated
*
*  @type	function
*  @date	4/03/2014
*  @since	1.0.0
*
*  @param	n/a
*  @return	n/a
*/

function reset_the_repeater_field() {
	
	return reset_rows();
	
}

function the_repeater_field( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}

function the_flexible_field( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}

function acf_filter_post_id( $post_id ) {
	
	return acf_get_valid_post_id( $post_id );
	
}

?>
