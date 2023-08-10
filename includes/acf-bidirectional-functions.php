<?php
/**
 * General functions relating to the bidirectional feature of some fields.
 *
 * @package ACF
 */

/**
 * Process updating bidirectional fields.
 *
 * @since 6.2
 *
 * @param array        $target_item_ids The post, user or term IDs which should be updated with the origin item ID.
 * @param int|string   $post_id The ACF encoded origin post, user or term ID.
 * @param array        $field The field being updated on the origin post, user or term ID.
 * @param string|false $target_prefix The ACF prefix for a post, user or term ID required for the update_field call for this field type.
 */
function acf_update_bidirectional_values( $target_item_ids, $post_id, $field, $target_prefix = false ) {

	// Bail early if we're already updating a bidirectional relationship to prevent recursion.
	if ( acf_get_data( 'acf_doing_bidirectional_update' ) ) {
		return;
	}

	// Support disabling bidirectionality globally.
	if ( ! acf_get_setting( 'enable_bidirection' ) ) {
		return;
	}

	if ( empty( $field['bidirectional'] ) || empty( $field['bidirectional_target'] ) ) {
		return;
	}

	$decoded            = acf_decode_post_id( $post_id );
	$item_id            = $decoded['id'];
	$valid_target_types = acf_get_valid_bidirectional_target_types( $decoded['type'] );
	$valid_targets      = array();

	foreach ( $field['bidirectional_target'] as $target_field ) {
		$target_field_object = get_field_object( $target_field );
		if ( empty( $target_field_object ) || ! is_array( $target_field_object ) ) {
			continue;
		}
		if ( in_array( $target_field_object['type'], $valid_target_types, true ) ) {
			$valid_targets[] = $target_field;
		}
	}

	if ( ! empty( $valid_targets ) ) {

		// Get current values for this field.
		$current_values = array_filter( acf_get_array( get_field( $field['key'], $post_id, false ) ) );
		$new_values     = array_filter( acf_get_array( $target_item_ids ) );

		$additions    = array_diff( $new_values, $current_values );
		$subtractions = array_diff( $current_values, $new_values );

		// Prefix additions and subtractions for destinations which aren't posts.
		if ( ! empty( $target_prefix ) ) {
			$mapper       = function( $v ) use ( $target_prefix ) {
				return $target_prefix . '_' . $v;
			};
			$additions    = array_map( $mapper, $additions );
			$subtractions = array_map( $mapper, $subtractions );
		}

		acf_set_data( 'acf_doing_bidirectional_update', true );

		// Loop over each target, processing additions and removals.
		foreach ( $valid_targets as $target_field ) {
			foreach ( $additions as $addition ) {
				$current_value = acf_get_array( get_field( $target_field, $addition, false ) );
				update_field( $target_field, array_unique( array_merge( $current_value, array( $item_id ) ) ), $addition );
			}

			foreach ( $subtractions as $subtraction ) {
				$current_value = acf_get_array( get_field( $target_field, $subtraction, false ) );
				update_field( $target_field, array_unique( array_diff( $current_value, array( $item_id ) ) ), $subtraction );
			}
		}

		acf_set_data( 'acf_doing_bidirectional_update', false );
	}
}

/**
 * Allows third party fields to enable support as a target field type for a particular object type
 *
 * @since 6.2
 *
 * @param string $object_type The object type that will be updated on the target field, such as 'term', 'user' or 'post'.
 * @return array An array of valid field type names (slugs) for the target of the bidirectional field.
 */
function acf_get_valid_bidirectional_target_types( $object_type ) {
	$valid_target_types = array();
	switch ( $object_type ) {
		case 'term':
			$valid_target_types = array( 'taxonomy' );
			break;
		case 'user':
			$valid_target_types = array( 'user' );
			break;
		case 'post':
			$valid_target_types = array( 'relationship', 'post_object' );
			break;
	}
	return apply_filters( 'acf/bidirectional/supported_field_types_for_post', $valid_target_types, $object_type );
}

/**
 * Build the complete choices argument for rendering the select2 field for bidirectional target based on the currently selected choices
 *
 * @since 6.2
 *
 * @param array $choices The currently selected choices (as an array of field keys).
 *
 * @return array
 */
function acf_build_bidirectional_target_current_choices( $choices ) {
	if ( empty( $choices ) ) {
		return array();
	}

	$results = array();
	foreach ( $choices as $choice ) {
		if ( empty( $choice ) || ! is_string( $choice ) ) {
			continue;
		}

		$field_object = get_field_object( $choice );
		if ( is_array( $field_object ) && ! empty( $field_object['label'] ) ) {
			$results[ $choice ] = $field_object['label'];
		} else {
			$results[ $choice ] = $choice;
		}
	}

	return $results;
}

/**
 * Build valid fields for a bidirectional relationship for select2 display
 *
 * @since 6.2
 *
 * @param array $results The original results array.
 * @param array $options The options provided to the select2 AJAX search.
 *
 * @return array
 */
function acf_build_bidirectional_relationship_field_target_args( $results, $options ) {
	$valid_field_types = apply_filters( 'acf/bidirectional/supported_target_field_types', array( 'relationship', 'post_object', 'user', 'taxonomy' ) );

	$field_groups = array_filter(
		acf_get_field_groups(),
		function( $field_group ) {
			return $field_group['active'];
		}
	);

	$valid_fields = array();
	foreach ( $field_groups as $field_group ) {
		$fields = acf_get_fields( $field_group );
		foreach ( $fields as $field ) {
			if ( in_array( $field['type'], $valid_field_types, true ) ) {
				if ( empty( $valid_fields[ $field_group['title'] ] ) ) {
					$valid_fields[ $field_group['title'] ] = array();
				}
				$valid_fields[ $field_group['title'] ][ $field['key'] ] = array(
					'type'  => $field['type'],
					'label' => $field['label'],
				);

				if ( isset( $options['parent_key'] ) && $options['parent_key'] === $field['key'] ) {
					$valid_fields[ $field_group['title'] ][ $field['key'] ]['this_field'] = true;
				}
			}
		}
	}

	foreach ( $valid_fields as $field_group_name => $fields ) {
		$field_group = array(
			'text'     => $field_group_name,
			'children' => array(),
		);
		foreach ( $fields as $key => $data ) {
			$field_group['children'][] = array(
				'id'               => $key,
				'text'             => $data['label'],
				'field_type'       => $data['type'],
				/* translators: %s A field type name, such as "Relationship" */
				'human_field_type' => sprintf( __( '%s Field', 'acf' ), acf_get_field_type_prop( $data['type'], 'label' ) ),
				'this_field'       => ! empty( $data['this_field'] ),
			);
		}
		$results['results'][] = $field_group;
	}

	return $results;
}

add_action( 'acf/fields/select/query/key=_acf_bidirectional_target', 'acf_build_bidirectional_relationship_field_target_args', 10, 2 );

/**
 * Renders the field settings required for bidirectional fields
 *
 * @since 6.2
 *
 * @param array $field The field object passed into field setting functions.
 */
function acf_render_bidirectional_field_settings( $field ) {
	if ( ! acf_get_setting( 'enable_bidirection' ) ) {
		return;
	}

	acf_render_field_setting(
		$field,
		array(
			'label'        => __( 'Bidirectional', 'acf' ),
			'instructions' => __( 'Update a field on the selected values, referencing back to this ID', 'acf' ),
			'type'         => 'true_false',
			'name'         => 'bidirectional',
			'ui'           => 1,
		)
	);

	acf_render_field_setting(
		$field,
		array(
			'name'       => 'bidirectional_notes',
			'type'       => 'message',
			'message'    => acf_get_bidirectional_field_settings_instruction_text(),
			'conditions' => array(
				'field'    => 'bidirectional',
				'operator' => '==',
				'value'    => 1,
			),
		)
	);

	acf_render_field_setting(
		$field,
		array(
			'type'         => 'select',
			'name'         => 'bidirectional_target',
			'label'        => __( 'Target Field', 'acf' ),
			'instructions' => __( 'Select field(s) to store the reference back to the item being updated. You may select this field. Target fields must be compatible with where this field is being displayed. For example, if this field is displayed on a Taxonomy, your target field should be of type Taxonomy', 'acf' ),
			'class'        => 'bidrectional_target',
			'choices'      => acf_build_bidirectional_target_current_choices( $field['bidirectional_target'] ),
			'conditions'   => array(
				'field'    => 'bidirectional',
				'operator' => '==',
				'value'    => 1,
			),
			'ui'           => 1,
			'multiple'     => 1,
			'ajax'         => 1,
		)
	);
}

/**
 * Returns the translated instructional text for the message field for the bidirectional field settings.
 *
 * @since 6.2
 *
 * @return string The html containing the instructional message.
 */
function acf_get_bidirectional_field_settings_instruction_text() {
	/* translators: %s the URL to ACF's bidirectional relationship documentation */
	$message = '<p class="acf-feature-notice with-warning-icon">' . sprintf( __( 'Enabling the bidirectional setting allows you to update a value in the target fields for each value selected for this field, adding or removing the Post ID, Taxonomy ID or User ID of the item being updated. For more information, please read the <a href="%s" target="_blank">documentation</a>.', 'acf' ), acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/bidirectional-relationships/', 'docs', 'bidirectional' ) ) . '</p>';
	return $message;
}
