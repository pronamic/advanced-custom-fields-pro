<?php
/**
 * ACF Block Bindings
 *
 * @since 6.2.8
 * @package ACF
 */

namespace ACF\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The core ACF Blocks binding class.
 */
class Bindings {
	/**
	 * Block Bindings constructor.
	 */
	public function __construct() {
		// Final check we're on WP 6.5 or newer.
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		add_action( 'acf/init', array( $this, 'register_binding_sources' ) );
	}

	/**
	 * Hooked to acf/init, register our binding sources.
	 */
	public function register_binding_sources() {
		if ( acf_get_setting( 'enable_block_bindings' ) ) {
			register_block_bindings_source(
				'acf/field',
				array(
					'label'              => _x( 'ACF Fields', 'The core ACF block binding source name for fields on the current page', 'acf' ),
					'get_value_callback' => array( $this, 'get_value' ),
				)
			);
		}
	}

	/**
	 * Handle returing the block binding value for an ACF meta value.
	 *
	 * @since 6.2.8
	 *
	 * @param array     $source_attrs   An array of the source attributes requested.
	 * @param \WP_Block $block_instance The block instance.
	 * @param string    $attribute_name The block's bound attribute name.
	 * @return string|null The block binding value or an empty string on failure.
	 */
	public function get_value( array $source_attrs, \WP_Block $block_instance, string $attribute_name ) {
		if ( ! isset( $source_attrs['key'] ) || ! is_string( $source_attrs['key'] ) ) {
			$value = '';
		} else {
			$field = get_field_object( $source_attrs['key'], false, true, true, true );

			if ( ! $field ) {
				return '';
			}

			if ( ! acf_field_type_supports( $field['type'], 'bindings', true ) ) {
				if ( is_preview() ) {
					return apply_filters( 'acf/bindings/field_not_supported_message', '[' . esc_html__( 'The requested ACF field type does not support output in Block Bindings or the ACF shortcode.', 'acf' ) . ']' );
				} else {
					return '';
				}
			}

			if ( isset( $field['allow_in_bindings'] ) && ! $field['allow_in_bindings'] ) {
				if ( is_preview() ) {
					return apply_filters( 'acf/bindings/field_not_allowed_message', '[' . esc_html__( 'The requested ACF field is not allowed to be output in bindings or the ACF Shortcode.', 'acf' ) . ']' );
				} else {
					return '';
				}
			}

			$value = $field['value'];

			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			// If we're not a scalar we'd throw an error, so return early for safety.
			if ( ! is_scalar( $value ) ) {
				$value = null;
			}
		}

		return apply_filters( 'acf/blocks/binding_value', $value, $source_attrs, $block_instance, $attribute_name );
	}
}
