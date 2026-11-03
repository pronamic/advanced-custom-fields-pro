<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

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
	$search  = $wpdb->esc_like( "{$prefix}_" ) . '%';
	$_search = $wpdb->esc_like( "_{$prefix}_" ) . '%';

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

/**
 * Returns true if a post meta key stores the value of an ACF field.
 *
 * A key belongs to a field if the post has a field key reference for it, or if it is a name ACF loads a value
 * from without a reference: a top-level field's name, the name of a group or clone sub field (such as
 * `{group}_{sub_field}`), or a repeater or flexible content row's sub field (such as `{repeater}_0_{sub_field}`).
 *
 * @since 6.8.11
 * @access private
 *
 * @param integer $post_id  The post ID.
 * @param string  $meta_key The meta key.
 * @return boolean
 */
function _acf_is_field_meta_key( $post_id, $meta_key ) {
	if ( ! is_string( $meta_key ) || '' === $meta_key ) {
		return false;
	}

	if ( $post_id && acf_is_field_key( get_post_meta( $post_id, '_' . $meta_key, true ) ) ) {
		return true;
	}

	$storage = _acf_get_field_storage_names();

	if ( isset( $storage['names'][ $meta_key ] ) ) {
		return true;
	}

	foreach ( $storage['patterns'] as $pattern ) {
		if ( preg_match( $pattern, $meta_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Returns the meta names ACF loads field values from without a field key reference.
 *
 * These are the names of top-level fields (in field groups, or local fields registered without one) and of
 * group and clone sub fields (as array keys in `names`), and
 * patterns for the sub fields of repeater and flexible content rows, whose names include the row number
 * (in `patterns`). They are worked out as the field types name sub fields when loading their values.
 *
 * Cached for the request, until local fields or posts (including database fields) change.
 *
 * @since 6.8.11
 * @access private
 *
 * @return array
 */
function _acf_get_field_storage_names() {
	static $cache = array(
		'version' => '',
		'storage' => array(),
	);

	$version = acf_get_local_store( 'fields' )->count() . '|' . acf_get_local_store( 'groups' )->count() . '|' . wp_cache_get_last_changed( 'posts' );

	if ( $cache['version'] === $version ) {
		return $cache['storage'];
	}

	$storage = array(
		'names'    => array(),
		'patterns' => array(),
	);

	foreach ( acf_get_field_groups() as $field_group ) {
		foreach ( (array) acf_get_fields( $field_group ) as $field ) {
			_acf_add_field_storage_names( $storage, $field );
		}
	}

	// Local fields registered without a field group (or field) parent are loaded by name too.
	foreach ( (array) acf_get_local_store( 'fields' )->get() as $local_field ) {
		$parent = (string) ( $local_field['parent'] ?? '' );

		if ( '' === $parent || ( ! acf_is_local_field_group( $parent ) && ! acf_is_local_field( $parent ) && ! acf_get_field_group( $parent ) ) ) {
			$field = acf_get_field( $local_field['key'] ?? '' );
			_acf_add_field_storage_names( $storage, $field ? $field : $local_field );
		}
	}

	$storage['patterns'] = array_keys( $storage['patterns'] );

	$cache = array(
		'version' => $version,
		'storage' => $storage,
	);

	return $storage;
}

/**
 * Adds the meta name a field is loaded from without a reference, and those of its sub fields.
 *
 * Group and clone sub field names are worked out with the field types' own prepare_field_for_db(), as when
 * their values are loaded. Repeater and flexible content rows are named `{field}_{row}_{sub_field}`; the row
 * number is held as a placeholder and turned into a pattern.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $storage The names and patterns, as array keys.
 * @param array $field   The field array, with its meta name in `name`.
 * @return void
 */
function _acf_add_field_storage_names( array &$storage, $field ) {
	$name = (string) ( $field['name'] ?? '' );

	if ( '' === $name ) {
		return;
	}

	// Names containing a row placeholder become patterns.
	if ( false !== strpos( $name, "\0" ) ) {
		$parts = array_map(
			function ( $part ) {
				return preg_quote( $part, '/' );
			},
			explode( "\0", $name )
		);

		$storage['patterns'][ '/^' . implode( '\d+', $parts ) . '$/' ] = true;
	} else {
		$storage['names'][ $name ] = true;
	}

	foreach ( _acf_get_field_storage_sub_fields( $field ) as $sub_field ) {
		_acf_add_field_storage_names( $storage, $sub_field );
	}
}

/**
 * Returns a field's sub fields with their meta names in `name`, as the field type names them when loading.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $field The field array, with its meta name in `name`.
 * @return array
 */
function _acf_get_field_storage_sub_fields( $field ) {
	$type = (string) ( $field['type'] ?? '' );

	if ( in_array( $type, array( 'group', 'clone' ), true ) ) {
		return _acf_get_prepared_sub_fields( $field, $type );
	}

	if ( in_array( $type, array( 'repeater', 'flexible_content' ), true ) ) {
		return _acf_get_row_sub_fields( $field, $type );
	}

	return array();
}

/**
 * Returns a group or clone field's sub fields named by the field type's prepare_field_for_db().
 *
 * @since 6.8.11
 * @access private
 *
 * @param array  $field The field array.
 * @param string $type  The field type.
 * @return array
 */
function _acf_get_prepared_sub_fields( $field, $type ) {
	$field_type = acf_get_field_type( $type );

	if ( $field_type && method_exists( $field_type, 'prepare_field_for_db' ) && ! empty( $field['sub_fields'] ) ) {
		$field = $field_type->prepare_field_for_db( $field );
	}

	return is_array( $field['sub_fields'] ?? null ) ? $field['sub_fields'] : array();
}

/**
 * Returns a repeater's sub fields, or those of every flexible content layout, named for their rows.
 *
 * Rows are named `{field}_{row}_{sub_field}`, with the row number held as a placeholder.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array  $field The field array.
 * @param string $type  The field type.
 * @return array
 */
function _acf_get_row_sub_fields( $field, $type ) {
	if ( 'repeater' === $type ) {
		$sub_fields = is_array( $field['sub_fields'] ?? null ) ? $field['sub_fields'] : array();
	} else {
		$sub_fields = array();
		foreach ( (array) ( $field['layouts'] ?? array() ) as $layout ) {
			$sub_fields = array_merge( $sub_fields, is_array( $layout['sub_fields'] ?? null ) ? $layout['sub_fields'] : array() );
		}
	}

	foreach ( $sub_fields as &$sub_field ) {
		$sub_field['name'] = $field['name'] . "_\0_" . ( $sub_field['name'] ?? '' );
	}

	return $sub_fields;
}

/**
 * Prevents WordPress's own custom field editing from changing post meta that belongs to an ACF field.
 *
 * The Custom Fields meta box (`metakeyinput` and `meta[]` in the post form), `wp_ajax_add_meta` and XML-RPC
 * `custom_fields` check these capabilities. Without this, a user who can edit a post could write values for ACF
 * fields that aren't shown to them, bypassing ACF's save checks. ACF saves its own values without these checks.
 * Meta keys registered with register_meta() and protected meta keys are left to WordPress.
 *
 * @since 6.8.11
 *
 * @param string[] $caps       The primitive capabilities required.
 * @param string   $cap        The capability being checked.
 * @param mixed    ...$context The user ID, and the post ID and meta key.
 * @return string[]
 */
function acf_map_post_meta_caps( $caps, $cap, ...$context ) {
	// The user ID isn't needed: the capability is denied whoever the user is.
	$args = isset( $context[1] ) ? (array) $context[1] : array();

	if ( ! in_array( $cap, array( 'add_post_meta', 'edit_post_meta', 'delete_post_meta' ), true ) || ! isset( $args[0], $args[1] ) || ! is_string( $args[1] ) ) {
		return $caps;
	}

	$post_id   = (int) $args[0];
	$meta_key  = $args[1];
	$post_type = (string) get_post_type( $post_id );

	if ( registered_meta_key_exists( 'post', $meta_key, $post_type ) || registered_meta_key_exists( 'post', $meta_key ) || is_protected_meta( $meta_key, 'post' ) ) {
		return $caps;
	}

	if ( _acf_is_field_meta_key( $post_id, $meta_key ) ) {
		$caps[] = 'do_not_allow';
	}

	return $caps;
}

add_filter( 'map_meta_cap', 'acf_map_post_meta_caps', 10, 4 );
