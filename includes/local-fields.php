<?php

// Register notices stores.
acf_register_store( 'local-fields' );
acf_register_store( 'local-groups' );
acf_register_store( 'local-empty' );

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
 * @return  bool
 */
function acf_is_local_enabled() {
	return ( acf_is_filter_enabled( 'local' ) && acf_get_setting( 'local' ) );
}

/**
 * acf_get_local_store
 *
 * Returns either local store or a dummy store for the given name.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   string $name The store name (fields|groups).
 * @return  ACF_Data
 */
function acf_get_local_store( $name = '' ) {

	// Check if enabled.
	if ( acf_is_local_enabled() ) {
		return acf_get_store( "local-$name" );

		// Return dummy store if not enabled.
	} else {
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
 * @return  bool
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
		$field_group['key'] = 'group_' . acf_slugify( $field_group['title'], '_' );
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
 * @return  bool
 */
function acf_remove_local_field_group( $key = '' ) {
	return acf_get_local_store( 'groups' )->remove( $key );
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
 * @return  bool
 */
function acf_is_local_field_group( $key = '' ) {
	return acf_get_local_store( 'groups' )->has( $key );
}

/**
 * acf_is_local_field_group_key
 *
 * Returns true if a field group exists for the given key.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   string $key The field group group key.
 * @return  bool
 */
function acf_is_local_field_group_key( $key = '' ) {
	return acf_get_local_store( 'groups' )->is( $key );
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
 * @return  bool
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
 * @return  int
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
 * @param   array $field The field array.
 * @param   bool  $prepared Whether or not the field has already been prepared for import.
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
 * @return  bool
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
 * @return  bool
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
 * @return  bool
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
 * @return  bool
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

	// Get local groups
	$local = acf_get_local_field_groups();
	if ( $local ) {

		// Generate map of "index" => "key" data.
		$map = wp_list_pluck( $groups, 'key' );

		// Loop over groups and update/append local.
		foreach ( $local as $group ) {

			// Get group allowing cache and filters to run.
			// $group = acf_get_field_group( $group['key'] );

			// Update.
			$i = array_search( $group['key'], $map );
			if ( $i !== false ) {
				unset( $group['ID'] );
				$groups[ $i ] = array_merge( $groups[ $i ], $group );

				// Append
			} else {
				$groups[] = acf_get_field_group( $group['key'] );
			}
		}

		// Sort list via menu_order and title.
		$groups = wp_list_sort(
			$groups,
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);
	}

	// Return groups.
	return $groups;
}

// Hook into filter.
add_filter( 'acf/load_field_groups', '_acf_apply_get_local_field_groups', 20, 1 );

/**
 * _acf_apply_is_local_field_key
 *
 * Returns true if is a local key.
 *
 * @date    23/1/19
 * @since   5.7.10
 *
 * @param   bool   $bool The result.
 * @param   string $id The identifier.
 * @return  bool
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
 * @param   bool   $bool The result.
 * @param   string $id The identifier.
 * @return  bool
 */
function _acf_apply_is_local_field_group_key( $bool, $id ) {
	return acf_is_local_field_group_key( $id );
}

// Hook into filter.
add_filter( 'acf/is_field_group_key', '_acf_apply_is_local_field_group_key', 20, 2 );

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


