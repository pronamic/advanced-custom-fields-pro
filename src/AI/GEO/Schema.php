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

/**
 * Class Schema
 *
 * Provides utilities for working with schema.org types and properties
 * using pre-generated schema data from SchemaData.php.
 */
class Schema {

	/**
	 * Primitive types that don't require a "@type" in JSON-LD output
	 *
	 * @var array
	 */
	private static array $primitive_types = array(
		'Text',
		'Number',
		'Integer',
		'Float',
		'Boolean',
		'Date',
		'DateTime',
		'Time',
		'URL',
		'CssSelectorType',
		'PronounceableText',
		'XPathType',
	);

	/**
	 * Schema.org types that are modeled as objects but are practically text values
	 *
	 * These types don't have meaningful sub-properties and are typically
	 * represented as formatted strings (e.g., "PT30M" for Duration).
	 * Text fields should be able to match properties expecting these types.
	 *
	 * @var array
	 */
	private static array $text_value_types = array(
		'Duration',
		'Distance',
		'Energy',
		'Mass',
	);

	/**
	 * Cache for properties grouped by type
	 *
	 * @var array|null
	 */
	private static $properties_by_type_cache = null;

	/**
	 * Get priority schema types for common use cases
	 *
	 * Returns an array of commonly used Schema.org types that should be
	 * displayed first in selection dropdowns. When a context ID (field group ID)
	 * is provided, schema types from associated post types and blocks are
	 * prepended to the list.
	 *
	 * @since 6.8.0
	 *
	 * @param integer $context_id Optional field group ID to get context-aware priority types.
	 * @return array Array of priority type names.
	 */
	public static function get_priority_types( int $context_id = 0 ): array {
		$priority_types = array(
			'Thing',
			'Article',
			'BlogPosting',
			'NewsArticle',
			'Recipe',
			'Product',
			'Event',
			'HowTo',
			'FAQPage',
			'Person',
			'Organization',
			'LocalBusiness',
			'Place',
			'WebPage',
		);

		// Prepend context-specific schema types if a field group context is provided.
		if ( $context_id ) {
			$context_types = self::get_schema_types_from_field_group( $context_id );
			if ( ! empty( $context_types ) ) {
				$priority_types = array_unique( array_merge( $context_types, $priority_types ) );
			}
		}

		/**
		 * Filter the priority Schema.org types
		 *
		 * Allows developers to customize which types appear first in selection lists.
		 *
		 * @param array   $priority_types Array of priority type names.
		 * @param integer $context_id     The field group ID providing context, or 0.
		 */
		return apply_filters( 'acf/schema/schema_priority_types', $priority_types, $context_id );
	}

	/**
	 * Get schema types configured on post types or blocks associated with a field group.
	 *
	 * Extracts schema_type values from ACF post types and blocks that are
	 * referenced in the field group's location rules.
	 *
	 * @since 6.8.0
	 *
	 * @param integer $field_group_id The field group ID.
	 * @return array Array of unique schema type names.
	 */
	private static function get_schema_types_from_field_group( int $field_group_id ): array {
		$field_group = acf_get_field_group( $field_group_id );

		if ( empty( $field_group['location'] ) ) {
			return array();
		}

		$schema_types   = array();
		$acf_post_types = null; // Lazy load.

		foreach ( $field_group['location'] as $group ) {
			foreach ( $group as $rule ) {
				if ( $rule['operator'] !== '==' ) {
					continue;
				}

				// Handle post type location rules.
				if ( $rule['param'] === 'post_type' ) {
					// Lazy load ACF post types.
					if ( $acf_post_types === null ) {
						$acf_post_types = acf_get_acf_post_types();
					}

					foreach ( $acf_post_types as $acf_post_type ) {
						if ( isset( $acf_post_type['post_type'] )
							&& $acf_post_type['post_type'] === $rule['value']
							&& ! empty( $acf_post_type['schema_type'] ) ) {
							$types        = (array) $acf_post_type['schema_type'];
							$schema_types = array_merge( $schema_types, $types );
						}
					}
				}

				// Handle block location rules.
				if ( $rule['param'] === 'block' && $rule['value'] !== 'all' ) {
					$block_type = acf_get_block_type( $rule['value'] );
					if ( $block_type && ! empty( $block_type['schema_type'] ) ) {
						$types        = (array) $block_type['schema_type'];
						$schema_types = array_merge( $schema_types, $types );
					}
				}
			}
		}

		return array_unique( $schema_types );
	}

	/**
	 * Get all parent types for a given type
	 *
	 * @since 6.8.0
	 *
	 * @param string $type The schema.org type name
	 * @return array Array of parent type names in order from direct parent to root
	 */
	private static function get_type_parents( $type ) {
		$parents = array();
		$current = $type;

		$type_hierarchy = SchemaData::get_type_hierarchy();
		while ( isset( $type_hierarchy[ $current ] ) ) {
			$parent    = $type_hierarchy[ $current ];
			$parents[] = $parent;
			$current   = $parent;
		}

		return $parents;
	}

	/**
	 * Check if type_a is a parent/ancestor of type_b
	 *
	 * @since 6.8.0
	 *
	 * @param string $type_a The potential parent type
	 * @param string $type_b The child type to check
	 * @return boolean True if type_a is an ancestor of type_b
	 */
	private static function is_parent_of( $type_a, $type_b ) {
		$parents = self::get_type_parents( $type_b );
		return in_array( $type_a, $parents, true );
	}

	/**
	 * Infer the minimal set of types needed for a set of properties
	 *
	 * Given a list of properties, returns the most general types that
	 * directly define those properties, avoiding redundant child types.
	 *
	 * For example:
	 * - ['prepTime', 'cookTime'] -> ['Recipe'] (most specific type with those properties)
	 * - ['headline'] -> ['CreativeWork'] (the base type that defines headline)
	 *
	 * @since 6.8.0
	 *
	 * @param array $properties Array of property names
	 * @return array Array of type names
	 */
	public static function infer_types_from_properties( $properties ) {
		if ( empty( $properties ) ) {
			return array();
		}

		// For each property, collect the types that directly define it
		$types_per_property = array();
		$property_domains   = SchemaData::get_property_domains();

		foreach ( $properties as $property ) {
			if ( ! isset( $property_domains[ $property ] ) ) {
				continue;
			}

			// These are the types that directly define this property
			$types_per_property[ $property ] = $property_domains[ $property ];
		}

		if ( empty( $types_per_property ) ) {
			return array();
		}

		// If we only have one property, return its direct types
		if ( count( $types_per_property ) === 1 ) {
			return array_values( reset( $types_per_property ) );
		}

		// Find types that can cover all properties (directly or through inheritance)
		$all_types   = array_unique( array_merge( ...array_values( $types_per_property ) ) );
		$valid_types = array();

		foreach ( $all_types as $type ) {
			$type_chain = array_merge( array( $type ), self::get_type_parents( $type ) );
			$covers_all = true;

			foreach ( $types_per_property as $property => $defining_types ) {
				// Check if this type or any of its parents define this property
				if ( empty( array_intersect( $type_chain, $defining_types ) ) ) {
					$covers_all = false;
					break;
				}
			}

			if ( $covers_all ) {
				$valid_types[] = $type;
			}
		}

		// If we found types that cover everything, remove redundant parents
		if ( ! empty( $valid_types ) ) {
			$minimal_types = array();
			foreach ( $valid_types as $type ) {
				$is_redundant = false;
				foreach ( $valid_types as $other_type ) {
					if ( $type !== $other_type && self::is_parent_of( $type, $other_type ) ) {
						// This type is a parent of another type in the list, so it's redundant
						$is_redundant = true;
						break;
					}
				}
				if ( ! $is_redundant ) {
					$minimal_types[] = $type;
				}
			}
			return array_values( $minimal_types );
		}

		// No single type covers all properties, need multiple types
		return self::find_minimal_type_set( $properties );
	}

	/**
	 * Find minimal set of types to cover all properties
	 *
	 * Uses a greedy algorithm to find the smallest set of types that
	 * collectively support all given properties.
	 *
	 * @since 6.8.0
	 *
	 * @param array $properties Array of property names
	 * @return array Array of type names
	 */
	private static function find_minimal_type_set( $properties ) {
		$uncovered_properties = $properties;
		$selected_types       = array();

		$type_hierarchy   = SchemaData::get_type_hierarchy();
		$property_domains = SchemaData::get_property_domains();

		while ( ! empty( $uncovered_properties ) ) {
			$best_type     = null;
			$best_coverage = 0;

			// Find the type that covers the most uncovered properties
			foreach ( $type_hierarchy as $type => $parent ) {
				$coverage = 0;
				foreach ( $uncovered_properties as $property ) {
					if ( isset( $property_domains[ $property ] ) ) {
						$valid_types = $property_domains[ $property ];
						// Check if this type or any of its parents support the property
						$type_chain = array_merge( array( $type ), self::get_type_parents( $type ) );
						if ( ! empty( array_intersect( $type_chain, $valid_types ) ) ) {
							++$coverage;
						}
					}
				}

				if ( $coverage > $best_coverage ) {
					$best_type     = $type;
					$best_coverage = $coverage;
				}
			}

			if ( null === $best_type ) {
				break; // No type covers remaining properties
			}

			$selected_types[] = $best_type;

			// Remove covered properties
			$type_chain           = array_merge( array( $best_type ), self::get_type_parents( $best_type ) );
			$uncovered_properties = array_filter(
				$uncovered_properties,
				function ( $property ) use ( $type_chain, $property_domains ) {
					if ( ! isset( $property_domains[ $property ] ) ) {
						return true;
					}
					$valid_types = $property_domains[ $property ];
					return empty( array_intersect( $type_chain, $valid_types ) );
				}
			);
		}

		return $selected_types;
	}

	/**
	 * Get all properties grouped by type
	 *
	 * Returns an associative array where keys are type names and values
	 * are arrays of property names that belong to that type.
	 *
	 * @since 6.8.0
	 *
	 * @return array Associative array of type => properties
	 */
	public static function get_properties_by_type() {
		// Return cached result if available.
		if ( self::$properties_by_type_cache !== null ) {
			return self::$properties_by_type_cache;
		}

		$properties_by_type = array();
		$property_domains   = SchemaData::get_property_domains();

		// Build reverse mapping from properties to types
		foreach ( $property_domains as $property => $types ) {
			foreach ( $types as $type ) {
				if ( ! isset( $properties_by_type[ $type ] ) ) {
					$properties_by_type[ $type ] = array();
				}
				$properties_by_type[ $type ][] = $property;
			}
		}

		// Sort properties within each type
		foreach ( $properties_by_type as $type => $properties ) {
			sort( $properties_by_type[ $type ] );
		}

		// Sort by type name
		ksort( $properties_by_type );

		// Cache the result.
		self::$properties_by_type_cache = $properties_by_type;

		return $properties_by_type;
	}

	/**
	 * Get the expected types (range) for a property
	 *
	 * Returns the types that a property expects as its value.
	 * For example, 'author' expects ['Person', 'Organization']
	 *
	 * @since 6.8.0
	 *
	 * @param string $property The property name
	 * @return array Array of type names, or empty array if not found
	 */
	public static function get_property_range( $property ) {
		$property_ranges = SchemaData::get_property_ranges();
		return $property_ranges[ $property ] ?? array();
	}

	/**
	 * Check if a property expects an object (not a primitive type)
	 *
	 * Returns true if the property expects a schema.org Type as its value,
	 * meaning it should be a nested object with @type.
	 *
	 * Primitive types: Text, Number, Boolean, Date, DateTime, Time, URL, etc.
	 *
	 * @since 6.8.0
	 *
	 * @param string $property The property name
	 * @return boolean True if property expects an object
	 */
	public static function property_expects_object( $property ) {
		$range = self::get_property_range( $property );

		if ( empty( $range ) ) {
			return false;
		}

		// If any range type is not a primitive, it expects an object
		foreach ( $range as $type ) {
			if ( ! in_array( $type, self::$primitive_types, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the preferred object type for a property
	 *
	 * When a property expects an object, this returns the most appropriate type.
	 * For properties with multiple possible types, returns the first one.
	 *
	 * @since 6.8.0
	 *
	 * @param string $property The property name
	 * @return string|null The type name, or null if property doesn't expect an object
	 */
	public static function get_preferred_object_type( $property ) {
		if ( ! self::property_expects_object( $property ) ) {
			return null;
		}

		$range = self::get_property_range( $property );

		// Filter out primitive types
		$object_types = array_diff( $range, self::$primitive_types );

		// Return first object type
		return ! empty( $object_types ) ? reset( $object_types ) : null;
	}

	/**
	 * Get the supported JSON-LD ranges for a field type
	 *
	 * Returns the Schema.org types that a field type can output.
	 *
	 * @since 6.8.0
	 *
	 * @param string $field_type The ACF field type name (e.g., 'image', 'user')
	 * @return array Array of supported range types
	 */
	public static function get_field_type_ranges( $field_type ) {
		$field_type_obj = acf_get_field_type( $field_type );

		if ( ! $field_type_obj || ! method_exists( $field_type_obj, 'get_jsonld_output_types' ) ) {
			return array();
		}

		return $field_type_obj->get_jsonld_output_types();
	}

	/**
	 * Check if a Schema.org type has properties defined on it or its ancestors
	 *
	 * Walks up the type hierarchy checking if the type or any parent (excluding
	 * Thing, which is too generic) has properties defined. This distinguishes
	 * structural types (Person, Place, Organization) from value types (Duration,
	 * Distance) that have no meaningful sub-properties.
	 *
	 * @since 6.8.0
	 *
	 * @param string $type The Schema.org type name
	 * @return boolean True if type or an ancestor has properties defined
	 */
	public static function type_has_properties( $type ) {
		$current          = $type;
		$property_domains = SchemaData::get_property_domains();
		$type_hierarchy   = SchemaData::get_type_hierarchy();

		// Walk up the hierarchy, but stop before Thing (too generic).
		while ( $current && 'Thing' !== $current ) {
			foreach ( $property_domains as $domains ) {
				if ( in_array( $current, $domains, true ) ) {
					return true;
				}
			}
			// Move to parent type.
			$current = $type_hierarchy[ $current ] ?? null;
		}

		return false;
	}

	/**
	 * Get valid output formats for a field/property combination
	 *
	 * When a field type supports multiple output formats (e.g., image can
	 * output URL or ImageObject), this returns the formats valid for a
	 * specific property.
	 *
	 * @since 6.8.0
	 *
	 * @param string $field_type The ACF field type name
	 * @param string $property   The Schema.org property name
	 * @return array Array of valid output format types
	 */
	public static function get_valid_output_formats( $field_type, $property ) {
		$field_ranges    = self::get_field_type_ranges( $field_type );
		$property_ranges = self::get_property_range( $property );

		if ( empty( $field_ranges ) || empty( $property_ranges ) ) {
			return array();
		}

		$valid_formats = array();

		foreach ( $field_ranges as $field_range ) {
			// Direct match
			if ( in_array( $field_range, $property_ranges, true ) ) {
				$valid_formats[] = $field_range;
				continue;
			}

			// Skip inheritance check for primitive types.
			if ( in_array( $field_range, self::$primitive_types, true ) ) {
				continue;
			}

			// Check if field range is a subtype of any property range
			foreach ( $property_ranges as $property_range ) {
				// Skip if property expects a primitive type.
				if ( in_array( $property_range, self::$primitive_types, true ) ) {
					continue;
				}

				if ( self::is_parent_of( $property_range, $field_range ) ) {
					$valid_formats[] = $field_range;
					break;
				}
			}

			// For fields that output Thing, include property ranges and their subtypes.
			// This allows Group/Repeater fields to show specific types like Person, HowToStep.
			if ( 'Thing' === $field_range ) {
				foreach ( $property_ranges as $property_range ) {
					// Skip primitives.
					if ( in_array( $property_range, self::$primitive_types, true ) ) {
						continue;
					}

					// Include the property range itself if it's a structural subtype of Thing.
					if ( self::is_parent_of( $field_range, $property_range ) ) {
						if ( self::type_has_properties( $property_range ) ) {
							$valid_formats[] = $property_range;
						}
					}

					// Also include subtypes of the property range (e.g., HowToStep for CreativeWork).
					$type_hierarchy = SchemaData::get_type_hierarchy();
					foreach ( $type_hierarchy as $type => $parent ) {
						if ( self::is_parent_of( $property_range, $type ) ) {
							if ( self::type_has_properties( $type ) ) {
								$valid_formats[] = $type;
							}
						}
					}
				}
			}
		}

		// Handle text value types (Duration, Distance, etc.).
		// These are modeled as objects in Schema.org but are really formatted strings.
		// Text fields should be able to output these types.
		if ( in_array( 'Text', $field_ranges, true ) ) {
			foreach ( $property_ranges as $property_range ) {
				if ( in_array( $property_range, self::$text_value_types, true ) ) {
					$valid_formats[] = $property_range;
				}
			}
		}

		return array_unique( $valid_formats );
	}

	/**
	 * Parse a qualified property string (e.g., "Offer.price" or "price")
	 *
	 * Returns an array with 'type' and 'property' keys.
	 * For unqualified properties (no dot), type will be null.
	 *
	 * @since 6.8.0
	 *
	 * @param string $qualified_property The property string, optionally prefixed with Type.
	 * @return array Array with 'type' (string|null) and 'property' (string) keys.
	 */
	public static function parse_qualified_property( $qualified_property ) {
		if ( strpos( $qualified_property, '.' ) !== false ) {
			list( $type, $property ) = explode( '.', $qualified_property, 2 );
			return array(
				'type'     => $type,
				'property' => $property,
			);
		}
		return array(
			'type'     => null,
			'property' => $qualified_property,
		);
	}

	/**
	 * Get just the property name from a qualified property string
	 *
	 * @since 6.8.0
	 *
	 * @param string $qualified_property The property string, optionally prefixed with Type.
	 * @return string The property name without the type prefix.
	 */
	public static function get_property_name( $qualified_property ) {
		return self::parse_qualified_property( $qualified_property )['property'];
	}

	/**
	 * Get the type from a qualified property string
	 *
	 * @since 6.8.0
	 *
	 * @param string $qualified_property The property string, optionally prefixed with Type.
	 * @return string|null The type name, or null if not qualified.
	 */
	public static function get_property_type( $qualified_property ) {
		return self::parse_qualified_property( $qualified_property )['type'];
	}

	/**
	 * Get the default output format for a field/property combination
	 *
	 * When multiple formats are valid, returns the most appropriate default.
	 * Prefers object types over primitives when both are available.
	 *
	 * @since 6.8.0
	 *
	 * @param string $field_type The ACF field type name
	 * @param string $property   The Schema.org property name
	 * @return string|null The default format type, or null if none valid
	 */
	public static function get_default_output_format( $field_type, $property ) {
		$valid_formats = self::get_valid_output_formats( $field_type, $property );

		if ( empty( $valid_formats ) ) {
			return null;
		}

		// If only one format, use it
		if ( count( $valid_formats ) === 1 ) {
			return $valid_formats[0];
		}

		// Prefer object types over primitives (richer data)
		$object_formats = array_diff( $valid_formats, self::$primitive_types );

		if ( ! empty( $object_formats ) ) {
			return reset( $object_formats );
		}

		// Fall back to first primitive
		return $valid_formats[0];
	}
}
