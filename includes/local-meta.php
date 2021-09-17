<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Local_Meta' ) ) :

	class ACF_Local_Meta {

		/** @var array Storage for meta data. */
		var $meta = array();

		/** @var mixed Storage for the current post_id. */
		var $post_id = 0;

		/**
		 * __construct
		 *
		 * Sets up the class functionality.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   void
		 * @return  void
		 */
		function __construct() {

			// add filters
			add_filter( 'acf/pre_load_post_id', array( $this, 'pre_load_post_id' ), 1, 2 );
			add_filter( 'acf/pre_load_meta', array( $this, 'pre_load_meta' ), 1, 2 );
			add_filter( 'acf/pre_load_metadata', array( $this, 'pre_load_metadata' ), 1, 4 );
		}

		/**
		 * add
		 *
		 * Adds postmeta to storage.
		 * Accepts data in either raw or request format.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   array $meta An array of metdata to store.
		 * @param   mixed $post_id The post_id for this data.
		 * @param   bool  $is_main Makes this postmeta visible to get_field() without a $post_id value.
		 * @return  array
		 */
		function add( $meta = array(), $post_id = 0, $is_main = false ) {

			// Capture meta if supplied meta is from a REQUEST.
			if ( $this->is_request( $meta ) ) {
				$meta = $this->capture( $meta, $post_id );
			}

			// Add to storage.
			$this->meta[ $post_id ] = $meta;

			// Set $post_id reference when is the "main" postmeta.
			if ( $is_main ) {
				$this->post_id = $post_id;
			}

			// Return meta.
			return $meta;
		}

		/**
		 * is_request
		 *
		 * Returns true if the supplied $meta is from a REQUEST (serialized <form> data).
		 *
		 * @date    11/3/19
		 * @since   5.7.14
		 *
		 * @param   array $meta An array of metdata to check.
		 * @return  bool
		 */
		function is_request( $meta = array() ) {
			return acf_is_field_key( key( $meta ) );
		}

		/**
		 * capture
		 *
		 * Returns a flattened array of meta for the given postdata.
		 * This is achieved by simulating a save whilst capturing all meta changes.
		 *
		 * @date    26/2/19
		 * @since   5.7.13
		 *
		 * @param   array $values An array of raw values.
		 * @param   mixed $post_id The post_id for this data.
		 * @return  array
		 */
		function capture( $values = array(), $post_id = 0 ) {

			// Reset meta.
			$this->meta[ $post_id ] = array();

			// Listen for any added meta.
			add_filter( 'acf/pre_update_metadata', array( $this, 'capture_update_metadata' ), 1, 5 );

			// Simulate update.
			if ( $values ) {
				acf_update_values( $values, $post_id );
			}

			// Remove listener filter.
			remove_filter( 'acf/pre_update_metadata', array( $this, 'capture_update_metadata' ), 1, 5 );

			// Return meta.
			return $this->meta[ $post_id ];
		}

		/**
		 * capture_update_metadata
		 *
		 * Records all meta activity and returns a non null value to bypass DB updates.
		 *
		 * @date    26/2/19
		 * @since   5.7.13
		 *
		 * @param   null         $null .
		 * @param   (int|string) $post_id The post id.
		 * @param   string       $name The meta name.
		 * @param   mixed        $value The meta value.
		 * @param   bool         $hidden If the meta is hidden (starts with an underscore).
		 * @return  false.
		 */
		function capture_update_metadata( $null, $post_id, $name, $value, $hidden ) {
			$name                            = ( $hidden ? '_' : '' ) . $name;
			$this->meta[ $post_id ][ $name ] = $value;

			// Return non null value to escape update process.
			return true;
		}

		/**
		 * remove
		 *
		 * Removes postmeta from storage.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   mixed $post_id The post_id for this data.
		 * @return  void
		 */
		function remove( $post_id = 0 ) {

			// unset meta
			unset( $this->meta[ $post_id ] );

			// reset post_id
			if ( $post_id === $this->post_id ) {
				$this->post_id = 0;
			}
		}

		/**
		 * pre_load_meta
		 *
		 * Injects the local meta.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   null  $null An empty parameter. Return a non null value to short-circuit the function.
		 * @param   mixed $post_id The post_id for this data.
		 * @return  mixed
		 */
		function pre_load_meta( $null, $post_id ) {
			if ( isset( $this->meta[ $post_id ] ) ) {
				return $this->meta[ $post_id ];
			}
			return $null;
		}

		/**
		 * pre_load_metadata
		 *
		 * Injects the local meta.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   null         $null An empty parameter. Return a non null value to short-circuit the function.
		 * @param   (int|string) $post_id The post id.
		 * @param   string       $name The meta name.
		 * @param   bool         $hidden If the meta is hidden (starts with an underscore).
		 * @return  mixed
		 */
		function pre_load_metadata( $null, $post_id, $name, $hidden ) {
			$name = ( $hidden ? '_' : '' ) . $name;
			if ( isset( $this->meta[ $post_id ] ) ) {
				if ( isset( $this->meta[ $post_id ][ $name ] ) ) {
					return $this->meta[ $post_id ][ $name ];
				}
				return '__return_null';
			}
			return $null;
		}

		/**
		 * pre_load_post_id
		 *
		 * Injects the local post_id.
		 *
		 * @date    8/10/18
		 * @since   5.8.0
		 *
		 * @param   null  $null An empty parameter. Return a non null value to short-circuit the function.
		 * @param   mixed $post_id The post_id for this data.
		 * @return  mixed
		 */
		function pre_load_post_id( $null, $post_id ) {
			if ( ! $post_id && $this->post_id ) {
				return $this->post_id;
			}
			return $null;
		}
	}

endif; // class_exists check

/**
 * acf_setup_meta
 *
 * Adds postmeta to storage.
 *
 * @date    8/10/18
 * @since   5.8.0
 * @see     ACF_Local_Meta::add() for list of parameters.
 *
 * @return  array
 */
function acf_setup_meta( $meta = array(), $post_id = 0, $is_main = false ) {
	return acf_get_instance( 'ACF_Local_Meta' )->add( $meta, $post_id, $is_main );
}

/**
 * acf_reset_meta
 *
 * Removes postmeta to storage.
 *
 * @date    8/10/18
 * @since   5.8.0
 * @see     ACF_Local_Meta::remove() for list of parameters.
 *
 * @return  void
 */
function acf_reset_meta( $post_id = 0 ) {
	return acf_get_instance( 'ACF_Local_Meta' )->remove( $post_id );
}
