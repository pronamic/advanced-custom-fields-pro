<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Ajax_Local_JSON_Diff' ) ) :

	class ACF_Ajax_Local_JSON_Diff extends ACF_Ajax {

		/**
		 * The AJAX action name.
		 *
		 * @var string
		 */
		public $action = 'acf/ajax/local_json_diff';

		/**
		 * Prevents access for non-logged in users.
		 *
		 * @var bool
		 */
		public $public = false;

		/**
		 * Returns the response data to sent back.
		 *
		 * @date    31/7/18
		 * @since   5.7.2
		 *
		 * @param array $request The request args.
		 * @return array|WP_Error The response data or WP_Error.
		 */
		public function get_response( $request ) {
			$json = array();

			// Extract props.
			$id = isset( $request['id'] ) ? intval( $request['id'] ) : 0;

			// Bail early if missing props.
			if ( ! $id ) {
				return new WP_Error( 'acf_invalid_param', __( 'Invalid field group parameter(s).', 'acf' ), array( 'status' => 404 ) );
			}

			$post_type = get_post_type( $id );
			if ( ! in_array( $post_type, acf_get_internal_post_types(), true ) ) {
				return new WP_Error( 'acf_invalid_post_type', __( 'Invalid post type selected for review.', 'acf' ), array( 'status' => 404 ) );
			}

			// Disable filters and load the post directly from database.
			acf_disable_filters();

			$post = acf_get_internal_post_type( $id, $post_type );
			if ( ! $post ) {
				return new WP_Error( 'acf_invalid_id', __( 'Invalid post ID.', 'acf' ), array( 'status' => 404 ) );
			}

			// Field groups also load in fields.
			if ( 'acf-field-group' === $post_type ) {
				$post['fields'] = acf_get_fields( $post );
			}

			$post['modified'] = get_post_modified_time( 'U', true, $post['ID'] );
			$post             = acf_prepare_internal_post_type_for_export( $post, $post_type );

			// Load local field group file.
			$files = acf_get_local_json_files( $post_type );
			$key   = $post['key'];
			if ( ! isset( $files[ $key ] ) ) {
				return new WP_Error( 'acf_cannot_compare', __( 'Sorry, this post is unavailable for diff comparison.', 'acf' ), array( 'status' => 404 ) );
			}
			$local_post = json_decode( file_get_contents( $files[ $key ] ), true );

			// Render diff HTML.
			$date_format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$date_template = __( 'Last updated: %s', 'acf' );
			$json['html']  = '
		<div class="acf-diff">
			<div class="acf-diff-title">
				<div class="acf-diff-title-left">
					<strong>' . __( 'Original', 'acf' ) . '</strong>
					<span>' . sprintf( $date_template, wp_date( $date_format, $post['modified'] ) ) . '</span>
				</div>
				<div class="acf-diff-title-right">
					<strong>' . __( 'JSON (newer)', 'acf' ) . '</strong>
					<span>' . sprintf( $date_template, wp_date( $date_format, $local_post['modified'] ) ) . '</span>
				</div>
			</div>
			<div class="acf-diff-content">
				' . wp_text_diff( acf_json_encode( $post ), acf_json_encode( $local_post ) ) . '
			</div>
		</div>';
			return $json;
		}
	}

	acf_new_instance( 'ACF_Ajax_Local_JSON_Diff' );

endif; // class_exists check
