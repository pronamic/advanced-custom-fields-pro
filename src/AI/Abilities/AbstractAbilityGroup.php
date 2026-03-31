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

		return wp_register_ability( $id, $ability_args );
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
			if ( empty( $field_group['allow_ai_access'] ) || empty( $field_group['show_in_rest'] ) ) {
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
			'required'    => false,
			'properties'  => array(),
		);

		foreach ( $acf_fields as $field_group ) {
			foreach ( $field_group['fields'] as $field ) {
				$schema['properties']['acf']['properties'][ $field['name'] ] = $field;
			}
		}

		return $schema;
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
