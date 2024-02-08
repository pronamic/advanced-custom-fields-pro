<?php

if ( ! class_exists( 'acf_field_url' ) ) :

	/**
	 * The URL field class.
	 */
	class acf_field_url extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @since 5.0.0
		 */
		public function initialize() {
			// vars
			$this->name          = 'url';
			$this->label         = __( 'URL', 'acf' );
			$this->description   = __( 'A text input specifically designed for storing web addresses.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-url.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/url/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'default_value' => '',
				'placeholder'   => '',
			);
			$this->supports      = array(
				'escaping_html' => true,
			);
		}


		/**
		 * Create the HTML interface for your field
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field( $field ) {
			$atts  = array();
			$keys  = array( 'type', 'id', 'class', 'name', 'value', 'placeholder', 'pattern' );
			$keys2 = array( 'readonly', 'disabled', 'required' );
			$html  = '';

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

			// render
			$html .= '<div class="acf-input-wrap acf-url">';
			$html .= '<i class="acf-icon -globe -small"></i>' . acf_get_text_input( $atts );
			$html .= '</div>';

			// return
			echo $html;
		}


		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field_settings( $field ) {
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
					'label'        => __( 'Placeholder Text', 'acf' ),
					'instructions' => __( 'Appears within the input', 'acf' ),
					'type'         => 'text',
					'name'         => 'placeholder',
				)
			);
		}


		/**
		 * Validate the fields value is correctly formatted as a URL
		 *
		 * @since   5.0.0
		 *
		 * @param mixed  $valid The current validity of the field value. Boolean true if valid, a validation error message string if not.
		 * @param string $value The value of the field.
		 * @param array  $field Field object array.
		 * @param string $input The form input name for this field.
		 *
		 * @return mixed Boolean true if valid, a validation error message string if not.
		 */
		public function validate_value( $valid, $value, $field, $input ) {

			// bail early if empty
			if ( empty( $value ) ) {
				return $valid;
			}

			if ( strpos( $value, '://' ) !== false ) {

				// url
			} elseif ( strpos( $value, '//' ) === 0 ) {

				// protocol relative url
			} else {
				$valid = __( 'Value must be a valid URL', 'acf' );
			}

			// return
			return $valid;
		}

		/**
		 * This filter is applied to the $value after it is loaded from the db, and before it is returned to the template
		 *
		 * @since 6.2.6
		 *
		 * @param mixed   $value       The value which was loaded from the database.
		 * @param mixed   $post_id     The $post_id from which the value was loaded.
		 * @param array   $field       The field array holding all the field options.
		 * @param boolean $escape_html Should the field return a HTML safe formatted value.
		 *
		 * @return mixed $value The modified value
		 */
		public function format_value( $value, $post_id, $field, $escape_html ) {
			if ( $escape_html ) {
				return esc_url( $value );
			}
			return $value;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field The field object.
		 *
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema           = parent::get_rest_schema( $field );
			$schema['format'] = 'uri';

			return $schema;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_url' );
endif; // class_exists check
