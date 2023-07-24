<?php

if ( ! class_exists( 'acf_field_button_group' ) ) :

	class acf_field_button_group extends acf_field {


		/**
		 *  initialize()
		 *
		 *  This function will setup the field type data
		 *
		 *  @date    18/9/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function initialize() {

			// vars
			$this->name          = 'button_group';
			$this->label         = __( 'Button Group', 'acf' );
			$this->category      = 'choice';
			$this->description   = __( 'A group of buttons with values that you specify, users can choose one option from the values provided.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-button-group.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/button-group/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'choices'       => array(),
				'default_value' => '',
				'allow_null'    => 0,
				'return_format' => 'value',
				'layout'        => 'horizontal',
			);

		}


		/**
		 *  render_field()
		 *
		 *  Creates the field's input HTML
		 *
		 *  @date    18/9/17
		 *  @since   5.6.3
		 *
		 *  @param   array $field The field settings array
		 *  @return  n/a
		 */

		function render_field( $field ) {

			// vars
			$html     = '';
			$selected = null;
			$buttons  = array();
			$value    = esc_attr( $field['value'] );

			// bail ealrly if no choices
			if ( empty( $field['choices'] ) ) {
				return;
			}

			// buttons
			foreach ( $field['choices'] as $_value => $_label ) {

				// checked
				$checked = ( $value === esc_attr( $_value ) );
				if ( $checked ) {
					$selected = true;
				}

				// append
				$buttons[] = array(
					'name'    => $field['name'],
					'value'   => $_value,
					'label'   => $_label,
					'checked' => $checked,
				);

			}

			// maybe select initial value
			if ( ! $field['allow_null'] && $selected === null ) {
				$buttons[0]['checked'] = true;
			}

			// div
			$div = array( 'class' => 'acf-button-group' );

			if ( $field['layout'] == 'vertical' ) {
				$div['class'] .= ' -vertical'; }
			if ( $field['class'] ) {
				$div['class'] .= ' ' . $field['class']; }
			if ( $field['allow_null'] ) {
				$div['data-allow_null'] = 1; }

			// hdden input
			$html .= acf_get_hidden_input( array( 'name' => $field['name'] ) );

			// open
			$html .= '<div ' . acf_esc_attr( $div ) . '>';

			// loop
			foreach ( $buttons as $button ) {

				// checked
				if ( $button['checked'] ) {
					$button['checked'] = 'checked';
				} else {
					unset( $button['checked'] );
				}

				// append
				$html .= acf_get_radio_input( $button );

			}

			// close
			$html .= '</div>';

			// return
			echo $html;

		}


		/**
		 *  render_field_settings()
		 *
		 *  Creates the field's settings HTML
		 *
		 *  @date    18/9/17
		 *  @since   5.6.3
		 *
		 *  @param   array $field The field settings array
		 *  @return  n/a
		 */
		function render_field_settings( $field ) {
			// Encode choices (convert from array).
			$field['choices'] = acf_encode_choices( $field['choices'] );

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Choices', 'acf' ),
					'instructions' => __( 'Enter each choice on a new line.', 'acf' ) . '<br />' . __( 'For more control, you may specify both a value and label like this:', 'acf' ) . '<br /><span class="acf-field-setting-example">' . __( 'red : Red', 'acf' ) . '</span>',
					'type'         => 'textarea',
					'name'         => 'choices',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf' ),
					'instructions' => __( 'Appears when creating a new post', 'acf' ),
					'type'         => 'text',
					'name'         => 'default_value',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Value', 'acf' ),
					'instructions' => __( 'Specify the returned value on front end', 'acf' ),
					'type'         => 'radio',
					'name'         => 'return_format',
					'layout'       => 'horizontal',
					'choices'      => array(
						'value' => __( 'Value', 'acf' ),
						'label' => __( 'Label', 'acf' ),
						'array' => __( 'Both (Array)', 'acf' ),
					),
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
					'label'        => __( 'Allow Null', 'acf' ),
					'instructions' => '',
					'name'         => 'allow_null',
					'type'         => 'true_false',
					'ui'           => 1,
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
					'label'        => __( 'Layout', 'acf' ),
					'instructions' => '',
					'type'         => 'radio',
					'name'         => 'layout',
					'layout'       => 'horizontal',
					'choices'      => array(
						'horizontal' => __( 'Horizontal', 'acf' ),
						'vertical'   => __( 'Vertical', 'acf' ),
					),
				)
			);
		}

		/*
		*  update_field()
		*
		*  This filter is appied to the $field before it is saved to the database
		*
		*  @date    18/9/17
		*  @since   5.6.3
		*
		*  @param   array $field The field array holding all the field options
		*  @return  $field
		*/

		function update_field( $field ) {

			return acf_get_field_type( 'radio' )->update_field( $field );
		}


		/*
		*  load_value()
		*
		*  This filter is appied to the $value after it is loaded from the db
		*
		*  @date    18/9/17
		*  @since   5.6.3
		*
		*  @param   mixed   $value      The value found in the database
		*  @param   mixed   $post_id    The post ID from which the value was loaded from
		*  @param   array   $field      The field array holding all the field options
		*  @return  $value
		*/

		function load_value( $value, $post_id, $field ) {

			return acf_get_field_type( 'radio' )->load_value( $value, $post_id, $field );

		}


		/*
		*  translate_field
		*
		*  This function will translate field settings
		*
		*  @date    18/9/17
		*  @since   5.6.3
		*
		*  @param   array $field The field array holding all the field options
		*  @return  $field
		*/

		function translate_field( $field ) {

			return acf_get_field_type( 'radio' )->translate_field( $field );

		}


		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @date    18/9/17
		*  @since   5.6.3
		*
		*  @param   mixed   $value      The value found in the database
		*  @param   mixed   $post_id    The post ID from which the value was loaded from
		*  @param   array   $field      The field array holding all the field options
		*  @return  $value
		*/

		function format_value( $value, $post_id, $field ) {

			return acf_get_field_type( 'radio' )->format_value( $value, $post_id, $field );

		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		function get_rest_schema( array $field ) {
			$schema = parent::get_rest_schema( $field );

			if ( isset( $field['default_value'] ) && '' !== $field['default_value'] ) {
				$schema['default'] = $field['default_value'];
			}

			/**
			 * If a user has defined keys for the buttons,
			 * we should use the keys for the available options to POST to,
			 * since they are what is displayed in GET requests.
			 */
			$button_keys = array_diff(
				array_keys( $field['choices'] ),
				array_values( $field['choices'] )
			);

			$schema['enum']   = empty( $button_keys ) ? $field['choices'] : $button_keys;
			$schema['enum'][] = null;

			// Allow null via UI will value to empty string.
			if ( ! empty( $field['allow_null'] ) ) {
				$schema['enum'][] = '';
			}

			return $schema;
		}

	}


	// initialize
	acf_register_field_type( 'acf_field_button_group' );

endif; // class_exists check


