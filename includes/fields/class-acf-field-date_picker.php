<?php

if ( ! class_exists( 'acf_field_date_picker' ) ) :

	class acf_field_date_picker extends acf_field {


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
			$this->name          = 'date_picker';
			$this->label         = __( 'Date Picker', 'acf' );
			$this->category      = 'advanced';
			$this->description   = __( 'An interactive UI for picking a date. The date return format can be customized using the field settings.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-date-picker.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/date-picker/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'display_format' => 'd/m/Y',
				'return_format'  => 'd/m/Y',
				'first_day'      => 1,
			);
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
			if ( ! acf_get_setting( 'enqueue_datepicker' ) ) {
				return;
			}

			// localize
			global $wp_locale;
			acf_localize_data(
				array(
					'datePickerL10n' => array(
						'closeText'       => _x( 'Done', 'Date Picker JS closeText', 'acf' ),
						'currentText'     => _x( 'Today', 'Date Picker JS currentText', 'acf' ),
						'nextText'        => _x( 'Next', 'Date Picker JS nextText', 'acf' ),
						'prevText'        => _x( 'Prev', 'Date Picker JS prevText', 'acf' ),
						'weekHeader'      => _x( 'Wk', 'Date Picker JS weekHeader', 'acf' ),
						'monthNames'      => array_values( $wp_locale->month ),
						'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
						'dayNames'        => array_values( $wp_locale->weekday ),
						'dayNamesMin'     => array_values( $wp_locale->weekday_initial ),
						'dayNamesShort'   => array_values( $wp_locale->weekday_abbrev ),
					),
				)
			);

			// script
			wp_enqueue_script( 'jquery-ui-datepicker' );

			// style
			wp_enqueue_style( 'acf-datepicker', acf_get_url( 'assets/inc/datepicker/jquery-ui.min.css' ), array(), '1.11.4' );
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
			$hidden_value  = '';
			$display_value = '';

			// format value
			if ( $field['value'] ) {
				$hidden_value  = acf_format_date( $field['value'], 'Ymd' );
				$display_value = acf_format_date( $field['value'], $field['display_format'] );
			}

			// elements
			$div          = array(
				'class'            => 'acf-date-picker acf-input-wrap',
				'data-date_format' => acf_convert_date_to_js( $field['display_format'] ),
				'data-first_day'   => $field['first_day'],
			);
			$hidden_input = array(
				'id'    => $field['id'],
				'name'  => $field['name'],
				'value' => $hidden_value,
			);
			$text_input   = array(
				'class' => $field['class'] . ' input',
				'value' => $display_value,
			);

			// special attributes
			foreach ( array( 'readonly', 'disabled' ) as $k ) {
				if ( ! empty( $field[ $k ] ) ) {
					$hidden_input[ $k ] = $k;
					$text_input[ $k ]   = $k;
				}
			}

			// save_format - compatibility with ACF < 5.0.0
			if ( ! empty( $field['save_format'] ) ) {

				// add custom JS save format
				$div['data-save_format'] = $field['save_format'];

				// revert hidden input value to raw DB value
				$hidden_input['value'] = $field['value'];

				// remove formatted value (will do this via JS)
				$text_input['value'] = '';
			}

			// html
			?>
		<div <?php echo acf_esc_attrs( $div ); ?>>
			<?php acf_hidden_input( $hidden_input ); ?>
			<?php acf_text_input( $text_input ); ?>
		</div>
			<?php
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
			global $wp_locale;

			$d_m_Y = date_i18n( 'd/m/Y' );
			$m_d_Y = date_i18n( 'm/d/Y' );
			$F_j_Y = date_i18n( 'F j, Y' );
			$Ymd   = date_i18n( 'Ymd' );

			echo '<div class="acf-field-settings-split">';

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Display Format', 'acf' ),
					'hint'         => __( 'The format displayed when editing a post', 'acf' ),
					'type'         => 'radio',
					'name'         => 'display_format',
					'other_choice' => 1,
					'choices'      => array(
						'd/m/Y'  => '<span>' . $d_m_Y . '</span><code>d/m/Y</code>',
						'm/d/Y'  => '<span>' . $m_d_Y . '</span><code>m/d/Y</code>',
						'F j, Y' => '<span>' . $F_j_Y . '</span><code>F j, Y</code>',
						'other'  => '<span>' . __( 'Custom:', 'acf' ) . '</span>',
					),
				)
			);

			// save_format - compatibility with ACF < 5.0.0
			if ( ! empty( $field['save_format'] ) ) {
				acf_render_field_setting(
					$field,
					array(
						'label' => __( 'Save Format', 'acf' ),
						'hint'  => __( 'The format used when saving a value', 'acf' ),
						'type'  => 'text',
						'name'  => 'save_format',
					// 'readonly'        => 1 // this setting was not readonly in v4
					)
				);
			} else {
				acf_render_field_setting(
					$field,
					array(
						'label'        => __( 'Return Format', 'acf' ),
						'hint'         => __( 'The format returned via template functions', 'acf' ),
						'type'         => 'radio',
						'name'         => 'return_format',
						'other_choice' => 1,
						'choices'      => array(
							'd/m/Y'  => '<span>' . $d_m_Y . '</span><code>d/m/Y</code>',
							'm/d/Y'  => '<span>' . $m_d_Y . '</span><code>m/d/Y</code>',
							'F j, Y' => '<span>' . $F_j_Y . '</span><code>F j, Y</code>',
							'Ymd'    => '<span>' . $Ymd . '</span><code>Ymd</code>',
							'other'  => '<span>' . __( 'Custom:', 'acf' ) . '</span>',
						),
					)
				);
			}

			echo '</div>';

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Week Starts On', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'first_day',
					'choices'      => array_values( $wp_locale->weekday ),
				)
			);
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

			// save_format - compatibility with ACF < 5.0.0
			if ( ! empty( $field['save_format'] ) ) {
				return $value;
			}

			// return
			return acf_format_date( $value, $field['return_format'] );
		}


		/**
		 *  This filter is applied to the $field after it is loaded from the database
		 *  and ensures the return and display values are set.
		 *
		 *  @type    filter
		 *  @since   5.11.0
		 *  @date    28/09/21
		 *
		 *  @param array $field The field array holding all the field options.
		 *
		 *  @return array
		 */
		function load_field( $field ) {
			if ( empty( $field['display_format'] ) ) {
				$field['display_format'] = $this->defaults['display_format'];
			}

			if ( empty( $field['return_format'] ) ) {
				$field['return_format'] = $this->defaults['return_format'];
			}

			return $field;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			return array(
				'type'        => array( 'string', 'null' ),
				'description' => 'A `Ymd` formatted date string.',
				'required'    => ! empty( $field['required'] ),
			);
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
			if ( ! $value ) {
				return null;
			}

			return (string) $value;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_date_picker' );
endif; // class_exists check

?>
