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

use WP_REST_Request;
use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract Ability Group
 *
 * Base class for all ability groups.
 */
abstract class AbstractAbilityGroup {

	const REST_ABILITY_CLASS = 'ACF\AI\Abilities\ACF_REST_Ability';

	/**
	 * WordPress REST API schema keywords that aren't part of JSON Schema, including JSON Hyper-Schema `links`.
	 */
	const REST_ONLY_SCHEMA_KEYWORDS = array( 'context', 'readonly', 'arg_options', 'sanitize_callback', 'validate_callback', 'links' );

	/**
	 * Schema keywords that hold a map of property names to subschemas.
	 */
	const SCHEMA_MAP_KEYWORDS = array( 'properties', 'patternProperties', 'definitions', '$defs' );

	/**
	 * Schema keywords that hold a list of subschemas.
	 */
	const SCHEMA_LIST_KEYWORDS = array( 'anyOf', 'oneOf', 'allOf' );

	/**
	 * Schema keywords that hold a single subschema.
	 */
	const SCHEMA_SINGLE_KEYWORDS = array( 'items', 'additionalProperties', 'not' );

	/**
	 * Type names used by some field REST schemas, mapped to their JSON Schema equivalents.
	 */
	const SCHEMA_TYPE_ALIASES = array(
		'int'   => 'integer',
		'float' => 'number',
	);

	/**
	 * Register abilities for this ability group
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	abstract public function register_abilities();

	/**
	 * Check if the WordPress Abilities API is available
	 *
	 * @since 6.8.0
	 *
	 * @return boolean
	 */
	protected function is_abilities_api_available() {
		return function_exists( 'wp_register_ability' );
	}

	/**
	 * Register an ability with error handling.
	 *
	 * @since 6.8.0
	 *
	 * @param string $id           Ability ID.
	 * @param array  $ability_args Ability arguments.
	 * @return object|null Registered ability object or null on failure.
	 */
	protected function register_ability( $id, $ability_args ) {
		if ( ! $this->is_abilities_api_available() ) {
			return null;
		}

		return wp_register_ability( $id, $this->prepare_ability_args( $ability_args ) );
	}

	/**
	 * Applies ACF defaults to ability arguments and normalizes their schemas.
	 *
	 * @since 6.8.12
	 *
	 * @param array $ability_args Ability arguments.
	 * @return array
	 */
	protected function prepare_ability_args( array $ability_args ): array {
		// Ensure meta array exists.
		if ( ! isset( $ability_args['meta'] ) ) {
			$ability_args['meta'] = array();
		}

		// Ensure mcp array exists.
		if ( ! isset( $ability_args['meta']['mcp'] ) ) {
			$ability_args['meta']['mcp'] = array();
		}

		// Set public to true by default for MCP exposure.
		if ( ! isset( $ability_args['meta']['mcp']['public'] ) ) {
			$ability_args['meta']['mcp']['public'] = true;
		}

		foreach ( array( 'input_schema', 'output_schema' ) as $schema_key ) {
			if ( ! empty( $ability_args[ $schema_key ] ) && is_array( $ability_args[ $schema_key ] ) ) {
				$ability_args[ $schema_key ] = $this->normalize_schema( $ability_args[ $schema_key ] );
			}
		}

		return $ability_args;
	}

	/**
	 * Converts a WordPress REST API style schema into plain JSON Schema.
	 *
	 * MCP clients validate ability schemas as JSON Schema, which only allows `required` as an
	 * array of property names on the parent object. WordPress REST schemas instead set a boolean
	 * `required` on each property and use keywords such as `context` and `readonly`. WordPress
	 * validates both forms of `required` the same way, so input validation is unchanged.
	 *
	 * @since 6.8.12
	 *
	 * @param array $schema The schema to normalize.
	 * @return array
	 */
	protected function normalize_schema( array $schema ): array {
		foreach ( self::REST_ONLY_SCHEMA_KEYWORDS as $keyword ) {
			unset( $schema[ $keyword ] );
		}

		if ( isset( $schema['type'] ) ) {
			if ( is_array( $schema['type'] ) ) {
				$schema['type'] = array_values( array_unique( array_map( array( $this, 'normalize_schema_type' ), $schema['type'] ) ) );
			} else {
				$schema['type'] = $this->normalize_schema_type( $schema['type'] );
			}
		}

		$required = isset( $schema['required'] ) && is_array( $schema['required'] ) ? $schema['required'] : array();
		unset( $schema['required'] );

		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $name => $property ) {
				if ( is_array( $property ) && isset( $property['required'] ) && ! is_array( $property['required'] ) ) {
					if ( $property['required'] ) {
						$required[] = (string) $name;
					}

					unset( $schema['properties'][ $name ]['required'] );
				}
			}

			// An empty PHP array would be encoded as a JSON list rather than an object.
			if ( empty( $schema['properties'] ) ) {
				unset( $schema['properties'] );
			}
		}

		if ( $required ) {
			$schema['required'] = array_values( array_unique( $required ) );
		}

		foreach ( self::SCHEMA_MAP_KEYWORDS as $keyword ) {
			if ( isset( $schema[ $keyword ] ) && is_array( $schema[ $keyword ] ) ) {
				foreach ( $schema[ $keyword ] as $name => $subschema ) {
					if ( is_array( $subschema ) ) {
						$schema[ $keyword ][ $name ] = $this->normalize_schema( $subschema );
					}
				}
			}
		}

		foreach ( self::SCHEMA_LIST_KEYWORDS as $keyword ) {
			if ( isset( $schema[ $keyword ] ) && is_array( $schema[ $keyword ] ) ) {
				// Boolean subschemas are valid branches, so keep them as they are.
				foreach ( $schema[ $keyword ] as $index => $subschema ) {
					if ( is_array( $subschema ) ) {
						$schema[ $keyword ][ $index ] = $this->normalize_schema( $subschema );
					}
				}

				$schema[ $keyword ] = array_values( $schema[ $keyword ] );
			}
		}

		foreach ( self::SCHEMA_SINGLE_KEYWORDS as $keyword ) {
			if ( isset( $schema[ $keyword ] ) && is_array( $schema[ $keyword ] ) ) {
				$schema[ $keyword ] = $this->normalize_schema( $schema[ $keyword ] );
			}
		}

		return $schema;
	}

	/**
	 * Maps a type name such as `int` to its JSON Schema equivalent.
	 *
	 * @since 6.8.12
	 *
	 * @param mixed $type The type name.
	 * @return mixed
	 */
	protected function normalize_schema_type( $type ) {
		return is_string( $type ) && isset( self::SCHEMA_TYPE_ALIASES[ $type ] ) ? self::SCHEMA_TYPE_ALIASES[ $type ] : $type;
	}

	/**
	 * Retrieves the AI-enabled ACF fields for the provided object.
	 *
	 * @since 6.8.0
	 *
	 * @param string         $object_type The object type being queried.
	 * @param integer|string $object_id   The object to get ACF fields for.
	 * @return array
	 */
	protected function get_acf_fields_for_object( $object_type, $object_id ) {
		// Get field groups that show on this object.
		$field_groups = acf_get_field_groups(
			array(
				$object_type => $object_id,
			)
		);

		$acf_fields = array();

		foreach ( $field_groups as $field_group ) {
			// Only include AI-accessible field groups that are exposed in REST.
			if ( empty( $field_group['enable_acf_abilities'] ) || empty( $field_group['show_in_rest'] ) ) {
				continue;
			}

			$fields = acf_get_fields( $field_group['key'] );

			if ( $fields ) {
				$acf_fields[ $field_group['key'] ] = array(
					'title'  => $field_group['title'],
					'key'    => $field_group['key'],
					'fields' => $this->format_acf_fields_for_schema( $fields ),
				);
			}
		}

		return $acf_fields;
	}

	/**
	 * A helper function to format ACF fields for schema output.
	 *
	 * @since 6.8.0
	 *
	 * @param array $fields The ACF fields array.
	 * @return array
	 */
	protected function format_acf_fields_for_schema( array $fields ): array {
		$formatted_fields = array();

		foreach ( $fields as $field ) {
			$field_schema = array(
				'key'        => $field['key'],
				'name'       => $field['name'],
				'label'      => $field['label'],
				'field_type' => $field['type'],
			);

			// Add description if available
			if ( ! empty( $field['instructions'] ) ) {
				$field_schema['description'] = $field['instructions'];
			}

			$field_schema = array_merge(
				$field_schema,
				acf_get_field_rest_schema( $field )
			);

			$formatted_fields[] = $field_schema;
		}

		return $formatted_fields;
	}

	/**
	 * Adds ACF fields to a schema.
	 *
	 * @since 6.8.0
	 *
	 * @param array $schema     The schema to add fields to.
	 * @param array $acf_fields The ACF fields to add.
	 * @return array
	 */
	protected function add_acf_fields_to_schema( array $schema, array $acf_fields ): array {
		if ( empty( $acf_fields ) ) {
			return $schema;
		}

		$schema['properties']['acf'] = array(
			'type'        => 'object',
			'description' => 'ACF field values',
			'properties'  => array(),
		);

		foreach ( $acf_fields as $field_group ) {
			foreach ( $field_group['fields'] as $field ) {
				$schema['properties']['acf']['properties'][ $field['name'] ] = $this->get_field_value_schema( $field );
			}
		}

		return $schema;
	}

	/**
	 * Returns the value schema for a field formatted by format_acf_fields_for_schema().
	 *
	 * The field's identity is moved into the description so that only schema keywords remain.
	 *
	 * @since 6.8.12
	 *
	 * @param array $field The formatted field.
	 * @return array
	 */
	protected function get_field_value_schema( array $field ): array {
		$description = sprintf( '%s (%s field)', $field['label'], $field['field_type'] );
		if ( ! empty( $field['description'] ) ) {
			$description .= '. ' . $field['description'];
		}

		unset( $field['key'], $field['name'], $field['label'], $field['field_type'] );
		$field['description'] = $description;

		return $field;
	}

	/**
	 * Execute a REST API request.
	 *
	 * @since 6.8.0
	 *
	 * @param string  $method    HTTP method (GET, POST, PUT, DELETE).
	 * @param string  $rest_base REST API base.
	 * @param array   $input     Input parameters.
	 * @param integer $item_id   Optional item ID for single item operations.
	 * @return array|WP_Error Response data or error.
	 */
	protected function execute_rest_request( string $method, string $rest_base, $input = array(), $item_id = null ) {
		$endpoint = '/wp/v2/' . $rest_base;

		if ( $item_id ) {
			$endpoint .= '/' . intval( $item_id );
		}

		$request = new WP_REST_Request( $method, $endpoint );

		// Set all input parameters.
		foreach ( $input as $key => $value ) {
			// Skip the ID since it's in the URL for single item operations.
			if ( $key === 'id' && $item_id ) {
				continue;
			}
			$request->set_param( $key, $value );
		}

		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			return $response->as_error();
		}

		return $response->get_data();
	}
}
