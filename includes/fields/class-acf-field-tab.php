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

if ( ! class_exists( 'acf_field_tab' ) ) :

	class acf_field_tab extends acf_field {

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
			$this->name          = 'tab';
			$this->label         = __( 'Tab', 'acf' );
			$this->category      = 'layout';
			$this->description   = __( 'Allows you to group fields into tabbed sections in the edit screen. Useful for keeping fields organized and structured.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-tabs.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/tab/', 'docs', 'field-type-selection' );
			$this->supports      = array(
				'required' => false,
				'bindings' => false,
			);
			$this->defaults      = array(
				'placement' => 'top',
				'endpoint'  => 0, // added in 5.2.8
				'selected'  => 0, // added in 6.3
			);
		}

		/**
		 * Output the HTML required for a tab.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array of the field data.
		 */
		public function render_field( $field ) {
			$atts = array(
				'href'           => '',
				'class'          => 'acf-tab-button',
				'data-placement' => $field['placement'],
				'data-endpoint'  => $field['endpoint'],
				'data-key'       => $field['key'],
				'data-selected'  => $field['selected'],
			);

			if ( isset( $field['unique_tab_key'] ) && ! empty( $field['unique_tab_key'] ) ) {
				$atts['data-unique-tab-key'] = $field['unique_tab_key'];
			}

			if ( isset( $field['settings-type'] ) ) {
				$atts['data-settings-type'] = acf_slugify( $field['settings-type'] );
				$atts['class']             .= ' acf-settings-type-' . acf_slugify( $field['settings-type'] );
			}

			if ( isset( $field['class'] ) && ! empty( $field['class'] ) ) {
				$atts['class'] .= ' ' . $field['class'];
			}

			?>
			<a <?php echo acf_esc_attrs( $atts ); ?>><?php echo acf_esc_html( $field['label'] ); ?></a>
			<?php
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

			/*
				// message
				$message = '';
				$message .= '<p>' . __( 'Use "Tab Fields" to better organize your edit screen by grouping fields together.', 'acf') . '</p>';
				$message .= '<p>' . __( 'All fields following this "tab field" (or until another "tab field" is defined) will be grouped together using this field\'s label as the tab heading.','acf') . '</p>';


				// default_value
				acf_render_field_setting( $field, array(
				'label'         => __('Instructions','acf'),
				'instructions'  => '',
				'name'          => 'notes',
				'type'          => 'message',
				'message'       => $message,
				));
			*/

			// preview_size
			acf_render_field_setting(
				$field,
				array(
					'label'   => __( 'Placement', 'acf' ),
					'type'    => 'select',
					'name'    => 'placement',
					'choices' => array(
						'top'  => __( 'Top aligned', 'acf' ),
						'left' => __( 'Left aligned', 'acf' ),
					),
				)
			);

			// endpoint
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'New Tab Group', 'acf' ),
					'instructions' => __( 'Start a new group of tabs at this tab.', 'acf' ),
					'name'         => 'endpoint',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);
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
	acf_register_field_type( 'acf_field_tab' );
endif; // class_exists check

?>
