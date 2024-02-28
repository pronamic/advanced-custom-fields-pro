<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax_Query' ) ) :

	class ACF_Ajax_Query extends ACF_Ajax {

		/** @var boolean Prevents access for non-logged in users. */
		var $public = true;

		/** @var integer The page of results to return. */
		var $page = 1;

		/** @var integer The number of results per page. */
		var $per_page = 20;

		/** @var boolean Signifies whether or not this AJAX query has more pages to load. */
		var $more = false;

		/** @var string The searched term. */
		var $search = '';

		/** @var boolean Signifies whether the current query is a search. */
		var $is_search = false;

		/** @var (int|string) The post_id being edited. */
		var $post_id = 0;

		/** @var array The ACF field related to this query. */
		var $field = false;

		/**
		 * get_response
		 *
		 * Returns the response data to sent back.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   array $request The request args.
		 * @return  (array|WP_Error) The response data or WP_Error.
		 */
		function get_response( $request ) {

			// Init request.
			$this->init_request( $request );

			// Get query args.
			$args = $this->get_args( $request );

			// Get query results.
			$results = $this->get_results( $args );
			if ( is_wp_error( $results ) ) {
				return $results;
			}

			// Return response.
			return array(
				'results' => $results,
				'more'    => $this->more,
			);
		}

		/**
		 * init_request
		 *
		 * Called at the beginning of a request to setup properties.
		 *
		 * @date    23/5/19
		 * @since   5.8.1
		 *
		 * @param   array $request The request args.
		 * @return  void
		 */
		function init_request( $request ) {

			// Get field for this query.
			if ( isset( $request['field_key'] ) ) {
				$this->field = acf_get_field( $request['field_key'] );
			}

			// Update query properties.
			if ( isset( $request['page'] ) ) {
				$this->page = intval( $request['page'] );
			}
			if ( isset( $request['per_page'] ) ) {
				$this->per_page = intval( $request['per_page'] );
			}
			if ( isset( $request['search'] ) && acf_not_empty( $request['search'] ) ) {
				$this->search    = sanitize_text_field( $request['search'] );
				$this->is_search = true;
			}
			if ( isset( $request['post_id'] ) ) {
				$this->post_id = $request['post_id'];
			}
		}

		/**
		 * get_args
		 *
		 * Returns an array of args for this query.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   array $request The request args.
		 * @return  array
		 */
		function get_args( $request ) {

			// Allow for custom "query" arg.
			if ( isset( $request['query'] ) ) {
				return (array) $request['query'];
			}
			return array();
		}

		/**
		 * get_items
		 *
		 * Returns an array of results for the given args.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   array args The query args.
		 * @return  array
		 */
		function get_results( $args ) {
			return array();
		}

		/**
		 * get_item
		 *
		 * Returns a single result for the given item object.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param   mixed $item A single item from the queried results.
		 * @return  array An array containing "id" and "text".
		 */
		function get_result( $item ) {
			return false;
		}
	}

endif; // class_exists check
