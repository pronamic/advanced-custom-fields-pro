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

if ( ! class_exists( 'ACF_Location_Page_Template' ) ) :

	class ACF_Location_Page_Template extends ACF_Location {

		/**
		 * Initializes props.
		 *
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function initialize() {
			$this->name           = 'page_template';
			$this->label          = __( 'Page Template', 'acf' );
			$this->category       = 'page';
			$this->object_type    = 'post';
			$this->object_subtype = 'page';
		}

		/**
		 * Matches the provided rule against the screen args returning a bool result.
		 *
		 * @date    9/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule        The location rule.
		 * @param   array $screen      The screen args.
		 * @param   array $field_group The field group settings.
		 * @return  boolean
		 */
		public function match( $rule, $screen, $field_group ) {

			// Check screen args.
			if ( isset( $screen['post_type'] ) ) {
				$post_type = $screen['post_type'];
			} elseif ( isset( $screen['post_id'] ) ) {
				$post_type = get_post_type( $screen['post_id'] );
			} else {
				return false;
			}

			// Page templates were extended in WordPress version 4.7 for all post types.
			// Prevent this rule (which is scoped to the "page" post type) appearing on all post types without a template selected (default template).
			if ( $rule['value'] === 'default' && $post_type !== 'page' ) {
				return false;
			}

			// Match rule using Post Template logic.
			return acf_get_location_type( 'post_template' )->match( $rule, $screen, $field_group );
		}

		/**
		 * Returns an array of possible values for this rule type.
		 *
		 * @date    9/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule A location rule.
		 * @return  array
		 */
		public function get_values( $rule ) {
			$post_templates = acf_get_post_templates();
			return array_merge(
				array(
					'default' => apply_filters( 'default_page_template_title', __( 'Default Template', 'acf' ), 'meta-box' ),
				),
				$post_templates['page']
			);
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Page_Template' );
endif; // class_exists check.
