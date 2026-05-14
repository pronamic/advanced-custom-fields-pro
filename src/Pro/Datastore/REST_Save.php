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

namespace ACF\Pro\Datastore;

/**
 * Handles ACF datastore saves during Gutenberg / REST post requests.
 *
 * Decodes the _acf transport meta included on REST post requests, writes
 * individual field meta to the post and (when applicable) to the revision,
 * then cleans up the transport blob. Also strips the transport blob from
 * REST responses so it never leaks to clients.
 */
class REST_Save {

	/**
	 * Decoded ACF values from the current REST save.
	 * Set in save_post_rest(), consumed in save_revision_meta().
	 *
	 * @since 6.8.1
	 * @var array|null
	 */
	private $current_acf_values = null;

	/**
	 * Post ID pending _acf cleanup.
	 * Set in save_post_rest(), consumed in cleanup_acf_transport_meta().
	 *
	 * @since 6.8.1
	 * @var integer|null
	 */
	private $pending_cleanup_post_id = null;

	/**
	 * Constructor.
	 *
	 * Defers hook registration to rest_api_init so the
	 * acf/settings/enable_datastore filter is available to themes
	 * and plugins by the time the gate is evaluated.
	 *
	 * @since 6.8.1
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'maybe_register_rest_save_hooks' ) );

		// Skip the legacy ACF_Form_Post::save_post() during the metabox AJAX
		// (meta-box-loader) that follows each Gutenberg REST save -- the REST
		// path has already saved the values, so re-running the legacy save
		// would clobber them.
		add_filter( 'acf/form-post/skip_save', array( $this, 'skip_metabox_loader_save' ), 10, 3 );
	}

	/**
	 * Returns true when the current request is the meta-box-loader AJAX
	 * and the datastore is enabled.
	 *
	 * @since 6.8.1
	 *
	 * @param boolean $skip    Whether the save should be skipped.
	 * @param integer $post_id The post ID being saved.
	 * @param mixed   $post    The post being saved.
	 * @return boolean
	 */
	public function skip_metabox_loader_save( $skip, $post_id, $post ) {
		if ( $skip ) {
			return $skip;
		}

		return acf_maybe_get_GET( 'meta-box-loader', false ) && acf_is_using_datastore();
	}

	/**
	 * Conditionally registers REST save hooks for all public post types.
	 *
	 * @since 6.8.1
	 *
	 * @return void
	 */
	public function maybe_register_rest_save_hooks() {
		if ( ! acf_is_using_datastore() ) {
			return;
		}

		add_action( '_wp_put_post_revision', array( $this, 'save_revision_meta' ), 11, 2 );

		foreach ( get_post_types( array( 'show_in_rest' => true ) ) as $post_type ) {
			// Post-save: decode _acf and write individual meta keys to post + revision.
			add_action( "rest_after_insert_{$post_type}", array( $this, 'save_post_rest' ), 10, 2 );

			// Strip _acf from post REST responses. Revisions use
			// rest_prepare_revision instead, so _acf passes through
			// for the revision viewer.
			add_filter( "rest_prepare_{$post_type}", array( $this, 'strip_acf_transport_meta' ) );
		}

		/**
		 * Autosave: WP_REST_Autosaves_Controller does not fire
		 * rest_after_insert_{post_type}, so this response filter is the only
		 * hook available in all autosave paths that carries the request object.
		 * No-ops on GET requests since the request body is empty.
		 */
		add_filter( 'rest_prepare_autosave', array( $this, 'save_autosave_rest' ), 10, 3 );

		// Fallback cleanup for the _acf transport meta. Runs at priority 20,
		// after the revision system (priority 9) has finished deciding whether
		// to create a revision. Catches orphaned _acf when no revision is
		// created (e.g., post type doesn't support revisions).
		add_action( 'wp_after_insert_post', array( $this, 'cleanup_acf_transport_meta' ), 20 );
	}

	/**
	 * Writes ACF field values to the revision after WordPress creates it.
	 *
	 * Called via _wp_put_post_revision, which fires AFTER rest_after_insert
	 * (where save_post_rest writes values to the post). At this point the
	 * decoded values are available in $this->current_acf_values.
	 *
	 * @since 6.8.1
	 *
	 * @param integer $revision_id The revision ID.
	 * @param integer $post_id     The parent post ID.
	 * @return void
	 */
	public function save_revision_meta( $revision_id, $post_id ) {
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			return;
		}

		$is_autosave = wp_is_post_autosave( $revision_id );

		// Clean up the transport-only _acf blob from the post.
		// wp_save_revisioned_meta_fields (priority 10) has already copied
		// it to the revision for future comparison.
		if ( ! $is_autosave ) {
			delete_post_meta( $post_id, '_acf' );
		}

		if ( empty( $this->current_acf_values ) || $is_autosave ) {
			return;
		}

		$values = $this->current_acf_values;

		if ( ! acf_allow_unfiltered_html() ) {
			$values = wp_kses_post_deep( $values );
		}

		acf_update_values( $values, $revision_id );

		$this->current_acf_values = null;
	}

	/**
	 * Processes ACF field values from the REST request.
	 * Decodes the _acf blob and saves individual meta keys to the post.
	 *
	 * Revision meta is handled separately by save_revision_meta(), which
	 * fires later via _wp_put_post_revision after WordPress creates the
	 * revision inside wp_after_insert_post().
	 *
	 * @since 6.8.1
	 *
	 * @param \WP_Post         $post    The post object.
	 * @param \WP_REST_Request $request The REST request.
	 * @return void
	 */
	public function save_post_rest( $post, $request ) {
		// Check if _acf data was included in the request.
		$meta = $request->get_param( 'meta' );
		if ( empty( $meta['_acf'] ) ) {
			return;
		}

		// Retrieve and decode the JSON.
		$acf_json = get_post_meta( $post->ID, '_acf', true );
		$values   = is_string( $acf_json ) ? json_decode( $acf_json, true ) : null;

		if ( empty( $values ) || ! is_array( $values ) ) {
			return;
		}

		// Save individual meta keys to the post.
		// Fires acf/save_post action, runs all ACF processing hooks.
		acf_save_post( $post->ID, $values );

		// Store values for save_revision_meta(), which fires later
		// via _wp_put_post_revision (after wp_after_insert_post creates
		// the revision). Don't delete _acf yet -- the revision comparison
		// needs it on the post when deciding whether to create a revision.
		// cleanup_acf_transport_meta() handles deletion after the revision
		// system finishes.
		$this->current_acf_values      = $values;
		$this->pending_cleanup_post_id = $post->ID;
	}

	/**
	 * Handles ACF values during autosave REST requests.
	 *
	 * @since 6.8.1
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_Post          $post     The post object.
	 * @param \WP_REST_Request  $request  The REST request.
	 * @return \WP_REST_Response
	 */
	public function save_autosave_rest( $response, $post, $request ) {
		$this->save_post_rest( $post, $request );
		return $response;
	}

	/**
	 * Cleans up the transport-only _acf meta after the revision system finishes.
	 *
	 * Hooked to wp_after_insert_post at priority 20, which runs after
	 * wp_save_post_revision_on_insert (priority 9). This catches orphaned
	 * _acf when no revision is created (e.g., post type doesn't support
	 * revisions). When a revision IS created, save_revision_meta() already
	 * deleted _acf, so this is a harmless no-op.
	 *
	 * @since 6.8.1
	 *
	 * @param integer $post_id The post ID.
	 * @return void
	 */
	public function cleanup_acf_transport_meta( $post_id ) {
		if ( $this->pending_cleanup_post_id === null || (int) $this->pending_cleanup_post_id !== (int) $post_id ) {
			return;
		}

		delete_post_meta( $post_id, '_acf' );
		$this->current_acf_values      = null;
		$this->pending_cleanup_post_id = null;
	}

	/**
	 * Strips _acf from post REST responses.
	 *
	 * The _acf meta is transport-only and should not appear in post
	 * responses. Revision responses use rest_prepare_revision instead,
	 * so _acf passes through for the revision viewer.
	 *
	 * @since 6.8.1
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @return \WP_REST_Response
	 */
	public function strip_acf_transport_meta( $response ) {
		$data = $response->get_data();

		if ( isset( $data['meta']['_acf'] ) ) {
			$data['meta']['_acf'] = '';
			$response->set_data( $data );
		}

		return $response;
	}
}
