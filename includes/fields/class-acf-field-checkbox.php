<?php

if ( ! class_exists( 'acf_field_checkbox' ) ) :

	class acf_field_checkbox extends acf_field {


		/*
		*  __construct
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
			$this->name          = 'checkbox';
			$this->label         = __( 'Checkbox', 'acf' );
			$this->category      = 'choice';
			$this->description   = __( 'A group of checkbox inputs that allow the user to select one, or multiple values that you specify.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-checkbox.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/checkbox/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'layout'                    => 'vertical',
				'choices'                   => array(),
				'default_value'             => '',
				'allow_custom'              => 0,
				'save_custom'               => 0,
				'toggle'                    => 0,
				'return_format'             => 'value',
				'custom_choice_button_text' => __( 'Add new choice', 'acf' ),
			);

		}


		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field (array) the $field being rendered
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field (array) the $field being edited
		*  @return  n/a
		*/

		function render_field( $field ) {

			// reset vars
			$this->_values      = array();
			$this->_all_checked = true;

			// ensure array
			$field['value']   = acf_get_array( $field['value'] );
			$field['choices'] = acf_get_array( $field['choices'] );

			// hiden input
			acf_hidden_input( array( 'name' => $field['name'] ) );

			// vars
			$li = '';
			$ul = array(
				'class' => 'acf-checkbox-list',
			);

			// append to class
			$ul['class'] .= ' ' . ( $field['layout'] == 'horizontal' ? 'acf-hl' : 'acf-bl' );
			$ul['class'] .= ' ' . $field['class'];

			// checkbox saves an array
			$field['name'] .= '[]';

			// choices
			if ( ! empty( $field['choices'] ) ) {

				// choices
				$li .= $this->render_field_choices( $field );

				// toggle
				if ( $field['toggle'] ) {
					$li = $this->render_field_toggle( $field ) . $li;
				}
			}

			// custom
			if ( $field['allow_custom'] ) {
				$li .= $this->render_field_custom( $field );
			}

			// return
			echo '<ul ' . acf_esc_attr( $ul ) . '>' . "\n" . $li . '</ul>' . "\n";

		}


		/*
		*  render_field_choices
		*
		*  description
		*
		*  @type    function
		*  @date    15/7/17
		*  @since   5.6.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function render_field_choices( $field ) {

			// walk
			return $this->walk( $field['choices'], $field );

		}

		/**
		 * Validates values for the checkbox field
		 *
		 * @date  09/12/2022
		 * @since 6.0.0
		 *
		 * @param bool   $valid  If the field is valid.
		 * @param mixed  $value  The value to validate.
		 * @param array  $field  The main field array.
		 * @param string $input  The input element's name attribute.
		 *
		 * @return bool
		 */
		function validate_value( $valid, $value, $field, $input ) {
			if ( ! is_array( $value ) || empty( $field['allow_custom'] ) ) {
				return $valid;
			}

			foreach ( $value as $value ) {
				if ( empty( $value ) && $value !== '0' ) {
					return __( 'Checkbox custom values cannot be empty. Uncheck any empty values.', 'acf' );
				}
			}

			return $valid;
		}

		/*
		*  render_field_toggle
		*
		*  description
		*
		*  @type    function
		*  @date    15/7/17
		*  @since   5.6.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function render_field_toggle( $field ) {

			// vars
			$atts = array(
				'type'  => 'checkbox',
				'class' => 'acf-checkbox-toggle',
				'label' => __( 'Toggle All', 'acf' ),
			);

			// custom label
			if ( is_string( $field['toggle'] ) ) {
				$atts['label'] = $field['toggle'];
			}

			// checked
			if ( $this->_all_checked ) {
				$atts['checked'] = 'checked';
			}

			// return
			return '<li>' . acf_get_checkbox_input( $atts ) . '</li>' . "\n";

		}


		/*
		*  render_field_custom
		*
		*  description
		*
		*  @type    function
		*  @date    15/7/17
		*  @since   5.6.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function render_field_custom( $field ) {

			// vars
			$html = '';

			// loop
			foreach ( $field['value'] as $value ) {

				// ignore if already eixsts
				if ( isset( $field['choices'][ $value ] ) ) {
					continue;
				}

				// vars
				$esc_value  = esc_attr( $value );
				$text_input = array(
					'name'  => $field['name'],
					'value' => $value,
				);

				// bail early if choice already exists
				if ( in_array( $esc_value, $this->_values ) ) {
					continue;
				}

				// append
				$html .= '<li><input class="acf-checkbox-custom" type="checkbox" checked="checked" />' . acf_get_text_input( $text_input ) . '</li>' . "\n";

			}

			// append button
			$html .= '<li><a href="#" class="button acf-add-checkbox">' . esc_attr( $field['custom_choice_button_text'] ) . '</a></li>' . "\n";

			// return
			return $html;

		}


		function walk( $choices = array(), $args = array(), $depth = 0 ) {

			// bail early if no choices
			if ( empty( $choices ) ) {
				return '';
			}

			// defaults
			$args = wp_parse_args(
				$args,
				array(
					'id'       => '',
					'type'     => 'checkbox',
					'name'     => '',
					'value'    => array(),
					'disabled' => array(),
				)
			);

			// vars
			$html = '';

			// sanitize values for 'selected' matching
			if ( $depth == 0 ) {
				$args['value']    = array_map( 'esc_attr', $args['value'] );
				$args['disabled'] = array_map( 'esc_attr', $args['disabled'] );
			}

			// loop
			foreach ( $choices as $value => $label ) {

				// open
				$html .= '<li>';

				// optgroup
				if ( is_array( $label ) ) {

					$html .= '<ul>' . "\n";
					$html .= $this->walk( $label, $args, $depth + 1 );
					$html .= '</ul>';

					// option
				} else {

					// vars
					$esc_value = esc_attr( $value );
					$atts      = array(
						'id'    => $args['id'] . '-' . str_replace( ' ', '-', $value ),
						'type'  => $args['type'],
						'name'  => $args['name'],
						'value' => $value,
						'label' => $label,
					);

					// selected
					if ( in_array( $esc_value, $args['value'] ) ) {
						$atts['checked'] = 'checked';
					} else {
						$this->_all_checked = false;
					}

					// disabled
					if ( in_array( $esc_value, $args['disabled'] ) ) {
						$atts['disabled'] = 'disabled';
					}

					// store value added
					$this->_values[] = $esc_value;

					// append
					$html .= acf_get_checkbox_input( $atts );

				}

				// close
				$html .= '</li>' . "\n";

			}

			// return
			return $html;

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
			// Encode choices (convert from array).
			$field['choices']       = acf_encode_choices( $field['choices'] );
			$field['default_value'] = acf_encode_choices( $field['default_value'], false );

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
					'instructions' => __( 'Enter each default value on a new line', 'acf' ),
					'type'         => 'textarea',
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
					'label'        => __( 'Allow Custom Values', 'acf' ),
					'name'         => 'allow_custom',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => __( "Allow 'custom' values to be added", 'acf' ),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Save Custom Values', 'acf' ),
					'name'         => 'save_custom',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => __( "Save 'custom' values to the field's choices", 'acf' ),
					'conditions'   => array(
						'field'    => 'allow_custom',
						'operator' => '==',
						'value'    => 1,
					),
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
						'vertical'   => __( 'Vertical', 'acf' ),
						'horizontal' => __( 'Horizontal', 'acf' ),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Add Toggle All', 'acf' ),
					'instructions' => __( 'Prepend an extra checkbox to toggle all choices', 'acf' ),
					'name'         => 'toggle',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);
		}

		/*
		*  update_field()
		*
		*  This filter is appied to the $field before it is saved to the database
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field - the field array holding all the field options
		*  @param   $post_id - the field group ID (post_type = acf)
		*
		*  @return  $field - the modified field
		*/

		function update_field( $field ) {

			// Decode choices (convert to array).
			$field['choices']       = acf_decode_choices( $field['choices'] );
			$field['default_value'] = acf_decode_choices( $field['default_value'], true );
			return $field;
		}


		/*
		*  update_value()
		*
		*  This filter is appied to the $value before it is updated in the db
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value - the value which will be saved in the database
		*  @param   $post_id - the $post_id of which the value will be saved
		*  @param   $field - the field array holding all the field options
		*
		*  @return  $value - the modified value
		*/

		function update_value( $value, $post_id, $field ) {

			// bail early if is empty
			if ( empty( $value ) ) {
				return $value;
			}

			// select -> update_value()
			$value = acf_get_field_type( 'select' )->update_value( $value, $post_id, $field );

			// save_other_choice
			if ( $field['save_custom'] ) {

				// get raw $field (may have been changed via repeater field)
				// if field is local, it won't have an ID
				$selector = $field['ID'] ? $field['ID'] : $field['key'];
				$field    = acf_get_field( $selector );
				if ( ! $field ) {
					return false;
				}

				// bail early if no ID (JSON only)
				if ( ! $field['ID'] ) {
					return $value;
				}

				// loop
				foreach ( $value as $v ) {

					// ignore if already eixsts
					if ( isset( $field['choices'][ $v ] ) ) {
						continue;
					}

					// unslash (fixes serialize single quote issue)
					$v = wp_unslash( $v );

					// sanitize (remove tags)
					$v = sanitize_text_field( $v );

					// append
					$field['choices'][ $v ] = $v;

				}

				// save
				acf_update_field( $field );

			}

			// return
			return $value;

		}


		/*
		*  translate_field
		*
		*  This function will translate field settings
		*
		*  @type    function
		*  @date    8/03/2016
		*  @since   5.3.2
		*
		*  @param   $field (array)
		*  @return  $field
		*/

		function translate_field( $field ) {

			return acf_get_field_type( 'select' )->translate_field( $field );

		}


		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value (mixed) the value which was loaded from the database
		*  @param   $post_id (mixed) the $post_id from which the value was loaded
		*  @param   $field (array) the field array holding all the field options
		*
		*  @return  $value (mixed) the modified value
		*/

		function format_value( $value, $post_id, $field ) {

			// Bail early if is empty.
			if ( acf_is_empty( $value ) ) {
				return array();
			}

			// Always convert to array of items.
			$value = acf_array( $value );

			// Return.
			return acf_get_field_type( 'select' )->format_value( $value, $post_id, $field );
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'integer', 'string', 'array', 'null' ),
				'required' => isset( $field['required'] ) && $field['required'],
				'items'    => array(
					'type' => array( 'string', 'integer' ),
				),
			);

			if ( isset( $field['default_value'] ) && '' !== $field['default_value'] ) {
				$schema['default'] = $field['default_value'];
			}

			// If we allow custom values, nothing else to do here.
			if ( ! empty( $field['allow_custom'] ) ) {
				return $schema;
			}

			/**
			 * If a user has defined keys for the checkboxes,
			 * we should use the keys for the available options to POST to,
			 * since they are what is displayed in GET requests.
			 */
			$checkbox_keys = array_map(
				'strval',
				array_diff(
					array_keys( $field['choices'] ),
					array_values( $field['choices'] )
				)
			);

			// Support users passing integers for the keys as well.
			$checkbox_keys = array_merge( $checkbox_keys, array_map( 'intval', array_keys( $field['choices'] ) ) );

			$schema['items']['enum'] = empty( $checkbox_keys ) ? $field['choices'] : $checkbox_keys;

			return $schema;
		}

	}


	// initialize
	acf_register_field_type( 'acf_field_checkbox' );

endif; // class_exists check


