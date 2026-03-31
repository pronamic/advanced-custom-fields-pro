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
defined( 'ABSPATH' ) || exit;

/**
 * ACF GEO Extension
 *
 * Extends ACF admin interface to add AI-related settings and functionality.
 */
class GEO {

	/**
	 * Constructs the GEO class.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the GEO extension,
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function init() {
		// Add hooks for ACF admin interface extensions for post types.
		add_filter( 'acf/post_type/additional_settings_tabs', array( $this, 'add_schema_tab' ) );
		add_action( 'acf/post_type/render_settings_tab/schema', array( $this, 'render_post_type_schema_tab' ) );

		// Initialize GEO submodules.
		// Field Settings.
		new FieldSettings();

		// JSON-LD Outputs.
		new Outputs\Posts();
		// Note: Blocks output is initialized separately in PRO (see acf-pro.php).
	}

	/**
	 * Adds the "Schema" settings tab for post types.
	 *
	 * @since 6.8.0
	 *
	 * @param array $tabs An array of the existing tabs.
	 * @return array
	 */
	public function add_schema_tab( $tabs ) {
		$tabs['schema'] = __( 'Schema', 'acf' );
		return $tabs;
	}

	/**
	 * Render "Schema" tab content for post types
	 *
	 * @since 6.8.0
	 *
	 * @param array $acf_post_type The ACF post type data.
	 */
	public function render_post_type_schema_tab( $acf_post_type ) {
		?>
		<span class="acf-experimental-badge acf-field"><?php esc_html_e( 'Experimental', 'acf' ); ?></span>
		<?php
		// Add post-type-specific field: auto JSON-LD.
		acf_render_field_wrap(
			array(
				'type'         => 'true_false',
				'name'         => 'auto_jsonld',
				'key'          => 'auto_jsonld',
				'prefix'       => 'acf_post_type',
				'value'        => $acf_post_type['auto_jsonld'] ?? 0,
				'label'        => __( 'Automatically add JSON-LD data for fields on this post type', 'acf' ),
				'instructions' => __( 'When enabled, ACF field data will be automatically included as JSON-LD structured data in the page head for better SEO and semantic markup.', 'acf' ),
				'ui'           => true,
				'default'      => 0,
			)
		);

		// Add post-type-specific field: schema type.
		acf_render_field_wrap(
			array(
				'type'         => 'select',
				'name'         => 'schema_type',
				'key'          => 'schema_type',
				'prefix'       => 'acf_post_type',
				'value'        => $acf_post_type['schema_type'] ?? '',
				'label'        => __( 'Schema.org Type', 'acf' ),
				'instructions' => __( 'The Schema.org @type for JSON-LD output. By default, the type is automatically detected based on the schema properties assigned to your fields. You can assign additional types here. Select multiple types if needed (e.g., Recipe + Article).', 'acf' ),
				'choices'      => $this->get_schema_types(),
				'default'      => '',
				'allow_null'   => 1,
				'multiple'     => 1,
				'ui'           => 1,
			),
			'div',
			'field'
		);
	}

	/**
	 * Get available Schema.org types for selection
	 *
	 * @since 6.8.0
	 *
	 * @return array A hierarchical array of Schema.org types grouped by category.
	 */
	public function get_schema_types() {
		$types = array();

		// Get all types from schema hierarchy.
		$all_types = array_keys( SchemaData::get_type_hierarchy() );
		// Add 'Thing' which is the root and doesn't have a parent.
		$all_types[] = 'Thing';
		sort( $all_types );

		// Get priority types from Schema class.
		$priority_types_list = Schema::get_priority_types();
		$common_types_cat    = __( 'Common Types', 'acf' );
		$all_types_cat       = __( 'All Types', 'acf' );

		// Add priority types under "Common Types" group.
		$types[ $common_types_cat ] = array();
		foreach ( $priority_types_list as $type ) {
			if ( in_array( $type, $all_types, true ) ) {
				$types[ $common_types_cat ][ $type ] = $type;
			}
		}

		// Add remaining types under "All Types".
		$remaining_types = array_diff( $all_types, $priority_types_list );
		if ( ! empty( $remaining_types ) ) {
			$types[ $all_types_cat ] = array();
			foreach ( $remaining_types as $type ) {
				$types[ $all_types_cat ][ $type ] = $type;
			}
		}

		/**
		 * Filter the available Schema.org types
		 *
		 * Allows developers to add custom Schema.org types or modify existing ones.
		 *
		 * @param array $types The Schema.org type mappings grouped by category.
		 */
		return apply_filters( 'acf/schema/schema_types', $types );
	}

	/**
	 * Process ACF fields and map them to Schema.org structure
	 *
	 * Takes an array of field objects and processes them based on their schema_property setting.
	 * Fields with a schema_property are mapped to core Schema.org properties. Properties that
	 * expect objects (like 'author' or 'publisher') automatically get proper "@type" added.
	 * Fields without a schema_property are skipped.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field_objects Array of ACF field objects with values.
	 * @return array Processed data with core properties, with 'field_types' key containing types from qualified properties.
	 */
	public static function process_fields( $field_objects ) {
		$data        = array();
		$field_types = array();

		foreach ( $field_objects as $field_name => $field_object ) {
			// Skip empty values.
			if ( null === $field_object['value'] || '' === $field_object['value'] ) {
				continue;
			}

			// Check if this field has a schema property mapping.
			$schema_property = $field_object['schema_property'] ?? '';

			if ( ! empty( $schema_property ) ) {
				// Parse qualified property (e.g., "Offer.price" -> type: "Offer", property: "price").
				$parsed        = Schema::parse_qualified_property( $schema_property );
				$property_name = $parsed['property'];

				// Collect field types from qualified properties.
				if ( $parsed['type'] ) {
					$field_types[] = $parsed['type'];
				}

				$formatted_value = self::format_field_value_for_jsonld( $field_object['value'], $field_object );

				// Field has a schema property - map to core property using just the property name.
				$data[ $property_name ] = $formatted_value;
			}
		}

		// Add @type to nested objects based on schema.org ranges.
		$data = self::add_types_to_nested_objects( $data );

		// Include field types from qualified properties.
		if ( ! empty( $field_types ) ) {
			$data['field_types'] = array_values( array_unique( $field_types ) );
		}

		return $data;
	}

	/**
	 * Determine the final "@type" value for JSON-LD output
	 *
	 * Merges provided types (from post type/block settings) with field types
	 * (from qualified properties like "Recipe.prepTime"). Falls back to the
	 * default type if neither source provides any types.
	 *
	 * @since 6.8.0
	 *
	 * @param string|array|null $provided_types Types explicitly set in settings (can be string, array, or null).
	 * @param array             $field_types    Types extracted from qualified properties.
	 * @param string            $default_type   Fallback type if no types provided.
	 * @return string|array Final @type value (string for single type, array for multiple).
	 */
	public static function determine_schema_type( $provided_types, $field_types, $default_type = 'Thing' ) {
		$types = array();

		// Add provided types from post type/block settings.
		if ( ! empty( $provided_types ) ) {
			$types = is_array( $provided_types ) ? $provided_types : array( $provided_types );
		}

		// Merge in field types from qualified properties.
		if ( ! empty( $field_types ) && is_array( $field_types ) ) {
			$types = array_merge( $types, $field_types );
		}

		$types = array_values( array_unique( $types ) );

		if ( empty( $types ) ) {
			return $default_type;
		}

		return count( $types ) === 1 ? $types[0] : $types;
	}

	/**
	 * Add "@type" to nested objects based on schema.org property ranges
	 *
	 * Examines each property in the data and if it expects an object type
	 * (like Person, Organization, etc.), automatically adds the appropriate @type.
	 *
	 * For example, if 'author' contains { 'name': 'John' }, it becomes:
	 * { '@type': 'Person', 'name': 'John' }
	 *
	 * @since 6.8.0
	 *
	 * @param array $data The data array to process.
	 * @return array The data with @type added to nested objects.
	 */
	private static function add_types_to_nested_objects( $data ) {
		foreach ( $data as $property => $value ) {
			// Skip if value is not an array (can't be a nested object).
			if ( ! is_array( $value ) ) {
				continue;
			}

			// Skip if already has @type.
			if ( isset( $value['@type'] ) ) {
				continue;
			}

			// Check if this is a sequential array (list) vs associative array (object).
			// Sequential arrays are for properties that accept multiple values.
			// We only add @type to associative arrays (objects).
			$is_list = array_keys( $value ) === range( 0, count( $value ) - 1 );

			if ( $is_list ) {
				// This is a list/array, not a single object. Skip adding @type.
				continue;
			}

			// Check if this property expects an object type.
			if ( Schema::property_expects_object( $property ) ) {
				// Get the preferred object type for this property.
				$object_type = Schema::get_preferred_object_type( $property );

				if ( $object_type ) {
					// Add @type at the beginning of the array.
					$data[ $property ] = array_merge(
						array( '@type' => $object_type ),
						$value
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Render a JSON-LD script tag with the provided data
	 *
	 * Shared helper method for outputting JSON-LD structured data.
	 *
	 * @since 6.8.0
	 *
	 * @param array $jsonld_data The JSON-LD data array to output.
	 */
	public static function render_jsonld_script( $jsonld_data ) {
		if ( empty( $jsonld_data ) ) {
			return;
		}

		/**
		 * Action fired before rendering JSON-LD script tag
		 *
		 * Allows developers to output custom schemas or capture the data.
		 *
		 * @param array $jsonld_data The JSON-LD data array.
		 */
		do_action( 'acf/schema/render_script', $jsonld_data );

		/**
		 * Filter to disable ACF's default JSON-LD output
		 *
		 * Return true to prevent ACF from outputting the JSON-LD script tag.
		 * Useful if you want to handle the output yourself via the action above.
		 *
		 * @param bool  $disable      Whether to disable default output. Default false.
		 * @param array $jsonld_data  The JSON-LD data that would be output.
		 */
		$disable_output = apply_filters( 'acf/schema/disable_output', false, $jsonld_data );

		if ( $disable_output ) {
			return;
		}

		// Output the JSON-LD script tag.
		echo "<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $jsonld_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE );
		echo "\n</script>\n";
	}

	/**
	 * Format ACF field value for JSON-LD output
	 *
	 * Shared helper method for formatting field values consistently.
	 * Checks for field-type-specific formatting methods in this order:
	 * 1. Pre-filter to allow complete bypass of formatting logic
	 * 2. format_value_for_jsonld() - custom method for JSON-LD formatting (if field type implements it)
	 * 3. Field-type-specific formatting, defaulting to format_value_for_rest() for most types
	 * 4. Post-filter on the final formatted value
	 *
	 * @since 6.8.0
	 *
	 * @param mixed $value        The field value.
	 * @param array $field_object The ACF field object.
	 * @return mixed Formatted value.
	 */
	public static function format_field_value_for_jsonld( $value, $field_object ) {
		$field_type_name = $field_object['type'] ?? '';
		$field_name      = $field_object['name'] ?? '';

		/**
		 * Filter to bypass the default formatting logic entirely
		 *
		 * Return a non-null value to bypass all default formatting.
		 * This runs before any other formatting logic.
		 *
		 * @param mixed|null $pre_value     Return non-null to bypass default formatting.
		 * @param mixed      $value         The raw field value.
		 * @param array      $field_object  The ACF field object.
		 * @param string     $field_type_name The field type name.
		 */
		$pre_value = apply_filters( 'acf/schema/format_value/pre', null, $value, $field_object, $field_type_name );
		$pre_value = apply_filters( "acf/schema/format_value/pre/type={$field_type_name}", $pre_value, $value, $field_object );
		$pre_value = apply_filters( "acf/schema/format_value/pre/name={$field_name}", $pre_value, $value, $field_object );

		if ( null !== $pre_value ) {
			return $pre_value;
		}

		// Get the field type class instance.
		$field_type = acf_get_field_type( $field_type_name );

		// First priority: Check if field type has a custom format_value_for_jsonld method.
		if ( $field_type && method_exists( $field_type, 'format_value_for_jsonld' ) ) {
			$formatted_value = $field_type->format_value_for_jsonld( $value, null, $field_object );
		} else {
			// Second priority: Use format_value_for_rest or return as-is.
			if ( $field_type && method_exists( $field_type, 'format_value_for_rest' ) ) {
				$formatted_value = $field_type->format_value_for_rest( $value, null, $field_object );
			} else {
				// Final fallback: return value as-is.
				// Arrays are valid JSON-LD (e.g., multi-select values).
				$formatted_value = $value;
			}
		}

		/**
		 * Filter the formatted value before returning
		 *
		 * Allows modification of the value after all default formatting has been applied.
		 *
		 * @param mixed  $formatted_value The formatted value.
		 * @param mixed  $value           The raw field value.
		 * @param array  $field_object    The ACF field object.
		 * @param string $field_type_name The field type name.
		 */
		$formatted_value = apply_filters( 'acf/schema/format_value', $formatted_value, $value, $field_object, $field_type_name );
		$formatted_value = apply_filters( "acf/schema/format_value/type={$field_type_name}", $formatted_value, $value, $field_object );
		$formatted_value = apply_filters( "acf/schema/format_value/name={$field_name}", $formatted_value, $value, $field_object );

		return $formatted_value;
	}
}
