<?php

/*
*  acf_get_metadata
*
*  This function will get a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (mixed)
*/

function acf_get_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$value = null;
	
	
	// bail early if no $post_id (acf_form - new_post)
	if( !$post_id ) return $value;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// post
	if( is_numeric($post_id) ) {
		
		$meta = get_metadata( 'post', $post_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	
	// user
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$meta = get_metadata( 'user', $user_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	
	// comment
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$meta = get_metadata( 'comment', $comment_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	 	
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$value = get_option( $post_id . '_' . $name, null );
		
	}
		
	
	// return
	return $value;
	
}


/*
*  acf_update_metadata
*
*  This function will update a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$value (mixed)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_update_metadata( $post_id = 0, $name = '', $value = '', $hidden = false ) {
	
	// vars
	$return = false;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// postmeta
	if( is_numeric($post_id) ) {
		
		$return = update_metadata('post', $post_id, $name, $value );
	
	// usermeta
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$return = update_metadata('user', $user_id, $name, $value);
		
	// commentmeta
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$return = update_metadata('comment', $comment_id, $name, $value);
	
	// options	
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$return = acf_update_option( $post_id . '_' . $name, $value );
		
	}
	
	
	// return
	return (boolean) $return;
	
}


/*
*  acf_delete_metadata
*
*  This function will delete a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_delete_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$return = false;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// postmeta
	if( is_numeric($post_id) ) {
		
		$return = delete_metadata('post', $post_id, $name );
	
	// usermeta
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$return = delete_metadata('user', $user_id, $name);
		
	// commentmeta
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$return = delete_metadata('comment', $comment_id, $name);
	
	// options	
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$return = delete_option( $post_id . '_' . $name );
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_update_option
*
*  This function is a wrapper for the WP update_option but provides logic for a 'no' autoload
*
*  @type	function
*  @date	4/01/2014
*  @since	5.0.0
*
*  @param	$option (string)
*  @param	$value (mixed)
*  @param	autoload (mixed)
*  @return	(boolean)
*/

function acf_update_option( $option = '', $value = '', $autoload = null ) {
	
	// vars
	$deprecated = '';
	$return = false;
	
	
	// autoload
	if( $autoload === null ){
		
		$autoload = acf_get_setting('autoload') ? 'yes' : 'no';
		
	}
	
	
	// for some reason, update_option does not use stripslashes_deep.
	// update_metadata -> https://core.trac.wordpress.org/browser/tags/3.4.2/wp-includes/meta.php#L82: line 101 (does use stripslashes_deep)
	// update_option -> https://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/option.php#L0: line 215 (does not use stripslashes_deep)
	$value = stripslashes_deep($value);
		
		
	// add or update
	if( get_option($option) !== false ) {
	
	    $return = update_option( $option, $value );
	    
	} else {
		
		$return = add_option( $option, $value, $deprecated, $autoload );
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_get_value
*
*  This function will load in a field's value
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @param	$field (array)
*  @return	(mixed)
*/

function acf_get_value( $post_id = 0, $field ) {
	
	// cache
	$found = false;
	$cache_slug = "load_value/post_id={$post_id}/name={$field['name']}";
	$cache = wp_cache_get($cache_slug, 'acf', false, $found);
	
	
	// return cache if found
	if( $found ) return $cache;
	
	
	// load value
	$value = acf_get_metadata( $post_id, $field['name'] );
	
	
	// if value was duplicated, it may now be a serialized string!
	$value = maybe_unserialize( $value );
	
	
	// no value? try default_value
	if( $value === null && isset($field['default_value']) ) {
		
		$value = $field['default_value'];
		
	}
	
	
	// filter for 3rd party customization
	$value = apply_filters( "acf/load_value", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// update cache
	wp_cache_set($cache_slug, $value, 'acf');

	
	// return
	return $value;
	
}


/*
*  acf_format_value
*
*  This function will format the value for front end use
*
*  @type	function
*  @date	3/07/2014
*  @since	5.0.0
*
*  @param	$value (mixed)
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	$value
*/

function acf_format_value( $value, $post_id, $field ) {
	
	// try cache
	$found = false;
	$cache_slug = "format_value/post_id={$post_id}/name={$field['name']}";
	$cache = wp_cache_get($cache_slug, 'acf', false, $found);
	
	
	// return cache if found
	if( $found ) return $cache;
	
	
	// apply filters
	$value = apply_filters( "acf/format_value", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// update cache
	wp_cache_set($cache_slug, $value, 'acf');
	
	
	// return
	return $value;
	
} 


/*
*  acf_update_value
*
*  updates a value into the db
*
*  @type	action
*  @date	23/01/13
*
*  @param	$value (mixed)
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	(boolean)
*/

function acf_update_value( $value = null, $post_id = 0, $field ) {
	
	// strip slashes
	if( acf_get_setting('stripslashes') ) {
		
		$value = stripslashes_deep($value);
		
	}
	
	
	// filter for 3rd party customization
	$value = apply_filters( "acf/update_value", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/name={$field['name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/key={$field['key']}", $value, $post_id, $field );
	

	// update value
	$return = acf_update_metadata( $post_id, $field['name'], $value );
	
	
	// update reference
	acf_update_metadata( $post_id, $field['name'], $field['key'], true );
	
	
	// clear cache
	wp_cache_delete( "load_value/post_id={$post_id}/name={$field['name']}", 'acf' );
	wp_cache_delete( "format_value/post_id={$post_id}/name={$field['name']}", 'acf' );

	
	// return
	return $return;
	
}


/*
*  acf_delete_value
*
*  This function will delete a value from the database
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	(boolean)
*/

function acf_delete_value( $post_id = 0, $field ) {
	
	// action for 3rd party customization
	do_action("acf/delete_value", $post_id, $field['name'], $field);
	do_action("acf/delete_value/type={$field['type']}", $post_id, $field['name'], $field);
	do_action("acf/delete_value/name={$field['_name']}", $post_id, $field['name'], $field);
	do_action("acf/delete_value/key={$field['key']}", $post_id, $field['name'], $field);
	
	
	// delete value
	$return = acf_delete_metadata( $post_id, $field['name'] );
	
	
	// delete reference
	acf_delete_metadata( $post_id, $field['name'], true );
	
	
	// clear cache
	wp_cache_delete( "load_value/post_id={$post_id}/name={$field['name']}", 'acf' );
	wp_cache_delete( "format_value/post_id={$post_id}/name={$field['name']}", 'acf' );
	
	
	// return
	return $return;
	
}

?>
