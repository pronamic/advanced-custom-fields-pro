<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// Register store.
acf_register_store( 'values' )->prop( 'multisite', true );

/**
 * acf_get_reference
 *
 * Retrieves the field key for a given field name and post_id.
 *
 * @date    26/1/18
 * @since   5.6.5
 *
 * @param   string $field_name The name of the field. eg 'sub_heading'.
 * @param   mixed  $post_id    The post_id of which the value is saved against.
 * @return  string|null The field key, or null on failure.
 */
function acf_get_reference( $field_name, $post_id ) {
	// Allow filter to short-circuit load_value logic.
	$reference = apply_filters( 'acf/pre_load_reference', null, $field_name, $post_id );
	if ( $reference !== null ) {
		return $reference;
	}

	// Back-compat.
	$reference = apply_filters( 'acf/pre_load_metadata', null, $post_id, $field_name, true );
	if ( $reference !== null ) {
		return ( $reference === '__return_null' ) ? null : $reference;
	}

	$decoded = acf_decode_post_id( $post_id );

	// Bail if no ID or type.
	if ( empty( $decoded['id'] ) || empty( $decoded['type'] ) ) {
		return null;
	}

	$meta_instance = acf_get_meta_instance( $decoded['type'] );
	if ( ! $meta_instance ) {
		return null;
	}

	// Get hidden meta for this field name.
	$reference = $meta_instance->get_reference( $decoded['id'], $field_name );

	/**
	 * Filters the reference value.
	 *
	 * @date    25/1/19
	 * @since   5.7.11
	 *
	 * @param   string $reference The reference value.
	 * @param   string $field_name The field name.
	 * @param   (int|string) $post_id The post ID where meta is stored.
	 */
	return apply_filters( 'acf/load_reference', $reference, $field_name, $post_id );
}

/**
 * Retrieves the value for a given field and post_id.
 *
 * @date    28/09/13
 * @since   5.0.0
 *
 * @param   integer|string $post_id The post id.
 * @param   array          $field   The field array.
 * @return  mixed
 */
function acf_get_value( $post_id, $field ) {

	// Allow filter to short-circuit load_value logic.
	$value = apply_filters( 'acf/pre_load_value', null, $post_id, $field );
	if ( $value !== null ) {
		return $value;
	}

	// Get field name.
	$field_name = $field['name'];

	// Get field ID & type.
	$decoded = acf_decode_post_id( $post_id );

	$allow_load = true;

	// If we don't have a proper field array, the field doesn't exist currently.
	if ( empty( $field['type'] ) && empty( $field['key'] ) ) {

		// Check if we should trigger warning about accessing fields too early via action.
		do_action( 'acf/get_invalid_field_value', $field, __FUNCTION__ );

		if ( apply_filters( 'acf/prevent_access_to_unknown_fields', false ) || ( 'option' === $decoded['type'] && 'options' !== $decoded['id'] ) ) {
			$allow_load = false;
		}
	}

	// If we're using a non options_ option key, ensure we have a valid reference key.
	if ( 'option' === $decoded['type'] && 'options' !== $decoded['id'] ) {
		// TODO: Move this into options meta class? i.e. return false
		$meta = acf_get_metadata_by_field( $post_id, $field, true );

		if ( ! $meta ) {
			$allow_load = false;
		} elseif ( $meta !== $field['key'] ) {
			if ( ! isset( $field['__key'] ) || $meta !== $field['__key'] ) {
				$allow_load = false;
			}
		}
	}

	// Load Store.
	$store = acf_get_store( 'values' );

	// If we're allowing load, check the store or load value from database.
	if ( $allow_load ) {
		if ( $store->has( "$post_id:$field_name" ) ) {
			return $store->get( "$post_id:$field_name" );
		}

		$value = acf_get_metadata_by_field( $post_id, $field );
	}

	// Use field's default_value if no meta was found.
	if ( $value === null && isset( $field['default_value'] ) ) {
		$value = $field['default_value'];
	}

	/**
	 * Filters the $value after it has been loaded.
	 *
	 * @date    28/09/13
	 * @since   5.0.0
	 *
	 * @param   mixed $value The value to preview.
	 * @param   string $post_id The post ID for this value.
	 * @param   array $field The field array.
	 */
	$value = apply_filters( 'acf/load_value', $value, $post_id, $field );

	// Update store if we allowed the value load.
	if ( $allow_load ) {
		$store->set( "$post_id:$field_name", $value );
	}

	// Return value.
	return $value;
}

// Register variation.
acf_add_filter_variations( 'acf/load_value', array( 'type', 'name', 'key' ), 2 );

/**
 * Returns a formatted version of the provided value.
 *
 * @since   5.0.0
 *
 * @param mixed          $value       The field value.
 * @param integer|string $post_id     The post id.
 * @param array          $field       The field array.
 * @param boolean        $escape_html Ask the field for a HTML safe version of it's output.
 *
 * @return mixed
 */
function acf_format_value( $value, $post_id, $field, $escape_html = false ) {

	// Allow filter to short-circuit load_value logic.
	$check = apply_filters( 'acf/pre_format_value', null, $value, $post_id, $field, $escape_html );
	if ( $check !== null ) {
		return $check;
	}

	// Get field name.
	$field_name = $field['name'];
	$cache_name = $escape_html ? "$post_id:$field_name:escaped" : "$post_id:$field_name:formatted";

	// Check store.
	$store = acf_get_store( 'values' );
	if ( $store->has( $cache_name ) ) {
		return $store->get( $cache_name );
	}

	/**
	 * Filters the $value for use in a template function.
	 *
	 * @since   5.0.0
	 *
	 * @param mixed   $value       The value to preview.
	 * @param string  $post_id     The post ID for this value.
	 * @param array   $field       The field array.
	 * @param boolean $escape_html Ask the field for a HTML safe version of it's output.
	 */
	$value = apply_filters( 'acf/format_value', $value, $post_id, $field, $escape_html );

	// Update store.
	$store->set( $cache_name, $value );

	// Return value.
	return $value;
}

// Register variation.
acf_add_filter_variations( 'acf/format_value', array( 'type', 'name', 'key' ), 2 );

/**
 * acf_update_value
 *
 * Updates the value for a given field and post_id.
 *
 * @date    28/09/13
 * @since   5.0.0
 *
 * @param   mixed        $value   The new value.
 * @param   (int|string) $post_id The post id.
 * @param   array        $field   The field array.
 * @return  boolean
 */
function acf_update_value( $value, $post_id, $field ) {

	// Allow filter to short-circuit update_value logic.
	$check = apply_filters( 'acf/pre_update_value', null, $value, $post_id, $field );
	if ( $check !== null ) {
		return $check;
	}

	/**
	 * Filters the $value before it is updated.
	 *
	 * @date    28/09/13
	 * @since   5.0.0
	 *
	 * @param   mixed $value The value to update.
	 * @param   string $post_id The post ID for this value.
	 * @param   array $field The field array.
	 * @param   mixed $original The original value before modification.
	 */
	$value = apply_filters( 'acf/update_value', $value, $post_id, $field, $value );

	// Allow null to delete value.
	if ( $value === null ) {
		return acf_delete_value( $post_id, $field );
	}

	// Update value and reference key.
	$return = acf_update_metadata_by_field( $post_id, $field, $value );
	acf_update_metadata_by_field( $post_id, $field, $field['key'], true );

	// Delete stored data.
	acf_flush_value_cache( $post_id, $field['name'] );

	// Return update status.
	return $return;
}

// Register variation.
acf_add_filter_variations( 'acf/update_value', array( 'type', 'name', 'key' ), 2 );

/**
 * acf_update_values
 *
 * Updates an array of values.
 *
 * @date    26/2/19
 * @since   5.7.13
 *
 * @param   array values The array of values.
 * @param   (int|string)                     $post_id The post id.
 * @return  void
 */
function acf_update_values( $values, $post_id ) {

	// Loop over values.
	foreach ( $values as $key => $value ) {

		// Get field.
		$field = acf_get_field( $key );

		// Update value.
		if ( $field ) {
			acf_update_value( $value, $post_id, $field );
		}
	}
}

/**
 * acf_flush_value_cache
 *
 * Deletes all cached data for this value.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   (int|string) $post_id    The post id.
 * @param   string       $field_name The field name.
 * @return  void
 */
function acf_flush_value_cache( $post_id = 0, $field_name = '' ) {

	// Delete stored data.
	acf_get_store( 'values' )
		->remove( "$post_id:$field_name" )
		->remove( "$post_id:$field_name:formatted" )
		->remove( "$post_id:$field_name:escaped" );
}

/**
 * acf_delete_value
 *
 * Deletes the value for a given field and post_id.
 *
 * @date    28/09/13
 * @since   5.0.0
 *
 * @param   (int|string) $post_id The post id.
 * @param   array        $field   The field array.
 * @return  boolean
 */
function acf_delete_value( $post_id, $field ) {

	/**
	 * Fires before a value is deleted.
	 *
	 * @date    28/09/13
	 * @since   5.0.0
	 *
	 * @param   string $post_id The post ID for this value.
	 * @param   mixed $name The meta name.
	 * @param   array $field The field array.
	 */
	do_action( 'acf/delete_value', $post_id, $field['name'], $field );

	// Delete value and reference key.
	$return = acf_delete_metadata_by_field( $post_id, $field );
	acf_delete_metadata_by_field( $post_id, $field, true );

	// Delete stored data.
	acf_flush_value_cache( $post_id, $field['name'] );

	// Return delete status.
	return $return;
}

// Register variation.
acf_add_filter_variations( 'acf/delete_value', array( 'type', 'name', 'key' ), 2 );

/**
 * acf_preview_value
 *
 * Return a human friendly 'preview' for a given field value.
 *
 * @date    28/09/13
 * @since   5.0.0
 *
 * @param   mixed        $value   The new value.
 * @param   (int|string) $post_id The post id.
 * @param   array        $field   The field array.
 * @return  boolean
 */
function acf_preview_value( $value, $post_id, $field ) {

	/**
	 * Filters the $value before used in HTML.
	 *
	 * @date    24/10/16
	 * @since   5.5.0
	 *
	 * @param   mixed $value The value to preview.
	 * @param   string $post_id The post ID for this value.
	 * @param   array $field The field array.
	 */
	return apply_filters( 'acf/preview_value', $value, $post_id, $field );
}

// Register variation.
acf_add_filter_variations( 'acf/preview_value', array( 'type', 'name', 'key' ), 2 );

/**
 * Potentially log an error if a field doesn't exist when we expect it to.
 *
 * @param array  $field    An array representing the field that a value was requested for.
 * @param string $function The function that noticed the problem.
 *
 * @return void
 */
function acf_log_invalid_field_notice( $field, $function ) {
	// If "init" has fired, ACF probably wasn't initialized early.
	if ( did_action( 'init' ) ) {
		return;
	}

	$error_text = sprintf(
		// This happens too early for translations to be loaded properly.
		'<strong>%1$s</strong> - We\'ve detected one or more calls to retrieve ACF field values before ACF has been initialized. This is not supported and can result in malformed or missing data. <a href="%2$s" target="_blank">Learn how to fix this</a>.',
		acf_get_setting( 'name' ),
		acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/acf-field-functions/', 'docs', 'early_init_warning' )
	);
	_doing_it_wrong( esc_html( $function ), acf_esc_html( $error_text ), '5.11.1' );
}
add_action( 'acf/get_invalid_field_value', 'acf_log_invalid_field_notice', 10, 2 );
