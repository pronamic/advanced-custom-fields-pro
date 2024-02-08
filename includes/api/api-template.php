<?php

/**
 * This function will return a custom field value for a specific field name/key + post_id.
 * There is a 3rd parameter to turn on/off formating. This means that an image field will not use
 * its 'return option' to format the value but return only what was saved in the database
 *
 * @since   3.6
 *
 * @param string  $selector     The field name or key.
 * @param mixed   $post_id      The post_id of which the value is saved against.
 * @param boolean $format_value Whether or not to format the value as described above.
 * @param boolean $escape_html  If we're formatting the value, make sure it's also HTML safe.
 *
 * @return mixed
 */
function get_field( $selector, $post_id = false, $format_value = true, $escape_html = false ) {

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id );

	// create dummy field
	$dummy_field = false;
	if ( ! $field ) {
		$field = acf_get_valid_field(
			array(
				'name' => $selector,
				'key'  => '',
				'type' => '',
			)
		);

		// prevent formatting, flag as dummy in case $escape_html is true.
		$format_value = false;
		$dummy_field  = true;
	}

	// get value for field
	$value = acf_get_value( $post_id, $field );

	// escape html is only compatible when formatting the value too
	if ( ! $dummy_field && ! $format_value && $escape_html ) {
		_doing_it_wrong( __FUNCTION__, __( 'Returning an escaped HTML value is only possible when format_value is also true. The field value has not been returned for security.', 'acf' ), '6.2.6' ); //phpcs:ignore -- escape not required.
		return false;
	}

	// format value
	if ( $format_value ) {
		if ( $escape_html ) {
			// return the escaped HTML version if requested.
			if ( acf_field_type_supports( $field['type'], 'escaping_html' ) ) {
				$value = acf_format_value( $value, $post_id, $field, true );
			} else {
				$new_value = acf_format_value( $value, $post_id, $field );
				if ( is_array( $new_value ) ) {
					$value = map_deep( $new_value, 'acf_esc_html' );
				} else {
					$value = acf_esc_html( $new_value );
				}
			}
		} else {
			// get value for field
			$value = acf_format_value( $value, $post_id, $field );
		}
	}

	// If we've built a dummy text field, we won't format the value, but they may still request it escaped. Use `acf_esc_html`
	if ( $dummy_field && $escape_html ) {
		if ( is_array( $value ) ) {
			$value = map_deep( $value, 'acf_esc_html' );
		} else {
			$value = acf_esc_html( $value );
		}
	}

	// return
	return $value;
}

/**
 * This function is the same as echo get_field(), but will soon escape the value by default.
 *
 * @since   1.0.3
 *
 * @param string  $selector     The field name or key.
 * @param mixed   $post_id      The post_id of which the value is saved against.
 * @param boolean $format_value Enable formatting of value.
 *
 * @return void
 */
function the_field( $selector, $post_id = false, $format_value = true ) {
	$field = get_field_object( $selector, $post_id, $format_value, true, false );
	$value = $field ? $field['value'] : get_field( $selector, $post_id, $format_value, false );

	if ( is_array( $value ) ) {
		$value = implode( ', ', $value );
	}

	// If we're not a scalar we'd throw an error, so return early for safety.
	if ( ! is_scalar( $value ) ) {
		return;
	}

	$field_type = is_array( $field ) && isset( $field['type'] ) ? $field['type'] : 'text';

	if ( ! apply_filters( 'acf/the_field/allow_unsafe_html', false, $selector, $post_id, $field_type, $field ) ) {
		$new_value = get_field( $selector, $post_id, $format_value, $format_value );

		if ( is_array( $new_value ) ) {
			$new_value = implode( ', ', $new_value );
		}

		// If $format_value is false, we've not been able to apply field level escaping as we're giving the raw DB value. Escape the output with `acf_esc_html`.
		if ( ! $format_value ) {
			$new_value = acf_esc_html( $new_value );
		}

		if ( (string) $value !== (string) $new_value ) {
			if ( apply_filters( 'acf/the_field/escape_html_optin', false ) ) {
				$value = $new_value;
				do_action( 'acf/removed_unsafe_html', __FUNCTION__, $selector, $field, $post_id );
			} else {
				do_action( 'acf/will_remove_unsafe_html', __FUNCTION__, $selector, $field, $post_id );
			}
		}
		unset( $new_value );
	}

	echo $value; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by logic above.
}

/**
 * Logs instances of ACF successfully escaping unsafe HTML.
 *
 * @since 6.2.5
 *
 * @param string $function The function that resulted in HTML being escaped.
 * @param string $selector The selector (field key, name, etc.) passed to that function.
 * @param array  $field    The field being queried when HTML was escaped.
 * @param mixed  $post_id  The post ID the function was called on.
 * @return void
 */
function _acf_log_escaped_html( $function, $selector, $field, $post_id ) {
	// If the notice isn't shown, no use in logging the errors.
	if ( apply_filters( 'acf/admin/prevent_escaped_html_notice', false ) ) {
		return;
	}

	// If the field isn't set, we've output a non-ACF field, so don't log anything.
	if ( ! is_array( $field ) ) {
		return;
	}

	$escaped = _acf_get_escaped_html_log();

	// Only store up to 100 results at a time.
	if ( count( $escaped ) >= 100 ) {
		return;
	}

	// Bail if we already logged an error for this field.
	if ( isset( $escaped[ $field['key'] ] ) ) {
		return;
	}

	$escaped[ $field['key'] ] = array(
		'selector' => $selector,
		'function' => $function,
		'field'    => $field['label'],
		'post_id'  => $post_id,
	);

	_acf_update_escaped_html_log( $escaped );
}
add_action( 'acf/removed_unsafe_html', '_acf_log_escaped_html', 10, 4 );

/**
 * Logs instances of where ACF will soon escape HTML using the_field.
 *
 * @since 6.2.5
 *
 * @param string $function The function that resulted in HTML being escaped.
 * @param string $selector The selector (field key, name, etc.) passed to that function.
 * @param array  $field    The field being queried when HTML was escaped.
 * @param mixed  $post_id  The post ID the function was called on.
 * @return void
 */
function _acf_log_will_escape_html( $function, $selector, $field, $post_id ) {
	// If the notice isn't shown, no use in logging the errors.
	if ( apply_filters( 'acf/admin/prevent_escaped_html_notice', false ) ) {
		return;
	}

	// If the field isn't set, we've output a non-ACF field, so don't log anything.
	if ( ! is_array( $field ) ) {
		return;
	}

	$will_escape = _acf_get_will_escape_html_log();

	// Only store up to 100 results at a time.
	if ( count( $will_escape ) >= 100 ) {
		return;
	}

	// Bail if we already logged an error for this field.
	if ( isset( $will_escape[ $field['key'] ] ) ) {
		return;
	}

	$will_escape[ $field['key'] ] = array(
		'selector' => $selector,
		'function' => $function,
		'field'    => $field['name'],
		'post_id'  => $post_id,
	);

	_acf_update_will_escape_html_log( $will_escape );
}
add_action( 'acf/will_remove_unsafe_html', '_acf_log_will_escape_html', 10, 4 );

/**
 * Returns an array of instances where HTML was altered due to escaping in the_field or a shortcode.
 *
 * @since 6.2.5
 *
 * @return array
 */
function _acf_get_escaped_html_log() {
	$escaped = get_option( 'acf_escaped_html_log', array() );
	return is_array( $escaped ) ? $escaped : array();
}

/**
 * Updates the array of instances where HTML was altered due to escaping in the_field or a shortcode.
 *
 * @since 6.2.5
 *
 * @param array $escaped The array of instances.
 * @return boolean True on success, or false on failure.
 */
function _acf_update_escaped_html_log( $escaped = array() ) {
	return update_option( 'acf_escaped_html_log', (array) $escaped, true );
}

/**
 * Deletes the array of instances where HTML was altered due to escaping in the_field or a shortcode.
 *
 * @since 6.2.5
 *
 * @return boolean True on success, or false on failure.
 */
function _acf_delete_escaped_html_log() {
	return delete_option( 'acf_escaped_html_log' );
}

/**
 * Returns an array of instances where HTML will be escaped in the_field().
 *
 * @since 6.2.5
 *
 * @return array
 */
function _acf_get_will_escape_html_log() {
	$will_escape = get_option( 'acf_will_escape_html_log', array() );
	return is_array( $will_escape ) ? $will_escape : array();
}

/**
 * Updates the array of instances where HTML will be escaped in the_field().
 *
 * @since 6.2.5
 *
 * @param array $escaped The array of instances.
 * @return boolean True on success, or false on failure.
 */
function _acf_update_will_escape_html_log( $escaped = array() ) {
	return update_option( 'acf_will_escape_html_log', (array) $escaped, true );
}

/**
 * Deletes the array of instances where HTML will be escaped in the_field().
 *
 * @since 6.2.5
 *
 * @return boolean True on success, or false on failure.
 */
function _acf_delete_will_escape_html_log() {
	return delete_option( 'acf_will_escape_html_log' );
}

/**
 * This function will return an array containing all the field data for a given field_name.
 *
 * @since 3.6
 *
 * @param string  $selector     The field name or key.
 * @param mixed   $post_id      The post_id of which the value is saved against.
 * @param boolean $format_value Whether to format the field value.
 * @param boolean $load_value   Whether to load the field value.
 * @param boolean $escape_html  Should the field return a HTML safe formatted value if $format_value is true.
 *
 * @return array|false $field
 */
function get_field_object( $selector, $post_id = false, $format_value = true, $load_value = true, $escape_html = false ) {
	// Compatibility with ACF ~4.
	if ( is_array( $format_value ) && isset( $format_value['format_value'] ) ) {
		$format_value = $format_value['format_value'];
	}

	$post_id = acf_get_valid_post_id( $post_id );
	$field   = acf_maybe_get_field( $selector, $post_id );

	if ( ! $field ) {
		return false;
	}

	if ( $load_value ) {
		$field['value'] = acf_get_value( $post_id, $field );
	}

	// escape html is only compatible when formatting the value too
	if ( ! $format_value && $escape_html ) {
		_doing_it_wrong( __FUNCTION__, __( 'Returning an escaped HTML value is only possible when format_value is also true. The field value has not been returned for security.', 'acf' ), '6.2.6' ); //phpcs:ignore -- escape not required.
		$field['value'] = false;
		return $field;
	}

	// format value
	if ( $load_value && $format_value ) {
		if ( $escape_html ) {
			// return the escaped HTML version if requested.
			if ( acf_field_type_supports( $field['type'], 'escaping_html' ) ) {
				$field['value'] = acf_format_value( $field['value'], $post_id, $field, true );
			} else {
				$new_value = acf_format_value( $field['value'], $post_id, $field );
				if ( is_array( $new_value ) ) {
					$field['value'] = map_deep( $new_value, 'acf_esc_html' );
				} else {
					$field['value'] = acf_esc_html( $new_value );
				}
			}
		} else {
			// get value for field
			$field['value'] = acf_format_value( $field['value'], $post_id, $field );
		}
	}

	return $field;
}

/**
 * This function will return a field for the given selector.
 * It will also review the field_reference to ensure the correct field is returned which makes it useful for the template API
 *
 * @since   5.2.3
 *
 * @param   $selector (mixed) identifier of field. Can be an ID, key, name or post object
 * @param   $post_id (mixed) the post_id of which the value is saved against
 * @param   $strict (boolean) if true, return a field only when a field key is found.
 *
 * @return  $field (array)
 */
function acf_maybe_get_field( $selector, $post_id = false, $strict = true ) {

	// init
	acf_init();

	// Check if field key was given.
	if ( acf_is_field_key( $selector ) ) {
		return acf_get_field( $selector );
	}

	// Lookup field via reference.
	$post_id = acf_get_valid_post_id( $post_id );
	$field   = acf_get_meta_field( $selector, $post_id );
	if ( $field ) {
		return $field;
	}

	// Lookup field loosely via name.
	if ( ! $strict ) {
		return acf_get_field( $selector );
	}

	// Return no result.
	return false;
}

/**
 * This function will attempt to find a sub field
 *
 * @since   5.4.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_maybe_get_sub_field( $selectors, $post_id = false, $strict = true ) {

	// bail early if not enough selectors
	if ( ! is_array( $selectors ) || count( $selectors ) < 3 ) {
		return false;
	}

	// vars
	$offset    = acf_get_setting( 'row_index_offset' );
	$selector  = acf_extract_var( $selectors, 0 );
	$selectors = array_values( $selectors ); // reset keys

	// attempt get field
	$field = acf_maybe_get_field( $selector, $post_id, $strict );

	// bail early if no field
	if ( ! $field ) {
		return false;
	}

	// loop
	for ( $j = 0; $j < count( $selectors ); $j += 2 ) {

		// vars
		$sub_i      = $selectors[ $j ];
		$sub_s      = $selectors[ $j + 1 ];
		$field_name = $field['name'];

		// find sub field
		$field = acf_get_sub_field( $sub_s, $field );

		// bail early if no sub field
		if ( ! $field ) {
			return false;
		}

		// add to name
		$field['name'] = $field_name . '_' . ( $sub_i - $offset ) . '_' . $field['name'];
	}

	// return
	return $field;
}

/**
 * This function will return an array containing all the custom field values for a specific post_id.
 * The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the values.
 *
 * @since   3.6
 *
 * @param mixed   $post_id      The post_id of which the value is saved against.
 * @param boolean $format_value Whether or not to format the field value.
 * @param boolean $escape_html  Should the field return a HTML safe formatted value if $format_value is true.
 *
 * @return array associative array where field name => field value
 */
function get_fields( $post_id = false, $format_value = true, $escape_html = false ) {

	// escape html is only compatible when formatting the value too
	if ( ! $format_value && $escape_html ) {
		_doing_it_wrong( __FUNCTION__, __( 'Returning escaped HTML values is only possible when format_value is also true. The field values have not been returned for security.', 'acf' ), '6.2.6' ); //phpcs:ignore -- escape not required.
		return false;
	}

	// vars
	$fields = get_field_objects( $post_id, $format_value, true, $escape_html );
	$meta   = array();

	// bail early
	if ( ! $fields ) {
		return false;
	}

	// populate
	foreach ( $fields as $k => $field ) {
		$meta[ $k ] = $field['value'];
	}

	// return
	return $meta;
}


/**
 * This function will return an array containing all the custom field objects for a specific post_id.
 * The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the fields / values.
 *
 * @since 3.6
 *
 * @param mixed   $post_id      The post_id of which the value is saved against.
 * @param boolean $format_value Whether or not to format the field value.
 * @param boolean $load_value   Whether or not to load the field value.
 * @param boolean $escape_html  Should the field return a HTML safe formatted value if $format_value is true.
 *
 * @return array associative array where field name => field
 */
function get_field_objects( $post_id = false, $format_value = true, $load_value = true, $escape_html = false ) {

	// init
	acf_init();

	// validate post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get meta
	$meta = acf_get_meta( $post_id );

	// bail early if no meta
	if ( empty( $meta ) ) {
		return false;
	}

	// escape html is only compatible when formatting the value too
	if ( ! $format_value && $escape_html ) {
		_doing_it_wrong( __FUNCTION__, __( 'Returning escaped HTML values is only possible when format_value is also true. The field values have not been returned for security.', 'acf' ), '6.2.6' ); //phpcs:ignore -- escape not required.
	}

	// populate vars
	$fields = array();
	foreach ( $meta as $key => $value ) {

		// bail if reference key does not exist
		if ( ! isset( $meta[ "_$key" ] ) || ( ! is_string( $meta[ "_$key" ] ) && ! is_numeric( $meta[ "_$key" ] ) ) ) {
			continue;
		}

		// get field
		$field = acf_get_field( $meta[ "_$key" ] );

		// bail early if no field, or if the field's name is different to $key
		// - solves problem where sub fields (and clone fields) are incorrectly allowed
		if ( ! $field || $field['name'] !== $key ) {
			continue;
		}

		// load value
		if ( $load_value ) {
			$field['value'] = acf_get_value( $post_id, $field );
		}

		// avoid returning field values when the function is called incorrectly.
		if ( ! $format_value && $escape_html ) {
			$field['value'] = false;
		}

		// format value
		if ( $load_value && $format_value ) {
			if ( $escape_html ) {
				// return the escaped HTML version if requested.
				if ( acf_field_type_supports( $field['type'], 'escaping_html' ) ) {
					$field['value'] = acf_format_value( $field['value'], $post_id, $field, true );
				} else {
					$new_value = acf_format_value( $field['value'], $post_id, $field );
					if ( is_array( $new_value ) ) {
						$field['value'] = map_deep( $new_value, 'acf_esc_html' );
					} else {
						$field['value'] = acf_esc_html( $new_value );
					}
				}
			} else {
				// get value for field
				$field['value'] = acf_format_value( $field['value'], $post_id, $field );
			}
		}

		// append to $value
		$fields[ $key ] = $field;
	}

	// no value
	if ( empty( $fields ) ) {
		return false;
	}

	// return
	return $fields;
}


/**
 * Checks if a field (such as Repeater or Flexible Content) has any rows of data to loop over.
 * This function is intended to be used in conjunction with the_row() to step through available values.
 *
 * @since   4.3.0
 *
 * @param   string $selector The field name or field key.
 * @param   mixed  $post_id  The post ID where the value is saved. Defaults to the current post.
 * @return  boolean
 */
function have_rows( $selector, $post_id = false ) {

	// Validate and backup $post_id.
	$_post_id = $post_id;
	$post_id  = acf_get_valid_post_id( $post_id );

	// Vars.
	$key         = "selector={$selector}/post_id={$post_id}";
	$active_loop = acf_get_loop( 'active' );
	$prev_loop   = acf_get_loop( 'previous' );
	$new_loop    = false;
	$sub_field   = false;

	// Check if no active loop.
	if ( ! $active_loop ) {
		$new_loop = 'parent';

		// Detect "change" compared to the active loop.
	} elseif ( $key !== $active_loop['key'] ) {

		// Find sub field and check if a sub value exists.
		$sub_field_exists = false;
		$sub_field        = acf_get_sub_field( $selector, $active_loop['field'] );
		if ( $sub_field ) {
			$sub_field_exists = isset( $active_loop['value'][ $active_loop['i'] ][ $sub_field['key'] ] );
		}

		// Detect change in post_id.
		if ( $post_id != $active_loop['post_id'] ) {

			// Case: Change in $post_id was due to this being a nested loop and not specifying the $post_id.
			// Action: Move down one level into a new loop.
			if ( empty( $_post_id ) && $sub_field_exists ) {
				$new_loop = 'child';

				// Case: Change in $post_id was due to a nested loop ending.
				// Action: move up one level through the loops.
			} elseif ( $prev_loop && $prev_loop['post_id'] == $post_id ) {
				acf_remove_loop( 'active' );
				$active_loop = $prev_loop;

				// Case: Chang in $post_id is the most obvious, used in an WP_Query loop with multiple $post objects.
				// Action: leave this current loop alone and create a new parent loop.
			} else {
				$new_loop = 'parent';
			}

			// Detect change in selector.
		} elseif ( $selector != $active_loop['selector'] ) {

			// Case: Change in $field_name was due to this being a nested loop.
			// Action: move down one level into a new loop.
			if ( $sub_field_exists ) {
				$new_loop = 'child';

				// Case: Change in $field_name was due to a nested loop ending.
				// Action: move up one level through the loops.
			} elseif ( $prev_loop && $prev_loop['selector'] == $selector && $prev_loop['post_id'] == $post_id ) {
				acf_remove_loop( 'active' );
				$active_loop = $prev_loop;

				// Case: Change in $field_name is the most obvious, this is a new loop for a different field within the $post.
				// Action: leave this current loop alone and create a new parent loop.
			} else {
				$new_loop = 'parent';
			}
		}
	}

	// Add loop if required.
	if ( $new_loop ) {
		$args = array(
			'key'      => $key,
			'selector' => $selector,
			'post_id'  => $post_id,
			'name'     => null,
			'value'    => null,
			'field'    => null,
			'i'        => -1,
		);

		// Case: Parent loop.
		if ( $new_loop === 'parent' ) {
			$field = get_field_object( $selector, $post_id, false );
			if ( $field ) {
				$args['field'] = $field;
				$args['value'] = $field['value'];
				$args['name']  = $field['name'];
				unset( $args['field']['value'] );
			}

			// Case: Child loop ($sub_field must exist).
		} else {
			$args['field']   = $sub_field;
			$args['value']   = $active_loop['value'][ $active_loop['i'] ][ $sub_field['key'] ];
			$args['name']    = "{$active_loop['name']}_{$active_loop['i']}_{$sub_field['name']}";
			$args['post_id'] = $active_loop['post_id'];
		}

		// Bail early if value is either empty or a non array.
		if ( ! $args['value'] || ! is_array( $args['value'] ) ) {
			return false;
		}

		// Allow for non repeatable data for Group and Clone fields.
		if ( acf_get_field_type_prop( $args['field']['type'], 'have_rows' ) === 'single' ) {
			$args['value'] = array( $args['value'] );
		}

		// Add loop.
		$active_loop = acf_add_loop( $args );
	}

	// Return true if next row exists.
	if ( $active_loop && isset( $active_loop['value'][ $active_loop['i'] + 1 ] ) ) {
		return true;
	}

	// Return false if no next row.
	acf_remove_loop( 'active' );
	return false;
}


/**
 * This function will progress the global repeater or flexible content value 1 row
 *
 * @since   4.3.0
 *
 * @param   N/A
 * @return  (array) the current row data
 */
function the_row( $format = false ) {

	// vars
	$i = acf_get_loop( 'active', 'i' );

	// increase
	++$i;

	// update
	acf_update_loop( 'active', 'i', $i );

	// return
	return get_row( $format );
}

function get_row( $format = false ) {

	// vars
	$loop = acf_get_loop( 'active' );

	// bail early if no loop
	if ( ! $loop ) {
		return false;
	}

	// get value
	$value = acf_maybe_get( $loop['value'], $loop['i'] );

	// bail early if no current value
	// possible if get_row_layout() is called before the_row()
	if ( ! $value ) {
		return false;
	}

	// format
	if ( $format ) {

		// vars
		$field = $loop['field'];

		// single row
		if ( acf_get_field_type_prop( $field['type'], 'have_rows' ) === 'single' ) {

			// format value
			$value = acf_format_value( $value, $loop['post_id'], $field );

			// multiple rows
		} else {

			// format entire value
			// - solves problem where cached value is incomplete
			// - no performance issues here thanks to cache
			$value = acf_format_value( $loop['value'], $loop['post_id'], $field );
			$value = acf_maybe_get( $value, $loop['i'] );
		}
	}

	// return
	return $value;
}

function get_row_index() {

	// vars
	$i      = acf_get_loop( 'active', 'i' );
	$offset = acf_get_setting( 'row_index_offset' );

	// return
	return $offset + $i;
}

function the_row_index() {

	echo get_row_index();
}


/**
 * This function is used inside a 'has_sub_field' while loop to return a sub field object
 *
 * @since   5.3.8
 *
 * @param   $selector (string)
 * @return  (array)
 */
function get_row_sub_field( $selector ) {

	// vars
	$row = acf_get_loop( 'active' );

	// bail early if no row
	if ( ! $row ) {
		return false;
	}

	// attempt to find sub field
	$sub_field = acf_get_sub_field( $selector, $row['field'] );

	// bail early if no field
	if ( ! $sub_field ) {
		return false;
	}

	// update field's name based on row data
	$sub_field['name'] = "{$row['name']}_{$row['i']}_{$sub_field['name']}";

	// return
	return $sub_field;
}


/**
 * This function is used inside a 'has_sub_field' while loop to return a sub field value
 *
 * @since   5.3.8
 *
 * @param   $selector (string)
 * @return  (mixed)
 */
function get_row_sub_value( $selector ) {

	// vars
	$row = acf_get_loop( 'active' );

	// bail early if no row
	if ( ! $row ) {
		return null;
	}

	// return value
	if ( isset( $row['value'][ $row['i'] ][ $selector ] ) ) {
		return $row['value'][ $row['i'] ][ $selector ];
	}

	// return
	return null;
}


/**
 * This function will find the current loop and unset it from the global array.
 * To be used when loop finishes or a break is used
 *
 * @since   5.0.0
 *
 * @param   $hard_reset (boolean) completely wipe the global variable, or just unset the active row
 * @return  (boolean)
 */
function reset_rows() {

	// remove last loop
	acf_remove_loop( 'active' );

	// return
	return true;
}


/**
 * This function is used inside a while loop to return either true or false (loop again or stop).
 * When using a repeater or flexible content field, it will loop through the rows until
 * there are none left or a break is detected
 *
 * @since   1.0.3
 *
 * @param   $field_name (string) the field name
 * @param   $post_id (mixed) the post_id of which the value is saved against
 * @return  (boolean)
 */
function has_sub_field( $field_name, $post_id = false ) {

	// vars
	$r = have_rows( $field_name, $post_id );

	// if has rows, progress through 1 row for the while loop to work
	if ( $r ) {
		the_row();
	}

	// return
	return $r;
}

/**
 * Alias of has_sub_field
 */
function has_sub_fields( $field_name, $post_id = false ) {
	return has_sub_field( $field_name, $post_id );
}


/**
 * This function is used inside a 'has_sub_field' while loop to return a sub field value
 *
 * @since 1.0.3
 *
 * @param string  $selector     The field name or key.
 * @param boolean $format_value Whether or not to format the value as described above.
 * @param boolean $escape_html  If we're formatting the value, make sure it's also HTML safe.
 *
 * @return mixed
 */
function get_sub_field( $selector = '', $format_value = true, $escape_html = false ) {

	// get sub field
	$sub_field = get_sub_field_object( $selector, $format_value, true, $escape_html );

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// return
	return $sub_field['value'];
}


/**
 * This function is the same as echo get_sub_field
 *
 * @since   1.0.3
 *
 * @param string  $field_name   The field name.
 * @param boolean $format_value Format the value before output.
 */
function the_sub_field( $field_name, $format_value = true ) {
	$field = get_sub_field_object( $field_name, $format_value );
	$value = ( is_array( $field ) && isset( $field['value'] ) ) ? $field['value'] : false;

	if ( is_array( $value ) ) {
		$value = implode( ', ', $value );
	}

	// If we're not a scalar we'd throw an error, so return early for safety.
	if ( ! is_scalar( $value ) ) {
		return;
	}

	$field_type = is_array( $field ) && isset( $field['type'] ) ? $field['type'] : 'text';

	if ( ! apply_filters( 'acf/the_field/allow_unsafe_html', false, $field_name, 'sub_field', $field_type, $field ) ) {
		$field     = get_sub_field_object( $field_name, $format_value, true, true );
		$new_value = ( is_array( $field ) && isset( $field['value'] ) ) ? $field['value'] : false;

		if ( is_array( $new_value ) ) {
			$new_value = implode( ', ', $new_value );
		}

		// If $format_value is false, we've not been able to apply field level escaping as we're giving the raw DB value. Escape the output with `acf_esc_html`.
		if ( ! $format_value ) {
			$new_value = acf_esc_html( $new_value );
		}

		if ( (string) $value !== (string) $new_value ) {
			if ( apply_filters( 'acf/the_field/escape_html_optin', false ) ) {
				$value = $new_value;
				do_action( 'acf/removed_unsafe_html', __FUNCTION__, $field_name, $field, false );
			} else {
				do_action( 'acf/will_remove_unsafe_html', __FUNCTION__, $field_name, $field, false );
			}
		}
		unset( $new_value );
	}

	echo $value; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside get_sub_field_object where necessary.
}


/**
 * This function is used inside a 'has_sub_field' while loop to return a sub field object
 *
 * @since 3.5.8.1
 *
 * @param string  $selector     The field name or key.
 * @param boolean $format_value Whether to format the field value.
 * @param boolean $load_value   Whether to load the field value.
 * @param boolean $escape_html  Should the field return a HTML safe formatted value.
 *
 * @return mixed
 */
function get_sub_field_object( $selector, $format_value = true, $load_value = true, $escape_html = false ) {

	$row = acf_get_loop( 'active' );

	// bail early if no row
	if ( ! $row ) {
		return false;
	}

	// attempt to find sub field
	$sub_field = get_row_sub_field( $selector );

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// load value
	if ( $load_value ) {
		$sub_field['value'] = get_row_sub_value( $sub_field['key'] );
	}

	// escape html is only compatible when formatting the value too
	if ( ! $format_value && $escape_html ) {
		_doing_it_wrong( __FUNCTION__, __( 'Returning an escaped HTML value is only possible when format_value is also true. The field value has not been returned for security.', 'acf' ), '6.2.6' ); //phpcs:ignore -- escape not required.
		$sub_field['value'] = false;
	}

	// format value
	if ( $load_value && $format_value ) {
		if ( $escape_html ) {
			// return the escaped HTML version if requested.
			if ( acf_field_type_supports( $sub_field['type'], 'escaping_html' ) ) {
				$sub_field['value'] = acf_format_value( $sub_field['value'], $row['post_id'], $sub_field, true );
			} else {
				$new_value = acf_format_value( $sub_field['value'], $row['post_id'], $sub_field );
				if ( is_array( $new_value ) ) {
					$sub_field['value'] = map_deep( $new_value, 'acf_esc_html' );
				} else {
					$sub_field['value'] = acf_esc_html( $new_value );
				}
			}
		} else {
			// get value for field
			$sub_field['value'] = acf_format_value( $sub_field['value'], $row['post_id'], $sub_field );
		}
	}

	// return
	return $sub_field;
}


/**
 * This function will return a string representation of the current row layout within a 'have_rows' loop
 *
 * @since   3.0.6
 *
 * @return mixed
 */
function get_row_layout() {

	// vars
	$row = get_row();

	// return
	if ( isset( $row['acf_fc_layout'] ) ) {
		return $row['acf_fc_layout'];
	}

	// return
	return false;
}

/**
 * This function is used to add basic shortcode support for the ACF plugin
 * eg. [acf field="heading" post_id="123" format_value="1"]
 *
 * @since 1.1.1
 *
 * @param array $atts The shortcode attributes.
 *
 * @return string
 */
function acf_shortcode( $atts ) {
	// Return if the ACF shortcode is disabled.
	if ( ! acf_get_setting( 'enable_shortcode' ) ) {
		return;
	}

	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		// Prevent the ACF shortcode in FSE block template parts by default.
		if ( ! doing_filter( 'the_content' ) && ! apply_filters( 'acf/shortcode/allow_in_block_themes_outside_content', false ) ) {
			return;
		}
	}

	// Limit previews of ACF shortcode data for users without publish_posts permissions.
	$preview_capability = apply_filters( 'acf/shortcode/preview_capability', 'publish_posts' );
	if ( is_preview() && ! current_user_can( $preview_capability ) ) {
		return apply_filters( 'acf/shortcode/preview_capability_message', __( '[ACF shortcode value disabled for preview]', 'acf' ) );
	}

	// Mitigate issue where some AJAX requests can return ACF field data.
	$ajax_capability = apply_filters( 'acf/ajax/shortcode_capability', 'edit_posts' );
	if ( wp_doing_ajax() && ( $ajax_capability !== false ) && ! current_user_can( $ajax_capability ) ) {
		return;
	}

	$atts = shortcode_atts(
		array(
			'field'        => '',
			'post_id'      => false,
			'format_value' => true,
		),
		$atts,
		'acf'
	);

	$access_already_prevented = apply_filters( 'acf/prevent_access_to_unknown_fields', false );
	$filter_applied           = false;

	if ( ! $access_already_prevented ) {
		$filter_applied = true;
		add_filter( 'acf/prevent_access_to_unknown_fields', '__return_true' );
	}

	// Get the escaped field value.
	$field = get_field_object( $atts['field'], $atts['post_id'], $atts['format_value'], true, true );
	$value = $field ? $field['value'] : get_field( $atts['field'], $atts['post_id'], $atts['format_value'], true );

	if ( is_array( $value ) ) {
		$value = implode( ', ', $value );
	}

	$field_type = is_array( $field ) && isset( $field['type'] ) ? $field['type'] : 'text';

	// Temporarily always get the unescaped version for action comparison.
	$unescaped_value = get_field( $atts['field'], $atts['post_id'], $atts['format_value'], false );

	// Remove the filter preventing access to unknown filters now we've got all the values.
	if ( $filter_applied ) {
		remove_filter( 'acf/prevent_access_to_unknown_fields', '__return_true' );
	}

	if ( is_array( $unescaped_value ) ) {
		$unescaped_value = implode( ', ', $unescaped_value );
	}

	// Handle getting the unescaped version if we're allowed unsafe html.
	if ( apply_filters( 'acf/shortcode/allow_unsafe_html', false, $atts, $field_type, $field ) ) {
		$value = $unescaped_value;
	} elseif ( (string) $value !== (string) $unescaped_value ) {
		do_action( 'acf/removed_unsafe_html', __FUNCTION__, $atts['field'], $field, $atts['post_id'] );
	}

	return $value;
}
add_shortcode( 'acf', 'acf_shortcode' );


/**
 * This function will update a value in the database
 *
 * @since   3.1.9
 *
 * @param string $selector The field name or key.
 * @param mixed  $value    The value to save in the database.
 * @param mixed  $post_id  The post_id of which the value is saved against.
 *
 * @return boolean
 */
function update_field( $selector, $value, $post_id = false ) {

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );

	// create dummy field
	if ( ! $field ) {
		$field = acf_get_valid_field(
			array(
				'name' => $selector,
				'key'  => '',
				'type' => '',
			)
		);
	}

	// save
	return acf_update_value( $value, $post_id, $field );
}


/**
 * This function will update a value of a sub field in the database
 *
 * @since   5.0.0
 *
 * @param   $selector (mixed) the sub field name or key, or an array of ancestors
 * @param   $value (mixed) the value to save in the database
 * @param   $post_id (mixed) the post_id of which the value is saved against
 *
 * @return  boolean
 */
function update_sub_field( $selector, $value, $post_id = false ) {

	// vars
	$sub_field = false;

	// get sub field
	if ( is_array( $selector ) ) {
		$post_id   = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	} else {
		$post_id   = acf_get_loop( 'active', 'post_id' );
		$sub_field = get_row_sub_field( $selector );
	}

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// update
	return acf_update_value( $value, $post_id, $sub_field );
}


/**
 * This function will remove a value from the database
 *
 * @since   3.1.9
 *
 * @param   $selector (string) the field name or key
 * @param   $post_id (mixed) the post_id of which the value is saved against
 *
 * @return  boolean
 */
function delete_field( $selector, $post_id = false ) {

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id );

	// delete
	return $field ? acf_delete_value( $post_id, $field ) : false;
}


/**
 * This function will delete a value of a sub field in the database
 *
 * @since   5.0.0
 *
 * @param   $selector (mixed) the sub field name or key, or an array of ancestors
 * @param   $value (mixed) the value to save in the database
 * @param   $post_id (mixed) the post_id of which the value is saved against
 * @return  (boolean)
 */
function delete_sub_field( $selector, $post_id = false ) {
	return update_sub_field( $selector, null, $post_id );
}


/**
 * This function will add a row of data to a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $row (array)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function add_row( $selector, $row = false, $post_id = false ) {

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );

	// bail early if no field
	if ( ! $field ) {
		return false;
	}

	// get raw value
	$value = acf_get_value( $post_id, $field );

	// ensure array
	$value = acf_get_array( $value );

	// append
	$value[] = $row;

	// Paginated repeaters should be saved normally.
	$field['pagination'] = false;

	// update value
	acf_update_value( $value, $post_id, $field );

	// return
	return count( $value );
}


/**
 * This function will add a row of data to a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $row (array)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function add_sub_row( $selector, $row = false, $post_id = false ) {

	// vars
	$sub_field = false;

	// get sub field
	if ( is_array( $selector ) ) {
		$post_id   = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	} else {
		$post_id   = acf_get_loop( 'active', 'post_id' );
		$sub_field = get_row_sub_field( $selector );
	}

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// get raw value
	$value = acf_get_value( $post_id, $sub_field );

	// ensure array
	$value = acf_get_array( $value );

	// append
	$value[] = $row;

	// update
	acf_update_value( $value, $post_id, $sub_field );

	// return
	return count( $value );
}


/**
 * This function will update a row of data to a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $i (int)
 * @param   $row (array)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function update_row( $selector, $i = 1, $row = false, $post_id = false ) {

	// vars
	$offset = acf_get_setting( 'row_index_offset' );
	$i      = $i - $offset;

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );

	// bail early if no field
	if ( ! $field ) {
		return false;
	}

	// get raw value
	$value = acf_get_value( $post_id, $field );

	// ensure array
	$value = acf_get_array( $value );

	// update
	$value[ $i ] = $row;

	// update value
	acf_update_value( $value, $post_id, $field );

	// return
	return true;
}


/**
 * This function will add a row of data to a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $row (array)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function update_sub_row( $selector, $i = 1, $row = false, $post_id = false ) {

	// vars
	$sub_field = false;
	$offset    = acf_get_setting( 'row_index_offset' );
	$i         = $i - $offset;

	// get sub field
	if ( is_array( $selector ) ) {
		$post_id   = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	} else {
		$post_id   = acf_get_loop( 'active', 'post_id' );
		$sub_field = get_row_sub_field( $selector );
	}

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// get raw value
	$value = acf_get_value( $post_id, $sub_field );

	// ensure array
	$value = acf_get_array( $value );

	// append
	$value[ $i ] = $row;

	// update
	acf_update_value( $value, $post_id, $sub_field );

	// return
	return true;
}


/**
 * This function will delete a row of data from a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $i (int)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function delete_row( $selector, $i = 1, $post_id = false ) {

	// vars
	$offset = acf_get_setting( 'row_index_offset' );
	$i      = $i - $offset;

	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );

	// get field
	$field = acf_maybe_get_field( $selector, $post_id );

	// bail early if no field
	if ( ! $field ) {
		return false;
	}

	// get value
	$value = acf_get_value( $post_id, $field );

	// ensure array
	$value = acf_get_array( $value );

	// bail early if index doesn't exist
	if ( ! isset( $value[ $i ] ) ) {
		return false;
	}

	// unset
	unset( $value[ $i ] );

	// update
	acf_update_value( $value, $post_id, $field );

	// return
	return true;
}


/**
 * This function will add a row of data to a field
 *
 * @since   5.2.3
 *
 * @param   $selector (string)
 * @param   $row (array)
 * @param   $post_id (mixed)
 * @return  (boolean)
 */
function delete_sub_row( $selector, $i = 1, $post_id = false ) {

	// vars
	$sub_field = false;
	$offset    = acf_get_setting( 'row_index_offset' );
	$i         = $i - $offset;

	// get sub field
	if ( is_array( $selector ) ) {
		$post_id   = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	} else {
		$post_id   = acf_get_loop( 'active', 'post_id' );
		$sub_field = get_row_sub_field( $selector );
	}

	// bail early if no sub field
	if ( ! $sub_field ) {
		return false;
	}

	// get raw value
	$value = acf_get_value( $post_id, $sub_field );

	// ensure array
	$value = acf_get_array( $value );

	// bail early if index doesn't exist
	if ( ! isset( $value[ $i ] ) ) {
		return false;
	}

	// append
	unset( $value[ $i ] );

	// update
	acf_update_value( $value, $post_id, $sub_field );

	// return
	return true;
}


/**
 * Depreceated Functions
 *
 * These functions are outdated
 *
 * @since   1.0.0
 *
 * @param   n/a
 * @return  n/a
 */
function create_field( $field ) {

	acf_render_field( $field );
}

function render_field( $field ) {

	acf_render_field( $field );
}

function reset_the_repeater_field() {

	return reset_rows();
}

function the_repeater_field( $field_name, $post_id = false ) {

	return has_sub_field( $field_name, $post_id );
}

function the_flexible_field( $field_name, $post_id = false ) {

	return has_sub_field( $field_name, $post_id );
}

function acf_filter_post_id( $post_id ) {

	return acf_get_valid_post_id( $post_id );
}
