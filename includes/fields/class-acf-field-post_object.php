<?php

if ( ! class_exists( 'acf_field_post_object' ) ) :

	class acf_field_post_object extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @since   5.0.0
		 */
		public function initialize() {
			$this->name          = 'post_object';
			$this->label         = __( 'Post Object', 'acf' );
			$this->category      = 'relational';
			$this->description   = __( 'An interactive and customizable UI for picking one or many posts, pages or post type items with the option to search. ', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-post-object.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/post-object/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'post_type'            => array(),
				'taxonomy'             => array(),
				'allow_null'           => 0,
				'multiple'             => 0,
				'return_format'        => 'object',
				'ui'                   => 1,
				'bidirectional_target' => array(),
			);

			// extra
			add_action( 'wp_ajax_acf/fields/post_object/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/post_object/query', array( $this, 'ajax_query' ) );
			add_filter( 'acf/conditional_logic/choices', array( $this, 'render_field_post_object_conditional_choices' ), 10, 3 );
		}

		/**
		 * Filters choices in post object conditions.
		 *
		 * @since 6.3
		 *
		 * @param array  $choices           The selected choice.
		 * @param array  $conditional_field The conditional field settings object.
		 * @param string $rule_value        The rule value.
		 * @return array
		 */
		public function render_field_post_object_conditional_choices( $choices, $conditional_field, $rule_value ) {
			if ( ! is_array( $conditional_field ) || $conditional_field['type'] !== 'post_object' ) {
				return $choices;
			}
			if ( ! empty( $rule_value ) ) {
				$post_title = get_the_title( $rule_value );
				$choices    = array( $rule_value => $post_title );
			}
			return $choices;
		}

		/**
		 * AJAX query handler for post object fields.
		 *
		 * @since   5.0.0
		 */
		public function ajax_query() {
			if ( ! acf_verify_ajax() ) {
				die();
			}

			// get choices
			$response = $this->get_ajax_query( $_POST );

			// return
			acf_send_ajax_results( $response );
		}


		/**
		 * This function will return an array of data formatted for use in a select2 AJAX response
		 *
		 * @since   5.0.9
		 *
		 * @param   array $options The options being queried for the ajax request.
		 * @return  array The AJAX response array.
		 */
		public function get_ajax_query( $options = array() ) {

			// defaults
			$options = acf_parse_args(
				$options,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'paged'     => 1,
					'include'   => '',
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
			$args['paged']          = $options['paged'];

			// search
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = wp_unslash( strval( $options['s'] ) );

				// update vars
				$args['s'] = $s;
				$is_search = true;
			}

			if ( ! empty( $options['include'] ) ) {
				$args['include'] = $options['include'];
			}

			// post_type
			if ( ! empty( $field['post_type'] ) ) {
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

			// If there is an include set, we will unset search to avoid attempting to further filter by the search term.
			if ( isset( $args['include'] ) ) {
				unset( $args['s'] );
			}

			// taxonomy
			if ( ! empty( $field['taxonomy'] ) ) {

				// vars
				$terms = acf_decode_taxonomy_terms( $field['taxonomy'] );

				// append to $args
				$args['tax_query'] = array();

				// now create the tax queries
				foreach ( $terms as $k => $v ) {
					$args['tax_query'][] = array(
						'taxonomy' => $k,
						'field'    => 'slug',
						'terms'    => $v,
					);
				}
			}

			// filters
			$args = apply_filters( 'acf/fields/post_object/query', $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/post_object/query/name=' . $field['name'], $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/post_object/query/key=' . $field['key'], $args, $field, $options['post_id'] );

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
					$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'], $is_search, true );
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

			// optgroup or single
			$post_type = acf_get_array( $args['post_type'] );
			if ( count( $post_type ) == 1 ) {
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
		 * @since   5.4.0
		 *
		 * @param   mixed  $id   The ID of the post result.
		 * @param   string $text The text for the response item.
		 * @return  array The combined result array.
		 */
		public function get_post_result( $id, $text ) {

			// vars
			$result = array(
				'id'   => $id,
				'text' => $text,
			);

			// look for parent
			$search = '| ' . __( 'Parent', 'acf' ) . ':';
			$pos    = strpos( $text, $search );

			if ( $pos !== false ) {
				$result['description'] = substr( $text, $pos + 2 );
				$result['text']        = substr( $text, 0, $pos );
			}

			// return
			return $result;
		}


		/**
		 * This function post object's filtered output post title
		 *
		 * @since   5.0.0
		 *
		 * @param   WP_Post $post      The WordPress post.
		 * @param   array   $field     The field being output.
		 * @param   integer $post_id   The post_id to which this value is saved to.
		 * @param   integer $is_search An int-as-boolean value for whether we're performing a search.
		 * @param   boolean $unescape  Should we return an unescaped post title.
		 * @return  string A potentially user filtered post title for the post, which may contain unsafe HTML.
		 */
		public function get_post_title( $post, $field, $post_id = 0, $is_search = 0, $unescape = false ) {

			// get post_id
			if ( ! $post_id ) {
				$post_id = acf_get_form_data( 'post_id' );
			}

			// vars
			$title = acf_get_post_title( $post, $is_search );

			// unescape for select2 output which handles the escaping.
			if ( $unescape ) {
				$title = html_entity_decode( $title );
			}

			// filters
			$title = apply_filters( 'acf/fields/post_object/result', $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/post_object/result/name=' . $field['_name'], $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/post_object/result/key=' . $field['key'], $title, $post, $field, $post_id );

			// return untrusted output.
			return $title;
		}


		/**
		 * Create the HTML interface for the post object field.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field( $field ) {

			// Change Field into a select
			$field['type']    = 'select';
			$field['ui']      = 1;
			$field['ajax']    = 1;
			$field['choices'] = array();

			// load posts
			$posts = $this->get_posts( $field['value'], $field );

			if ( $posts ) {
				foreach ( array_keys( $posts ) as $i ) {

					// vars
					$post = acf_extract_var( $posts, $i );

					// append to choices
					$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );
				}
			}

			// render
			acf_render_field( $field );
		}


		/**
		 * Create extra options for post object field. This is rendered when editing.
		 * The value of $field['name'] can be used (like below) to save extra data to the $field.
		 *
		 * @since 3.6
		 *
		 * @param array $field An array holding all the field's data.
		 */
		public function render_field_settings( $field ) {
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

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Select Multiple', 'acf' ),
					'instructions' => 'Allow content editors to select multiple values',
					'name'         => 'multiple',
					'type'         => 'true_false',
					'ui'           => 1,
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
		public function render_field_validation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Allow Null', 'acf' ),
					'instructions' => '',
					'name'         => 'allow_null',
					'type'         => 'true_false',
					'ui'           => 1,
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
		 * This filter is applied to the $value after it is loaded from the db
		 *
		 * @since   3.6
		 *
		 * @param  mixed $value   The value found in the database
		 * @param  mixed $post_id The post_id from which the value was loaded
		 * @param  array $field   The field array holding all the field options
		 * @return mixed $value
		 */
		public function load_value( $value, $post_id, $field ) {

			// ACF4 null
			if ( $value === 'null' ) {
				return false;
			}

			// return
			return $value;
		}


		/**
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @since 3.6
		 *
		 * @param  mixed $value   The value found in the database
		 * @param  mixed $post_id The post_id from which the value was loaded
		 * @param  array $field   The field array holding all the field options
		 * @return mixed $value
		 */
		public function format_value( $value, $post_id, $field ) {
			$value = acf_get_numeric( $value );

			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// load posts if needed
			if ( $field['return_format'] == 'object' ) {
				$value = $this->get_posts( $value, $field );
			}

			// convert back from array if neccessary
			if ( ! $field['multiple'] && is_array( $value ) ) {
				$value = current( $value );
			}

			// return value
			return $value;
		}


		/**
		 * Filters the field value before it is saved into the database.
		 *
		 * @since 3.6
		 *
		 * @param  mixed   $value   The value which will be saved in the database.
		 * @param  integer $post_id The post_id of which the value will be saved.
		 * @param  array   $field   The field array holding all the field options.
		 * @return mixed   $value   The modified value.
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

			return $value;
		}


		/**
		 * This function will return an array of posts for a given field value
		 *
		 * @since 5.0
		 *
		 * @param  mixed $value The value of the field.
		 * @param  array $field The field array holding all the field options.
		 * @return array $value An array of post objects.
		 */
		public function get_posts( $value, $field ) {

			// numeric
			$value = acf_get_numeric( $value );

			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// get posts
			$posts = acf_get_posts(
				array(
					'post__in'  => $value,
					'post_type' => $field['post_type'],
				)
			);

			// return
			return $posts;
		}

		/**
		 * Validates post object fields updated via the REST API.
		 *
		 * @since 5.11
		 *
		 * @param  boolean $valid The current validity booleean
		 * @param  integer $value The value of the field
		 * @param  array   $field The field array
		 * @return boolean|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			if ( is_null( $value ) ) {
				return $valid;
			}

			$param = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
			$data  = array( 'param' => $param );
			$value = is_array( $value ) ? $value : array( $value );

			$invalid_posts    = array();
			$post_type_errors = array();
			$taxonomy_errors  = array();

			foreach ( $value as $post_id ) {
				if ( is_string( $post_id ) ) {
					continue;
				}

				$post_type = get_post_type( $post_id );
				if ( ! $post_type ) {
					$invalid_posts[] = $post_id;
					continue;
				}

				if (
					is_array( $field['post_type'] ) &&
					! empty( $field['post_type'] ) &&
					! in_array( $post_type, $field['post_type'] )
				) {
					$post_type_errors[] = $post_id;
				}

				if ( is_array( $field['taxonomy'] ) && ! empty( $field['taxonomy'] ) ) {
					$found = false;
					foreach ( $field['taxonomy'] as $taxonomy_term ) {
						$decoded = acf_decode_taxonomy_term( $taxonomy_term );
						if ( $decoded && is_object_in_term( $post_id, $decoded['taxonomy'], $decoded['term'] ) ) {
							$found = true;
							break;
						}
					}

					if ( ! $found ) {
						$taxonomy_errors[] = $post_id;
					}
				}
			}

			if ( count( $invalid_posts ) ) {
				$error         = sprintf(
					__( '%1$s must have a valid post ID.', 'acf' ),
					$param
				);
				$data['value'] = $invalid_posts;
				return new WP_Error( 'rest_invalid_param', $error, $data );
			}

			if ( count( $post_type_errors ) ) {
				$error         = sprintf(
					_n(
						'%1$s must be of post type %2$s.',
						'%1$s must be of one of the following post types: %2$s',
						count( $field['post_type'] ),
						'acf'
					),
					$param,
					count( $field['post_type'] ) > 1 ? implode( ', ', $field['post_type'] ) : $field['post_type'][0]
				);
				$data['value'] = $post_type_errors;

				return new WP_Error( 'rest_invalid_param', $error, $data );
			}

			if ( count( $taxonomy_errors ) ) {
				$error         = sprintf(
					_n(
						'%1$s must have term %2$s.',
						'%1$s must have one of the following terms: %2$s',
						count( $field['taxonomy'] ),
						'acf'
					),
					$param,
					count( $field['taxonomy'] ) > 1 ? implode( ', ', $field['taxonomy'] ) : $field['taxonomy'][0]
				);
				$data['value'] = $taxonomy_errors;

				return new WP_Error( 'rest_invalid_param', $error, $data );
			}

			return $valid;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @since 5.11
		 *
		 * @param array $field The field array.
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

			if ( empty( $field['multiple'] ) ) {
				$schema['maxItems'] = 1;
			}

			return $schema;
		}

		/**
		 * REST link attributes generator for this field.
		 *
		 * @since 5.11
		 * @see \acf_field::get_rest_links()
		 *
		 * @param mixed          $value   The raw (unformatted) field value.
		 * @param integer|string $post_id The post ID being queried.
		 * @param array          $field   The field array.
		 * @return array
		 */
		public function get_rest_links( $value, $post_id, array $field ) {
			$links = array();

			if ( empty( $value ) ) {
				return $links;
			}

			foreach ( (array) $value as $object_id ) {
				if ( ! $post_type = get_post_type( $object_id ) ) {
					continue;
				}

				if ( ! $post_type_object = get_post_type_object( $post_type ) ) {
					continue;
				}

				$rest_base = acf_get_object_type_rest_base( $post_type_object );
				$links[]   = array(
					'rel'        => $post_type_object->name === 'attachment' ? 'acf:attachment' : 'acf:post',
					'href'       => rest_url( sprintf( '/wp/v2/%s/%s', $rest_base, $object_id ) ),
					'embeddable' => true,
				);
			}

			return $links;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @since 5.11
		 *
		 * @param mixed          $value   The raw (unformatted) field value.
		 * @param integer|string $post_id The post ID being queried.
		 * @param array          $field   The field array.
		 * @return mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			return acf_format_numerics( $value );
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_post_object' );
endif; // class_exists check
