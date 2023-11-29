<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Page_Type' ) ) :

	class ACF_Location_Page_Type extends ACF_Location {

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
			$this->name           = 'page_type';
			$this->label          = __( 'Page Type', 'acf' );
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
			if ( isset( $screen['post_id'] ) ) {
				$post_id = $screen['post_id'];
			} else {
				return false;
			}

			// Get post.
			$post = get_post( $post_id );
			if ( ! $post ) {
				return false;
			}

			// Compare.
			switch ( $rule['value'] ) {
				case 'front_page':
					$front_page = (int) get_option( 'page_on_front' );
					$result     = ( $front_page === $post->ID );
					break;

				case 'posts_page':
					$posts_page = (int) get_option( 'page_for_posts' );
					$result     = ( $posts_page === $post->ID );
					break;

				case 'top_level':
					$page_parent = (int) ( isset( $screen['page_parent'] ) ? $screen['page_parent'] : $post->post_parent );
					$result      = ( $page_parent === 0 );
					break;

				case 'parent':
					$children = get_posts(
						array(
							'post_type'      => $post->post_type,
							'post_parent'    => $post->ID,
							'posts_per_page' => 1,
							'fields'         => 'ids',
						)
					);
					$result   = ! empty( $children );
					break;

				case 'child':
					$page_parent = (int) ( isset( $screen['page_parent'] ) ? $screen['page_parent'] : $post->post_parent );
					$result      = ( $page_parent !== 0 );
					break;

				default:
					return false;
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
			return array(
				'front_page' => __( 'Front Page', 'acf' ),
				'posts_page' => __( 'Posts Page', 'acf' ),
				'top_level'  => __( 'Top Level Page (no parent)', 'acf' ),
				'parent'     => __( 'Parent Page (has children)', 'acf' ),
				'child'      => __( 'Child Page (has parent)', 'acf' ),
			);
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Page_Type' );
endif; // class_exists check
