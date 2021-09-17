<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Taxonomy' ) ) :

	class ACF_Location_Taxonomy extends ACF_Location {

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
			$this->name        = 'taxonomy';
			$this->label       = __( 'Taxonomy', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'term';
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
			if ( isset( $screen['taxonomy'] ) ) {
				$taxonomy = $screen['taxonomy'];
			} else {
				return false;
			}

			// Compare rule against $taxonomy.
			return $this->compare_to_rule( $taxonomy, $rule );
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
				acf_get_taxonomy_labels()
			);
		}

		/**
		 * Returns the object_subtype connected to this location.
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule A location rule.
		 * @return  string|array
		 */
		function get_object_subtype( $rule ) {
			if ( $rule['operator'] === '==' ) {
				return $rule['value'];
			}
			return '';
		}

	}

	// initialize
	acf_register_location_type( 'ACF_Location_Taxonomy' );

endif; // class_exists check
