<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax_Upgrade' ) ) :

	class ACF_Ajax_Upgrade extends ACF_Ajax {

		/** @var string The AJAX action name */
		var $action = 'acf/ajax/upgrade';

		/**
		 * Returns the response data to sent back.
		 *
		 * @since 5.7.2
		 *
		 * @param array $request The request args.
		 * @return boolean|WP_Error True if successful, or WP_Error on failure.
		 */
		public function get_response( $request ) {
			if ( ! current_user_can( acf_get_setting( 'capability' ) ) ) {
				return new WP_Error( 'upgrade_error', __( 'Sorry, you don\'t have permission to do that.', 'acf' ) );
			}

			// Switch blog.
			if ( isset( $request['blog_id'] ) ) {
				switch_to_blog( $request['blog_id'] );
			}

			// Bail early if no upgrade avaiable.
			if ( ! acf_has_upgrade() ) {
				return new WP_Error( 'upgrade_error', __( 'No updates available.', 'acf' ) );
			}

			// Listen for output.
			ob_start();

			// Run upgrades.
			acf_upgrade_all();

			// Store output.
			$error = ob_get_clean();

			// Return error or success.
			if ( $error ) {
				return new WP_Error( 'upgrade_error', $error );
			}

			return true;
		}
	}

	acf_new_instance( 'ACF_Ajax_Upgrade' );
endif; // class_exists check
