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

if ( ! class_exists( 'ACF_Location_Widget' ) ) :

	class ACF_Location_Widget extends ACF_Location {

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
			$this->name        = 'widget';
			$this->label       = __( 'Widget', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'widget';
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
			if ( isset( $screen['widget'] ) ) {
				$widget = $screen['widget'];
			} else {
				return false;
			}

			// Compare rule against $widget.
			return $this->compare_to_rule( $widget, $rule );
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
			global $wp_widget_factory;

			// Populate choices.
			$choices = array(
				'all' => __( 'All', 'acf' ),
			);
			if ( $wp_widget_factory->widgets ) {
				foreach ( $wp_widget_factory->widgets as $widget ) {
					$choices[ $widget->id_base ] = $widget->name;
				}
			}
			return $choices;
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Widget' );
endif; // class_exists check
