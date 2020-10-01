<?php

/**
 * Returns a WordPress object type.
 *
 * @date	1/4/20
 * @since	5.9.0
 *
 * @param	string $object_type The object type (post, term, user, etc).
 * @param	string $object_subtype Optional object subtype (post type, taxonomy).
 * @return	object
 */
function acf_get_object_type( $object_type, $object_subtype = '' ) {
	$props = array(
		'type'		=> $object_type,
		'subtype'	=> $object_subtype,
		'name'		=> '',
		'label'		=> '',
		'icon'		=> ''
	);
	
	// Set unique identifier as name.
	if( $object_subtype ) {
		$props['name'] = "$object_type/$object_subtype";
	} else {
		$props['name'] = $object_type;
	}
	
	// Set label and icon.
	switch ( $object_type ) {
		case 'post':
			if( $object_subtype ) {
				$post_type = get_post_type_object( $object_subtype );
				if( $post_type ) {
					$props['label'] = $post_type->labels->name;
					$props['icon'] = acf_with_default( $post_type->menu_icon, 'dashicons-admin-post' );
				} else {
					return false;
				}
			} else {
				$props['label'] = __('Posts', 'acf');
				$props['icon'] = 'dashicons-admin-post';
			}
			break;
		case 'term':
			if( $object_subtype ) {
				$taxonomy = get_taxonomy( $object_subtype );
				if( $taxonomy ) {
					$props['label'] = $taxonomy->labels->name;
				} else {
					return false;
				}
			} else {
				$props['label'] = __('Taxonomies', 'acf');
			}
			$props['icon'] = 'dashicons-tag';
			break;
		case 'attachment':
			$props['label'] = __('Attachments', 'acf');
			$props['icon'] = 'dashicons-admin-media';
			break;
		case 'comment':
			$props['label'] = __('Comments', 'acf');
			$props['icon'] = 'dashicons-admin-comments';
			break;
		case 'widget':
			$props['label'] = __('Widgets', 'acf');
			$props['icon'] = 'dashicons-screenoptions';
			break;
		case 'menu':
			$props['label'] = __('Menus', 'acf');
			$props['icon'] = 'dashicons-admin-appearance';
			break;
		case 'menu_item':
			$props['label'] = __('Menu items', 'acf');
			$props['icon'] = 'dashicons-admin-appearance';
			break;
		case 'user':
			$props['label'] = __('Users', 'acf');
			$props['icon'] = 'dashicons-admin-users';
			break;
		case 'option':
			$props['label'] = __('Options', 'acf');
			$props['icon'] = 'dashicons-admin-generic';
			break;
		case 'block':
			$props['label'] = __('Blocks', 'acf');
			$props['icon'] = acf_version_compare('wp', '>=', '5.5') ? 'dashicons-block-default' : 'dashicons-layout';
			break;
		default:
			return false;
	}
	
	// Convert to object.
	$object = (object) $props;
	
	/**
	 * Filters the object type.
	 *
	 * @date	6/4/20
	 * @since	5.9.0
	 *
	 * @param	object $object The object props.
	 * @param	string $object_type The object type (post, term, user, etc).
	 * @param	string $object_subtype Optional object subtype (post type, taxonomy).
	 */
	return apply_filters( 'acf/get_object_type', $object, $object_type, $object_subtype );
}
