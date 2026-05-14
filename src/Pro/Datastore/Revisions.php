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
 * ACF datastore integration with the WordPress revisions system.
 *
 * Registers the _acf transport meta as a revisioned key so changes to ACF
 * field values trigger revision creation, and short-circuits the legacy
 * metabox-AJAX-driven revision path during REST requests (where REST_Save
 * is in charge instead).
 */
class Revisions {

	/**
	 * Constructor.
	 *
	 * register_meta is deferred to the `init` hook so themes and plugins
	 * have a chance to filter `acf/settings/enable_datastore` before the
	 * gate is evaluated.
	 *
	 * @since 6.8.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_acf_to_revision_meta_keys' ) );
		add_filter( 'acf/revisions/skip_legacy_metabox_handling', array( $this, 'skip_during_rest' ) );
	}

	/**
	 * Registers the _acf transport meta when the datastore is enabled.
	 *
	 * _acf carries field values in the REST request. revisions_enabled is
	 * false here -- _acf is conditionally added to wp_post_revision_meta_keys
	 * only during REST requests so it triggers revision creation without
	 * causing duplicate revisions from the metabox AJAX (meta-box-loader)
	 * that follows each REST save. _acf is stripped from non-revision REST
	 * responses via rest_prepare_{post_type} in REST_Save.
	 *
	 * @since 6.8.1
	 *
	 * @return void
	 */
	public function register_meta() {
		if ( ! acf_is_using_datastore() ) {
			return;
		}

		register_meta(
			'post',
			'_acf',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'revisions_enabled' => false,
				'auth_callback'     => function ( $allowed, $meta_key, $object_id, $user_id ) {
					return user_can( $user_id, 'edit_post', $object_id );
				},
				'sanitize_callback' => function ( $value ) {
					if ( ! is_string( $value ) ) {
						return '';
					}
					$decoded = json_decode( $value, true );
					if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
						return '';
					}
					return wp_json_encode( self::canonicalize_acf_value( $decoded ) );
				},
			)
		);
	}

	/**
	 * Adds _acf to the list of revisioned meta keys during REST requests.
	 *
	 * _acf triggers revision creation when ACF values change. During the
	 * metabox AJAX (meta-box-loader) that follows each Gutenberg REST save,
	 * _acf must not be compared -- wp_update_post() fires again for metabox
	 * re-rendering and would create a duplicate revision.
	 *
	 * @since 6.8.1
	 *
	 * @param array $keys The meta keys that should be revisioned.
	 * @return array
	 */
	public function add_acf_to_revision_meta_keys( $keys ) {
		if ( acf_is_using_datastore() && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$keys[] = '_acf';
		}

		return $keys;
	}

	/**
	 * Tells acf_revisions to skip the legacy metabox handling on REST requests.
	 *
	 * Hooked to the acf/revisions/skip_legacy_metabox_handling filter. During
	 * a REST save, REST_Save copies field values to the post and revision,
	 * so the legacy metabox-AJAX-driven path in acf_revisions must not run.
	 *
	 * @since 6.8.1
	 *
	 * @param boolean $skip Whether to skip the legacy handling.
	 * @return boolean
	 */
	public function skip_during_rest( $skip ) {
		if ( ! acf_is_using_datastore() ) {
			return $skip;
		}

		return $skip || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Recursively sorts associative array keys for canonical JSON encoding.
	 *
	 * Sequential arrays (repeater rows, flexible content layouts, multi-value
	 * selections) keep their user-defined order; associative arrays -- at any
	 * level, regardless of whether keys are ACF field keys, the acf_fc_layout
	 * discriminator, or other string keys (e.g. link field title/url/target) --
	 * are sorted by key. JSON object keys are semantically unordered, so this
	 * is a no-op for consumers but makes the stored bytes byte-stable across
	 * saves so WordPress's revision meta byte comparison treats reordered
	 * saves as equal.
	 *
	 * @since 6.8.1
	 *
	 * @param mixed $value Decoded JSON value.
	 * @return mixed
	 */
	private static function canonicalize_acf_value( $value ) {
		if ( ! is_array( $value ) || array() === $value ) {
			return $value;
		}

		$is_sequential = array_keys( $value ) === range( 0, count( $value ) - 1 );
		if ( ! $is_sequential ) {
			ksort( $value );
		}

		foreach ( $value as $k => $v ) {
			$value[ $k ] = self::canonicalize_acf_value( $v );
		}

		return $value;
	}
}
