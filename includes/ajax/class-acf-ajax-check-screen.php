<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax_Check_Screen' ) ) :

	class ACF_Ajax_Check_Screen extends ACF_Ajax {

		/** @var string The AJAX action name. */
		var $action = 'acf/ajax/check_screen';

		/** @var boolean Prevents access for non-logged in users. */
		var $public = false;

		/**
		 * Returns the response data to sent back.
		 *
		 * @since 5.7.2
		 *
		 * @param array $request The request args.
		 * @return array|WP_Error The response data or WP_Error.
		 */
		public function get_response( $request ) {
			$request = is_array( $request ) ? $request : (array) $this->request;
			$post    = get_post( (int) ( $request['post_id'] ?? 0 ) );

			if ( ! $post || ! acf_current_user_can_edit_post( $post->ID ) ) {
				return new WP_Error( 'acf_invalid_permissions', __( 'Sorry, you do not have permission to do that.', 'acf' ) );
			}

			// The rendered fields are signed for the post, so field groups are only matched with what the editor can change.
			$args = $this->get_screen_args( $post, $request );

			$response = array(
				'results' => array(),
				'style'   => '',
			);

			// get field groups
			$field_groups = acf_get_field_groups( $args );

			// loop through field groups
			if ( $field_groups ) {
				foreach ( $field_groups as $i => $field_group ) {

					// vars
					$item = array(
						'id'       => esc_attr( 'acf-' . $field_group['key'] ),
						'key'      => esc_attr( $field_group['key'] ),
						'title'    => acf_esc_html( acf_get_field_group_title( $field_group ) ),
						'position' => esc_attr( $field_group['position'] ),
						'classes'  => postbox_classes( 'acf-' . $field_group['key'], $args['screen'] ),
						'style'    => esc_attr( $field_group['style'] ),
						'label'    => esc_attr( $field_group['label_placement'] ),
						'edit'     => esc_url( acf_get_field_group_edit_link( $field_group['ID'] ) ),
						'html'     => '',
					);

					$hidden_metaboxes = get_hidden_meta_boxes( $args['screen'] );

					if ( is_array( $hidden_metaboxes ) && in_array( $item['id'], $hidden_metaboxes ) ) {
						$item['classes'] = trim( $item['classes'] . ' hide-if-js' );
					}

					$item['classes'] = esc_attr( $item['classes'] );

					// append html if doesnt already exist on page
					if ( ! in_array( $field_group['key'], $args['exists'] ) ) {

						// load fields
						$fields = acf_get_fields( $field_group );

						// get field HTML
						ob_start();

						// render
						acf_render_fields( $fields, $args['post_id'], 'div', $field_group['instruction_placement'] );

						$item['html'] = ob_get_clean();
					}

					// append
					$response['results'][] = $item;
				}

				// Get style from first field group.
				$response['style'] = acf_get_field_group_style( $field_groups[0] );
			}

			// Custom metabox order.
			$response['sorted'] = get_user_option( 'meta-box-order_' . $args['post_type'] );

			/**
			 * Filters the check_screen response, allowing extensions to attach
			 * extra data tied to the screen.
			 *
			 * @since 6.8.1
			 *
			 * @param array $response     The response array.
			 * @param array $field_groups The field groups returned for this screen.
			 * @param array $args         The check_screen request args.
			 */
			return apply_filters( 'acf/ajax/check_screen/response', $response, $field_groups, $args );
		}

		/**
		 * Returns the location screen args to match field groups with for a post being edited.
		 *
		 * The fields rendered here carry a field list signed for the post, which the post save accepts.
		 * So the post ID, post type and screen come from the post itself, and only the attributes the
		 * user can change in the editor are taken from the request, each checked against what they could
		 * actually set. Other args (users, options pages, other object types) are ignored. A signed list
		 * shows the fields were rendered; it doesn't authorise them.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post    The post being edited.
		 * @param array   $request The request args.
		 * @return array
		 */
		protected function get_screen_args( $post, $request ) {
			$args = array(
				'screen'    => 'post',
				'post_id'   => $post->ID,
				'post_type' => $post->post_type,
				'ajax'      => true,
				'exists'    => acf_normalize_field_keys( $request['exists'] ?? array() ),
			);

			$page_type   = $request['page_type'] ?? null;
			$post_status = $request['post_status'] ?? null;
			$post_terms  = $request['post_terms'] ?? null;

			$editable = array(
				'page_template' => $this->get_page_template_arg( $post, $request['page_template'] ?? null ),
				'page_parent'   => $this->get_page_parent_arg( $post, $request['page_parent'] ?? null ),
				'page_type'     => in_array( $page_type, array( 'child', 'parent' ), true ) ? $page_type : null,
				'post_format'   => $this->get_post_format_arg( $post, $request['post_format'] ?? null ),
				'post_status'   => is_string( $post_status ) && $this->user_can_set_post_status( $post, $post_status ) ? $post_status : null,
				'post_terms'    => is_array( $post_terms ) ? $this->get_assignable_post_terms( $post, $post_terms ) : null,
			);

			// Args that weren't sent, or that the user couldn't set, are left to the post's saved values.
			foreach ( $editable as $name => $value ) {
				if ( null !== $value ) {
					$args[ $name ] = $value;
				}
			}

			return $args;
		}

		/**
		 * Returns a submitted page template if it is one the post can use, or null.
		 *
		 * The block editor sends an empty template for the default template.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post          The post being edited.
		 * @param mixed   $page_template The submitted page template.
		 * @return string|null
		 */
		protected function get_page_template_arg( $post, $page_template ) {
			if ( ! is_string( $page_template ) ) {
				return null;
			}

			if ( in_array( $page_template, array( '', 'default' ), true ) || array_key_exists( $page_template, wp_get_theme()->get_page_templates( $post, $post->post_type ) ) ) {
				return $page_template;
			}

			return null;
		}

		/**
		 * Returns a submitted page parent if it is none or another post of the same type, or null.
		 *
		 * "(no parent)" is sent as an empty value.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post        The post being edited.
		 * @param mixed   $page_parent The submitted page parent.
		 * @return integer|null
		 */
		protected function get_page_parent_arg( $post, $page_parent ) {
			if ( '' === $page_parent ) {
				return 0;
			}

			if ( ! is_scalar( $page_parent ) || ! is_numeric( $page_parent ) ) {
				return null;
			}

			$page_parent = (int) $page_parent;

			if ( 0 === $page_parent || ( $page_parent !== $post->ID && get_post_type( $page_parent ) === $post->post_type ) ) {
				return $page_parent;
			}

			return null;
		}

		/**
		 * Returns a submitted post format if the post type supports it, or null.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post        The post being edited.
		 * @param mixed   $post_format The submitted post format.
		 * @return string|null
		 */
		protected function get_post_format_arg( $post, $post_format ) {
			if ( is_string( $post_format ) && post_type_supports( $post->post_type, 'post-formats' ) && in_array( $post_format, get_post_format_slugs(), true ) ) {
				return $post_format;
			}

			return null;
		}

		/**
		 * Returns true if the user can give the post a status in the editor.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post        The post being edited.
		 * @param string  $post_status The status.
		 * @return boolean
		 */
		protected function user_can_set_post_status( $post, $post_status ) {
			if ( $post_status === $post->post_status ) {
				return true;
			}

			// Registered statuses that aren't internal (such as trash or auto-draft), including custom ones.
			if ( ! in_array( $post_status, get_post_stati( array( 'internal' => false ) ), true ) ) {
				return false;
			}

			// Statuses that publish the post need the post type's publish capability.
			if ( in_array( $post_status, array( 'publish', 'future', 'private' ), true ) ) {
				$post_type = get_post_type_object( $post->post_type );
				return $post_type && current_user_can( $post_type->cap->publish_posts );
			}

			return true;
		}

		/**
		 * Returns the submitted terms for the post's taxonomies the user can assign terms in, and the post's
		 * current terms for those they can't, whether or not they were submitted.
		 *
		 * Once `post_terms` is set, location rules treat a taxonomy missing from it as having no terms, so
		 * leaving a taxonomy out must not be a way to change what the post appears to have.
		 *
		 * @since 6.8.11
		 *
		 * @param WP_Post $post  The post being edited.
		 * @param array   $terms The submitted terms, keyed by taxonomy.
		 * @return array
		 */
		protected function get_assignable_post_terms( $post, $terms ) {
			$post_terms = array();

			foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $taxonomy ) {
				if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
					$current                       = wp_get_object_terms( $post->ID, $taxonomy->name, array( 'fields' => 'ids' ) );
					$post_terms[ $taxonomy->name ] = is_wp_error( $current ) ? array() : array_map( 'intval', $current );
				} elseif ( isset( $terms[ $taxonomy->name ] ) ) {
					$post_terms[ $taxonomy->name ] = array_values( array_map( 'sanitize_text_field', array_filter( (array) $terms[ $taxonomy->name ], 'is_scalar' ) ) );
				}
			}

			return $post_terms;
		}
	}

	acf_new_instance( 'ACF_Ajax_Check_Screen' );
endif; // class_exists check
