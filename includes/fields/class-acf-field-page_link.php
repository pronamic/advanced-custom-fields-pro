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

if ( ! class_exists( 'acf_field_page_link' ) ) :

	class acf_field_page_link extends acf_field {


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
			$this->name          = 'page_link';
			$this->label         = __( 'Page Link', 'acf' );
			$this->category      = 'relational';
			$this->description   = __( 'An interactive dropdown to select one or more posts, pages, custom post type items or archive URLs, with the option to search.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-page-link.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/page-link/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'post_type'      => array(),
				'taxonomy'       => array(),
				'allow_null'     => 0,
				'multiple'       => 0,
				'allow_archives' => 1,
			);

			// extra
			add_action( 'wp_ajax_acf/fields/page_link/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/page_link/query', array( $this, 'ajax_query' ) );
			add_filter( 'acf/conditional_logic/choices', array( $this, 'render_field_page_link_conditional_choices' ), 10, 3 );
		}

		/**
		 * Filters choices in page link conditions.
		 *
		 * @since 6.3
		 *
		 * @param array  $choices           The selected choice.
		 * @param array  $conditional_field The conditional field settings object.
		 * @param string $rule_value        The rule value.
		 * @return array
		 */
		public function render_field_page_link_conditional_choices( $choices, $conditional_field, $rule_value ) {
			if ( ! is_array( $conditional_field ) || $conditional_field['type'] !== 'page_link' ) {
				return $choices;
			}
			if ( ! empty( $rule_value ) ) {
				$post_title = esc_html( get_the_title( $rule_value ) );
				$choices    = array( $rule_value => $post_title );
			}
			return $choices;
		}

		/**
		 * Returns AJAX results for the Page Link field.
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

			if ( ! acf_verify_ajax( $nonce, $key, ! $conditional_logic ) ) {
				die();
			}

			// defaults
			$options = acf_parse_args(
				$_POST,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'paged'     => 1,
					'include'   => '',
				)
			);

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

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				die();
			}

			// update $args
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

			// create tax queries
			if ( ! empty( $field['taxonomy'] ) ) {

				// append to $args
				$args['tax_query'] = array();

				// decode terms
				$taxonomies = acf_decode_taxonomy_terms( $field['taxonomy'] );

				// now create the tax queries
				foreach ( $taxonomies as $taxonomy => $terms ) {
					$args['tax_query'][] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $terms,
					);
				}
			}

			if ( ! empty( $options['include'] ) ) {
				$args['include'] = $options['include'];
			}

			// filters
			$args = apply_filters( 'acf/fields/page_link/query', $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/page_link/query/name=' . $field['name'], $args, $field, $options['post_id'] );
			$args = apply_filters( 'acf/fields/page_link/query/key=' . $field['key'], $args, $field, $options['post_id'] );

			// add archives to $results
			if ( $field['allow_archives'] && $args['paged'] == 1 ) {

				// Generate unique list of URLs.
				$links   = array();
				$links[] = home_url();
				foreach ( $args['post_type'] as $post_type ) {
					$links[] = get_post_type_archive_link( $post_type );
				}
				$links = array_filter( $links );
				$links = array_unique( $links );

				// Convert list into choices.
				$children = array();
				foreach ( $links as $link ) {

					// Ignore if search does not match.
					if ( $is_search && stripos( $link, $s ) === false ) {
						continue;
					}
					$children[] = array(
						'id'   => $link,
						'text' => $link,
					);
				}
				if ( $children ) {
					$results[] = array(
						'text'     => __( 'Archives', 'acf' ),
						'children' => $children,
					);
				}
			}

			// If there is an include set, we will unset search to avoid attempting to further filter by the search term.
			if ( isset( $args['include'] ) ) {
				unset( $args['s'] );
			}

			// get posts grouped by post type
			$groups = acf_get_grouped_posts( $args );

			// loop
			if ( ! empty( $groups ) ) {
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
						$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'], $is_search );
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
			}

			// return
			acf_send_ajax_results(
				array(
					'results' => $results,
					'limit'   => $args['posts_per_page'],
				)
			);
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

			// filters
			$title = apply_filters( 'acf/fields/page_link/result', $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/page_link/result/name=' . $field['_name'], $title, $post, $field, $post_id );
			$title = apply_filters( 'acf/fields/page_link/result/key=' . $field['key'], $title, $post, $field, $post_id );

			// return
			return $title;
		}


		/**
		 * This function will return an array of posts for a given field value
		 *
		 * @type    function
		 * @date    13/06/2014
		 * @since   5.0.0
		 *
		 * @param   $value (array)
		 * @return  $value
		 */
		function get_posts( $value, $field ) {

			// force value to array
			$value = acf_get_array( $value );

			// get selected post ID's
			$post__in = array();

			foreach ( $value as $k => $v ) {
				if ( is_numeric( $v ) ) {

					// append to $post__in
					$post__in[] = (int) $v;
				}
			}

			// bail early if no posts
			if ( empty( $post__in ) ) {
				return $value;
			}

			// get posts
			$posts = acf_get_posts(
				array(
					'post__in'  => $post__in,
					'post_type' => $field['post_type'],
				)
			);

			// override value with post
			$return = array();

			// append to $return
			foreach ( $value as $k => $v ) {
				if ( is_numeric( $v ) ) {

					// extract first post
					$post = array_shift( $posts );

					// append
					if ( $post ) {
						$return[] = $post;
					}
				} else {
					$return[] = $v;
				}
			}

			// return
			return $return;
		}


		/**
		 * Renders the Page Link field.
		 *
		 * @since 3.6
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		public function render_field( $field ) {
			// Change Field into a select
			$field['type']    = 'select';
			$field['ui']      = 1;
			$field['ajax']    = 1;
			$field['choices'] = array();
			$field['nonce']   = wp_create_nonce( 'acf_field_' . $this->name . '_' . $field['key'] );

			// populate choices if value exists
			if ( ! empty( $field['value'] ) ) {

				// get posts
				$posts = $this->get_posts( $field['value'], $field );

				// set choices
				if ( ! empty( $posts ) ) {
					foreach ( array_keys( $posts ) as $i ) {

						// vars
						$post = acf_extract_var( $posts, $i );

						if ( is_object( $post ) ) {

							// append to choices
							$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );
						} else {

							// append to choices
							$field['choices'][ $post ] = $post;
						}
					}
				}
			}

			// render
			acf_render_field( $field );
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
					'label'        => __( 'Allow Archives URLs', 'acf' ),
					'instructions' => '',
					'name'         => 'allow_archives',
					'type'         => 'true_false',
					'ui'           => 1,
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
		function render_field_validation_settings( $field ) {
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
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
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

			// ACF4 null
			if ( $value === 'null' ) {
				return false;
			}

			// bail early if no value
			if ( empty( $value ) ) {
				return $value;
			}

			// get posts
			$value = $this->get_posts( $value, $field );

			// set choices
			foreach ( array_keys( $value ) as $i ) {

				// vars
				$post = acf_extract_var( $value, $i );

				// convert $post to permalink
				if ( is_object( $post ) ) {
					$post = get_permalink( $post );
				}

				// append back to $value
				$value[ $i ] = $post;
			}

			// convert back from array if neccessary
			if ( ! $field['multiple'] ) {
				$value = array_shift( $value );
			}

			// return value
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
		 * @param   $post_id - the post_id of which the value will be saved
		 * @param   $field - the field array holding all the field options
		 *
		 * @return  $value - the modified value
		 */
		function update_value( $value, $post_id, $field ) {

			// Bail early if no value.
			if ( empty( $value ) ) {
				return $value;
			}

			// Format array of values.
			// - ensure each value is an id.
			// - Parse each id as string for SQL LIKE queries.
			if ( acf_is_sequential_array( $value ) ) {
				$value = array_map( 'acf_maybe_idval', $value );
				$value = array_map( 'strval', $value );

				// Parse single value for id.
			} else {
				$value = acf_maybe_idval( $value );
			}

			// Return value.
			return $value;
		}

		/**
		 * Validates page link fields updated via the REST API.
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
					'type' => array( 'integer' ),
				),
			);

			if ( empty( $field['allow_null'] ) ) {
				$schema['minItems'] = 1;
			}

			if ( ! empty( $field['allow_archives'] ) ) {
				$schema['type'][]          = 'string';
				$schema['items']['type'][] = 'string';
			}

			if ( empty( $field['multiple'] ) ) {
				$schema['maxItems'] = 1;
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
	acf_register_field_type( 'acf_field_page_link' );
endif; // class_exists check
