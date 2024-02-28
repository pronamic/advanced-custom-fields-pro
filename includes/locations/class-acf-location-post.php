<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Post' ) ) :

	class ACF_Location_Post extends ACF_Location {

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
			$this->name        = 'post';
			$this->label       = __( 'Post', 'acf' );
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
			if ( isset( $screen['post_id'] ) ) {
				$post_id = $screen['post_id'];
			} else {
				return false;
			}

			// Compare rule against post_id.
			return $this->compare_to_rule( $post_id, $rule );
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

			// Get post types.
			$post_types = acf_get_post_types(
				array(
					'show_ui' => 1,
					'exclude' => array( 'page', 'attachment' ),
				)
			);

			// Get grouped posts.
			$groups = acf_get_grouped_posts(
				array(
					'post_type' => $post_types,
				)
			);

			// Append to choices.
			if ( $groups ) {
				foreach ( $groups as $label => $posts ) {
					$choices[ $label ] = array();
					foreach ( $posts as $post ) {
						$choices[ $label ][ $post->ID ] = acf_get_post_title( $post );
					}
				}
			}
			return $choices;
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Post' );
endif; // class_exists check
