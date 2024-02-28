<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Current_User_Role' ) ) :

	class ACF_Location_Current_User_Role extends ACF_Location {

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
			$this->name     = 'current_user_role';
			$this->label    = __( 'Current User Role', 'acf' );
			$this->category = 'user';
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

			// Get current user.
			$user = wp_get_current_user();
			if ( ! $user ) {
				return false;
			}

			// Check super_admin value.
			if ( $rule['value'] == 'super_admin' ) {
				$result = is_super_admin( $user->ID );

				// Check role.
			} else {
				$result = in_array( $rule['value'], $user->roles );
			}

			// Reverse result for "!=" operator.
			if ( $rule['operator'] === '!=' ) {
				return ! $result;
			}
			return $result;
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
			$choices = wp_roles()->get_names();

			// Prepend Super Admin choice.
			if ( is_multisite() ) {
				return array_merge(
					array(
						'super_admin' => __( 'Super Admin', 'acf' ),
					),
					$choices
				);
			}
			return $choices;
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Current_User_Role' );
endif; // class_exists check
