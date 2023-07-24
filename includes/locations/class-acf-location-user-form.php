<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_User_Form' ) ) :

	class ACF_Location_User_Form extends ACF_Location {

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
			$this->name        = 'user_form';
			$this->label       = __( 'User Form', 'acf' );
			$this->category    = 'user';
			$this->object_type = 'user';
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
			// REST API has no forms, so we should always allow it.
			if ( ! empty( $screen['rest'] ) ) {
				return true;
			}

			// Check screen args.
			if ( isset( $screen['user_form'] ) ) {
				$user_form = $screen['user_form'];
			} else {
				return false;
			}

			// The "Add / Edit" choice (foolishly valued "edit") should match true for either "add" or "edit".
			if ( $rule['value'] === 'edit' && $user_form === 'add' ) {
				$user_form = 'edit';
			}

			// Compare rule against $user_form.
			return $this->compare_to_rule( $user_form, $rule );
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
			return array(
				'all'      => __( 'All', 'acf' ),
				'add'      => __( 'Add', 'acf' ),
				'edit'     => __( 'Add / Edit', 'acf' ),
				'register' => __( 'Register', 'acf' ),
			);
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_User_Form' );

endif; // class_exists check
