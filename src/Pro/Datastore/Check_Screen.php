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

namespace ACF\Pro\Datastore;

/**
 * Attaches datastore field data to the acf/ajax/check_screen response.
 *
 * The check_screen AJAX endpoint runs when WordPress loads metaboxes
 * dynamically (the meta-box-loader path). When new field groups appear
 * on screen, the JS-side datastore needs to know about their fields and
 * values. This class collects that data and merges it into the response
 * as `storeData` for groups that were not already on the page.
 */
class Check_Screen {

	/**
	 * Constructor.
	 *
	 * @since 6.8.1
	 */
	public function __construct() {
		add_filter( 'acf/ajax/check_screen/response', array( $this, 'attach_store_data' ), 10, 3 );
	}

	/**
	 * Attaches datastore data for newly-loaded field groups to the response.
	 *
	 * @since 6.8.1
	 *
	 * @param array $response     The check_screen response array.
	 * @param array $field_groups The field groups returned for this screen.
	 * @param array $args         The check_screen request args (post_id, screen, exists, ...).
	 * @return array
	 */
	public function attach_store_data( $response, $field_groups, $args ) {
		if ( ! acf_is_using_datastore() ) {
			return $response;
		}

		$store_data = array(
			'context'     => array(
				'postId'   => (int) $args['post_id'],
				'postType' => get_post_type( (int) $args['post_id'] ) ?: '',
			),
			'fields'      => array(),
			'values'      => array(),
			'fieldGroups' => array(),
		);

		$localization = acf_get_instance( 'ACF\\Pro\\Datastore\\Localization' );
		$exists       = isset( $args['exists'] ) ? (array) $args['exists'] : array();

		foreach ( (array) $field_groups as $field_group ) {
			// Only collect data for field groups not already on the page.
			if ( in_array( $field_group['key'], $exists, true ) ) {
				continue;
			}

			$fields = acf_get_fields( $field_group );
			if ( ! $fields ) {
				continue;
			}

			$localization->collect_field_data( $fields, $args['post_id'], $field_group['key'], $store_data );

			$store_data['fieldGroups'][] = array(
				'key'                   => $field_group['key'],
				'title'                 => acf_esc_html( acf_get_field_group_title( $field_group ) ),
				'position'              => $field_group['position'],
				'style'                 => $field_group['style'],
				'label_placement'       => $field_group['label_placement'],
				'instruction_placement' => $field_group['instruction_placement'],
				'edit_url'              => esc_url( acf_get_field_group_edit_link( $field_group['ID'] ) ),
			);
		}

		if ( ! empty( $store_data['fields'] ) ) {
			$response['storeData'] = $store_data;
		}

		return $response;
	}
}
