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
 * Get the REST API schema for a given field.
 *
 * @param array $field
 * @return array
 */
function acf_get_field_rest_schema( array $field ) {
	$type   = acf_get_field_type( $field['type'] );
	$schema = array();

	if ( ! is_object( $type ) || ! method_exists( $type, 'get_rest_schema' ) ) {
		return $schema;
	}

	$schema = $type->get_rest_schema( $field );

	/**
	 * Filter the REST API schema for a given field.
	 *
	 * @param array $schema The field schema array.
	 * @param array $field The field array.
	 */
	return (array) apply_filters( 'acf/rest/get_field_schema', $schema, $field );
}

acf_add_filter_variations( 'acf/rest/get_field_schema', array( 'type', 'name', 'key' ), 1 );

/**
 * Get the REST API field links for a given field. The links are appended to the REST response under the _links property
 * and provide API resource links to related objects. If a link is marked as 'embeddable', WordPress can load the resource
 * in the main request under the _embedded property when the request contains the _embed URL parameter.
 *
 * @see \acf_field::get_rest_links()
 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/linking-and-embedding/
 *
 * @param string|integer $post_id
 * @param array          $field
 * @return array
 */
function acf_get_field_rest_links( $post_id, array $field ) {
	$value = acf_get_value( $post_id, $field );
	$type  = acf_get_field_type( $field['type'] );
	$links = $type->get_rest_links( $value, $post_id, $field );

	/**
	 * Filter the REST API links for a given field.
	 *
	 * @param array      $links
	 * @param string|int $post_id
	 * @param array      $field
	 * @param mixed      $value
	 */
	return (array) apply_filters( 'acf/rest/get_field_links', $links, $post_id, $field, $value );
}

acf_add_filter_variations( 'acf/rest/get_field_links', array( 'type', 'name', 'key' ), 2 );

/**
 * Replaces User subfield data with REST-safe values.
 *
 * Walks the subfields of a container field (Group, Clone, Repeater layout row,
 * or Flexible Content layout row), delegating each subfield's value to
 * {@see acf_rest_sanitize_user_data()} so that any nested User field data is
 * reduced to IDs.
 *
 * @since ACF 6.8.7
 *
 * @param mixed  $formatted_value The formatted parent value.
 * @param mixed  $raw_value       The raw parent value.
 * @param array  $sub_fields      The parent field's subfields.
 * @param string $output_property The subfield property used as the output key.
 * @return mixed
 */
function acf_rest_sanitize_user_sub_fields( $formatted_value, $raw_value, $sub_fields, $output_property ) {
	if ( ! is_array( $formatted_value ) || ! is_array( $sub_fields ) ) {
		return $formatted_value;
	}

	$raw_value = is_array( $raw_value ) ? $raw_value : array();

	foreach ( $sub_fields as $sub_field ) {
		if ( ! is_array( $sub_field ) ) {
			continue;
		}

		$output_key = array_key_exists( $output_property, $sub_field )
			? $sub_field[ $output_property ]
			: $sub_field['name'] ?? '';
		if ( '' === $output_key || ! array_key_exists( $output_key, $formatted_value ) ) {
			continue;
		}

		$raw_key       = $sub_field['key'] ?? '';
		$raw_sub_value = '' !== $raw_key && array_key_exists( $raw_key, $raw_value )
			? $raw_value[ $raw_key ]
			: null;

		$formatted_value[ $output_key ] = acf_rest_sanitize_user_data(
			$formatted_value[ $output_key ],
			$raw_sub_value,
			$sub_field
		);
	}

	return $formatted_value;
}

/**
 * Replaces formatted User field data with REST-safe values based on the field definition.
 *
 * Recurses into Group, Clone, Repeater, and Flexible Content containers so
 * nested User fields are handled the same way as top-level User fields.
 *
 * @since ACF 6.8.7
 *
 * @param mixed $formatted_value The formatted field value.
 * @param mixed $raw_value       The raw field value.
 * @param array $field           The field array.
 * @return mixed
 */
function acf_rest_sanitize_user_data( $formatted_value, $raw_value, $field ) {
	if ( empty( $field['type'] ) ) {
		return $formatted_value;
	}

	if ( 'user' === $field['type'] ) {
		if ( ! $formatted_value ) {
			return $formatted_value;
		}

		if ( ! empty( $field['multiple'] ) && is_array( $formatted_value ) ) {
			$user_ids = array();
			foreach ( $formatted_value as $user ) {
				$user_ids[] = acf_idval( $user );
			}

			return $user_ids;
		}

		return acf_idval( $formatted_value );
	}

	if ( ! is_array( $formatted_value ) ) {
		return $formatted_value;
	}

	$is_clone = 'clone' === $field['type'];
	$is_group = 'group' === $field['type'];

	if ( $is_group || $is_clone ) {
		$output_property = $is_clone ? '__name' : '_name';

		return acf_rest_sanitize_user_sub_fields(
			$formatted_value,
			$raw_value,
			$field['sub_fields'] ?? array(),
			$output_property
		);
	}

	if ( 'repeater' === $field['type'] ) {
		$raw_value = is_array( $raw_value ) ? $raw_value : array();

		foreach ( $formatted_value as $row_index => $formatted_row ) {
			$raw_row = array_key_exists( $row_index, $raw_value ) ? $raw_value[ $row_index ] : array();

			$formatted_value[ $row_index ] = acf_rest_sanitize_user_sub_fields(
				$formatted_row,
				$raw_row,
				$field['sub_fields'] ?? array(),
				'_name'
			);
		}

		return $formatted_value;
	}

	if ( 'flexible_content' === $field['type'] ) {
		$raw_value = is_array( $raw_value ) ? $raw_value : array();

		foreach ( $formatted_value as $row_index => $formatted_row ) {
			if ( ! is_array( $formatted_row ) ) {
				continue;
			}

			$raw_row     = array_key_exists( $row_index, $raw_value ) && is_array( $raw_value[ $row_index ] )
				? $raw_value[ $row_index ]
				: array();
			$layout_name = $formatted_row['acf_fc_layout'] ?? $raw_row['acf_fc_layout'] ?? '';

			foreach ( $field['layouts'] ?? array() as $layout ) {
				if ( ! isset( $layout['name'] ) || $layout_name !== $layout['name'] ) {
					continue;
				}

				$formatted_value[ $row_index ] = acf_rest_sanitize_user_sub_fields(
					$formatted_row,
					$raw_row,
					$layout['sub_fields'] ?? array(),
					'_name'
				);
				break;
			}
		}
	}

	return $formatted_value;
}

/**
 * Format a given field's value for output in the REST API.
 *
 * @param        $value
 * @param        $post_id
 * @param        $field
 * @param string  $format 'light' for normal REST API formatting or 'standard' to apply ACF's normal field formatting.
 * @return mixed
 */
function acf_format_value_for_rest( $value, $post_id, $field, $format = 'light' ) {
	if ( $format === 'standard' ) {
		$value_formatted = acf_format_value( $value, $post_id, $field );
	} else {
		$type            = acf_get_field_type( $field['type'] );
		$value_formatted = $type->format_value_for_rest( $value, $post_id, $field );
	}

	/**
	 * Filter the formatted value for a given field.
	 *
	 * @param mixed      $value_formatted The formatted value.
	 * @param string|int $post_id The post ID of the current object.
	 * @param array      $field The field array.
	 * @param mixed      $value The raw/unformatted value.
	 * @param string     $format The format applied to the field value.
	 */
	return apply_filters( 'acf/rest/format_value_for_rest', $value_formatted, $post_id, $field, $value, $format );
}

acf_add_filter_variations( 'acf/rest/format_value_for_rest', array( 'type', 'name', 'key' ), 2 );

/**
 * Reduces User field REST responses to IDs for requesters without the
 * `list_users` capability. Hooked into acf/rest/format_value_for_rest so
 * the sanitizer only runs for field types that can carry user data.
 *
 * @since ACF 6.8.7
 *
 * @param mixed          $value_formatted The formatted field value.
 * @param string|integer $post_id         The post ID of the current object.
 * @param array          $field           The field array.
 * @param mixed          $value           The raw/unformatted value.
 * @param string         $format          The format applied to the field value.
 * @return mixed
 */
function acf_rest_apply_user_data_sanitizer( $value_formatted, $post_id, $field, $value, $format ) {
	if ( 'standard' !== $format || empty( $field['type'] ) ) {
		return $value_formatted;
	}

	if ( ! in_array( $field['type'], array( 'user', 'group', 'clone', 'repeater', 'flexible_content' ), true ) ) {
		return $value_formatted;
	}

	// Preserve existing behavior for requesters authorized to list users.
	if ( current_user_can( 'list_users' ) ) {
		return $value_formatted;
	}

	return acf_rest_sanitize_user_data( $value_formatted, $value, $field );
}
add_filter( 'acf/rest/format_value_for_rest', 'acf_rest_apply_user_data_sanitizer', 10, 5 );

/**
 * Field types the reference sanitiser reduces at the leaf level. Each of these
 * expands a stored post/attachment ID into a full resource in
 * `?acf_format=standard` mode, so the leaf reducer must gate them on the
 * caller's read permission for the referenced resource.
 *
 * @since ACF 6.8.10
 *
 * @return string[]
 */
function acf_rest_reducible_reference_leaf_types() {
	return array( 'relationship', 'post_object', 'image', 'gallery', 'file', 'icon_picker' );
}

/**
 * Walks the subfields of a container field (Group, Clone, Repeater layout row,
 * or Flexible Content layout row), delegating each subfield's value to
 * {@see acf_rest_sanitize_reference_data()} so that any nested reference
 * field data pointing at resources the current user cannot read is reduced to
 * its light-format shape.
 *
 * @since ACF 6.8.10
 *
 * @param mixed  $formatted_value The formatted parent value.
 * @param mixed  $raw_value       The raw parent value.
 * @param array  $sub_fields      The parent field's subfields.
 * @param string $output_property The subfield property used as the output key.
 * @return mixed
 */
function acf_rest_sanitize_reference_sub_fields( $formatted_value, $raw_value, $sub_fields, $output_property ) {
	if ( ! is_array( $formatted_value ) || ! is_array( $sub_fields ) ) {
		return $formatted_value;
	}

	$raw_value = is_array( $raw_value ) ? $raw_value : array();

	foreach ( $sub_fields as $sub_field ) {
		if ( ! is_array( $sub_field ) ) {
			continue;
		}

		$output_key = array_key_exists( $output_property, $sub_field )
			? $sub_field[ $output_property ]
			: $sub_field['name'] ?? '';
		if ( '' === $output_key || ! array_key_exists( $output_key, $formatted_value ) ) {
			continue;
		}

		$raw_key       = $sub_field['key'] ?? '';
		$raw_sub_value = '' !== $raw_key && array_key_exists( $raw_key, $raw_value )
			? $raw_value[ $raw_key ]
			: null;

		$formatted_value[ $output_key ] = acf_rest_sanitize_reference_data(
			$formatted_value[ $output_key ],
			$raw_sub_value,
			$sub_field
		);
	}

	return $formatted_value;
}

/**
 * Reduces reference field data (Relationship, Post Object, Image, Gallery,
 * File) to light-format IDs when the current user cannot read one or more of
 * the referenced resources.
 *
 * Recurses into Group, Clone, Repeater, and Flexible Content containers so
 * nested reference fields are handled the same way as top-level ones.
 *
 * @since ACF 6.8.10
 *
 * @param mixed $formatted_value The formatted field value.
 * @param mixed $raw_value       The raw field value.
 * @param array $field           The field array.
 * @return mixed
 */
function acf_rest_sanitize_reference_data( $formatted_value, $raw_value, $field ) {
	if ( empty( $field['type'] ) ) {
		return $formatted_value;
	}

	if ( 'icon_picker' === $field['type'] ) {
		return acf_rest_reduce_icon_picker_reference( $formatted_value, $raw_value, $field );
	}

	if ( in_array( $field['type'], acf_rest_reducible_reference_leaf_types(), true ) ) {
		return acf_rest_reduce_reference( $formatted_value, $raw_value, $field );
	}

	if ( ! is_array( $formatted_value ) ) {
		return $formatted_value;
	}

	$is_clone = 'clone' === $field['type'];
	$is_group = 'group' === $field['type'];

	if ( $is_group || $is_clone ) {
		$output_property = $is_clone ? '__name' : '_name';

		return acf_rest_sanitize_reference_sub_fields(
			$formatted_value,
			$raw_value,
			$field['sub_fields'] ?? array(),
			$output_property
		);
	}

	if ( 'repeater' === $field['type'] ) {
		$raw_value = is_array( $raw_value ) ? $raw_value : array();

		foreach ( $formatted_value as $row_index => $formatted_row ) {
			$raw_row = array_key_exists( $row_index, $raw_value ) ? $raw_value[ $row_index ] : array();

			$formatted_value[ $row_index ] = acf_rest_sanitize_reference_sub_fields(
				$formatted_row,
				$raw_row,
				$field['sub_fields'] ?? array(),
				'_name'
			);
		}

		return $formatted_value;
	}

	if ( 'flexible_content' === $field['type'] ) {
		$raw_value = is_array( $raw_value ) ? $raw_value : array();

		foreach ( $formatted_value as $row_index => $formatted_row ) {
			if ( ! is_array( $formatted_row ) ) {
				continue;
			}

			$raw_row     = array_key_exists( $row_index, $raw_value ) && is_array( $raw_value[ $row_index ] )
				? $raw_value[ $row_index ]
				: array();
			$layout_name = $formatted_row['acf_fc_layout'] ?? $raw_row['acf_fc_layout'] ?? '';

			foreach ( $field['layouts'] ?? array() as $layout ) {
				if ( ! isset( $layout['name'] ) || $layout_name !== $layout['name'] ) {
					continue;
				}

				$formatted_value[ $row_index ] = acf_rest_sanitize_reference_sub_fields(
					$formatted_row,
					$raw_row,
					$layout['sub_fields'] ?? array(),
					'_name'
				);
				break;
			}
		}
	}

	return $formatted_value;
}

/**
 * All-or-nothing reduction for reference field values.
 *
 * If every referenced target passes {@see acf_rest_reference_is_exposable()},
 * the fully-expanded formatted value is returned unchanged. As soon as any one
 * target fails the check, the whole field value is reduced to its light-format
 * shape (integer ID for single-value fields, integer ID array for multi-value
 * fields). This matches the ACF-1639 precedent for User fields and gives
 * consumers a uniform response shape: they see either the full expansion or
 * the compact ID shape they would have got from `?acf_format=light`, not a
 * mixed array.
 *
 * IDs are taken from the stored raw value (which is always the resource ID or
 * IDs for these field types), with a defensive fallback that extracts IDs from
 * the formatted value if the raw value is unusable.
 *
 * @since ACF 6.8.10
 *
 * @param mixed $formatted_value The formatted field value.
 * @param mixed $raw_value       The raw field value (stored ID or ID array).
 * @param array $field           The field array.
 * @return mixed
 */
function acf_rest_reduce_reference( $formatted_value, $raw_value, $field ) {
	if ( empty( $formatted_value ) ) {
		return $formatted_value;
	}

	// Prefer raw_value — for reducible reference types it is always the
	// stored resource ID (scalar) or IDs (array).
	if ( is_array( $raw_value ) ) {
		$item_ids = array_values( array_filter( array_map( 'intval', $raw_value ) ) );
		$is_multi = true;
	} elseif ( is_numeric( $raw_value ) && (int) $raw_value > 0 ) {
		$item_ids = array( (int) $raw_value );
		$is_multi = false;
	} else {
		// Defensive fallback — extract IDs from the formatted value when the
		// raw value is missing or in an unexpected shape.
		$is_multi = is_array( $formatted_value ) && ( empty( $formatted_value ) || acf_is_sequential_array( $formatted_value ) );
		$iterable = $is_multi ? $formatted_value : array( $formatted_value );
		$item_ids = array();
		foreach ( (array) $iterable as $item ) {
			if ( $item instanceof WP_Post ) {
				$item_ids[] = (int) $item->ID;
			} elseif ( is_array( $item ) && isset( $item['ID'] ) ) {
				$item_ids[] = (int) $item['ID'];
			} elseif ( is_numeric( $item ) ) {
				$item_ids[] = (int) $item;
			}
		}
		$item_ids = array_values( array_filter( $item_ids ) );
	}

	if ( empty( $item_ids ) ) {
		return $formatted_value;
	}

	// All-or-nothing: any single unauthorised target collapses the whole field.
	foreach ( $item_ids as $id ) {
		if ( ! acf_rest_reference_is_exposable( $id, $field ) ) {
			return $is_multi ? $item_ids : (int) $item_ids[0];
		}
	}

	return $formatted_value;
}

/**
 * All-or-nothing reduction for Icon Picker fields.
 *
 * Icon Picker stores a compound value `{ type: string, value: mixed }` and
 * only its `media_library` variant expands a stored attachment ID into a full
 * attachment array in standard-format mode. The other variants (`dashicons`,
 * `url`, `svg`) carry user-provided data with no permission gate needed.
 *
 * When the type is `media_library` and the caller cannot read the target
 * attachment, the field is reduced to the same shape
 * `?acf_format=light` would return: `{ type: 'media_library', value: <int> }`.
 * Everything else passes through unchanged.
 *
 * @since ACF 6.8.10
 *
 * @param mixed $formatted_value The formatted field value.
 * @param mixed $raw_value       The raw field value.
 * @param array $field           The field array.
 * @return mixed
 */
function acf_rest_reduce_icon_picker_reference( $formatted_value, $raw_value, $field ) {
	if ( ! is_array( $formatted_value ) ) {
		return $formatted_value;
	}

	$type = $formatted_value['type'] ?? ( is_array( $raw_value ) ? $raw_value['type'] ?? null : null );
	if ( 'media_library' !== $type ) {
		return $formatted_value;
	}

	$target_id = 0;
	if ( is_array( $raw_value ) && isset( $raw_value['value'] ) && is_numeric( $raw_value['value'] ) ) {
		$target_id = (int) $raw_value['value'];
	} elseif ( isset( $formatted_value['value'] ) ) {
		$expanded = $formatted_value['value'];
		if ( is_array( $expanded ) && isset( $expanded['ID'] ) ) {
			$target_id = (int) $expanded['ID'];
		} elseif ( is_numeric( $expanded ) ) {
			$target_id = (int) $expanded;
		}
	}

	if ( $target_id <= 0 ) {
		return $formatted_value;
	}

	if ( acf_rest_reference_is_exposable( $target_id, $field ) ) {
		return $formatted_value;
	}

	return array(
		'type'  => 'media_library',
		'value' => $target_id,
	);
}

/**
 * Determines whether the current user should see the fully-expanded target of
 * a reference field (Relationship, Post Object, Image, Gallery, File).
 *
 * Delegates to the REST controller core registered for the target post type
 * (via `WP_Post_Type::get_rest_controller()`; usually `WP_REST_Posts_Controller`,
 * or `WP_REST_Attachments_Controller` for attachments, or a custom class declared
 * via `rest_controller_class`) so this stays in lockstep with what
 * `/wp/v2/<post_type>/<id>` returns for the same caller. Targets whose post type
 * is not REST-exposed (`show_in_rest === false`) are treated as non-exposable,
 * matching core.
 *
 * Fail-closed contract: if core has no REST controller for the post type (for
 * example, a `rest_controller_class` that doesn't extend `WP_REST_Controller`),
 * or the controller does not implement `check_read_permission()`, the target is
 * treated as non-exposable. A
 * bare `current_user_can( 'read_post', $id )` fallback is intentionally NOT
 * used because it evaluates to false for anonymous callers on published posts
 * (anon lacks the `read` cap on standard installs), so the fallback would
 * silently reduce publicly readable posts. Sites running a non-standard REST
 * controller for a post type can re-expose targets via the
 * `acf/rest/expose_reference_target` filter after making an informed decision.
 *
 * Password-protected targets whose password has not been entered are only
 * exposable to users who can edit them, even though the REST controller's read
 * check passes for published protected posts.
 *
 * @since ACF 6.8.10
 * @since ACF 6.8.11 Password-protected targets are only exposed to users who entered the password or can edit them.
 *
 * @param integer $target_id The referenced resource ID (post or attachment).
 * @param array   $field     The field array (context for filter subscribers).
 * @return boolean
 */
function acf_rest_reference_is_exposable( $target_id, $field ) {
	$exposable = false;
	$post      = $target_id > 0 ? get_post( $target_id ) : null;

	if ( $post instanceof WP_Post ) {
		$post_type_obj = get_post_type_object( $post->post_type );

		if ( $post_type_obj && ! empty( $post_type_obj->show_in_rest ) ) {
			$controller = $post_type_obj->get_rest_controller();

			if ( $controller && method_exists( $controller, 'check_read_permission' ) ) {
				$exposable = (bool) $controller->check_read_permission( $post );
			}

			// Core's read check passes for published password-protected posts, so hide them from anyone who can't edit them.
			if ( $exposable && post_password_required( $post ) && ! current_user_can( 'edit_post', $post->ID ) ) {
				$exposable = false;
			}
		}
	}

	/**
	 * Filters whether an expanded reference target should be exposed to the
	 * current caller when `?acf_format=standard` is in play. Return true to
	 * re-expose a target the default cap check would reduce.
	 *
	 * @since ACF 6.8.10
	 *
	 * @param boolean $exposable Whether the target's full expansion should be exposed.
	 * @param integer $target_id The referenced resource ID.
	 * @param array   $field     The ACF field array.
	 */
	return (bool) apply_filters( 'acf/rest/expose_reference_target', $exposable, $target_id, $field );
}

/**
 * Reduces reference-field REST responses (Relationship, Post Object, Image,
 * Gallery, File) to light-format IDs when the current caller cannot read one
 * or more of the referenced resources. Hooked into
 * `acf/rest/format_value_for_rest` so the sanitizer only runs for field types
 * that can carry reference data (either directly or via containers).
 *
 * @since ACF 6.8.10
 *
 * @param mixed          $value_formatted The formatted field value.
 * @param string|integer $post_id         The post ID of the current object.
 * @param array          $field           The field array.
 * @param mixed          $value           The raw/unformatted value.
 * @param string         $format          The format applied to the field value.
 * @return mixed
 */
function acf_rest_apply_reference_sanitizer( $value_formatted, $post_id, $field, $value, $format ) {
	unset( $post_id );

	if ( 'standard' !== $format || empty( $field['type'] ) ) {
		return $value_formatted;
	}

	$reducible_types = array_merge(
		acf_rest_reducible_reference_leaf_types(),
		array( 'group', 'clone', 'repeater', 'flexible_content' )
	);

	if ( ! in_array( $field['type'], $reducible_types, true ) ) {
		return $value_formatted;
	}

	return acf_rest_sanitize_reference_data( $value_formatted, $value, $field );
}
add_filter( 'acf/rest/format_value_for_rest', 'acf_rest_apply_reference_sanitizer', 10, 5 );
