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

namespace ACF\AI\Abilities;

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF Field Group Abilities
 *
 * Handles ACF field group related abilities for the WordPress Abilities API.
 */
class FieldGroup extends AbstractAbilityGroup {

	/**
	 * Register field group related abilities.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! $this->is_abilities_api_available() ) {
			return;
		}

		// Register ACF field groups ability.
		$this->register_ability(
			'acf/field-groups',
			array(
				'label'               => __( 'List ACF Field Groups', 'acf' ),
				'description'         => __( 'Get all ACF field groups that allow AI access.', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type'                 => array( 'object', 'null' ),
					'properties'           => array(),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'field_groups' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'object',
							),
						),
						'count'        => array(
							'type' => 'integer',
						),
						'message'      => array(
							'type' => 'string',
						),
					),
				),
				'execute_callback'    => array( $this, 'get_field_groups' ),
				'permission_callback' => function () {
					return current_user_can( acf_get_setting( 'capability' ) );
				},
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
					),
					'show_in_rest' => true,
				),
			)
		);

		// Register field group ability.
		$this->register_ability(
			'acf/register-field-group',
			array(
				'label'               => __( 'Register ACF Field Group', 'acf' ),
				'description'         => __( 'Register a new ACF field group schema with field definitions. This creates the field structure that will appear on content, not the field values themselves. Field values are set when creating or updating posts, terms, or other content.', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'title'                 => array(
							'type'        => 'string',
							'description' => 'The title of the field group',
							'minLength'   => 1,
						),
						'fields'                => $this->get_fields_schema(),
						'location'              => $this->get_location_schema(),
						'description'           => array(
							'type'        => 'string',
							'description' => 'A description for this field group',
						),
						'position'              => array(
							'type'        => 'string',
							'description' => 'Where to show the field group (normal, side, acf_after_title)',
							'enum'        => array( 'normal', 'side', 'acf_after_title' ),
							'default'     => 'normal',
						),
						'style'                 => array(
							'type'        => 'string',
							'description' => 'Field group style (default, seamless)',
							'enum'        => array( 'default', 'seamless' ),
							'default'     => 'default',
						),
						'label_placement'       => array(
							'type'        => 'string',
							'description' => 'Where to place field labels (top, left)',
							'enum'        => array( 'top', 'left' ),
							'default'     => 'top',
						),
						'instruction_placement' => array(
							'type'        => 'string',
							'description' => 'Where to show field instructions (label, field)',
							'enum'        => array( 'label', 'field' ),
							'default'     => 'label',
						),
						'hide_on_screen'        => array(
							'type'        => 'array',
							'description' => 'Items that should be hidden from the edit screen containing this field group',
							'items'       => array(
								'type' => 'string',
								'enum' => array(
									'permalink',
									'the_content',
									'excerpt',
									'custom_fields',
									'discussion',
									'comments',
									'revisions',
									'slug',
									'author',
									'format',
									'page_attributes',
									'featured_image',
									'categories',
									'tags',
									'send-trackbacks',
								),
							),
						),
						'active'                => array(
							'type'        => 'boolean',
							'description' => 'Whether the field group is active',
							'default'     => true,
						),
						'show_in_rest'          => array(
							'type'        => 'boolean',
							'description' => 'Whether the field group is shown in the REST API',
							'default'     => true,
						),
						'allow_ai_access'       => array(
							'type'        => 'boolean',
							'description' => 'Whether the field group allows access to AI',
							'default'     => true,
						),
						'ai_description'        => array(
							'type'        => 'string',
							'description' => 'A short description of the field group to provide AI more context',
						),
					),
					'required'             => array( 'title', 'fields', 'location' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'        => array(
							'type' => 'boolean',
						),
						'field_group'    => array(
							'type'       => 'object',
							'properties' => array(
								'ID'                    => array( 'type' => 'integer' ),
								'key'                   => array( 'type' => 'string' ),
								'title'                 => array( 'type' => 'string' ),
								'fields'                => array( 'type' => 'array' ),
								'location'              => array( 'type' => 'array' ),
								'position'              => array( 'type' => 'string' ),
								'style'                 => array( 'type' => 'string' ),
								'label_placement'       => array( 'type' => 'string' ),
								'instruction_placement' => array( 'type' => 'string' ),
								'active'                => array( 'type' => 'boolean' ),
								'description'           => array( 'type' => 'string' ),
								'show_in_rest'          => array( 'type' => 'boolean' ),
								'allow_ai_access'       => array( 'type' => 'boolean' ),
								'ai_description'        => array( 'type' => 'string' ),
							),
						),
						'field_group_id' => array(
							'type'        => 'integer',
							'description' => 'The ID of the created field group',
						),
						'message'        => array(
							'type' => 'string',
						),
					),
				),
				'execute_callback'    => array( $this, 'create_field_group' ),
				'permission_callback' => function () {
					return current_user_can( acf_get_setting( 'capability' ) );
				},
				'meta'                => array(
					'annotations'  => array(
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Get the field schema that includes all registered field types.
	 *
	 * Returns a JSON Schema with oneOf containing schemas for all ACF field types,
	 * allowing the AI to see available properties for each field type.
	 *
	 * @since 6.8.0
	 *
	 * @return array
	 */
	private function get_fields_schema(): array {
		$field_types = acf_get_field_types();
		$schemas     = array();

		foreach ( $field_types as $field_type ) {
			// Get the schema for this field type.
			$schema = array();
			if ( method_exists( $field_type, 'get_field_creation_schema' ) ) {
				$schema = $field_type->get_field_creation_schema();
			}

			// Skip if the schema is empty.
			if ( empty( $schema ) ) {
				continue;
			}

			$schemas[] = $schema;
		}

		return array(
			'type'        => 'array',
			'description' => 'Array of fields to add to the field group',
			'minItems'    => 1,
			'items'       => array(
				'oneOf' => $schemas,
			),
		);
	}

	/**
	 * Returns the schema needed to create field group location rules.
	 *
	 * @since 6.8.0
	 *
	 * @return array
	 */
	private function get_location_schema(): array {
		// Get all location types organized by category
		$location_types = acf_get_location_rule_types();

		// Build oneOf schemas for each location type
		$location_rule_schemas = array();

		foreach ( $location_types as $types ) {
			foreach ( $types as $param => $label ) {
				// Create a sample rule to get operators and values
				$sample_rule = array( 'param' => $param );

				// Get operators for this param
				$operators = acf_get_location_rule_operators( $sample_rule );

				// Build schema for this specific location type
				$location_rule_schemas[] = array(
					'type'       => 'object',
					'properties' => array(
						'param'    => array(
							'type'        => 'string',
							'enum'        => array( $param ),
							'description' => $label,
						),
						'operator' => array(
							'type'        => 'string',
							'enum'        => array_keys( $operators ),
							'description' => 'Comparison operator',
							'default'     => '==',
						),
						'value'    => array(
							'type'        => 'string',
							'description' => sprintf( 'Value for %s', $label ),
						),
					),
					'required'   => array( 'param', 'operator', 'value' ),
				);
			}
		}

		// Return full location schema supporting multiple groups and rules
		return array(
			'type'        => 'array',
			'description' => 'Location rules determining where this field group appears. Each array item is an OR group containing AND rules.',
			'minItems'    => 1,
			'items'       => array(
				'type'        => 'array',
				'description' => 'Group of location rules (AND logic)',
				'minItems'    => 1,
				'items'       => array(
					'oneOf' => $location_rule_schemas,
				),
			),
		);
	}

	/**
	 * Callback for the "acf/get-field-groups" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input Ability input (unused).
	 * @return array
	 */
	public function get_field_groups( $input = array() ) {
		unset( $input ); // Not used, but required by interface.

		$field_groups = $this->get_ai_accessible_field_groups();
		$count        = count( $field_groups );

		return array(
			'field_groups' => $field_groups,
			'count'        => $count,
			'message'      => sprintf(
				/* translators: %d: Number of found field groups */
				_n( 'Found %d ACF field group.', 'Found %d ACF field groups.', $count, 'acf' ),
				$count
			),
		);
	}

	/**
	 * A helper function to get the field groups that allow AI access.
	 *
	 * @since 6.8.0
	 *
	 * @return array
	 */
	public function get_ai_accessible_field_groups(): array {
		$field_groups  = acf_get_field_groups();
		$ai_accessible = array();

		foreach ( $field_groups as $field_group ) {
			if ( $this->is_field_group_ai_accessible( $field_group ) ) {
				$ai_accessible[] = $field_group;
			}
		}

		return $ai_accessible;
	}

	/**
	 * Check if a field group allows AI access.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field_group Field group array.
	 * @return boolean
	 */
	private function is_field_group_ai_accessible( $field_group ): bool {
		return ! empty( $field_group['allow_ai_access'] );
	}

	/**
	 * Callback for the "acf/register-field-group" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input Ability arguments containing title and fields.
	 * @return array|WP_Error
	 */
	public function create_field_group( $input = array() ) {
		// Prepare the field group data.
		$field_group_data = array(
			'key'                   => 'group_' . uniqid(),
			'title'                 => sanitize_text_field( $input['title'] ),
			'fields'                => $input['fields'],
			'location'              => $this->sanitize_location_rules( $input['location'] ),
			'description'           => isset( $input['description'] ) ? sanitize_textarea_field( $input['description'] ) : '',
			'position'              => $input['position'] ?? 'normal',
			'style'                 => $input['style'] ?? 'default',
			'label_placement'       => $input['label_placement'] ?? 'top',
			'instruction_placement' => $input['instruction_placement'] ?? 'label',
			'hide_on_screen'        => ! empty( $input['hide_on_screen'] ) ? $input['hide_on_screen'] : array(),
			'active'                => ! isset( $input['active'] ) || $input['active'],
			'show_in_rest'          => ! isset( $input['show_in_rest'] ) || $input['show_in_rest'],
			'allow_ai_access'       => ! isset( $input['allow_ai_access'] ) || $input['allow_ai_access'],
			'ai_description'        => isset( $input['ai_description'] ) ? sanitize_text_field( $input['ai_description'] ) : '',
		);

		// Create the field group using ACF's function.
		add_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_ability_import' ), 5 );
		$field_group = acf_import_field_group( $field_group_data );
		remove_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_ability_import' ) );

		if ( empty( $field_group['ID'] ) || ! is_int( $field_group['ID'] ) ) {
			return new WP_Error(
				'field_group_creation_failed',
				__( 'Failed to create the field group', 'acf' ),
				array( 'field_group_data' => $field_group )
			);
		}

		return array(
			'success'        => true,
			'field_group'    => $field_group,
			'field_group_id' => $field_group['ID'],
			'message'        => sprintf(
				/* translators: %s: Field group title */
				__( 'Field group "%s" created successfully.', 'acf' ),
				$field_group['title']
			),
		);
	}

	/**
	 * Ensures a field has a key and name before import and sanitizes user input.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field The field being prepared for import.
	 * @return array The field with key, name, and sanitized values.
	 */
	public function prepare_field_for_ability_import( $field ) {
		// Generate field name if not provided.
		if ( empty( $field['name'] ) && ! empty( $field['label'] ) ) {
			$field['name'] = acf_slugify( $field['label'], '_' );
		}

		// Generate field key if not provided.
		if ( empty( $field['key'] ) ) {
			$field['key'] = 'field_' . uniqid();
		}

		if ( ! empty( $field['label'] ) ) {
			$field['label'] = sanitize_text_field( $field['label'] );
		}

		if ( ! empty( $field['instructions'] ) ) {
			$field['instructions'] = wp_kses_post( $field['instructions'] );
		}

		if ( ! empty( $field['placeholder'] ) ) {
			$field['placeholder'] = sanitize_text_field( $field['placeholder'] );
		}

		if ( ! empty( $field['prepend'] ) ) {
			$field['prepend'] = sanitize_text_field( $field['prepend'] );
		}

		if ( ! empty( $field['append'] ) ) {
			$field['append'] = sanitize_text_field( $field['append'] );
		}

		if ( ! empty( $field['wrapper']['class'] ) ) {
			$field['wrapper']['class'] = sanitize_text_field( $field['wrapper']['class'] );
		}

		if ( ! empty( $field['wrapper']['id'] ) ) {
			$field['wrapper']['id'] = sanitize_key( $field['wrapper']['id'] );
		}

		if ( ! empty( $field['choices'] ) && is_array( $field['choices'] ) ) {
			$field['choices'] = array_map( 'sanitize_text_field', $field['choices'] );
		}

		if ( isset( $field['min'] ) ) {
			$field['min'] = absint( $field['min'] );
		}

		if ( isset( $field['max'] ) ) {
			$field['max'] = absint( $field['max'] );
		}

		if ( isset( $field['default_value'] ) ) {
			if ( is_string( $field['default_value'] ) ) {
				$field['default_value'] = sanitize_text_field( $field['default_value'] );
			} elseif ( is_array( $field['default_value'] ) ) {
				$field['default_value'] = array_map( 'sanitize_text_field', $field['default_value'] );
			}
		}

		if ( ! empty( $field['message'] ) ) {
			$field['message'] = wp_kses_post( $field['message'] );
		}

		if ( ! empty( $field['conditional_logic'] ) && is_array( $field['conditional_logic'] ) ) {
			$field['conditional_logic'] = $this->sanitize_conditional_logic( $field['conditional_logic'] );
		}

		return $field;
	}

	/**
	 * Sanitize location rules for a field group.
	 *
	 * @since 6.8.0
	 *
	 * @param array $location The location rules array.
	 * @return array The sanitized location rules.
	 */
	private function sanitize_location_rules( array $location ): array {
		foreach ( $location as &$group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}

			foreach ( $group as &$rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				if ( isset( $rule['param'] ) ) {
					$rule['param'] = sanitize_text_field( $rule['param'] );
				}

				if ( isset( $rule['operator'] ) ) {
					$rule['operator'] = sanitize_text_field( $rule['operator'] );
				}

				if ( isset( $rule['value'] ) ) {
					$rule['value'] = sanitize_text_field( $rule['value'] );
				}
			}
		}

		return $location;
	}

	/**
	 * Sanitize conditional logic rules for a field.
	 *
	 * @since 6.8.0
	 *
	 * @param array $conditional_logic The conditional logic array.
	 * @return array The sanitized conditional logic.
	 */
	private function sanitize_conditional_logic( array $conditional_logic ): array {
		foreach ( $conditional_logic as &$group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}

			foreach ( $group as &$rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				if ( isset( $rule['field'] ) ) {
					$rule['field'] = sanitize_text_field( $rule['field'] );
				}

				if ( isset( $rule['operator'] ) ) {
					$rule['operator'] = sanitize_text_field( $rule['operator'] );
				}

				if ( isset( $rule['value'] ) ) {
					$rule['value'] = sanitize_text_field( $rule['value'] );
				}
			}
		}

		return $conditional_logic;
	}
}
