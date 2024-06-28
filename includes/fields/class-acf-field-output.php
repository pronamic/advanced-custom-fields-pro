<?php

if ( ! class_exists( 'acf_field_output' ) ) :

	/**
	 * This class and field type has been deprecated since ACF 6.3.2 and will not output anything.
	 */
	class acf_field_output extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @since   5.0.0
		 */
		public function initialize() {

			// vars
			$this->name     = 'output';
			$this->label    = 'output';
			$this->public   = false;
			$this->defaults = array(
				'html' => false,
			);
		}


		/**
		 * The render field call. Deprecated since ACF 6.3.2.
		 *
		 * @param   array $field The $field being edited
		 * @return  false
		 */
		public function render_field( $field ) {

			// Deprecated since 6.3.2 and will be removed in a future release.
			_deprecated_function( __FUNCTION__, '6.3.2' );
			return false;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_output' );
endif; // class_exists check
