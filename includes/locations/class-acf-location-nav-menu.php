<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Nav_Menu' ) ) :

	class ACF_Location_Nav_Menu extends ACF_Location {

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
			$this->name        = 'nav_menu';
			$this->label       = __( 'Menu', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'menu';
		}

		/**
		 * Matches the provided rule against the screen args returning a bool result.
		 *
		 * @date    9/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule The location rule.
		 * @param   array $screen The screen args.
		 * @param   array $field_group The field group settings.
		 * @return  bool
		 */
		public function match( $rule, $screen, $field_group ) {

			// Check screen args.
			if ( isset( $screen['nav_menu'] ) ) {
				$nav_menu = $screen['nav_menu'];
			} else {
				return false;
			}

			// Allow for "location/xxx" rule value.
			$bits = explode( '/', $rule['value'] );
			if ( $bits[0] === 'location' ) {
				$location = $bits[1];

				// Get the map of menu locations [location => menu_id] and update $nav_menu to a location value.
				$menu_locations = get_nav_menu_locations();
				if ( isset( $menu_locations[ $location ] ) ) {
					$rule['value'] = $menu_locations[ $location ];
				}
			}

			// Compare rule against $nav_menu.
			return $this->compare_to_rule( $nav_menu, $rule );
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
			$choices = array(
				'all' => __( 'All', 'acf' ),
			);

			// Append locations.
			$nav_locations = get_registered_nav_menus();
			if ( $nav_locations ) {
				$cat = __( 'Menu Locations', 'acf' );
				foreach ( $nav_locations as $slug => $title ) {
					$choices[ $cat ][ "location/$slug" ] = $title;
				}
			}

			// Append menu IDs.
			$nav_menus = wp_get_nav_menus();
			if ( $nav_menus ) {
				$cat = __( 'Menus', 'acf' );
				foreach ( $nav_menus as $nav_menu ) {
					$choices[ $cat ][ $nav_menu->term_id ] = $nav_menu->name;
				}
			}

			// Return choices.
			return $choices;
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Nav_Menu' );
endif; // class_exists check
