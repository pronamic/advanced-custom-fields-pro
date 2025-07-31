<?php

use ACF\Pro\Fields\FlexibleContent\Render;
use ACF\Pro\Fields\FlexibleContent\Layout;

if ( ! class_exists( 'acf_field_flexible_content' ) ) :

	class acf_field_flexible_content extends acf_field {

		/**
		 * The post/page ID that we're rendering for.
		 *
		 * @var mixed
		 */
		public $post_id = false;

		/**
		 * An array of layout meta for the current field.
		 *
		 * @var array
		 */
		public $layout_meta = array();

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
		public function initialize() {

			// vars
			$this->name          = 'flexible_content';
			$this->label         = __( 'Flexible Content', 'acf' );
			$this->category      = 'layout';
			$this->description   = __( 'Allows you to define, create and manage content with total control by creating layouts that contain subfields that content editors can choose from.', 'acf' ) . ' ' . __( 'We do not recommend using this field in ACF Blocks.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-flexible-content.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/flexible-content/', 'docs', 'field-type-selection' );
			$this->tutorial_url  = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/building-layouts-with-the-flexible-content-field-in-a-theme/', 'docs', 'field-type-selection' );
			$this->pro           = true;
			$this->supports      = array( 'bindings' => false );
			$this->defaults      = array(
				'layouts'      => array(),
				'min'          => '',
				'max'          => '',
				'button_label' => __( 'Add Row', 'acf' ),
			);

			// ajax
			$this->add_action( 'wp_ajax_acf/fields/flexible_content/layout_title', array( $this, 'ajax_layout_title' ) );
			$this->add_action( 'wp_ajax_nopriv_acf/fields/flexible_content/layout_title', array( $this, 'ajax_layout_title' ) );

			// filters
			$this->add_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_any_field_for_export' ) );
			$this->add_filter( 'acf/clone_field', array( $this, 'clone_any_field' ), 10, 2 );
			$this->add_filter( 'acf/validate_field', array( $this, 'validate_any_field' ) );
			$this->add_filter( 'acf/pre_render_fields', array( $this, 'pre_render_fields' ), 10, 2 );

			// field filters
			$this->add_field_filter( 'acf/get_sub_field', array( $this, 'get_sub_field' ), 10, 3 );
			$this->add_field_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_field_for_export' ) );
			$this->add_field_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_import' ) );
		}


		/**
		 * Admin scripts enqueue for field.
		 *
		 * @since 5.3.2
		 *
		 * @return void
		 */
		public function input_admin_enqueue_scripts() {
			acf_localize_text(
				array(

					// identifiers
					'layout'  => esc_html__( 'layout', 'acf' ),
					'layouts' => esc_html__( 'layouts', 'acf' ),
					'Fields'  => esc_html__( 'Fields', 'acf' ),

					// Deleting a layout.
					'Delete' => esc_html__( 'Delete', 'acf' ),
					'Delete Layout' => esc_html__( 'Delete Layout', 'acf' ),
					/* translators: %s - Name of the Flexible content layout */
					'Delete %s' => esc_html__( 'Delete %s', 'acf' ),
					'Are you sure you want to delete the layout?' => esc_html__( 'Are you sure you want to delete the layout?', 'acf' ),
					/* translators: %s - Name of the Flexible content layout */
					'Are you sure you want to delete %s?' => esc_html__( 'Are you sure you want to delete %s?', 'acf' ),

					// Renaming a layout.
					'Rename Layout' => esc_html__( 'Rename Layout', 'acf' ),
					'Rename' => esc_html__( 'Rename', 'acf' ),
					'New Label' => esc_html__( 'New Label', 'acf' ),
					'Remove Custom Label' => esc_html__( 'Remove Custom Label', 'acf' ),

					// min / max
					'This field requires at least {min} {label} {identifier}' => esc_html__( 'This field requires at least {min} {label} {identifier}', 'acf' ),
					'This field has a limit of {max} {label} {identifier}' => esc_html__( 'This field has a limit of {max} {label} {identifier}', 'acf' ),

					// popup badge
					'{available} {label} {identifier} available (max {max})' => esc_html__( '{available} {label} {identifier} available (max {max})', 'acf' ),
					'{required} {label} {identifier} required (min {min})' => esc_html__( '{required} {label} {identifier} required (min {min})', 'acf' ),

					// field settings
					'Flexible Content requires at least 1 layout' => esc_html__( 'Flexible Content requires at least 1 layout', 'acf' ),
				)
			);
		}


		/**
		 * This function will fill in the missing keys to create a valid layout
		 *
		 * @type    function
		 * @date    3/10/13
		 * @since   1.1.0
		 *
		 * @param   $layout (array)
		 * @return  $layout (array)
		 */
		function get_valid_layout( $layout = array() ) {

			// parse
			$layout = wp_parse_args(
				$layout,
				array(
					'key'        => uniqid( 'layout_' ),
					'name'       => '',
					'label'      => '',
					'display'    => 'block',
					'sub_fields' => array(),
					'min'        => '',
					'max'        => '',
				)
			);

			// return
			return $layout;
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

			// bail early if no field layouts
			if ( empty( $field['layouts'] ) ) {
				return $field;
			}

			// vars
			$sub_fields = acf_get_fields( $field );

			// loop through layouts, sub fields and swap out the field key with the real field
			foreach ( array_keys( $field['layouts'] ) as $i ) {

				// extract layout
				$layout = acf_extract_var( $field['layouts'], $i );

				// validate layout
				$layout = $this->get_valid_layout( $layout );

				// append sub fields
				if ( ! empty( $sub_fields ) ) {
					foreach ( array_keys( $sub_fields ) as $k ) {

						// check if 'parent_layout' is empty
						if ( empty( $sub_fields[ $k ]['parent_layout'] ) ) {

							// parent_layout did not save for this field, default it to first layout
							$sub_fields[ $k ]['parent_layout'] = $layout['key'];
						}

						// append sub field to layout,
						if ( $sub_fields[ $k ]['parent_layout'] == $layout['key'] ) {
							$layout['sub_fields'][] = acf_extract_var( $sub_fields, $k );
						}
					}
				}

				// append back to layouts
				$field['layouts'][ $i ] = $layout;
			}

			// return
			return $field;
		}


		/**
		 * This function will return a specific sub field
		 *
		 * @type    function
		 * @date    29/09/2016
		 * @since   5.4.0
		 *
		 * @param   $sub_field
		 * @param   $selector (string)
		 * @param   $field (array)
		 * @return  $post_id (int)
		 */
		function get_sub_field( $sub_field, $id, $field ) {

			// Get active layout.
			$active = get_row_layout();

			// Loop over layouts.
			if ( $field['layouts'] ) {
				foreach ( $field['layouts'] as $layout ) {

					// Restict to active layout if within a have_rows() loop.
					if ( $active && $active !== $layout['name'] ) {
						continue;
					}

					// Check sub fields.
					if ( $layout['sub_fields'] ) {
						$sub_field = acf_search_fields( $id, $layout['sub_fields'] );
						if ( $sub_field ) {
							break;
						}
					}
				}
			}

			// return
			return $sub_field;
		}

		/**
		 * Runs on the "acf/pre_render_fields" filter. Used to signify
		 * that we're currently rendering a Flexible Content field.
		 *
		 * @since 6.5
		 *
		 * @param array $fields  The main field array.
		 * @param mixed $post_id The post ID for the field being rendered.
		 * @return array
		 */
		public function pre_render_fields( $fields, $post_id = false ) {
			if ( is_admin() ) {
				$this->post_id = $post_id;
			}

			return $fields;
		}

		/**
		 * Renders the Flexible Content field.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 * @return void
		 */
		public function render_field( $field ) {
			// Add some defaults.
			if ( empty( $field['button_label'] ) ) {
				$field['button_label'] = $this->defaults['button_label'];
			}

			// Render the field.
			$renderer = new Render(
				$field,
				$this->get_layout_meta( $this->post_id, $field )
			);

			$renderer->render();
		}

		/**
		 * Renders a single layout in a Flexible Content field.
		 *
		 * @since   5.0.0
		 *
		 * @param array          $field  The field array.
		 * @param array          $layout The layout to render
		 * @param integer|string $i      The order of the layout being rendered.
		 * @param mixed          $value  The value of the layout.
		 * @return void
		 */
		public function render_layout( $field, $layout, $i, $value ) {
			$disabled_layouts = $this->get_disabled_layouts( $this->post_id, $field );
			$renamed_layouts  = $this->get_renamed_layouts( $this->post_id, $field );
			$layout_disabled  = in_array( $i, $disabled_layouts, true );
			$renamed          = ! empty( $renamed_layouts[ $i ] ) ? $renamed_layouts[ $i ] : '';

			$layout = new Layout( $field, $layout, $i, $value, $layout_disabled, $renamed );
			$layout->render();
		}

		/**
		 * Renders the flexible content field layouts in the field group editor.
		 *
		 * @since 3.6
		 * @date  23/01/13
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field_settings( $field ) {
			$layout_open = apply_filters( 'acf/fields/flexible_content/layout_default_expanded', false );

			// Load default layout.
			if ( empty( $field['layouts'] ) ) {
				$layout_open      = true;
				$field['layouts'] = array(
					array(),
				);
			}

			$field_settings_class = $layout_open ? 'open' : '';
			$toggle_class         = $layout_open ? 'open' : 'closed';
			$field_settings_style = $layout_open ? '' : 'display: none;';

			// loop through layouts
			foreach ( $field['layouts'] as $layout ) {

				// get valid layout
				$layout = $this->get_valid_layout( $layout );

				// vars
				$layout_prefix = "{$field['prefix']}[layouts][{$layout['key']}]";

				?>
				<div class="acf-field acf-field-setting-fc_layout" data-name="fc_layout" data-setting="flexible_content" data-layout-label="<?php echo esc_attr( $layout['label'] ); ?>" data-layout-name="<?php echo esc_attr( $layout['name'] ); ?>" data-id="<?php echo esc_attr( $layout['key'] ); ?>">
					<div class="acf-label acf-field-settings-fc_head">
						<div class="acf-fc_draggable">
							<label class="acf-fc-layout-label reorder-layout"><?php esc_attr_e( 'Layout', 'acf' ); ?></label>
						</div>

						<div class="acf-fc-layout-name copyable">
							<span class="layout-name"></span>
						</div>

						<ul class="acf-bl acf-fl-actions">
							<li><button class="acf-btn acf-btn-tertiary acf-btn-sm acf-field-setting-fc-delete"><i class="acf-icon acf-icon-trash delete-layout " href="#" title="<?php esc_attr_e( 'Delete Layout', 'acf' ); ?>"></i></button></li>
							<li><button class="acf-btn acf-btn-tertiary acf-btn-sm acf-field-setting-fc-duplicate"><i class="acf-icon -duplicate duplicate-layout" href="#" title="<?php esc_attr_e( 'Duplicate Layout', 'acf' ); ?>"></i></button></li>
							<li class="acf-fc-add-layout"><button class="add-layout acf-btn acf-btn-primary add-field" href="#" title="<?php esc_attr_e( 'Add New Layout', 'acf' ); ?>"><i class="acf-icon acf-icon-plus"></i><?php esc_html_e( 'Add Layout', 'acf' ); ?></button></li>
							<li><button type="button" class="acf-toggle-fc-layout" aria-expanded="true"></li>
							<li><span class="toggle-indicator  <?php echo esc_attr( $toggle_class ); ?>" aria-hidden="true"></span></li>
						</ul>
					</div>
					<div class="acf-input acf-field-layout-settings <?php echo esc_attr( $field_settings_class ); ?>" style="<?php echo esc_attr( $field_settings_style ); ?>">
						<?php

						acf_hidden_input(
							array(
								'id'    => acf_idify( $layout_prefix . '[key]' ),
								'name'  => $layout_prefix . '[key]',
								'class' => 'layout-key',
								'value' => $layout['key'],
							)
						);

						?>
						<ul class="acf-fc-meta acf-bl">
							<li class="acf-fc-meta-label acf-fc-meta-left">
								<?php

								acf_render_field(
									array(
										'type'    => 'text',
										'name'    => 'label',
										'class'   => 'layout-label',
										'prefix'  => $layout_prefix,
										'value'   => $layout['label'],
										'prepend' => __( 'Label', 'acf' ),
									)
								);

								?>
							</li>
							<li class="acf-fc-meta-name acf-fc-meta-right copyable input-copyable">
									<?php

									acf_render_field(
										array(
											'type'       => 'text',
											'name'       => 'name',
											'class'      => 'layout-name',
											'input-data' => array( '1p-ignore' => 'true' ),
											'prefix'     => $layout_prefix,
											'value'      => $layout['name'],
											'prepend'    => __( 'Name', 'acf' ),
										)
									);

									?>
							</li>
							<li class="acf-fc-meta-display acf-fc-meta-left">
								<div class="acf-input-prepend"><?php esc_html_e( 'Layout', 'acf' ); ?></div>
								<div class="acf-input-wrap">
									<?php

									acf_render_field(
										array(
											'type'    => 'select',
											'name'    => 'display',
											'prefix'  => $layout_prefix,
											'value'   => $layout['display'],
											'class'   => 'acf-is-prepended',
											'choices' => array(
												'table' => __( 'Table', 'acf' ),
												'block' => __( 'Block', 'acf' ),
												'row'   => __( 'Row', 'acf' ),
											),
										)
									);

									?>
								</div>
							</li>
							<li class="acf-fc-meta-min">
									<?php

									acf_render_field(
										array(
											'type'    => 'text',
											'name'    => 'min',
											'prefix'  => $layout_prefix,
											'value'   => $layout['min'],
											'prepend' => __( 'Min', 'acf' ),
										)
									);

									?>
							</li>
							<li class="acf-fc-meta-max">
									<?php

									acf_render_field(
										array(
											'type'    => 'text',
											'name'    => 'max',
											'prefix'  => $layout_prefix,
											'value'   => $layout['max'],
											'prepend' => __( 'Max', 'acf' ),
										)
									);

									?>
							</li>
						</ul>
						<div class="acf-input-sub">
						<?php

						// vars
						$args = array(
							'fields'      => $layout['sub_fields'],
							'parent'      => $field['ID'],
							'is_subfield' => true,
						);

						acf_get_view( 'acf-field-group/fields', $args );

						?>
						</div>
					</div>
				</div>
				<?php
			}
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

			// min
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Minimum Layouts', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'min',
				)
			);

			// max
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum Layouts', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'max',
				)
			);

			// add new row label
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Button Label', 'acf' ),
					'instructions' => '',
					'type'         => 'text',
					'name'         => 'button_label',
				)
			);
		}

		/**
		 * Filters the $value after it is loaded from the database.
		 *
		 * @since   3.6
		 *
		 * @param  mixed $value   The value found in the database
		 * @param  mixed $post_id The post_id from which the value was loaded
		 * @param  array $field   The field array holding all the field options
		 * @return $value
		 */
		public function load_value( $value, $post_id, $field ) {
			// bail early if no value
			if ( empty( $value ) || empty( $field['layouts'] ) ) {
				return $value;
			}

			$value            = acf_get_array( $value );
			$disabled_layouts = $this->get_disabled_layouts( $post_id, $field );
			$rows             = array();
			$layouts          = array();

			// sort layouts into names
			foreach ( $field['layouts'] as $k => $layout ) {
				$layouts[ $layout['name'] ] = $layout['sub_fields'];
			}

			// loop through rows
			foreach ( $value as $i => $l ) {
				// If the layout is disabled, prevent it from showing up on the frontend.
				if ( $this->should_disable_layout( $i, $disabled_layouts ) ) {
					continue;
				}

				// append to $values
				$rows[ $i ]                  = array();
				$rows[ $i ]['acf_fc_layout'] = $l;

				// bail early if layout deosnt contain sub fields
				if ( empty( $layouts[ $l ] ) ) {
					continue;
				}

				// get layout
				$layout = $layouts[ $l ];

				// loop through sub fields
				foreach ( array_keys( $layout ) as $j ) {

					// get sub field
					$sub_field = $layout[ $j ];

					// bail early if no name (tab)
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// update full name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

					// get value
					$sub_value = acf_get_value( $post_id, $sub_field );

					// add value
					$rows[ $i ][ $sub_field['key'] ] = $sub_value;
				}
			}

			return $rows;
		}

		/**
		 * Checks if a layout should be disabled based on the provided index and disabled layouts.
		 *
		 * @since 6.5
		 *
		 * @param integer|string $layout_index     The index of the layout to check.
		 * @param array          $disabled_layouts The array of disabled layout indices.
		 * @return boolean
		 */
		private function should_disable_layout( $layout_index, $disabled_layouts = array() ): bool {
			// No disabled layouts provided, so no need to disable.
			if ( ! is_array( $disabled_layouts ) || empty( $disabled_layouts ) ) {
				return false;
			}

			// The layout is not in the disabled list, so no need to disable.
			if ( ! in_array( $layout_index, $disabled_layouts, true ) ) {
				return false;
			}

			if ( is_admin() ) {
				$args = acf_request_args(
					array(
						'action' => '',
						'query'  => '',
					)
				);

				// If this is a block preview, disable the layout.
				if ( ( $args['action'] === 'acf/ajax/fetch-block' && ! empty( $args['query']['preview'] ) ) ||
					acf_get_data( 'acf_doing_block_preview' ) ) {
					return true;
				}

				// Editing a layout in the admin, so don't disable it.
				return false;
			}

			// The layout has been disabled, and we're on the frontend.
			return true;
		}

		/**
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @type  filter
		 * @since 3.6
		 *
		 * @param  mixed   $value       The value which was loaded from the database.
		 * @param  mixed   $post_id     The $post_id from which the value was loaded.
		 * @param  array   $field       The field array holding all the field options.
		 * @param  boolean $escape_html Should the field return a HTML safe formatted value.
		 * @return mixed   $value       The modified value.
		 */
		public function format_value( $value, $post_id, $field, $escape_html = false ) {

			// bail early if no value
			if ( empty( $value ) || empty( $field['layouts'] ) ) {
				return false;
			}

			// sort layouts into names
			$layouts = array();
			foreach ( $field['layouts'] as $k => $layout ) {
				$layouts[ $layout['name'] ] = $layout['sub_fields'];
			}

			// loop over rows
			foreach ( array_keys( $value ) as $i ) {

				// get layout name
				$l = $value[ $i ]['acf_fc_layout'];

				// bail early if layout deosnt exist
				if ( empty( $layouts[ $l ] ) ) {
					continue;
				}

				// get layout
				$layout = $layouts[ $l ];

				// loop through sub fields
				foreach ( array_keys( $layout ) as $j ) {

					// get sub field
					$sub_field = $layout[ $j ];

					// bail early if no name (tab)
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// extract value
					$sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );

					// update $sub_field name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

					// format value
					$sub_value = acf_format_value( $sub_value, $post_id, $sub_field, $escape_html );

					// append to $row
					$value[ $i ][ $sub_field['_name'] ] = $sub_value;
				}
			}

			// return
			return $value;
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
		public function validate_value( $valid, $value, $field, $input ) {

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

			// validate min
			$min = (int) $field['min'];
			if ( $min && $count < $min ) {

				// vars
				$error      = __( 'This field requires at least {min} {label} {identifier}', 'acf' );
				$identifier = _n( 'layout', 'layouts', $min, 'acf' );

				// replace
				$error = str_replace( '{min}', $min, $error );
				$error = str_replace( '{label}', '', $error );
				$error = str_replace( '{identifier}', $identifier, $error );

				// return
				return $error;
			}

			// find layouts
			$layouts = array();
			foreach ( array_keys( $field['layouts'] ) as $i ) {

				// vars
				$layout = $field['layouts'][ $i ];

				// add count
				$layout['count'] = 0;

				// append
				$layouts[ $layout['name'] ] = $layout;
			}

			// validate value
			if ( $count ) {

				// loop rows
				foreach ( $value as $i => $row ) {
					// ensure row is an array
					if ( ! is_array( $row ) ) {
						continue;
					}

					// get layout
					$l = $row['acf_fc_layout'];

					// bail if layout doesn't exist
					if ( ! isset( $layouts[ $l ] ) ) {
						continue;
					}

					// increase count
					++$layouts[ $l ]['count'];

					// bail if no sub fields
					if ( empty( $layouts[ $l ]['sub_fields'] ) ) {
						continue;
					}

					// loop sub fields
					foreach ( $layouts[ $l ]['sub_fields'] as $sub_field ) {

						// get sub field key
						$k = $sub_field['key'];

						// bail if no value
						if ( ! isset( $value[ $i ][ $k ] ) ) {
							continue;
						}

						// validate
						acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
					}
					// end loop sub fields
				}
				// end loop rows
			}

			// validate layouts
			foreach ( $layouts as $layout ) {

				// validate min / max
				$min   = (int) $layout['min'];
				$count = $layout['count'];
				$label = $layout['label'];

				if ( $min && $count < $min ) {

					// vars
					$error      = __( 'This field requires at least {min} {label} {identifier}', 'acf' );
					$identifier = _n( 'layout', 'layouts', $min, 'acf' );

					// replace
					$error = str_replace( '{min}', $min, $error );
					$error = str_replace( '{label}', '"' . $label . '"', $error );
					$error = str_replace( '{identifier}', $identifier, $error );

					// return
					return $error;
				}
			}

			// return
			return $valid;
		}


		/**
		 * This function will return a specific layout by name from a field
		 *
		 * @since   5.5.8
		 *
		 * @param  string $name  The layout name.
		 * @param  array  $field The field to load the layout from.
		 * @return array|false
		 */
		public function get_layout( $name, $field ) {

			// bail early if no layouts
			if ( ! isset( $field['layouts'] ) ) {
				return false;
			}

			// loop
			foreach ( $field['layouts'] as $layout ) {

				// match
				if ( $layout['name'] === $name ) {
					return $layout;
				}
			}

			// return
			return false;
		}

		/**
		 * Retrieves layout meta for the Flexible Content field saved to the provided post.
		 *
		 * @since 6.5
		 *
		 * @param integer|string $post_id The ID of the post being edited.
		 * @param array          $field   The Flexible Content field array.
		 * @return array
		 */
		public function get_layout_meta( $post_id, $field ) {
			$field_name = $field['name'];

			// Enables compatibility with nested Flexible Content fields during render.
			if ( ! empty( $field['_prepare'] ) ) {
				$field_name = acf_get_field_type( 'repeater' )->get_field_name_from_input_name( $field_name );
			}

			// Bail early if we don't have a field name to check.
			if ( empty( $field_name ) ) {
				return array();
			}

			// Return the cached meta if we have it.
			if ( ! empty( $this->layout_meta[ $field_name ] ) ) {
				return $this->layout_meta[ $field_name ];
			}

			$layout_meta = acf_get_metadata_by_field(
				$post_id,
				array(
					'name' => '_' . $field_name . '_layout_meta',
				)
			);

			if ( empty( $layout_meta ) || ! is_array( $layout_meta ) ) {
				return array();
			}

			$this->layout_meta[ $field_name ] = $layout_meta;

			return $this->layout_meta[ $field_name ];
		}

		/**
		 * Returns an array of layouts that have been disabled for the current field.
		 *
		 * @since 6.5
		 *
		 * @param integer|string $post_id The ID of the post being edited.
		 * @param array          $field   The Flexible Content field array.
		 * @return array
		 */
		public function get_disabled_layouts( $post_id, $field ): array {
			$layout_meta = $this->get_layout_meta( $post_id, $field );

			if ( empty( $layout_meta['disabled'] ) || ! is_array( $layout_meta['disabled'] ) ) {
				return array();
			}

			return $layout_meta['disabled'];
		}

		/**
		 * Returns an array of layouts that have been renamed for the current field.
		 *
		 * @since 6.5
		 *
		 * @param integer|string $post_id The ID of the post being edited.
		 * @param array          $field   The Flexible Content field array.
		 * @return array
		 */
		public function get_renamed_layouts( $post_id, $field ): array {
			$layout_meta = $this->get_layout_meta( $post_id, $field );

			if ( empty( $layout_meta['renamed'] ) || ! is_array( $layout_meta['renamed'] ) ) {
				return array();
			}

			return $layout_meta['renamed'];
		}

		/**
		 * This function will delete a value row
		 *
		 * @date    15/2/17
		 * @since   5.5.8
		 *
		 * @param   integer $i
		 * @param   array   $field
		 * @param   mixed   $post_id
		 * @return  boolean
		 */
		public function delete_row( $i, $field, $post_id ) {

			// vars
			$value = acf_get_metadata_by_field( $post_id, $field );

			// bail early if no value
			if ( ! is_array( $value ) || ! isset( $value[ $i ] ) ) {
				return false;
			}

			// get layout
			$layout = $this->get_layout( $value[ $i ], $field );

			// bail early if no layout
			if ( ! $layout || empty( $layout['sub_fields'] ) ) {
				return false;
			}

			// loop
			foreach ( $layout['sub_fields'] as $sub_field ) {

				// modify name for delete
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

				// delete value
				acf_delete_value( $post_id, $sub_field );
			}

			// return
			return true;
		}

		/**
		 * This function will update a value row
		 *
		 * @date    15/2/17
		 * @since   5.5.8
		 *
		 * @param   array   $row
		 * @param   integer $i
		 * @param   array   $field
		 * @param   mixed   $post_id
		 * @return  boolean
		 */
		public function update_row( $row, $i, $field, $post_id ) {
			// bail early if no layout reference
			if ( ! is_array( $row ) || ! isset( $row['acf_fc_layout'] ) ) {
				return false;
			}

			// get layout
			$layout = $this->get_layout( $row['acf_fc_layout'], $field );

			// bail early if no layout
			if ( ! $layout || empty( $layout['sub_fields'] ) ) {
				return false;
			}

			foreach ( $layout['sub_fields'] as $sub_field ) {
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
		 * Filters the $value before it is updated in the database.
		 *
		 * @since 3.6
		 *
		 * @param   mixed $value   The value which will be saved in the database
		 * @param   mixed $post_id The post_id of which the value will be saved
		 * @param   array $field   The field array holding all the field options
		 * @return  mixed $value   The modified value
		 */
		public function update_value( $value, $post_id, $field ) {
			// Bail early if no layouts or field name.
			if ( empty( $field['layouts'] ) || empty( $field['name'] ) ) {
				return $value;
			}

			// vars
			$new_value        = array();
			$disabled_layouts = array();
			$renamed_layouts  = array();
			$old_value        = acf_get_metadata_by_field( $post_id, $field );
			$old_value        = is_array( $old_value ) ? $old_value : array();

			// update
			if ( ! empty( $value ) ) {
				$i = -1;

				// remove acfcloneindex
				if ( isset( $value['acfcloneindex'] ) ) {
					unset( $value['acfcloneindex'] );
				}

				// loop through rows
				foreach ( $value as $row ) {
					++$i;

					// bail early if no layout reference
					if ( ! is_array( $row ) || ! isset( $row['acf_fc_layout'] ) ) {
						continue;
					}

					// delete old row if layout has changed
					if ( isset( $old_value[ $i ] ) && $old_value[ $i ] !== $row['acf_fc_layout'] ) {
						$this->delete_row( $i, $field, $post_id );
					}

					if ( ! empty( $row['acf_fc_layout_disabled'] ) ) {
						$disabled_layouts[] = $i;
					}
					unset( $row['acf_fc_layout_disabled'] );

					if ( ! empty( $row['acf_fc_layout_custom_label'] ) ) {
						$renamed_layouts[ $i ] = $row['acf_fc_layout_custom_label'];
					}
					unset( $row['acf_fc_layout_custom_label'] );

					// update row
					$this->update_row( $row, $i, $field, $post_id );

					// append to order
					$new_value[] = $row['acf_fc_layout'];
				}
			}

			// vars
			$old_count = empty( $old_value ) ? 0 : count( $old_value );
			$new_count = empty( $new_value ) ? 0 : count( $new_value );

			// Update layout meta.
			acf_update_metadata_by_field(
				$post_id,
				array(
					'name' => '_' . $field['name'] . '_layout_meta',
				),
				array(
					'disabled' => $disabled_layouts,
					'renamed'  => $renamed_layouts,
				)
			);

			// remove old rows
			if ( $old_count > $new_count ) {

				// loop
				for ( $i = $new_count; $i < $old_count; $i++ ) {
					$this->delete_row( $i, $field, $post_id );
				}
			}

			// save false for empty value
			if ( empty( $new_value ) ) {
				$new_value = '';
			}

			// return
			return $new_value;
		}


		/**
		 * Deletes a layout from a flexible content field.
		 *
		 * @type    function
		 * @date    1/07/2015
		 * @since   5.2.3
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		public function delete_value( $post_id, $key, $field ) {

			// vars
			$old_value = acf_get_metadata_by_field( $post_id, $field['name'] );
			$old_value = is_array( $old_value ) ? $old_value : array();

			// bail early if no rows or no sub fields
			if ( empty( $old_value ) ) {
				return;
			}

			// loop
			foreach ( array_keys( $old_value ) as $i ) {
				$this->delete_row( $i, $field, $post_id );
			}
		}


		/**
		 * This filter is appied to the $field before it is saved to the database
		 *
		 * @type    filter
		 * @since   3.6
		 *
		 * @param  array $field The field array holding all the field options
		 * @return array $field The modified field
		 */
		public function update_field( $field ) {

			// loop
			if ( ! empty( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					unset( $layout['sub_fields'] );
				}
			}

			// return
			return $field;
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    4/04/2014
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function delete_field( $field ) {

			if ( ! empty( $field['layouts'] ) ) {

				// loop through layouts
				foreach ( $field['layouts'] as $layout ) {

					// loop through sub fields
					if ( ! empty( $layout['sub_fields'] ) ) {
						foreach ( $layout['sub_fields'] as $sub_field ) {
							acf_delete_field( $sub_field['ID'] );
						}
						// foreach
					}
					// if
				}
				// foreach
			}
			// if
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

			// vars
			$sub_fields = array();

			if ( ! empty( $field['layouts'] ) ) {

				// loop through layouts
				foreach ( $field['layouts'] as $layout ) {

					// extract sub fields
					$extra = acf_extract_var( $layout, 'sub_fields' );

					// merge
					if ( ! empty( $extra ) ) {
						$sub_fields = array_merge( $sub_fields, $extra );
					}
				}
				// foreach
			}

			// save field to get ID
			$field = acf_update_field( $field );

			// duplicate sub fields
			acf_duplicate_fields( $sub_fields, $field['ID'] );

			return $field;
		}


		/**
		 * Output the layout title for an AJAX response.
		 *
		 * @since 5.3.2
		 */
		public function ajax_layout_title() {

			$options = acf_parse_args(
				$_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				array(
					'post_id'   => 0,
					'i'         => 0,
					'field_key' => '',
					'nonce'     => '',
					'layout'    => '',
					'value'     => array(),
				)
			);

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				die();
			}

			// vars
			$layout = $this->get_layout( $options['layout'], $field );
			if ( ! $layout ) {
				die();
			}

			// title
			$title = $this->get_layout_title( $field, $layout, $options['i'], $options['value'] );

			// echo
			echo acf_esc_html( $title );
			die;
		}

		/**
		 * Get a layout title for a field.
		 *
		 * @param  array   $field  The field array
		 * @param  array   $layout The layout array
		 * @param  integer $i      The order number of the layout
		 * @param  array   $value  The value of the layout
		 * @return string The layout title, optionally filtered.
		 */
		public function get_layout_title( $field, $layout, $i, $value ) {
			$layout = new Layout( $field, $layout, $i, $value );
			return $layout->get_title();
		}

		/**
		 * This function will update clone field settings based on the origional field
		 *
		 * @type    function
		 * @date    28/06/2016
		 * @since   5.3.8
		 *
		 * @param   $clone (array)
		 * @param   $field (array)
		 * @return  $clone
		 */
		public function clone_any_field( $field, $clone_field ) {

			// remove parent_layout
			// - allows a sub field to be rendered as a normal field
			unset( $field['parent_layout'] );

			// attempt to merger parent_layout
			if ( isset( $clone_field['parent_layout'] ) ) {
				$field['parent_layout'] = $clone_field['parent_layout'];
			}

			// return
			return $field;
		}


		/**
		 * Handles preparing the layouts for export.
		 *
		 * @since   5.0.0
		 *
		 * @param  array $field The whole fiel array
		 * @return array The export ready field array.
		 */
		public function prepare_field_for_export( $field ) {

			// loop
			if ( ! empty( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					$layout['sub_fields'] = acf_prepare_fields_for_export( $layout['sub_fields'] );
				}
			}

			// return
			return $field;
		}

		function prepare_any_field_for_export( $field ) {

			// remove parent_layout
			unset( $field['parent_layout'] );

			// return
			return $field;
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    11/03/2014
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		public function prepare_field_for_import( $field ) {

			// Bail early if no layouts
			if ( empty( $field['layouts'] ) ) {
				return $field;
			}

			// Storage for extracted fields.
			$extra = array();

			// Loop over layouts.
			foreach ( $field['layouts'] as &$layout ) {

				// Ensure layout is valid.
				$layout = $this->get_valid_layout( $layout );

				// Extract sub fields.
				$sub_fields = acf_extract_var( $layout, 'sub_fields' );

				// Modify and append sub fields to $extra.
				if ( $sub_fields ) {
					foreach ( $sub_fields as $i => $sub_field ) {

						// Update atttibutes
						$sub_field['parent']        = $field['key'];
						$sub_field['parent_layout'] = $layout['key'];
						$sub_field['menu_order']    = $i;

						// Append to extra.
						$extra[] = $sub_field;
					}
				}
			}

			// Merge extra sub fields.
			if ( $extra ) {
				array_unshift( $extra, $field );
				return $extra;
			}

			// Return field.
			return $field;
		}


		/**
		 * This function will add compatibility for the 'column_width' setting
		 *
		 * @type    function
		 * @date    30/1/17
		 * @since   5.5.6
		 *
		 * @param   $field (array)
		 * @return  $field
		 */
		function validate_any_field( $field ) {

			// width has changed
			if ( isset( $field['column_width'] ) ) {
				$field['wrapper']['width'] = acf_extract_var( $field, 'column_width' );
			}

			// return
			return $field;
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
			$field['button_label'] = acf_translate( $field['button_label'] );

			// loop
			if ( ! empty( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					$layout['label'] = acf_translate( $layout['label'] );
				}
			}

			// return
			return $field;
		}

		/**
		 * Additional validation for the flexible content field when submitted via REST.
		 *
		 * @param  boolean $valid The current validity booleean
		 * @param  integer $value The value of the field
		 * @param  array   $field The field array
		 * @return boolean|WP
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			$param = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
			$data  = array(
				'param' => $param,
				'value' => $value,
			);

			if ( ! is_array( $value ) && is_null( $value ) ) {
				$error = sprintf( __( '%s must be of type array or null.', 'acf' ), $param );
				return new WP_Error( 'rest_invalid_param', $error, $param );
			}

			$layouts_to_update = array_count_values( array_column( $value, 'acf_fc_layout' ) );

			foreach ( $field['layouts'] as $layout ) {
				$num_layouts = isset( $layouts_to_update[ $layout['name'] ] ) ? $layouts_to_update[ $layout['name'] ] : 0;

				if ( '' !== $layout['min'] && $num_layouts < (int) $layout['min'] ) {
					$error = sprintf(
						_n(
							'%1$s must contain at least %2$s %3$s layout.',
							'%1$s must contain at least %2$s %3$s layouts.',
							$layout['min'],
							'acf'
						),
						$param,
						number_format_i18n( $layout['min'] ),
						$layout['name']
					);

					return new WP_Error( 'rest_invalid_param', $error, $data );
				}

				if ( '' !== $layout['max'] && $num_layouts > (int) $layout['max'] ) {
					$error = sprintf(
						_n(
							'%1$s must contain at most %2$s %3$s layout.',
							'%1$s must contain at most %2$s %3$s layouts.',
							$layout['max'],
							'acf'
						),
						$param,
						number_format_i18n( $layout['max'] ),
						$layout['name']
					);

					return new WP_Error( 'rest_invalid_param', $error, $data );
				}
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
					'oneOf' => array(),
				),
			);

			// Loop through layouts building up a schema for each.
			foreach ( $field['layouts'] as $layout ) {
				$layout_schema = array(
					'type'       => 'object',
					'properties' => array(
						'acf_fc_layout' => array(
							'type'     => 'string',
							'required' => true,
							// By using a pattern match against the layout name, data sent in must match an available
							// layout on the flexible field. If it doesn't, a 400 Bad Request response will result.
							'pattern'  => '^' . $layout['name'] . '$',
						),
					),
				);

				foreach ( $layout['sub_fields'] as $sub_field ) {
					if ( $sub_field_schema = acf_get_field_rest_schema( $sub_field ) ) {
						$layout_schema['properties'][ $sub_field['name'] ] = $sub_field_schema;
					}
				}

				$schema['items']['oneOf'][] = $layout_schema;
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
		 * @param mixed          $value
		 * @param integer|string $post_id
		 * @param array          $field
		 * @return array|mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			if ( empty( $value ) || ! is_array( $value ) || empty( $field['layouts'] ) ) {
				return null;
			}

			// Create a map of layout sub fields mapped to layout names.
			foreach ( $field['layouts'] as $layout ) {
				$layouts[ $layout['name'] ] = $layout['sub_fields'];
			}

			// Loop through each layout and within that, each sub field to process sub fields individually.
			foreach ( $value as &$layout ) {
				$name = $layout['acf_fc_layout'];

				if ( empty( $layouts[ $name ] ) ) {
					continue;
				}

				foreach ( $layouts[ $name ] as $sub_field ) {

					// Bail early if the field has no name (tab).
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// Extract the sub field 'field_key'=>'value' pair from the $layout and format it.
					$sub_value = acf_extract_var( $layout, $sub_field['key'] );
					$sub_value = acf_format_value_for_rest( $sub_value, $post_id, $sub_field );

					// Add the sub field value back to the $layout but mapped to the field name instead
					// of the key reference.
					$layout[ $sub_field['name'] ] = $sub_value;
				}
			}

			return $value;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_flexible_content' );
endif; // class_exists check

?>
