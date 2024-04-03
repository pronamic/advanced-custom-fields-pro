<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Block' ) ) :

	class ACF_Location_Block extends ACF_Location {

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
			$this->name        = 'block';
			$this->label       = __( 'Block', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'block';

			add_filter( 'acf/field_group/list_table_classes', array( $this, 'field_group_list_table_classes' ), 10, 3 );
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
			if ( isset( $screen['block'] ) ) {
				$block = $screen['block'];
			} else {
				return false;
			}

			// Compare rule against $block.
			return $this->compare_to_rule( $block, $rule );
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

			// Append block types.
			$blocks = acf_get_block_types();
			if ( $blocks ) {
				$choices['all'] = __( 'All', 'acf' );
				foreach ( $blocks as $block ) {
					$choices[ $block['name'] ] = $block['title'];
				}
			} else {
				$choices[''] = __( 'No block types exist', 'acf' );
			}

			// Return choices.
			return $choices;
		}

		/**
		 * Adds block-specific classes to field groups in the Field Groups list table.
		 *
		 * @since 6.2.8
		 *
		 * @param array   $classes   An array of the classes used by the field group.
		 * @param array   $css_class An array of additional classes added to the field group.
		 * @param integer $post_id   The ID of the field group.
		 * @return array
		 */
		public function field_group_list_table_classes( $classes, $css_class, $post_id ) {
			// Add a CSS class if the field group has a block location.
			if ( acf_field_group_has_location_type( $post_id, 'block' ) ) {
				$classes[] = 'acf-has-block-location';
			}

			return $classes;
		}
	}

	// initialize
	acf_register_location_type( 'ACF_Location_Block' );
endif; // class_exists check
