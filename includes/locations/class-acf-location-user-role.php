<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_User_Role' ) ) :

	class ACF_Location_User_Role extends ACF_Location {

		/**
		 * initialize
		 *
		 * Sets up the class functionality.
		 *
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {
			$this->name        = 'user_role';
			$this->label       = __( 'User Role', 'acf' );
			$this->category    = 'user';
			$this->object_type = 'user';
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
			if ( isset( $screen['user_role'] ) ) {
				$user_role = $screen['user_role'];
			} elseif ( isset( $screen['user_id'] ) ) {
				$user_id   = $screen['user_id'];
				$user_role = '';

				// Determine $user_role from $user_id.
				if ( $user_id === 'new' ) {
					$user_role = get_option( 'default_role' );

					// Check if user can, and if so, set the value allowing them to match.
				} elseif ( user_can( $user_id, $rule['value'] ) ) {
					$user_role = $rule['value'];
				}
			} else {
				return false;
			}

			// Compare rule against $user_role.
			return $this->compare_to_rule( $user_role, $rule );
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
			global $wp_roles;
			return array_merge(
				array(
					'all' => __( 'All', 'acf' ),
				),
				$wp_roles->get_names()
			);
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_User_Role' );
endif; // class_exists check
