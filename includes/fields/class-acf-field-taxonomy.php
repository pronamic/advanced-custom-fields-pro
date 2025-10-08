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

if ( ! class_exists( 'acf_field_taxonomy' ) ) :

	class acf_field_taxonomy extends acf_field {

		// vars
		var $save_post_terms = array();


		/**
		 * This function will setup the field type data
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.0.0
		 */
		public function initialize() {
			$this->name          = 'taxonomy';
			$this->label         = __( 'Taxonomy', 'acf' );
			$this->category      = 'relational';
			$this->description   = __( 'Allows the selection of one or more taxonomy terms based on the criteria and options specified in the fields settings.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-taxonomy.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/taxonomy/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'taxonomy'             => 'category',
				'field_type'           => 'checkbox',
				'multiple'             => 0,
				'allow_null'           => 0,
				'return_format'        => 'id',
				'add_term'             => 1, // 5.2.3
				'load_terms'           => 0, // 5.2.7
				'save_terms'           => 0, // 5.2.7
				'bidirectional_target' => array(),
			);

			// Register filter variations.
			acf_add_filter_variations( 'acf/fields/taxonomy/query', array( 'name', 'key' ), 1 );
			acf_add_filter_variations( 'acf/fields/taxonomy/result', array( 'name', 'key' ), 2 );

			// ajax
			add_action( 'wp_ajax_acf/fields/taxonomy/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/taxonomy/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_acf/fields/taxonomy/add_term', array( $this, 'ajax_add_term' ) );
			add_filter( 'acf/conditional_logic/choices', array( $this, 'render_field_taxonomy_conditional_choices' ), 10, 3 );

			// actions
			add_action( 'acf/save_post', array( $this, 'save_post' ), 15, 1 );
		}

		/**
		 * Returns AJAX results for the Taxonomy field.
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

			acf_send_ajax_results( $this->get_ajax_query( $_POST ) );
		}

		/**
		 * This function will return an array of data formatted for use in a select2 AJAX response
		 *
		 * @type    function
		 * @date    15/10/2014
		 * @since   5.0.9
		 *
		 * @param   $options (array)
		 * @return  (array)
		 */
		function get_ajax_query( $options = array() ) {
			$options = acf_parse_args(
				$options,
				array(
					'post_id'   => 0,
					's'         => '',
					'field_key' => '',
					'term_id'   => '',
					'include'   => '',
					'paged'     => 1,
				)
			);

			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}

			// if options include isset, then we are loading a specific term.
			if ( ! empty( $options['include'] ) ) {
				$options['term_id'] = $options['include'];
				// paged should be 1.
				$options['paged'] = 1;
			}

			// Bail early if taxonomy does not exist.
			if ( ! taxonomy_exists( $field['taxonomy'] ) ) {
				return false;
			}

			$results         = array();
			$is_hierarchical = is_taxonomy_hierarchical( $field['taxonomy'] );
			$is_pagination   = ( $options['paged'] > 0 );
			$is_search       = false;
			$limit           = 20;
			$offset          = 20 * ( $options['paged'] - 1 );

			$args = array(
				'taxonomy'   => $field['taxonomy'],
				'hide_empty' => false,
			);

			// Don't bother for hierarchial terms, we will need to load all terms anyway.
			if ( $is_pagination && ! $is_hierarchical ) {
				$args['number'] = $limit;
				$args['offset'] = $offset;
			}

			// search
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = wp_unslash( strval( $options['s'] ) );

				$args['search'] = isset( $options['term_id'] ) && $options['term_id'] ? '' : $s;
				$is_search      = true;
			}

			$args = apply_filters( 'acf/fields/taxonomy/query', $args, $field, $options['post_id'] );

			if ( ! empty( $options['include'] ) ) {
				// Limit search to a specific id if one is provided.
				$args['include'] = $options['include'];
			}

			$terms = acf_get_terms( $args );

			// Sort hierachial.
			if ( $is_hierarchical ) {
				$limit  = acf_maybe_get( $args, 'number', $limit );
				$offset = acf_maybe_get( $args, 'offset', $offset );

				$parent = acf_maybe_get( $args, 'parent', 0 );
				$parent = acf_maybe_get( $args, 'child_of', $parent );

				// This will fail if a search has taken place because parents wont exist.
				if ( ! $is_search ) {
					$ordered_terms = _get_term_children( $parent, $terms, $field['taxonomy'] );
					// Check for empty array. Possible if parent did not exist within original data.
					if ( ! empty( $ordered_terms ) ) {
						$terms = $ordered_terms;
					}
				}

				// Fake pagination.
				if ( $is_pagination && ! $options['include'] ) {
					$terms = array_slice( $terms, $offset, $limit );
				}
			}

			// Append to r.
			foreach ( $terms as $term ) {

				// Add to json.
				$results[] = array(
					'id'   => $term->term_id,
					'text' => $this->get_term_title( $term, $field, $options['post_id'], true ),
				);
			}

			$response = array(
				'results' => $results,
				'limit'   => $limit,
			);

			return $response;
		}

		/**
		 * Returns the Term's title displayed in the field UI.
		 *
		 * @since   5.0.0
		 *
		 * @param   WP_Term $term     The term object.
		 * @param   array   $field    The field settings.
		 * @param   mixed   $post_id  The post_id being edited.
		 * @param   boolean $unescape Should we return an unescaped post title.
		 * @return  string
		 */
		function get_term_title( $term, $field, $post_id = 0, $unescape = false ) {
			$title = acf_get_term_title( $term );

			// Default $post_id to current post being edited.
			$post_id = $post_id ? $post_id : acf_get_form_data( 'post_id' );

			// unescape for select2 output which handles the escaping.
			if ( $unescape ) {
				$title = html_entity_decode( $title );
			}

			/**
			 * Filters the term title.
			 *
			 * @date    1/11/2013
			 * @since   5.0.0
			 *
			 * @param   string $title The term title.
			 * @param   WP_Term $term The term object.
			 * @param   array $field The field settings.
			 * @param   (int|string) $post_id The post_id being edited.
			 */
			return apply_filters( 'acf/fields/taxonomy/result', $title, $term, $field, $post_id );
		}


		/**
		 * This function will return an array of terms for a given field value
		 *
		 * @type    function
		 * @date    13/06/2014
		 * @since   5.0.0
		 *
		 * @param   $value (array)
		 * @return  $value
		 */
		function get_terms( $value, $taxonomy = 'category' ) {

			// load terms in 1 query to save multiple DB calls from following code
			if ( count( $value ) > 1 ) {
				$terms = acf_get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'include'    => $value,
						'hide_empty' => false,
					)
				);
			}

			// update value to include $post
			foreach ( array_keys( $value ) as $i ) {
				$value[ $i ] = get_term( $value[ $i ], $taxonomy );
			}

			// filter out null values
			$value = array_filter( $value );

			// return
			return $value;
		}


		/**
		 * This filter is appied to the $value after it is loaded from the db
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value - the value found in the database
		 * @param   $post_id - the post_id from which the value was loaded from
		 * @param   $field - the field array holding all the field options
		 *
		 * @return  $value - the value to be saved in te database
		 */
		function load_value( $value, $post_id, $field ) {

			// get valid terms
			$value = acf_get_valid_terms( $value, $field['taxonomy'] );

			// load_terms
			if ( $field['load_terms'] ) {

				// Decode $post_id for $type and $id.
				$decoded = acf_decode_post_id( $post_id );
				$type    = $decoded['type'];
				$id      = $decoded['id'];

				if ( $type === 'block' ) {
					// Get parent block...
				}

				// get terms
				$term_ids = wp_get_object_terms(
					$id,
					$field['taxonomy'],
					array(
						'fields'  => 'ids',
						'orderby' => 'none',
					)
				);

				// bail early if no terms
				if ( empty( $term_ids ) || is_wp_error( $term_ids ) ) {
					return false;
				}

				// sort
				if ( ! empty( $value ) ) {
					$order = array();

					foreach ( $term_ids as $i => $v ) {
						$order[ $i ] = array_search( $v, $value );
					}

					array_multisort( $order, $term_ids );
				}

				// update value
				$value = $term_ids;
			}

			// convert back from array if neccessary
			if ( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {
				$value = array_shift( $value );
			}

			// return
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
		 * @return mixed $value The modified value.
		 */
		public function update_value( $value, $post_id, $field ) {

			if ( is_array( $value ) ) {
				$value = array_filter( $value );
			}

			acf_update_bidirectional_values( acf_get_array( $value ), $post_id, $field, 'term' );

			// save_terms if enabled.
			if ( $field['save_terms'] ) {

				// vars
				$taxonomy = $field['taxonomy'];

				// force value to array.
				$term_ids = acf_get_array( $value );

				// convert to int.
				$term_ids = array_map( 'intval', $term_ids );

				// get existing term id's (from a previously saved field).
				$old_term_ids = isset( $this->save_post_terms[ $taxonomy ] ) ? $this->save_post_terms[ $taxonomy ] : array();

				// append
				$this->save_post_terms[ $taxonomy ] = array_merge( $old_term_ids, $term_ids );

				// if called directly from frontend update_field().
				if ( ! did_action( 'acf/save_post' ) ) {
					$this->save_post( $post_id );
					return $value;
				}
			}

			return $value;
		}

		/**
		 * This function will save any terms in the save_post_terms array
		 *
		 * @since   5.0.9
		 *
		 * @param  mixed $post_id The ACF post ID to save to.
		 * @return void
		 */
		public function save_post( $post_id ) {
			// Check for saved terms.
			if ( ! empty( $this->save_post_terms ) ) {
				/**
				 * Determine object ID allowing for non "post" $post_id (user, taxonomy, etc).
				 * Although not fully supported by WordPress, non "post" objects may use the term relationships table.
				 * Sharing taxonomies across object types is discouraged, but unique taxonomies work well.
				 * Note: Do not attempt to restrict to "post" only. This has been attempted in 5.8.9 and later reverted.
				 */
				$decoded = acf_decode_post_id( $post_id );
				$type    = $decoded['type'];
				$id      = $decoded['id'];

				if ( $type === 'block' ) {
					// Get parent block...
				}

				// Loop over taxonomies and save terms.
				foreach ( $this->save_post_terms as $taxonomy => $term_ids ) {
					wp_set_object_terms( $id, $term_ids, $taxonomy, false );
				}

				// Reset storage.
				$this->save_post_terms = array();
			}
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

			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// force value to array
			$value = acf_get_array( $value );

			// load posts if needed
			if ( $field['return_format'] == 'object' ) {

				// get posts
				$value = $this->get_terms( $value, $field['taxonomy'] );
			}

			// convert back from array if neccessary
			if ( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {
				$value = array_shift( $value );
			}

			// return
			return $value;
		}

		/**
		 * Renders the Taxonomy field.
		 *
		 * @since 3.6
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		public function render_field( $field ) {
			// force value to array
			$field['value'] = acf_get_array( $field['value'] );

			$nonce = wp_create_nonce( 'acf_field_' . $this->name . '_' . $field['key'] );

			// vars
			$div = array(
				'class'           => 'acf-taxonomy-field',
				'data-save'       => $field['save_terms'],
				'data-ftype'      => $field['field_type'],
				'data-taxonomy'   => $field['taxonomy'],
				'data-allow_null' => $field['allow_null'],
				'data-nonce'      => $nonce,
			);
			// get taxonomy
			$taxonomy = get_taxonomy( $field['taxonomy'] );

			// bail early if taxonomy does not exist
			if ( ! $taxonomy ) {
				return;
			}

			?>
<div <?php echo acf_esc_attrs( $div ); ?>>
			<?php if ( $field['add_term'] && current_user_can( $taxonomy->cap->manage_terms ) ) : ?>
	<div class="acf-actions -hover">
		<a href="#" class="acf-icon -plus acf-js-tooltip small" data-name="add" title="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>"></a>
	</div>
				<?php
	endif;

			if ( $field['field_type'] == 'select' ) {
				$field['multiple'] = 0;

				$this->render_field_select( $field, $nonce );
			} elseif ( $field['field_type'] == 'multi_select' ) {
				$field['multiple'] = 1;

				$this->render_field_select( $field, $nonce );
			} elseif ( $field['field_type'] == 'radio' ) {
				$this->render_field_checkbox( $field );
			} elseif ( $field['field_type'] == 'checkbox' ) {
				$this->render_field_checkbox( $field );
			}

			?>
</div>
			<?php
		}

		/**
		 * Create the HTML interface for your field
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $field - an array holding all the field's data
		 */
		function render_field_select( $field, $nonce ) {

			// Change Field into a select
			$field['type']    = 'select';
			$field['ui']      = 1;
			$field['ajax']    = 1;
			$field['nonce']   = $nonce;
			$field['choices'] = array();

			// value
			if ( ! empty( $field['value'] ) ) {

				// get terms
				$terms = $this->get_terms( $field['value'], $field['taxonomy'] );

				// set choices
				if ( ! empty( $terms ) ) {
					foreach ( array_keys( $terms ) as $i ) {

						// vars
						$term = acf_extract_var( $terms, $i );

						// append to choices
						$field['choices'][ $term->term_id ] = $this->get_term_title( $term, $field );
					}
				}
			}

			// render select
			acf_render_field( $field );
		}


		/**
		 * Create the HTML interface for your field
		 *
		 * @since   3.6
		 *
		 * @param array $field an array holding all the field's data.
		 */
		public function render_field_checkbox( $field ) {

			// hidden input.
			acf_hidden_input(
				array(
					'type' => 'hidden',
					'name' => $field['name'],
				)
			);

			// checkbox saves an array.
			if ( $field['field_type'] == 'checkbox' ) {
				$field['name'] .= '[]';
			}

			// taxonomy.
			$taxonomy_obj = get_taxonomy( $field['taxonomy'] );

			// include walker.
			acf_include( 'includes/walkers/class-acf-walker-taxonomy-field.php' );

			// vars.
			$args = array(
				'taxonomy'         => $field['taxonomy'],
				'show_option_none' => sprintf( _x( 'No %s', 'No Terms', 'acf' ), $taxonomy_obj->labels->name ),
				'hide_empty'       => false,
				'style'            => 'none',
				'walker'           => new ACF_Taxonomy_Field_Walker( $field ),
			);

			// filter for 3rd party customization.
			$args = apply_filters( 'acf/fields/taxonomy/wp_list_categories', $args, $field );
			$args = apply_filters( 'acf/fields/taxonomy/wp_list_categories/name=' . $field['_name'], $args, $field );
			$args = apply_filters( 'acf/fields/taxonomy/wp_list_categories/key=' . $field['key'], $args, $field );

			// Build UL attributes for accessibility and consistency.
			$ul = array(
				'class' => 'acf-checkbox-list acf-bl',
				'role'  => $field['field_type'] === 'radio' ? 'radiogroup' : 'group',
			);

			if ( ! empty( $field['id'] ) ) {
				$ul['aria-labelledby'] = $field['id'] . '-label';
			}
			?>
<div class="categorychecklist-holder">
	<ul <?php echo acf_esc_attrs( $ul ); ?>>
			<?php wp_list_categories( $args ); ?>
	</ul>
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
					'label'        => __( 'Taxonomy', 'acf' ),
					'instructions' => __( 'Select the taxonomy to be displayed', 'acf' ),
					'type'         => 'select',
					'name'         => 'taxonomy',
					'choices'      => acf_get_taxonomy_labels(),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Create Terms', 'acf' ),
					'instructions' => __( 'Allow new terms to be created whilst editing', 'acf' ),
					'name'         => 'add_term',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Save Terms', 'acf' ),
					'instructions' => __( 'Connect selected terms to the post', 'acf' ),
					'name'         => 'save_terms',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Load Terms', 'acf' ),
					'instructions' => __( 'Load value from posts terms', 'acf' ),
					'name'         => 'load_terms',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Value', 'acf' ),
					'instructions' => '',
					'type'         => 'radio',
					'name'         => 'return_format',
					'choices'      => array(
						'object' => __( 'Term Object', 'acf' ),
						'id'     => __( 'Term ID', 'acf' ),
					),
					'layout'       => 'horizontal',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Appearance', 'acf' ),
					'instructions' => __( 'Select the appearance of this field', 'acf' ),
					'type'         => 'select',
					'name'         => 'field_type',
					'optgroup'     => true,
					'choices'      => array(
						__( 'Multiple Values', 'acf' ) => array(
							'checkbox'     => __( 'Checkbox', 'acf' ),
							'multi_select' => __( 'Multi Select', 'acf' ),
						),
						__( 'Single Value', 'acf' )    => array(
							'radio'  => __( 'Radio Buttons', 'acf' ),
							'select' => _x( 'Select', 'noun', 'acf' ),
						),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Allow Null', 'acf' ),
					'instructions' => '',
					'name'         => 'allow_null',
					'type'         => 'true_false',
					'ui'           => 1,
					'conditions'   => array(
						'field'    => 'field_type',
						'operator' => '!=',
						'value'    => 'checkbox',
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
		 * Filters choices in taxonomy conditions.
		 *
		 * @since 6.3
		 *
		 * @param array  $choices           The selected choice.
		 * @param array  $conditional_field The conditional field settings object.
		 * @param string $rule_value        The rule value.
		 * @return mixed
		 */
		public function render_field_taxonomy_conditional_choices( $choices, $conditional_field, $rule_value ) {
			if ( is_array( $conditional_field ) && $conditional_field['type'] === 'taxonomy' ) {
				if ( ! empty( $rule_value ) ) {
					$term    = get_term( $rule_value );
					$choices = array( $rule_value => $term->name );
				}
			}
			return $choices;
		}


		/**
		 * AJAX handler for adding Taxonomy field terms.
		 *
		 * @since 5.2.3
		 *
		 * @return void
		 */
		public function ajax_add_term() {
			$args = acf_request_args(
				array(
					'nonce'       => '',
					'field_key'   => '',
					'term_name'   => '',
					'term_parent' => '',
				)
			);

			if ( ! acf_verify_ajax( $args['nonce'], $args['field_key'], true ) ) {
				die();
			}

			// load field
			$field = acf_get_field( $args['field_key'] );
			if ( ! $field ) {
				die();
			}

			// vars
			$taxonomy_obj   = get_taxonomy( $field['taxonomy'] );
			$taxonomy_label = $taxonomy_obj->labels->singular_name;

			// validate cap
			// note: this situation should never occur due to condition of the add new button
			if ( ! current_user_can( $taxonomy_obj->cap->manage_terms ) ) {
				wp_send_json_error(
					array(
						'error' => sprintf( __( 'User unable to add new %s', 'acf' ), $taxonomy_label ),
					)
				);
			}

			// save?
			if ( $args['term_name'] ) {

				// exists
				if ( term_exists( $args['term_name'], $field['taxonomy'], $args['term_parent'] ) ) {
					wp_send_json_error(
						array(
							'error' => sprintf( __( '%s already exists', 'acf' ), $taxonomy_label ),
						)
					);
				}

				// vars
				$extra = array();
				if ( $args['term_parent'] ) {
					$extra['parent'] = (int) $args['term_parent'];
				}

				// insert
				$data = wp_insert_term( $args['term_name'], $field['taxonomy'], $extra );

				// error
				if ( is_wp_error( $data ) ) {
					wp_send_json_error(
						array(
							'error' => $data->get_error_message(),
						)
					);
				}

				// load term
				$term = get_term( $data['term_id'] );

				// prepend ancenstors count to term name
				$prefix    = '';
				$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
				if ( ! empty( $ancestors ) ) {
					$prefix = str_repeat( '- ', count( $ancestors ) );
				}

				// success
				wp_send_json_success(
					array(
						'message'     => sprintf( __( '%s added', 'acf' ), $taxonomy_label ),
						'term_id'     => $term->term_id,
						'term_name'   => $term->name,
						'term_label'  => $prefix . $term->name,
						'term_parent' => $term->parent,
					)
				);
			}

			?>
		<form method="post">
			<?php

			acf_render_field_wrap(
				array(
					'label' => __( 'Name', 'acf' ),
					'name'  => 'term_name',
					'type'  => 'text',
				)
			);

			if ( is_taxonomy_hierarchical( $field['taxonomy'] ) ) {
				$choices  = array();
				$response = $this->get_ajax_query( $args );

				if ( $response ) {
					foreach ( $response['results'] as $v ) {
						$choices[ $v['id'] ] = $v['text'];
					}
				}

				acf_render_field_wrap(
					array(
						'label'      => __( 'Parent', 'acf' ),
						'name'       => 'term_parent',
						'type'       => 'select',
						'allow_null' => 1,
						'ui'         => 0,
						'choices'    => $choices,
					)
				);
			}

			?>
		<p class="acf-submit">
			<button class="acf-submit-button button button-primary" type="submit"><?php esc_html_e( 'Add', 'acf' ); ?></button>
		</p>
		</form><?php

		// die
		die;
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

			if ( in_array( $field['field_type'], array( 'radio', 'select' ) ) ) {
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
				$term = get_term( $object_id );
				if ( ! $term instanceof WP_Term ) {
					continue;
				}

				$rest_base = acf_get_object_type_rest_base( get_taxonomy( $term->taxonomy ) );
				if ( ! $rest_base ) {
					continue;
				}

				$links[] = array(
					'rel'        => 'acf:term',
					'href'       => rest_url( sprintf( '/wp/v2/%s/%s', $rest_base, $object_id ) ),
					'embeddable' => true,
					'taxonomy'   => $term->taxonomy,
				);
			}

			return $links;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_taxonomy' );
endif; // class_exists check

?>
