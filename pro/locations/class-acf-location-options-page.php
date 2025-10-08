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

if ( ! class_exists( 'ACF_Location_Options_Page' ) ) :

	class ACF_Location_Options_Page extends ACF_Location {

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
			$this->name        = 'options_page';
			$this->label       = __( 'Options Page', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'option';
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
			if ( isset( $screen['options_page'] ) ) {
				$options_page = $screen['options_page'];
			} else {
				return false;
			}

			// Compare rule against $nav_menu.
			return $this->compare_to_rule( $options_page, $rule );
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
			$choices = array();

			// Append pages.
			$pages = acf_get_options_pages();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$choices[ $page['menu_slug'] ] = $page['page_title'];
				}
			} else {
				$choices[''] = __( 'Select options page...', 'acf' );
			}

			if ( acf_get_setting( 'enable_options_pages_ui' ) ) {
				$choices['add_new_options_page'] = __( 'Add New Options Page', 'acf' );
			}

			// Return choices.
			return $choices;
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Options_Page' );
endif; // class_exists check
