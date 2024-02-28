<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Legacy_Location' ) ) :
	abstract class ACF_Legacy_Location {

		/**
		 * Constructor.
		 *
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function __construct() {

			// Add legacy method filters.
			if ( method_exists( $this, 'rule_match' ) ) {
				add_filter( "acf/location/rule_match/{$this->name}", array( $this, 'rule_match' ), 5, 3 );
			}
			if ( method_exists( $this, 'rule_operators' ) ) {
				add_filter( "acf/location/rule_operators/{$this->name}", array( $this, 'rule_operators' ), 5, 2 );
			}
			if ( method_exists( $this, 'rule_values' ) ) {
				add_filter( "acf/location/rule_values/{$this->name}", array( $this, 'rule_values' ), 5, 2 );
			}
		}

		/**
		 * Magic __call method for backwards compatibility.
		 *
		 * @date    10/4/20
		 * @since   5.9.0
		 *
		 * @param   string $name      The method name.
		 * @param   array  $arguments The array of arguments.
		 * @return  mixed
		 */
		public function __call( $name, $arguments ) {

			// Add backwards compatibility for legacy methods.
			// - Combine 3x legacy filters cases together (remove first args).
			switch ( $name ) {
				case 'rule_match':
					$method       = isset( $method ) ? $method : 'match';
					$arguments[3] = isset( $arguments[3] ) ? $arguments[3] : false; // Add $field_group param.
				case 'rule_operators':
					$method = isset( $method ) ? $method : 'get_operators';
				case 'rule_values':
					$method = isset( $method ) ? $method : 'get_values';
					array_shift( $arguments );
					return call_user_func_array( array( $this, $method ), $arguments );
				case 'compare':
					return call_user_func_array( array( $this, 'compare_to_rule' ), $arguments );
			}
		}
	}

endif; // class_exists check
