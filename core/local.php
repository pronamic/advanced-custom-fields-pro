<?php 

class acf_local {
	
	// vars
	var $enabled	= true,
		$groups 	= array(),
		$fields 	= array(),
		$parents 	= array();
		
		
	function __construct() {
		
		add_filter('acf/get_field_groups', array($this, 'get_field_groups'), 10, 1);
		add_action('acf/delete_field', 	array($this, 'delete_field'), 10, 1);
		
	}
	
	
	/*
	*  get_field_groups
	*
	*  This function will override and add field groups to the `acf_get_field_groups()` results
	*
	*  @type	filter (acf/get_field_groups)
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$field_groups (array)
	*  @return	$field_groups
	*/
	
	function get_field_groups( $field_groups ) {
		
		// validate
		if( !acf_have_local_field_groups() ) {
			
			return $field_groups;
			
		}
		
		
		// vars
		$ignore = array();
		$added = false;
		
		
		// populate ignore list
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $k => $group ) {

				$ignore[] = $group['key'];
				
			}
			
		}
		
		
		// append field groups
		$groups = acf_get_local_field_groups();
		
		foreach( $groups as $group ) {
			
			// is ignore
			if( in_array($group['key'], $ignore) ) {
				
				continue;
					
			}
			
			
			// append
			$field_groups[] = $group;
			$added = true;
			
		}
		
		
		// order field groups based on menu_order, title
		if( $added ) {
			
			$menu_order = array();
			$title = array();
			
			foreach( $field_groups as $key => $row ) {
				
			    $menu_order[ $key ] = $row['menu_order'];
			    $title[ $key ] = $row['title'];
			}
			
			
			// sort the array with menu_order ascending
			array_multisort( $menu_order, SORT_ASC, $title, SORT_ASC, $field_groups );
				
		}
		
		
		// return
		return $field_groups;
		
	}
	
	
	/*
	*  delete_field
	*
	*  description
	*
	*  @type	function
	*  @date	10/12/2014
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_field( $field ) {
		
		$this->remove_field( $field['key'] );
		
	}
	
	
	/*
	*  add_field_group
	*
	*  This function will add a $field group to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function add_field_group( $field_group ) {
		
		// validate
		$field_group = acf_get_valid_field_group($field_group);
		
		
		// don't allow overrides
		if( acf_is_local_field_group($field_group['key']) ) {
			
			return;	
			
		}
		
		
		// add local
		if( empty($field_group['local']) ) {
			
			$field_group['local'] = 'php';
			
		}
		
		
		// remove fields
		$fields = acf_extract_var($field_group, 'fields');
		
		
		// format fields
		$fields = acf_prepare_fields_for_import( $fields );
		
		
		// add field group
		$this->groups[ $field_group['key'] ] = $field_group;
		
		
		// add fields
		foreach( $fields as $field ) {
			
			// add parent
			if( empty($field['parent']) ) {
				
				$field['parent'] = $field_group['key'];
				
			}
			
			
			// add field
			$this->add_field( $field );
			
		}
		
	}
	
	
	/*
	*  add_field
	*
	*  This function will add a $field to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	n/a
	*/
	
	function add_field( $field ) {
		
		// vars
		// - allow for the very unexpected case where no key or parent exist
		$key = acf_maybe_get($field, 'key', '');
		$parent = acf_maybe_get($field, 'parent', '');
		
		
		// add parent reference
		$this->add_parent_reference( $parent, $key );
		
		
		// add in menu order
		$field['menu_order'] = count( $this->parents[ $parent ] ) - 1;
		
		
		// add field
		$this->fields[ $key ] = $field;
		
		
		// clear cache
		// - delete cache was origional added to ensure changes to JSON / PHP would appear in WP when using memcache
		// - the downside is that wp_cache_delet is taxing on the system so has been commented out
		//wp_cache_delete( "get_field/key={$key}", 'acf' );
		//wp_cache_delete( "get_fields/parent={$parent}", 'acf' );
		
	}
	
	
	/*
	*  remove_field
	*
	*  This function will remove a $field to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	n/a
	*/
	
	function remove_field( $key ) {
		
		// get field
		$field = acf_get_field( $key );
		
		
		// remove parent reference
		$this->remove_parent_reference( $field['parent'], $field['key'] );
		
		
		// remove field
		unset( $this->fields[ $key ] );
		
		
		// remove children
		if( acf_have_local_fields( $key) ) {
			
			acf_remove_local_fields( $key );
			
		}
		
	}
	
	
	function add_parent_reference( $parent_key, $field_key ) {
		
		// create array
		if( !isset($this->parents[ $parent_key ]) ) {
			
			$this->parents[ $parent_key ] = array();
			
		} elseif( in_array($field_key, $this->parents[ $parent_key ]) ) {
			
			// bail early if already in array
			return false;
			
		}
		
		
		// append
		$this->parents[ $parent_key ][] = $field_key;
		
		
		// return
		return true;
		
	}
	
	
	function remove_parent_reference( $parent_key, $field_key ) {
		
		// bail early if no parent
		if( !isset($this->parents[ $parent_key ]) ) {
			
			return false;
			
		}
		
		
		// remove
		$this->parents[ $parent_key ] = array_diff($this->parents[ $parent_key ], array($field_key));
		
		
		// return
		return true;
	}

	
}


/*
*  acf_local
*
*  This function will return the one true acf_local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	acf_local (object)
*/

function acf_local() {
	
	// globals
	global $acf_local;
	
	
	// instantiate
	if( !isset($acf_local) )
	{
		$acf_local = new acf_local();
	}
	
	
	// return
	return $acf_local;
}


/*
*  acf_disable_local
*
*  This function will disable the local functionality for DB only interaction
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_disable_local() {
	
	acf_local()->enabled = false;
	
}


/*
*  acf_enable_local
*
*  This function will enable the local functionality
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enable_local() {
	
	acf_local()->enabled = true;
	
}


/*
*  acf_reset_local
*
*  This function will remove (reset) all field group and fields
*
*  @type	function
*  @date	2/06/2016
*  @since	5.3.8
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_reset_local() {
	
	// vars
	acf_local()->groups = array();
	acf_local()->fields = array();
	acf_local()->parents = array();
	
}


/*
*  acf_is_local_enabled
*
*  This function will return true|false if the local functionality is enabled
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_is_local_enabled() {
	
	// validate
	if( !acf_get_setting('local') ) {
		
		return false;
		
	}
	
	
	if( !acf_local()->enabled ) {
		
		return false;
		
	}
	
	
	// return
	return true;
	
}


/*
*  acf_count_local_field_groups
*
*  This function will return the number of local field groups
*
*  @type	function
*  @date	3/12/2014
*  @since	5.1.5
*
*  @param	$type (string) specify the type. eg. 'json'
*  @return	(int)
*/

function acf_count_local_field_groups( $type = '' ) {
	
	// vars
	$count = 0;
	
	
	// check for groups
	if( !empty(acf_local()->groups) ) {
		
		// acf_local
		foreach( acf_local()->groups as $group ) {
			
			// ignore if not specific type
			if( $type && $group['local'] != $type ) {
				
				continue;
				
			}
			
			$count++;
			
		}
		
	}
	
	
	// return
	return $count;
	
}


/*
*  acf_have_local_field_groups
*
*  This function will return true if fields exist for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(bolean)
*/

function acf_have_local_field_groups() {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check for groups
	if( !empty(acf_local()->groups) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_groups
*
*  This function will return an array of fields for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field_groups() {
	
	// bail early if no groups
	if( !acf_have_local_field_groups() ) {
		
		return false;
		
	}
	
	
	// vars
	$groups = array();
	
	
	// acf_local
	foreach( acf_local()->groups as $group ) {
		
		$groups[] = $group;
		
	}
	
	
	// return
	return $groups;
	
}


/*
*  acf_add_local_field_group
*
*  This function will add a $field group to the local placeholder
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_local_field_group( $field_group ) {
	
	acf_local()->add_field_group( $field_group );
	
}


/*
*  acf_is_local_field_group
*
*  This function will return true if the field group has been added as local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_is_local_field_group( $key ) {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check groups
	if( isset( acf_local()->groups[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_group
*
*  This function will return a local field group for a given key
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field_group( $key ) {
	
	// bail early if no group
	if( !acf_is_local_field_group($key) ) {
		
		return false;
		
	}
	
	
	// return
	return acf_local()->groups[ $key ];
	
}


/*
*  acf_add_local_field
*
*  This function will add a $field to the local placeholder
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_local_field( $field ) {
	
	acf_local()->add_field( $field );
	
}


/*
*  acf_remove_local_field
*
*  This function will remove a $field to the local placeholder
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_remove_local_field( $key ) {
	
	acf_local()->remove_field( $key );
	
}


/*
*  acf_is_local_field
*
*  This function will return true if the field has been added as local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_is_local_field( $key ) {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check fields
	if( isset( acf_local()->fields[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_group
*
*  This function will return a local field for a given key
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field( $key ) {
	
	// bail early if no field
	if( !acf_is_local_field($key) ) {
		
		return false;
		
	}
	
	
	// return
	return acf_local()->fields[ $key ];
	
}


/*
*  acf_count_local_fields
*
*  This function will return the number of local fields for a parent
*
*  @type	function
*  @date	3/12/2014
*  @since	5.1.5
*
*  @param	n/a
*  @return	(int)
*/

function acf_count_local_fields( $key ) {
	
	// check for fields
	if( !empty(acf_local()->parents[ $key ]) ) {
		
		return count( acf_local()->parents[ $key ] );
		
	}
	
	
	// return
	return 0;
	
}


/*
*  acf_have_local_fields
*
*  This function will return true if fields exist for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_have_local_fields( $key ) {

	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check parents
	if( isset( acf_local()->parents[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_fields
*
*  This function will return an array of fields for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_fields( $parent ) {
	
	// bail early if no parent
	if( !acf_have_local_fields($parent) ) {
		
		return false;
		
	}
	
	
	// vars
	$fields = array();
	
	
	// append
	foreach( acf_local()->parents[ $parent ] as $key ) {
		
		$fields[] = acf_get_field( $key );
		
	}
	
	
	// return
	return $fields;
	
}


/*
*  acf_remove_local_fields
*
*  This function will remove the field reference for a field group
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_remove_local_fields( $parent ) {
	
	// bail early if no reference
	if( empty( acf_local()->parents[ $parent ] ) ) {
		
		return false;
		
	}
	
	
	foreach( acf_local()->parents[ $parent ] as $key ) {
		
		acf_remove_local_field( $key );
	
	}
	
	
	// return
	return true;
}

?>
