<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Applying auto inline editing to ACF blocks.
 *
 * @package ACF
 */

namespace ACF\Blocks\AutoInlineEditing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns an array of field type names which support contenteditable (allows typing text) attribute.
 *
 * @return array
 */
function get_allowed_contenteditable_fields(): array {
	return array( 'text', 'textarea' );
}

/**
 * Returns an array of field type names will be ignored by the automatic application of inline editing attributes.
 *
 * @return array
 */
function get_non_auto_inline_editing_fields(): array {
	return array( 'repeater', 'flexible-content' );
}

/**
 * This function populates a global variable called acf_fields_used_in_block_render_template, which is an array
 * where each key is the value entered for the field, and the value is the field data, including the current value.
 *
 * @param mixed  $field_value The field_value.
 * @param string $post_id     The post ID for this value.
 * @param array  $field       The field array.
 *
 * @return mixed
 */
function populate_auto_inline_editing_values( $field_value, $post_id, $field ) {

	global $acf_fields_used_in_block_render_template, $acf_blocks_doing_auto_inline_editing;

	if ( ! $acf_blocks_doing_auto_inline_editing || ! empty( $field['parent_repeater'] ) ) {
		return $field_value;
	}

	// Add this field and its value to the global variable so we can grab it when rendering later in apply_inline_editing_attributes_to_render_template.
	if ( ! is_array( $field_value ) ) {
		if ( empty( $field_value ) ) {
			$field_value = 'acf_auto_inline_editing_field_name_' . $field['name'];
		}

		$field['value'] = $field_value;

		// Note: If 2 fields happen to have the exact same value it's most-likely fine, but there are edge cases.
		// Because in the DOM they get applied top-to-bottom, we also check top-to-bottom when pulling them from this array.
		//
		// A small, known edge case exists here if someone calls $a = get_field('a') and $b = get_field('b'), but then renders $b before $a.
		// If BOTH field $a and field $b have the exact same value AND are rendered in a different order than they were called, you would end up
		// with a scenario where editing the inline value of $a actually edits the value for $b.
		// Regardless, it would likely be obvious to the block developer because you would see it,
		// both in the field value in the block sidebar, and also inline/preview, wherever field a/b are used.
		$acf_fields_used_in_block_render_template[] = $field;
	}

	return $field_value;
}
add_filter( 'acf/format_value', __NAMESPACE__ . '\populate_auto_inline_editing_values', 10, 3 );

/**
 * Applies inline editing attributes to dom elements if they contain field values.
 *
 * @param string  $path       The path to the render template for this block.
 * @param array   $block      The block data.
 * @param boolean $is_preview Whether we are in the block editor or not.
 * @return string
 */
function apply_inline_editing_attributes_to_render_template( $path, $block, $is_preview ): string {
	global $acf_fields_used_in_block_render_template, $acf_blocks_doing_auto_inline_editing;

	// Don't apply autoInlineEditing if the current PHP doesn't include DOMDocument or DOMXPath.
	if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
		ob_start();
		include $path;
		return ob_get_clean();
	}

	$allowed_contenteditable_field_types = get_allowed_contenteditable_fields();
	$non_auto_inline_editing_fields      = get_non_auto_inline_editing_fields();

	$acf_fields_used_in_block_render_template = array();

	$acf_blocks_doing_auto_inline_editing = true;

	ob_start();
	include $path;
	$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>' . ob_get_clean();

	$acf_blocks_doing_auto_inline_editing = false;

	// Load the HTML into DOMDocument
	$dom = new \DOMDocument();
	libxml_use_internal_errors( true ); // Suppress warnings for invalid HTML
	$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	// Get all elements
	$xpath    = new \DOMXPath( $dom );
	$elements = $xpath->query( '//*' );

	// Iterate over elements and modify based on text content
	foreach ( $elements as $element ) {
		$field_names_for_popover = array();

		$top_level_text = '';

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		if ( empty( $element->childNodes ) ) {
			continue;
		}

		// Loop through the child nodes of the current element
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		foreach ( $element->childNodes as $child ) {
			// Check if the child node is a text node
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName
			if ( $child->nodeType === XML_TEXT_NODE ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				$top_level_text .= $child->nodeValue;
			}
		}

		$top_level_text = trim( $top_level_text );

		if ( ! empty( $top_level_text ) ) {
			$acf_field_found = false;

			// Loop through each field used in this render template.
			foreach ( $acf_fields_used_in_block_render_template as $key => $field_data ) {
				if ( ! $field_data['name'] ) {
					continue;
				}

				// If the value for this field matches the text in the dom, apply the inline editing attributes.
				if ( $field_data['value'] === $top_level_text ) {
					$acf_field_found        = true;
					$field_slug             = $field_data['name'];
					$field_value            = $field_data['value'];
					$field_type             = $field_data['type'];
					$field_placeholder_text = ! empty( $field_data['placeholder'] ) ? $field_data['placeholder'] : __( 'Type to edit...', 'acf' );

					if ( ! in_array( $field_type, $non_auto_inline_editing_fields, true ) ) {
						if ( in_array( $field_type, $allowed_contenteditable_field_types, true ) ) {
							// Add the contenteditable things.
							if ( ! $element->getAttribute( 'data-acf-inline-contenteditable' ) ) {
								$element->setAttribute( 'role', 'button' );
								$element->setAttribute( 'data-acf-inline-contenteditable', true );

								$element->setAttribute( 'data-acf-inline-contenteditable-field-slug', str_replace( 'acf_auto_inline_editing_field_name_', '', $field_slug ) );
								$element->setAttribute( 'data-acf-placeholder', $field_placeholder_text );
							}

							// phpcs:ignore WordPress.NamingConventions.ValidVariableName
							if ( $field_value !== 'acf_auto_inline_editing_field_name_' . $field_slug ) {
								// phpcs:ignore WordPress.NamingConventions.ValidVariableName
								$element->nodeValue = $field_value;
							} else {
								// phpcs:ignore WordPress.NamingConventions.ValidVariableName
								$element->nodeValue = '';
							}
						} else {
							// Make the field popover instead of contenteditable.
							$field_names_for_popover[] = str_replace( 'acf_auto_inline_editing_field_name_', '', $field_slug );
						}
					}

					// We found a matching field so we can stop looping.
					break;
				}
			}

			if ( ! $acf_field_found ) {
				foreach ( $acf_fields_used_in_block_render_template as $key => $field_data ) {
					// Remove the acf_auto_inline_editing_field_name_ placeholder from the text node.
					if ( strpos( $top_level_text, 'acf_auto_inline_editing_field_name_' . $field_data['name'] ) !== false ) {
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName
						$element->nodeValue = str_replace( 'acf_auto_inline_editing_field_name_' . $field_data['name'], '', $top_level_text );
					}
				}
			}
		}

		// If the value for this field matches the field slug, remove it.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		if ( str_starts_with( $top_level_text, 'acf_auto_inline_editing_field_name_' ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName
			$element->textContent = '';
		}

		// Loop over each attribute. If an attribute comes from acf, make it popup when parent is selected.
		foreach ( $element->attributes as $attribute ) {
			if ( $attribute->name === 'data-acf-inline-contenteditable-field-slug' ) {
				continue;
			}
			$attribute_value = trim( $attribute->value );

			foreach ( $acf_fields_used_in_block_render_template as $field_data ) {
				if ( empty( $field_data['name'] ) ) {
					continue;
				}

				$field_slug  = $field_data['name'];
				$field_value = $field_data['value'];

				if ( ! is_array( $field_value ) && $attribute_value === $field_value ) {
					$field_names_for_popover[] = str_replace( 'acf_auto_inline_editing_field_name_', '', $field_slug );
				}

				if ( strpos( $attribute_value, 'acf_auto_inline_editing_field_name_' ) !== false ) {
					$attribute_value = str_replace( 'acf_auto_inline_editing_field_name_' . $field_slug, '', $attribute_value );
					$element->setAttribute( $attribute->name, $attribute_value );
				}
			}
		}

		$field_names_for_popover = array_unique( $field_names_for_popover );

		// Don't add popover fields to the top level element unless it has text content (as opposed to html/non-text content, which is what most top level elements contain).
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$is_top_level = isset( $element->parentNode->tagName ) && $element->parentNode->tagName === 'body';

		if ( ! $is_top_level && ! empty( $field_names_for_popover ) ) {
			$preexisting_inline_fields_uid = $element->getAttribute( 'data-acf-inline-fields-uid' );
			if ( ! $preexisting_inline_fields_uid ) {
				$element->setAttribute( 'data-acf-inline-fields-uid', implode( '__', $field_names_for_popover ) . '__' . $block['id'] );
				$element->setAttribute(
					'data-acf-inline-fields',
					wp_json_encode( $field_names_for_popover ),
				);
			}
		}
	}
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$dom->preserveWhiteSpace = true;
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$dom->formatOutput = false;

	return str_replace( '<meta charset="UTF-8">', '', $dom->saveHTML() );
}
