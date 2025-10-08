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

if ( ! class_exists( 'acf_field_color_picker' ) ) :

	class acf_field_color_picker extends acf_field {


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
			$this->name          = 'color_picker';
			$this->label         = __( 'Color Picker', 'acf' );
			$this->category      = 'advanced';
			$this->description   = __( 'An interactive UI for selecting a color, or specifying a Hex value.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-color-picker.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/color-picker/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'default_value'         => '',
				'enable_opacity'        => false,
				'custom_palette_source' => '',
				'palette_colors'        => '',
				'show_color_wheel'      => true,
				'return_format'         => 'string', // 'string'|'array'
			);
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    16/12/2015
		 * @since   5.3.2
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function input_admin_enqueue_scripts() {

			// Register scripts for non-admin.
			// Applies logic from wp_default_scripts() function defined in "wp-includes/script-loader.php".
			if ( ! is_admin() ) {
				$suffix  = defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ? '' : '.min';
				$scripts = wp_scripts();
				$scripts->add( 'iris', '/wp-admin/js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), '1.0.7', 1 );
				$scripts->add( 'wp-color-picker', "/wp-admin/js/color-picker$suffix.js", array( 'iris' ), false, 1 );

				// Handle localisation across multiple WP versions.
				// WP 5.0+
				if ( method_exists( $scripts, 'set_translations' ) ) {
					$scripts->set_translations( 'wp-color-picker' );
					// WP 4.9
				} else {
					$scripts->localize(
						'wp-color-picker',
						'wpColorPickerL10n',
						array(
							'clear'            => __( 'Clear', 'acf' ),
							'clearAriaLabel'   => __( 'Clear color', 'acf' ),
							'defaultString'    => __( 'Default', 'acf' ),
							'defaultAriaLabel' => __( 'Select default color', 'acf' ),
							'pick'             => __( 'Select Color', 'acf' ),
							'defaultLabel'     => __( 'Color value', 'acf' ),
						)
					);
				}
			}

			// Enqueue alpha color picker assets.
			wp_enqueue_script(
				'acf-color-picker-alpha',
				acf_get_url( 'assets/inc/color-picker-alpha/wp-color-picker-alpha.js' ),
				array( 'jquery', 'wp-color-picker' ),
				'3.0.0'
			);

			// Enqueue.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			acf_localize_data(
				array(
					'colorPickerL10n' => array(
						'hex_string'  => __( 'Hex String', 'acf' ),
						'rgba_string' => __( 'RGBA String', 'acf' ),
					),
				)
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
		public function render_field( $field ) {
			$text_input                             = acf_get_sub_array( $field, array( 'id', 'class', 'name', 'value' ) );
			$hidden_input                           = acf_get_sub_array( $field, array( 'name', 'value' ) );
			$text_input['data-alpha-skip-debounce'] = true;

			// Color picker alpha library requires a specific data attribute to exist.
			if ( $field['enable_opacity'] ) {
				$text_input['data-alpha-enabled'] = true;
			}

			// Handle color palette when the theme supports theme.json.
			if ( wp_theme_has_theme_json() ) {
				// If the field was set to use themejson.
				if ( $field['custom_palette_source'] === 'themejson' ) {
					$text_input['data-acf-palette-type'] = 'custom';

					// Get the palette (theme + custom).
					$global_settings = wp_get_global_settings();
					$palette         = $global_settings['color']['palette']['theme'] ?? array();

					// Extract only the color values.
					$color_values = array_map(
						fn( $c ) => $c['color'] ?? null,
						$palette
					);

					// Remove nulls (in case any entries are missing 'color')
					$color_values = array_filter( $color_values );

					$hex_string = implode( ',', $color_values );

					$text_input['data-acf-palette-colors'] = $hex_string;
				} elseif ( $field['custom_palette_source'] === 'custom' && ! empty( $field['palette_colors'] ) ) {
					// If the field was set to use a custom palette.
					$text_input['data-acf-palette-type']   = 'custom';
					$text_input['data-acf-palette-colors'] = $field['palette_colors'];
				} elseif ( $field['custom_palette_source'] === '' && ! empty( $field['palette_colors'] ) ) {
					// This state can happen if they switched from a classic theme to a themejson theme without resaving the field.
					$text_input['data-acf-palette-type']   = 'custom';
					$text_input['data-acf-palette-colors'] = $field['palette_colors'];
				} else {
					// Fallback to use the default color palette for the iris color picker.
					$text_input['data-acf-palette-type'] = 'default';
				}
			} else {
				// Handle color palette for themes that do not support themejson.
				if ( ! empty( $field['palette_colors'] ) ) {
					$text_input['data-acf-palette-type']   = 'custom';
					$text_input['data-acf-palette-colors'] = $field['palette_colors'];
				} else {
					// Fallback to use the default color palette for the iris color picker.
					$text_input['data-acf-palette-type'] = 'default';
				}
			}

			// html
			?>
		<div class="acf-color-picker<?php echo esc_attr( ! $field['show_color_wheel'] ? ' acf-hide-color-picker-color-wheel' : '' ); ?>">
			<?php acf_hidden_input( $hidden_input ); ?>
			<?php acf_text_input( $text_input ); ?>
		</div>
			<?php
		}


		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $field  - an array holding all the field's data
		 */
		function render_field_settings( $field ) {

			// display_format
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf' ),
					'instructions' => '',
					'type'         => 'text',
					'name'         => 'default_value',
					'placeholder'  => '#FFFFFF',
				)
			);

			// Toggle opacity control.
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Enable Transparency', 'acf' ),
					'instructions' => '',
					'type'         => 'true_false',
					'name'         => 'enable_opacity',
					'ui'           => 1,
				)
			);

			// Return format control.
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf' ),
					'instructions' => '',
					'type'         => 'radio',
					'name'         => 'return_format',
					'layout'       => 'horizontal',
					'choices'      => array(
						'string' => __( 'Hex String', 'acf' ),
						'array'  => __( 'RGBA Array', 'acf' ),
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
		public function render_field_presentation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Show Custom Palette', 'acf' ),
					'instructions' => '',
					'type'         => 'true_false',
					'name'         => 'show_custom_palette',
					'ui'           => 1,
				)
			);

			$custom_palette_conditions = array(
				'field'    => 'show_custom_palette',
				'operator' => '==',
				'value'    => 1,
			);

			if ( wp_theme_has_theme_json() ) {
				acf_render_field_setting(
					$field,
					array(
						'label'        => __( 'Custom Palette Source', 'acf' ),
						'instructions' => '',
						'type'         => 'radio',
						'name'         => 'custom_palette_source',
						'layout'       => 'vertical',
						'choices'      => array(
							'custom'  => __( 'Specify custom colors', 'acf' ),
							'themejson' => __( 'Use colors from theme.json', 'acf' ),
						),
						'conditions'    => array(
							'field'    => 'show_custom_palette',
							'operator' => '==',
							'value'    => 1,
						),
					)
				);

				$custom_palette_conditions = array(
					'field'    => 'custom_palette_source',
					'operator' => '==',
					'value'    => 'custom',
				);
			}

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Custom Palette', 'acf' ),
					'instructions' => __( 'Use a custom color palette by entering comma separated hex or rgba values', 'acf' ),
					'type'         => 'text',
					'name'         => 'palette_colors',
					'conditions'   => $custom_palette_conditions,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'         => __( 'Show Color Wheel', 'acf' ),
					'instructions'  => '',
					'type'          => 'true_false',
					'name'          => 'show_color_wheel',
					'default_value' => 1,
					'ui'            => 1,
				)
			);
		}

		/**
		 * Format the value for use in templates. At this stage, the value has been loaded from the
		 * database and is being returned by an API function such as get_field(), the_field(), etc.
		 *
		 * @since 5.10
		 *
		 * @param  mixed   $value   The field value
		 * @param  integer $post_id The post ID
		 * @param  array   $field   The field array
		 * @return string|array
		 */
		public function format_value( $value, $post_id, $field ) {
			if ( isset( $field['return_format'] ) && $field['return_format'] === 'array' ) {
				$value = $this->string_to_array( $value );
			}

			return $value;
		}

		/**
		 * Convert either a Hexadecimal or RGBA string to an RGBA array.
		 *
		 * @since        5.10
		 * @date         15/12/20
		 *
		 * @param string $value
		 * @return array
		 */
		private function string_to_array( $value ) {
			$value = is_string( $value ) ? trim( $value ) : '';

			// Match and collect r,g,b values from 6 digit hex code. If there are 4
			// match-results, we have the values we need to build an r,g,b,a array.
			preg_match( '/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $value, $matches );
			if ( count( $matches ) === 4 ) {
				return array(
					'red'   => hexdec( $matches[1] ),
					'green' => hexdec( $matches[2] ),
					'blue'  => hexdec( $matches[3] ),
					'alpha' => (float) 1,
				);
			}

			// Match and collect r,g,b values from 3 digit hex code. If there are 4
			// match-results, we have the values we need to build an r,g,b,a array.
			// We have to duplicate the matched hex digit for 3 digit hex codes.
			preg_match( '/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i', $value, $matches );
			if ( count( $matches ) === 4 ) {
				return array(
					'red'   => hexdec( $matches[1] . $matches[1] ),
					'green' => hexdec( $matches[2] . $matches[2] ),
					'blue'  => hexdec( $matches[3] . $matches[3] ),
					'alpha' => (float) 1,
				);
			}

			// Attempt to match an rgba(…) or rgb(…) string (case-insensitive), capturing the decimals
			// as a string. If there are two match results, we have the RGBA decimal values as a
			// comma-separated string. Break it apart and, depending on the number of values, return
			// our formatted r,g,b,a array.
			preg_match( '/^rgba?\(([0-9,.]+)\)/i', $value, $matches );
			if ( count( $matches ) === 2 ) {
				$decimals = explode( ',', $matches[1] );

				// Handle rgba() format.
				if ( count( $decimals ) === 4 ) {
					return array(
						'red'   => (int) $decimals[0],
						'green' => (int) $decimals[1],
						'blue'  => (int) $decimals[2],
						'alpha' => (float) $decimals[3],
					);
				}

				// Handle rgb() format.
				if ( count( $decimals ) === 3 ) {
					return array(
						'red'   => (int) $decimals[0],
						'green' => (int) $decimals[1],
						'blue'  => (int) $decimals[2],
						'alpha' => (float) 1,
					);
				}
			}

			return array(
				'red'   => 0,
				'green' => 0,
				'blue'  => 0,
				'alpha' => (float) 0,
			);
		}
	}

	// initialize
	acf_register_field_type( 'acf_field_color_picker' );
endif; // class_exists check

?>
