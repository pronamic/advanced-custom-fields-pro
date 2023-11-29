<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Location_Attachment' ) ) :

	class ACF_Location_Attachment extends ACF_Location {

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
			$this->name        = 'attachment';
			$this->label       = __( 'Attachment', 'acf' );
			$this->category    = 'forms';
			$this->object_type = 'attachment';
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
			if ( isset( $screen['attachment'] ) ) {
				$attachment = $screen['attachment'];
			} else {
				return false;
			}

			// Get attachment mime type
			$mime_type = get_post_mime_type( $attachment );

			// Allow for unspecific mim_type matching such as "image" or "video".
			if ( ! strpos( $rule['value'], '/' ) ) {

				// Explode mime_type into bits ([0] => type, [1] => subtype) and match type.
				$bits = explode( '/', $mime_type );
				if ( $bits[0] === $rule['value'] ) {
					$mime_type = $rule['value'];
				}
			}
			return $this->compare_to_rule( $mime_type, $rule );
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
			$choices = array(
				'all' => __( 'All', 'acf' ),
			);

			// Get mime types and append into optgroups.
			$mime_types = get_allowed_mime_types();
			foreach ( $mime_types as $regex => $mime_type ) {

				// Get type "image" from mime_type "image/jpeg".
				$type = current( explode( '/', $mime_type ) );

				// Append group and mimetype.
				$choices[ $type ][ $type ]      = sprintf( __( 'All %s formats', 'acf' ), $type );
				$choices[ $type ][ $mime_type ] = "$regex ($mime_type)";
			}

			// return
			return $choices;
		}
	}

	// Register.
	acf_register_location_type( 'ACF_Location_Attachment' );
endif; // class_exists check
