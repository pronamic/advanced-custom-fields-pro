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

/**
 * Returns a WordPress object type.
 *
 * @date    1/4/20
 * @since   5.9.0
 *
 * @param   string $object_type    The object type (post, term, user, etc).
 * @param   string $object_subtype Optional object subtype (post type, taxonomy).
 * @return  object
 */
function acf_get_object_type( $object_type, $object_subtype = '' ) {
	$props = array(
		'type'    => $object_type,
		'subtype' => $object_subtype,
		'name'    => '',
		'label'   => '',
		'icon'    => '',
	);

	// Set unique identifier as name.
	if ( $object_subtype ) {
		$props['name'] = "$object_type/$object_subtype";
	} else {
		$props['name'] = $object_type;
	}

	// Set label and icon.
	switch ( $object_type ) {
		case 'post':
			if ( $object_subtype ) {
				$post_type = get_post_type_object( $object_subtype );
				if ( $post_type ) {
					$props['label'] = $post_type->labels->name;
					if ( is_string( $post_type->menu_icon ) && ! preg_match( '/^[\w\-]+$/', $post_type->menu_icon ) ) {
						$post_type->menu_icon = false;
					}
					$props['icon'] = acf_with_default( $post_type->menu_icon, 'dashicons-admin-post' );
				} else {
					return false;
				}
			} else {
				$props['label'] = __( 'Posts', 'acf' );
				$props['icon']  = 'dashicons-admin-post';
			}
			break;
		case 'term':
			if ( $object_subtype ) {
				$taxonomy = get_taxonomy( $object_subtype );
				if ( $taxonomy ) {
					$props['label'] = $taxonomy->labels->name;
				} else {
					return false;
				}
			} else {
				$props['label'] = __( 'Taxonomies', 'acf' );
			}
			$props['icon'] = 'dashicons-tag';
			break;
		case 'attachment':
			$props['label'] = __( 'Attachments', 'acf' );
			$props['icon']  = 'dashicons-admin-media';
			break;
		case 'comment':
			$props['label'] = __( 'Comments', 'acf' );
			$props['icon']  = 'dashicons-admin-comments';
			break;
		case 'widget':
			$props['label'] = __( 'Widgets', 'acf' );
			$props['icon']  = 'dashicons-screenoptions';
			break;
		case 'menu':
			$props['label'] = __( 'Menus', 'acf' );
			$props['icon']  = 'dashicons-admin-appearance';
			break;
		case 'menu_item':
			$props['label'] = __( 'Menu items', 'acf' );
			$props['icon']  = 'dashicons-admin-appearance';
			break;
		case 'user':
			$props['label'] = __( 'Users', 'acf' );
			$props['icon']  = 'dashicons-admin-users';
			break;
		case 'option':
			$props['label'] = __( 'Options', 'acf' );
			$props['icon']  = 'dashicons-admin-generic';
			break;
		case 'block':
			$props['label'] = __( 'Blocks', 'acf' );
			$props['icon']  = 'dashicons-block-default';
			break;
		default:
			return false;
	}

	// Convert to object.
	$object = (object) $props;

	/**
	 * Filters the object type.
	 *
	 * @date    6/4/20
	 * @since   5.9.0
	 *
	 * @param   object $object The object props.
	 * @param   string $object_type The object type (post, term, user, etc).
	 * @param   string $object_subtype Optional object subtype (post type, taxonomy).
	 */
	return apply_filters( 'acf/get_object_type', $object, $object_type, $object_subtype );
}

/**
 * Decodes a post_id value such as 1 or "user_1" into an array containing the type and ID.
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   (int|string) $post_id The post id.
 * @return  array
 */
function acf_decode_post_id( $post_id = 0 ) {
	$type = '';
	$id   = 0;

	// Interpret numeric value (123).
	if ( is_numeric( $post_id ) ) {
		$type = 'post';
		$id   = $post_id;

		// Interpret string value ("user_123" or "option").
	} elseif ( is_string( $post_id ) ) {
		$i = strrpos( $post_id, '_' );
		if ( $i > 0 ) {
			$type = substr( $post_id, 0, $i );
			$id   = substr( $post_id, $i + 1 );
		} else {
			$type = $post_id;
			$id   = '';
		}

		// Handle incorrect param type.
	} else {
		return compact( 'type', 'id' );
	}

	// Validate props based on param format.
	$format = $type . '_' . ( is_numeric( $id ) ? '%d' : '%s' );
	switch ( $format ) {
		case 'post_%d':
			$type = 'post';
			$id   = absint( $id );
			break;
		case 'term_%d':
			$type = 'term';
			$id   = absint( $id );
			break;
		case 'attachment_%d':
			$type = 'post';
			$id   = absint( $id );
			break;
		case 'comment_%d':
			$type = 'comment';
			$id   = absint( $id );
			break;
		case 'widget_%s':
		case 'widget_%d':
			$type = 'option';
			$id   = $post_id;
			break;
		case 'menu_%d':
			$type = 'term';
			$id   = absint( $id );
			break;
		case 'menu_item_%d':
			$type = 'post';
			$id   = absint( $id );
			break;
		case 'user_%d':
			$type = 'user';
			$id   = absint( $id );
			break;
		case 'block_%s':
		case 'block_%d':
			$type = 'block';
			$id   = $post_id;
			break;
		case 'option_%s':
			$type = 'option';
			$id   = $post_id;
			break;
		case 'blog_%d':
		case 'site_%d':
			// Allow backwards compatibility for custom taxonomies.
			$type = taxonomy_exists( $type ) ? 'term' : 'blog';
			$id   = absint( $id );
			break;
		case 'woo_order_%d':
			$type = 'woo_order';
			$id   = absint( $id );
			break;
		default:
			// Check for taxonomy name.
			if ( taxonomy_exists( $type ) && is_numeric( $id ) ) {
				$type = 'term';
				$id   = absint( $id );
				break;
			}

			// Treat unknown post_id format as an option.
			$type = 'option';
			$id   = $post_id;
			break;
	}

	/**
	 * Filters the decoded post_id information.
	 *
	 * @date    25/1/19
	 * @since   5.7.11
	 *
	 * @param   array $props An array containing "type" and "id" information.
	 * @param   (int|string) $post_id The post id.
	 */
	return apply_filters( 'acf/decode_post_id', compact( 'type', 'id' ), $post_id );
}

/**
 * Determine the REST base for a post type or taxonomy object. Note that this is not intended for use
 * with term or post objects but is, instead, to be used with the underlying WP_Post_Type and WP_Taxonomy
 * instances.
 *
 * @param WP_Post_Type|WP_Taxonomy $type_object
 * @return string|null
 */
function acf_get_object_type_rest_base( $type_object ) {
	if ( $type_object instanceof WP_Post_Type || $type_object instanceof WP_Taxonomy ) {
		return ! empty( $type_object->rest_base ) ? $type_object->rest_base : $type_object->name;
	}

	return null;
}

/**
 * Extract the ID of a given object/array. This supports all expected types handled by our update_fields() and
 * load_fields() callbacks.
 *
 * @param WP_Post|WP_User|WP_Term|WP_Comment|array $object
 * @return integer|mixed|null
 */
function acf_get_object_id( $object ) {
	if ( is_object( $object ) ) {
		switch ( get_class( $object ) ) {
			case WP_User::class:
			case WP_Post::class:
				return (int) $object->ID;
			case WP_Term::class:
				return (int) $object->term_id;
			case WP_Comment::class:
				return (int) $object->comment_ID;
		}
	} elseif ( isset( $object['id'] ) ) {
		return (int) $object['id'];
	} elseif ( isset( $object['ID'] ) ) {
		return (int) $object['ID'];
	}

	return null;
}

/**
 * Inserts or updates one of ACF's internal posts (fields, field groups, post types,
 * taxonomies and options pages), storing its serialized `post_content` exactly as given.
 *
 * Filters on `content_save_pre` (kses, `convert_invalid_entities`, etc.) treat the
 * serialized settings as HTML and can change string lengths, which corrupts them.
 * Settings must already be sanitized, see _acf_maybe_kses_internal_post_settings().
 *
 * @since 6.8.11
 *
 * @param array $postarr Slashed post data, as passed to wp_insert_post().
 * @return integer|WP_Error The post ID on success. The value 0 or WP_Error on failure.
 */
function acf_insert_or_update_internal_post( $postarr ) {
	// Remember the exact content being saved, so other posts written by hooks during the save are still filtered.
	$saving = acf_get_data( 'saving_internal_post_content' );
	acf_set_data( 'saving_internal_post_content', $postarr['post_content'] ?? null );

	try {
		if ( ! empty( $postarr['ID'] ) ) {
			return wp_update_post( $postarr );
		}

		return wp_insert_post( $postarr );
	} finally {
		acf_set_data( 'saving_internal_post_content', $saving );
	}
}

/**
 * Private callback for the "wp_insert_post_data" filter.
 *
 * Restores the unsanitized `post_content` of ACF's internal posts when it's the content
 * being saved by acf_insert_or_update_internal_post(), or when an update leaves the content
 * unchanged, such as trashing or restoring. Both values are slashed at this point.
 * Follows WP_Customize_Manager::preserve_insert_changeset_post_content().
 *
 * @since 6.8.11
 * @internal
 *
 * @param array   $data                An array of slashed, sanitized post data.
 * @param array   $postarr             An array of sanitized post data.
 * @param array   $unsanitized_postarr An array of slashed yet unsanitized post data.
 * @param boolean $update              Whether this is an existing post being updated.
 * @return array
 */
function _acf_preserve_internal_post_content( $data, $postarr, $unsanitized_postarr, $update = false ) {
	$internal_post_types = array_merge( array( 'acf-field' ), acf_get_internal_post_types() );

	if ( ! isset( $data['post_type'], $unsanitized_postarr['post_content'] ) || ! in_array( $data['post_type'], $internal_post_types, true ) ) {
		return $data;
	}

	$expected = acf_get_data( 'saving_internal_post_content' );
	$preserve = null !== $expected && $expected === $unsanitized_postarr['post_content'];

	// Updates that only change the status, like trashing or restoring, resave the stored content.
	if ( ! $preserve && $update && ! empty( $postarr['ID'] ) ) {
		$stored   = wp_slash( get_post_field( 'post_content', $postarr['ID'], 'raw' ) );
		$preserve = $stored === $unsanitized_postarr['post_content'];
	}

	if ( $preserve ) {
		$data['post_content'] = $unsanitized_postarr['post_content'];
	}

	return $data;
}

add_filter( 'wp_insert_post_data', '_acf_preserve_internal_post_content', PHP_INT_MAX, 4 );

/**
 * Runs a callback that saves ACF internal posts from a trusted source, without
 * sanitizing their settings for the current user.
 *
 * Settings saved by users without the `unfiltered_html` capability are otherwise filtered
 * with wp_kses_post(). Use this only for settings that are already stored or sanitized,
 * or that come from the server, such as local JSON files. Never for user input.
 *
 * @since 6.8.11
 *
 * @param callable $callback The callback that saves the posts.
 * @param mixed    ...$args  Arguments to pass to the callback.
 * @return mixed The callback's return value.
 */
function acf_skip_internal_post_sanitization( callable $callback, ...$args ) {
	$skipping = acf_get_data( 'skip_internal_post_sanitization' );
	acf_set_data( 'skip_internal_post_sanitization', true );

	try {
		return $callback( ...$args );
	} finally {
		acf_set_data( 'skip_internal_post_sanitization', $skipping );
	}
}

/**
 * Checks whether the settings of an ACF internal post being saved need sanitizing, because
 * the current user can't use unfiltered HTML and sanitization isn't skipped with
 * acf_skip_internal_post_sanitization().
 *
 * @since 6.8.11
 * @internal
 *
 * @return boolean
 */
function _acf_should_sanitize_internal_post_settings() {
	return ! acf_get_data( 'skip_internal_post_sanitization' ) && ! current_user_can( 'unfiltered_html' );
}

/**
 * Sanitizes the settings of an ACF internal post being saved, when the current user can't
 * use unfiltered HTML and sanitization isn't skipped with acf_skip_internal_post_sanitization().
 *
 * Settings are stored exactly as given, so this does what kses on `post_content` would
 * otherwise do for these users, without corrupting the serialized data.
 *
 * @since 6.8.11
 * @internal
 *
 * @param array $settings The unslashed settings.
 * @return array
 */
function _acf_maybe_kses_internal_post_settings( $settings ) {
	if ( ! _acf_should_sanitize_internal_post_settings() ) {
		return $settings;
	}

	return _acf_kses_post_deep_with_keys( $settings );
}

/**
 * Recursively runs wp_kses_post() over string keys and string values that contain HTML.
 *
 * Browsers only start a tag at "<" followed by a letter, "/", "!" or "?", so other text
 * can't contain HTML and is left exactly as typed. Otherwise kses can rewrite plain text,
 * such as "&" or entities, breaking choice keys and conditional logic operators like "<".
 *
 * @since 6.8.11
 * @internal
 *
 * @param mixed $data The data to sanitize.
 * @return mixed
 */
function _acf_kses_post_deep_with_keys( $data ) {
	if ( is_string( $data ) ) {
		return preg_match( '~<[a-zA-Z/!?]~', $data ) ? wp_kses_post( $data ) : $data;
	}

	if ( ! is_array( $data ) ) {
		return $data;
	}

	$sanitized = array();
	foreach ( $data as $key => $value ) {
		$sanitized[ _acf_kses_post_deep_with_keys( $key ) ] = _acf_kses_post_deep_with_keys( $value );
	}

	return $sanitized;
}
