<?php

if ( ! class_exists( 'acf_field_message' ) ) :

	class acf_field_message extends acf_field {

		public $show_in_rest = false;

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
			$this->name          = 'message';
			$this->label         = __( 'Message', 'acf' );
			$this->category      = 'layout';
			$this->description   = __( 'Used to display a message to editors alongside other fields. Useful for providing additional context or instructions around your fields.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-message.png';
			$this->supports      = array( 'required' => false );
			$this->defaults      = array(
				'message'   => '',
				'esc_html'  => 0,
				'new_lines' => 'wpautop',
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
			$m = $field['message'];

			// wptexturize (improves "quotes")
			$m = wptexturize( $m );

			// esc_html
			if ( $field['esc_html'] ) {
				$m = esc_html( $m );
			}

			// new lines
			if ( $field['new_lines'] == 'wpautop' ) {
				$m = wpautop( $m );
			} elseif ( $field['new_lines'] == 'br' ) {
				$m = nl2br( $m );
			}

			// return
			echo acf_esc_html( $m );
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
					'label'        => __( 'Message', 'acf' ),
					'instructions' => '',
					'type'         => 'textarea',
					'name'         => 'message',
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

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Escape HTML', 'acf' ),
					'instructions' => __( 'Allow HTML markup to display as visible text instead of rendering', 'acf' ),
					'name'         => 'esc_html',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);
		}

		/**
		 * This function will translate field settings
		 *
		 * @type    function
		 * @date    8/03/2016
		 * @since   5.3.2
		 *
		 * @param   $field (array)
		 * @return  $field
		 */
		function translate_field( $field ) {

			// translate
			$field['message'] = acf_translate( $field['message'] );

			// return
			return $field;
		}


		/**
		 * This filter is appied to the $field after it is loaded from the database
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $field - the field array holding all the field options
		 *
		 * @return  $field - the field array holding all the field options
		 */
		function load_field( $field ) {

			// remove name to avoid caching issue
			$field['name'] = '';

			// remove instructions
			$field['instructions'] = '';

			// remove required to avoid JS issues
			$field['required'] = 0;

			// set value other than 'null' to avoid ACF loading / caching issue
			$field['value'] = false;

			// return
			return $field;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_message' );
endif; // class_exists check
