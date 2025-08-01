<?php

if ( ! class_exists( 'acf_field__group' ) ) :
	class acf_field__group extends acf_field {


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
			$this->name          = 'group';
			$this->label         = __( 'Group', 'acf' );
			$this->category      = 'layout';
			$this->description   = __( 'Provides a way to structure fields into groups to better organize the data and the edit screen.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-group.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/group/', 'docs', 'field-type-selection' );
			$this->supports      = array(
				'bindings' => false,
			);
			$this->defaults      = array(
				'sub_fields' => array(),
				'layout'     => 'block',
			);
			$this->have_rows     = 'single';

			// field filters
			$this->add_field_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_field_for_export' ) );
			$this->add_field_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_import' ) );
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

			// vars
			$sub_fields = acf_get_fields( $field );

			// append
			if ( $sub_fields ) {
				$field['sub_fields'] = $sub_fields;
			}

			// return
			return $field;
		}


		/**
		 * This filter is applied to the $value after it is loaded from the db
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value (mixed) the value found in the database
		 * @param   $post_id (mixed) the post_id from which the value was loaded
		 * @param   $field (array) the field array holding all the field options
		 * @return  $value
		 */
		function load_value( $value, $post_id, $field ) {

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return $value;
			}

			// modify names
			$field = $this->prepare_field_for_db( $field );

			// load sub fields
			$value = array();

			// loop
			foreach ( $field['sub_fields'] as $sub_field ) {

				// load
				$value[ $sub_field['key'] ] = acf_get_value( $post_id, $sub_field );
			}

			// return
			return $value;
		}


		/**
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @type    filter
		 * @since   3.6
		 *
		 * @param  mixed   $value       The value which was loaded from the database.
		 * @param  mixed   $post_id     The $post_id from which the value was loaded.
		 * @param  array   $field       The field array holding all the field options.
		 * @param  boolean $escape_html Should the field return a HTML safe formatted value.
		 * @return mixed the modified value
		 */
		public function format_value( $value, $post_id, $field, $escape_html = false ) {
			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// modify names
			$field = $this->prepare_field_for_db( $field );

			// loop
			foreach ( $field['sub_fields'] as $sub_field ) {

				// extract value
				$sub_value = acf_extract_var( $value, $sub_field['key'] );

				// format value
				$sub_value = acf_format_value( $sub_value, $post_id, $sub_field, $escape_html );

				// append to $row
				$value[ $sub_field['_name'] ] = $sub_value;
			}

			// return
			return $value;
		}


		/**
		 * This filter is appied to the $value before it is updated in the db
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value - the value which will be saved in the database
		 * @param   $field - the field array holding all the field options
		 * @param   $post_id - the post_id of which the value will be saved
		 *
		 * @return  $value - the modified value
		 */
		function update_value( $value, $post_id, $field ) {

			// bail early if no value
			if ( ! acf_is_array( $value ) ) {
				return null;
			}

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return null;
			}

			// modify names
			$field = $this->prepare_field_for_db( $field );

			// loop
			foreach ( $field['sub_fields'] as $sub_field ) {

				// vars
				$v = false;

				// key (backend)
				if ( isset( $value[ $sub_field['key'] ] ) ) {
					$v = $value[ $sub_field['key'] ];

					// name (frontend)
				} elseif ( isset( $value[ $sub_field['_name'] ] ) ) {
					$v = $value[ $sub_field['_name'] ];

					// empty
				} else {

					// input is not set (hidden by conditioanl logic)
					continue;
				}

				// update value
				acf_update_value( $v, $post_id, $sub_field );
			}

			// return
			return '';
		}


		/**
		 * This function will modify sub fields ready for update / load
		 *
		 * @type    function
		 * @date    4/11/16
		 * @since   5.5.0
		 *
		 * @param   $field (array)
		 * @return  $field
		 */
		function prepare_field_for_db( $field ) {

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return $field;
			}

			// loop
			foreach ( $field['sub_fields'] as &$sub_field ) {

				// prefix name
				$sub_field['name'] = $field['name'] . '_' . $sub_field['_name'];
			}

			// return
			return $field;
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

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return;
			}

			// load values
			foreach ( $field['sub_fields'] as &$sub_field ) {

				// add value
				if ( isset( $field['value'][ $sub_field['key'] ] ) ) {

					// this is a normal value
					$sub_field['value'] = $field['value'][ $sub_field['key'] ];
				} elseif ( isset( $sub_field['default_value'] ) ) {

					// no value, but this sub field has a default value
					$sub_field['value'] = $sub_field['default_value'];
				}

				// update prefix to allow for nested values
				$sub_field['prefix'] = $field['name'];

				// restore required
				if ( $field['required'] ) {
					$sub_field['required'] = 0;
				}
			}

			// render
			if ( $field['layout'] == 'table' ) {
				$this->render_field_table( $field );
			} else {
				$this->render_field_block( $field );
			}
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    12/07/2016
		 * @since   5.4.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function render_field_block( $field ) {

			// vars
			$label_placement = ( $field['layout'] == 'block' ) ? 'top' : 'left';

			// html
			echo '<div class="acf-fields -' . esc_attr( $label_placement ) . ' -border">';

			foreach ( $field['sub_fields'] as $sub_field ) {
				acf_render_field_wrap( $sub_field );
			}

			echo '</div>';
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    12/07/2016
		 * @since   5.4.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function render_field_table( $field ) {

			?>
<table class="acf-table">
	<thead>
		<tr>
			<?php
			foreach ( $field['sub_fields'] as $sub_field ) :

				// prepare field (allow sub fields to be removed)
				$sub_field = acf_prepare_field( $sub_field );

				// bail early if no field
				if ( ! $sub_field ) {
					continue;
				}

				// vars
				$atts              = array();
				$atts['class']     = 'acf-th';
				$atts['data-name'] = $sub_field['_name'];
				$atts['data-type'] = $sub_field['type'];
				$atts['data-key']  = $sub_field['key'];

				// Add custom width
				if ( $sub_field['wrapper']['width'] ) {
					$atts['data-width'] = $sub_field['wrapper']['width'];
					$atts['style']      = 'width: ' . $sub_field['wrapper']['width'] . '%;';
				}

				?>
			<th <?php echo acf_esc_attrs( $atts ); ?>>
				<?php acf_render_field_label( $sub_field ); ?>
				<?php acf_render_field_instructions( $sub_field ); ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<tr class="acf-row">
			<?php

			foreach ( $field['sub_fields'] as $sub_field ) {
				acf_render_field_wrap( $sub_field, 'td' );
			}

			?>
		</tr>
	</tbody>
</table>
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

			// vars
			$args = array(
				'fields'      => $field['sub_fields'],
				'parent'      => $field['ID'],
				'is_subfield' => true,
			);

			?>
			<div class="acf-field acf-field-setting-sub_fields" data-setting="group" data-name="sub_fields">
				<div class="acf-label">
					<label><?php esc_html_e( 'Sub Fields', 'acf' ); ?></label>	
				</div>
				<div class="acf-input acf-input-sub">
					<?php

					acf_get_view( 'acf-field-group/fields', $args );

					?>
				</div>
			</div>
			<?php

			// layout
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Layout', 'acf' ),
					'instructions' => __( 'Specify the style used to render the selected fields', 'acf' ),
					'type'         => 'radio',
					'name'         => 'layout',
					'layout'       => 'horizontal',
					'choices'      => array(
						'block' => __( 'Block', 'acf' ),
						'table' => __( 'Table', 'acf' ),
						'row'   => __( 'Row', 'acf' ),
					),
				)
			);
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    11/02/2014
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function validate_value( $valid, $value, $field, $input ) {

			// bail early if no $value
			if ( empty( $value ) ) {
				return $valid;
			}

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return $valid;
			}

			// loop
			foreach ( $field['sub_fields'] as $sub_field ) {

				// get sub field
				$k = $sub_field['key'];

				// bail early if value not set (conditional logic?)
				if ( ! isset( $value[ $k ] ) ) {
					continue;
				}

				// required
				if ( $field['required'] ) {
					$sub_field['required'] = 1;
				}

				// validate
				acf_validate_value( $value[ $k ], $sub_field, "{$input}[{$k}]" );
			}

			// return
			return $valid;
		}


		/**
		 * This filter is appied to the $field before it is duplicated and saved to the database
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $field - the field array holding all the field options
		 *
		 * @return  $field - the modified field
		 */
		function duplicate_field( $field ) {

			// get sub fields
			$sub_fields = acf_extract_var( $field, 'sub_fields' );

			// save field to get ID
			$field = acf_update_field( $field );

			// duplicate sub fields
			acf_duplicate_fields( $sub_fields, $field['ID'] );

			// return
			return $field;
		}

		/**
		 * prepare_field_for_export
		 *
		 * Prepares the field for export.
		 *
		 * @date    11/03/2014
		 * @since   5.0.0
		 *
		 * @param   array $field The field settings.
		 * @return  array
		 */
		function prepare_field_for_export( $field ) {

			// Check for sub fields.
			if ( ! empty( $field['sub_fields'] ) ) {
				$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
			}
			return $field;
		}

		/**
		 * prepare_field_for_import
		 *
		 * Returns a flat array of fields containing all sub fields ready for import.
		 *
		 * @date    11/03/2014
		 * @since   5.0.0
		 *
		 * @param   array $field The field settings.
		 * @return  array
		 */
		function prepare_field_for_import( $field ) {

			// Check for sub fields.
			if ( ! empty( $field['sub_fields'] ) ) {
				$sub_fields = acf_extract_var( $field, 'sub_fields' );

				// Modify sub fields.
				foreach ( $sub_fields as $i => $sub_field ) {
					$sub_fields[ $i ]['parent']     = $field['key'];
					$sub_fields[ $i ]['menu_order'] = $i;
				}

				// Return array of [field, sub_1, sub_2, ...].
				return array_merge( array( $field ), $sub_fields );
			}
			return $field;
		}


		/**
		 * Called when deleting this field's value.
		 *
		 * @date    1/07/2015
		 * @since   5.2.3
		 *
		 * @param   mixed  $post_id  The post ID being saved
		 * @param   string $meta_key The field name as seen by the DB
		 * @param   array  $field    The field settings
		 * @return  void
		 */
		function delete_value( $post_id, $meta_key, $field ) {

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return null;
			}

			// modify names
			$field = $this->prepare_field_for_db( $field );

			// loop
			foreach ( $field['sub_fields'] as $sub_field ) {
				acf_delete_value( $post_id, $sub_field );
			}
		}

		/**
		 * delete_field
		 *
		 * Called when deleting a field of this type.
		 *
		 * @date    8/11/18
		 * @since   5.8.0
		 *
		 * @param   arra $field The field settings.
		 * @return  void
		 */
		function delete_field( $field ) {

			// loop over sub fields and delete them
			if ( $field['sub_fields'] ) {
				foreach ( $field['sub_fields'] as $sub_field ) {
					acf_delete_field( $sub_field['ID'] );
				}
			}
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'       => array( 'object', 'null' ),
				'properties' => array(),
				'required'   => ! empty( $field['required'] ),
			);

			foreach ( $field['sub_fields'] as $sub_field ) {
				if ( $sub_field_schema = acf_get_field_rest_schema( $sub_field ) ) {
					$schema['properties'][ $sub_field['name'] ] = $sub_field_schema;
				}
			}

			return $schema;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed          $value
		 * @param integer|string $post_id
		 * @param array          $field
		 * @return array|mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			if ( empty( $value ) || ! is_array( $value ) || empty( $field['sub_fields'] ) ) {
				return $value;
			}

			// Loop through each row and within that, each sub field to process sub fields individually.
			foreach ( $field['sub_fields'] as $sub_field ) {

				// Extract the sub field 'field_key'=>'value' pair from the $value and format it.
				$sub_value = acf_extract_var( $value, $sub_field['key'] );
				$sub_value = acf_format_value_for_rest( $sub_value, $post_id, $sub_field );

				// Add the sub field value back to the $value but mapped to the field name instead
				// of the key reference.
				$value[ $sub_field['name'] ] = $sub_value;
			}

			return $value;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field__group' );
endif; // class_exists check

?>
