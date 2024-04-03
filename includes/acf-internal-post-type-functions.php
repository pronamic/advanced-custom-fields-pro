<?php
/**
 * Generic functions for accessing ACF objects stored as WordPress post types which aren't handled by type specific functions.
 *
 * @package ACF
 */

/**
 * Gets an instance of an ACF_Internal_Post_Type.
 *
 * @param string $post_type The ACF internal post type to get the instance for.
 * @return ACF_Internal_Post_Type|bool The internal post type class instance, or false on failure.
 */
function acf_get_internal_post_type_instance( $post_type = 'acf-field-group' ) {
	$store = acf_get_store( 'internal-post-types' );
	if ( ! $store ) {
		return false;
	}

	$instance = $store->get( $post_type );
	if ( ! $instance ) {
		return false;
	}

	return acf_get_instance( $instance );
}

/**
 * Get an ACF CPT object as an array
 *
 * @param integer $id        The post ID being queried.
 * @param string  $post_type The post type being queried.
 * @return array|false The post type object.
 */
function acf_get_internal_post_type( $id, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( ! $instance ) {
		return false;
	}

	return $instance->get_post( $id );
}

/**
 * Retrieves raw internal post type data for the given identifier.
 *
 * @since   6.1
 *
 * @param   integer|string $id        The post ID.
 * @param   string         $post_type The post type name.
 * @return  array|false The internal post type array.
 */
function acf_get_raw_internal_post_type( $id, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( ! $instance ) {
		return false;
	}

	return $instance->get_raw_post( $id );
}

/**
 * Gets a post object from an ACF internal post type.
 *
 * @since 6.1
 *
 * @param integer|string $id        The post ID, key, or name.
 * @param string         $post_type The post type name.
 * @return object|boolean The post object, or false on failure.
 */
function acf_get_internal_post_type_post( $id, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( ! $instance ) {
		return false;
	}

	return $instance->get_post_object( $id );
}

/**
 * Returns true if the given identifier is a ACF internal post type key.
 *
 * @since 6.1
 *
 * @param string $id        The identifier.
 * @param string $post_type The ACF post type the key is for.
 * @return boolean
 */
function acf_is_internal_post_type_key( $id = '', $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );
	if ( ! $instance ) {
		return false;
	}

	return $instance->is_post_key( $id );
}

/**
 * Validates an ACF internal post type.
 *
 * @since 6.1
 *
 * @param array  $internal_post_type The internal post type array.
 * @param string $post_type_name     The post type name.
 * @return array|boolean
 */
function acf_validate_internal_post_type( $internal_post_type, $post_type_name ) {
	$instance = acf_get_internal_post_type_instance( $post_type_name );

	if ( ! $instance ) {
		return false; // TODO: Should this return an empty array instead?
	}

	return $instance->validate_post( $internal_post_type );
}

/**
 * Translates the settings for an ACF internal post type.
 *
 * @since 6.1
 *
 * @param array  $internal_post_type The ACF post array.
 * @param string $post_type          The post type name.
 * @return array
 */
function acf_translate_internal_post_type( $internal_post_type, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( ! $instance ) {
		return $internal_post_type;
	}

	return $instance->translate_post( $internal_post_type );
}

/**
 * Returns and array of ACF posts for the given $filter.
 *
 * @since 6.1
 *
 * @param string $post_type The ACF post type to get posts for.
 * @param array  $filter    An array of args to filter results by.
 * @return array
 */
function acf_get_internal_post_type_posts( $post_type = 'acf-field-group', $filter = array() ) {
	$posts    = array();
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$posts = $instance->get_posts( $filter );
	}

	return $posts;
}

/**
 * Returns an array of raw/unvalidated ACF post data.
 *
 * @since 6.1
 *
 * @param string $post_type The ACF post type to get post data for.
 * @return array
 */
function acf_get_raw_internal_post_type_posts( $post_type ) {
	$raw_posts = array();
	$instance  = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$raw_posts = $instance->get_raw_posts();
	}

	return $raw_posts;
}

/**
 * Returns a filtered array of ACF posts based on the given $args.
 *
 * @since 6.1
 *
 * @param array  $posts     An array of ACF posts.
 * @param array  $args      An array of args to filter by.
 * @param string $post_type The ACF post type of the posts being filtered.
 * @return array
 */
function acf_filter_internal_post_type_posts( $posts, $args = array(), $post_type = 'acf-field-group' ) {
	$filtered = array();
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$filtered = $instance->filter_posts( $posts, $args );
	}

	return $filtered;
}

/**
 * Updates a internal post type in the database.
 *
 * @since   6.1
 *
 * @param array  $internal_post_type Array of data to be saved.
 * @param string $post_type_name     The internal post type being updated.
 * @return array
 */
function acf_update_internal_post_type( $internal_post_type, $post_type_name ) {
	$instance = acf_get_internal_post_type_instance( $post_type_name );

	if ( $instance ) {
		$internal_post_type = $instance->update_post( $internal_post_type );
	}

	return $internal_post_type;
}

/**
 * Deletes all caches for the provided ACF post.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The ACF post type the cache is being cleared for.
 * @return void
 */
function acf_flush_internal_post_type_cache( $post, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$instance->flush_post_cache( $post );
	}
}

/**
 * Deletes an internal post type from the database.
 *
 * @since 6.1
 *
 * @param integer|string $id             The internal post type ID, key or name.
 * @param string         $post_type_name The post type to be deleted.
 * @return boolean True if field group was deleted.
 */
function acf_delete_internal_post_type( $id = 0, $post_type_name = '' ) {
	$instance = acf_get_internal_post_type_instance( $post_type_name );

	if ( $instance ) {
		return $instance->delete_post( $id );
	}

	return false;
}

/**
 * Trashes an internal post type.
 *
 * @since 6.1
 *
 * @param integer|string $id             The internal post type ID, key, or name.
 * @param string         $post_type_name The post type being trashed.
 * @return boolean True if post was trashed.
 */
function acf_trash_internal_post_type( $id = 0, $post_type_name = '' ) {
	$instance = acf_get_internal_post_type_instance( $post_type_name );

	if ( $instance ) {
		return $instance->trash_post( $id );
	}

	return false;
}

/**
 * Restores an ACF post from the trash.
 *
 * @since 6.1
 *
 * @param integer|string $id             The internal post type ID, key, or name.
 * @param string         $post_type_name The post type being untrashed.
 * @return boolean True if post was untrashed.
 */
function acf_untrash_internal_post_type( $id = 0, $post_type_name = '' ) {
	$instance = acf_get_internal_post_type_instance( $post_type_name );

	if ( $instance ) {
		return $instance->untrash_post( $id );
	}

	return false;
}

/**
 * Returns true if the given params match an ACF post.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The ACF post type.
 * @return boolean
 */
function acf_is_internal_post_type( $post, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		return $instance->is_post( $post );
	}

	return false;
}

/**
 * Duplicates an ACF post.
 *
 * @since 6.1
 *
 * @param integer|string $id          The field_group ID, key or name.
 * @param integer        $new_post_id Optional ID to override.
 * @param string         $post_type   The post type of the post being duplicated.
 * @return array|boolean The new ACF post, or false on failure.
 */
function acf_duplicate_internal_post_type( $id = 0, $new_post_id = 0, $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		return $instance->duplicate_post( $id, $new_post_id );
	}

	return false;
}

/**
 * Activates or deactivates an ACF post.
 *
 * @param integer|string $id        The field_group ID, key or name.
 * @param boolean        $activate  True if the post should be activated.
 * @param string         $post_type The post type being activated/deactivated.
 * @return boolean
 */
function acf_update_internal_post_type_active_status( $id, $activate = true, $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		return $instance->update_post_active_status( $id, $activate );
	}

	return false;
}

/**
 * Checks if the current user can edit the field group and returns the edit url.
 *
 * @since 6.1
 *
 * @param integer $post_id   The ACF post ID.
 * @param string  $post_type The ACF post type to get the edit link for.
 * @return string
 */
function acf_get_internal_post_type_edit_link( $post_id, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		return $instance->get_post_edit_link( $post_id );
	}

	return '';
}

/**
 * Returns a modified field group ready for export.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The post type of the ACF post being exported.
 * @return array
 */
function acf_prepare_internal_post_type_for_export( $post = array(), $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$post = $instance->prepare_post_for_export( $post );
	}

	return $post;
}

/**
 * Exports an ACF post as PHP.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The post type of the ACF post being exported.
 * @return string|boolean
 */
function acf_export_internal_post_type_as_php( $post, $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		return $instance->export_post_as_php( $post );
	}

	return false;
}

/**
 * Prepares an ACF post for the import process.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The post type of the ACF post being imported.
 * @return  array
 */
function acf_prepare_internal_post_type_for_import( $post = array(), $post_type = 'acf-field-group' ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$post = $instance->prepare_post_for_import( $post );
	}

	return $post;
}

/**
 * Imports an ACF post into the database.
 *
 * @since 6.1
 *
 * @param array  $post      The ACF post array.
 * @param string $post_type The post type of the ACF post being imported.
 * @return array The imported post.
 */
function acf_import_internal_post_type( $post, $post_type ) {
	$instance = acf_get_internal_post_type_instance( $post_type );

	if ( $instance ) {
		$post = $instance->import_post( $post );
	}

	return $post;
}

/**
 * Tries to determine the ACF post type for the provided key.
 *
 * @param string $key The key to check.
 * @return string|boolean
 */
function acf_determine_internal_post_type( $key ) {
	$store      = acf_get_store( 'internal-post-types' );
	$post_types = array();

	if ( $store ) {
		$post_types = $store->get();
		$post_types = array_keys( $post_types );
	}

	foreach ( $post_types as $post_type ) {
		if ( acf_is_internal_post_type_key( $key, $post_type ) ) {
			return $post_type;
		}
	}

	return false;
}

/**
 * Check if the provided key is an identifiable ACF post type.
 *
 * @since 6.2.8
 *
 * @param string $key The key to check.
 * @return boolean
 */
function acf_is_valid_internal_post_type_key( string $key ) {
	return (bool) acf_determine_internal_post_type( $key );
}

/**
 * Check if the provided post type object contains a valid internal post type key.
 *
 * @since 6.2.8
 *
 * @param array $internal_post_type The post type object array to check it's key.
 * @return boolean
 */
function acf_internal_post_object_contains_valid_key( array $internal_post_type ) {
	if ( ! is_array( $internal_post_type ) || empty( $internal_post_type['key'] ) || ! is_string( $internal_post_type['key'] ) ) {
		return false;
	}
	return acf_is_valid_internal_post_type_key( $internal_post_type['key'] );
}

/**
 * Returns an array of tabs for the post type advanced settings.
 *
 * @since 6.1
 *
 * @return array
 */
function acf_get_combined_post_type_settings_tabs() {
	$default_post_type_tabs = array(
		'general'     => __( 'General', 'acf' ),
		'labels'      => __( 'Labels', 'acf' ),
		'visibility'  => __( 'Visibility', 'acf' ),
		'urls'        => __( 'URLs', 'acf' ),
		'permissions' => __( 'Permissions', 'acf' ),
		'rest_api'    => __( 'REST API', 'acf' ),
	);

	$additional_post_type_tabs = (array) apply_filters( 'acf/post_type/additional_settings_tabs', array() );

	// Remove any default tab values from the filtered tabs.
	foreach ( $additional_post_type_tabs as $key => $tab ) {
		if ( isset( $default_post_type_tabs[ $key ] ) ) {
			unset( $additional_post_type_tabs[ $key ] );
		}
	}

	return array_merge( $default_post_type_tabs, $additional_post_type_tabs );
}

/**
 * Returns an array of tabs for the taxonomy advanced settings.
 *
 * @since 6.1
 *
 * @return array
 */
function acf_get_combined_taxonomy_settings_tabs() {
	$default_taxonomy_tabs = array(
		'general'     => __( 'General', 'acf' ),
		'labels'      => __( 'Labels', 'acf' ),
		'visibility'  => __( 'Visibility', 'acf' ),
		'urls'        => __( 'URLs', 'acf' ),
		'permissions' => __( 'Permissions', 'acf' ),
		'rest_api'    => __( 'REST API', 'acf' ),
	);

	$additional_taxonomy_tabs = (array) apply_filters( 'acf/taxonomy/additional_settings_tabs', array() );

	// Remove any default tab values from the filtered tabs.
	foreach ( $additional_taxonomy_tabs as $key => $tab ) {
		if ( isset( $default_taxonomy_tabs[ $key ] ) ) {
			unset( $additional_taxonomy_tabs[ $key ] );
		}
	}

	return array_merge( $default_taxonomy_tabs, $additional_taxonomy_tabs );
}

/**
 * Returns an array of tabs for the options page advanced settings
 *
 * @since 6.2
 *
 * @return array
 */
function acf_get_combined_options_page_settings_tabs() {
	$default_options_page_tabs = array(
		'visibility'  => __( 'Visibility', 'acf' ),
		'labels'      => __( 'Labels', 'acf' ),
		'permissions' => __( 'Permissions', 'acf' ),
	);

	$additional_options_page_tabs = (array) apply_filters( 'acf/ui_options_page/additional_settings_tabs', array() );

	// Remove any default tab values from the filtered tabs.
	foreach ( $additional_options_page_tabs as $key => $tab ) {
		if ( isset( $default_options_page_tabs[ $key ] ) ) {
			unset( $additional_options_page_tabs[ $key ] );
		}
	}

	return array_merge( $default_options_page_tabs, $additional_options_page_tabs );
}

/**
 * Converts an _acf_screen or hook value into a post type.
 *
 * @since 6.1
 *
 * @param string $screen The ACF screen being viewed.
 * @return string The post type matching the screen or hook value.
 */
function acf_get_post_type_from_screen_value( $screen ) {
	switch ( $screen ) {
		case 'post_type':
			return 'acf-post-type';
		case 'taxonomy':
			return 'acf-taxonomy';
		case 'field_group':
			return 'acf-field-group';
		case 'ui_options_page':
			return 'acf-ui-options-page';
		default:
			return false;
	}
}

/**
 * Calls the ajax validator for a post type
 *
 * @since 6.1
 *
 * @param string $post_type The post type being validated.
 * @return mixed
 */
function acf_validate_internal_post_type_values( $post_type ) {
	if ( $post_type ) {
		$instance = acf_get_internal_post_type_instance( $post_type );
		return $instance->ajax_validate_values();
	}
	return false;
}

/**
 * Adds a validation error for ACF internal post types.
 *
 * @since 6.1
 *
 * @param string $name      The name of the input.
 * @param string $message   An optional error message to display.
 * @param string $post_type Optional post type the error message is for.
 * @return void
 */
function acf_add_internal_post_type_validation_error( $name, $message = '', $post_type = '' ) {
	if ( empty( $post_type ) ) {
		$screen    = isset( $_POST['_acf_screen'] ) ? (string) $_POST['_acf_screen'] : 'post_type'; // phpcs:ignore WordPress.Security -- Nonce verified upstream, value only used for comparison.
		$post_type = acf_get_post_type_from_screen_value( $screen );

		if ( ! $post_type ) {
			return;
		}
	}

	$input_prefix = str_replace( '-', '_', $post_type );

	if ( substr( $name, 0, strlen( $input_prefix ) ) !== $input_prefix ) {
		$name = "{$input_prefix}[$name]";
	}

	return acf_add_validation_error( $name, $message );
}

/**
 * Gets an ACF post type from request args and verifies nonce based on action.
 *
 * @since 6.1.5
 *
 * @param string $action The action being performed.
 * @return array|boolean
 */
function acf_get_post_type_from_request_args( $action = '' ) {
	$acf_use_post_type = (int) acf_request_arg( 'use_post_type', 0 );

	if ( ! $acf_use_post_type || ! wp_verify_nonce( acf_request_arg( '_wpnonce' ), $action . '-' . $acf_use_post_type ) ) {
		return false;
	}

	return acf_get_internal_post_type( $acf_use_post_type, 'acf-post-type' );
}

/**
 * Gets an ACF taxonomy from request args and verifies nonce based on action.
 *
 * @since 6.1.5
 *
 * @param string $action The action being performed.
 * @return array|boolean
 */
function acf_get_taxonomy_from_request_args( $action = '' ) {
	$acf_use_taxonomy = (int) acf_request_arg( 'use_taxonomy', 0 );

	if ( ! $acf_use_taxonomy || ! wp_verify_nonce( acf_request_arg( '_wpnonce' ), $action . '-' . $acf_use_taxonomy ) ) {
		return false;
	}

	return acf_get_internal_post_type( $acf_use_taxonomy, 'acf-taxonomy' );
}

/**
 * Gets an ACF options page from request args and verifies nonce based on action.
 *
 * @since 6.2
 *
 * @param string $action The action being performed.
 * @return array|boolean
 */
function acf_get_ui_options_page_from_request_args( $action = '' ) {
	$acf_use_options_page = (int) acf_request_arg( 'use_options_page', 0 );

	if ( ! $acf_use_options_page || ! wp_verify_nonce( acf_request_arg( '_wpnonce' ), $action . '-' . $acf_use_options_page ) ) {
		return false;
	}

	return acf_get_internal_post_type( $acf_use_options_page, 'acf-ui-options-page' );
}
