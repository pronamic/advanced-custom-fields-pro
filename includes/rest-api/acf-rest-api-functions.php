<?php

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
 * @param string|int $post_id
 * @param array      $field
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
