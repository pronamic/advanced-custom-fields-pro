<?php

if ( ! class_exists( 'acf_field_relationship' ) ) :

	class acf_field_relationship extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.0.0
		 */
		public function initialize() {
			$this->name          = 'relationship';
			$this->label         = __( 'Relationship', 'acf' );
			$this->category      = 'relational';
			$this->description   = __( 'A dual-column interface to select one or more posts, pages, or custom post type items to create a relationship with the item that you\'re currently editing. Includes options to search and filter.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-relationship.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/relationship/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'post_type'            => array(),
				'taxonomy'             => array(),
				'min'                  => 0,
				'max'                  => 0,
				'filters'              => array( 'search', 'post_type', 'taxonomy' ),
				'elements'             => array(),
				'return_format'        => 'object',
				'bidirectional_target' => array(),
			);
			add_filter( 'acf/conditional_logic/choices', array( $this, 'render_field_relation_conditional_choices' ), 10, 3 );

			// extra
			add_action( 'wp_ajax_acf/fields/relationship/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/relationship/query', array( $this, 'ajax_query' ) );
		}

		/**
		 * Filters choices in relation conditions.
		 *
		 * @since 6.3
		 *
		 * @param array  $choices           The selected choice.
		 * @param array  $conditional_field The conditional field settings object.
		 * @param string $rule_value        The rule value.
		 * @return array
		 */
		public function render_field_relation_conditional_choices( $choices, $conditional_field, $rule_value ) {
			if ( ! is_array( $conditional_field ) || $conditional_field['type'] !== 'relationship' ) {
				return $choices;
			}
			if ( ! empty( $rule_value ) ) {
				$post_title = get_the_title( $rule_value );
				$choices    = array( $rule_value => $post_title );
			}
			return $choices;
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

			// localize
			acf_localize_text(
				array(
					// 'Minimum values reached ( {min} values )' => __('Minimum values reached ( {min} values )', 'acf'),
					'Maximum values reached ( {max} values )' => __( 'Maximum values reached ( {max} values )', 'acf' ),
					'Loading'          => __( 'Loading', 'acf' ),
					'No matches found' => __( 'No matches found', 'acf' ),
				)
			);
		}

		/**
		 * Returns AJAX results for the Relationship field.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function ajax_query() {
			$nonce             = acf_request_arg( 'nonce', '' );
			$key               = acf_request_arg( 'field_key', '' );
			$conditional_logic = (bool) acf_request_arg( 'conditional_logic', false );

			if ( $conditional_logic ) {
				if ( ! acf_current_user_can_admin() ) {
					die();
				}

				// Use the standard ACF admin nonce.
				$nonce = '';
				$key   = '';
			}

			if ( ! acf_verify_ajax( $nonce, $key ) ) {
				die();
			}

			acf_send_ajax_results( $this->get_ajax_query( $_POST ) );
		}

		/**
		 * This function will return an array of data formatted for use in a select2 AJAX response
		 *
		 * @since   5.0.9
		 *
		 * @param array $options An array of options for the query.
		 * @return array
		 */
		public function get_ajax_query( $options = array() ) {
			// defaults
			$options = wp_parse_args(
				$options,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'paged'     => 1,
					'post_type' => '',
					'include'   => '',
					'taxonomy'  => '',
				)
			);

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}

			// vars
			$results   = array();
			$args      = array();
			$s         = false;
			$is_search = false;

			// paged
			$args['posts_per_page'] = 20;
			$args['paged']          = intval( $options['paged'] );

			// search
			if ( $options['s'] !== '' && empty( $options['include'] ) ) {
				// strip slashes (search may be integer)
				$s = wp_unslash( strval( $options['s'] ) );

				// update vars
				$args['s'] = $s;
				$is_search = true;
			}

			// post_type
			if ( ! empty( $options['post_type'] ) ) {
				$args['post_type'] = acf_get_array( $options['post_type'] );
			} elseif ( ! empty( $field['post_type'] ) ) {
				$args['post_type'] = acf_get_array( $field['post_type'] );
			} else {
				$args['post_type'] = acf_get_post_types();
			}

			// post status
			if ( ! empty( $options['post_status'] ) ) {
				$args['post_status'] = acf_get_array( $options['post_status'] );
			} elseif ( ! empty( $field['post_status'] ) ) {
				$args['post_status'] = acf_get_array( $field['post_status'] );
			}

			// taxonomy
			if ( ! empty( $options['taxonomy'] ) ) {

				// vars
				$term = acf_decode_taxonomy_term( $options['taxonomy'] );

				// tax query
				$args['tax_query'] = array();

				// append
				$args['tax_query'][] = array(
					'taxonomy' => $term['taxonomy'],
					'field'    => 'slug',
					'terms'    => $term['term'],
				);
			} elseif ( ! empty( $field['taxonomy'] ) ) {

				// vars
				$terms = acf_decode_taxonomy_terms( $field['taxonomy'] );

				// append to $args
				$args['tax_query'] = array(
					'relation' => 'OR',
				);

				// now create the tax queries
				foreach ( $terms as $k => $v ) {
					$args['tax_query'][] = array(
						'taxonomy' => $k,
						'field'    => 'slug',
						'terms'    => $v,
					);
				}
			}

			if ( ! empty( $options['include'] ) ) {
				// If we have an include, we need to return only the selected posts.
				$args['post__in'] = array( $options['include'] );
			}

			// filters
			$args = apply_filters( 'acf/fields/relationship/query', $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/relationship/query/name=' . $field['name'], $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/relationship/query/key=' . $field['key'], $args, $field, $options['post_id'] );

			// get posts grouped by post type
			$groups = acf_get_grouped_posts( $args );

			// bail early if no posts
			if ( empty( $groups ) ) {
				return false;
			}

			// loop
			foreach ( array_keys( $groups ) as $group_title ) {

				// vars
				$posts = acf_extract_var( $groups, $group_title );

				// data
				$data = array(
					'text'     => $group_title,
					'children' => array(),
				);

				// convert post objects to post titles
				foreach ( array_keys( $posts ) as $post_id ) {
					$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'] );
				}

				// order posts by search
				if ( $is_search && empty( $args['orderby'] ) && isset( $args['s'] ) ) {
					$posts = acf_order_by_search( $posts, $args['s'] );
				}

				// append to $data
				foreach ( array_keys( $posts ) as $post_id ) {
					$data['children'][] = $this->get_post_result( $post_id, $posts[ $post_id ] );
				}

				// append to $results
				$results[] = $data;
			}

			// add as optgroup or results
			if ( count( $args['post_type'] ) == 1 ) {
				$results = $results[0]['children'];
			}

			// vars
			$response = array(
				'results' => $results,
				'limit'   => $args['posts_per_page'],
			);

			// return
			return $response;
		}

		/**
		 * This function will return an array containing id, text and maybe description data
		 *
		 * @type    function
		 * @date    7/07/2016
		 * @since   5.4.0
		 *
		 * @param   $id (mixed)
		 * @param   $text (string)
		 * @return  (array)
		 */
		function get_post_result( $id, $text ) {

			// vars
			$result = array(
				'id'   => $id,
				'text' => $text,
			);

			// return
			return $result;
		}


		/**
		 * This function returns the HTML for a result
		 *
		 * @type    function
		 * @date    1/11/2013
		 * @since   5.0.0
		 *
		 * @param   $post (object)
		 * @param   $field (array)
		 * @param   $post_id (int) the post_id to which this value is saved to
		 * @return  (string)
		 */
		function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {

			// get post_id
			if ( ! $post_id ) {
				$post_id = acf_get_form_data( 'post_id' );
			}

			// vars
			$title = acf_get_post_title( $post, $is_search );

			// featured_image
			if ( acf_in_array( 'featured_image', $field['elements'] ) ) {

				// vars
				$class     = 'thumbnail';
				$thumbnail = acf_get_post_thumbnail( $post->ID, array( 17, 17 ) );

				// icon
				if ( $thumbnail['type'] == 'icon' ) {
					$class .= ' -' . $thumbnail['type'];
				}

				// append
				$title = '<div class="' . $class . '">' . $thumbnail['html'] . '</div>' . $title;
			}

			// filters
			$title = apply_filters( 'acf/fields/relationship/result', $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/relationship/result/name=' . $field['_name'], $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/relationship/result/key=' . $field['key'], $title, $post, $field, $post_id );

			// return
			return $title;
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
			$post_type = acf_get_array( $field['post_type'] );
			$taxonomy  = acf_get_array( $field['taxonomy'] );
			$filters   = acf_get_array( $field['filters'] );

			// filters
			$filter_count             = count( $filters );
			$filter_post_type_choices = array();
			$filter_taxonomy_choices  = array();

			// post_type filter
			if ( in_array( 'post_type', $filters ) ) {
				$filter_post_type_choices = array(
					'' => __( 'Select post type', 'acf' ),
				) + acf_get_pretty_post_types( $post_type );
			}

			// taxonomy filter
			if ( in_array( 'taxonomy', $filters ) ) {
				$term_choices            = array();
				$filter_taxonomy_choices = array(
					'' => __( 'Select taxonomy', 'acf' ),
				);

				// check for specific taxonomy setting
				if ( $taxonomy ) {
					$terms        = acf_get_encoded_terms( $taxonomy );
					$term_choices = acf_get_choices_from_terms( $terms, 'slug' );

					// if no terms were specified, find all terms
				} else {

					// restrict taxonomies by the post_type selected
					$term_args = array();
					if ( $post_type ) {
						$term_args['taxonomy'] = acf_get_taxonomies(
							array(
								'post_type' => $post_type,
							)
						);
					}

					// get terms
					$terms        = acf_get_grouped_terms( $term_args );
					$term_choices = acf_get_choices_from_grouped_terms( $terms, 'slug' );
				}

				// append term choices
				$filter_taxonomy_choices = $filter_taxonomy_choices + $term_choices;
			}

			// div attributes
			$atts = array(
				'id'             => $field['id'],
				'class'          => "acf-relationship {$field['class']}",
				'data-min'       => $field['min'],
				'data-max'       => $field['max'],
				'data-s'         => '',
				'data-paged'     => 1,
				'data-post_type' => '',
				'data-taxonomy'  => '',
				'data-nonce'     => wp_create_nonce( $field['key'] ),
			);

			?>
<div <?php echo acf_esc_attrs( $atts ); ?>>
	
			<?php
			acf_hidden_input(
				array(
					'name'  => $field['name'],
					'value' => '',
				)
			);
			?>
	
			<?php

			/* filters */
			if ( $filter_count ) :
				?>
	<div class="filters -f<?php echo esc_attr( $filter_count ); ?>">
				<?php

				/* search */
				if ( in_array( 'search', $filters ) ) :
					?>
		<div class="filter -search">
					<?php
					acf_text_input(
						array(
							'placeholder' => __( 'Search...', 'acf' ),
							'data-filter' => 's',
						)
					);
					?>
		</div>
					<?php
			endif;

				/* post_type */
				if ( in_array( 'post_type', $filters ) ) :
					?>
		<div class="filter -post_type">
					<?php
					acf_select_input(
						array(
							'choices'     => $filter_post_type_choices,
							'data-filter' => 'post_type',
						)
					);
					?>
		</div>
					<?php
			endif;

				/* post_type */
				if ( in_array( 'taxonomy', $filters ) ) :
					?>
		<div class="filter -taxonomy">
					<?php
					acf_select_input(
						array(
							'choices'     => $filter_taxonomy_choices,
							'data-filter' => 'taxonomy',
						)
					);
					?>
		</div>
				<?php endif; ?>		
	</div>
			<?php endif; ?>
	
	<div class="selection">
		<div class="choices">
			<ul class="acf-bl list choices-list"></ul>
		</div>
		<div class="values">
			<ul class="acf-bl list values-list">
				<?php
				if ( ! empty( $field['value'] ) ) :

					// get posts
					$posts = acf_get_posts(
						array(
							'post__in'  => $field['value'],
							'post_type' => $field['post_type'],
						)
					);

						// loop
					foreach ( $posts as $post ) :
						?>
					<li>
						<?php
							acf_hidden_input(
								array(
									'name'  => $field['name'] . '[]',
									'value' => $post->ID,
								)
							);
						?>
						<span tabindex="0" data-id="<?php echo esc_attr( $post->ID ); ?>" class="acf-rel-item acf-rel-item-remove">
								<?php echo acf_esc_html( $this->get_post_title( $post, $field ) ); ?>
							<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>
						</span>
					</li>
						<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
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
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filter by Post Type', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'post_type',
					'choices'      => acf_get_pretty_post_types(),
					'multiple'     => 1,
					'ui'           => 1,
					'allow_null'   => 1,
					'placeholder'  => __( 'All post types', 'acf' ),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filter by Post Status', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'post_status',
					'choices'      => acf_get_pretty_post_statuses(),
					'multiple'     => 1,
					'ui'           => 1,
					'allow_null'   => 1,
					'placeholder'  => __( 'Any post status', 'acf' ),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filter by Taxonomy', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'taxonomy',
					'choices'      => acf_get_taxonomy_terms(),
					'multiple'     => 1,
					'ui'           => 1,
					'allow_null'   => 1,
					'placeholder'  => __( 'All taxonomies', 'acf' ),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filters', 'acf' ),
					'instructions' => '',
					'type'         => 'checkbox',
					'name'         => 'filters',
					'choices'      => array(
						'search'    => __( 'Search', 'acf' ),
						'post_type' => __( 'Post Type', 'acf' ),
						'taxonomy'  => __( 'Taxonomy', 'acf' ),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf' ),
					'instructions' => '',
					'type'         => 'radio',
					'name'         => 'return_format',
					'choices'      => array(
						'object' => __( 'Post Object', 'acf' ),
						'id'     => __( 'Post ID', 'acf' ),
					),
					'layout'       => 'horizontal',
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
			$field['min'] = empty( $field['min'] ) ? '' : $field['min'];
			$field['max'] = empty( $field['max'] ) ? '' : $field['max'];

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Minimum Posts', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'min',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum Posts', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'max',
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
					'label'        => __( 'Elements', 'acf' ),
					'instructions' => __( 'Selected elements will be displayed in each result', 'acf' ),
					'type'         => 'checkbox',
					'name'         => 'elements',
					'choices'      => array(
						'featured_image' => __( 'Featured Image', 'acf' ),
					),
				)
			);
		}

		/**
		 * Renders the field settings used in the "Advanced" tab.
		 *
		 * @since 6.2
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		public function render_field_advanced_settings( $field ) {
			acf_render_bidirectional_field_settings( $field );
		}

		/**
		 * This filter is applied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value (mixed) the value which was loaded from the database
		 * @param   $post_id (mixed) the post_id from which the value was loaded
		 * @param   $field (array) the field array holding all the field options
		 *
		 * @return  $value (mixed) the modified value
		 */
		function format_value( $value, $post_id, $field ) {

			// bail early if no value
			if ( empty( $value ) ) {
				return $value;
			}

			// force value to array
			$value = acf_get_array( $value );

			// convert to int
			$value = array_map( 'intval', $value );

			// load posts if needed
			if ( $field['return_format'] == 'object' ) {

				// get posts
				$value = acf_get_posts(
					array(
						'post__in'  => $value,
						'post_type' => $field['post_type'],
					)
				);
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
		function validate_value( $valid, $value, $field, $input ) {

			// default
			if ( empty( $value ) || ! is_array( $value ) ) {
				$value = array();
			}

			// min
			if ( count( $value ) < $field['min'] ) {
				$valid = _n( '%1$s requires at least %2$s selection', '%1$s requires at least %2$s selections', $field['min'], 'acf' );
				$valid = sprintf( $valid, $field['label'], $field['min'] );
			}

			// return
			return $valid;
		}


		/**
		 * Filters the field value before it is saved into the database.
		 *
		 * @since 3.6
		 *
		 * @param mixed   $value   The value which will be saved in the database.
		 * @param integer $post_id The post_id of which the value will be saved.
		 * @param array   $field   The field array holding all the field options.
		 *
		 * @return mixed $value The modified value.
		 */
		public function update_value( $value, $post_id, $field ) {

			// Bail early if no value.
			if ( empty( $value ) ) {
				acf_update_bidirectional_values( array(), $post_id, $field );
				return $value;
			}

			// Format array of values.
			// - ensure each value is an id.
			// - Parse each id as string for SQL LIKE queries.
			if ( acf_is_sequential_array( $value ) ) {
				$value = array_map( 'acf_idval', $value );
				$value = array_map( 'strval', $value );

				// Parse single value for id.
			} else {
				$value = acf_idval( $value );
			}

			acf_update_bidirectional_values( acf_get_array( $value ), $post_id, $field );

			// Return value.
			return $value;
		}

		/**
		 * Validates relationship fields updated via the REST API.
		 *
		 * @param  boolean $valid The current validity booleean
		 * @param  integer $value The value of the field
		 * @param  array   $field The field array
		 * @return boolean|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			return acf_get_field_type( 'post_object' )->validate_rest_value( $valid, $value, $field );
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'integer', 'array', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'type' => 'integer',
				),
			);

			if ( empty( $field['allow_null'] ) ) {
				$schema['minItems'] = 1;
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
		 * @see \acf_field::get_rest_links()
		 * @param mixed          $value   The raw (unformatted) field value.
		 * @param integer|string $post_id
		 * @param array          $field
		 * @return array
		 */
		public function get_rest_links( $value, $post_id, array $field ) {
			$links = array();

			if ( empty( $value ) ) {
				return $links;
			}

			foreach ( (array) $value as $object_id ) {
				if ( ! $post_type = get_post_type( $object_id ) or ! $post_type = get_post_type_object( $post_type ) ) {
					continue;
				}
				$rest_base = acf_get_object_type_rest_base( $post_type );
				$links[]   = array(
					'rel'        => $post_type->name === 'attachment' ? 'acf:attachment' : 'acf:post',
					'href'       => rest_url( sprintf( '/wp/v2/%s/%s', $rest_base, $object_id ) ),
					'embeddable' => true,
				);
			}

			return $links;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed          $value
		 * @param string|integer $post_id
		 * @param array          $field
		 * @return mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			return acf_format_numerics( $value );
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_relationship' );
endif; // class_exists check

?>
