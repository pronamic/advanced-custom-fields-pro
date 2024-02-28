<?php

if ( ! class_exists( 'acf_field_textarea' ) ) :

	class acf_field_textarea extends acf_field {


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
			$this->name          = 'textarea';
			$this->label         = __( 'Text Area', 'acf' );
			$this->description   = __( 'A basic textarea input for storing paragraphs of text.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-textarea.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/textarea/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'default_value' => '',
				'new_lines'     => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'rows'          => '',
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

			// vars
			$atts  = array();
			$keys  = array( 'id', 'class', 'name', 'value', 'placeholder', 'rows', 'maxlength' );
			$keys2 = array( 'readonly', 'disabled', 'required' );

			// rows
			if ( ! $field['rows'] ) {
				$field['rows'] = 8;
			}

			// atts (value="123")
			foreach ( $keys as $k ) {
				if ( isset( $field[ $k ] ) ) {
					$atts[ $k ] = $field[ $k ];
				}
			}

			// atts2 (disabled="disabled")
			foreach ( $keys2 as $k ) {
				if ( ! empty( $field[ $k ] ) ) {
					$atts[ $k ] = $k;
				}
			}

			// remove empty atts
			$atts = acf_clean_atts( $atts );

			// return
			acf_textarea_input( $atts );
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
					'type'         => 'textarea',
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
					'label'        => __( 'Rows', 'acf' ),
					'instructions' => __( 'Sets the textarea height', 'acf' ),
					'type'         => 'number',
					'name'         => 'rows',
					'placeholder'  => 8,
				)
			);

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
					'label'        => __( 'New Lines', 'acf' ),
					'instructions' => __( 'Controls how new lines are rendered', 'acf' ),
					'type'         => 'select',
					'name'         => 'new_lines',
					'choices'      => array(
						'wpautop' => __( 'Automatically add paragraphs', 'acf' ),
						'br'      => __( 'Automatically add &lt;br&gt;', 'acf' ),
						''        => __( 'No Formatting', 'acf' ),
					),
				)
			);
		}

		/**
		 * This filter is applied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value (mixed) the value which was loaded from the database
		 * @param   $post_id (mixed) the post_id from which the value was loaded
		 * @param   $field (array) the field array holding all the field options
		 *
		 * @return  $value (mixed) the modified value
		 */

		function format_value( $value, $post_id, $field ) {

			// bail early if no value or not for template
			if ( empty( $value ) || ! is_string( $value ) ) {
				return $value;
			}

			// new lines
			if ( $field['new_lines'] == 'wpautop' ) {
				$value = wpautop( $value );
			} elseif ( $field['new_lines'] == 'br' ) {
				$value = nl2br( $value );
			}

			// return
			return $value;
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

			// Check maxlength.
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
	acf_register_field_type( 'acf_field_textarea' );
endif; // class_exists check
