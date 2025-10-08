<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax_User_Setting' ) ) :

	class ACF_Ajax_User_Setting extends ACF_Ajax {

		/**
		 * The AJAX action name.
		 *
		 * @var string
		 */
		public $action = 'acf/ajax/user_setting';

		/**
		 * Prevents access for non-logged in users.
		 *
		 * @var boolean
		 */
		public $public = false;

		/**
		 * get_response
		 *
		 * Returns the response data to sent back.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   array $request The request args.
		 * @return  mixed The response data or WP_Error.
		 */
		public function get_response( $request ) {
			if ( ! acf_current_user_can_admin() ) {
				return new WP_Error( 'acf_invalid_permissions', __( 'Sorry, you do not have permission to do that.', 'acf' ) );
			}

			// update
			if ( $this->has( 'value' ) ) {
				return acf_update_user_setting( $this->get( 'name' ), $this->get( 'value' ) );

				// get
			} else {
				return acf_get_user_setting( $this->get( 'name' ) );
			}
		}
	}

	acf_new_instance( 'ACF_Ajax_User_Setting' );
endif; // class_exists check
