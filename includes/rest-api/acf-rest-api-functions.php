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
