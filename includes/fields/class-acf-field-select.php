<?php

if ( ! class_exists( 'acf_field_select' ) ) :

	class acf_field_select extends acf_field {


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
			$this->name          = 'select';
			$this->label         = _x( 'Select', 'noun', 'acf' );
			$this->category      = 'choice';
			$this->description   = __( 'A dropdown list with a selection of choices that you specify.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-select.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/select/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'multiple'      => 0,
				'allow_null'    => 0,
				'choices'       => array(),
				'default_value' => '',
				'ui'            => 0,
				'ajax'          => 0,
				'placeholder'   => '',
				'return_format' => 'value',
			);

			// ajax
			add_action( 'wp_ajax_acf/fields/select/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/select/query', array( $this, 'ajax_query' ) );

		}


		/*
		*  input_admin_enqueue_scripts
		*
		*  description
		*
		*  @type    function
		*  @date    16/12/2015
		*  @since   5.3.2
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function input_admin_enqueue_scripts() {

			// bail early if no enqueue
			if ( ! acf_get_setting( 'enqueue_select2' ) ) {
				return;
			}

			// globals
			global $wp_scripts, $wp_styles;

			// vars
			$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$major   = acf_get_setting( 'select2_version' );
			$version = '';
			$script  = '';
			$style   = '';

			// attempt to find 3rd party Select2 version
			// - avoid including v3 CSS when v4 JS is already enququed
			if ( isset( $wp_scripts->registered['select2'] ) ) {

				$major = (int) $wp_scripts->registered['select2']->ver;

			}

			// v4
			if ( $major == 4 ) {

				$version = '4.0.13';
				$script  = acf_get_url( "assets/inc/select2/4/select2.full{$min}.js" );
				$style   = acf_get_url( "assets/inc/select2/4/select2{$min}.css" );

				// v3
			} else {

				$version = '3.5.2';
				$script  = acf_get_url( "assets/inc/select2/3/select2{$min}.js" );
				$style   = acf_get_url( 'assets/inc/select2/3/select2.css' );

			}

			// enqueue
			wp_enqueue_script( 'select2', $script, array( 'jquery' ), $version );
			wp_enqueue_style( 'select2', $style, '', $version );

			// localize
			acf_localize_data(
				array(
					'select2L10n' => array(
						'matches_1'            => _x( 'One result is available, press enter to select it.', 'Select2 JS matches_1', 'acf' ),
						'matches_n'            => _x( '%d results are available, use up and down arrow keys to navigate.', 'Select2 JS matches_n', 'acf' ),
						'matches_0'            => _x( 'No matches found', 'Select2 JS matches_0', 'acf' ),
						'input_too_short_1'    => _x( 'Please enter 1 or more characters', 'Select2 JS input_too_short_1', 'acf' ),
						'input_too_short_n'    => _x( 'Please enter %d or more characters', 'Select2 JS input_too_short_n', 'acf' ),
						'input_too_long_1'     => _x( 'Please delete 1 character', 'Select2 JS input_too_long_1', 'acf' ),
						'input_too_long_n'     => _x( 'Please delete %d characters', 'Select2 JS input_too_long_n', 'acf' ),
						'selection_too_long_1' => _x( 'You can only select 1 item', 'Select2 JS selection_too_long_1', 'acf' ),
						'selection_too_long_n' => _x( 'You can only select %d items', 'Select2 JS selection_too_long_n', 'acf' ),
						'load_more'            => _x( 'Loading more results&hellip;', 'Select2 JS load_more', 'acf' ),
						'searching'            => _x( 'Searching&hellip;', 'Select2 JS searching', 'acf' ),
						'load_fail'            => _x( 'Loading failed', 'Select2 JS load_fail', 'acf' ),
					),
				)
			);
		}


		/*
		*  ajax_query
		*
		*  description
		*
		*  @type    function
		*  @date    24/10/13
		*  @since   5.0.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function ajax_query() {

			// validate
			if ( ! acf_verify_ajax() ) {
				die();
			}

			// get choices
			$response = $this->get_ajax_query( $_POST );

			// return
			acf_send_ajax_results( $response );

		}


		/*
		*  get_ajax_query
		*
		*  This function will return an array of data formatted for use in a select2 AJAX response
		*
		*  @type    function
		*  @date    15/10/2014
		*  @since   5.0.9
		*
		*  @param   $options (array)
		*  @return  (array)
		*/

		function get_ajax_query( $options = array() ) {

			// defaults
			$options = acf_parse_args(
				$options,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'paged'     => 1,
				)
			);

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}

			// get choices
			$choices = acf_get_array( $field['choices'] );
			if ( empty( $field['choices'] ) ) {
				return false;
			}

			// vars
			$results = array();
			$s       = null;

			// search
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = strval( $options['s'] );
				$s = wp_unslash( $s );

			}

			// loop
			foreach ( $field['choices'] as $k => $v ) {

				// ensure $v is a string
				$v = strval( $v );

				// if searching, but doesn't exist
				if ( is_string( $s ) && stripos( $v, $s ) === false ) {
					continue;
				}

				// append
				$results[] = array(
					'id'   => $k,
					'text' => $v,
				);

			}

			// vars
			$response = array(
				'results' => $results,
			);

			// return
			return $response;

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

			// convert
			$value   = acf_get_array( $field['value'] );
			$choices = acf_get_array( $field['choices'] );

			// placeholder
			if ( empty( $field['placeholder'] ) ) {
				$field['placeholder'] = _x( 'Select', 'verb', 'acf' );
			}

			// add empty value (allows '' to be selected)
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

			// vars
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

			if ( $field['aria-label'] ) {
				$select['aria-label'] = $field['aria-label'];
			}

			// multiple
			if ( $field['multiple'] ) {

				$select['multiple'] = 'multiple';
				$select['size']     = 5;
				$select['name']    .= '[]';

				// Reduce size to single line if UI.
				if ( $field['ui'] ) {
					$select['size'] = 1;
				}
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

			if ( ! empty( $field['hide_search'] ) ) {
				$select['data-minimum-results-for-search'] = '-1';
			}

			// hidden input is needed to allow validation to see <select> element with no selected value
			if ( $field['multiple'] || $field['ui'] ) {
				acf_hidden_input(
					array(
						'id'   => $field['id'] . '-input',
						'name' => $field['name'],
					)
				);
			}

			if ( ! empty( $field['query_nonce'] ) ) {
				$select['data-query-nonce'] = $field['query_nonce'];
			}

			// append
			$select['value']   = $value;
			$select['choices'] = $choices;

			// render
			acf_select_input( $select );

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
		}

		/*
		*  load_value()
		*
		*  This filter is applied to the $value after it is loaded from the db
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value (mixed) the value found in the database
		*  @param   $post_id (mixed) the $post_id from which the value was loaded
		*  @param   $field (array) the field array holding all the field options
		*  @return  $value
		*/
		function load_value( $value, $post_id, $field ) {

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

			// decode choices (convert to array)
			$field['choices']       = acf_decode_choices( $field['choices'] );
			$field['default_value'] = acf_decode_choices( $field['default_value'], true );

			// Convert back to string for single selects.
			if ( ! $field['multiple'] ) {
				$field['default_value'] = acf_unarray( $field['default_value'] );
			}

			// return
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

			// Bail early if no value.
			if ( empty( $value ) ) {
				return $value;
			}

			// Format array of values.
			// - Parse each value as string for SQL LIKE queries.
			if ( is_array( $value ) ) {
				$value = array_map( 'strval', $value );
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

			// translate
			$field['choices'] = acf_translate( $field['choices'] );

			// return
			return $field;

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
			if ( is_array( $value ) ) {
				foreach ( $value as $i => $val ) {
					$value[ $i ] = $this->format_value_single( $val, $post_id, $field );
				}
			} else {
				$value = $this->format_value_single( $value, $post_id, $field );
			}
			return $value;
		}


		function format_value_single( $value, $post_id, $field ) {

			// bail early if is empty
			if ( acf_is_empty( $value ) ) {
				return $value;
			}

			// vars
			$label = acf_maybe_get( $field['choices'], $value, $value );

			// value
			if ( $field['return_format'] == 'value' ) {

				// do nothing

				// label
			} elseif ( $field['return_format'] == 'label' ) {

				$value = $label;

				// array
			} elseif ( $field['return_format'] == 'array' ) {

				$value = array(
					'value' => $value,
					'label' => $label,
				);

			}

			// return
			return $value;

		}

		/**
		 * Validates select fields updated via the REST API.
		 *
		 * @param bool  $valid
		 * @param int   $value
		 * @param array $field
		 *
		 * @return bool|WP_Error
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
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			/**
			 * If a user has defined keys for the select options,
			 * we should use the keys for the available options to POST to,
			 * since they are what is displayed in GET requests.
			 */
			$option_keys = array_diff(
				array_keys( $field['choices'] ),
				array_values( $field['choices'] )
			);

			$schema = array(
				'type'     => array( 'string', 'array', 'int', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'type' => array( 'string', 'int' ),
					'enum' => empty( $option_keys ) ? $field['choices'] : $option_keys,
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


