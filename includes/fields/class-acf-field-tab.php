<?php

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
			$this->supports      = array( 'required' => false );
			$this->defaults      = array(
				'placement' => 'top',
				'endpoint'  => 0, // added in 5.2.8
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
			$atts = array(
				'href'           => '',
				'class'          => 'acf-tab-button',
				'data-placement' => $field['placement'],
				'data-endpoint'  => $field['endpoint'],
				'data-key'       => $field['key'],
			);

			if ( isset( $field['settings-type'] ) ) {
				$atts['class'] .= ' acf-settings-type-' . acf_slugify( $field['settings-type'] );
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
