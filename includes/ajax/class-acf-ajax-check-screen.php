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
			$args = wp_parse_args(
				$this->request,
				array(
					'screen'  => '',
					'post_id' => 0,
					'ajax'    => true,
					'exists'  => array(),
				)
			);

			if ( ! acf_current_user_can_edit_post( (int) $args['post_id'] ) ) {
				return new WP_Error( 'acf_invalid_permissions', __( 'Sorry, you do not have permission to do that.', 'acf' ) );
			}

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
			if ( $this->get( 'screen' ) == 'post' ) {
				$response['sorted'] = get_user_option( 'meta-box-order_' . $this->get( 'post_type' ) );
			}

			// return
			return $response;
		}
	}

	acf_new_instance( 'ACF_Ajax_Check_Screen' );
endif; // class_exists check
