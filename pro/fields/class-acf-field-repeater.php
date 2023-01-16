<?php

if ( ! class_exists( 'acf_field_repeater' ) ) :

	class acf_field_repeater extends acf_field {

		/**
		 * If we're currently rendering fields.
		 *
		 * @var bool
		 */
		public $is_rendering = false;

		/**
		 * The post/page ID that we're rendering for.
		 *
		 * @var mixed
		 */
		public $post_id = false;

		/**
		 * This function will set up the field type data
		 *
		 * @date  5/03/2014
		 * @since 5.0.0
		 */
		public function initialize() {
			$this->name     = 'repeater';
			$this->label    = __( 'Repeater', 'acf' );
			$this->category = 'layout';
			$this->defaults = array(
				'sub_fields'    => array(),
				'min'           => 0,
				'max'           => 0,
				'rows_per_page' => 20,
				'layout'        => 'table',
				'button_label'  => '',
				'collapsed'     => '',
			);

			// field filters
			$this->add_field_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_field_for_export' ) );
			$this->add_field_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_import' ) );

			// filters
			$this->add_filter( 'acf/validate_field', array( $this, 'validate_any_field' ) );
			$this->add_filter( 'acf/pre_render_fields', array( $this, 'pre_render_fields' ), 10, 2 );

			add_action( 'wp_ajax_acf/ajax/query_repeater', array( $this, 'ajax_get_rows' ) );
		}

		/**
		 * Localizes text for the repeater field.
		 *
		 * @date    16/12/2015
		 * @since   5.3.2
		 */
		public function input_admin_enqueue_scripts() {
			acf_localize_text(
				array(
					'Minimum rows reached ({min} rows)' => __( 'Minimum rows reached ({min} rows)', 'acf' ),
					'Maximum rows reached ({max} rows)' => __( 'Maximum rows reached ({max} rows)', 'acf' ),
					'Error loading page'                => __( 'Error loading page', 'acf' ),
					'Order will be assigned upon save'  => __( 'Order will be assigned upon save', 'acf' ),
				)
			);
		}

		/**
		 * Filters the field array after it's loaded from the database.
		 *
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param array $field The field array holding all the field options.
		 * @return array
		 */
		public function load_field( $field ) {
			$field['min'] = (int) $field['min'];
			$field['max'] = (int) $field['max'];
			$sub_fields   = acf_get_fields( $field );

			if ( $sub_fields ) {
				$field['sub_fields'] = array_map(
					function( $sub_field ) use ( $field ) {
						$sub_field['parent_repeater'] = $field['key'];
						return $sub_field;
					},
					$sub_fields
				);
			}

			if ( empty( $field['rows_per_page'] ) || (int) $field['rows_per_page'] < 1 ) {
				$field['rows_per_page'] = 20;
			}

			if ( '' === $field['button_label'] ) {
				$field['button_label'] = __( 'Add Row', 'acf' );
			}

			return $field;
		}

		/**
		 * Runs on the "acf/pre_render_fields" filter. Used to signify
		 * that we're currently rendering a repeater field.
		 *
		 * @since 6.0.0
		 *
		 * @param array $fields  The main field array.
		 * @param mixed $post_id The post ID for the field being rendered.
		 * @return array
		 */
		public function pre_render_fields( $fields, $post_id = false ) {
			if ( is_admin() ) {
				$this->is_rendering = true;
				$this->post_id      = $post_id;
			}

			return $fields;
		}

		/**
		 * Create the HTML interface for your field
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field( $field ) {
			$field['orig_name']  = $this->get_field_name_from_input_name( $field['name'] );
			$field['total_rows'] = (int) acf_get_metadata( $this->post_id, $field['orig_name'] );
			$table               = new ACF_Repeater_Table( $field );
			$table->render();
		}

		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param array $field An array holding all the field's data.
		 */
		function render_field_settings( $field ) {
			$args                = array(
				'fields'      => $field['sub_fields'],
				'parent'      => $field['ID'],
				'is_subfield' => true,
			);
			$supports_pagination = ( empty( $field['parent_repeater'] ) && empty( $field['parent_layout'] ) );
			?>
			<div class="acf-field acf-field-setting-sub_fields" data-setting="repeater" data-name="sub_fields">
				<div class="acf-label">
					<label><?php _e( 'Sub Fields', 'acf' ); ?></label>
					<p class="description"></p>		
				</div>
				<div class="acf-input acf-input-sub">
					<?php

					acf_get_view( 'field-group-fields', $args );

					?>
				</div>
			</div>
			<?php
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Layout', 'acf' ),
					'instructions' => '',
					'class'        => 'acf-repeater-layout',
					'type'         => 'radio',
					'name'         => 'layout',
					'layout'       => 'horizontal',
					'choices'      => array(
						'table' => __( 'Table', 'acf' ),
						'block' => __( 'Block', 'acf' ),
						'row'   => __( 'Row', 'acf' ),
					),
				)
			);

			if ( $supports_pagination ) {
				acf_render_field_setting(
					$field,
					array(
						'label'        => __( 'Pagination', 'acf' ),
						'instructions' => __( 'Useful for fields with a large number of rows.', 'acf' ),
						'class'        => 'acf-repeater-pagination',
						'type'         => 'true_false',
						'name'         => 'pagination',
						'ui'           => 1,
					)
				);

				acf_render_field_setting(
					$field,
					array(
						'label'        => __( 'Rows Per Page', 'acf' ),
						'instructions' => __( 'Set the number of rows to be displayed on a page.', 'acf' ),
						'class'        => 'acf-repeater-pagination-num-rows',
						'type'         => 'number',
						'name'         => 'rows_per_page',
						'placeholder'  => 20,
						'ui'           => 1,
						'min'          => 1,
						'conditions'   => array(
							'field'    => 'pagination',
							'operator' => '==',
							'value'    => 1,
						),
					)
				);
			}
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
			$field['min'] = empty( $field['min'] ) ? '' : $field['min'];
			$field['max'] = empty( $field['max'] ) ? '' : $field['max'];

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Minimum Rows', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'min',
					'placeholder'  => '0',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum Rows', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'max',
					'placeholder'  => '0',
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
			$choices = array();
			if ( $field['collapsed'] ) {
				$sub_field = acf_get_field( $field['collapsed'] );

				if ( $sub_field ) {
					$choices[ $sub_field['key'] ] = $sub_field['label'];
				}
			}

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Collapsed', 'acf' ),
					'instructions' => __( 'Select a sub field to show when row is collapsed', 'acf' ),
					'type'         => 'select',
					'name'         => 'collapsed',
					'allow_null'   => 1,
					'choices'      => $choices,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Button Label', 'acf' ),
					'instructions' => '',
					'type'         => 'text',
					'name'         => 'button_label',
					'placeholder'  => __( 'Add Row', 'acf' ),
				)
			);
		}

		/**
		 * Filters the field $value after it is loaded from the database.
		 *
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param mixed $value    The value found in the database.
		 * @param mixed $post_id  The $post_id from which the value was loaded.
		 * @param array $field    The field array holding all the field options.
		 * @return array $value
		 */
		public function load_value( $value, $post_id, $field ) {
			// Bail early if we don't have enough info to load the field.
			if ( empty( $value ) || ! is_numeric( $value ) || empty( $field['sub_fields'] ) ) {
				return false;
			}

			$value  = (int) $value;
			$rows   = array();
			$offset = 0;

			// Ensure pagination is disabled inside blocks.
			if ( acf_get_data( 'acf_inside_rest_call' ) || doing_action( 'wp_ajax_acf/ajax/fetch-block' ) ) {
				$field['pagination'] = false;
			}

			if ( ! empty( $field['pagination'] ) && $this->is_rendering ) {
				$rows_per_page = isset( $field['rows_per_page'] ) ? (int) $field['rows_per_page'] : 20;

				if ( $rows_per_page < 1 ) {
					$rows_per_page = 20;
				}

				if ( doing_action( 'wp_ajax_acf/ajax/query_repeater' ) ) {
					$offset = ( intval( $_POST['paged'] ) - 1 ) * $rows_per_page; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
					$value  = min( $value, $offset + $rows_per_page );
				} else {
					$value = min( $value, $rows_per_page );
				}
			}

			for ( $i = $offset; $i < $value; $i++ ) {
				$rows[ $i ] = array();

				foreach ( array_keys( $field['sub_fields'] ) as $j ) {
					$sub_field = $field['sub_fields'][ $j ];

					// Bail early if no name (tab field).
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// Update $sub_field name and value.
					$sub_field['name']               = "{$field['name']}_{$i}_{$sub_field['name']}";
					$sub_value                       = acf_get_value( $post_id, $sub_field );
					$rows[ $i ][ $sub_field['key'] ] = $sub_value;
				}
			}

			return $rows;
		}

		/**
		 * This filter is applied to the $value after it is loaded from the db,
		 * and before it is returned to the template.
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param mixed $value   The value which was loaded from the database.
		 * @param mixed $post_id The $post_id from which the value was loaded.
		 * @param array $field   The field array holding all the field options.
		 *
		 * @return array $value The modified value.
		 */
		function format_value( $value, $post_id, $field ) {
			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// bail early if not array
			if ( ! is_array( $value ) ) {
				return false;
			}

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return false;
			}

			// loop over rows
			foreach ( array_keys( $value ) as $i ) {

				// loop through sub fields
				foreach ( array_keys( $field['sub_fields'] ) as $j ) {

					// get sub field
					$sub_field = $field['sub_fields'][ $j ];

					// bail early if no name (tab)
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// extract value
					$sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );

					// update $sub_field name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

					// format value
					$sub_value = acf_format_value( $sub_value, $post_id, $sub_field );

					// append to $row
					$value[ $i ][ $sub_field['_name'] ] = $sub_value;
				}
			}

			return $value;
		}

		/**
		 * Validates values for the repeater field
		 *
		 * @date  11/02/2014
		 * @since 5.0.0
		 *
		 * @param bool   $valid  If the field is valid.
		 * @param mixed  $value  The value to validate.
		 * @param array  $field  The main field array.
		 * @param string $input  The input element's name attribute.
		 *
		 * @return bool
		 */
		function validate_value( $valid, $value, $field, $input ) {
			// vars
			$count = 0;

			// check if is value (may be empty string)
			if ( is_array( $value ) ) {

				// remove acfcloneindex
				if ( isset( $value['acfcloneindex'] ) ) {
					unset( $value['acfcloneindex'] );
				}

				// count
				$count = count( $value );
			}

			// validate required
			if ( $field['required'] && ! $count ) {
				$valid = false;
			}

			// min
			$min = (int) $field['min'];
			if ( empty( $field['pagination'] ) && $min && $count < $min ) {

				// create error
				$error = __( 'Minimum rows reached ({min} rows)', 'acf' );
				$error = str_replace( '{min}', $min, $error );

				// return
				return $error;
			}

			// validate value
			if ( $count ) {

				// bail early if no sub fields
				if ( ! $field['sub_fields'] ) {
					return $valid;
				}

				// loop rows
				foreach ( $value as $i => $row ) {

					// Skip rows that were deleted in paginated repeaters.
					if ( false !== strpos( $i, '_deleted' ) ) {
						continue;
					}

					// loop sub fields
					foreach ( $field['sub_fields'] as $sub_field ) {

						// vars
						$k = $sub_field['key'];

						// test sub field exists
						if ( ! isset( $row[ $k ] ) ) {
							continue;
						}

						// validate
						acf_validate_value( $row[ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
					}
					// end loop sub fields
				}
				// end loop rows
			}

			return $valid;
		}

		/**
		 * This function will update a value row.
		 *
		 * @date    15/2/17
		 * @since   5.5.8
		 *
		 * @param   array $row
		 * @param   int   $i
		 * @param   array $field
		 * @param   mixed $post_id
		 * @return  boolean
		 */
		function update_row( $row, $i, $field, $post_id ) {
			// bail early if no layout reference
			if ( ! is_array( $row ) ) {
				return false;
			}

			// bail early if no layout
			if ( empty( $field['sub_fields'] ) ) {
				return false;
			}

			foreach ( $field['sub_fields'] as $sub_field ) {
				$value = null;

				if ( array_key_exists( $sub_field['key'], $row ) ) {
					$value = $row[ $sub_field['key'] ];
				} elseif ( array_key_exists( $sub_field['name'], $row ) ) {
					$value = $row[ $sub_field['name'] ];
				} else {
					// Value does not exist.
					continue;
				}

				// modify name for save
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

				// update field
				acf_update_value( $value, $post_id, $sub_field );
			}

			return true;
		}

		/**
		 * This function will delete a value row.
		 *
		 * @date    15/2/17
		 * @since   5.5.8
		 *
		 * @param   int   $i
		 * @param   array $field
		 * @param   mixed $post_id
		 * @return  boolean
		 */
		function delete_row( $i, $field, $post_id ) {
			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return false;
			}

			foreach ( $field['sub_fields'] as $sub_field ) {
				// modify name for delete
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

				// delete value
				acf_delete_value( $post_id, $sub_field );
			}

			return true;
		}

		/**
		 * Filters the $value before it is updated in the database.
		 *
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param mixed $value   The value which will be saved in the database.
		 * @param array $field   The field array holding all the field options.
		 * @param mixed $post_id The $post_id of which the value will be saved.
		 *
		 * @return mixed $value
		 */
		function update_value( $value, $post_id, $field ) {
			// Bail early if no sub fields.
			if ( empty( $field['sub_fields'] ) ) {
				return $value;
			}

			if ( ! is_array( $value ) ) {
				$value = array();
			}

			if ( isset( $value['acfcloneindex'] ) ) {
				unset( $value['acfcloneindex'] );
			}

			$new_value = 0;
			$old_value = (int) acf_get_metadata( $post_id, $field['name'] );

			if ( ! empty( $field['pagination'] ) && did_action( 'acf/save_post' ) && ! isset( $_POST['_acf_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Value not used.
				$old_rows       = acf_get_value( $post_id, $field );
				$old_rows       = is_array( $old_rows ) ? $old_rows : array();
				$edited_rows    = array();
				$deleted_rows   = array();
				$reordered_rows = array();
				$new_rows       = array();

				// Categorize the submitted values, so we know what to do with them.
				foreach ( $value as $key => $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}

					// Check if this is a new row.
					if ( false === strpos( $key, 'row' ) ) {
						unset( $row['acf_changed'] );

						// Check if this new row was inserted before an existing row.
						$reordered_row_num = isset( $row['acf_reordered'] ) ? (int) $row['acf_reordered'] : false;

						if ( false !== $reordered_row_num && $reordered_row_num <= $old_value ) {
							$reordered_rows[ $key ] = $reordered_row_num;
						} else {
							$new_rows[ $key ] = $row;
						}

						continue;
					}

					$row_num = (int) str_replace( 'row-', '', $key );

					if ( isset( $row['acf_deleted'] ) ) {
						$deleted_rows[] = $row_num;
					} elseif ( isset( $row['acf_reordered'] ) ) {
						$reordered_rows[ $row_num ] = (int) $row['acf_reordered'];
					} else {
						unset( $row['acf_changed'] );
						$edited_rows[ $row_num ] = $row;
					}
				}

				// Process any row deletions first, but don't remove their keys yet.
				foreach ( $deleted_rows as $deleted_row ) {
					$this->delete_row( $deleted_row, $field, $post_id );
					$old_rows[ $deleted_row ] = 'acf_deleted';
				}

				// Update any existing rows that were edited.
				foreach ( $edited_rows as $key => $row ) {
					if ( array_key_exists( $key, $old_rows ) ) {
						$old_rows[ $key ] = $row;
					}
				}

				$rows_to_move     = array();
				$new_rows_to_move = array();
				foreach ( $reordered_rows as $old_order => $new_order ) {
					if ( is_int( $old_order ) ) {
						$rows_to_move[ $new_order ][] = $value[ 'row-' . $old_order ];
						unset( $old_rows[ $old_order ] );
					} else {
						$new_rows_to_move[ $new_order ][] = $value[ $old_order ];
					}
				}

				// Iterate over existing moved rows first.
				if ( ! empty( $rows_to_move ) ) {
					ksort( $rows_to_move );
					foreach ( $rows_to_move as $key => $values ) {
						array_splice( $old_rows, $key, 0, $values );
					}
				}

				// Iterate over inserted/duplicated rows in reverse order, so they're inserted into the correct spot.
				if ( ! empty( $new_rows_to_move ) ) {
					krsort( $new_rows_to_move );
					foreach ( $new_rows_to_move as $key => $values ) {
						array_splice( $old_rows, $key, 0, $values );
					}
				}

				// Append any new rows.
				foreach ( $new_rows as $new_row ) {
					$old_rows[] = $new_row;
				}

				// Update the rows in the database.
				$new_row_num = 0;
				foreach ( $old_rows as $key => $row ) {
					if ( 'acf_deleted' === $row ) {
						unset( $old_rows[ $key ] );
						continue;
					}

					$this->update_row( $row, $new_row_num, $field, $post_id );
					$new_row_num++;
				}

				// Calculate the total number of rows that will be saved after this update.
				$new_value = count( $old_rows );
			} else {
				$i = -1;

				foreach ( $value as $row ) {
					$i++;

					// Bail early if no row.
					if ( ! is_array( $row ) ) {
						continue;
					}

					$this->update_row( $row, $i, $field, $post_id );
					$new_value++;
				}
			}

			// Remove old rows.
			if ( $old_value > $new_value ) {
				for ( $i = $new_value; $i < $old_value; $i++ ) {
					$this->delete_row( $i, $field, $post_id );
				}
			}

			// Save empty string for empty value.
			if ( empty( $new_value ) ) {
				$new_value = '';
			}

			return $new_value;
		}

		/**
		 * Deletes any subfields after the field has been deleted.
		 *
		 * @date    4/04/2014
		 * @since   5.0.0
		 *
		 * @param array $field The main field array.
		 * @return void
		 */
		function delete_field( $field ) {
			// Bail early if no subfields.
			if ( empty( $field['sub_fields'] ) ) {
				return;
			}

			// Delete any subfields.
			foreach ( $field['sub_fields'] as $sub_field ) {
				acf_delete_field( $sub_field['ID'] );
			}
		}

		/**
		 * Deletes a value from the database.
		 *
		 * @date    1/07/2015
		 * @since   5.2.3
		 *
		 * @param int    $post_id The post ID to delete the value from.
		 * @param string $key     The meta name/key (unused).
		 * @param array  $field   The main field array.
		 * @return void
		 */
		function delete_value( $post_id, $key, $field ) {
			// Get the old value from the database.
			$old_value = (int) acf_get_metadata( $post_id, $field['name'] );

			// Bail early if no rows or no subfields.
			if ( ! $old_value || empty( $field['sub_fields'] ) ) {
				return;
			}

			for ( $i = 0; $i < $old_value; $i++ ) {
				$this->delete_row( $i, $field, $post_id );
			}
		}

		/**
		 * This filter is applied to the $field before it is saved to the database.
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param array $field The field array holding all the field options.
		 *
		 * @return array
		 */
		function update_field( $field ) {
			unset( $field['sub_fields'] );
			return $field;
		}

		/**
		 * This filter is applied to the $field before it is duplicated and saved to the database.
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param array $field The field array holding all the field options.
		 * @return array
		 */
		function duplicate_field( $field ) {
			// get sub fields
			$sub_fields = acf_extract_var( $field, 'sub_fields' );

			// save field to get ID
			$field = acf_update_field( $field );

			// duplicate sub fields
			acf_duplicate_fields( $sub_fields, $field['ID'] );

			return $field;
		}

		/**
		 * This function will translate field settings.
		 *
		 * @date  8/03/2016
		 * @since 5.3.2
		 *
		 * @param array $field The main field array.
		 * @return array
		 */
		function translate_field( $field ) {
			$field['button_label'] = acf_translate( $field['button_label'] );
			return $field;
		}

		/**
		 * This function will add compatibility for the 'column_width' setting
		 *
		 * @date  30/1/17
		 * @since 5.5.6
		 *
		 * @param array $field The main field array.
		 * @return array
		 */
		function validate_any_field( $field ) {
			// width has changed
			if ( isset( $field['column_width'] ) ) {
				$field['wrapper']['width'] = acf_extract_var( $field, 'column_width' );
			}

			return $field;
		}

		/**
		 * Prepares the field for export.
		 *
		 * @date  11/03/2014
		 * @since 5.0.0
		 *
		 * @param array $field The field settings.
		 * @return array
		 */
		function prepare_field_for_export( $field ) {
			// Check for subfields.
			if ( ! empty( $field['sub_fields'] ) ) {
				$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
			}

			return $field;
		}

		/**
		 * Returns a flat array of fields containing all subfields ready for import.
		 *
		 * @date   11/03/2014
		 * @since  5.0.0
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
		 * Additional validation for the repeater field when submitted via REST.
		 *
		 * @param bool  $valid
		 * @param int   $value
		 * @param array $field
		 *
		 * @return bool|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			if ( ! is_array( $value ) && is_null( $value ) ) {
				$param = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
				$data  = array(
					'param' => $param,
					'value' => $value,
				);
				$error = sprintf( __( '%s must be of type array or null.', 'acf' ), $param );
				return new WP_Error( 'rest_invalid_param', $error, $param );
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
			$schema = array(
				'type'     => array( 'array', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'type'       => 'object',
					'properties' => array(),
				),
			);

			foreach ( $field['sub_fields'] as $sub_field ) {
				if ( $sub_field_schema = acf_get_field_rest_schema( $sub_field ) ) {
					$schema['items']['properties'][ $sub_field['name'] ] = $sub_field_schema;
				}
			}

			if ( ! empty( $field['min'] ) ) {
				$schema['minItems'] = (int) $field['min'];
			}

			if ( ! empty( $field['max'] ) ) {
				$schema['maxItems'] = (int) $field['max'];
			}

			return $schema;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed      $value
		 * @param int|string $post_id
		 * @param array      $field
		 * @return array|mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			if ( empty( $value ) || ! is_array( $value ) || empty( $field['sub_fields'] ) ) {
				return null;
			}

			// Loop through each row and within that, each sub field to process sub fields individually.
			foreach ( $value as &$row ) {
				foreach ( $field['sub_fields'] as $sub_field ) {

					// Bail early if the field has no name (tab).
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// Extract the sub field 'field_key'=>'value' pair from the $row and format it.
					$sub_value = acf_extract_var( $row, $sub_field['key'] );
					$sub_value = acf_format_value_for_rest( $sub_value, $post_id, $sub_field );

					// Add the sub field value back to the $row but mapped to the field name instead
					// of the key reference.
					$row[ $sub_field['name'] ] = $sub_value;
				}
			}

			return $value;
		}

		/**
		 * Takes the provided input name and turns it into a field name that
		 * works with repeater fields that are subfields of other fields.
		 *
		 * @param string $input_name The name attribute used in the repeater.
		 *
		 * @return string|bool
		 */
		public function get_field_name_from_input_name( $input_name ) {
			$parts = array();
			preg_match_all( '/\[([^\]]*)\]/', $input_name, $parts );

			if ( ! isset( $parts[1] ) ) {
				return false;
			}

			$field_keys = $parts[1];
			$name_parts = array();

			foreach ( $field_keys as $field_key ) {
				if ( ! acf_is_field_key( $field_key ) ) {
					if ( 'acfcloneindex' === $field_key ) {
						$name_parts[] = 'acfcloneindex';
						continue;
					}

					$row_num = str_replace( 'row-', '', $field_key );
					if ( is_numeric( $row_num ) ) {
						$name_parts[] = (int) $row_num;
						continue;
					}
				}

				$field = acf_get_field( $field_key );

				if ( $field ) {
					$name_parts[] = $field['name'];
				}
			}

			return implode( '_', $name_parts );
		}

		/**
		 * Returns an array of rows used to populate the repeater table over AJAX.
		 *
		 * @since 6.0.0
		 *
		 * @return void|WP_Error
		 */
		public function ajax_get_rows() {
			if ( ! acf_verify_ajax() ) {
				$error = array( 'error' => __( 'Invalid nonce.', 'acf' ) );
				wp_send_json_error( $error, 401 );
			}

			$args = acf_request_args(
				array(
					'field_name'    => '',
					'field_key'     => '',
					'post_id'       => 0,
					'rows_per_page' => 0,
					'refresh'       => false,
				)
			);

			if ( '' === $args['field_name'] || '' === $args['field_key'] ) {
				$error = array( 'error' => __( 'Invalid field key or name.', 'acf' ) );
				wp_send_json_error( $error, 404 );
			}

			$field    = acf_get_field( $args['field_key'] );
			$post_id  = acf_get_valid_post_id( $args['post_id'] );
			$response = array();

			if ( ! $field || ! $post_id ) {
				$error = array( 'error' => __( 'There was an error retrieving the field.', 'acf' ) );
				wp_send_json_error( $error, 404 );
			}

			// Make sure we have a valid field.
			$field = acf_validate_field( $field );

			// Make sure that we only get a subset of the rows.
			$this->is_rendering = true;

			$args['rows_per_page'] = (int) $args['rows_per_page'];

			if ( $args['rows_per_page'] ) {
				$field['rows_per_page'] = $args['rows_per_page'];
			}

			/**
			 * We have to swap out the field name with the one sent via JS,
			 * as the repeater could be inside a subfield.
			 */
			$field['name'] = $args['field_name'];

			$field['value']   = acf_get_value( $post_id, $field );
			$field            = acf_prepare_field( $field );
			$repeater_table   = new ACF_Repeater_Table( $field );
			$response['rows'] = $repeater_table->rows( true );

			if ( $args['refresh'] ) {
				$response['total_rows'] = (int) acf_get_metadata( $post_id, $args['field_name'] );
			}

			wp_send_json_success( $response );
		}

	}

	// initialize
	acf_register_field_type( 'acf_field_repeater' );
endif; // class_exists check

