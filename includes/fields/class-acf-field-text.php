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

if ( ! class_exists( 'acf_field_text' ) ) :

	class acf_field_text extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function initialize() {

			// vars
			$this->name          = 'text';
			$this->label         = __( 'Text', 'acf' );
			$this->description   = __( 'A basic text input, useful for storing single string values.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-text.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/text/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'default_value' => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
		}


		/**
		 * Create the HTML interface for your field
		 *
		 * @param   $field - an array holding all the field's data
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 */
		function render_field( $field ) {
			$html = '';

			// Prepend text.
			if ( $field['prepend'] !== '' ) {
				$field['class'] .= ' acf-is-prepended';
				$html           .= '<div class="acf-input-prepend">' . acf_esc_html( $field['prepend'] ) . '</div>';
			}

			// Append text.
			if ( $field['append'] !== '' ) {
				$field['class'] .= ' acf-is-appended';
				$html           .= '<div class="acf-input-append">' . acf_esc_html( $field['append'] ) . '</div>';
			}

			// Input.
			$input_attrs = array();
			foreach ( array( 'type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern', 'readonly', 'disabled', 'required' ) as $k ) {
				if ( isset( $field[ $k ] ) ) {
					$input_attrs[ $k ] = $field[ $k ];
				}
			}

			if ( isset( $field['input-data'] ) && is_array( $field['input-data'] ) ) {
				foreach ( $field['input-data'] as $name => $attr ) {
					$input_attrs[ 'data-' . $name ] = $attr;
				}
			}

			$html .= '<div class="acf-input-wrap">' . acf_get_text_input( acf_filter_attrs( $input_attrs ) ) . '</div>';

			// Display.
			echo $html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- only safe HTML output generated and escaped by functions above.
		}


		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @param   $field  - an array holding all the field's data
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 */
		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf' ),
					'instructions' => __( 'Appears when creating a new post', 'acf' ),
					'type'         => 'text',
					'name'         => 'default_value',
				)
			);
		}

		/**
		 * Renders the field settings used in the "Validation" tab.
		 *
		 * @since 6.0
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		function render_field_validation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Character Limit', 'acf' ),
					'instructions' => __( 'Leave blank for no limit', 'acf' ),
					'type'         => 'number',
					'name'         => 'maxlength',
				)
			);
		}

		/**
		 * Renders the field settings used in the "Presentation" tab.
		 *
		 * @since 6.0
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		function render_field_presentation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Placeholder Text', 'acf' ),
					'instructions' => __( 'Appears within the input', 'acf' ),
					'type'         => 'text',
					'name'         => 'placeholder',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Prepend', 'acf' ),
					'instructions' => __( 'Appears before the input', 'acf' ),
					'type'         => 'text',
					'name'         => 'prepend',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Append', 'acf' ),
					'instructions' => __( 'Appears after the input', 'acf' ),
					'type'         => 'text',
					'name'         => 'append',
				)
			);
		}

		/**
		 * validate_value
		 *
		 * Validates a field's value.
		 *
		 * @date    29/1/19
		 * @since   5.7.11
		 *
		 * @param   (bool|string) Whether the value is vaid or not.
		 * @param   mixed                                          $value The field value.
		 * @param   array                                          $field The field array.
		 * @param   string                                         $input The HTML input name.
		 * @return  (bool|string)
		 */
		function validate_value( $valid, $value, $field, $input ) {

			// Check maxlength
			if ( $field['maxlength'] && ( acf_strlen( $value ) > $field['maxlength'] ) ) {
				return sprintf( __( 'Value must not exceed %d characters', 'acf' ), $field['maxlength'] );
			}

			// Return.
			return $valid;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		function get_rest_schema( array $field ) {
			$schema = parent::get_rest_schema( $field );

			if ( ! empty( $field['maxlength'] ) ) {
				$schema['maxLength'] = (int) $field['maxlength'];
			}

			return $schema;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_text' );
endif; // class_exists check
