<?php 

class acf_revisions {

	/*
	*  __construct
	*
	*  A good place to add actions / filters
	*
	*  @type	function
	*  @date	11/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
		// actions		
		add_action('wp_restore_post_revision', array($this, 'wp_restore_post_revision'), 10, 2 );
		
		
		// filters
		add_filter('_wp_post_revision_fields', array($this, 'wp_preview_post_fields') );
		add_filter('_wp_post_revision_fields', array($this, 'wp_post_revision_fields') );
		add_filter('wp_save_post_revision_check_for_changes', array($this, 'force_save_revision'), 10, 3);
	}
	
	
	/*
	*  wp_preview_post_fields
	*
	*  This function is used to trick WP into thinking that one of the $post's fields has changed and
	*  will allow an autosave to be updated. 
	*  Fixes an odd bug causing the preview page to render the non autosave post data on every odd attempt
	*
	*  @type	function
	*  @date	21/10/2014
	*  @since	5.1.0
	*
	*  @param	$fields (array)
	*  @return	$fields
	*/
	
	function wp_preview_post_fields( $fields ) {
		
		// bail early if not previewing a post
		if( empty($_POST['wp-preview']) || $_POST['wp-preview'] != 'dopreview') {
			
			return $fields;
			
		}
		
		
		// add to fields if ACF has changed
		if( !empty($_POST['_acfchanged']) ) {
			
			$fields['_acfchanged'] = 'different than 1';
			
		}
		
		
		// return
		return $fields;
		
	}
	
	
	/*
	*  force_save_revision
	*
	*  This filter will return false and force WP to save a revision. This is required due to
	*  WP checking only post_title, post_excerpt and post_content values, not custom fields.
	*
	*  @type	filter
	*  @date	19/09/13
	*
	*  @param	$return (boolean) defaults to true
	*  @param	$last_revision (object) the last revision that WP will compare against
	*  @param	$post (object) the $post that WP will compare against
	*  @return	$return (boolean)
	*/
	
	function force_save_revision( $return, $last_revision, $post )
	{
		// preview hack
		if( isset($_POST['_acfchanged']) && $_POST['_acfchanged'] == '1' )
		{
			$return = false;
		}
		
		
		// return
		return $return;
	}
	
	
	/*
	*  wp_post_revision_fields
	*
	*  This filter will add the ACF fields to the returned array
	*  Versions 3.5 and 3.6 of WP feature different uses of the revisions filters, so there are
	*  some hacks to allow both versions to work correctly
	*
	*  @type	filter
	*  @date	11/08/13
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function wp_post_revision_fields( $return ) {
		
		
		//globals
		global $post, $pagenow;
		

		// validate
		$allowed = false;
		
		
		// Normal revisions page
		if( $pagenow == 'revision.php' )
		{
			$allowed = true;
		}
		
		
		// WP 3.6 AJAX revision
		if( $pagenow == 'admin-ajax.php' && isset($_POST['action']) && $_POST['action'] == 'get-revision-diffs' )
		{
			$allowed = true;
		}
		
		
		// bail
		if( !$allowed ) 
		{
			return $return;
		}
		
		
		// vars
		$post_id = 0;
		
		
		// determine $post_id
		if( isset($_POST['post_id']) )
		{
			$post_id = $_POST['post_id'];
		}
		elseif( isset($post->ID) )
		{
			$post_id = $post->ID;
		}
		else
		{
			return $return;
		}
		
		
		// setup global array
		$GLOBALS['acf_revisions_fields'] = array();
		
		
		// get field objects
		$custom_fields = get_post_custom( $post_id );
		
		
		// populate vars
		if( !empty($custom_fields) )
		{
			foreach( $custom_fields as $k => $v )
			{
				// value is always an array
				$v = $v[0];
				
				
				// bail early if $value is not is a field_key
				if( !acf_is_field_key($v) )
				{
					continue;
				}
				
				
				// remove prefix '_' field from reference
				$field_name = substr($k, 1);
				
				
				// get field
				//$field = acf_get_field($v);
				
				
				// append to return
				$return[ $field_name ] = $field_name;
				
				
				// load value
				add_filter("_wp_post_revision_field_{$field_name}", array($this, 'wp_post_revision_field'), 10, 4);
				
				
				// WP 3.5: left vs right
				// Add a value of the revision ID (as there is no way to determine this within the '_wp_post_revision_field_' filter!)
				if( isset($_GET['action'], $_GET['left'], $_GET['right']) && $_GET['action'] == 'diff' )
				{
					global $left_revision, $right_revision;
					
					$left_revision->$field_name = 'revision_id=' . $_GET['left'];
					$right_revision->$field_name = 'revision_id=' . $_GET['right'];
				}
				
			}
		}
				
		
		return $return;
	
	}
	
	
	/*
	*  wp_post_revision_field
	*
	*  This filter will load the value for the given field and return it for rendering
	*
	*  @type	filter
	*  @date	11/08/13
	*
	*  @param	$value (mixed) should be false as it has not yet been loaded
	*  @param	$field_name (string) The name of the field
	*  @param	$post (mixed) Holds the $post object to load from - in WP 3.5, this is not passed!
	*  @param	$direction (string) to / from - not used
	*  @return	$value (string)
	*/
	
	function wp_post_revision_field( $value, $field_name, $post = null, $direction = false)
	{
		// vars
		$post_id = 0;
		
		
		// determine $post_id
		if( isset($post->ID) )
		{
			// WP 3.6
			$post_id = $post->ID;
		}
		elseif( isset($_GET['revision']) )
		{
			// WP 3.5
			$post_id = (int) $_GET['revision'];
		}
		elseif( strpos($value, 'revision_id=') !== false )
		{
			// WP 3.5 (left vs right)
			$post_id = (int) str_replace('revision_id=', '', $value);
		}
		
		
		// load field
		$field = acf_maybe_get_field( $field_name, $post_id );
		
		
		// update value
		//$value = $field['value'];
		
		
		// default formatting
		if( is_array($value) ) {
			
			$value = implode(', ', $value);
			
		}
		
		
		// format
		if( !empty($value) )
		{
			// image || file?
			if( $field['type'] == 'image' || $field['type'] == 'file' )
			{
				$url = wp_get_attachment_url($value);
				$value = $value . ' (' . $url . ')';
			}
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  wp_restore_post_revision
	*
	*  This action will copy and paste the metadata from a revision to the post
	*
	*  @type	action
	*  @date	11/08/13
	*
	*  @param	$parent_id (int) the destination post
	*  @return	$revision_id (int) the source post
	*/
	
	function wp_restore_post_revision( $post_id, $revision_id ) {
	
		// global
		global $wpdb;
		
		
		// get field objects
		$custom_fields = get_post_custom( $revision_id );
		
		
		// populate vars
		if( !empty($custom_fields) )
		{
			foreach( $custom_fields as $k => $v )
			{
				// value is always an array
				$v = $v[0];
				
				
				// bail early if $value is not is a field_key
				if( !acf_is_field_key($v) )
				{
					continue;
				}
				
				
				// remove prefix '_' field from reference
				$field_name = substr($k, 1);
				
				
				// bail early if value could not be found
				if( !isset($custom_fields[ $field_name ][0]) )
				{
					continue;
				}
				
				
				update_post_meta( $post_id, $field_name, $custom_fields[ $field_name ][0] );
				
				
			}
		}
		
		
			
	}
	
			
}

new acf_revisions();

?>
