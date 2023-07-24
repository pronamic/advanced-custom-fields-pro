<?php

/**
 * acf_get_field_group
 *
 * Retrieves a field group for the given identifier.
 *
 * @date    30/09/13
 * @since   5.0.0
 *
 * @param   (int|string) $id The field group ID, key or name.
 * @return  (array|false) The field group array.
 */
function acf_get_field_group( $id = 0 ) {
	return acf_get_internal_post_type( $id, 'acf-field-group' );
}

/**
 * acf_get_raw_field_group
 *
 * Retrieves raw field group data for the given identifier.
 *
 * @date    18/1/19
 * @since   5.7.10
 *
 * @param   (int|string) $id The field ID, key or name.
 * @return  (array|false) The field group array.
 */
function acf_get_raw_field_group( $id = 0 ) {
	return acf_get_raw_internal_post_type( $id, 'acf-field-group' );
}

/**
 * acf_get_field_group_post
 *
 * Retrieves the field group's WP_Post object.
 *
 * @date    18/1/19
 * @since   5.7.10
 *
 * @param   (int|string) $id The field group's ID, key or name.
 * @return  (array|false) The field group's array.
 */
function acf_get_field_group_post( $id = 0 ) {
	return acf_get_internal_post_type_post( $id, 'acf-field-group' );
}

/**
 * acf_is_field_group_key
 *
 * Returns true if the given identifier is a field group key.
 *
 * @date    6/12/2013
 * @since   5.0.0
 *
 * @param   string $id The identifier.
 * @return  bool
 */
function acf_is_field_group_key( $id = '' ) {
	return acf_is_internal_post_type_key( $id, 'acf-field-group' );
}

/**
 * Ensures the given field group is valid.
 *
 * @date    18/1/19
 * @since   5.7.10
 *
 * @param array $field_group The field group array.
 * @return array
 */
function acf_validate_field_group( $field_group = array() ) {
	return acf_validate_internal_post_type( $field_group, 'acf-field-group' );
}

/**
 * acf_get_valid_field_group
 *
 * Ensures the given field group is valid.
 *
 * @date        28/09/13
 * @since       5.0.0
 *
 * @param   array $field_group The field group array.
 * @return  array
 */
function acf_get_valid_field_group( $field_group = false ) {
	return acf_validate_field_group( $field_group );
}

/**
 * acf_translate_field_group
 *
 * Translates a field group's settings.
 *
 * @date    8/03/2016
 * @since   5.3.2
 *
 * @param   array $field_group The field group array.
 * @return  array
 */
function acf_translate_field_group( $field_group = array() ) {
	return acf_translate_internal_post_type( $field_group, 'acf-field-group' );
}

/**
 * acf_get_field_groups
 *
 * Returns and array of field_groups for the given $filter.
 *
 * @date    30/09/13
 * @since   5.0.0
 *
 * @param   array $filter An array of args to filter results by.
 * @return  array
 */
function acf_get_field_groups( $filter = array() ) {
	return acf_get_internal_post_type_posts( 'acf-field-group', $filter );
}

/**
 * acf_get_raw_field_groups
 *
 * Returns and array of raw field_group data.
 *
 * @date    18/1/19
 * @since   5.7.10
 *
 * @param   void
 * @return  array
 */
function acf_get_raw_field_groups() {
	return acf_get_raw_internal_post_type_posts( 'acf-field-group' );
}

/**
 * acf_filter_field_groups
 *
 * Returns a filtered aray of field groups based on the given $args.
 *
 * @date    29/11/2013
 * @since   5.0.0
 *
 * @param   array $field_groups An array of field groups.
 * @param   array $args An array of location args.
 * @return  array
 */
function acf_filter_field_groups( $field_groups, $args = array() ) {
	return acf_filter_internal_post_type_posts( $field_groups, $args, 'acf-field-group' );
}

/**
 * acf_get_field_group_visibility
 *
 * Returns true if the given field group's location rules match the given $args.
 *
 * @date    7/10/13
 * @since   5.0.0
 *
 * @param   array $field_groups An array of field groups.
 * @param   array $args An array of location args.
 * @return  bool
 */
function acf_get_field_group_visibility( $field_group, $args = array() ) {

	// Check if active.
	if ( ! $field_group['active'] ) {
		return false;
	}

	// Check if location rules exist
	if ( $field_group['location'] ) {

		// Get the current screen.
		$screen = acf_get_location_screen( $args );

		// Loop through location groups.
		foreach ( $field_group['location'] as $group ) {

			// ignore group if no rules.
			if ( empty( $group ) ) {
				continue;
			}

			// Loop over rules and determine if all rules match.
			$match_group = true;
			foreach ( $group as $rule ) {
				if ( ! acf_match_location_rule( $rule, $screen, $field_group ) ) {
					$match_group = false;
					break;
				}
			}

			// If this group matches, show the field group.
			if ( $match_group ) {
				return true;
			}
		}
	}

	// Return default.
	return false;
}

/**
 * acf_update_field_group
 *
 * Updates a field group in the database.
 *
 * @date    21/1/19
 * @since   5.7.10
 *
 * @param   array $field_group The field group array.
 * @return  array
 */
function acf_update_field_group( $field_group ) {
	return acf_update_internal_post_type( $field_group, 'acf-field-group' );
}

/**
 * _acf_apply_unique_field_group_slug
 *
 * Allows full control over 'acf-field-group' slugs.
 *
 * @date    21/1/19
 * @since   5.7.10
 *
 * @param string $slug          The post slug.
 * @param int    $post_ID       Post ID.
 * @param string $post_status   The post status.
 * @param string $post_type     Post type.
 * @param int    $post_parent   Post parent ID
 * @param string $original_slug The original post slug.
 */
function _acf_apply_unique_field_group_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {

	// Check post type and reset to original value.
	if ( $post_type === 'acf-field-group' ) {
		return $original_slug;
	}

	// Return slug.
	return $slug;
}

/**
 * acf_flush_field_group_cache
 *
 * Deletes all caches for this field group.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array $field_group The field group array.
 * @return  void
 */
function acf_flush_field_group_cache( $field_group ) {
	acf_flush_internal_post_type_cache( $field_group, 'acf-field-group' );
}

/**
 * acf_delete_field_group
 *
 * Deletes a field group from the database.
 *
 * @date    21/1/19
 * @since   5.7.10
 *
 * @param   (int|string) $id The field group ID, key or name.
 * @return  bool True if field group was deleted.
 */
function acf_delete_field_group( $id = 0 ) {
	return acf_delete_internal_post_type( $id, 'acf-field-group' );
}

/**
 * acf_trash_field_group
 *
 * Trashes a field group from the database.
 *
 * @date    2/10/13
 * @since   5.0.0
 *
 * @param   (int|string) $id The field group ID, key or name.
 * @return  bool True if field group was trashed.
 */
function acf_trash_field_group( $id = 0 ) {
	return acf_trash_internal_post_type( $id, 'acf-field-group' );
}

/**
 * acf_untrash_field_group
 *
 * Restores a field_group from the trash.
 *
 * @date    2/10/13
 * @since   5.0.0
 *
 * @param   (int|string) $id The field_group ID, key or name.
 * @return  bool True if field_group was trashed.
 */
function acf_untrash_field_group( $id = 0 ) {
	return acf_untrash_internal_post_type( $id, 'acf-field-group' );
}

/**
 * Filter callback which returns the previous post_status instead of "draft" for the "acf-field-group" post type.
 *
 * Prior to WordPress 5.6.0, this filter was not needed as restored posts were always assigned their original status.
 *
 * @since 5.9.5
 *
 * @param string $new_status      The new status of the post being restored.
 * @param int    $post_id         The ID of the post being restored.
 * @param string $previous_status The status of the post at the point where it was trashed.
 * @return string.
 */
function _acf_untrash_field_group_post_status( $new_status, $post_id, $previous_status ) {
	return ( get_post_type( $post_id ) === 'acf-field-group' ) ? $previous_status : $new_status;
}

/**
 * acf_is_field_group
 *
 * Returns true if the given params match a field group.
 *
 * @date    21/1/19
 * @since   5.7.10
 *
 * @param   array $field_group The field group array.
 * @param   mixed $id An optional identifier to search for.
 * @return  bool
 */
function acf_is_field_group( $field_group = false ) {
	return acf_is_internal_post_type( $field_group, 'acf-field-group' );
}

/**
 * acf_duplicate_field_group
 *
 * Duplicates a field group.
 *
 * @date    16/06/2014
 * @since   5.0.0
 *
 * @param   (int|string) $id The field_group ID, key or name.
 * @param   int          $new_post_id Optional post ID to override.
 * @return  array The new field group.
 */
function acf_duplicate_field_group( $id = 0, $new_post_id = 0 ) {
	return acf_duplicate_internal_post_type( $id, $new_post_id, 'acf-field-group' );
}

/**
 * Activates or deactivates a field group.
 *
 * @param int|string $id       The field_group ID, key or name.
 * @param bool       $activate True if the post should be activated.
 * @return bool
 */
function acf_update_field_group_active_status( $id, $activate = true ) {
	return acf_update_internal_post_type_active_status( $id, $activate, 'acf-field-group' );
}

/**
 * acf_get_field_group_style
 *
 * Returns the CSS styles generated from field group settings.
 *
 * @date    20/10/13
 * @since   5.0.0
 *
 * @param   array $field_group The field group array.
 * @return  string.
 */
function acf_get_field_group_style( $field_group ) {

	// Vars.
	$style    = '';
	$elements = array(
		'permalink'       => '#edit-slug-box',
		'the_content'     => '#postdivrich',
		'excerpt'         => '#postexcerpt',
		'custom_fields'   => '#postcustom',
		'discussion'      => '#commentstatusdiv',
		'comments'        => '#commentsdiv',
		'slug'            => '#slugdiv',
		'author'          => '#authordiv',
		'format'          => '#formatdiv',
		'page_attributes' => '#pageparentdiv',
		'featured_image'  => '#postimagediv',
		'revisions'       => '#revisionsdiv',
		'categories'      => '#categorydiv',
		'tags'            => '#tagsdiv-post_tag',
		'send-trackbacks' => '#trackbacksdiv',
	);

	// Loop over field group settings and generate list of selectors to hide.
	if ( is_array( $field_group['hide_on_screen'] ) ) {
		$hide = array();
		foreach ( $field_group['hide_on_screen'] as $k ) {
			if ( isset( $elements[ $k ] ) ) {
				$id     = $elements[ $k ];
				$hide[] = $id;
				$hide[] = '#screen-meta label[for=' . substr( $id, 1 ) . '-hide]';
			}
		}
		$style = implode( ', ', $hide ) . ' {display: none;}';
	}

	/**
	 * Filters the generated CSS styles.
	 *
	 * @date    12/02/2014
	 * @since   5.0.0
	 *
	 * @param   string $style The CSS styles.
	 * @param   array $field_group The field group array.
	 */
	return apply_filters( 'acf/get_field_group_style', $style, $field_group );
}

/**
 * acf_get_field_group_edit_link
 *
 * Checks if the current user can edit the field group and returns the edit url.
 *
 * @date    23/9/18
 * @since   5.7.7
 *
 * @param   int $post_id The field group ID.
 * @return  string
 */
function acf_get_field_group_edit_link( $post_id ) {
	return acf_get_internal_post_type_edit_link( $post_id, 'acf-field-group' );
}

/**
 * acf_prepare_field_group_for_export
 *
 * Returns a modified field group ready for export.
 *
 * @date    11/03/2014
 * @since   5.0.0
 *
 * @param   array $field_group The field group array.
 * @return  array
 */
function acf_prepare_field_group_for_export( $field_group = array() ) {
	return acf_prepare_internal_post_type_for_export( $field_group, 'acf-field-group' );
}

/**
 * acf_prepare_field_group_for_import
 *
 * Prepares a field group for the import process.
 *
 * @date    21/11/19
 * @since   5.8.8
 *
 * @param   array $field_group The field group array.
 * @return  array
 */
function acf_prepare_field_group_for_import( $field_group ) {
	return acf_prepare_internal_post_type_for_import( $field_group, 'acf-field-group' );
}

/**
 * acf_import_field_group
 *
 * Imports a field group into the databse.
 *
 * @date    11/03/2014
 * @since   5.0.0
 *
 * @param   array $field_group The field group array.
 * @return  array The new field group.
 */
function acf_import_field_group( $field_group ) {
	return acf_import_internal_post_type( $field_group, 'acf-field-group' );
}

/**
 *  Returns an array of tabs for the field group settings.
 *  We combine a list of default tabs with filtered tabs.
 *  I.E. Default tabs should be static and should not be changed by the
 *  filtered tabs.
 *
 *  @since 6.1
 *
 *  @return array Key/value array of the default settings tabs for field group settings.
 */
function acf_get_combined_field_group_settings_tabs() {
	$default_field_group_settings_tabs = array(
		'location_rules' => __( 'Location Rules', 'acf' ),
		'presentation'   => __( 'Presentation', 'acf' ),
		'group_settings' => __( 'Group Settings', 'acf' ),
	);

	$field_group_settings_tabs = (array) apply_filters( 'acf/field_group/additional_group_settings_tabs', array() );

	// remove any default tab values from the filter tabs.
	foreach ( $field_group_settings_tabs as $key => $tab ) {
		if ( isset( $default_field_group_settings_tabs[ $key ] ) ) {
			unset( $field_group_settings_tabs[ $key ] );
		}
	}

	$combined_field_group_settings_tabs = array_merge( $default_field_group_settings_tabs, $field_group_settings_tabs );

	return $combined_field_group_settings_tabs;
}
