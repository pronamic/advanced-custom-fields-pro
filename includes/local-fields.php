<?php

// Register local stores.
acf_register_store( 'local-fields' );
acf_register_store( 'local-groups' );
acf_register_store( 'local-empty' );
acf_register_store( 'local-post-types' );
acf_register_store( 'local-taxonomies' );
acf_register_store( 'local-ui-options-pages' );

// Register filter.
acf_enable_filter( 'local' );

/**
 * acf_enable_local
 *
 * Enables the local filter.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  void
 */
function acf_enable_local() {
	acf_enable_filter( 'local' );
}

/**
 * acf_disable_local
 *
 * Disables the local filter.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  void
 */
function acf_disable_local() {
	acf_disable_filter( 'local' );
}

/**
 * acf_is_local_enabled
 *
 * Returns true if local fields are enabled.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  boolean
 */
function acf_is_local_enabled() {
	return ( acf_is_filter_enabled( 'local' ) && acf_get_setting( 'local' ) );
}

/**
 * Returns either local store or a dummy store for the given name or post type.
 *
 * @date 23/1/19
 * @since 5.7.10
 *
 * @param string $name      The store name.
 * @param string $post_type The post type for the desired store.
 * @return ACF_Data
 */
function acf_get_local_store( $name = '', $post_type = '' ) {
	if ( '' !== $post_type ) {
		switch ( $post_type ) {
			case 'acf-post-type':
				$name = 'post-types';
				break;
			case 'acf-taxonomy':
				$name = 'taxonomies';
				break;
			case 'acf-field-group':
				$name = 'groups';
				break;
			case 'acf-field':
				$name = 'fields';
				break;
			case 'acf-ui-options-page':
				$name = 'ui-options-pages';
				break;
		}
	}

	if ( acf_is_local_enabled() && '' !== $name ) {
		return acf_get_store( "local-$name" );
	} else {
		// Return dummy store if not enabled or no name provided.
		return acf_get_store( 'local-empty' );
	}
}

/**
 * acf_reset_local
 *
 * Resets the local data.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  void
 */
function acf_reset_local() {
	acf_get_local_store( 'fields' )->reset();
	acf_get_local_store( 'groups' )->reset();
	acf_get_local_store( 'post-types' )->reset();
	acf_get_local_store( 'taxonomies' )->reset();
}

/**
 * acf_get_local_field_groups
 *
 * Returns all local field groups.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  array
 */
function acf_get_local_field_groups() {
	return acf_get_local_store( 'groups' )->get();
}

/**
 * Returns local ACF posts with the provided post type.
 *
 * @since 6.1
 *
 * @param string $post_type The post type to check for.
 * @return array|mixed
 */
function acf_get_local_internal_posts( $post_type = 'acf-field-group' ) {
	return acf_get_local_store( '', $post_type )->get();
}

/**
 * acf_have_local_field_groups
 *
 * description
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   type $var Description. Default.
 * @return  type Description.
 */
function acf_have_local_field_groups() {
	return acf_get_local_store( 'groups' )->count() ? true : false;
}

/**
 * acf_count_local_field_groups
 *
 * description
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   type $var Description. Default.
 * @return  type Description.
 */
function acf_count_local_field_groups() {
	return acf_get_local_store( 'groups' )->count();
}

/**
 * acf_add_local_field_group
 *
 * Adds a local field group.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array $field_group The field group array.
 * @return  boolean
 */
function acf_add_local_field_group( $field_group ) {
	// Apply default properties needed for import.
	$field_group = wp_parse_args(
		$field_group,
		array(
			'key'    => '',
			'title'  => '',
			'fields' => array(),
			'local'  => 'php',
		)
	);

	// Generate key if only name is provided.
	if ( ! $field_group['key'] ) {
		$field_group_key = 'group_' . acf_slugify( $field_group['title'], '_' );
		if ( $field_group_key === 'group_' ) {
			$field_group_key = 'group_' . md5( $field_group['title'] );
		}
		$field_group['key'] = $field_group_key;
	}

	// Bail early if field group already exists.
	if ( acf_is_local_field_group( $field_group['key'] ) ) {
		return false;
	}

	// Prepare field group for import (adds menu_order and parent properties to fields).
	$field_group = acf_prepare_field_group_for_import( $field_group );

	// Extract fields from group.
	$fields = acf_extract_var( $field_group, 'fields' );

	// Add to store
	acf_get_local_store( 'groups' )->set( $field_group['key'], $field_group );

	// Add fields
	if ( $fields ) {
		acf_add_local_fields( $fields );
	}

	// Return true on success.
	return true;
}

/**
 * Adds a local ACF internal post type.
 *
 * @since 6.1
 *
 * @param array  $post      The main ACF post array.
 * @param string $post_type The post type being added.
 * @return boolean
 */
function acf_add_local_internal_post_type( $post, $post_type ) {
	// Apply default properties needed for import.
	$post = wp_parse_args(
		$post,
		array(
			'key'   => '',
			'title' => '',
			'local' => 'json',
		)
	);

	// Bail early if field group already exists.
	if ( acf_is_local_internal_post_type( $post['key'], $post_type ) ) {
		return false;
	}

	// Prepare field group for import (adds menu_order and parent properties to fields).
	$post = acf_prepare_internal_post_type_for_import( $post, $post_type );

	// Add to store.
	acf_get_local_store( '', $post_type )->set( $post['key'], $post );

	return true;
}

/**
 * register_field_group
 *
 * See acf_add_local_field_group().
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array $field_group The field group array.
 * @return  void
 */
function register_field_group( $field_group ) {
	acf_add_local_field_group( $field_group );
}

/**
 * acf_remove_local_field_group
 *
 * Removes a field group for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  boolean
 */
function acf_remove_local_field_group( $key = '' ) {
	return acf_remove_local_internal_post_type( $key, 'acf-field-group' );
}

/**
 * Removes a local ACF post with the given key and post type.
 *
 * @since 6.1
 *
 * @param string $key       The ACF key.
 * @param string $post_type The ACF post type.
 * @return boolean
 */
function acf_remove_local_internal_post_type( $key = '', $post_type = 'acf-field-group' ) {
	return acf_get_local_store( '', $post_type )->remove( $key );
}

/**
 * acf_is_local_field_group
 *
 * Returns true if a field group exists for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  boolean
 */
function acf_is_local_field_group( $key = '' ) {
	return acf_get_local_store( 'groups' )->has( $key );
}


/**
 * Returns true if an ACF post exists for the given key.
 *
 * @since 6.1
 *
 * @param string $key       The ACF key.
 * @param string $post_type The ACF post type.
 * @return  boolean
 */
function acf_is_local_internal_post_type( $key = '', $post_type = 'acf-field-group' ) {
	return acf_get_local_store( '', $post_type )->has( $key );
}

/**
 * acf_is_local_field_group_key
 *
 * Returns true if a field group exists for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  boolean
 */
function acf_is_local_field_group_key( $key = '' ) {
	return acf_is_local_internal_post_type_key( $key, 'acf-field-group' );
}

/**
 * Returns true if a local ACF post exists for the given key.
 *
 * @since 6.1
 *
 * @param string $key       The ACF post key.
 * @param string $post_type The post type to check.
 * @return boolean
 */
function acf_is_local_internal_post_type_key( $key = '', $post_type = '' ) {
	return acf_get_local_store( '', $post_type )->is( $key );
}

/**
 * acf_get_local_field_group
 *
 * Returns a field group for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  (array|null)
 */
function acf_get_local_field_group( $key = '' ) {
	return acf_get_local_store( 'groups' )->get( $key );
}

/**
 * Returns an ACF post for the given key.
 *
 * @since 6.1
 *
 * @param string $key       The field group key.
 * @param string $post_type The ACF post type.
 * @return array|null
 */
function acf_get_local_internal_post_type( $key = '', $post_type = 'acf-field-group' ) {
	return acf_get_local_store( '', $post_type )->get( $key );
}

/**
 * acf_add_local_fields
 *
 * Adds an array of local fields.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array $fields An array of un prepared fields.
 * @return  array
 */
function acf_add_local_fields( $fields = array() ) {

	// Prepare for import (allows parent fields to offer up children).
	$fields = acf_prepare_fields_for_import( $fields );

	// Add each field.
	foreach ( $fields as $field ) {
		acf_add_local_field( $field, true );
	}
}

/**
 * acf_get_local_fields
 *
 * Returns all local fields for the given parent.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $parent The parent key.
 * @return  array
 */
function acf_get_local_fields( $parent = '' ) {

	// Return children
	if ( $parent ) {
		return acf_get_local_store( 'fields' )->query(
			array(
				'parent' => $parent,
			)
		);

		// Return all.
	} else {
		return acf_get_local_store( 'fields' )->get();
	}
}

/**
 * acf_have_local_fields
 *
 * Returns true if local fields exist.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $parent The parent key.
 * @return  boolean
 */
function acf_have_local_fields( $parent = '' ) {
	return acf_get_local_fields( $parent ) ? true : false;
}

/**
 * acf_count_local_fields
 *
 * Returns the number of local fields for the given parent.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $parent The parent key.
 * @return  integer
 */
function acf_count_local_fields( $parent = '' ) {
	return count( acf_get_local_fields( $parent ) );
}

/**
 * acf_add_local_field
 *
 * Adds a local field.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array   $field    The field array.
 * @param   boolean $prepared Whether or not the field has already been prepared for import.
 * @return  void
 */
function acf_add_local_field( $field, $prepared = false ) {

	// Apply default properties needed for import.
	$field = wp_parse_args(
		$field,
		array(
			'key'    => '',
			'name'   => '',
			'type'   => '',
			'parent' => '',
		)
	);

	// Generate key if only name is provided.
	if ( ! $field['key'] ) {
		$field['key'] = 'field_' . $field['name'];
	}

	// If called directly, allow sub fields to be correctly prepared.
	if ( ! $prepared ) {
		return acf_add_local_fields( array( $field ) );
	}

	// Extract attributes.
	$key  = $field['key'];
	$name = $field['name'];

	// Allow sub field to be added multipel times to different parents.
	$store = acf_get_local_store( 'fields' );
	if ( $store->is( $key ) ) {
		$old_key = _acf_generate_local_key( $store->get( $key ) );
		$new_key = _acf_generate_local_key( $field );
		if ( $old_key !== $new_key ) {
			$key = $new_key;
		}
	}

	// Add field.
	$store->set( $key, $field )->alias( $key, $name );
}

/**
 * _acf_generate_local_key
 *
 * Generates a unique key based on the field's parent.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field key.
 * @return  boolean
 */
function _acf_generate_local_key( $field ) {
	return "{$field['key']}:{$field['parent']}";
}

/**
 * acf_remove_local_field
 *
 * Removes a field for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field key.
 * @return  boolean
 */
function acf_remove_local_field( $key = '' ) {
	return acf_get_local_store( 'fields' )->remove( $key );
}

/**
 * acf_is_local_field
 *
 * Returns true if a field exists for the given key or name.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  boolean
 */
function acf_is_local_field( $key = '' ) {
	return acf_get_local_store( 'fields' )->has( $key );
}

/**
 * acf_is_local_field_key
 *
 * Returns true if a field exists for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  boolean
 */
function acf_is_local_field_key( $key = '' ) {
	return acf_get_local_store( 'fields' )->is( $key );
}

/**
 * acf_get_local_field
 *
 * Returns a field for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group key.
 * @return  (array|null)
 */
function acf_get_local_field( $key = '' ) {
	return acf_get_local_store( 'fields' )->get( $key );
}

/**
 * _acf_apply_get_local_field_groups
 *
 * Appends local field groups to the provided array.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   array $field_groups An array of field groups.
 * @return  array
 */
function _acf_apply_get_local_field_groups( $groups = array() ) {
	return _acf_apply_get_local_internal_posts( $groups, 'acf-field-group' );
}

/**
 * Appends local ACF internal post types to the provided array.
 *
 * @since 6.1
 *
 * @param array  $posts     An array of ACF posts.
 * @param string $post_type The ACF internal post type being loaded.
 * @return array
 */
function _acf_apply_get_local_internal_posts( $posts = array(), $post_type = 'acf-field-group' ) {
	// Get local posts.
	$local_posts = acf_get_local_internal_posts( $post_type );

	if ( ! $local_posts ) {
		return $posts;
	}

	// Generate map of "index" => "key" data.
	$map = wp_list_pluck( $posts, 'key' );

	// Loop over local posts and update/append local.
	foreach ( $local_posts as $post ) {
		$i = array_search( $post['key'], $map, true );
		if ( $i !== false ) {
			unset( $post['ID'] );
			$posts[ $i ] = array_merge( $posts[ $i ], $post );
		} else {
			$posts[] = acf_get_internal_post_type( $post['key'], $post_type );
		}
	}

	// Sort list via menu_order and title.
	return wp_list_sort(
		$posts,
		array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		)
	);
}
add_filter( 'acf/load_field_groups', '_acf_apply_get_local_internal_posts', 20, 2 );
add_filter( 'acf/load_post_types', '_acf_apply_get_local_internal_posts', 20, 2 );
add_filter( 'acf/load_taxonomies', '_acf_apply_get_local_internal_posts', 20, 2 );
add_filter( 'acf/load_ui_options_pages', '_acf_apply_get_local_internal_posts', 20, 2 );

/**
 * _acf_apply_is_local_field_key
 *
 * Returns true if is a local key.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   boolean $bool The result.
 * @param   string  $id   The identifier.
 * @return  boolean
 */
function _acf_apply_is_local_field_key( $bool, $id ) {
	return acf_is_local_field_key( $id );
}

// Hook into filter.
add_filter( 'acf/is_field_key', '_acf_apply_is_local_field_key', 20, 2 );

/**
 * _acf_apply_is_local_field_group_key
 *
 * Returns true if is a local key.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   boolean $bool The result.
 * @param   string  $id   The identifier.
 * @return  boolean
 */
function _acf_apply_is_local_field_group_key( $bool, $id ) {
	return acf_is_local_field_group_key( $id );
}

/**
 * Returns true if is a local key.
 *
 * @since 6.1
 *
 * @param boolean $bool      The result.
 * @param string  $id        The identifier.
 * @param string  $post_type The post type.
 * @return boolean
 */
function _acf_apply_is_local_internal_post_type_key( $bool, $id, $post_type = 'acf-field-group' ) {
	return acf_is_local_internal_post_type_key( $id, $post_type );
}

// Hook into filter.
add_filter( 'acf/is_field_group_key', '_acf_apply_is_local_internal_post_type_key', 20, 3 );
add_filter( 'acf/is_post_type_key', '_acf_apply_is_local_internal_post_type_key', 20, 3 );
add_filter( 'acf/is_taxonomy_key', '_acf_apply_is_local_internal_post_type_key', 20, 3 );

/**
 * _acf_do_prepare_local_fields
 *
 * Local fields that are added too early will not be correctly prepared by the field type class.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  void
 */
function _acf_do_prepare_local_fields() {

	// Get fields.
	$fields = acf_get_local_fields();

	// If fields have been registered early, re-add to correctly prepare them.
	if ( $fields ) {
		acf_add_local_fields( $fields );
	}
}

// Hook into action.
add_action( 'acf/include_fields', '_acf_do_prepare_local_fields', 0, 1 );
