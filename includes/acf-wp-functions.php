<?php

/**
 * Returns a WordPress object type.
 *
 * @date    1/4/20
 * @since   5.9.0
 *
 * @param   string $object_type The object type (post, term, user, etc).
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
					$props['icon']  = acf_with_default( $post_type->menu_icon, 'dashicons-admin-post' );
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
			$props['icon']  = acf_version_compare( 'wp', '>=', '5.5' ) ? 'dashicons-block-default' : 'dashicons-layout';
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
