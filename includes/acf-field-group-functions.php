<?php 

// Register store.
acf_register_store( 'field-groups' )->prop( 'multisite', true );

/**
 * acf_get_field_group
 *
 * Retrieves a field group for the given identifier.
 *
 * @date	30/09/13
 * @since	5.0.0
 *
 * @param	(int|string) $id The field group ID, key or name.
 * @return	(array|false) The field group array.
 */
function acf_get_field_group( $id = 0 ) {
	
	// Allow WP_Post to be passed.
	if( is_object($id) ) {
		$id = $id->ID;
	}
	
	// Check store.
	$store = acf_get_store( 'field-groups' );
	if( $store->has( $id ) ) {
		return $store->get( $id );
	}
	
	// Check local fields first.
	if( acf_is_local_field_group($id) ) {
		$field_group = acf_get_local_field_group( $id );
	
	// Then check database.
	} else {
		$field_group = acf_get_raw_field_group( $id );
	}
	
	// Bail early if no field group.
	if( !$field_group ) {
		return false;
	}
	
	// Validate field group.
	$field_group = acf_validate_field_group( $field_group );
	
	/**
	 * Filters the $field_group array after it has been loaded.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array The field_group array.
	 */
	$field_group = apply_filters( 'acf/load_field_group', $field_group );
	
	// Store field group using aliasses to also find via key, ID and name.
	$store->set( $field_group['key'], $field_group );
	$store->alias( $field_group['key'], $field_group['ID'] );
	
	// Return.
	return $field_group;
}

/**
 * acf_get_raw_field_group
 *
 * Retrieves raw field group data for the given identifier.
 *
 * @date	18/1/19
 * @since	5.7.10
 *
 * @param	(int|string) $id The field ID, key or name.
 * @return	(array|false) The field group array.
 */
function acf_get_raw_field_group( $id = 0 ) {
	
	// Get raw field group from database.
	$post = acf_get_field_group_post( $id );
	if( !$post ) {
		return false;
	}
	
	// Bail early if incorrect post type.
	if( $post->post_type !== 'acf-field-group' ) {
		return false;
	}
	
	// Unserialize post_content.
	$field_group = (array) maybe_unserialize( $post->post_content );
	
	// update attributes
	$field_group['ID'] = $post->ID;
	$field_group['title'] = $post->post_title;
	$field_group['key'] = $post->post_name;
	$field_group['menu_order'] = $post->menu_order;
	$field_group['active'] = in_array($post->post_status, array('publish', 'auto-draft'));

	// Return field.
	return $field_group;
}

/**
 * acf_get_field_group_post
 *
 * Retrieves the field group's WP_Post object.
 *
 * @date	18/1/19
 * @since	5.7.10
 *
 * @param	(int|string) $id The field group's ID, key or name.
 * @return	(array|false) The field group's array.
 */
function acf_get_field_group_post( $id = 0 ) {
	
	// Get post if numeric.
	if( is_numeric($id) ) {
		return get_post( $id );
	
	// Search posts if is string.
	} elseif( is_string($id) ) {
		
		// Try cache.
		$cache_key = acf_cache_key( "acf_get_field_group_post:key:$id" );
		$post_id = wp_cache_get( $cache_key, 'acf' );
		if( $post_id === false ) {
			
			// Query posts.
			$posts = get_posts(array(
				'posts_per_page'			=> 1,
				'post_type'					=> 'acf-field-group',
				'post_status'				=> array('publish', 'acf-disabled', 'trash'),
				'orderby' 					=> 'menu_order title',
				'order'						=> 'ASC',
				'suppress_filters'			=> false,
				'cache_results'				=> true,
				'update_post_meta_cache'	=> false,
				'update_post_term_cache'	=> false,
				'acf_group_key'				=> $id
			));
			
			// Update $post_id with a non false value.
			$post_id = $posts ? $posts[0]->ID : 0;
			
			// Update cache.
			wp_cache_set( $cache_key, $post_id, 'acf' );
		}
		
		// Check $post_id and return the post when possible.
		if( $post_id ) {
			return get_post( $post_id );
		}
	}
	
	// Return false by default.
	return false;
}

/**
 * acf_is_field_group_key
 *
 * Returns true if the given identifier is a field group key.
 *
 * @date	6/12/2013
 * @since	5.0.0
 *
 * @param	string $id The identifier.
 * @return	bool
 */
function acf_is_field_group_key( $id = '' ) {
	
	// Check if $id is a string starting with "group_".
	if( is_string($id) && substr($id, 0, 6) === 'group_' ) {
		return true;
	}
	
	/**
	 * Filters whether the $id is a field group key.
	 *
	 * @date	23/1/19
	 * @since	5.7.10
	 *
	 * @param	bool $bool The result.
	 * @param	string $id The identifier.
	 */
	return apply_filters( 'acf/is_field_group_key', false, $id );
}

/**
 * acf_validate_field_group
 *
 * Ensures the given field group is valid.
 *
 * @date	18/1/19
 * @since	5.7.10
 *
 * @param	array $field The field group array.
 * @return	array
 */
function acf_validate_field_group( $field_group = array() ) {
	
	// Bail early if already valid.
	if( is_array($field_group) && !empty($field_group['_valid']) ) {
		return $field_group;
	}
	
	// Apply defaults.
	$field_group = wp_parse_args($field_group, array(
		'ID'					=> 0,
		'key'					=> '',
		'title'					=> '',
		'fields'				=> array(),
		'location'				=> array(),
		'menu_order'			=> 0,
		'position'				=> 'normal',
		'style'					=> 'default',
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label',
		'hide_on_screen'		=> array(),
		'active'				=> true,
		'description'			=> '',
	));
	
	// Convert types.
	$field_group['ID'] = (int) $field_group['ID'];
	$field_group['menu_order'] = (int) $field_group['menu_order'];
	
	// Field group is now valid.
	$field_group['_valid'] = 1;
	
	/**
	 * Filters the $field_group array to validate settings.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field group array.
	 */
	$field_group = apply_filters( 'acf/validate_field_group', $field_group );
	
	// return
	return $field_group;
}

/**
 * acf_get_valid_field_group
 *
 * Ensures the given field group is valid.
 *
 * @date		28/09/13
 * @since		5.0.0
 *
 * @param	array $field_group The field group array.
 * @return	array
 */
function acf_get_valid_field_group( $field_group = false ) {
	return acf_validate_field_group( $field_group );
}

/**
 * acf_translate_field_group
 *
 * Translates a field group's settings.
 *
 * @date	8/03/2016
 * @since	5.3.2
 *
 * @param	array $field_group The field group array.
 * @return	array
 */
function acf_translate_field_group( $field_group = array() ) {
	
	// Get settings.
	$l10n = acf_get_setting('l10n');
	$l10n_textdomain = acf_get_setting('l10n_textdomain');
	
	// Translate field settings if textdomain is set.
	if( $l10n && $l10n_textdomain ) {
		
		$field_group['title'] = acf_translate( $field_group['title'] );
		
		/**
		 * Filters the $field group array to translate strings.
		 *
		 * @date	12/02/2014
		 * @since	5.0.0
		 *
		 * @param	array $field_group The field group array.
		 */
		$field_group = apply_filters( "acf/translate_field_group", $field_group );
	}
	
	// Return field.
	return $field_group;
}

// Translate field groups passing through validation.
add_action('acf/validate_field_group', 'acf_translate_field_group');


/**
 * acf_get_field_groups
 *
 * Returns and array of field_groups for the given $filter.
 *
 * @date	30/09/13
 * @since	5.0.0
 *
 * @param	array $filter An array of args to filter results by.
 * @return	array
 */
function acf_get_field_groups( $filter = array() ) {
	
	// Vars.
	$field_groups = array();
	
	// Check database.
	$raw_field_groups = acf_get_raw_field_groups();
	if( $raw_field_groups ) {
		foreach( $raw_field_groups as $raw_field_group ) {
			$field_groups[] = acf_get_field_group( $raw_field_group['ID'] );
		}
	}
	
	/**
	 * Filters the $field_groups array.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_groups The array of field_groups.
	 */
	$field_groups = apply_filters( 'acf/load_field_groups', $field_groups );
	
	// Filter results.
	if( $filter ) {
		return acf_filter_field_groups( $field_groups, $filter );
	}
	
	// Return field groups.
	return $field_groups;
}

/**
 * acf_get_raw_field_groups
 *
 * Returns and array of raw field_group data.
 *
 * @date	18/1/19
 * @since	5.7.10
 *
 * @param	void
 * @return	array
 */
function acf_get_raw_field_groups() {
	
	// Try cache.
	$cache_key = acf_cache_key( "acf_get_field_group_posts" );
	$post_ids = wp_cache_get( $cache_key, 'acf' );
	if( $post_ids === false ) {
		
		// Query posts.
		$posts = get_posts(array(
			'posts_per_page'			=> -1,
			'post_type'					=> 'acf-field-group',
			'orderby'					=> 'menu_order title',
			'order'						=> 'ASC',
			'suppress_filters'			=> false, // Allow WPML to modify the query
			'cache_results'				=> true,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'post_status'				=> array('publish', 'acf-disabled'),
		));
		
		// Update $post_ids with a non false value.
		$post_ids = array();
		foreach( $posts as $post ) {
			$post_ids[] = $post->ID;
		}
		
		// Update cache.
		wp_cache_set( $cache_key, $post_ids, 'acf' );
	}
	
	// Loop over ids and populate array of field groups.
	$field_groups = array();
	foreach( $post_ids as $post_id ) {
		$field_groups[] = acf_get_raw_field_group( $post_id );
	}
	
	// Return field groups.
	return $field_groups;
}

/**
 * acf_filter_field_groups
 *
 * Returns a filtered aray of field groups based on the given $args.
 *
 * @date	29/11/2013
 * @since	5.0.0
 *
 * @param	array $field_groups An array of field groups.
 * @param	array $args An array of location args.
 * @return	array
 */
function acf_filter_field_groups( $field_groups, $args = array() ) {
	
	// Loop over field groups and check visibility.
	$filtered = array();
	if( $field_groups ) {
		foreach( $field_groups as $field_group ) {
			if( acf_get_field_group_visibility( $field_group, $args ) ) {
				$filtered[] = $field_group;
			}
		}
	}
	
	// Return filtered.
	return $filtered;
}

/**
 * acf_get_field_group_visibility
 *
 * Returns true if the given field group's location rules match the given $args.
 *
 * @date	7/10/13
 * @since	5.0.0
 *
 * @param	array $field_groups An array of field groups.
 * @param	array $args An array of location args.
 * @return	bool
 */
function acf_get_field_group_visibility( $field_group, $args = array() ) {
	
	// Check if active.
	if( !$field_group['active'] ) {
		return false;
	}
	
	// Check if location rules exist
	if( $field_group['location'] ) {
		
		// Get the current screen.
		$screen = acf_get_location_screen( $args );
		
		// Loop through location groups.
		foreach( $field_group['location'] as $group ) {
			
			// ignore group if no rules.
			if( empty($group) ) {
				continue;
			}
			
			// Loop over rules and determine if all rules match.
			$match_group = true;
			foreach( $group as $rule ) {
				if( !acf_match_location_rule( $rule, $screen, $field_group ) ) {
					$match_group = false;
					break;
				}
			}
			
			// If this group matches, show the field group.
			if( $match_group ) {
				return true;
			}
		}
	}
	
	// Return default.
	return false;
}

/**
 * acf_update_field_group
 *
 * Updates a field group in the database.
 *
 * @date	21/1/19
 * @since	5.7.10
 *
 * @param	array $field_group The field group array.
 * @return	array
 */
function acf_update_field_group( $field_group ) {
	
	// Validate field group.
	$field_group = acf_get_valid_field_group( $field_group );
	
	// May have been posted. Remove slashes.
	$field_group = wp_unslash( $field_group );
	
	// Parse types (converts string '0' to int 0).
	$field_group = acf_parse_types( $field_group );
	
	// Clean up location keys.
	if( $field_group['location'] ) {
		
		// Remove empty values and convert to associated array.
		$field_group['location'] = array_filter( $field_group['location'] );
		$field_group['location'] = array_values( $field_group['location'] );
		$field_group['location'] = array_map( 'array_filter', $field_group['location'] );
		$field_group['location'] = array_map( 'array_values', $field_group['location'] );
	}
	
	// Make a backup of field group data and remove some args.
	$_field_group = $field_group;
	acf_extract_vars( $_field_group, array( 'ID', 'key', 'title', 'menu_order', 'fields', 'active', '_valid' ) );
	
	// Create array of data to save.
	$save = array(
		'ID'			=> $field_group['ID'],
    	'post_status'	=> $field_group['active'] ? 'publish' : 'acf-disabled',
    	'post_type'		=> 'acf-field-group',
    	'post_title'	=> $field_group['title'],
    	'post_name'		=> $field_group['key'],
    	'post_excerpt'	=> sanitize_title( $field_group['title'] ),
    	'post_content'	=> maybe_serialize( $_field_group ),
    	'menu_order'	=> $field_group['menu_order'],
    	'comment_status' => 'closed',
    	'ping_status'	=> 'closed',
	);
	
	// Unhook wp_targeted_link_rel() filter from WP 5.1 corrupting serialized data.
	remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
	
	// Slash data.
	// WP expects all data to be slashed and will unslash it (fixes '\' character issues).
	$save = wp_slash( $save );
	
	// Update or Insert.
	if( $field_group['ID'] ) {
		wp_update_post( $save );
	} else	{
		$field_group['ID'] = wp_insert_post( $save );
	}
	
	// Flush field group cache.
	acf_flush_field_group_cache( $field_group );	
	
	/**
	 * Fires immediately after a field group has been updated.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field group array.
	 */
	do_action( 'acf/update_field_group', $field_group );
	
	// Return field group.
	return $field_group;
}

/**
 * _acf_apply_unique_field_group_slug
 *
 * Allows full control over 'acf-field-group' slugs.
 *
 * @date	21/1/19
 * @since	5.7.10
 *
 * @param string $slug          The post slug.
 * @param int    $post_ID       Post ID.
 * @param string $post_status   The post status.
 * @param string $post_type     Post type.
 * @param int    $post_parent   Post parent ID
 * @param string $original_slug The original post slug.
 */
function _acf_apply_unique_field_group_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
	
	// Check post type and reset to original value.
	if( $post_type === 'acf-field-group' ) {
		return $original_slug;
	}
	
	// Return slug.
	return $slug;
}

// Hook into filter.
add_filter( 'wp_unique_post_slug', '_acf_apply_unique_field_group_slug', 999, 6 );

/**
 * acf_flush_field_group_cache
 *
 * Deletes all caches for this field group.
 *
 * @date	22/1/19
 * @since	5.7.10
 *
 * @param	array $field_group The field group array.
 * @return	void
 */
function acf_flush_field_group_cache( $field_group ) {
	
	// Delete stored data.
	acf_get_store( 'field-groups' )->remove( $field_group['key'] );
	
	// Flush cached post_id for this field group's key.
	wp_cache_delete( acf_cache_key( "acf_get_field_group_post:key:{$field_group['key']}" ), 'acf' );
	
	// Flush cached array of post_ids for collection of field groups.
	wp_cache_delete( acf_cache_key( "acf_get_field_group_posts" ), 'acf' );
}

/**
 * acf_delete_field_group
 *
 * Deletes a field group from the database.
 *
 * @date	21/1/19
 * @since	5.7.10
 *
 * @param	(int|string) $id The field group ID, key or name.
 * @return	bool True if field group was deleted.
 */
function acf_delete_field_group( $id = 0 ) {
	
	// Disable filters to ensure ACF loads data from DB.
	acf_disable_filters();
	
	// Get the field_group.
	$field_group = acf_get_field_group( $id );
	
	// Bail early if field group was not found.
	if( !$field_group || !$field_group['ID'] ) {
		return false;
	}
	
	// Delete fields.
	$fields = acf_get_fields( $field_group );
	if( $fields ) {
		foreach( $fields as $field ) {
			acf_delete_field( $field['ID'] );
		}
	}
	
	// Delete post.
	wp_delete_post( $field_group['ID'], true );
	
	// Flush field group cache.
	acf_flush_field_group_cache( $field_group );
	
	/**
	 * Fires immediately after a field group has been deleted.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field group array.
	 */
	do_action( 'acf/delete_field_group', $field_group );
	
	// Return true.
	return true;
}

/**
 * acf_trash_field_group
 *
 * Trashes a field group from the database.
 *
 * @date	2/10/13
 * @since	5.0.0
 *
 * @param	(int|string) $id The field group ID, key or name.
 * @return	bool True if field group was trashed.
 */
function acf_trash_field_group( $id = 0 ) {
	
	// Disable filters to ensure ACF loads data from DB.
	acf_disable_filters();
	
	// Get the field_group.
	$field_group = acf_get_field_group( $id );
	
	// Bail early if field_group was not found.
	if( !$field_group || !$field_group['ID'] ) {
		return false;
	}
	
	// Trash fields.
	$fields = acf_get_fields( $field_group );
	if( $fields ) {
		foreach( $fields as $field ) {
			acf_trash_field( $field['ID'] );
		}
	}
	
	// Trash post.
	wp_trash_post( $field_group['ID'], true );
	
	// Flush field group cache.
	acf_flush_field_group_cache( $field_group );
	
	/**
	 * Fires immediately after a field_group has been trashed.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field_group array.
	 */
	do_action( 'acf/trash_field_group', $field_group );
	
	// Return true.
	return true;
}

/**
 * acf_untrash_field_group
 *
 * Restores a field_group from the trash.
 *
 * @date	2/10/13
 * @since	5.0.0
 *
 * @param	(int|string) $id The field_group ID, key or name.
 * @return	bool True if field_group was trashed.
 */
function acf_untrash_field_group( $id = 0 ) {
	
	// Disable filters to ensure ACF loads data from DB.
	acf_disable_filters();
	
	// Get the field_group.
	$field_group = acf_get_field_group( $id );
	
	// Bail early if field_group was not found.
	if( !$field_group || !$field_group['ID'] ) {
		return false;
	}
	
	// Untrash fields.
	$fields = acf_get_fields( $field_group );
	if( $fields ) {
		foreach( $fields as $field ) {
			acf_untrash_field( $field['ID'] );
		}
	}
	
	// Untrash post.
	wp_untrash_post( $field_group['ID'], true );
	
	// Flush field group cache.
	acf_flush_field_group_cache( $field_group );
	
	/**
	 * Fires immediately after a field_group has been trashed.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field_group array.
	 */
	do_action( 'acf/untrash_field_group', $field_group );
	
	// Return true.
	return true;
}

/**
 * acf_is_field_group
 *
 * Returns true if the given params match a field group.
 *
 * @date	21/1/19
 * @since	5.7.10
 *
 * @param	array $field_group The field group array.
 * @param	mixed $id An optional identifier to search for.
 * @return	bool
 */
function acf_is_field_group( $field_group = false ) {
	return ( 
		is_array($field_group)
		&& isset($field_group['key'])
		&& isset($field_group['title'])
	);
}

/**
 * acf_duplicate_field_group
 *
 * Duplicates a field group.
 *
 * @date	16/06/2014
 * @since	5.0.0
 *
 * @param	(int|string) $id The field_group ID, key or name.
 * @param	int $new_post_id Optional post ID to override.
 * @return	array The new field group.
 */
function acf_duplicate_field_group( $id = 0, $new_post_id = 0 ){
	
	// Disable filters to ensure ACF loads data from DB.
	acf_disable_filters();
	
	// Get the field_group.
	$field_group = acf_get_field_group( $id );
	
	// Bail early if field_group was not found.
	if( !$field_group || !$field_group['ID'] ) {
		return false;
	}
	
	// Get fields.
	$fields = acf_get_fields( $field_group );
	
	// Update attributes.
	$field_group['ID'] = $new_post_id;
	$field_group['key'] = uniqid('group_');
	
	// Add (copy) to title when apropriate.
	if( !$new_post_id ) {
		$field_group['title'] .= ' (' . __("copy", 'acf') . ')';
	}
	
	// When importing a new field group, insert a temporary post and set the field group's ID.
	// This allows fields to be updated before the field group (field group ID is needed for field parent setting).
	if( !$field_group['ID'] ) {
		$field_group['ID'] = wp_insert_post( array( 'post_title' => $field_group['key'] ) );
	}
	
	// Duplicate fields.
	$duplicates = acf_duplicate_fields( $fields, $field_group['ID'] );
	
	// Save field group.
	$field_group = acf_update_field_group( $field_group );
	
	/**
	 * Fires immediately after a field_group has been duplicated.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field_group array.
	 */
	do_action( 'acf/duplicate_field_group', $field_group );
	
	// return
	return $field_group;
}

/**
 * acf_get_field_group_style
 *
 * Returns the CSS styles generated from field group settings.
 *
 * @date	20/10/13
 * @since	5.0.0
 *
 * @param	array $field_group The field group array.
 * @return	string.
 */
function acf_get_field_group_style( $field_group ) {
	
	// Vars.
	$style = '';
	$elements = array(
		'permalink'			=> '#edit-slug-box',
		'the_content'		=> '#postdivrich',
		'excerpt'			=> '#postexcerpt',
		'custom_fields'		=> '#postcustom',
		'discussion'		=> '#commentstatusdiv',
		'comments'			=> '#commentsdiv',
		'slug'				=> '#slugdiv',
		'author'			=> '#authordiv',
		'format'			=> '#formatdiv',
		'page_attributes'	=> '#pageparentdiv',
		'featured_image'	=> '#postimagediv',
		'revisions'			=> '#revisionsdiv',
		'categories'		=> '#categorydiv',
		'tags'				=> '#tagsdiv-post_tag',
		'send-trackbacks'	=> '#trackbacksdiv'
	);
	
	// Loop over field group settings and generate list of selectors to hide.
	if( is_array($field_group['hide_on_screen']) ) {
		$hide = array();
		foreach( $field_group['hide_on_screen'] as $k ) {
			if( isset($elements[ $k ]) ) {
				$id = $elements[ $k ];
				$hide[] = $id;
				$hide[] = '#screen-meta label[for=' . substr($id, 1) . '-hide]';
			}
		}
		$style = implode(', ', $hide) . ' {display: none;}';
	}
	
	/**
	 * Filters the generated CSS styles.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	string $style The CSS styles.
	 * @param	array $field_group The field group array.
	 */
	return apply_filters('acf/get_field_group_style', $style, $field_group);
}

/**
 * acf_get_field_group_edit_link
 *
 * Checks if the current user can edit the field group and returns the edit url.
 *
 * @date	23/9/18
 * @since	5.7.7
 *
 * @param	int $post_id The field group ID.
 * @return	string
 */
function acf_get_field_group_edit_link( $post_id ) {
	if( $post_id && acf_current_user_can_admin() ) {
		return admin_url('post.php?post=' . $post_id . '&action=edit');
	}
	return '';
}

/**
 * acf_prepare_field_group_for_export
 *
 * Returns a modified field group ready for export.
 *
 * @date	11/03/2014
 * @since	5.0.0
 *
 * @param	array $field_group The field group array.
 * @return	array
 */
function acf_prepare_field_group_for_export( $field_group = array() ) {

	// Remove args.
	acf_extract_vars( $field_group, array( 'ID', 'local', '_valid' ) );
	
	// Prepare fields.
	$field_group['fields'] = acf_prepare_fields_for_export( $field_group['fields'] );
	
	/**
	 * Filters the $field_group array before being returned to the export tool.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The $field group array.
	 */
	return apply_filters( 'acf/prepare_field_group_for_export', $field_group );
}

/**
 * acf_import_field_group
 *
 * Imports a field group into the databse.
 *
 * @date	11/03/2014
 * @since	5.0.0
 *
 * @param	array $field_group The field group array.
 * @return	array The new field group.
 */
function acf_import_field_group( $field_group ) {
	
	// Disable filters to ensure data is not modified by local, clone, etc.
	$filters = acf_disable_filters();
	
	// Validate field group.
	$field_group = acf_get_valid_field_group( $field_group );
	
	// Stores a map of field "key" => "ID".
	$ids = array();
	
	// Stores a map of field "parent_key" => "child_count".
	$count = array();
	
	// Prepare fields for import.
	$fields = acf_prepare_fields_for_import( $field_group['fields'] );
	
	// If the field group has an ID, review and delete stale fields in the databse. 
	if( $field_group['ID'] ) {
		
		// Load database fields.
		$db_fields = acf_get_fields( $field_group );
		$db_fields = acf_prepare_fields_for_import( $db_fields );
		
		// Generate map of "index" => "key" data.
		$keys = wp_list_pluck( $fields, 'key' );
		
		// Loop over db fields and delete those who don't exist in $new_fields.
		foreach( $db_fields as $field ) {
			
			// Add field data to $ids map.
			$ids[ $field['key'] ] = $field['ID'];
			
			// Delete field if not in $keys.
			if( !in_array($field['key'], $keys) ) {
				acf_delete_field( $field['ID'] );
			}
		}
	}
	
	// When importing a new field group, insert a temporary post and set the field group's ID.
	// This allows fields to be updated before the field group (field group ID is needed for field parent setting).
	if( !$field_group['ID'] ) {
		$field_group['ID'] = wp_insert_post( array( 'post_title' => $field_group['key'] ) );
	}
	
	// Add field group data to $ids map.
	$ids[ $field_group['key'] ] = $field_group['ID'];
	
	// Add count to map.
	$count[ $field_group['ID'] ] = 0;
	
	// Loop over and add fields.
	if( $fields ) {
		foreach( $fields as $field ) {
			
			// Check $ids map for existing ID for this key.
			if( isset($ids[ $field['key'] ]) ) {
				$field['ID'] = $ids[ $field['key'] ];	
			}
			
			// Add field group as parent.
			if( empty($field['parent']) ) {
				$field['parent'] = $field_group['ID'];
			
			// Check $ids map for existing parent	
			} elseif( isset($ids[ $field['parent'] ]) ) {
				$field['parent'] = $ids[ $field['parent'] ];	
			}
			
			// Add field menu_order.
			if( !isset($count[ $field['parent'] ]) ) {
				$count[ $field['parent'] ] = 1;
			} else {
				$count[ $field['parent'] ]++;
			}
			
			// Only add menu order if doesn't already exist.
			// Allows Flexible Content field to set custom order.
			if( !isset($field['menu_order']) ) {
				$field['menu_order'] = ($count[ $field['parent'] ] - 1);
			}
			
			// Save field.
			$field = acf_update_field( $field );
			
			// Add field data to $ids map.
			$ids[ $field['key'] ] = $field['ID'];
		}
	}
	
	// Save field group.
	$field_group = acf_update_field_group( $field_group );
	
	// Enable filters again.
	acf_enable_filters( $filters );
	
	/**
	 * Fires immediately after a field_group has been imported.
	 *
	 * @date	12/02/2014
	 * @since	5.0.0
	 *
	 * @param	array $field_group The field_group array.
	 */
	do_action( 'acf/import_field_group', $field_group );
	
	// return new field group.
	return $field_group;
}
