<?php
/**
 * Functions for ACF taxonomy objects.
 *
 * @package ACF
 */

/**
 * Get an ACF taxonomy as an array
 *
 * @param integer|string $id The post ID being queried.
 * @return array|false The taxonomy object.
 */
function acf_get_taxonomy( $id ) {
	return acf_get_internal_post_type( $id, 'acf-taxonomy' );
}

/**
 * Retrieves a raw ACF taxonomy.
 *
 * @since   6.1
 *
 * @param   integer|string $id The post ID.
 * @return  array|false The taxonomy array.
 */
function acf_get_raw_taxonomy( $id ) {
	return acf_get_raw_internal_post_type( $id, 'acf-taxonomy' );
}

/**
 * Gets a post object for an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param integer|string $id The post ID, key, or name.
 * @return object|boolean The post object, or false on failure.
 */
function acf_get_taxonomy_post( $id ) {
	return acf_get_internal_post_type_post( $id, 'acf-taxonomy' );
}

/**
 * Returns true if the given identifier is an ACF taxonomy key.
 *
 * @since 6.1
 *
 * @param string $id The identifier.
 * @return boolean
 */
function acf_is_taxonomy_key( $id ) {
	return acf_is_internal_post_type_key( $id, 'acf-taxonomy' );
}

/**
 * Validates an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return array|boolean
 */
function acf_validate_taxonomy( array $taxonomy = array() ) {
	return acf_validate_internal_post_type( $taxonomy, 'acf-taxonomy' );
}

/**
 * Translates the settings for an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return array
 */
function acf_translate_taxonomy( array $taxonomy ) {
	return acf_translate_internal_post_type( $taxonomy, 'acf-taxonomy' );
}

/**
 * Returns an array of ACF taxonomies for the given $filter.
 *
 * @since 6.1
 *
 * @param array $filter An array of args to filter results by.
 * @return array
 */
function acf_get_acf_taxonomies( array $filter = array() ) {
	return acf_get_internal_post_type_posts( 'acf-taxonomy', $filter );
}

/**
 * Returns an array of raw ACF taxonomies.
 *
 * @since 6.1
 *
 * @return array
 */
function acf_get_raw_taxonomies() {
	return acf_get_raw_internal_post_type_posts( 'acf-taxonomy' );
}

/**
 * Returns a filtered array of ACF taxonomies based on the given $args.
 *
 * @since 6.1
 *
 * @param array $taxonomies An array of ACF taxonomies.
 * @param array $args       An array of args to filter by.
 * @return array
 */
function acf_filter_taxonomies( array $taxonomies, array $args = array() ) {
	return acf_filter_internal_post_type_posts( $taxonomies, $args, 'acf-taxonomy' );
}

/**
 * Updates an ACF taxonomy in the database.
 *
 * @since   6.1
 *
 * @param array $taxonomy The main ACF taxonomy array.
 * @return array
 */
function acf_update_taxonomy( array $taxonomy ) {
	return acf_update_internal_post_type( $taxonomy, 'acf-taxonomy' );
}

/**
 * Deletes all caches for the provided ACF taxonomy.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return void
 */
function acf_flush_taxonomy_cache( array $taxonomy ) {
	acf_flush_internal_post_type_cache( $taxonomy, 'acf-taxonomy' );
}

/**
 * Deletes an ACF taxonomy from the database.
 *
 * @since 6.1
 *
 * @param integer|string $id The ACF taxonomy ID, key or name.
 * @return boolean True if taxonomy was deleted.
 */
function acf_delete_taxonomy( $id = 0 ) {
	return acf_delete_internal_post_type( $id, 'acf-taxonomy' );
}

/**
 * Trashes an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param integer|string $id The taxonomy ID, key, or name.
 * @return boolean True if taxonomy was trashed.
 */
function acf_trash_taxonomy( $id = 0 ) {
	return acf_trash_internal_post_type( $id, 'acf-taxonomy' );
}

/**
 * Restores an ACF taxonomy from the trash.
 *
 * @since 6.1
 *
 * @param integer|string $id The taxonomy ID, key, or name.
 * @return boolean True if taxonomy was untrashed.
 */
function acf_untrash_taxonomy( $id = 0 ) {
	return acf_untrash_internal_post_type( $id, 'acf-taxonomy' );
}

/**
 * Returns true if the given params match an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return boolean
 */
function acf_is_taxonomy( $taxonomy ) {
	return acf_is_internal_post_type( $taxonomy, 'acf-taxonomy' );
}

/**
 * Duplicates an ACF taxonomy.
 *
 * @since 6.1
 *
 * @param integer|string $id          The ACF taxonomy ID, key or name.
 * @param integer        $new_post_id Optional ID to override.
 * @return array|boolean The new ACF taxonomy, or false on failure.
 */
function acf_duplicate_taxonomy( $id = 0, $new_post_id = 0 ) {
	return acf_duplicate_internal_post_type( $id, $new_post_id, 'acf-taxonomy' );
}

/**
 * Activates or deactivates an ACF taxonomy.
 *
 * @param integer|string $id       The ACF taxonomy ID, key or name.
 * @param boolean        $activate True if the taxonomy should be activated.
 * @return boolean
 */
function acf_update_taxonomy_active_status( $id, $activate = true ) {
	return acf_update_internal_post_type_active_status( $id, $activate, 'acf-taxonomy' );
}

/**
 * Checks if the current user can edit the taxonomy and returns the edit url.
 *
 * @since 6.1
 *
 * @param integer $post_id The ACF taxonomy ID.
 * @return string
 */
function acf_get_taxonomy_edit_link( $post_id ) {
	return acf_get_internal_post_type_edit_link( $post_id, 'acf-taxonomy' );
}

/**
 * Returns a modified ACF taxonomy ready for export.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return array
 */
function acf_prepare_taxonomy_for_export( array $taxonomy = array() ) {
	return acf_prepare_internal_post_type_for_export( $taxonomy, 'acf-taxonomy' );
}

/**
 * Exports an ACF taxonomy as PHP.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return string|boolean
 */
function acf_export_taxonomy_as_php( array $taxonomy ) {
	return acf_export_internal_post_type_as_php( $taxonomy, 'acf-taxonomy' );
}

/**
 * Prepares an ACF taxonomy for the import process.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return array
 */
function acf_prepare_taxonomy_for_import( array $taxonomy = array() ) {
	return acf_prepare_internal_post_type_for_import( $taxonomy, 'acf-taxonomy' );
}

/**
 * Imports an ACF taxonomy into the database.
 *
 * @since 6.1
 *
 * @param array $taxonomy The ACF taxonomy array.
 * @return array The imported taxonomy.
 */
function acf_import_taxonomy( array $taxonomy ) {
	return acf_import_internal_post_type( $taxonomy, 'acf-taxonomy' );
}
