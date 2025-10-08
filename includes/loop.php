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

if ( ! class_exists( 'acf_loop' ) ) :
	class acf_loop {

		/**
		 * An array of loops.
		 * @var array
		 */
		public $loops = array();

		/**
		 * This function will return true if no loops exist
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   n/a
		 * @return  (boolean)
		 */
		function is_empty() {

			return empty( $this->loops );
		}


		/**
		 * This function will return true if a loop exists for the given array index
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $i (int)
		 * @return  (boolean)
		 */
		function is_loop( $i = 0 ) {

			return isset( $this->loops[ $i ] );
		}


		/**
		 * This function will return a valid array index for the given $i
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $i (mixed)
		 * @return  (int)
		 */
		function get_i( $i = 0 ) {

			// 'active'
			if ( $i === 'active' ) {
				$i = -1;
			}

			// 'previous'
			if ( $i === 'previous' ) {
				$i = -2;
			}

			// allow negative to look at end of loops
			if ( $i < 0 ) {
				$i = count( $this->loops ) + $i;
			}

			// return
			return $i;
		}


		/**
		 * This function will add a new loop
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $loop (array)
		 * @return  n/a
		 */
		function add_loop( $loop = array() ) {

			// defaults
			$loop = wp_parse_args(
				$loop,
				array(
					'selector' => '',
					'name'     => '',
					'value'    => false,
					'field'    => false,
					'i'        => -1,
					'post_id'  => 0,
					'key'      => '',
				)
			);

			// ensure array
			$loop['value'] = acf_get_array( $loop['value'] );

			// Re-index values if this loop starts from index 0.
			// This allows ajax previews to work ($_POST data contains random unique array keys)
			if ( $loop['i'] == -1 ) {
				$loop['value'] = array_values( $loop['value'] );
			}

			// append
			$this->loops[] = $loop;

			// return
			return $loop;
		}


		/**
		 * This function will update a loop's setting
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $i (mixed)
		 * @param   $key (string) the loop setting name
		 * @param   $value (mixed) the loop setting value
		 * @return  (boolean) true on success
		 */
		function update_loop( $i = 'active', $key = null, $value = null ) {

			// i
			$i = $this->get_i( $i );

			// bail early if no set
			if ( ! $this->is_loop( $i ) ) {
				return false;
			}

			// set
			$this->loops[ $i ][ $key ] = $value;

			// return
			return true;
		}


		/**
		 * This function will return a loop, or loop's setting for a given index & key
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $i (mixed)
		 * @param   $key (string) the loop setting name
		 * @return  (mixed) false on failure
		 */
		function get_loop( $i = 'active', $key = null ) {

			// i
			$i = $this->get_i( $i );

			// bail early if no set
			if ( ! $this->is_loop( $i ) ) {
				return false;
			}

			// check for key
			if ( $key !== null ) {
				return $this->loops[ $i ][ $key ];
			}

			// return
			return $this->loops[ $i ];
		}


		/**
		 * This function will remove a loop
		 *
		 * @type    function
		 * @date    3/03/2016
		 * @since   5.3.2
		 *
		 * @param   $i (mixed)
		 * @return  (boolean) true on success
		 */
		function remove_loop( $i = 'active' ) {

			// i
			$i = $this->get_i( $i );

			// bail early if no set
			if ( ! $this->is_loop( $i ) ) {
				return false;
			}

			// remove
			unset( $this->loops[ $i ] );

			// reset keys
			$this->loops = array_values( $this->loops );

			// PHP 7.2 no longer resets array keys for empty value
			if ( $this->is_empty() ) {
				$this->loops = array();
			}
		}
	}

	// initialize
	acf()->loop = new acf_loop();
endif; // class_exists check



/**
 * alias of acf()->loop->add_loop()
 *
 * @type    function
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_add_loop( $loop = array() ) {

	return acf()->loop->add_loop( $loop );
}


/**
 * alias of acf()->loop->update_loop()
 *
 * @type    function
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_update_loop( $i = 'active', $key = null, $value = null ) {

	return acf()->loop->update_loop( $i, $key, $value );
}


/**
 * alias of acf()->loop->get_loop()
 *
 * @type    function
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_get_loop( $i = 'active', $key = null ) {

	return acf()->loop->get_loop( $i, $key );
}


/**
 * alias of acf()->loop->remove_loop()
 *
 * @type    function
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_remove_loop( $i = 'active' ) {

	return acf()->loop->remove_loop( $i );
}
