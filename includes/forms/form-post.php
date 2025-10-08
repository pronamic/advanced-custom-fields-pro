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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Form_Post' ) ) :

	class ACF_Form_Post {

		/** @var string The first field groups style CSS. */
		var $style = '';

		/**
		 * __construct
		 *
		 * Sets up the class functionality.
		 *
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function __construct() {

			// initialize on post edit screens
			add_action( 'load-post.php', array( $this, 'initialize' ) );
			add_action( 'load-post-new.php', array( $this, 'initialize' ) );

			// save
			add_filter( 'wp_insert_post_empty_content', array( $this, 'wp_insert_post_empty_content' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		}


		/**
		 * initialize
		 *
		 * Sets up Form functionality.
		 *
		 * @date    19/9/18
		 * @since   5.7.6
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {

			// globals
			global $typenow;

			$acf_post_types = acf_get_internal_post_types();

			foreach ( $acf_post_types as $post_type ) {
				remove_meta_box( 'submitdiv', $post_type, 'side' );
			}

			// restrict specific post types
			$restricted = array_merge( $acf_post_types, array( 'acf-taxonomy', 'attachment' ) );
			if ( in_array( $typenow, $restricted ) ) {
				return;
			}

			// enqueue scripts
			acf_enqueue_scripts(
				array(
					'uploader' => true,
				)
			);

			// actions
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		}

		/**
		 * add_meta_boxes
		 *
		 * Adds ACF metaboxes for the given $post_type and $post.
		 *
		 * @date    19/9/18
		 * @since   5.7.6
		 *
		 * @param   string  $post_type The post type.
		 * @param   WP_Post $post      The post being edited.
		 * @return  void
		 */
		function add_meta_boxes( $post_type, $post ) {

			// Storage for localized postboxes.
			$postboxes = array();

			// Get field groups for this screen.
			$field_groups = acf_get_field_groups(
				array(
					'post_id'   => $post->ID,
					'post_type' => $post_type,
				)
			);

			// Loop over field groups.
			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {
					$id       = esc_attr( "acf-{$field_group['key']}" );
					$context  = esc_attr( $field_group['position'] );
					$priority = 'high';

					// Reduce priority for sidebar metaboxes for best position.
					if ( $context === 'side' ) {
						$priority = 'core';
					}

					/**
					 * Filters the metabox priority.
					 *
					 * @date    23/06/12
					 * @since   3.1.8
					 *
					 * @param   string $priority The metabox priority (high, core, default, low).
					 * @param   array $field_group The field group array.
					 */
					$priority = apply_filters( 'acf/input/meta_box_priority', $priority, $field_group );

					// Localize data
					$postboxes[] = array(
						'id'    => $id,
						'key'   => esc_attr( $field_group['key'] ),
						'style' => esc_attr( $field_group['style'] ),
						'label' => esc_attr( $field_group['label_placement'] ),
						'edit'  => esc_url( acf_get_field_group_edit_link( $field_group['ID'] ) ),
					);

					// Add the meta box.
					add_meta_box(
						$id,
						acf_esc_html( acf_get_field_group_title( $field_group ) ),
						array( $this, 'render_meta_box' ),
						$post_type,
						$context,
						$priority,
						array( 'field_group' => $field_group )
					);
				}

				// Set style from first field group.
				$this->style = acf_get_field_group_style( $field_groups[0] );

				// Localize postboxes.
				acf_localize_data(
					array(
						'postboxes' => $postboxes,
					)
				);
			}

			// remove postcustom metabox (removes expensive SQL query)
			if ( acf_get_setting( 'remove_wp_meta_box' ) ) {
				remove_meta_box( 'postcustom', false, 'normal' );
			}

			// Add hidden input fields.
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );

			/**
			* Fires after metaboxes have been added.
			*
			* @date    13/12/18
			* @since   5.8.0
			*
			* @param   string $post_type The post type.
			* @param   WP_Post $post The post being edited.
			* @param   array $field_groups The field groups added.
			*/
			do_action( 'acf/add_meta_boxes', $post_type, $post, $field_groups );
		}

		/**
		 * Called after the title and before the content editor to render the after title metaboxes.
		 * Also renders the CSS required to hide the "hide-on-screen" elements on the page based on the field group settings.
		 *
		 * @since 5.7.6
		 */
		public function edit_form_after_title() {

			// globals
			global $post, $wp_meta_boxes;

			// render post data
			acf_form_data(
				array(
					'screen'  => 'post',
					'post_id' => $post->ID,
				)
			);

			// render 'acf_after_title' metaboxes
			do_meta_boxes( get_current_screen(), 'acf_after_title', $post );

			$style = '';
			if ( is_string( $this->style ) ) {
				$style = $this->style;
			}

			// Render dynamic field group style, using wp_strip_all_tags as this is filterable, but should only contain valid styles and no html.
			echo '<style type="text/css" id="acf-style">' . wp_strip_all_tags( $style ) . '</style>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS only, escaped by wp_strip_all_tags.
		}

		/**
		 * render_meta_box
		 *
		 * Renders the ACF metabox HTML.
		 *
		 * @date    19/9/18
		 * @since   5.7.6
		 *
		 * @param   WP_Post                               $post The post being edited.
		 * @param   array metabox The add_meta_box() args.
		 * @return  void
		 */
		function render_meta_box( $post, $metabox ) {

			// vars
			$id          = $metabox['id'];
			$field_group = $metabox['args']['field_group'];

			// Render fields.
			$fields = acf_get_fields( $field_group );
			acf_render_fields( $fields, $post->ID, 'div', $field_group['instruction_placement'] );
		}

		/**
		 * wp_insert_post_empty_content
		 *
		 * Allows WP to insert a new post without title or post_content if ACF data exists.
		 *
		 * @date    16/07/2014
		 * @since   5.0.1
		 *
		 * @param   boolean $maybe_empty Whether the post should be considered "empty".
		 * @param   array   $postarr     Array of post data.
		 * @return  boolean
		 */
		function wp_insert_post_empty_content( $maybe_empty, $postarr ) {

			// return false and allow insert if '_acf_changed' exists
			if ( $maybe_empty && acf_maybe_get_POST( '_acf_changed' ) ) {
				return false;
			}

			// return
			return $maybe_empty;
		}

		/**
		 * Checks if the $post is allowed to be saved.
		 * Used to avoid triggering "acf/save_post" on dynamically created posts during save.
		 *
		 * @type    function
		 * @date    26/06/2016
		 * @since   5.3.8
		 *
		 * @param   WP_Post $post The post to check.
		 * @return  boolean
		 */
		function allow_save_post( $post ) {

			// vars
			$allow = true;

			// restrict post types
			$restrict = array( 'auto-draft', 'revision', 'acf-field', 'acf-field-group' );
			if ( in_array( $post->post_type, $restrict ) ) {
				$allow = false;
			}

			// disallow if the $_POST ID value does not match the $post->ID
			$form_post_id = (int) acf_maybe_get_POST( 'post_ID' );
			if ( $form_post_id && $form_post_id !== $post->ID ) {
				$allow = false;
			}

			// revision (preview)
			if ( $post->post_type == 'revision' ) {

				// allow if doing preview and this $post is a child of the $_POST ID
				if ( acf_maybe_get_POST( 'wp-preview' ) == 'dopreview' && $form_post_id === $post->post_parent ) {
					$allow = true;
				}
			}

			// return
			return $allow;
		}

		/**
		 * Triggers during the 'save_post' action to save the $_POST data.
		 *
		 * @since   1.0.0
		 *
		 * @param integer $post_id The post ID.
		 * @param WP_Post $post    The post object.
		 * @return integer
		 */
		public function save_post( $post_id, $post ) {
			// Bail early if not allowed to save this post type.
			if ( ! $this->allow_save_post( $post ) ) {
				return $post_id;
			}

			// Verify nonce.
			if ( ! acf_verify_nonce( 'post' ) ) {
				return $post_id;
			}

			// Validate for published post (allow draft to save without validation).
			if ( $post->post_status === 'publish' ) {
				// Bail early if validation fails.
				if ( ! acf_validate_save_post() ) {
					return;
				}
			}

			acf_save_post( $post_id );

			// We handle revisions differently on WP 6.4+.
			if ( version_compare( get_bloginfo( 'version' ), '6.4', '<' ) && post_type_supports( $post->post_type, 'revisions' ) ) {
				acf_save_post_revision( $post_id );
			}

			return $post_id;
		}
	}

	acf_new_instance( 'ACF_Form_Post' );
endif;
