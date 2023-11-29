<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Post_Taxonomy' ) ) :

	class ACF_Location_Post_Taxonomy extends ACF_Location {

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
			$this->name        = 'post_taxonomy';
			$this->label       = __( 'Post Taxonomy', 'acf' );
			$this->category    = 'post';
			$this->object_type = 'post';
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
			if ( isset( $screen['post_id'] ) ) {
				$post_id = $screen['post_id'];
			} elseif ( isset( $screen['attachment_id'] ) ) {
				$post_id = $screen['attachment_id'];
			} else {
				return false;
			}

			// Get WP_Term from rule value.
			$term = acf_get_term( $rule['value'] );
			if ( ! $term || is_wp_error( $term ) ) {
				return false;
			}

			// Get terms connected to post.
			if ( isset( $screen['post_terms'] ) ) {
				$post_terms = acf_maybe_get( $screen['post_terms'], $term->taxonomy, array() );
			} else {
				$post_terms = wp_get_post_terms( $post_id, $term->taxonomy, array( 'fields' => 'ids' ) );
			}

			// If no post terms are found, and we are dealing with the "category" taxonomy, treat as default "Uncategorized" category.
			if ( ! $post_terms && $term->taxonomy == 'category' ) {
				$post_terms = array( 1 );
			}

			// Search $post_terms for a match.
			$result = ( in_array( $term->term_id, $post_terms ) || in_array( $term->slug, $post_terms ) );

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
			return acf_get_taxonomy_terms();
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
		public function get_object_subtype( $rule ) {
			if ( $rule['operator'] === '==' ) {
				$term = acf_decode_term( $rule['value'] );
				if ( $term ) {
					$taxonomy = get_taxonomy( $term['taxonomy'] );
					if ( $taxonomy ) {
						return $taxonomy->object_type;
					}
				}
			}
			return '';
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Post_Taxonomy' );
endif; // class_exists check
