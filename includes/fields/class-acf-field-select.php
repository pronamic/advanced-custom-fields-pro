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

if ( ! class_exists( 'acf_field_select' ) ) :

	class acf_field_select extends acf_field {

		/**
		 * Sets up the field type data.
		 *
		 * @since   5.0.0
		 *
		 * @return void
		 */
		public function initialize() {
			$this->name          = 'select';
			$this->label         = _x( 'Select', 'noun', 'acf' );
			$this->category      = 'choice';
			$this->description   = __( 'A dropdown list with a selection of choices that you specify.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-select.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/select/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'multiple'       => 0,
				'allow_null'     => 0,
				'choices'        => array(),
				'default_value'  => '',
				'ui'             => 0,
				'ajax'           => 0,
				'placeholder'    => '',
				'return_format'  => 'value',
				'create_options' => 0,
				'save_options'   => 0,
			);

			add_action( 'wp_ajax_acf/fields/select/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/select/query', array( $this, 'ajax_query' ) );
		}

		/**
		 * Enqueues admin scripts for the Select field.
		 *
		 * @since 5.3.2
		 *
		 * @return void
		 */
		public function input_admin_enqueue_scripts() {
			// Bail early if not enqueuing select2.
			if ( ! acf_get_setting( 'enqueue_select2' ) ) {
				return;
			}

			global $wp_scripts;

			$min   = defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ? '' : '.min';
			$major = acf_get_setting( 'select2_version' );

			// attempt to find 3rd party Select2 version
			// - avoid including v3 CSS when v4 JS is already enqueued.
			if ( isset( $wp_scripts->registered['select2'] ) ) {
				$major = (int) $wp_scripts->registered['select2']->ver;
			}

			if ( $major === 3 ) {
				// Use v3 if necessary.
				$version = '3.5.2';
				$script  = acf_get_url( "assets/inc/select2/3/select2{$min}.js" );
				$style   = acf_get_url( 'assets/inc/select2/3/select2.css' );
			} else {
				// Default to v4.
				$version = '4.0.13';
				$script  = acf_get_url( "assets/inc/select2/4/select2.full{$min}.js" );
				$style   = acf_get_url( "assets/inc/select2/4/select2{$min}.css" );
			}

			wp_enqueue_script( 'select2', $script, array( 'jquery' ), $version );
			wp_enqueue_style( 'select2', $style, '', $version );

			acf_localize_data(
				array(
					'select2L10n' => array(
						'matches_1'            => _x( 'One result is available, press enter to select it.', 'Select2 JS matches_1', 'acf' ),
						/* translators: %d - number of results available in select field */
						'matches_n'            => _x( '%d results are available, use up and down arrow keys to navigate.', 'Select2 JS matches_n', 'acf' ),
						'matches_0'            => _x( 'No matches found', 'Select2 JS matches_0', 'acf' ),
						'input_too_short_1'    => _x( 'Please enter 1 or more characters', 'Select2 JS input_too_short_1', 'acf' ),
						/* translators: %d - number of characters to enter into select field input */
						'input_too_short_n'    => _x( 'Please enter %d or more characters', 'Select2 JS input_too_short_n', 'acf' ),
						'input_too_long_1'     => _x( 'Please delete 1 character', 'Select2 JS input_too_long_1', 'acf' ),
						/* translators: %d - number of characters that should be removed from select field */
						'input_too_long_n'     => _x( 'Please delete %d characters', 'Select2 JS input_too_long_n', 'acf' ),
						'selection_too_long_1' => _x( 'You can only select 1 item', 'Select2 JS selection_too_long_1', 'acf' ),
						/* translators: %d - maximum number of items that can be selected in the select field */
						'selection_too_long_n' => _x( 'You can only select %d items', 'Select2 JS selection_too_long_n', 'acf' ),
						'load_more'            => _x( 'Loading more results&hellip;', 'Select2 JS load_more', 'acf' ),
						'searching'            => _x( 'Searching&hellip;', 'Select2 JS searching', 'acf' ),
						'load_fail'            => _x( 'Loading failed', 'Select2 JS load_fail', 'acf' ),
					),
				)
			);
		}

		/**
		 * AJAX handler for getting Select field choices.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function ajax_query() {
			$nonce = acf_request_arg( 'nonce', '' );
			$key   = acf_request_arg( 'field_key', '' );

			$is_field_key = acf_is_field_key( $key );

			// Back-compat for field settings.
			if ( ! $is_field_key ) {
				if ( ! acf_current_user_can_admin() ) {
					die();
				}

				$nonce = '';
				$key   = '';
			}

			if ( ! acf_verify_ajax( $nonce, $key, $is_field_key ) ) {
				die();
			}

			acf_send_ajax_results( $this->get_ajax_query( $_POST ) );
		}

		/**
		 * This function will return an array of data formatted for use in a select2 AJAX response
		 *
		 * @since   5.0.9
		 *
		 * @param array $options An array of options.
		 * @return array A select2 compatible array of options.
		 */
		public function get_ajax_query( $options = array() ) {
			$options = acf_parse_args(
				$options,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'paged'     => 1,
				)
			);

			$shortcut = apply_filters( 'acf/fields/select/query', array(), $options );
			$shortcut = apply_filters( 'acf/fields/select/query/key=' . $options['field_key'], $shortcut, $options );
			if ( ! empty( $shortcut ) ) {
				return $shortcut;
			}

			// load field.
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}

			// get choices.
			$choices = acf_get_array( $field['choices'] );
			if ( empty( $field['choices'] ) ) {
				return false;
			}

			$results = array();
			$s       = null;

			// search.
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = strval( $options['s'] );
				$s = wp_unslash( $s );
			}

			foreach ( $field['choices'] as $k => $v ) {

				// ensure $v is a string.
				$v = strval( $v );

				// if searching, but doesn't exist.
				if ( is_string( $s ) && stripos( $v, $s ) === false ) {
					continue;
				}

				// append results.
				$results[] = array(
					'id'   => $k,
					'text' => $v,
				);
			}

			$response = array(
				'results' => $results,
			);

			return $response;
		}


		/**
		 * Creates the HTML interface for the field.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 * @return void
		 */
		public function render_field( $field ) {
			$value   = acf_get_array( $field['value'] );
			$choices = acf_get_array( $field['choices'] );

			if ( empty( $field['placeholder'] ) ) {
				$field['placeholder'] = _x( 'Select', 'verb', 'acf' );
			}

			// Add empty value (allows '' to be selected).
			if ( empty( $value ) ) {
				$value = array( '' );
			}

			// prepend empty choice
			// - only for single selects
			// - have tried array_merge but this causes keys to re-index if is numeric (post ID's)
			if ( $field['allow_null'] && ! $field['multiple'] ) {
				$choices = array( '' => "- {$field['placeholder']} -" ) + $choices;
			}

			// clean up choices if using ajax
			if ( $field['ui'] && $field['ajax'] ) {
				$minimal = array();
				foreach ( $value as $key ) {
					if ( isset( $choices[ $key ] ) ) {
						$minimal[ $key ] = $choices[ $key ];
					}
				}
				$choices = $minimal;
			}

			$select = array(
				'id'               => $field['id'],
				'class'            => $field['class'],
				'name'             => $field['name'],
				'data-ui'          => $field['ui'],
				'data-ajax'        => $field['ajax'],
				'data-multiple'    => $field['multiple'],
				'data-placeholder' => $field['placeholder'],
				'data-allow_null'  => $field['allow_null'],
			);

			if ( ! empty( $field['aria-label'] ) ) {
				$select['aria-label'] = $field['aria-label'];
			}

			if ( $field['multiple'] ) {
				$select['multiple'] = 'multiple';
				$select['size']     = 5;
				$select['name']    .= '[]';

				// Reduce size to single line if UI.
				if ( $field['ui'] ) {
					$select['size'] = 1;
				}
			}

			if ( ! empty( $field['create_options'] ) && $field['ui'] ) {
				$select['data-create_options'] = true;
			}

			// special atts
			if ( ! empty( $field['readonly'] ) ) {
				$select['readonly'] = 'readonly';
			}
			if ( ! empty( $field['disabled'] ) ) {
				$select['disabled'] = 'disabled';
			}
			if ( ! empty( $field['ajax_action'] ) ) {
				$select['data-ajax_action'] = $field['ajax_action'];
			}
			if ( ! empty( $field['nonce'] ) ) {
				$select['data-nonce'] = $field['nonce'];
			}
			if ( $field['ajax'] && empty( $field['nonce'] ) && acf_is_field_key( $field['key'] ) ) {
				$select['data-nonce'] = wp_create_nonce( 'acf_field_' . $this->name . '_' . $field['key'] );
			}
			if ( ! empty( $field['hide_search'] ) ) {
				$select['data-minimum-results-for-search'] = '-1';
			}

			// Hidden input is needed to allow validation to see <select> element with no selected value.
			if ( $field['multiple'] || $field['ui'] ) {
				acf_hidden_input(
					array(
						'id'   => $field['id'] . '-input',
						'name' => $field['name'],
					)
				);
			}

			$select['value']   = $value;
			$select['choices'] = $choices;

			if ( ! empty( $field['create_options'] ) && $field['ui'] && is_array( $field['value'] ) ) {
				foreach ( $field['value'] as $value ) {
					// Already exists in choices.
					if ( isset( $field['choices'][ $value ] ) ) {
						continue;
					}

					$option = esc_attr( $value );

					$select['choices'][ $option ] = $option;
				}
			}

			acf_select_input( $select );
		}

		/**
		 * Renders the field settings used in the "General" tab.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 * @return void
		 */
		public function render_field_settings( $field ) {

			// encode choices (convert from array)
			$field['choices']       = acf_encode_choices( $field['choices'] );
			$field['default_value'] = acf_encode_choices( $field['default_value'], false );

			// choices
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Choices', 'acf' ),
					'instructions' => __( 'Enter each choice on a new line.', 'acf' ) . '<br />' . __( 'For more control, you may specify both a value and label like this:', 'acf' ) . '<br /><span class="acf-field-setting-example">' . __( 'red : Red', 'acf' ) . '</span>',
					'name'         => 'choices',
					'type'         => 'textarea',
				)
			);

			// default_value
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf' ),
					'instructions' => __( 'Enter each default value on a new line', 'acf' ),
					'name'         => 'default_value',
					'type'         => 'textarea',
				)
			);

			// return_format
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf' ),
					'instructions' => __( 'Specify the value returned', 'acf' ),
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

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Select Multiple', 'acf' ),
					'instructions' => 'Allow content editors to select multiple values',
					'name'         => 'multiple',
					'type'         => 'true_false',
					'ui'           => 1,
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
		public function render_field_validation_settings( $field ) {
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
		public function render_field_presentation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Stylized UI', 'acf' ),
					'instructions' => __( 'Use a stylized checkbox using select2', 'acf' ),
					'name'         => 'ui',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Use AJAX to lazy load choices?', 'acf' ),
					'instructions' => '',
					'name'         => 'ajax',
					'type'         => 'true_false',
					'ui'           => 1,
					'conditions'   => array(
						'field'    => 'ui',
						'operator' => '==',
						'value'    => 1,
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Create Options', 'acf' ),
					'instructions' => __( 'Allow content editors to create new options by typing in the Select input. Multiple options can be created from a comma separated string.', 'acf' ),
					'name'         => 'create_options',
					'type'         => 'true_false',
					'ui'           => 1,
					'conditions'   => array(
						array(
							'field'    => 'ui',
							'operator' => '==',
							'value'    => 1,
						),
						array(
							'field'    => 'multiple',
							'operator' => '==',
							'value'    => 1,
						),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Save Options', 'acf' ),
					'instructions' => __( 'Save created options back to the "Choices" setting in the field definition.', 'acf' ),
					'name'         => 'save_options',
					'type'         => 'true_false',
					'ui'           => 1,
					'conditions'   => array(
						array(
							'field'    => 'ui',
							'operator' => '==',
							'value'    => 1,
						),
						array(
							'field'    => 'multiple',
							'operator' => '==',
							'value'    => 1,
						),
						array(
							'field'    => 'create_options',
							'operator' => '==',
							'value'    => 1,
						),
					),
				)
			);
		}

		/**
		 * Filters the $value after it is loaded from the db.
		 *
		 * @since   3.6
		 *
		 * @param mixed          $value   The value found in the database.
		 * @param integer|string $post_id The post_id from which the value was loaded.
		 * @param array          $field   The field array holding all the field options.
		 * @return mixed
		 */
		public function load_value( $value, $post_id, $field ) {
			// Return an array when field is set for multiple.
			if ( $field['multiple'] ) {
				if ( acf_is_empty( $value ) ) {
					return array();
				}
				return acf_array( $value );
			}

			// Otherwise, return a single value.
			return acf_unarray( $value );
		}

		/**
		 * Filters the $field before it is saved to the database.
		 *
		 * @since 3.6
		 *
		 * @param array $field The field array holding all the field options.
		 * @return array
		 */
		public function update_field( $field ) {
			// Decode choices (convert to array).
			$field['choices']       = acf_decode_choices( $field['choices'] );
			$field['default_value'] = acf_decode_choices( $field['default_value'], true );

			// Convert back to string for single selects.
			if ( ! $field['multiple'] ) {
				$field['default_value'] = acf_unarray( $field['default_value'] );
			}

			return $field;
		}

		/**
		 * Filters the $value before it is updated in the db.
		 *
		 * @since 3.6
		 *
		 * @param mixed          $value   The value which will be saved in the database.
		 * @param integer|string $post_id The post_id of which the value will be saved.
		 * @param array          $field   The field array holding all the field options.
		 *
		 * @return mixed
		 */
		public function update_value( $value, $post_id, $field ) {
			// Bail early if no value.
			if ( empty( $value ) ) {
				return $value;
			}

			// Format array of values.
			// - Parse each value as string for SQL LIKE queries.
			if ( is_array( $value ) ) {
				$value = array_map( 'strval', $value );
			}

			// Save custom options back to the field definition if configured.
			if ( ! empty( $field['save_options'] ) && is_array( $value ) ) {
				// Get the raw field, using the ID if present or the key otherwise (i.e. when using JSON).
				$selector = $field['ID'] ? $field['ID'] : $field['key'];
				$field    = acf_get_field( $selector );

				// Bail if we don't have a valid field or field ID (JSON only).
				if ( empty( $field['ID'] ) ) {
					return $value;
				}

				foreach ( $value as $v ) {
					// Ignore if the option already exists.
					if ( isset( $field['choices'][ $v ] ) ) {
						continue;
					}

					// Unslash (fixes serialize single quote issue) and sanitize.
					$v = wp_unslash( $v );
					$v = sanitize_text_field( $v );

					// Append to the field choices.
					$field['choices'][ $v ] = $v;
				}

				acf_update_field( $field );
			}

			return $value;
		}

		/**
		 * Translates the field settings.
		 *
		 * @since 5.3.2
		 *
		 * @param array $field The main field array.
		 * @return array
		 */
		public function translate_field( $field ) {
			$field['choices'] = acf_translate( $field['choices'] );
			return $field;
		}


		/**
		 * Filters the $value after it is loaded from the db, and before it is returned to the template.
		 *
		 * @since   3.6
		 *
		 * @param mixed          $value   The value which was loaded from the database.
		 * @param integer|string $post_id The post_id from which the value was loaded.
		 * @param array          $field   The field array holding all the field options.
		 *
		 * @return mixed
		 */
		public function format_value( $value, $post_id, $field ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $i => $val ) {
					$value[ $i ] = $this->format_value_single( $val, $post_id, $field );
				}
			} else {
				$value = $this->format_value_single( $value, $post_id, $field );
			}

			return $value;
		}

		/**
		 * Formats the value when the select is not a multi-select.
		 *
		 * @since 3.6
		 *
		 * @param mixed          $value   The value to format.
		 * @param integer|string $post_id The post_id from which the value was loaded.
		 * @param array          $field   The field array holding all the field options.
		 * @return mixed
		 */
		public function format_value_single( $value, $post_id, $field ) {
			// Bail early if is empty.
			if ( acf_is_empty( $value ) ) {
				return $value;
			}

			$label = acf_maybe_get( $field['choices'], $value, $value );

			if ( $field['return_format'] === 'label' ) {
				$value = $label;
			} elseif ( $field['return_format'] === 'array' ) {
				$value = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $value;
		}

		/**
		 * Validates select fields updated via the REST API.
		 *
		 * @param  boolean $valid The current validity booleean
		 * @param  integer $value The value of the field
		 * @param  array   $field The field array
		 * @return boolean|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			// rest_validate_request_arg() handles the other types, we just worry about strings.
			if ( is_null( $value ) || is_array( $value ) ) {
				return $valid;
			}

			$option_keys = array_diff(
				array_keys( $field['choices'] ),
				array_values( $field['choices'] )
			);

			$allowed = empty( $option_keys ) ? $field['choices'] : $option_keys;

			if ( ! in_array( $value, $allowed ) ) {
				$param = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
				$data  = array(
					'param' => $param,
					'value' => $value,
				);
				$error = sprintf(
					__( '%1$s is not one of %2$s', 'acf' ),
					$param,
					implode( ', ', $allowed )
				);

				return new WP_Error( 'rest_invalid_param', $error, $data );
			}

			return $valid;
		}

		/**
		 * Formats the choices available for the REST API.
		 *
		 * @since 6.2
		 *
		 * @param array $choices The choices for the field.
		 * @return array
		 */
		public function format_rest_choices( $choices ) {
			$keys        = array_keys( $choices );
			$values      = array_values( $choices );
			$int_choices = array();

			if ( array_diff( $keys, $values ) ) {
				// User has specified custom keys.
				$choices = $keys;
			} else {
				// Default keys, same as value.
				$choices = $values;
			}

			// Assume everything is a string by default.
			$choices = array_map( 'strval', $choices );

			// Also allow integers if is_numeric().
			foreach ( $choices as $choice ) {
				if ( is_numeric( $choice ) ) {
					$int_choices[] = (int) $choice;
				}
			}

			return array_merge( $choices, $int_choices );
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field The main field array.
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'string', 'array', 'int', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'type' => array( 'string', 'int' ),
					'enum' => $this->format_rest_choices( $field['choices'] ),
				),
			);

			if ( empty( $field['allow_null'] ) ) {
				$schema['minItems'] = 1;
			}

			if ( empty( $field['multiple'] ) ) {
				$schema['maxItems'] = 1;
			}

			if ( isset( $field['default_value'] ) && '' !== $field['default_value'] ) {
				$schema['default'] = $field['default_value'];
			}

			return $schema;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_select' );
endif; // class_exists check
