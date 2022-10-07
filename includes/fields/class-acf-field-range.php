<?php

if ( ! class_exists( 'acf_field_range' ) ) :

	class acf_field_range extends acf_field_number {


		/*
		*  initialize
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since   5.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function initialize() {

			// vars
			$this->name     = 'range';
			$this->label    = __( 'Range', 'acf' );
			$this->defaults = array(
				'default_value' => '',
				'min'           => '',
				'max'           => '',
				'step'          => '',
				'prepend'       => '',
				'append'        => '',
			);

		}


		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field( $field ) {

			// vars
			$atts  = array();
			$keys  = array( 'type', 'id', 'class', 'name', 'value', 'min', 'max', 'step' );
			$keys2 = array( 'readonly', 'disabled', 'required' );
			$html  = '';

			// step
			if ( ! $field['step'] ) {
				$field['step'] = 1;
			}

			// min / max
			if ( ! $field['min'] ) {
				$field['min'] = 0;
			}
			if ( ! $field['max'] ) {
				$field['max'] = 100;
			}

			// allow for prev 'non numeric' value
			if ( ! is_numeric( $field['value'] ) ) {
				$field['value'] = 0;
			}

			// constrain within max and min
			$field['value'] = max( $field['value'], $field['min'] );
			$field['value'] = min( $field['value'], $field['max'] );

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

			// open
			$html .= '<div class="acf-range-wrap">';

			// prepend
			if ( $field['prepend'] !== '' ) {
				$html .= '<div class="acf-prepend">' . acf_esc_html( $field['prepend'] ) . '</div>';
			}

			// range
			$html .= acf_get_text_input( $atts );

			// Calculate input width based on the largest possible input character length.
			// Also take into account the step size for decimal steps minus - 1.5 chars for leading "0.".
			$len = max(
				strlen( strval( $field['min'] ) ),
				strlen( strval( $field['max'] ) )
			);
			if ( floatval( $atts['step'] ) < 1 ) {
				$len += strlen( strval( $field['step'] ) ) - 1.5;
			}

				// input
				$html .= acf_get_text_input(
					array(
						'type'  => 'number',
						'id'    => $atts['id'] . '-alt',
						'value' => $atts['value'],
						'step'  => $atts['step'],
						// 'min' => $atts['min'], // removed to avoid browser validation errors
						// 'max' => $atts['max'],
						'style' => 'width: ' . ( 1.8 + $len * 0.7 ) . 'em;',
					)
				);

				// append
			if ( $field['append'] !== '' ) {
				$html .= '<div class="acf-append">' . acf_esc_html( $field['append'] ) . '</div>';
			}

			// close
			$html .= '</div>';

			// return
			echo $html;
		}


		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field  - an array holding all the field's data
		*/

		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf' ),
					'instructions' => __( 'Appears when creating a new post', 'acf' ),
					'type'         => 'number',
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
					'label'        => __( 'Minimum Value', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'min',
					'placeholder'  => '0',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum Value', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'max',
					'placeholder'  => '100',
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
					'label'        => __( 'Step Size', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'step',
					'placeholder'  => '1',
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
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'number', 'null' ),
				'required' => ! empty( $field['required'] ),
				'minimum'  => empty( $field['min'] ) ? 0 : (int) $field['min'],
				'maximum'  => empty( $field['max'] ) ? 100 : (int) $field['max'],
			);

			if ( isset( $field['default_value'] ) && is_numeric( $field['default_value'] ) ) {
				$schema['default'] = (int) $field['default_value'];
			}

			return $schema;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed      $value
		 * @param string|int $post_id
		 * @param array      $field
		 * @return mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			return acf_format_numerics( $value );
		}


	}


	// initialize
	acf_register_field_type( 'acf_field_range' );

endif; // class_exists check


