<?php

/**
 * Returns an array of "ACF only" meta for the given post_id.
 *
 * @date    9/10/18
 * @since   5.8.0
 *
 * @param mixed $post_id The post_id for this data.
 *
 * @return array
 */
function acf_get_meta( $post_id = 0 ) {
	// Allow filter to short-circuit load_value logic.
	$null = apply_filters( 'acf/pre_load_meta', null, $post_id );
	if ( $null !== null ) {
		return ( $null === '__return_null' ) ? null : $null;
	}

	// Decode the $post_id for $type and $id.
	$decoded  = acf_decode_post_id( $post_id );
	$instance = acf_get_meta_instance( $decoded['type'] );
	$meta     = array();

	if ( $instance ) {
		$meta = $instance->get_meta( $decoded['id'] );
	}

	/**
	 * Filters the $meta array after it has been loaded.
	 *
	 * @date    25/1/19
	 * @since   5.7.11
	 *
	 * @param array  $meta    The array of loaded meta.
	 * @param string $post_id The $post_id for this meta.
	 */
	return apply_filters( 'acf/load_meta', $meta, $post_id );
}


/**
 * acf_get_option_meta
 *
 * Returns an array of meta for the given wp_option name prefix in the same format as get_post_meta().
 *
 * @date    9/10/18
 * @since   5.8.0
 *
 * @param   string $prefix The wp_option name prefix.
 * @return  array
 */
function acf_get_option_meta( $prefix = '' ) {
	// Globals.
	global $wpdb;

	// Vars.
	$meta    = array();
	$search  = "{$prefix}_%";
	$_search = "_{$prefix}_%";

	// Escape underscores for LIKE.
	$search  = str_replace( '_', '\_', $search );
	$_search = str_replace( '_', '\_', $_search );

	// Query database for results.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * 
		FROM $wpdb->options 
		WHERE option_name LIKE %s 
		OR option_name LIKE %s",
			$search,
			$_search
		),
		ARRAY_A
	);

	// Loop over results and append meta (removing the $prefix from the option name).
	$len = strlen( "{$prefix}_" );
	foreach ( $rows as $row ) {
		$meta[ substr( $row['option_name'], $len ) ][] = $row['option_value'];
	}

	// Return results.
	return $meta;
}

/**
 * Retrieves specific metadata from the database.
 *
 * @date    16/10/2015
 * @since   5.2.3
 *
 * @param   integer|string $post_id The post id.
 * @param   string         $name    The meta name.
 * @param   boolean        $hidden  If the meta is hidden (starts with an underscore).
 *
 * @return  mixed
 */
function acf_get_metadata( $post_id = 0, $name = '', $hidden = false ) {
	// Allow filter to short-circuit logic.
	$null = apply_filters( 'acf/pre_load_metadata', null, $post_id, $name, $hidden );
	if ( $null !== null ) {
		return ( $null === '__return_null' ) ? null : $null;
	}

	// Decode $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Hidden meta uses an underscore prefix.
	$prefix = $hidden ? '_' : '';

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return null;
	}

	// Determine CRUD function.
	// - Relies on decoded post_id result to identify option or meta types.
	// - Uses xxx_metadata(type) instead of xxx_type_meta() to bypass additional logic that could alter the ID.
	if ( $type === 'option' ) {
		return get_option( "{$prefix}{$id}_{$name}", null );
	} else {
		$meta = get_metadata( $type, $id, "{$prefix}{$name}", false );
		return isset( $meta[0] ) ? $meta[0] : null;
	}
}

/**
 * Updates metadata in the database.
 *
 * @date    16/10/2015
 * @since   5.2.3
 *
 * @param   integer|string $post_id The post id.
 * @param   string         $name    The meta name.
 * @param   mixed          $value   The meta value.
 * @param   boolean        $hidden  If the meta is hidden (starts with an underscore).
 *
 * @return  integer|boolean Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function acf_update_metadata( $post_id = 0, $name = '', $value = '', $hidden = false ) {
	// Allow filter to short-circuit logic.
	$pre = apply_filters( 'acf/pre_update_metadata', null, $post_id, $name, $value, $hidden );
	if ( $pre !== null ) {
		return $pre;
	}

	// Decode $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Hidden meta uses an underscore prefix.
	$prefix = $hidden ? '_' : '';

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return false;
	}

	// Determine CRUD function.
	// - Relies on decoded post_id result to identify option or meta types.
	// - Uses xxx_metadata(type) instead of xxx_type_meta() to bypass additional logic that could alter the ID.
	if ( $type === 'option' ) {
		$value    = wp_unslash( $value );
		$autoload = (bool) acf_get_setting( 'autoload' );
		return update_option( "{$prefix}{$id}_{$name}", $value, $autoload );
	} else {
		return update_metadata( $type, $id, "{$prefix}{$name}", $value );
	}
}

/**
 * Deletes metadata from the database.
 *
 * @date    16/10/2015
 * @since   5.2.3
 *
 * @param   integer|string $post_id The post id.
 * @param   string         $name    The meta name.
 * @param   boolean        $hidden  If the meta is hidden (starts with an underscore).
 *
 * @return  boolean
 */
function acf_delete_metadata( $post_id = 0, $name = '', $hidden = false ) {
	// Allow filter to short-circuit logic.
	$pre = apply_filters( 'acf/pre_delete_metadata', null, $post_id, $name, $hidden );
	if ( $pre !== null ) {
		return $pre;
	}

	// Decode $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Hidden meta uses an underscore prefix.
	$prefix = $hidden ? '_' : '';

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return false;
	}

	// Determine CRUD function.
	// - Relies on decoded post_id result to identify option or meta types.
	// - Uses xxx_metadata(type) instead of xxx_type_meta() to bypass additional logic that could alter the ID.
	if ( $type === 'option' ) {
		return delete_option( "{$prefix}{$id}_{$name}" );
	} else {
		return delete_metadata( $type, $id, "{$prefix}{$name}" );
	}
}

/**
 * Copies meta from one post to another. Useful for saving and restoring revisions.
 *
 * @since   5.3.8
 *
 * @param integer|string $from_post_id The post id to copy from.
 * @param integer|string $to_post_id   The post id to paste to.
 * @return void
 */
function acf_copy_metadata( $from_post_id = 0, $to_post_id = 0 ) {
	// Get all metadata.
	$meta = acf_get_meta( $from_post_id );

	$decoded  = acf_decode_post_id( $to_post_id );
	$instance = acf_get_meta_instance( $decoded['type'] );

	if ( $meta && $instance ) {
		$instance->update_meta( $decoded['id'], $meta );
	}
}

/**
 * acf_copy_postmeta
 *
 * Copies meta from one post to another. Useful for saving and restoring revisions.
 *
 * @date    25/06/2016
 * @since   5.3.8
 * @deprecated 5.7.11
 *
 * @param   integer $from_post_id The post id to copy from.
 * @param   integer $to_post_id   The post id to paste to.
 * @return  void
 */
function acf_copy_postmeta( $from_post_id = 0, $to_post_id = 0 ) {
	return acf_copy_metadata( $from_post_id, $to_post_id );
}

/**
 * acf_get_meta_field
 *
 * Returns a field using the provided $id and $post_id parameters.
 * Looks for a reference to help loading the correct field via name.
 *
 * @date    21/1/19
 * @since   5.7.10
 *
 * @param   string       $key     The meta name (field name).
 * @param   (int|string) $post_id The post_id where this field's value is saved.
 * @return  (array|false) The field array.
 */
function acf_get_meta_field( $key = 0, $post_id = 0 ) {

	// Try reference.
	$field_key = acf_get_reference( $key, $post_id );

	if ( $field_key ) {
		$field = acf_get_field( $field_key );
		if ( $field ) {
			$field['name'] = $key;
			return $field;
		}
	}

	// Return false.
	return false;
}

/**
 * acf_get_metaref
 *
 * Retrieves reference metadata from the database.
 *
 * @date    16/10/2015
 * @since   5.2.3
 *
 * @param   (int|string)                                   $post_id The post id.
 * @param   string type The reference type (fields|groups).
 * @param   string                                         $name    An optional specific name
 * @return  mixed
 */
function acf_get_metaref( $post_id = 0, $type = 'fields', $name = '' ) {

	// Load existing meta.
	$meta = acf_get_metadata( $post_id, "_acf_$type" );

	// Handle no meta.
	if ( ! $meta ) {
		return $name ? '' : array();
	}

	// Return specific reference.
	if ( $name ) {
		return isset( $meta[ $name ] ) ? $meta[ $name ] : '';

		// Or return all references.
	} else {
		return $meta;
	}
}

/**
 * acf_update_metaref
 *
 * Updates reference metadata in the database.
 *
 * @date    16/10/2015
 * @since   5.2.3
 *
 * @param   (int|string)                                   $post_id    The post id.
 * @param   string type The reference type (fields|groups).
 * @param   array                                          $references An array of references.
 * @return  (int|bool) Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function acf_update_metaref( $post_id = 0, $type = 'fields', $references = array() ) {

	// Get current references.
	$current = acf_get_metaref( $post_id, $type );

	// Merge in new references.
	$references = array_merge( $current, $references );

	// Simplify groups
	if ( $type === 'groups' ) {
		$references = array_values( $references );
	}

	// Remove duplicate references.
	$references = array_unique( $references );

	// Update metadata.
	return acf_update_metadata( $post_id, "_acf_$type", $references );
}

/**
 * Retrieves an ACF meta instance for the provided meta type.
 *
 * @since 6.4
 *
 * @param string $type The meta type as decoded from the post ID.
 * @return object|null
 */
function acf_get_meta_instance( string $type ): ?object {
	$instance       = null;
	$meta_locations = acf_get_store( 'acf-meta-locations' );

	if ( $meta_locations && $meta_locations->has( $type ) ) {
		$instance = acf_get_instance( $meta_locations->get( $type ) );
	}

	return $instance;
}

/**
 * Gets metadata from the database.
 *
 * @since 6.4
 *
 * @param integer|string $post_id The post id.
 * @param array          $field   The field array.
 * @param boolean        $hidden  True if we should return the reference key.
 * @return mixed
 */
function acf_get_metadata_by_field( $post_id = 0, $field = array(), bool $hidden = false ) {
	if ( empty( $field['name'] ) ) {
		return null;
	}

	// Allow filter to short-circuit logic.
	$null = apply_filters( 'acf/pre_load_metadata', null, $post_id, $field['name'], $hidden );
	if ( $null !== null ) {
		return ( $null === '__return_null' ) ? null : $null;
	}

	// Decode the $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return null;
	}

	$meta_instance = acf_get_meta_instance( $type );

	if ( ! $meta_instance ) {
		return false;
	}

	if ( $hidden ) {
		return $meta_instance->get_reference( $id, $field['name'] );
	}

	return $meta_instance->get_value( $id, $field );
}

/**
 * Updates metadata in the database.
 *
 * @since 6.4
 *
 * @param integer|string $post_id The post id.
 * @param array          $field   The field array.
 * @param mixed          $value   The meta value.
 * @param boolean        $hidden  True if we should update the reference key.
 * @return integer|boolean Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function acf_update_metadata_by_field( $post_id = 0, $field = array(), $value = '', bool $hidden = false ) {
	if ( empty( $field['name'] ) ) {
		return null;
	}

	// Allow filter to short-circuit logic.
	$pre = apply_filters( 'acf/pre_update_metadata', null, $post_id, $field['name'], $value, $hidden );
	if ( $pre !== null ) {
		return $pre;
	}

	// Decode the $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return false;
	}

	$meta_instance = acf_get_meta_instance( $type );

	if ( ! $meta_instance ) {
		return false;
	}

	if ( $hidden ) {
		return $meta_instance->update_reference( $id, $field['name'], $field['key'] );
	}

	return $meta_instance->update_value( $id, $field, $value );
}

/**
 * Deletes metadata from the database.
 *
 * @since 6.4
 *
 * @param integer|string $post_id The post id.
 * @param array          $field   The field array.
 * @param boolean        $hidden  True if we should update the reference key.
 * @return boolean
 */
function acf_delete_metadata_by_field( $post_id = 0, $field = array(), bool $hidden = false ) {
	if ( empty( $field['name'] ) ) {
		return null;
	}

	// Allow filter to short-circuit logic.
	$pre = apply_filters( 'acf/pre_delete_metadata', null, $post_id, $field['name'], $hidden );
	if ( $pre !== null ) {
		return $pre;
	}

	// Decode the $post_id for $type and $id.
	$decoded = acf_decode_post_id( $post_id );
	$id      = $decoded['id'];
	$type    = $decoded['type'];

	// Bail early if no $id (possible during new acf_form).
	if ( ! $id ) {
		return false;
	}

	$meta_instance = acf_get_meta_instance( $type );

	if ( ! $meta_instance ) {
		return false;
	}

	if ( $hidden ) {
		return $meta_instance->delete_reference( $id, $field['name'] );
	}

	return $meta_instance->delete_value( $id, $field );
}
