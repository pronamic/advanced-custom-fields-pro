<?php
/**
 * Functions for ACF post type objects.
 *
 * @package ACF
 */

/**
 * Get an ACF CPT as an array
 *
 * @param int|string $id The post ID being queried.
 * @return array|false The post type object.
 */
function acf_get_post_type( $id ) {
	return acf_get_internal_post_type( $id, 'acf-post-type' );
}

/**
 * Retrieves a raw ACF CPT.
 *
 * @since   6.1
 *
 * @param   int|string $id        The post ID.
 * @return  array|false The internal post type array.
 */
function acf_get_raw_post_type( $id ) {
	return acf_get_raw_internal_post_type( $id, 'acf-post-type' );
}

/**
 * Gets a post object for an ACF CPT.
 *
 * @since 6.1
 *
 * @param int|string $id The post ID, key, or name.
 * @return object|bool The post object, or false on failure.
 */
function acf_get_post_type_post( $id ) {
	return acf_get_internal_post_type_post( $id, 'acf-post-type' );
}

/**
 * Returns true if the given identifier is an ACF CPT key.
 *
 * @since 6.1
 *
 * @param string $id The identifier.
 * @return bool
 */
function acf_is_post_type_key( $id ) {
	return acf_is_internal_post_type_key( $id, 'acf-post-type' );
}

/**
 * Validates an ACF CPT.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return array|bool
 */
function acf_validate_post_type( array $post_type = array() ) {
	return acf_validate_internal_post_type( $post_type, 'acf-post-type' );
}

/**
 * Translates the settings for an ACF internal post type.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return array
 */
function acf_translate_post_type( array $post_type ) {
	return acf_translate_internal_post_type( $post_type, 'acf-post-type' );
}

/**
 * Returns and array of ACF post types for the given $filter.
 *
 * @since 6.1
 *
 * @param array $filter An array of args to filter results by.
 * @return array
 */
function acf_get_acf_post_types( array $filter = array() ) {
	return acf_get_internal_post_type_posts( 'acf-post-type', $filter );
}

/**
 * Returns an array of raw ACF post types.
 *
 * @since 6.1
 *
 * @return array
 */
function acf_get_raw_post_types() {
	return acf_get_raw_internal_post_type_posts( 'acf-post-type' );
}

/**
 * Returns a filtered array of ACF post types based on the given $args.
 *
 * @since 6.1
 *
 * @param array $post_types An array of ACF posts.
 * @param array $args       An array of args to filter by.
 * @return array
 */
function acf_filter_post_types( array $post_types, array $args = array() ) {
	return acf_filter_internal_post_type_posts( $post_types, $args, 'acf-post-type' );
}

/**
 * Updates an ACF post type in the database.
 *
 * @since   6.1
 *
 * @param array $post_type The main ACF post type array.
 * @return array
 */
function acf_update_post_type( array $post_type ) {
	return acf_update_internal_post_type( $post_type, 'acf-post-type' );
}

/**
 * Deletes all caches for the provided ACF post type.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return void
 */
function acf_flush_post_type_cache( array $post_type ) {
	acf_flush_internal_post_type_cache( $post_type, 'acf-post-type' );
}

/**
 * Deletes an ACF post type from the database.
 *
 * @since 6.1
 *
 * @param int|string $id The ACF post type ID, key or name.
 * @return bool True if post type was deleted.
 */
function acf_delete_post_type( $id = 0 ) {
	return acf_delete_internal_post_type( $id, 'acf-post-type' );
}

/**
 * Trashes an ACF post type.
 *
 * @since 6.1
 *
 * @param int|string $id The post type ID, key, or name.
 * @return bool True if post was trashed.
 */
function acf_trash_post_type( $id = 0 ) {
	return acf_trash_internal_post_type( $id, 'acf-post-type' );
}

/**
 * Restores an ACF post type from the trash.
 *
 * @since 6.1
 *
 * @param int|string $id The post type ID, key, or name.
 * @return bool True if post was untrashed.
 */
function acf_untrash_post_type( $id = 0 ) {
	return acf_untrash_internal_post_type( $id, 'acf-post-type' );
}

/**
 * Returns true if the given params match an ACF post type.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return bool
 */
function acf_is_post_type( $post_type ) {
	return acf_is_internal_post_type( $post_type, 'acf-post-type' );
}

/**
 * Duplicates an ACF post type.
 *
 * @since 6.1
 *
 * @param int|string $id          The ACF post type ID, key or name.
 * @param int        $new_post_id Optional ID to override.
 * @return array|bool The new ACF post type, or false on failure.
 */
function acf_duplicate_post_type( $id = 0, $new_post_id = 0 ) {
	return acf_duplicate_internal_post_type( $id, $new_post_id, 'acf-post-type' );
}

/**
 * Activates or deactivates an ACF post type.
 *
 * @param int|string $id        The ACF post type ID, key or name.
 * @param bool       $activate  True if the post type should be activated.
 * @return bool
 */
function acf_update_post_type_active_status( $id, $activate = true ) {
	return acf_update_internal_post_type_active_status( $id, $activate, 'acf-post-type' );
}

/**
 * Checks if the current user can edit the post type and returns the edit url.
 *
 * @since 6.1
 *
 * @param int $post_id The ACF post type ID.
 * @return string
 */
function acf_get_post_type_edit_link( $post_id ) {
	return acf_get_internal_post_type_edit_link( $post_id, 'acf-post-type' );
}

/**
 * Returns a modified ACF post type ready for export.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return array
 */
function acf_prepare_post_type_for_export( array $post_type = array() ) {
	return acf_prepare_internal_post_type_for_export( $post_type, 'acf-post-type' );
}

/**
 * Exports an ACF post type as PHP.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return string|bool
 */
function acf_export_post_type_as_php( array $post_type ) {
	return acf_export_internal_post_type_as_php( $post_type, 'acf-post-type' );
}

/**
 * Prepares an ACF post type for the import process.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return array
 */
function acf_prepare_post_type_for_import( array $post_type = array() ) {
	return acf_prepare_internal_post_type_for_import( $post_type, 'acf-post-type' );
}

/**
 * Imports an ACF post type into the database.
 *
 * @since 6.1
 *
 * @param array $post_type The ACF post type array.
 * @return array The imported post type.
 */
function acf_import_post_type( array $post_type ) {
	return acf_import_internal_post_type( $post_type, 'acf-post-type' );
}

/**
 * Exports the "Enter Title Here" text for the provided ACF post types.
 *
 * @since 6.2.1
 *
 * @param array $post_types The post types being exported.
 * @return string
 */
function acf_export_enter_title_here( array $post_types ) {
	$to_export = array();
	$export    = '';

	foreach ( $post_types as $post_type ) {
		if ( ! empty( $post_type['enter_title_here'] ) ) {
			$to_export[ $post_type['post_type'] ] = $post_type['enter_title_here'];
		}
	}

	if ( ! empty( $to_export ) ) {
		$export .= "\r\nadd_filter( 'enter_title_here', function( \$default, \$post ) {\r\n";
		$export .= "\tswitch ( \$post->post_type ) {\r\n";

		foreach ( $to_export as $post_type => $enter_title_here ) {
			$export .= "\t\tcase '$post_type':\r\n\t\t\treturn '$enter_title_here';\r\n";
		}

		$export .= "\t}\r\n\r\n\treturn \$default;\r\n}, 10, 2 );\r\n\r\n";
	}

	return esc_textarea( $export );
}
