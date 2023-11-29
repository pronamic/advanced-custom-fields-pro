<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Page_Parent' ) ) :

	class ACF_Location_Page_Parent extends ACF_Location {

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
			$this->name           = 'page_parent';
			$this->label          = __( 'Page Parent', 'acf' );
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
		 * @param   array $rule The location rule.
		 * @param   array $screen The screen args.
		 * @param   array $field_group The field group settings.
		 * @return  bool
		 */
		public function match( $rule, $screen, $field_group ) {

			// Check screen args.
			if ( isset( $screen['page_parent'] ) ) {
				$page_parent = $screen['page_parent'];
			} elseif ( isset( $screen['post_id'] ) ) {
				$post        = get_post( $screen['post_id'] );
				$page_parent = $post ? $post->post_parent : false;
			} else {
				return false;
			}

			// Compare rule against $page_parent.
			return $this->compare_to_rule( $page_parent, $rule );
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
			return acf_get_location_type( 'page' )->get_values( $rule );
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Page_Parent' );
endif; // class_exists check
