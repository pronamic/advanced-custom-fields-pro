<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax' ) ) :

	class ACF_Ajax {

		/** @var string The AJAX action name. */
		var $action = '';

		/** @var array The $_REQUEST data. */
		var $request;

		/** @var boolean Prevents access for non-logged in users. */
		var $public = false;

		/**
		 * __construct
		 *
		 * Sets up the class functionality.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   void
		 * @return  void
		 */
		function __construct() {
			$this->initialize();
			$this->add_actions();
		}

		/**
		 * has
		 *
		 * Returns true if the request has data for the given key.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   string $key The data key.
		 * @return  boolean
		 */
		function has( $key = '' ) {
			return isset( $this->request[ $key ] );
		}

		/**
		 * get
		 *
		 * Returns request data for the given key.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   string $key The data key.
		 * @return  mixed
		 */
		function get( $key = '' ) {
			return isset( $this->request[ $key ] ) ? $this->request[ $key ] : null;
		}

		/**
		 * Sets request data for the given key.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   string $key   The data key.
		 * @param   mixed  $value The data value.
		 * @return  ACF_Ajax
		 */
		function set( $key = '', $value = null ) {
			$this->request[ $key ] = $value;
			return $this;
		}

		/**
		 * initialize
		 *
		 * Allows easy access to modifying properties without changing constructor.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {
			/* do nothing */
		}

		/**
		 * add_actions
		 *
		 * Adds the ajax actions for this response.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   void
		 * @return  void
		 */
		function add_actions() {

			// add action for logged-in users
			add_action( "wp_ajax_{$this->action}", array( $this, 'request' ) );

			// add action for non logged-in users
			if ( $this->public ) {
				add_action( "wp_ajax_nopriv_{$this->action}", array( $this, 'request' ) );
			}
		}

		/**
		 * request
		 *
		 * Callback for ajax action. Sets up properties and calls the get_response() function.
		 *
		 * @date    1/8/18
		 * @since   5.7.2
		 *
		 * @param   void
		 * @return  void
		 */
		function request() {

			// Store data for has() and get() functions.
			$this->request = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified below in verify_request().

			// Verify request and handle error.
			$error = $this->verify_request( $this->request );
			if ( is_wp_error( $error ) ) {
				$this->send( $error );
			}

			// Send response.
			$this->send( $this->get_response( $this->request ) );
		}

		/**
		 * Verifies the request.
		 *
		 * @date    9/3/20
		 * @since   5.8.8
		 *
		 * @param   array $request The request args.
		 * @return  (bool|WP_Error) True on success, WP_Error on fail.
		 */
		function verify_request( $request ) {

			// Verify nonce.
			if ( ! acf_verify_ajax() ) {
				return new WP_Error( 'acf_invalid_nonce', __( 'Invalid nonce.', 'acf' ), array( 'status' => 404 ) );
			}
			return true;
		}

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
		function get_response( $request ) {
			return true;
		}

		/**
		 * send
		 *
		 * Sends back JSON based on the $response as either success or failure.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   mixed $response The response to send back.
		 * @return  void
		 */
		function send( $response ) {

			// Return error.
			if ( is_wp_error( $response ) ) {
				$this->send_error( $response );

				// Return success.
			} else {
				wp_send_json( $response );
			}
		}

		/**
		 * Sends a JSON response for the given WP_Error object.
		 *
		 * @date    8/3/20
		 * @since   5.8.8
		 *
		 * @param   WP_Error error The error object.
		 * @return  void
		 */
		function send_error( $error ) {

			// Get error status
			$error_data = $error->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
				$status_code = $error_data['status'];
			} else {
				$status_code = 500;
			}

			wp_send_json(
				array(
					'code'    => $error->get_error_code(),
					'message' => $error->get_error_message(),
					'data'    => $error->get_error_data(),
				),
				$status_code
			);
		}
	}

endif; // class_exists check
