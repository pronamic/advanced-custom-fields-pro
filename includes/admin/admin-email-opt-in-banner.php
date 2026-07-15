<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ACF_Admin_Email_Opt_In_Banner' ) ) :

	/**
	 * Renders the ACF Free email opt-in banner on ACF admin screens
	 * (Field Groups, Post Types, Taxonomies).
	 *
	 * Dismissed/submitted state is persisted per-site in the
	 * `acf_email_opt_in_banner_state` option.
	 */
	class ACF_Admin_Email_Opt_In_Banner {

		/**
		 * Option key used to persist the banner's dismissed/submitted state.
		 * Stored per-subsite on multisite (uses `get_option`/`update_option`,
		 * not `get_site_option`).
		 *
		 * @var string
		 */
		const STATE_OPTION = 'acf_email_opt_in_banner_state';

		/**
		 * Persisted state value set when the banner is dismissed.
		 *
		 * @var string
		 */
		const STATE_DISMISSED = 'dismissed';

		/**
		 * Persisted state value set after a successful submission.
		 *
		 * @var string
		 */
		const STATE_SUBMITTED = 'submitted';

		/**
		 * Constructor.
		 *
		 * @since 6.8.6
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'current_screen' ) );
			add_action( 'wp_ajax_acf/email_opt_in_banner/state', array( $this, 'ajax_set_state' ) );
			add_action( 'wp_ajax_acf/email_opt_in_banner/submit', array( $this, 'ajax_submit' ) );
		}

		/**
		 * Hooks the banner onto ACF admin screens only.
		 *
		 * @since 6.8.6
		 */
		public function current_screen() {
			if ( ! $this->should_show() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_footer', array( $this, 'render' ) );
		}

		/**
		 * Determines if the banner should be shown for the current request.
		 *
		 * @since 6.8.6
		 *
		 * @return boolean
		 */
		public function should_show() {
			if ( ! acf_get_setting( 'show_admin' ) ) {
				return false;
			}

			if ( ! current_user_can( acf_get_setting( 'capability' ) ) ) {
				return false;
			}

			if ( ! $this->is_supported_screen() ) {
				return false;
			}

			$default = ! $this->is_state_persisted();

			/**
			 * Filters whether the ACF Free email opt-in banner should be shown.
			 *
			 * @since 6.8.6
			 *
			 * @param boolean $show Whether to show the banner.
			 */
			return (bool) apply_filters( 'acf/admin/show_email_opt_in_banner', $default );
		}

		/**
		 * Returns true on the ACF Free admin screens the banner supports:
		 * the Field Groups, Post Types and Taxonomies list screens, and the
		 * Options Pages preview.
		 *
		 * @since 6.8.6
		 *
		 * @return boolean
		 */
		public function is_supported_screen() {
			$screens = array(
				'edit-acf-field-group',
				'edit-acf-post-type',
				'edit-acf-taxonomy',
			);

			foreach ( $screens as $screen ) {
				if ( acf_is_screen( $screen ) ) {
					return true;
				}
			}

			// The Options Pages preview is a submenu page rather than a list screen.
			if ( 'acf_options_preview' === acf_request_arg( 'page', '' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Returns true if the banner's state has already been persisted by a
		 * previous dismiss or submit.
		 *
		 * @since 6.8.6
		 *
		 * @return boolean
		 */
		public function is_state_persisted() {
			$state = get_option( self::STATE_OPTION, '' );

			return in_array( $state, array( self::STATE_DISMISSED, self::STATE_SUBMITTED ), true );
		}

		/**
		 * AJAX handler that persists the banner's dismissed/submitted state.
		 *
		 * @since 6.8.6
		 */
		public function ajax_set_state() {
			if ( ! acf_verify_ajax() || ! acf_current_user_can_admin() ) {
				wp_send_json_error();
			}

			$state = acf_request_arg( 'state', '' );

			if ( ! in_array( $state, array( self::STATE_DISMISSED, self::STATE_SUBMITTED ), true ) ) {
				wp_send_json_error();
			}

			update_option( self::STATE_OPTION, $state, false );

			wp_send_json_success( array( 'state' => $state ) );
		}

		/**
		 * AJAX handler that submits the opt-in and persists the `submitted`
		 * state on success.
		 *
		 * @since 6.8.6
		 */
		public function ajax_submit() {
			if ( ! acf_verify_ajax() || ! acf_current_user_can_admin() ) {
				wp_send_json_error();
			}

			if ( self::STATE_SUBMITTED === get_option( self::STATE_OPTION, '' ) ) {
				wp_send_json_success( array( 'state' => self::STATE_SUBMITTED ) );
			}

			$email = sanitize_email( (string) acf_request_arg( 'email', '' ) );
			if ( ! is_email( $email ) ) {
				wp_send_json_error();
			}

			$response = $this->send_opt_in( $this->build_opt_in_payload( $email ) );

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				wp_send_json_error();
			}

			update_option( self::STATE_OPTION, self::STATE_SUBMITTED, false );

			wp_send_json_success( array( 'state' => self::STATE_SUBMITTED ) );
		}

		/**
		 * Builds the opt-in submission payload in the API shape expected
		 * by the endpoint that subscribes the email address to ACF
		 * updates and news.
		 *
		 * @since 6.8.6
		 *
		 * @param string $email Sanitized email address.
		 * @return array
		 */
		private function build_opt_in_payload( $email ) {
			return array(
				'fields' => array(
					array(
						'objectTypeId' => '0-1',
						'name'         => 'email',
						'value'        => $email,
					),
				),
			);
		}

		/**
		 * POSTs a prepared opt-in payload to the endpoint that subscribes the
		 * email address to ACF updates and news.
		 *
		 * @since 6.8.6
		 *
		 * @param array $payload The payload from `build_opt_in_payload`.
		 * @return array|WP_Error The `wp_remote_post` response.
		 */
		private function send_opt_in( $payload ) {
			$url = 'https://api.hsforms.com/submissions/v3/integration/submit/46851451/e420c547-8255-4339-888c-32c58e36a80f';

			return wp_remote_post(
				$url,
				array(
					'timeout' => 10,
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( $payload ),
				)
			);
		}

		/**
		 * Enqueues the banner script and localized strings.
		 *
		 * @since 6.8.6
		 */
		public function admin_enqueue_scripts() {
			$suffix  = defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ? '' : '.min';
			$version = acf_get_setting( 'version' );

			wp_register_script( 'acf-email-opt-in-banner', acf_get_url( 'assets/build/js/acf-email-opt-in-banner' . $suffix . '.js' ), array( 'jquery', 'acf' ), $version, true );
			wp_enqueue_script( 'acf-email-opt-in-banner' );

			wp_localize_script(
				'acf-email-opt-in-banner',
				'acf_email_opt_in_banner',
				array(
					'empty_email'   => __( 'Email address is required.', 'acf' ),
					'invalid_email' => __( 'Please enter a valid email address.', 'acf' ),
					'generic_error' => __( 'Something went wrong on our end. Please try again.', 'acf' ),
				)
			);
		}

		/**
		 * Renders the banner markup in the admin footer for JS to relocate.
		 *
		 * The email field is intentionally not prefilled to avoid accidental opt-ins.
		 *
		 * @since 6.8.6
		 */
		public function render() {
			acf_get_view( 'email-opt-in-banner' );
		}
	}

	new ACF_Admin_Email_Opt_In_Banner();
endif;
