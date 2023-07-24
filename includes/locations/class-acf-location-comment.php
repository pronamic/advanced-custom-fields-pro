<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Comment' ) ) :

	class ACF_Location_Comment extends ACF_Location {

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
			$this->name        = 'comment';
			$this->label       = __( 'Comment', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'comment';
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
			if ( isset( $screen['comment'] ) ) {
				$comment = $screen['comment'];
			} else {
				return false;
			}
			return $this->compare_to_rule( $comment, $rule );
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
			return array_merge(
				array(
					'all' => __( 'All', 'acf' ),
				),
				acf_get_pretty_post_types() // Todo: Change to post types that support comments.
			);
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Comment' );

endif; // class_exists check
