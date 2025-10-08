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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is already defined, return.
if ( class_exists( 'ACF_Rest_Api' ) ) {
	return;
}

/**
 * Class ACF_Rest_Embed_Links
 *
 * Manage the addition of embed links on supported REST endpoints.
 */
class ACF_Rest_Embed_Links {

	/** @var array Links to add to the response. These can be flagged as embeddable and expanded when _embed is passed with the request. */
	private $links = array();

	public function initialize() {
		$this->hook_link_handlers();
	}

	/**
	 * Hook into all REST-enabled post type, taxonomy, and the user controllers in order to prepare links.
	 */
	private function hook_link_handlers() {
		foreach ( get_post_types( array( 'show_in_rest' => true ) ) as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", array( $this, 'load_item_links' ), 10, 3 );
		}

		foreach ( get_taxonomies( array( 'show_in_rest' => true ) ) as $taxonomy ) {
			add_filter( "rest_prepare_{$taxonomy}", array( $this, 'load_item_links' ), 10, 3 );
		}

		add_filter( 'rest_prepare_user', array( $this, 'load_item_links' ), 10, 3 );
	}

	/**
	 * Add links to internal property for subsequent use in \ACF_Rest_Embed_Links::load_item_links().
	 *
	 * @param       $post_id
	 * @param array   $field
	 */
	public function prepare_links( $post_id, array $field ) {
		$links = acf_get_field_rest_links( $post_id, $field );
		if ( ! $links ) {
			return;
		}

		foreach ( $links as $link ) {
			// If required array keys are not provided, skip.
			if ( empty( $link['rel'] ) or empty( $link['href'] ) ) {
				continue;
			}

			// Use the 'rel' and 'href' to for a key. The key only prevents against the same object
			// appearing more than once within the same 'rel' property.
			$this->links[ $link['rel'] . ':' . $link['href'] ] = $link;
		}
	}

	/**
	 * Hook into the rest_prepare_{$type} filters and add links for the object being prepared.
	 *
	 * @param WP_REST_Response        $response
	 * @param WP_Post|WP_User|WP_Term $item
	 * @param WP_REST_Request         $request
	 * @return WP_REST_Response
	 */
	public function load_item_links( $response, $item, $request ) {
		if ( empty( $this->links ) ) {
			return $response;
		}

		while ( $attributes = array_pop( $this->links ) ) {
			$response->add_link(
				acf_extract_var( $attributes, 'rel' ),
				acf_extract_var( $attributes, 'href' ),
				$attributes
			);
		}

		// Reset the links prop.
		$this->links = array();

		return $response;
	}
}
