<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Data' ) ) :
	#[AllowDynamicProperties]
	class ACF_Data {

		/** @var string Unique identifier. */
		var $cid = '';

		/** @var array Storage for data. */
		var $data = array();

		/** @var array Storage for data aliases. */
		var $aliases = array();

		/** @var boolean Enables unique data per site. */
		var $multisite = false;

		/**
		 * __construct
		 *
		 * Sets up the class functionality.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   array $data Optional data to set.
		 * @return  void
		 */
		function __construct( $data = false ) {

			// Set cid.
			$this->cid = acf_uniqid();

			// Set data.
			if ( $data ) {
				$this->set( $data );
			}

			// Initialize.
			$this->initialize();
		}

		/**
		 * initialize
		 *
		 * Called during constructor to setup class functionality.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {
			// Do nothing.
		}

		/**
		 * prop
		 *
		 * Sets a property for the given name and returns $this for chaining.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   (string|array) $name  The data name or an array of data.
		 * @param   mixed          $value The data value.
		 * @return  ACF_Data
		 */
		function prop( $name = '', $value = null ) {

			// Update property.
			$this->{$name} = $value;

			// Return this for chaining.
			return $this;
		}

		/**
		 * _key
		 *
		 * Returns a key for the given name allowing aliasses to work.
		 *
		 * @date    18/1/19
		 * @since   5.7.10
		 *
		 * @param   type $var Description. Default.
		 * @return  type Description.
		 */
		function _key( $name = '' ) {
			return isset( $this->aliases[ $name ] ) ? $this->aliases[ $name ] : $name;
		}

		/**
		 * has
		 *
		 * Returns true if this has data for the given name.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   string $name The data name.
		 * @return  boolean
		 */
		function has( $name = '' ) {
			$key = $this->_key( $name );
			return isset( $this->data[ $key ] );
		}

		/**
		 * is
		 *
		 * Similar to has() but does not check aliases.
		 *
		 * @date    7/2/19
		 * @since   5.7.11
		 *
		 * @param   type $var Description. Default.
		 * @return  type Description.
		 */
		function is( $key = '' ) {
			return isset( $this->data[ $key ] );
		}

		/**
		 * get
		 *
		 * Returns data for the given name of null if doesn't exist.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   string $name The data name.
		 * @return  mixed
		 */
		function get( $name = false ) {

			// Get all.
			if ( $name === false ) {
				return $this->data;

				// Get specific.
			} else {
				$key = $this->_key( $name );
				return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
			}
		}

		/**
		 * get_data
		 *
		 * Returns an array of all data.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   void
		 * @return  array
		 */
		function get_data() {
			return $this->data;
		}

		/**
		 * set
		 *
		 * Sets data for the given name and returns $this for chaining.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   (string|array) $name  The data name or an array of data.
		 * @param   mixed          $value The data value.
		 * @return  ACF_Data
		 */
		function set( $name = '', $value = null ) {

			// Set multiple.
			if ( is_array( $name ) ) {
				$this->data = array_merge( $this->data, $name );

				// Set single.
			} else {
				$this->data[ $name ] = $value;
			}

			// Return this for chaining.
			return $this;
		}

		/**
		 * append
		 *
		 * Appends data for the given name and returns $this for chaining.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   mixed $value The data value.
		 * @return  ACF_Data
		 */
		function append( $value = null ) {

			// Append.
			$this->data[] = $value;

			// Return this for chaining.
			return $this;
		}

		/**
		 * remove
		 *
		 * Removes data for the given name.
		 *
		 * @date    9/1/19
		 * @since   5.7.10
		 *
		 * @param   string $name The data name.
		 * @return  ACF_Data
		 */
		function remove( $name = '' ) {

			// Remove data.
			unset( $this->data[ $name ] );

			// Return this for chaining.
			return $this;
		}

		/**
		 * reset
		 *
		 * Resets the data.
		 *
		 * @date    22/1/19
		 * @since   5.7.10
		 *
		 * @param   void
		 * @return  void
		 */
		function reset() {
			$this->data    = array();
			$this->aliases = array();
		}

		/**
		 * count
		 *
		 * Returns the data count.
		 *
		 * @date    23/1/19
		 * @since   5.7.10
		 *
		 * @param   void
		 * @return  integer
		 */
		function count() {
			return count( $this->data );
		}

		/**
		 * query
		 *
		 * Returns a filtered array of data based on the set of key => value arguments.
		 *
		 * @date    23/1/19
		 * @since   5.7.10
		 *
		 * @param   void
		 * @return  integer
		 */
		function query( $args, $operator = 'AND' ) {
			return wp_list_filter( $this->data, $args, $operator );
		}

		/**
		 * alias
		 *
		 * Sets an alias for the given name allowing data to be found via multiple identifiers.
		 *
		 * @date    18/1/19
		 * @since   5.7.10
		 *
		 * @param   type $var Description. Default.
		 * @return  type Description.
		 */
		function alias( $name = '' /*, $alias, $alias2, etc */ ) {

			// Get all aliases.
			$args = func_get_args();
			array_shift( $args );

			// Loop over aliases and add to data.
			foreach ( $args as $alias ) {
				$this->aliases[ $alias ] = $name;
			}

			// Return this for chaining.
			return $this;
		}

		/**
		 * switch_site
		 *
		 * Triggered when switching between sites on a multisite installation.
		 *
		 * @date    13/2/19
		 * @since   5.7.11
		 *
		 * @param   integer                       $site_id New blog ID.
		 * @param   int prev_blog_id Prev blog ID.
		 * @return  void
		 */
		function switch_site( $site_id, $prev_site_id ) {

			// Bail early if not multisite compatible.
			if ( ! $this->multisite ) {
				return;
			}

			// Bail early if no change in blog ID.
			if ( $site_id === $prev_site_id ) {
				return;
			}

			// Create storage.
			if ( ! isset( $this->site_data ) ) {
				$this->site_data    = array();
				$this->site_aliases = array();
			}

			// Save state.
			$this->site_data[ $prev_site_id ]    = $this->data;
			$this->site_aliases[ $prev_site_id ] = $this->aliases;

			// Reset state.
			$this->data    = array();
			$this->aliases = array();

			// Load state.
			if ( isset( $this->site_data[ $site_id ] ) ) {
				$this->data    = $this->site_data[ $site_id ];
				$this->aliases = $this->site_aliases[ $site_id ];
				unset( $this->site_data[ $site_id ] );
				unset( $this->site_aliases[ $site_id ] );
			}
		}
	}

endif; // class_exists check
