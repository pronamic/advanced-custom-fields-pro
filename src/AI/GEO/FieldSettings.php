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

namespace ACF\AI\GEO;

// Exit if accessed directly.
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * ACF GEO Field Settings
 *
 * Adds JSON-LD field role settings to ACF fields.
 */
class FieldSettings {

	/**
	 * Cache for schema properties by field type
	 *
	 * @var array
	 */
	private static $properties_cache = array();

	/**
	 * Constructs the FieldSettings class.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the field settings
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function init() {
		// Add the Schema.org Property setting to field types that support it.
		add_action( 'acf/render_field_general_settings', array( $this, 'render_field_schema_settings' ) );

		// AJAX output format handler (needs to be added early for AJAX requests).
		add_action( 'wp_ajax_acf/schema/get_output_formats', array( $this, 'ajax_get_output_formats' ) );
	}

	/**
	 * AJAX handler to get output format choices for a field type + property combination.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function ajax_get_output_formats() {
		// Verify request.
		if ( ! acf_verify_ajax() ) {
			wp_send_json_error(
				new WP_Error(
					'acf_invalid_nonce',
					__( 'Invalid nonce.', 'acf' )
				)
			);
		}

		// Verify user can admin.
		if ( ! acf_current_user_can_admin() ) {
			wp_send_json_error(
				new WP_Error(
					'acf_invalid_permissions',
					__( 'Sorry, you do not have permission to do that.', 'acf' )
				)
			);
		}

		$field_type         = acf_request_arg( 'field_type', '' );
		$qualified_property = acf_request_arg( 'property', '' );
		$property           = Schema::get_property_name( $qualified_property );

		if ( empty( $field_type ) || empty( $property ) ) {
			wp_send_json_error(
				new WP_Error(
					'acf_invalid_param',
					__( 'Missing required parameters', 'acf' )
				)
			);
		}

		$choices       = array();
		$valid_formats = Schema::get_valid_output_formats( $field_type, $property );

		foreach ( $valid_formats as $format ) {
			$choices[] = array(
				'id'   => $format,
				'text' => $format,
			);
		}

		$default = Schema::get_default_output_format( $field_type, $property );

		wp_send_json_success(
			array(
				'choices' => $choices,
				'default' => $default,
			)
		);
	}

	/**
	 * Render the field-level schema settings.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field The field being edited.
	 * @return void
	 */
	public function render_field_schema_settings( $field ) {
		$field_type = $field['type'] ?? '';

		// Check if field type supports JSON-LD output.
		$supported_ranges = Schema::get_field_type_ranges( $field_type );
		if ( empty( $supported_ranges ) ) {
			// Field type doesn't support JSON-LD output (e.g., tab, accordion).
			return;
		}

		// Get available Schema.org properties filtered by field type compatibility.
		$parent_id = (int) ( $field['parent'] ?? 0 );

		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Schema.org Property', 'acf' ),
				'instructions' => __( 'Map this field to a Schema.org property instead of using additionalProperty.', 'acf' ),
				'type'         => 'select',
				'name'         => 'schema_property',
				'class'        => 'acf-schema-property',
				'wrapper'      => array(
					'class' => 'acf-field-meta-box',
				),
				'choices'      => $this->get_schema_properties( $field_type, $parent_id ),
				'allow_null'   => 1,
				'ui'           => 1,
				'experimental' => 1,
			)
		);

		$output_choices = $this->get_output_format_choices( $field );

		// Get the default format for this field type + property.
		$qualified_property = $field['schema_property'] ?? '';
		$property_name      = Schema::get_property_name( $qualified_property );
		$default_format     = Schema::get_default_output_format( $field_type, $property_name );

		acf_render_field_setting(
			$field,
			array(
				'label'         => __( 'Schema.org Output Format', 'acf' ),
				'type'          => 'select',
				'name'          => 'schema_output_format',
				'class'         => 'acf-schema-output-format',
				'wrapper'       => array(
					'class' => 'acf-field-meta-box',
				),
				'choices'       => $output_choices,
				'default_value' => $default_format,
				'ui'            => 1,
				'experimental'  => 1,
				'conditions'    => array(
					'field'    => 'schema_property',
					'operator' => '!=',
					'value'    => '',
				),
			)
		);
	}

	/**
	 * Get available Schema.org properties for a field type
	 *
	 * Returns a hierarchical array of Schema.org properties organized by type,
	 * filtered to only include properties compatible with the field type.
	 *
	 * Uses pre-computed compatibility data for fast lookups.
	 *
	 * @since 6.8.0
	 *
	 * @param string  $field_type The ACF field type name.
	 * @param integer $context_id Optional field group ID for context-aware priority ordering.
	 * @return array Array of properties grouped by Schema.org type.
	 */
	public function get_schema_properties( string $field_type = '', int $context_id = 0 ): array {
		// Build cache key including context.
		$cache_key = $field_type . '_' . $context_id;

		// Return cached result if available.
		if ( isset( self::$properties_cache[ $cache_key ] ) ) {
			return self::$properties_cache[ $cache_key ];
		}

		$roles = array();

		// Get compatible properties using pre-computed data.
		$compatible_set = $this->get_compatible_properties_set( $field_type );

		if ( empty( $compatible_set ) ) {
			self::$properties_cache[ $cache_key ] = $roles;
			return $roles;
		}

		// Get all properties grouped by type from schema.org vocabulary.
		$properties_by_type = Schema::get_properties_by_type();

		// Get priority types with context-aware ordering.
		$priority_types = Schema::get_priority_types( $context_id );

		// Add priority types first.
		foreach ( $priority_types as $type ) {
			if ( isset( $properties_by_type[ $type ] ) ) {
				$type_compatible = array();
				foreach ( $properties_by_type[ $type ] as $property ) {
					if ( isset( $compatible_set[ $property ] ) ) {
						$type_compatible[ $type . '.' . $property ] = $property;
					}
				}

				if ( ! empty( $type_compatible ) ) {
					$type_label           = sprintf( '%s Properties', $type );
					$roles[ $type_label ] = $type_compatible;
				}
			}
		}

		// Add remaining types alphabetically.
		foreach ( $properties_by_type as $type => $properties ) {
			// Skip priority types (already processed).
			if ( in_array( $type, $priority_types, true ) ) {
				continue;
			}

			// Skip types with no properties.
			if ( empty( $properties ) ) {
				continue;
			}

			$type_compatible = array();
			foreach ( $properties as $property ) {
				if ( isset( $compatible_set[ $property ] ) ) {
					$type_compatible[ $type . '.' . $property ] = $property;
				}
			}

			if ( ! empty( $type_compatible ) ) {
				$type_label           = sprintf( '%s Properties', $type );
				$roles[ $type_label ] = $type_compatible;
			}
		}

		/**
		 * Filter the available Schema.org properties.
		 *
		 * Allows developers to add custom Schema.org properties or modify existing ones.
		 *
		 * @param array  $properties The Schema.org role mappings grouped by type.
		 * @param string $field_type The ACF field type being configured.
		 */
		$roles = apply_filters( 'acf/schema/schema_properties', $roles, $field_type );

		// Cache the result.
		self::$properties_cache[ $cache_key ] = $roles;

		return $roles;
	}

	/**
	 * Get compatible properties as a set (for fast lookup)
	 *
	 * Uses pre-computed data from SchemaData::get_compatible_properties().
	 *
	 * @since 6.8.0
	 *
	 * @param string $field_type The ACF field type name.
	 * @return array Properties as keys for O(1) lookup.
	 */
	private function get_compatible_properties_set( $field_type ) {
		// Get field type's output types.
		$field_ranges = Schema::get_field_type_ranges( $field_type );

		if ( empty( $field_ranges ) ) {
			return array();
		}

		// Get pre-computed compatible properties mapping.
		$compatible_by_type = SchemaData::get_compatible_properties();

		// Merge compatible properties for all output types.
		$compatible = array();
		foreach ( $field_ranges as $output_type ) {
			if ( isset( $compatible_by_type[ $output_type ] ) ) {
				foreach ( $compatible_by_type[ $output_type ] as $property ) {
					$compatible[ $property ] = true;
				}
			}
		}

		return $compatible;
	}

	/**
	 * Get output format choices for a field
	 *
	 * Returns the valid output formats for the field's type and selected property.
	 * For example, an Image field mapped to the 'image' property can output
	 * either 'URL' or 'ImageObject'.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field The field being edited.
	 * @return array Array of format => label pairs.
	 */
	private function get_output_format_choices( $field ) {
		$field_type         = $field['type'] ?? '';
		$qualified_property = $field['schema_property'] ?? '';

		// If no property selected yet, return empty choices.
		if ( empty( $qualified_property ) ) {
			return array();
		}

		// Extract just the property name from qualified property (e.g., "Recipe.recipeYield" -> "recipeYield").
		$property      = Schema::get_property_name( $qualified_property );
		$valid_formats = Schema::get_valid_output_formats( $field_type, $property );

		if ( empty( $valid_formats ) ) {
			return array();
		}

		// Build choices array with format as both key and label.
		$choices = array();
		foreach ( $valid_formats as $format ) {
			$choices[ $format ] = $format;
		}

		/**
		 * Filter the available output format choices.
		 *
		 * @param array  $choices    The output format choices.
		 * @param string $field_type The ACF field type.
		 * @param string $property   The selected Schema.org property.
		 */
		return apply_filters( 'acf/schema/output_format_choices', $choices, $field_type, $property );
	}
}
