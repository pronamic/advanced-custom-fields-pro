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

namespace ACF\Pro\AI\GEO\Outputs;

use ACF\AI\GEO\GEO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF GEO Blocks Output
 *
 * Extends ACF Blocks to add JSON-LD structured data output for block fields.
 *
 * To enable JSON-LD output for a block, add "autoJsonLd": true to the ACF namespace
 * in your block.json file, or use the acf/ai/block_jsonld_enabled filter.
 *
 * See README.md for complete usage examples and documentation.
 */
class Blocks {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the GEO Blocks extension.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function init() {
		// Add support for autoJsonLd property from block.json ACF namespace.
		add_filter( 'block_type_metadata_settings', array( $this, 'add_block_json_auto_jsonld_support' ), 10, 2 );

		// Add support for autoJsonLd property from programmatic registration.
		add_filter( 'acf/register_block_type_args', array( $this, 'add_programmatic_auto_jsonld_support' ) );

		// Add front-end JSON-LD output for blocks.
		add_action( 'acf/blocks/pre_block_template_render', array( $this, 'output_block_jsonld_data' ), 10, 6 );
	}

	/**
	 * Add support for autoJsonLd property from block.json ACF namespace
	 *
	 * Maps the 'autoJsonLd' property from block.json's acf namespace to 'auto_jsonld' setting.
	 * Also maps 'schemaType' to 'schema_type' for custom Schema.org @type values.
	 * This runs after ACF's own block.json handler.
	 *
	 * @since 6.8.0
	 *
	 * @param array $settings The compiled block settings.
	 * @param array $metadata The raw json metadata.
	 * @return array Modified block settings.
	 */
	public function add_block_json_auto_jsonld_support( $settings, $metadata ) {
		// Only process ACF blocks.
		if ( ! isset( $metadata['acf'] ) || ! is_array( $metadata['acf'] ) ) {
			return $settings;
		}

		// Map autoJsonLd from ACF namespace to auto_jsonld.
		if ( isset( $metadata['acf']['autoJsonLd'] ) ) {
			$settings['auto_jsonld'] = $metadata['acf']['autoJsonLd'];
		}

		// Map schemaType from ACF namespace to schema_type.
		if ( isset( $metadata['acf']['schemaType'] ) ) {
			$settings['schema_type'] = $metadata['acf']['schemaType'];
		}

		return $settings;
	}

	/**
	 * Add support for autoJsonLd property from programmatic registration
	 *
	 * Maps the 'autoJsonLd' property from the acf namespace to 'auto_jsonld' setting
	 * and 'schemaType' to 'schema_type' for blocks registered via acf_register_block_type().
	 *
	 * @since 6.8.0
	 *
	 * @param array $block The block settings array.
	 * @return array Modified block settings.
	 */
	public function add_programmatic_auto_jsonld_support( $block ) {
		// Check if this is a programmatic registration with ACF namespace.
		if ( isset( $block['acf'] ) && is_array( $block['acf'] ) ) {
			if ( isset( $block['acf']['autoJsonLd'] ) ) {
				$block['auto_jsonld'] = $block['acf']['autoJsonLd'];
			}

			if ( isset( $block['acf']['schemaType'] ) ) {
				$block['schema_type'] = $block['acf']['schemaType'];
			}
		}

		return $block;
	}

	/**
	 * Output JSON-LD structured data for ACF block fields.
	 *
	 * @since 6.8.0
	 *
	 * @param array    $block      The block props.
	 * @param string   $content    The block content.
	 * @param boolean  $is_preview Whether or not the block is being rendered for editing preview.
	 * @param integer  $post_id    The current post being edited or viewed.
	 * @param WP_Block $wp_block   The block instance.
	 * @param array    $context    The block context array.
	 */
	public function output_block_jsonld_data( $block, $content, $is_preview, $post_id, $wp_block, $context ) {
		/**
		 * Filters whether to output debug comments in HTML
		 *
		 * @since 6.8.0
		 *
		 * @param bool $debug Whether to output debug comments. Default false.
		 */
		$debug = apply_filters( 'acf/schema/debug', false );

		// Don't output JSON-LD in the block editor preview.
		if ( $is_preview ) {
			if ( $debug ) {
				echo "<!-- ACF AI Block JSON-LD: Skipped (block editor preview) -->\n";
			}
			return;
		}

		// Don't output if we don't have a block name.
		if ( empty( $block['name'] ) ) {
			if ( $debug ) {
				echo "<!-- ACF AI Block JSON-LD: Skipped (no block name) -->\n";
			}
			return;
		}

		if ( $debug ) {
			echo '<!-- ACF AI Block JSON-LD: Checking block: ' . esc_html( $block['name'] ) . " -->\n";
		}

		// Get the block type.
		$block_type = acf_get_block_type( $block['name'] );
		if ( ! $block_type ) {
			if ( $debug ) {
				echo '<!-- ACF AI Block JSON-LD: Block type not found for ' . esc_html( $block['name'] ) . " -->\n";
			}
			return;
		}

		// Check if this block has auto_jsonld enabled.
		$auto_jsonld = isset( $block_type['auto_jsonld'] ) ? $block_type['auto_jsonld'] : false;

		/**
		 * Filters whether JSON-LD output is enabled for this specific block.
		 *
		 * @since 6.8.0
		 *
		 * @param boolean $auto_jsonld Whether JSON-LD is enabled for this block.
		 * @param array   $block       The block props.
		 * @param array   $block_type  The block type settings.
		 */
		$auto_jsonld = apply_filters( 'acf/schema/block_jsonld_enabled', $auto_jsonld, $block, $block_type );

		// Exit if auto JSON-LD is not enabled for this block.
		if ( ! $auto_jsonld ) {
			if ( $debug ) {
				echo '<!-- ACF AI Block JSON-LD: Block \'' . esc_html( $block['name'] ) . "' does not have JSON-LD enabled -->\n";
			}
			return;
		}

		/**
		 * Filters the field objects before retrieval, allowing blocks to provide custom data.
		 *
		 * This is useful for blocks that link to other post types or need custom field data handling.
		 * Return a non-null value to short-circuit the default get_field_objects() call.
		 *
		 * @since 6.8.0
		 *
		 * @param array|null $field_objects The field objects array, or null to use default behavior.
		 * @param array      $block         The block props.
		 * @param array      $block_type    The block type settings.
		 * @param int        $post_id       The current post ID.
		 */
		$field_objects = apply_filters( 'acf/schema/block_field_objects', null, $block, $block_type, $post_id );

		/**
		 * Filters the field objects for a specific block name/type.
		 *
		 * The dynamic portion of the hook name, `$block['name']`, refers to the block type name.
		 * For example, 'acf/schema/block_field_objects/block_name=acf/testimonial' for the testimonial block.
		 *
		 * @since 6.8.0
		 *
		 * @param array|null $field_objects The field objects array, or null to use default behavior.
		 * @param array      $block         The block props.
		 * @param array      $block_type    The block type settings.
		 * @param int        $post_id       The current post ID.
		 */
		$field_objects = apply_filters( 'acf/schema/block_field_objects/block_name=' . $block['name'], $field_objects, $block, $block_type, $post_id );

		// If no custom field objects were provided, get them from the block.
		if ( null === $field_objects ) {
			// Get all ACF field objects with values for this block.
			// Use get_field_objects() to get both field metadata and values in a single call.
			$field_objects = get_field_objects( $block['id'], false );
		}

		if ( ! $field_objects || ! is_array( $field_objects ) ) {
			if ( $debug ) {
				echo '<!-- ACF AI Block JSON-LD: No ACF fields found for block ' . esc_html( $block['name'] ) . " -->\n";
			}
			return;
		}

		if ( $debug ) {
			echo '<!-- ACF AI Block JSON-LD: Found ' . count( $field_objects ) . " ACF fields -->\n";
		}

		// Process ACF fields and extract types from qualified properties.
		// This handles schema_property mapping.
		$processed_fields = GEO::process_fields( $field_objects );

		// Get any explicitly set schema type for this block.
		$provided_type = ! empty( $block_type['schema_type'] ) ? $block_type['schema_type'] : null;
		$field_types   = $processed_fields['field_types'] ?? array();

		// Determine the final @type based on provided type or field types from qualified properties.
		// Supports both string (single type) and array (multiple types).
		$schema_type = GEO::determine_schema_type( $provided_type, $field_types, 'PropertyValue' );

		// Remove internal type data from processed fields.
		unset( $processed_fields['field_types'] );

		// Build base JSON-LD structured data.
		$jsonld_data = array(
			'@context' => 'https://schema.org',
			'@type'    => $schema_type, // Can be string or array
			'@id'      => ! empty( $block['id'] ) ? get_permalink( $post_id ) . '#' . $block['id'] : get_permalink( $post_id ),
		);

		// Add block title if available.
		if ( ! empty( $block_type['title'] ) ) {
			$jsonld_data['name'] = $block_type['title'];
		}

		// Add block description if available.
		if ( ! empty( $block_type['description'] ) ) {
			$jsonld_data['description'] = $block_type['description'];
		}

		// Merge processed fields into JSON-LD data.
		$jsonld_data = array_merge( $jsonld_data, $processed_fields );

		/**
		 * Filters the JSON-LD data before output for a block.
		 *
		 * @since 6.8.0
		 *
		 * @param array $jsonld_data The JSON-LD data array.
		 * @param array $block       The block props.
		 * @param array $block_type  The block type settings.
		 */
		$jsonld_data = apply_filters( 'acf/schema/data', $jsonld_data, $block, $block_type );

		// Only output if we have data after filtering.
		if ( empty( $jsonld_data ) ) {
			return;
		}

		// Output the JSON-LD using the shared helper.
		GEO::render_jsonld_script( $jsonld_data );
	}
}
