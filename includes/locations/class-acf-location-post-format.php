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

if ( ! class_exists( 'ACF_Location_Post_Format' ) ) :

	class ACF_Location_Post_Format extends ACF_Location {

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
			$this->name        = 'post_format';
			$this->label       = __( 'Post Format', 'acf' );
			$this->category    = 'post';
			$this->object_type = 'post';
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
			if ( isset( $screen['post_format'] ) ) {
				$post_format = $screen['post_format'];
			} elseif ( isset( $screen['post_id'] ) ) {
				$post_type   = get_post_type( $screen['post_id'] );
				$post_format = get_post_format( $screen['post_id'] );

				// Treat new posts (that support post-formats) without a saved format as "standard".
				if ( ! $post_format && post_type_supports( $post_type, 'post-formats' ) ) {
					$post_format = 'standard';
				}
			} else {
				return false;
			}

			// Compare rule against $post_format.
			return $this->compare_to_rule( $post_format, $rule );
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
			return get_post_format_strings();
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Post_Format' );
endif; // class_exists check
