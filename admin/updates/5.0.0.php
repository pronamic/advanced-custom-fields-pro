<?php 

/*
*  Upgrade to version 5.0.0
*
*  @type	upgrade
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

// Exit if accessed directly
if( !defined('ABSPATH') ) exit;


// global
global $wpdb;


// migrate field groups
$ofgs = get_posts(array(
	'numberposts' 		=> -1,
	'post_type' 		=> 'acf',
	'orderby' 			=> 'menu_order title',
	'order' 			=> 'asc',
	'suppress_filters'	=> true,
));


// populate acfs
if( $ofgs ){ foreach( $ofgs as $ofg ){
	
	// migrate field group
	$nfg = _migrate_field_group_500( $ofg );
	
	
	// get field from postmeta
	$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $ofg->ID, 'field_%'), ARRAY_A);
	
	
	if( $rows )
	{
		$nfg['fields'] = array();
		
		foreach( $rows as $row )
		{
			$field = $row['meta_value'];
			$field = maybe_unserialize( $field );
			$field = maybe_unserialize( $field ); // run again for WPML
			
			
			// add parent
			$field['parent'] = $nfg['ID'];
			
			
			// migrate field
			$field = _migrate_field_500( $field );
		}
 	}
 		
}}


/*
*  _migrate_field_group_500
*
*  description
*
*  @type	function
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function _migrate_field_group_500( $ofg ) {
	
	// global
	global $wpdb;
	
	
	// get post status
	$post_status = $ofg->post_status;
	
	
	// create new field group
	$nfg = array(
		'ID'			=> 0,
		'title'			=> $ofg->post_title,
		'menu_order'	=> $ofg->menu_order,
	);
	
	
	// location rules
	$groups = array();
	
	
	// get all rules
 	$rules = get_post_meta($ofg->ID, 'rule', false);
 	
 	if( is_array($rules) ) {
 	
 		$group_no = 0;
 		
	 	foreach( $rules as $rule ) {
	 		
	 		// if field group was duplicated, it may now be a serialized string!
	 		$rule = maybe_unserialize($rule);
	 		
	 		
		 	// does this rule have a group?
		 	// + groups were added in 4.0.4
		 	if( !isset($rule['group_no']) ) {
		 	
			 	$rule['group_no'] = $group_no;
			 	
			 	// sperate groups?
			 	if( get_post_meta($ofg->ID, 'allorany', true) == 'any' ) {
			 	
				 	$group_no++;
				 	
			 	}
			 	
		 	}
		 	
		 	
		 	// extract vars
		 	$group = acf_extract_var( $rule, 'group_no' );
		 	$order = acf_extract_var( $rule, 'order_no' );
		 	
		 	
		 	// add to group
		 	$groups[ $group ][ $order ] = $rule;
		 	
		 	
		 	// sort rules
		 	ksort( $groups[ $group ] );
 	
	 	}
	 	
	 	// sort groups
		ksort( $groups );
 	}
 	
 	$nfg['location'] = $groups;
 	
 	
	// settings
 	if( $position = get_post_meta($ofg->ID, 'position', true) ) {
 	
		$nfg['position'] = $position;
		
	}
	
 	if( $layout = get_post_meta($ofg->ID, 'layout', true) ) {
 	
		$nfg['layout'] = $layout;
		
	}
	
 	if( $hide_on_screen = get_post_meta($ofg->ID, 'hide_on_screen', true) ) {
 	
		$nfg['hide_on_screen'] = maybe_unserialize($hide_on_screen);
		
	}
	
	
	// Note: acf_update_field_group will call the acf_get_valid_field_group function and apply 'compatibility' changes
	
	
	// add old ID reference
	$nfg['old_ID'] = $ofg->ID;
	
	
	// save field group
	$nfg = acf_update_field_group( $nfg );
	
	
	// trash?
	if( $post_status == 'trash' ) {
		
		acf_trash_field_group( $nfg['ID'] );
		
	}
	
	
	// return
	return $nfg;
}


/*
*  _migrate_field_500
*
*  description
*
*  @type	function
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function _migrate_field_500( $field ) {
	
	// orig
	$orig = $field;
	
	
	// order_no is now menu_order
	$field['menu_order'] = acf_extract_var( $field, 'order_no' );
	
	
	// correct very old field keys
	if( substr($field['key'], 0, 6) !== 'field_' ) {
	
		$field['key'] = 'field_' . str_replace('field', '', $field['key']);
		
	}
	
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// save field
	$field = acf_update_field( $field );
	
	
	// sub fields
	if( $field['type'] == 'repeater' ) {
		
		// get sub fields
		$sub_fields = acf_extract_var( $orig, 'sub_fields' );
		
		
		// save sub fields
		if( !empty($sub_fields) ) {
			
			$keys = array_keys($sub_fields);
		
			foreach( $keys as $key ) {
			
				$sub_field = acf_extract_var($sub_fields, $key);
				$sub_field['parent'] = $field['ID'];
				
				_migrate_field_500( $sub_field );
				
			}
			
		}
		
	
	} elseif( $field['type'] == 'flexible_content' ) {
		
		// get layouts
		$layouts = acf_extract_var( $orig, 'layouts' );
		
		
		// update layouts
		$field['layouts'] = array();
		
		
		// save sub fields
		if( !empty($layouts) ) {
			
			foreach( $layouts as $layout ) {
				
				// vars
				$layout_key = uniqid();
				
				
				// append layotu key
				$layout['key'] = $layout_key;
				
				
				// extract sub fields
				$sub_fields = acf_extract_var($layout, 'sub_fields');
				
				
				// save sub fields
				if( !empty($sub_fields) ) {
					
					$keys = array_keys($sub_fields);
					
					foreach( $keys as $key ) {
					
						$sub_field = acf_extract_var($sub_fields, $key);
						$sub_field['parent'] = $field['ID'];
						$sub_field['parent_layout'] = $layout_key;
						
						_migrate_field_500( $sub_field );
						
					}
					// foreach
					
				}
				// if
				
				
				// append layout
				$field['layouts'][] = $layout;
			
			}
			// foreach
			
		}
		// if
		
		
		// save field again with less sub field data
		$field = acf_update_field( $field );
		
	}
	
	
	// return
	return $field;
}

?>
