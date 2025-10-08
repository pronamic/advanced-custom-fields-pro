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

/**
 * Get an ACF UI options page as an array
 *
 * @since 6.2
 *
 * @param integer|string $id The post ID being queried.
 * @return array|false The UI options page array.
 */
function acf_get_ui_options_page( $id ) {
	return acf_get_internal_post_type( $id, 'acf-ui-options-page' );
}

/**
 * Retrieves a raw ACF UI options page.
 *
 * @since   6.2
 *
 * @param integer|string $id The post ID.
 * @return array|false The UI options page array.
 */
function acf_get_raw_ui_options_page( $id ) {
	return acf_get_raw_internal_post_type( $id, 'acf-ui-options-page' );
}

/**
 * Gets a post object for an ACF UI options page.
 *
 * @since 6.2
 *
 * @param integer|string $id The post ID, key, or name.
 * @return object|boolean The post object, or false on failure.
 */
function acf_get_ui_options_page_post( $id ) {
	return acf_get_internal_post_type_post( $id, 'acf-ui-options-page' );
}

/**
 * Returns true if the given identifier is an ACF UI options page key.
 *
 * @since 6.2
 *
 * @param string $id The identifier.
 * @return boolean
 */
function acf_is_ui_options_page_key( $id ) {
	return acf_is_internal_post_type_key( $id, 'acf-ui-options-page' );
}

/**
 * Validates an ACF UI options page.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array to validate.
 * @return array|boolean
 */
function acf_validate_ui_options_page( array $ui_options_page = array() ) {
	return acf_validate_internal_post_type( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Translates the settings for an ACF UI options page.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return array
 */
function acf_translate_ui_options_page( array $ui_options_page ) {
	return acf_translate_internal_post_type( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Returns and array of ACF UI options pages for the given $filter.
 *
 * @since 6.2
 *
 * @param array $filter An array of args to filter results by.
 * @return array
 */
function acf_get_ui_options_pages( array $filter = array() ) {
	return acf_get_internal_post_type_posts( 'acf-ui-options-page', $filter );
}

/**
 * Returns an array of raw ACF UI options pages.
 *
 * @since 6.2
 *
 * @return array
 */
function acf_get_raw_ui_options_pages() {
	return acf_get_raw_internal_post_type_posts( 'acf-ui-options-page' );
}

/**
 * Returns a filtered array of ACF UI options pages based on the given $args.
 *
 * @since 6.2
 *
 * @param array $ui_options_pages An array of ACF UI options pages.
 * @param array $args             An array of args to filter by.
 * @return array
 */
function acf_filter_ui_options_pages( array $ui_options_pages, array $args = array() ) {
	return acf_filter_internal_post_type_posts( $ui_options_pages, $args, 'acf-ui-options-page' );
}

/**
 * Updates an ACF UI options page in the database.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The main ACF UI options page array.
 * @return array
 */
function acf_update_ui_options_page( array $ui_options_page ) {
	return acf_update_internal_post_type( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Deletes all caches for the provided ACF UI options page.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return void
 */
function acf_flush_ui_options_page_cache( array $ui_options_page ) {
	acf_flush_internal_post_type_cache( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Deletes an ACF UI options page from the database.
 *
 * @since 6.2
 *
 * @param integer|string $id The ACF UI options page ID, key or name.
 * @return boolean True if the options page was deleted.
 */
function acf_delete_ui_options_page( $id = 0 ) {
	return acf_delete_internal_post_type( $id, 'acf-ui-options-page' );
}

/**
 * Trashes an ACF UI options page.
 *
 * @since 6.2
 *
 * @param integer|string $id The UI options page ID, key, or name.
 * @return boolean True if the options page was trashed.
 */
function acf_trash_ui_options_page( $id = 0 ) {
	return acf_trash_internal_post_type( $id, 'acf-ui-options-page' );
}

/**
 * Restores an ACF UI options page from the trash.
 *
 * @since 6.2
 *
 * @param integer|string $id The UI options page ID, key, or name.
 * @return boolean True if the options page was untrashed.
 */
function acf_untrash_ui_options_page( $id = 0 ) {
	return acf_untrash_internal_post_type( $id, 'acf-ui-options-page' );
}

/**
 * Returns true if the given params match an ACF UI options page.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return boolean
 */
function acf_is_ui_options_page( $ui_options_page ) {
	return acf_is_internal_post_type( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Duplicates an ACF UI options page.
 *
 * @since 6.2
 *
 * @param integer|string $id          The ACF UI options page ID, key or name.
 * @param integer        $new_post_id Optional ID to override.
 * @return array|boolean The new ACF UI options page, or false on failure.
 */
function acf_duplicate_ui_options_page( $id = 0, $new_post_id = 0 ) {
	return acf_duplicate_internal_post_type( $id, $new_post_id, 'acf-ui-options-page' );
}

/**
 * Activates or deactivates an ACF UI options page.
 *
 * @since 6.2
 *
 * @param integer|string $id       The ACF UI options page ID, key or name.
 * @param boolean        $activate True if the UI options page should be activated.
 * @return boolean
 */
function acf_update_ui_options_page_active_status( $id, $activate = true ) {
	return acf_update_internal_post_type_active_status( $id, $activate, 'acf-ui-options-page' );
}

/**
 * Checks if the current user can edit the UI options page and returns the edit URL.
 *
 * @since 6.2
 *
 * @param integer $post_id The ACF UI options page ID.
 * @return string
 */
function acf_get_ui_options_page_edit_link( $post_id ) {
	return acf_get_internal_post_type_edit_link( $post_id, 'acf-ui-options-page' );
}

/**
 * Returns a modified ACF UI options page ready for export.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return array
 */
function acf_prepare_ui_options_page_for_export( array $ui_options_page = array() ) {
	return acf_prepare_internal_post_type_for_export( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Exports an ACF UI options page as PHP.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return string|boolean
 */
function acf_export_ui_options_page_as_php( array $ui_options_page ) {
	return acf_export_internal_post_type_as_php( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Prepares an ACF UI options page for the import process.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return array
 */
function acf_prepare_ui_options_page_for_import( array $ui_options_page = array() ) {
	return acf_prepare_internal_post_type_for_import( $ui_options_page, 'acf-ui-options-page' );
}

/**
 * Imports an ACF UI options page into the database.
 *
 * @since 6.2
 *
 * @param array $ui_options_page The ACF UI options page array.
 * @return array The imported options page.
 */
function acf_import_ui_options_page( array $ui_options_page ) {
	return acf_import_internal_post_type( $ui_options_page, 'acf-ui-options-page' );
}
