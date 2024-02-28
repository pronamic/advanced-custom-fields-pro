<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location' ) ) :
	abstract class ACF_Location extends ACF_Legacy_Location {

		/**
		 * The location rule name.
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $name = '';

		/**
		 * The location rule label.
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $label = '';

		/**
		 * The location rule category.
		 *
		 * Accepts "post", "page", "user", "forms" or a custom label.
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $category = 'post';

		/**
		 * Whether or not the location rule is publicly accessible.
		 *
		 * @since 5.0.0
		 * @var boolean
		 */
		public $public = true;

		/**
		 * The object type related to this location rule.
		 *
		 * Accepts an object type discoverable by `acf_get_object_type()`.
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $object_type = '';

		/**
		 * The object subtype related to this location rule.
		 *
		 * Accepts a custom post type or custom taxonomy.
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $object_subtype = '';

		/**
		 * Constructor.
		 *
		 * @date    8/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function __construct() {
			$this->initialize();

			// Call legacy constructor.
			parent::__construct();
		}

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
			// Set props here.
		}

		/**
		 * Returns an array of operators for this location.
		 *
		 * @date    9/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule A location rule.
		 * @return  array
		 */
		public static function get_operators( $rule ) {
			return array(
				'==' => __( 'is equal to', 'acf' ),
				'!=' => __( 'is not equal to', 'acf' ),
			);
		}

		/**
		 * Returns an array of possible values for this location.
		 *
		 * @date    9/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule A location rule.
		 * @return  array
		 */
		public function get_values( $rule ) {
			return array();
		}

		/**
		 * Returns the object_type connected to this location.
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param   array $rule A location rule.
		 * @return  string
		 */
		public function get_object_type( $rule ) {
			return $this->object_type;
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
			return $this->object_subtype;
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
			return false;
		}

		/**
		 * Compares the given value and rule params returning true when they match.
		 *
		 * @date    17/9/19
		 * @since   5.8.1
		 *
		 * @param   array $rule  The location rule data.
		 * @param   mixed $value The value to compare against.
		 * @return  boolean
		 */
		public function compare_to_rule( $value, $rule ) {
			$result = ( $value == $rule['value'] );

			// Allow "all" to match any value.
			if ( $rule['value'] === 'all' ) {
				$result = true;
			}

			// Reverse result for "!=" operator.
			if ( $rule['operator'] === '!=' ) {
				return ! $result;
			}
			return $result;
		}
	}

endif; // class_exists check
