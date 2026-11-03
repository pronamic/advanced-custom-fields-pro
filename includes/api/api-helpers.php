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
 * This function will return true for a non empty array
 *
 * @since   5.4.0
 *
 * @param   mixed $array The variable to test.
 * @return  boolean
 */
function acf_is_array( $array ) {
	return ( is_array( $array ) && ! empty( $array ) );
}

/**
 * Alias of acf()->has_setting()
 *
 * @since   5.6.5
 *
 * @param   string $name Name of the setting to check for.
 * @return  boolean
 */
function acf_has_setting( $name = '' ) {
	return acf()->has_setting( $name );
}

/**
 * acf_raw_setting
 *
 * alias of acf()->get_setting()
 *
 * @since   5.6.5
 *
 * @param   n/a
 * @return  n/a
 */
function acf_raw_setting( $name = '' ) {
	return acf()->get_setting( $name );
}

/**
 * acf_update_setting
 *
 * alias of acf()->update_setting()
 *
 * @since   5.0.0
 *
 * @param   $name (string)
 * @param   $value (mixed)
 * @return  n/a
 */
function acf_update_setting( $name, $value ) {
	// validate name.
	$name = acf_validate_setting( $name );

	// update.
	return acf()->update_setting( $name, $value );
}

/**
 * acf_validate_setting
 *
 * Returns the changed setting name if available.
 *
 * @since   5.6.5
 *
 * @param   n/a
 * @return  n/a
 */
function acf_validate_setting( $name = '' ) {
	return apply_filters( 'acf/validate_setting', $name );
}

/**
 * Alias of acf()->get_setting()
 *
 * @since   5.0.0
 *
 * @param   string $name  The name of the setting to test.
 * @param string $value An optional default value for the setting if it doesn't exist.
 * @return  n/a
 */
function acf_get_setting( $name, $value = null ) {
	$name = acf_validate_setting( $name );

	// replace default setting value if it exists.
	if ( acf_has_setting( $name ) ) {
		$value = acf_raw_setting( $name );
	}

	// filter.
	$value = apply_filters( "acf/settings/{$name}", $value );

	return $value;
}

/**
 * Return an array of ACF's internal post type names
 *
 * @since 6.1
 * @return array An array of ACF's internal post type names
 */
function acf_get_internal_post_types() {
	return array( 'acf-field-group', 'acf-post-type', 'acf-taxonomy', 'acf-ui-options-page' );
}

/**
 * acf_append_setting
 *
 * This function will add a value into the settings array found in the acf object
 *
 * @since   5.0.0
 *
 * @param   $name (string)
 * @param   $value (mixed)
 * @return  n/a
 */
function acf_append_setting( $name, $value ) {

	// vars
	$setting = acf_raw_setting( $name );

	// bail early if not array
	if ( ! is_array( $setting ) ) {
		$setting = array();
	}

	// append
	$setting[] = $value;

	// update
	return acf_update_setting( $name, $setting );
}

/**
 * acf_get_data
 *
 * Returns data.
 *
 * @since   5.0.0
 *
 * @param   string $name
 * @return  mixed
 */
function acf_get_data( $name ) {
	return acf()->get_data( $name );
}

/**
 * acf_set_data
 *
 * Sets data.
 *
 * @since   5.0.0
 *
 * @param   string $name
 * @param   mixed  $value
 * @return  n/a
 */
function acf_set_data( $name, $value ) {
	return acf()->set_data( $name, $value );
}

/**
 * Appends data to an existing key.
 *
 * @since   5.9.0
 *
 * @param string $name The data name.
 * @param mixed  $data The data to append to name.
 */
function acf_append_data( $name, $data ) {
	$prev_data = acf()->get_data( $name );
	if ( is_array( $prev_data ) ) {
		$data = array_merge( $prev_data, $data );
	}
	acf()->set_data( $name, $data );
}

/**
 * Alias of acf()->init() - the core ACF init function.
 *
 * @since   5.0.0
 */
function acf_init() {
	acf()->init();
}

/**
 * acf_has_done
 *
 * This function will return true if this action has already been done
 *
 * @since   5.3.2
 *
 * @param   $name (string)
 * @return  (boolean)
 */
function acf_has_done( $name ) {

	// return true if already done
	if ( acf_raw_setting( "has_done_{$name}" ) ) {
		return true;
	}

	// update setting and return
	acf_update_setting( "has_done_{$name}", true );
	return false;
}

/**
 * This function will return the path to a file within an external folder
 *
 * @since   5.5.8
 *
 * @param   string $file Directory path.
 * @param   string $path Optional file path.
 * @return  string File path.
 */
function acf_get_external_path( $file, $path = '' ) {
	return plugin_dir_path( $file ) . $path;
}

/**
 * This function will return the url to a file within an internal ACF folder
 *
 * @since   5.5.8
 *
 * @param   string $file Directory path.
 * @param   string $path Optional file path.
 * @return  string File path.
 */
function acf_get_external_dir( $file, $path = '' ) {
	return acf_plugin_dir_url( $file ) . $path;
}

/**
 * This function will calculate the url to a plugin folder.
 * Different to the WP plugin_dir_url(), this function can calculate for urls outside of the plugins folder (theme include).
 *
 * @since   5.6.8
 *
 * @param   string $file A file path inside the ACF plugin to get the plugin directory path from.
 * @return  string The plugin directory path.
 */
function acf_plugin_dir_url( $file ) {
	$path = plugin_dir_path( $file );
	$path = wp_normalize_path( $path );

	// check plugins.
	$check_path = wp_normalize_path( realpath( WP_PLUGIN_DIR ) );
	if ( strpos( $path, $check_path ) === 0 ) {
		return str_replace( $check_path, plugins_url(), $path );
	}

	// check wp-content.
	$check_path = wp_normalize_path( realpath( WP_CONTENT_DIR ) );
	if ( strpos( $path, $check_path ) === 0 ) {
		return str_replace( $check_path, content_url(), $path );
	}

	// check root.
	$check_path = wp_normalize_path( realpath( ABSPATH ) );
	if ( strpos( $path, $check_path ) === 0 ) {
		return str_replace( $check_path, site_url( '/' ), $path );
	}

	// return.
	return plugin_dir_url( $file );
}

/**
 * This function will merge together 2 arrays and also convert any numeric values to ints
 *
 * @since   5.0.0
 *
 * @param   array $args     The configured arguments array.
 * @param   array $defaults The default properties for the passed args to inherit.
 * @return  array $args Parsed arguments with defaults applied.
 */
function acf_parse_args( $args, $defaults = array() ) {
	$args = wp_parse_args( $args, $defaults );

	// parse types
	$args = acf_parse_types( $args );

	return $args;
}

/**
 * acf_parse_types
 *
 * This function will convert any numeric values to int and trim strings
 *
 * @since   5.0.0
 *
 * @param   $var (mixed)
 * @return  $var (mixed)
 */
function acf_parse_types( $array ) {
	return array_map( 'acf_parse_type', $array );
}

/**
 * acf_parse_type
 *
 * description
 *
 * @since   5.0.9
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_parse_type( $v ) {

	// Check if is string.
	if ( is_string( $v ) ) {

		// Trim ("Word " = "Word").
		$v = trim( $v );

		// Convert int strings to int ("123" = 123).
		if ( is_numeric( $v ) && strval( intval( $v ) ) === $v ) {
			$v = intval( $v );
		}
	}

	// return.
	return $v;
}

/**
 * This function will load in a file from the 'admin/views' folder and allow variables to be passed through
 *
 * @since   5.0.0
 *
 * @param string $view_path
 * @param array  $view_args
 */
function acf_get_view( $view_path = '', $view_args = array() ) {
	// allow view file name shortcut
	if ( substr( $view_path, -4 ) !== '.php' ) {
		$view_path = acf_get_path( "includes/admin/views/{$view_path}.php" );
	}

	// include
	if ( file_exists( $view_path ) ) {
		// Use `EXTR_SKIP` here to prevent `$view_path` from being accidentally/maliciously overridden.
		extract( $view_args, EXTR_SKIP );
		include $view_path;
	}
}

/**
 * acf_merge_atts
 *
 * description
 *
 * @since   5.0.9
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_merge_atts( $atts, $extra = array() ) {

	// bail early if no $extra
	if ( empty( $extra ) ) {
		return $atts;
	}

	// trim
	$extra = array_map( 'trim', $extra );
	$extra = array_filter( $extra );

	// merge in new atts
	foreach ( $extra as $k => $v ) {

		// append
		if ( $k == 'class' || $k == 'style' ) {
			$atts[ $k ] .= ' ' . $v;

			// merge
		} else {
			$atts[ $k ] = $v;
		}
	}

	return $atts;
}

/**
 * This function will create and echo a basic nonce input
 *
 * @since   5.6.0
 *
 * @param string $nonce The nonce parameter string.
 */
function acf_nonce_input( $nonce = '' ) {
	echo '<input type="hidden" name="_acf_nonce" value="' . esc_attr( wp_create_nonce( $nonce ) ) . '" />';
}

/**
 * This function will remove the var from the array, and return the var
 *
 * @since   5.0.0
 *
 * @param array  $extract_array an array passed as reference to be extracted.
 * @param string $key           The key to extract from the array.
 * @param mixed  $default_value The default value if it doesn't exist in the extract array.
 * @return mixed Extracted var or default.
 */
function acf_extract_var( &$extract_array, $key, $default_value = null ) {
	// check if exists - uses array_key_exists to extract NULL values (isset will fail).
	if ( is_array( $extract_array ) && array_key_exists( $key, $extract_array ) ) {

		// store and unset value.
		$v = $extract_array[ $key ];
		unset( $extract_array[ $key ] );

		return $v;
	}

	return $default_value;
}

/**
 * This function will remove the vars from the array, and return the vars
 *
 * @since   5.0.0
 *
 * @param array $extract_array an array passed as reference to be extracted.
 * @param array $keys          An array of keys to extract from the original array.
 * @return array An array of extracted values.
 */
function acf_extract_vars( &$extract_array, $keys ) {
	$r = array();

	foreach ( $keys as $key ) {
		$r[ $key ] = acf_extract_var( $extract_array, $key );
	}

	return $r;
}

/**
 * acf_get_sub_array
 *
 * This function will return a sub array of data
 *
 * @since   5.3.2
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_get_sub_array( $array, $keys ) {

	$r = array();

	foreach ( $keys as $key ) {
		$r[ $key ] = $array[ $key ];
	}

	return $r;
}

/**
 * Returns an array of post type names.
 *
 * @since   5.0.0
 *
 * @param array $args Optional. An array of key => value arguments to match against the post type objects. Default empty array.
 * @return array A list of post type names.
 */
function acf_get_post_types( $args = array() ) {
	$post_types = array();

	// extract special arg
	$exclude   = acf_extract_var( $args, 'exclude', array() );
	$exclude[] = 'acf-field';
	$exclude[] = 'acf-field-group';
	$exclude[] = 'acf-post-type';
	$exclude[] = 'acf-taxonomy';
	$exclude[] = 'acf-ui-options-page';

	// Get post type objects.
	$objects = get_post_types( $args, 'objects' );

	foreach ( $objects as $i => $object ) {
		// Bail early if is exclude.
		if ( in_array( $i, $exclude ) ) {
			continue;
		}

		// Bail early if is builtin (WP) private post type
		// i.e. nav_menu_item, revision, customize_changeset, etc.
		if ( $object->_builtin && ! $object->public ) {
			continue;
		}

		$post_types[] = $i;
	}

	return apply_filters( 'acf/get_post_types', $post_types, $args );
}

function acf_get_pretty_post_types( $post_types = array() ) {

	// get post types
	if ( empty( $post_types ) ) {

		// get all custom post types
		$post_types = acf_get_post_types();
	}

	// get labels
	$ref = array();
	$r   = array();

	foreach ( $post_types as $post_type ) {

		// vars
		$label = acf_get_post_type_label( $post_type );

		// append to r
		$r[ $post_type ] = $label;

		// increase counter
		if ( ! isset( $ref[ $label ] ) ) {
			$ref[ $label ] = 0;
		}

		++$ref[ $label ];
	}

	// get slugs
	foreach ( array_keys( $r ) as $i ) {

		// vars
		$post_type = $r[ $i ];

		if ( $ref[ $post_type ] > 1 ) {
			$r[ $i ] .= ' (' . $i . ')';
		}
	}

	// return
	return $r;
}

/**
 * Function acf_get_post_stati()
 *
 * Returns an array of post status names.
 *
 * @since   6.1.0
 *
 * @param   array $args Optional. An array of key => value arguments to match against the post status objects. Default empty array.
 * @return  array A list of post status names.
 */
function acf_get_post_stati( $args = array() ) {

	$args['internal'] = false;

	$post_statuses = get_post_stati( $args );

	unset( $post_statuses['acf-disabled'] );

	$post_statuses = (array) apply_filters( 'acf/get_post_stati', $post_statuses, $args );

	return $post_statuses;
}
/**
 * Function acf_get_pretty_post_statuses()
 *
 * Returns a clean array of post status names.
 *
 * @since   6.1.0
 *
 * @param   array $post_statuses Optional. An array of post status objects. Default empty array.
 * @return  array An array of post status names.
 */
function acf_get_pretty_post_statuses( $post_statuses = array() ) {

	// Get all post statuses.
	$post_statuses = array_merge( $post_statuses, acf_get_post_stati() );

	$ref    = array();
	$result = array();

	foreach ( $post_statuses as $post_status ) {
		$label = acf_get_post_status_label( $post_status );

		$result[ $post_status ] = $label;

		if ( ! isset( $ref[ $label ] ) ) {
			$ref[ $label ] = 0;
		}

		++$ref[ $label ];
	}

	foreach ( array_keys( $result ) as $i ) {
		$post_status = $result[ $i ];

		if ( $ref[ $post_status ] > 1 ) {
			$result[ $i ] .= ' (' . $i . ')';
		}
	}

	return $result;
}

/**
 * acf_get_post_type_label
 *
 * This function will return a pretty label for a specific post_type
 *
 * @since   5.4.0
 *
 * @param   $post_type (string)
 * @return  (string)
 */
function acf_get_post_type_label( $post_type ) {

	// vars
	$label = $post_type;

	// check that object exists
	// - case exists when importing field group from another install and post type does not exist
	if ( post_type_exists( $post_type ) ) {
		$obj   = get_post_type_object( $post_type );
		$label = $obj->labels->singular_name;
	}

	// return
	return $label;
}

/**
 * Function acf_get_post_status_label()
 *
 * This function will return a pretty label for a specific post_status
 *
 * @since   6.1.0
 *
 * @param   string $post_status The post status.
 * @return  string The post status label.
 */
function acf_get_post_status_label( $post_status ) {
	$label = $post_status;
	$obj   = get_post_status_object( $post_status );
	$label = is_object( $obj ) ? $obj->label : '';

	return $label;
}

/**
 * acf_verify_nonce
 *
 * This function will look at the $_POST['_acf_nonce'] value and return true or false
 *
 * @since   5.0.0
 *
 * @param   $nonce (string)
 * @return  (boolean)
 */
function acf_verify_nonce( $value ) {

	// vars
	$nonce = acf_maybe_get_POST( '_acf_nonce' );

	// bail early nonce does not match (post|user|comment|term)
	if ( ! $nonce || ! wp_verify_nonce( $nonce, $value ) ) {
		return false;
	}

	// reset nonce (only allow 1 save)
	$_POST['_acf_nonce'] = false;

	// return
	return true;
}

/**
 * Returns true if the current AJAX request is valid.
 * It's action will also allow WPML to set the lang and avoid AJAX get_posts issues
 *
 * @since   5.2.3
 *
 * @param string  $nonce               The nonce to check.
 * @param string  $action              The action of the nonce.
 * @param boolean $action_is_field     If the action is a field, modify the action to match validate the field type.
 * @param string  $expected_field_type Optional field type the resolved field must be when $action_is_field is true. Prevents a nonce minted for one field type from being accepted by an AJAX handler that expects a different one. Defaults to empty (no type validation).
 * @return boolean
 */
function acf_verify_ajax( $nonce = '', $action = '', $action_is_field = false, $expected_field_type = '' ) {
	// Bail early if we don't have a nonce to check.
	if ( empty( $nonce ) && empty( $_REQUEST['nonce'] ) ) {
		return false;
	}

	// Build the action if we're trying to validate a specific field nonce.
	if ( $action_is_field ) {
		if ( ! acf_is_field_key( $action ) ) {
			return false;
		}

		$field = acf_get_field( $action );

		if ( empty( $field['type'] ) ) {
			return false;
		}

		if ( ! empty( $expected_field_type ) && $field['type'] !== $expected_field_type ) {
			return false;
		}

		$action = 'acf_field_' . $field['type'] . '_' . $action;
	}

	$nonce_to_check = ! empty( $nonce ) ? $nonce : $_REQUEST['nonce']; // phpcs:ignore WordPress.Security -- We're verifying a nonce here.
	$nonce_action   = ! empty( $action ) ? $action : 'acf_nonce';

	// Bail if nonce can't be verified.
	if ( ! wp_verify_nonce( sanitize_text_field( $nonce_to_check ), $nonce_action ) ) {
		return false;
	}

	// Action for 3rd party customization (WPML).
	do_action( 'acf/verify_ajax' );

	return true;
}

/**
 * acf_get_image_sizes
 *
 * This function will return an array of available image sizes
 *
 * @since   5.0.0
 *
 * @param   n/a
 * @return  (array)
 */
function acf_get_image_sizes() {

	// vars
	$sizes = array(
		'thumbnail' => __( 'Thumbnail', 'acf' ),
		'medium'    => __( 'Medium', 'acf' ),
		'large'     => __( 'Large', 'acf' ),
	);

	// find all sizes
	$all_sizes = get_intermediate_image_sizes();

	// add extra registered sizes
	if ( ! empty( $all_sizes ) ) {
		foreach ( $all_sizes as $size ) {

			// bail early if already in array
			if ( isset( $sizes[ $size ] ) ) {
				continue;
			}

			// append to array
			$label          = str_replace( '-', ' ', $size );
			$label          = ucwords( $label );
			$sizes[ $size ] = $label;
		}
	}

	// add sizes
	foreach ( array_keys( $sizes ) as $s ) {

		// vars
		$data = acf_get_image_size( $s );

		// append
		if ( $data['width'] && $data['height'] ) {
			$sizes[ $s ] .= ' (' . $data['width'] . ' x ' . $data['height'] . ')';
		}
	}

	// add full end
	$sizes['full'] = __( 'Full Size', 'acf' );

	// filter for 3rd party customization
	$sizes = apply_filters( 'acf/get_image_sizes', $sizes );

	// return
	return $sizes;
}

function acf_get_image_size( $s = '' ) {

	// global
	global $_wp_additional_image_sizes;

	// rename for nicer code
	$_sizes = $_wp_additional_image_sizes;

	// vars
	$data = array(
		'width'  => isset( $_sizes[ $s ]['width'] ) ? $_sizes[ $s ]['width'] : get_option( "{$s}_size_w" ),
		'height' => isset( $_sizes[ $s ]['height'] ) ? $_sizes[ $s ]['height'] : get_option( "{$s}_size_h" ),
	);

	// return
	return $data;
}

/**
 * acf_version_compare
 *
 * Similar to the version_compare() function but with extra functionality.
 *
 * @since   5.5.0
 *
 * @param   string $left    The left version number.
 * @param   string $compare The compare operator.
 * @param   string $right   The right version number.
 * @return  boolean
 */
function acf_version_compare( $left = '', $compare = '>', $right = '' ) {

	// Detect 'wp' placeholder.
	if ( $left === 'wp' ) {
		global $wp_version;
		$left = $wp_version;
	}

	// Return result.
	return version_compare( $left, $right, $compare );
}

/**
 * acf_get_full_version
 *
 * This function will remove any '-beta1' or '-RC1' strings from a version
 *
 * @since   5.5.0
 *
 * @param   $version (string)
 * @return  (string)
 */
function acf_get_full_version( $version = '1' ) {

	// remove '-beta1' or '-RC1'
	if ( $pos = strpos( $version, '-' ) ) {
		$version = substr( $version, 0, $pos );
	}

	// return
	return $version;
}

/**
 * acf_get_terms
 *
 * This function is a wrapper for the get_terms() function
 *
 * @since   5.4.0
 *
 * @param   $args (array)
 * @return  (array)
 */
function acf_get_terms( $args ) {

	// defaults
	$args = wp_parse_args(
		$args,
		array(
			'taxonomy'               => null,
			'hide_empty'             => false,
			'update_term_meta_cache' => false,
		)
	);

	// return
	return get_terms( $args );
}

/**
 * acf_get_taxonomy_terms
 *
 * This function will return an array of available taxonomy terms
 *
 * @since   5.0.0
 *
 * @param   $taxonomies (array)
 * @return  (array)
 */
function acf_get_taxonomy_terms( $taxonomies = array() ) {

	// force array
	$taxonomies = acf_get_array( $taxonomies );

	// get pretty taxonomy names
	$taxonomies = acf_get_pretty_taxonomies( $taxonomies );

	// vars
	$r = array();

	// populate $r
	foreach ( array_keys( $taxonomies ) as $taxonomy ) {

		// vars
		$label           = $taxonomies[ $taxonomy ];
		$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
		$terms           = acf_get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		// bail early i no terms
		if ( empty( $terms ) ) {
			continue;
		}

		// sort into hierachial order!
		if ( $is_hierarchical ) {
			$terms = _get_term_children( 0, $terms, $taxonomy );
		}

		// add placeholder
		$r[ $label ] = array();

		// add choices
		foreach ( $terms as $term ) {
			$k                 = "{$taxonomy}:{$term->slug}";
			$r[ $label ][ $k ] = acf_get_term_title( $term );
		}
	}

	// return
	return $r;
}

/**
 * acf_decode_taxonomy_terms
 *
 * This function decodes the $taxonomy:$term strings into a nested array
 *
 * @since   5.0.0
 *
 * @param   $terms (array)
 * @return  (array)
 */
function acf_decode_taxonomy_terms( $strings = false ) {

	// bail early if no terms
	if ( empty( $strings ) ) {
		return false;
	}

	// vars
	$terms = array();

	// loop
	foreach ( $strings as $string ) {

		// vars
		$data     = acf_decode_taxonomy_term( $string );
		$taxonomy = $data['taxonomy'];
		$term     = $data['term'];

		// create empty array
		if ( ! isset( $terms[ $taxonomy ] ) ) {
			$terms[ $taxonomy ] = array();
		}

		// append
		$terms[ $taxonomy ][] = $term;
	}

	// return
	return $terms;
}

/**
 * acf_decode_taxonomy_term
 *
 * This function will return the taxonomy and term slug for a given value
 *
 * @since   5.0.0
 *
 * @param   $string (string)
 * @return  (array)
 */
function acf_decode_taxonomy_term( $value ) {

	// vars
	$data = array(
		'taxonomy' => '',
		'term'     => '',
	);

	// int
	if ( is_numeric( $value ) ) {
		$data['term'] = $value;

		// string
	} elseif ( is_string( $value ) ) {
		$value            = explode( ':', $value );
		$data['taxonomy'] = isset( $value[0] ) ? $value[0] : '';
		$data['term']     = isset( $value[1] ) ? $value[1] : '';

		// error
	} else {
		return false;
	}

	// allow for term_id (Used by ACF v4)
	if ( is_numeric( $data['term'] ) ) {

		// global
		global $wpdb;

		// find taxonomy
		if ( ! $data['taxonomy'] ) {
			$data['taxonomy'] = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $data['term'] ) );
		}

		// find term (may have numeric slug '123')
		$term = get_term_by( 'slug', $data['term'], $data['taxonomy'] );

		// attempt get term via ID (ACF4 uses ID)
		if ( ! $term ) {
			$term = get_term( $data['term'], $data['taxonomy'] );
		}

		// bail early if no term
		if ( ! $term ) {
			return false;
		}

		// update
		$data['taxonomy'] = $term->taxonomy;
		$data['term']     = $term->slug;
	}

	// return
	return $data;
}

/**
 * acf_array
 *
 * Casts the value into an array.
 *
 * @since   5.7.10
 *
 * @param   mixed $val The value to cast.
 * @return  array
 */
function acf_array( $val = array() ) {
	return (array) $val;
}

/**
 * Returns a non-array value.
 *
 * @since   5.8.10
 *
 * @param   mixed $val The value to review.
 * @return  mixed
 */
function acf_unarray( $val ) {
	if ( is_array( $val ) ) {
		return reset( $val );
	}
	return $val;
}

/**
 * acf_get_array
 *
 * This function will force a variable to become an array
 *
 * @since   5.0.0
 *
 * @param   $var (mixed)
 * @return  (array)
 */
function acf_get_array( $var = false, $delimiter = '' ) {

	// array
	if ( is_array( $var ) ) {
		return $var;
	}

	// bail early if empty
	if ( acf_is_empty( $var ) ) {
		return array();
	}

	// string
	if ( is_string( $var ) && $delimiter ) {
		return explode( $delimiter, $var );
	}

	// place in array
	return (array) $var;
}

/**
 * acf_get_numeric
 *
 * This function will return numeric values
 *
 * @since   5.4.0
 *
 * @param   $value (mixed)
 * @return  (mixed)
 */
function acf_get_numeric( $value = '' ) {

	// vars
	$numbers  = array();
	$is_array = is_array( $value );

	// loop
	foreach ( (array) $value as $v ) {
		if ( is_numeric( $v ) ) {
			$numbers[] = (int) $v;
		}
	}

	// bail early if is empty
	if ( empty( $numbers ) ) {
		return false;
	}

	// convert array
	if ( ! $is_array ) {
		$numbers = $numbers[0];
	}

	// return
	return $numbers;
}

/**
 * acf_get_posts
 *
 * Similar to the get_posts() function but with extra functionality.
 *
 * @since   5.1.5
 *
 * @param   array $args The query args.
 * @return  array
 */
function acf_get_posts( $args = array() ) {

	// Vars.
	$posts = array();

	// Apply default args.
	$args = wp_parse_args(
		$args,
		array(
			'posts_per_page'         => -1,
			'post_type'              => '',
			'post_status'            => 'any',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	// Avoid default 'post' post_type by providing all public types.
	if ( ! $args['post_type'] ) {
		$args['post_type'] = acf_get_post_types();
	}

	if ( ! $args['post_status'] ) {
		$args['post_status'] = acf_get_post_stati();
	}

	// Check if specific post IDs have been provided.
	if ( $args['post__in'] ) {

		// Clean value into an array of IDs.
		$args['post__in'] = array_map( 'intval', acf_array( $args['post__in'] ) );
	}

	/**
	 * Filters the args used in `acf_get_posts()` that are passed to `get_posts()`.
	 *
	 * @since 6.1.7
	 *
	 * @param array $args The args passed to `get_posts()`.
	 */
	$args = apply_filters( 'acf/acf_get_posts/args', $args );

	// Query posts.
	$posts = get_posts( $args );

	// Remove any potential empty results.
	$posts = array_filter( $posts );

	// Manually order results.
	if ( $posts && $args['post__in'] ) {
		$order = array();
		foreach ( $posts as $i => $post ) {
			$order[ $i ] = array_search( $post->ID, $args['post__in'] );
		}
		array_multisort( $order, $posts );
	}

	/**
	 * Filters the results found in the `acf_get_posts()` function.
	 *
	 * @since 6.1.7
	 *
	 * @param array $posts The results from the `get_posts()` call.
	 */
	return apply_filters( 'acf/acf_get_posts/results', $posts );
}

/**
 * _acf_query_remove_post_type
 *
 * This function will remove the 'wp_posts.post_type' WHERE clause completely
 * When using 'post__in', this clause is unneccessary and slow.
 *
 * @since   5.1.5
 *
 * @param   $sql (string)
 * @return  $sql
 */
function _acf_query_remove_post_type( $sql ) {

	// global
	global $wpdb;

	// bail early if no 'wp_posts.ID IN'
	if ( strpos( $sql, "$wpdb->posts.ID IN" ) === false ) {
		return $sql;
	}

	// get bits
	$glue = 'AND';
	$bits = explode( $glue, $sql );

	// loop through $where and remove any post_type queries
	foreach ( $bits as $i => $bit ) {
		if ( strpos( $bit, "$wpdb->posts.post_type" ) !== false ) {
			unset( $bits[ $i ] );
		}
	}

	// join $where back together
	$sql = implode( $glue, $bits );

	// return
	return $sql;
}

/**
 * acf_get_grouped_posts
 *
 * This function will return all posts grouped by post_type
 * This is handy for select settings
 *
 * @since   5.0.0
 *
 * @param array $args The query arguments.
 * @return array
 */
function acf_get_grouped_posts( $args ) {

	// vars
	$data = array();

	// defaults
	$args = wp_parse_args(
		$args,
		array(
			'posts_per_page'         => -1,
			'paged'                  => 0,
			'post_type'              => 'post',
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'post_status'            => 'any',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
		)
	);

	// Narrow anonymous callers to publicly-viewable types/statuses.
	if ( ! is_user_logged_in() ) {
		$requested_post_types = acf_get_array( $args['post_type'] );

		if ( in_array( 'any', $requested_post_types, true ) ) {
			$requested_post_types = get_post_types();
		}

		$viewable_post_types = array_values( array_filter( $requested_post_types, 'is_post_type_viewable' ) );

		if ( empty( $viewable_post_types ) ) {
			return $data;
		}

		$requested_post_statuses = acf_get_array( $args['post_status'] );
		$public_post_statuses    = array_values( array_filter( get_post_stati(), 'is_post_status_viewable' ) );

		if ( empty( $requested_post_statuses ) ) {
			return $data;
		}

		if ( in_array( 'any', $requested_post_statuses, true ) ) {
			$viewable_post_statuses = $public_post_statuses;

			if ( in_array( 'attachment', $viewable_post_types, true ) ) {
				$viewable_post_statuses[] = 'inherit';
			}
		} else {
			$viewable_post_statuses = array_values( array_intersect( $requested_post_statuses, $public_post_statuses ) );

			if ( in_array( 'attachment', $viewable_post_types, true ) && in_array( 'inherit', $requested_post_statuses, true ) ) {
				$viewable_post_statuses[] = 'inherit';
			}
		}

		if ( empty( $viewable_post_statuses ) ) {
			return $data;
		}

		$args['post_type']   = $viewable_post_types;
		$args['post_status'] = array_values( array_unique( $viewable_post_statuses ) );
	}

	// `perm=readable` only covers private posts, so also hide other posts and attachments the user can't read.
	if ( is_user_logged_in() && isset( $args['perm'] ) && 'readable' === $args['perm'] ) {
		$args['acf_restrict_unreadable_posts'] = true;
	}

	// find array of post_type
	$post_types          = acf_get_array( $args['post_type'] );
	$is_single_post_type = ( count( $post_types ) === 1 );

	// WordPress 6.8+ sorts post_type arrays for cache key generation
	// We need to use the same sorted order when processing results
	if (
		! $is_single_post_type &&
		$args['posts_per_page'] !== -1 &&
		version_compare( get_bloginfo( 'version' ), '6.8', '>=' )
	) {
		sort( $post_types );
	}

	$post_types_labels = acf_get_pretty_post_types( $post_types );

	// attachment doesn't work if it is the only item in an array
	if ( $is_single_post_type ) {
		$args['post_type'] = reset( $post_types );
	}

	// add filter to orderby post type
	if ( ! $is_single_post_type ) {
		add_filter( 'posts_orderby', '_acf_orderby_post_type', 10, 2 );
	}

	// get posts
	$posts = get_posts( $args );

	// remove this filter (only once)
	if ( ! $is_single_post_type ) {
		remove_filter( 'posts_orderby', '_acf_orderby_post_type', 10, 2 );
	}

	// loop
	foreach ( $post_types as $post_type ) {

		// vars
		$this_posts = array();
		$this_group = array();

		// populate $this_posts
		foreach ( $posts as $post ) {
			if ( $post->post_type == $post_type ) {
				$this_posts[] = $post;
			}
		}

		// bail early if no posts for this post type
		if ( empty( $this_posts ) ) {
			continue;
		}

		// sort into hierachial order!
		// this will fail if a search has taken place because parents wont exist
		if ( is_post_type_hierarchical( $post_type ) && empty( $args['s'] ) ) {

			// vars
			$post_id   = $this_posts[0]->ID;
			$parent_id = acf_maybe_get( $args, 'post_parent', 0 );
			$offset    = 0;
			$length    = count( $this_posts );

			// get all posts from this post type
			$all_posts = get_posts(
				array_merge(
					$args,
					array(
						'posts_per_page' => -1,
						'paged'          => 0,
						'post_type'      => $post_type,
					)
				)
			);

			// find starting point (offset)
			foreach ( $all_posts as $i => $post ) {
				if ( $post->ID == $post_id ) {
					$offset = $i;
					break;
				}
			}

			// order posts
			$ordered_posts = get_page_children( $parent_id, $all_posts );

			// compare aray lengths
			// if $ordered_posts is smaller than $all_posts, WP has lost posts during the get_page_children() function
			// this is possible when get_post( $args ) filter out parents (via taxonomy, meta and other search parameters)
			if ( count( $ordered_posts ) == count( $all_posts ) ) {
				$this_posts = array_slice( $ordered_posts, $offset, $length );
			}
		}

		// populate $this_posts
		foreach ( $this_posts as $post ) {
			$this_group[ $post->ID ] = $post;
		}

		// group by post type
		$label          = $post_types_labels[ $post_type ];
		$data[ $label ] = $this_group;
	}

	// return
	return $data;
}

/**
 * Normalizes an args array so `perm=readable` actually applies inside `WP_Query`.
 *
 * WP_Query ignores `perm=readable` when `post_status='any'` (Trac #52094). Callers
 * that opt into `perm=readable` therefore need `post_status` enumerated. This helper
 * is idempotent and safe to call before and after the per-field `acf/fields/<type>/query`
 * filters run — the post-filter call catches callbacks that reset `post_status`
 * back to 'any' while leaving `perm` untouched.
 *
 * @since 6.8.7
 *
 * @param array $args WP_Query args.
 * @return array Args with `post_status` enumerated when `perm=readable` requires it.
 */
function acf_ensure_perm_readable_post_status( $args ) {
	if ( ! isset( $args['perm'] ) || 'readable' !== $args['perm'] ) {
		return $args;
	}

	$status = isset( $args['post_status'] ) ? $args['post_status'] : '';
	$is_any = empty( $status )
		|| 'any' === $status
		|| ( is_array( $status ) && in_array( 'any', $status, true ) );

	if ( $is_any ) {
		$args['post_status'] = get_post_stati( array( 'exclude_from_search' => false ) );
	}

	return $args;
}

/**
 * The internal ACF function to return the statuses of a post type's posts that the current user can't
 * read when another user wrote them, following how core maps the `read_post` capability.
 *
 * Core lets users read other users' draft, pending and scheduled posts only if they can edit them, which
 * for scheduled posts also requires the capability to edit published posts. Other users' private posts
 * require the capability to read private posts. Post types that don't map meta capabilities let users
 * with the post type's read capability read all of its posts.
 *
 * Access granted to individual posts can't be known here, so sites that grant it can use the
 * `acf/fields/relational/unreadable_post_statuses` filter.
 *
 * @since 6.8.11
 *
 * @param string $post_type The post type.
 * @return string[]
 */
function _acf_get_unreadable_post_statuses( $post_type ) {
	$post_type_object   = get_post_type_object( $post_type );
	$protected_statuses = get_post_stati( array( 'protected' => true ) );
	$private_statuses   = get_post_stati( array( 'private' => true ) );

	if ( ! $post_type_object ) {
		return array_values( array_merge( $protected_statuses, $private_statuses ) );
	}

	$statuses = array();

	if ( ! $post_type_object->map_meta_cap ) {
		// `read_post` and `read_page` are meta capabilities, which can't be checked without a post.
		$read_cap = $post_type_object->cap->read_post;
		$can_read = ! in_array( $read_cap, array( 'read_post', 'read_page' ), true ) && current_user_can( $read_cap );

		if ( ! $can_read ) {
			$statuses = array_merge( $protected_statuses, $private_statuses );
		}
	} else {
		if ( ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$statuses = $protected_statuses;
		} elseif ( ! current_user_can( $post_type_object->cap->edit_published_posts ) ) {
			$statuses = array_intersect( $protected_statuses, array( 'future' ) );
		}

		if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
			$statuses = array_merge( $statuses, $private_statuses );
		}
	}

	/**
	 * Filters the statuses of other users' posts that relational field searches hide from the current user.
	 *
	 * Posts with a status removed here are included in the search query, and each is then checked with
	 * `acf_current_user_can_read_post()`, so posts the user can't read are still left out. Use this when
	 * access to individual posts is granted, such as through the `acf/user_can_read_post` filter
	 * or a `map_meta_cap` callback. Pages of results may then contain fewer posts, or none, and the next
	 * page being loaded can show that the search matched posts the user can't read.
	 *
	 * @since 6.8.11
	 *
	 * @param string[] $statuses  The statuses hidden from the current user.
	 * @param string   $post_type The post type.
	 */
	$statuses = apply_filters( 'acf/fields/relational/unreadable_post_statuses', array_values( $statuses ), $post_type );

	return array_values( array_filter( (array) $statuses, 'is_string' ) );
}

/**
 * The internal ACF function to hide posts the current user can't read from `acf_get_grouped_posts()`
 * queries.
 *
 * `WP_Query` only applies `perm=readable` to private posts, and checks an attachment's own `inherit`
 * status rather than its parent's. This also hides other users' draft, pending and scheduled posts the
 * current user can't edit, and attachments that inherit a status from a parent the user can't read.
 *
 * When the query has the field's post types in `acf_field_post_types`, it also hides other users' posts
 * of post types that aren't public, unless the field lists them or the user can open their list of posts
 * in the admin or edit other users' posts, matching `acf_user_can_read_post()`.
 *
 * @since 6.8.11
 *
 * @param string   $where    The WHERE clause of the query.
 * @param WP_Query $wp_query The WP_Query.
 * @return string The potentially modified WHERE clause.
 */
function _acf_query_restrict_unreadable_posts( $where, $wp_query ) {
	global $wpdb;

	if ( ! $wp_query->get( 'acf_restrict_unreadable_posts' ) ) {
		return $where;
	}

	$post_types = acf_get_array( $wp_query->get( 'post_type' ) );

	if ( empty( $post_types ) || in_array( 'any', $post_types, true ) ) {
		$post_types = get_post_types();
	}

	$field_post_types    = $wp_query->get( 'acf_field_post_types', null );
	$readable_post_types = array();
	$conditions          = array( $wpdb->prepare( "{$wpdb->posts}.post_author = %d", get_current_user_id() ) );

	foreach ( $post_types as $post_type ) {
		// Other users' posts of these post types are left out, so only the user's own posts are found.
		if ( is_array( $field_post_types ) && _acf_post_type_is_hidden_from_search( $post_type, $field_post_types ) ) {
			continue;
		}

		$statuses = _acf_get_unreadable_post_statuses( $post_type );

		if ( empty( $statuses ) ) {
			$readable_post_types[] = $post_type;
			continue;
		}

		$statuses  = implode( "','", array_map( 'esc_sql', $statuses ) );
		$condition = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", $post_type ) . " AND {$wpdb->posts}.post_status NOT IN ('$statuses')";

		// Attachments inherit their parent's status, so their parent must be readable too.
		if ( 'attachment' === $post_type ) {
			$condition .= " AND NOT ( {$wpdb->posts}.post_status = 'inherit' AND EXISTS ( SELECT 1 FROM {$wpdb->posts} AS acf_parent WHERE acf_parent.ID = {$wpdb->posts}.post_parent AND acf_parent.post_status IN ('$statuses') ) )";
		}

		$conditions[] = "( $condition )";
	}

	if ( count( $readable_post_types ) === count( $post_types ) ) {
		return $where;
	}

	if ( $readable_post_types ) {
		$readable_post_types = implode( "','", array_map( 'esc_sql', $readable_post_types ) );
		$conditions[]        = "{$wpdb->posts}.post_type IN ('$readable_post_types')";
	}

	return $where . ' AND (' . implode( ' OR ', $conditions ) . ')';
}
add_filter( 'posts_where', '_acf_query_restrict_unreadable_posts', 10, 2 );

/**
 * The internal ACF function to check whether relational field searches leave out other users' posts of a
 * post type, because it isn't public and the current user can't see its posts in the admin.
 *
 * @since 6.8.11
 *
 * @param string   $post_type        The post type.
 * @param string[] $field_post_types The post types listed by the field's "Filter by Post Type" setting.
 * @return boolean
 */
function _acf_post_type_is_hidden_from_search( $post_type, $field_post_types ) {
	if ( is_post_type_viewable( $post_type ) || in_array( $post_type, $field_post_types, true ) ) {
		return false;
	}

	$post_type_object = get_post_type_object( $post_type );

	return ! $post_type_object
		|| ( ! _acf_user_can_list_post_type( wp_get_current_user(), $post_type ) && ! current_user_can( $post_type_object->cap->edit_others_posts ) );
}

/**
 * The internal ACF function to add order by post types for use in `acf_get_grouped_posts`
 *
 * @param string $orderby  The current orderby value for a query.
 * @param object $wp_query The WP_Query.
 * @return string The potentially modified orderby string.
 */
function _acf_orderby_post_type( $orderby, $wp_query ) {
	global $wpdb;

	$post_types = $wp_query->get( 'post_type' );

	// Prepend the SQL.
	if ( is_array( $post_types ) ) {
		$post_types = array_map( 'esc_sql', $post_types );
		$post_types = implode( "','", $post_types );
		$orderby    = "FIELD({$wpdb->posts}.post_type,'$post_types')," . $orderby;
	}

	return $orderby;
}

function acf_get_post_title( $post = 0, $is_search = false ) {

	// vars
	$post    = get_post( $post );
	$title   = '';
	$prepend = '';
	$append  = '';

	// bail early if no post
	if ( ! $post ) {
		return '';
	}

	// title
	$title = get_the_title( $post->ID );

	// empty
	if ( $title === '' ) {
		$title = __( '(no title)', 'acf' );
	}

	// status
	if ( get_post_status( $post->ID ) != 'publish' ) {
		$append .= ' (' . get_post_status( $post->ID ) . ')';
	}

	// ancestors
	if ( $post->post_type !== 'attachment' ) {

		// get ancestors
		$ancestors = get_ancestors( $post->ID, $post->post_type );
		$prepend  .= str_repeat( '- ', count( $ancestors ) );
	}

	// merge
	$title = $prepend . $title . $append;

	// return
	return $title;
}

/**
 * Checks whether a user can read a post referenced by a relational field.
 *
 * Matches WordPress core: published posts can be read by anyone (as in the REST API, and as the
 * relational field search already shows them), and other posts need to be publicly viewable or
 * need the `read_post` capability (which needs edit access for other users' drafts).
 *
 * Posts of post types that aren't public, such as WooCommerce coupons, can only be seen in the admin.
 * Published ones are readable if the field's "Filter by Post Type" setting lists their post type, or
 * the user can open the post type's list of posts in the admin, which shows their titles. Others need
 * the user to be able to edit them. Revisions are never public, so they need edit access, which for a
 * revision means edit access to its parent.
 *
 * Unlike `acf_rest_reference_is_exposable()`, this doesn't require the post type to be shown in the
 * REST API, because these fields can reference any post type in the editor.
 *
 * @since 6.8.11
 *
 * @param integer|WP_User $user  The user ID or object. 0 for visitors who aren't logged in.
 * @param integer|WP_Post $post  The post ID or object.
 * @param array           $field Optional. The field referencing the post.
 * @return boolean
 */
function acf_user_can_read_post( $user, $post, $field = array() ) {
	$user = $user instanceof WP_User ? $user : new WP_User( (int) $user );
	$post = get_post( $post );

	if ( ! $post ) {
		$can_read = false;
	} elseif ( 'revision' === $post->post_type ) {
		$can_read = user_can( $user, 'edit_post', $post->ID );
	} elseif ( ! is_post_type_viewable( $post->post_type ) && ! in_array( $post->post_type, acf_get_array( $field['post_type'] ?? array() ), true ) ) {
		$can_read = ( 'publish' === get_post_status( $post ) && _acf_user_can_list_post_type( $user, $post->post_type ) )
			|| user_can( $user, 'edit_post', $post->ID );
	} else {
		$can_read = 'publish' === get_post_status( $post )
			|| is_post_publicly_viewable( $post )
			|| user_can( $user, 'read_post', $post->ID );
	}

	/**
	 * Filters whether a user can read a post referenced by a relational field.
	 *
	 * The user is usually the current user. While an ACF Block saved in a post or synced pattern renders,
	 * other than in a preview, it's the author of that post, so check `$user` rather than the current user.
	 *
	 * Relational field searches leave out other users' posts the user can't read before this filter runs,
	 * so returning true doesn't add them to search results. Use the
	 * `acf/fields/relational/unreadable_post_statuses` filter to include them.
	 *
	 * @since 6.8.11
	 *
	 * @param boolean      $can_read Whether the user can read the post.
	 * @param WP_Post|null $post     The post object, or null if the post doesn't exist.
	 * @param array        $field    The field referencing the post, if any.
	 * @param WP_User      $user     The user.
	 */
	return (bool) apply_filters( 'acf/user_can_read_post', $can_read, $post, $field, $user );
}

/**
 * Checks whether the current user can read a post referenced by a relational field.
 *
 * @since 6.8.11
 *
 * @see acf_user_can_read_post()
 *
 * @param integer|WP_Post $post  The post ID or object.
 * @param array           $field Optional. The field referencing the post.
 * @return boolean
 */
function acf_current_user_can_read_post( $post, $field = array() ) {
	return acf_user_can_read_post( wp_get_current_user(), $post, $field );
}

/**
 * The internal ACF function to check whether a user can open a post type's list of posts in the admin,
 * which shows the titles of all its published posts.
 *
 * @since 6.8.11
 *
 * @param integer|WP_User $user      The user ID or object.
 * @param string          $post_type The post type.
 * @return boolean
 */
function _acf_user_can_list_post_type( $user, $post_type ) {
	$post_type_object = get_post_type_object( $post_type );

	return $post_type_object && $post_type_object->show_ui && user_can( $user, $post_type_object->cap->edit_posts );
}

/**
 * Removes posts the current user can't read from a relational field's search results.
 *
 * The query already hides posts core doesn't let the user read, but the `acf/user_can_read_post`
 * filter can deny others. Searches that a site has opted out of `perm=readable` aren't checked.
 *
 * @since 6.8.11
 *
 * @param array $groups The posts from `acf_get_grouped_posts()`, grouped by post type label.
 * @param array $args   The query args.
 * @param array $field  The relational field being searched.
 * @return array An array with `groups`, the groups without posts the user can't read, and `more`, whether
 *               there may be more pages of results, judged before any posts were removed.
 */
function acf_remove_unreadable_grouped_posts( $groups, $args, $field ) {
	$groups   = acf_get_array( $groups );
	$per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 0;
	$more     = $per_page > 0 && array_sum( array_map( 'count', $groups ) ) >= $per_page;

	if ( isset( $args['perm'] ) && 'readable' === $args['perm'] ) {
		foreach ( array_keys( $groups ) as $label ) {
			$groups[ $label ] = array_filter(
				$groups[ $label ],
				function ( $post ) use ( $field ) {
					return acf_current_user_can_read_post( $post, $field );
				}
			);

			if ( empty( $groups[ $label ] ) ) {
				unset( $groups[ $label ] );
			}
		}
	}

	return array(
		'groups' => $groups,
		'more'   => $more,
	);
}

/**
 * Returns the label shown in place of a selected post the current user can't read.
 *
 * @since 6.8.11
 *
 * @return string
 */
function acf_get_restricted_post_title() {
	return __( '(Restricted item)', 'acf' );
}

/**
 * The internal ACF function to return the object whose stored values a save is compared against.
 * Revisions don't store values of their own yet, so they're compared against their parent post.
 *
 * @since 6.8.11
 *
 * @param integer|string $post_id The ACF post ID being saved.
 * @return integer|string
 */
function _acf_get_stored_values_post_id( $post_id ) {
	$decoded = acf_decode_post_id( $post_id );
	$parent  = 'post' === $decoded['type'] ? wp_is_post_revision( (int) $decoded['id'] ) : false;

	return $parent ? $parent : $post_id;
}

/**
 * The internal ACF function to check whether relational fields should check the post IDs being saved.
 * That's while ACF saves submitted values. Values code saves with update_field() during that save, such
 * as back-references written by bidirectional sync, aren't checked.
 *
 * @since 6.8.11
 *
 * @return boolean
 */
function _acf_is_checking_submitted_post_ids() {
	return (bool) acf_get_data( 'acf_saving_submitted_values' );
}

/**
 * The internal ACF function to return whether a field is, or contains, a Relationship, Post Object or
 * Page Link field, at any depth inside Repeater, Group, Clone and Flexible Content fields.
 *
 * @since 6.8.11
 *
 * @param array $field The field array.
 * @return boolean
 */
function _acf_field_has_relational_fields( $field ) {
	static $cache = array();

	$key = isset( $field['key'] ) ? (string) $field['key'] : '';

	if ( '' !== $key && isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$has_relational = in_array( $field['type'] ?? '', array( 'relationship', 'post_object', 'page_link' ), true );
	$children       = acf_get_array( $field['sub_fields'] ?? array() );

	foreach ( acf_get_array( $field['layouts'] ?? array() ) as $layout ) {
		$children = array_merge( $children, acf_get_array( $layout['sub_fields'] ?? array() ) );
	}

	foreach ( $children as $child ) {
		if ( $has_relational ) {
			break;
		}

		$has_relational = is_array( $child ) && _acf_field_has_relational_fields( $child );
	}

	if ( '' !== $key ) {
		$cache[ $key ] = $has_relational;
	}

	return $has_relational;
}

/**
 * The internal ACF function to return the post IDs stored in an object's relational fields before the
 * current user-submitted save changed them.
 *
 * Repeater and Flexible Content rows are saved one at a time, and can be moved, added or removed, so a
 * selection may be saved to a different row than it was stored in, after that row was already updated.
 * The IDs are recorded, by field key, before the first relational field is updated, and kept until the
 * save finishes. A sub field has the same key in every row, so this covers moved rows at any depth.
 *
 * @since 6.8.11
 *
 * @param integer|string $post_id The ACF post ID being saved.
 * @return array An array with `ids`, the sets of post IDs stored in each relational field, keyed by field
 *               key, and `names`, the meta names that have a field key reference.
 */
function _acf_get_stored_post_ids_snapshot( $post_id ) {
	$post_id   = _acf_get_stored_values_post_id( $post_id );
	$snapshots = (array) acf_get_data( 'acf_stored_post_ids_snapshots' );

	if ( ! isset( $snapshots[ $post_id ] ) ) {
		$meta       = (array) acf_get_meta( $post_id );
		$relational = array();
		$snapshot   = array(
			'ids'   => array(),
			'names' => array(),
		);

		// Each stored value has a reference to the key of the field it belongs to.
		foreach ( $meta as $name => $value ) {
			if ( ! isset( $meta[ '_' . $name ] ) ) {
				continue;
			}

			$field_key                  = $meta[ '_' . $name ];
			$snapshot['names'][ $name ] = true;

			if ( ! isset( $relational[ $field_key ] ) ) {
				$field                    = acf_get_field( $field_key );
				$relational[ $field_key ] = $field && in_array( $field['type'], array( 'relationship', 'post_object', 'page_link' ), true );
			}

			if ( ! $relational[ $field_key ] ) {
				continue;
			}

			foreach ( acf_get_array( acf_maybe_unserialize( $value ) ) as $id ) {
				if ( is_numeric( $id ) ) {
					$snapshot['ids'][ $field_key ][ (string) $id ] = true;
				}
			}
		}

		$snapshots[ $post_id ] = $snapshot;
		acf_set_data( 'acf_stored_post_ids_snapshots', $snapshots );
	}

	return $snapshots[ $post_id ];
}

/**
 * The internal ACF function to record an object's stored post IDs during a user-submitted save, when the
 * first field that is, or contains, a relational field is updated, before any of them are changed. Saves
 * without relational fields don't record anything.
 *
 * @since 6.8.11
 *
 * @param mixed          $check   The short-circuit value, returned unchanged.
 * @param mixed          $value   The value being updated.
 * @param integer|string $post_id The ACF post ID being saved.
 * @param array          $field   The field being updated.
 * @return mixed
 */
function _acf_snapshot_stored_post_ids( $check, $value, $post_id, $field ) {
	if ( ! _acf_is_checking_submitted_post_ids() ) {
		return $check;
	}

	$snapshots = (array) acf_get_data( 'acf_stored_post_ids_snapshots' );

	if ( ! isset( $snapshots[ _acf_get_stored_values_post_id( $post_id ) ] ) && is_array( $field ) && _acf_field_has_relational_fields( $field ) ) {
		_acf_get_stored_post_ids_snapshot( $post_id );
	}

	return $check;
}
add_filter( 'acf/pre_update_value', '_acf_snapshot_stored_post_ids', 1, 4 );

/**
 * The internal ACF function to discard an object's stored post ID snapshot once its save has finished.
 *
 * @since 6.8.11
 *
 * @param integer|string $post_id The ACF post ID that was saved.
 * @return void
 */
function _acf_clear_stored_post_ids_snapshot( $post_id ) {
	$snapshots = (array) acf_get_data( 'acf_stored_post_ids_snapshots' );
	unset( $snapshots[ _acf_get_stored_values_post_id( $post_id ) ] );
	acf_set_data( 'acf_stored_post_ids_snapshots', $snapshots );
}
add_action( 'acf/save_post', '_acf_clear_stored_post_ids_snapshot', 99 );

/**
 * The internal ACF function to discard stored post ID snapshots after each REST request (including each
 * request in a batch), which updates fields without firing `acf/save_post`.
 *
 * REST requests made while a submitted save is still running, such as with `rest_do_request()` from an
 * `acf/update_value` callback, leave the snapshots alone, as that save still needs them.
 *
 * @since 6.8.11
 *
 * @param mixed $response The REST response, returned unchanged.
 * @return mixed
 */
function _acf_clear_rest_stored_post_ids_snapshots( $response ) {
	if ( _acf_is_checking_submitted_post_ids() ) {
		return $response;
	}

	acf_set_data( 'acf_stored_post_ids_snapshots', array() );

	return $response;
}
add_filter( 'rest_request_after_callbacks', '_acf_clear_rest_stored_post_ids_snapshots' );

/**
 * Removes newly selected post IDs that the current user isn't allowed to add to a relational field.
 *
 * Only runs while ACF is saving submitted values. A new ID must belong to an existing post that the
 * current user can read. IDs already stored in the field, in any Repeater or Flexible Content row, are
 * kept, so a selection the user can't read isn't lost when they save.
 *
 * Stored values are matched by field key. A value stored under a different key, such as by a field that
 * was later recreated or imported with a new key, isn't matched, so a user who can't read its posts drops
 * them when they save it.
 *
 * @since 6.8.11
 *
 * @param mixed          $value   The normalized field value: an ID, or an array of IDs.
 * @param integer|string $post_id The ACF post ID being saved.
 * @param array          $field   The field array.
 * @return mixed The value with disallowed IDs removed.
 */
function acf_remove_disallowed_post_ids( $value, $post_id, $field ) {
	if ( empty( $value ) || ! _acf_is_checking_submitted_post_ids() ) {
		return $value;
	}

	$snapshot   = _acf_get_stored_post_ids_snapshot( $post_id );
	$stored_ids = isset( $snapshot['ids'][ $field['key'] ] ) ? $snapshot['ids'][ $field['key'] ] : array();

	// A seamless Clone saved by a form references the cloned field's key, but the REST API saves it under the
	// Clone's prefixed key.
	if ( ! empty( $field['__key'] ) && isset( $snapshot['ids'][ $field['__key'] ] ) ) {
		$stored_ids = $stored_ids + $snapshot['ids'][ $field['__key'] ];
	}

	// Values saved without a field key reference, such as by an import, can only be found by name. This reads
	// the stored value directly, before this field is updated, so `acf/load_value` filters can't add to it.
	if ( ! isset( $snapshot['names'][ $field['name'] ] ) ) {
		$legacy_ids = acf_get_array( acf_get_metadata_by_field( _acf_get_stored_values_post_id( $post_id ), $field ) );
		$stored_ids = $stored_ids + array_fill_keys( array_map( 'strval', array_filter( $legacy_ids, 'is_numeric' ) ), true );
	}

	$is_allowed = function ( $id ) use ( $stored_ids, $field ) {
		// Page Link URLs aren't post IDs, so they're kept. Other fields only store post IDs, and other code,
		// such as bidirectional sync, decodes values like `post_42` or `user_3` as objects to update.
		if ( ! is_numeric( $id ) ) {
			return 'page_link' === $field['type'];
		}

		// Other code, such as bidirectional sync, converts negative IDs to the positive post ID.
		if ( (int) $id <= 0 ) {
			return false;
		}

		// Only check new post IDs. Existing selections are kept.
		if ( isset( $stored_ids[ (string) $id ] ) ) {
			return true;
		}

		return acf_current_user_can_read_post( (int) $id, $field );
	};

	if ( ! is_array( $value ) ) {
		return $is_allowed( $value ) ? $value : '';
	}

	$value = array_filter( $value, $is_allowed );

	return acf_is_sequential_array( $value ) ? array_values( $value ) : $value;
}

/**
 * Removes posts that can't be read from a relational field's value before it's formatted.
 *
 * Applies to values formatted while rendering an ACF Block by default. Block data is stored in the post
 * content, which anyone who can edit the post controls, and the block editor renders previews from data
 * that hasn't been saved yet, so selections in it are never checked when they're saved.
 *
 * Previews are checked against the current user. Other blocks are checked against the author of the post
 * or synced pattern they're saved in, so posts that author picked show to every visitor but posts they
 * couldn't have picked don't. Blocks that aren't saved in either are checked against the current user when
 * rendered in the admin, by AJAX or by a REST API endpoint, and aren't checked on the front end, where
 * they're in places like template parts and widgets that only administrators can edit. See
 * `_acf_get_block_reader()`.
 *
 * The `acf/fields/relational/hide_unreadable_posts` filter can apply this to other values too, or turn it
 * off.
 *
 * @since 6.8.11
 *
 * @param mixed          $value   The value: an ID, or an array of IDs (and Page Link URLs).
 * @param integer|string $post_id The ACF post ID the value is formatted for.
 * @param array          $field   The field array.
 * @return mixed The value without posts that can't be read.
 */
function acf_remove_unreadable_formatted_post_ids( $value, $post_id, $field ) {
	if ( empty( $value ) ) {
		return $value;
	}

	$user = wp_get_current_user();
	$hide = false;

	if ( is_string( $post_id ) && 0 === strpos( $post_id, 'block_' ) ) {
		// Set while an ACF Block renders: false when it isn't checked, otherwise the user to check.
		$reader = acf_get_data( 'acf_block_reader' );
		$hide   = false !== $reader;
		$user   = $reader instanceof WP_User ? $reader : $user;
	}

	/**
	 * Filters whether a relational field's formatted value leaves out posts that can't be read.
	 *
	 * True by default for values formatted while rendering an ACF Block, such as by get_field() in a block
	 * template, except for blocks on the front end that aren't saved in a post or synced pattern. Return
	 * true to also leave them out elsewhere, such as from get_field() in theme templates, or false to show
	 * them in blocks again. Values other than those of blocks are checked against the current user.
	 * Visitors who aren't logged in can only read published posts and posts with a public status.
	 *
	 * @since 6.8.11
	 *
	 * @param boolean        $hide    Whether to leave out posts that can't be read.
	 * @param integer|string $post_id The ACF post ID the value is formatted for.
	 * @param array          $field   The field array.
	 */
	if ( ! apply_filters( 'acf/fields/relational/hide_unreadable_posts', $hide, $post_id, $field ) ) {
		return $value;
	}

	$is_readable = function ( $id ) use ( $user, $field ) {
		// Page Link URLs aren't post IDs, so they're kept. Other fields convert values like `123x` to post IDs.
		if ( ! is_numeric( $id ) ) {
			return 'page_link' === $field['type'];
		}

		return acf_user_can_read_post( $user, (int) $id, $field );
	};

	if ( ! is_array( $value ) ) {
		return $is_readable( $value ) ? $value : '';
	}

	_prime_post_caches( array_map( 'intval', array_filter( $value, 'is_numeric' ) ), false, false );

	$value = array_filter( $value, $is_readable );

	return acf_is_sequential_array( $value ) ? array_values( $value ) : $value;
}

function acf_order_by_search( $array, $search ) {

	// vars
	$weights = array();
	$needle  = strtolower( $search );

	// add key prefix
	foreach ( array_keys( $array ) as $k ) {
		$array[ '_' . $k ] = acf_extract_var( $array, $k );
	}

	// add search weight
	foreach ( $array as $k => $v ) {

		// vars
		$weight   = 0;
		$haystack = strtolower( $v );
		$strpos   = strpos( $haystack, $needle );

		// detect search match
		if ( $strpos !== false ) {

			// set eright to length of match
			$weight = strlen( $search );

			// increase weight if match starts at begining of string
			if ( $strpos == 0 ) {
				++$weight;
			}
		}

		// append to wights
		$weights[ $k ] = $weight;
	}

	// sort the array with menu_order ascending
	array_multisort( $weights, SORT_DESC, $array );

	// remove key prefix
	foreach ( array_keys( $array ) as $k ) {
		$array[ substr( $k, 1 ) ] = acf_extract_var( $array, $k );
	}

	// return
	return $array;
}

/**
 * acf_get_pretty_user_roles
 *
 * description
 *
 * @since   5.3.2
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_get_pretty_user_roles( $allowed = false ) {

	// vars
	$editable_roles = get_editable_roles();
	$allowed        = acf_get_array( $allowed );
	$roles          = array();

	// loop
	foreach ( $editable_roles as $role_name => $role_details ) {

		// bail early if not allowed
		if ( ! empty( $allowed ) && ! in_array( $role_name, $allowed ) ) {
			continue;
		}

		// append
		$roles[ $role_name ] = translate_user_role( $role_details['name'] );
	}

	// return
	return $roles;
}

/**
 * acf_get_grouped_users
 *
 * This function will return all users grouped by role
 * This is handy for select settings
 *
 * @since   5.0.0
 *
 * @param   $args (array)
 * @return  (array)
 */
function acf_get_grouped_users( $args = array() ) {

	// vars
	$r = array();

	// defaults
	$args = wp_parse_args(
		$args,
		array(
			'users_per_page' => -1,
			'paged'          => 0,
			'role'           => '',
			'orderby'        => 'login',
			'order'          => 'ASC',
		)
	);

	// offset
	$i              = 0;
	$min            = 0;
	$max            = 0;
	$users_per_page = acf_extract_var( $args, 'users_per_page' );
	$paged          = acf_extract_var( $args, 'paged' );

	if ( $users_per_page > 0 ) {

		// prevent paged from being -1
		$paged = max( 0, $paged );

		// set min / max
		$min = ( ( $paged - 1 ) * $users_per_page ) + 1; // 1,  11
		$max = ( $paged * $users_per_page ); // 10, 20

	}

	// find array of post_type
	$user_roles = acf_get_pretty_user_roles( $args['role'] );

	// fix role
	if ( is_array( $args['role'] ) ) {

		// global
		global $wp_version, $wpdb;

		// vars
		$roles = acf_extract_var( $args, 'role' );

		// new WP has role__in
		if ( version_compare( $wp_version, '4.4', '>=' ) ) {
			$args['role__in'] = $roles;

			// old WP doesn't have role__in
		} else {

			// vars
			$blog_id    = get_current_blog_id();
			$meta_query = array( 'relation' => 'OR' );

			// loop
			foreach ( $roles as $role ) {
				$meta_query[] = array(
					'key'     => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
					'value'   => '"' . $role . '"',
					'compare' => 'LIKE',
				);
			}

			// append
			$args['meta_query'] = $meta_query;
		}
	}

	// get posts
	$users = get_users( $args );

	// loop
	foreach ( $user_roles as $user_role_name => $user_role_label ) {

		// vars
		$this_users = array();
		$this_group = array();

		// populate $this_posts
		foreach ( array_keys( $users ) as $key ) {

			// bail early if not correct role
			if ( ! in_array( $user_role_name, $users[ $key ]->roles ) ) {
				continue;
			}

			// extract user
			$user = acf_extract_var( $users, $key );

			// increase
			++$i;

			// bail early if too low
			if ( $min && $i < $min ) {
				continue;
			}

			// bail early if too high (don't bother looking at any more users)
			if ( $max && $i > $max ) {
				break;
			}

			// group by post type
			$this_users[ $user->ID ] = $user;
		}

		// bail early if no posts for this post type
		if ( empty( $this_users ) ) {
			continue;
		}

		// append
		$r[ $user_role_label ] = $this_users;
	}

	// return
	return $r;
}

/**
 * acf_json_encode
 *
 * Returns json_encode() ready for file / database use.
 *
 * @since   5.0.0
 *
 * @param   array $json The array of data to encode.
 * @return  string
 */
function acf_json_encode( $json ) {
	return json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
}

/**
 * acf_str_exists
 *
 * This function will return true if a sub string is found
 *
 * @since   5.0.0
 *
 * @param   $needle (string)
 * @param   $haystack (string)
 * @return  (boolean)
 */
function acf_str_exists( $needle, $haystack ) {

	// return true if $haystack contains the $needle
	if ( is_string( $haystack ) && strpos( $haystack, $needle ) !== false ) {
		return true;
	}

	// return
	return false;
}

/**
 * A legacy function designed for developer debugging.
 *
 * @deprecated 6.2.6 Removed for security, but keeping the definition in case third party devs have it in their code.
 * @since 5.0.0
 *
 * @return false
 */
function acf_debug() {
	_deprecated_function( __FUNCTION__, '6.2.7' );
	return false;
}

/**
 * A legacy function designed for developer debugging.
 *
 * @deprecated 6.2.6 Removed for security, but keeping the definition in case third party devs have it in their code.
 * @since 5.0.0
 *
 * @return false
 */
function acf_debug_start() {
	_deprecated_function( __FUNCTION__, '6.2.7' );
	return false;
}

/**
 * A legacy function designed for developer debugging.
 *
 * @deprecated 6.2.6 Removed for security, but keeping the definition in case third party devs have it in their code.
 * @since 5.0.0
 *
 * @return false
 */
function acf_debug_end() {
	_deprecated_function( __FUNCTION__, '6.2.7' );
	return false;
}

/**
 * acf_encode_choices
 *
 * description
 *
 * @since   5.0.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_encode_choices( $array = array(), $show_keys = true ) {

	// bail early if not array (maybe a single string)
	if ( ! is_array( $array ) ) {
		return $array;
	}

	// bail early if empty array
	if ( empty( $array ) ) {
		return '';
	}

	// vars
	$string = '';

	// if allowed to show keys (good for choices, not for default values)
	if ( $show_keys ) {

		// loop
		foreach ( $array as $k => $v ) {

			// ignore if key and value are the same
			if ( strval( $k ) == strval( $v ) ) {
				continue;
			}

			// show key in the value
			$array[ $k ] = $k . ' : ' . $v;
		}
	}

	// implode
	$string = implode( "\n", $array );

	// return
	return $string;
}

function acf_decode_choices( $string = '', $array_keys = false ) {

	// bail early if already array
	if ( is_array( $string ) ) {
		return $string;

		// allow numeric values (same as string)
	} elseif ( is_numeric( $string ) ) {

		// do nothing
		// bail early if not a string
	} elseif ( ! is_string( $string ) ) {
		return array();

		// bail early if is empty string
	} elseif ( $string === '' ) {
		return array();
	}

	// vars
	$array = array();

	// explode
	$lines = explode( "\n", $string );

	// key => value
	foreach ( $lines as $line ) {

		// vars
		$k = trim( $line );
		$v = trim( $line );

		// look for ' : '
		if ( acf_str_exists( ' : ', $line ) ) {
			$line = explode( ' : ', $line );

			$k = trim( $line[0] );
			$v = trim( $line[1] );
		}

		// append
		$array[ $k ] = $v;
	}

	// return only array keys? (good for checkbox default_value)
	if ( $array_keys ) {
		return array_keys( $array );
	}

	// return
	return $array;
}

/**
 * acf_str_replace
 *
 * This function will replace an array of strings much like str_replace
 * The difference is the extra logic to avoid replacing a string that has alread been replaced
 * This is very useful for replacing date characters as they overlap with eachother
 *
 * @since   5.3.8
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_str_replace( $string = '', $search_replace = array() ) {

	// vars
	$ignore = array();

	// remove potential empty search to avoid PHP error
	unset( $search_replace[''] );

	// loop over conversions
	foreach ( $search_replace as $search => $replace ) {

		// ignore this search, it was a previous replace
		if ( in_array( $search, $ignore ) ) {
			continue;
		}

		// bail early if subsctring not found
		if ( strpos( $string, $search ) === false ) {
			continue;
		}

		// replace
		$string = str_replace( $search, $replace, $string );

		// append to ignore
		$ignore[] = $replace;
	}

	// return
	return $string;
}

/**
* date & time formats
 *
 * These settings contain an association of format strings from PHP => JS
 *
 * @since   5.3.8
 *
 * @param   n/a
 * @return  n/a
 */

acf_update_setting(
	'php_to_js_date_formats',
	array(

		// Year
		'Y' => 'yy',    // Numeric, 4 digits                                1999, 2003
		'y' => 'y',     // Numeric, 2 digits                                99, 03


	// Month
		'm' => 'mm',    // Numeric, with leading zeros                      01–12
		'n' => 'm',     // Numeric, without leading zeros                   1–12
		'F' => 'MM',    // Textual full                                     January – December
		'M' => 'M',     // Textual three letters                            Jan - Dec


	// Weekday
		'l' => 'DD',    // Full name  (lowercase 'L')                       Sunday – Saturday
		'D' => 'D',     // Three letter name                                Mon – Sun


	// Day of Month
		'd' => 'dd',    // Numeric, with leading zeros                      01–31
		'j' => 'd',     // Numeric, without leading zeros                   1–31
		'S' => '',      // The English suffix for the day of the month      st, nd or th in the 1st, 2nd or 15th.

	)
);

acf_update_setting(
	'php_to_js_time_formats',
	array(

		'a' => 'tt',    // Lowercase Ante meridiem and Post meridiem        am or pm
		'A' => 'TT',    // Uppercase Ante meridiem and Post meridiem        AM or PM
		'h' => 'hh',    // 12-hour format of an hour with leading zeros     01 through 12
		'g' => 'h',     // 12-hour format of an hour without leading zeros  1 through 12
		'H' => 'HH',    // 24-hour format of an hour with leading zeros     00 through 23
		'G' => 'H',     // 24-hour format of an hour without leading zeros  0 through 23
		'i' => 'mm',    // Minutes with leading zeros                       00 to 59
		's' => 'ss',    // Seconds, with leading zeros                      00 through 59

	)
);


/**
 * acf_split_date_time
 *
 * This function will split a format string into seperate date and time
 *
 * @since   5.3.8
 *
 * @param   $date_time (string)
 * @return  $formats (array)
 */
function acf_split_date_time( $date_time = '' ) {

	// vars
	$php_date = acf_get_setting( 'php_to_js_date_formats' );
	$php_time = acf_get_setting( 'php_to_js_time_formats' );
	$chars    = str_split( $date_time );
	$type     = 'date';

	// default
	$data = array(
		'date' => '',
		'time' => '',
	);

	// loop
	foreach ( $chars as $i => $c ) {

		// find type
		// - allow misc characters to append to previous type
		if ( isset( $php_date[ $c ] ) ) {
			$type = 'date';
		} elseif ( isset( $php_time[ $c ] ) ) {
			$type = 'time';
		}

		// append char
		$data[ $type ] .= $c;
	}

	// trim
	$data['date'] = trim( $data['date'] );
	$data['time'] = trim( $data['time'] );

	// return
	return $data;
}

/**
 * acf_convert_date_to_php
 *
 * This fucntion converts a date format string from JS to PHP
 *
 * @since   5.0.0
 *
 * @param   $date (string)
 * @return  (string)
 */
function acf_convert_date_to_php( $date = '' ) {

	// vars
	$php_to_js = acf_get_setting( 'php_to_js_date_formats' );
	$js_to_php = array_flip( $php_to_js );

	// return
	return acf_str_replace( $date, $js_to_php );
}

/**
 * acf_convert_date_to_js
 *
 * This fucntion converts a date format string from PHP to JS
 *
 * @since   5.0.0
 *
 * @param   $date (string)
 * @return  (string)
 */
function acf_convert_date_to_js( $date = '' ) {

	// vars
	$php_to_js = acf_get_setting( 'php_to_js_date_formats' );

	// return
	return acf_str_replace( $date, $php_to_js );
}

/**
 * acf_convert_time_to_php
 *
 * This fucntion converts a time format string from JS to PHP
 *
 * @since   5.0.0
 *
 * @param   $time (string)
 * @return  (string)
 */
function acf_convert_time_to_php( $time = '' ) {

	// vars
	$php_to_js = acf_get_setting( 'php_to_js_time_formats' );
	$js_to_php = array_flip( $php_to_js );

	// return
	return acf_str_replace( $time, $js_to_php );
}

/**
 * acf_convert_time_to_js
 *
 * This fucntion converts a date format string from PHP to JS
 *
 * @since   5.0.0
 *
 * @param   $time (string)
 * @return  (string)
 */
function acf_convert_time_to_js( $time = '' ) {

	// vars
	$php_to_js = acf_get_setting( 'php_to_js_time_formats' );

	// return
	return acf_str_replace( $time, $php_to_js );
}

/**
 * acf_update_user_setting
 *
 * description
 *
 * @since   5.0.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_update_user_setting( $name, $value ) {

	// get current user id
	$user_id = get_current_user_id();

	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', true );

	// ensure array
	$settings = acf_get_array( $settings );

	// delete setting (allow 0 to save)
	if ( acf_is_empty( $value ) ) {
		unset( $settings[ $name ] );

		// append setting
	} else {
		$settings[ $name ] = $value;
	}

	// update user data
	return update_metadata( 'user', $user_id, 'acf_user_settings', $settings );
}

/**
 * acf_get_user_setting
 *
 * description
 *
 * @since   5.0.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_get_user_setting( $name = '', $default = false ) {

	// get current user id
	$user_id = get_current_user_id();

	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', true );

	// ensure array
	$settings = acf_get_array( $settings );

	// bail arly if no settings
	if ( ! isset( $settings[ $name ] ) ) {
		return $default;
	}

	// return
	return $settings[ $name ];
}

/**
 * acf_in_array
 *
 * description
 *
 * @since   5.0.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_in_array( $value = '', $array = false ) {

	// bail early if not array
	if ( ! is_array( $array ) ) {
		return false;
	}

	// find value in array
	return in_array( $value, $array );
}

/**
 * acf_get_valid_post_id
 *
 * This function will return a valid post_id based on the current screen / parameter
 *
 * @since   5.0.0
 *
 * @param   $post_id (mixed)
 * @return  $post_id (mixed)
 */
function acf_get_valid_post_id( $post_id = 0 ) {

	// allow filter to short-circuit load_value logic
	$preload = apply_filters( 'acf/pre_load_post_id', null, $post_id );
	if ( $preload !== null ) {
		return $preload;
	}

	// vars
	$_post_id = $post_id;

	// if not $post_id, load queried object
	if ( ! $post_id ) {

		// try for global post (needed for setup_postdata)
		$post_id = (int) get_the_ID();

		// try for current screen
		if ( ! $post_id ) {
			$post_id = get_queried_object();
		}
	}

	// $post_id may be an object.
	// todo: Compare class types instead.
	if ( is_object( $post_id ) ) {

		// post
		if ( isset( $post_id->post_type, $post_id->ID ) ) {
			$post_id = $post_id->ID;

			// user
		} elseif ( isset( $post_id->roles, $post_id->ID ) ) {
			$post_id = 'user_' . $post_id->ID;

			// term
		} elseif ( isset( $post_id->taxonomy, $post_id->term_id ) ) {
			$post_id = 'term_' . $post_id->term_id;

			// comment
		} elseif ( isset( $post_id->comment_ID ) ) {
			$post_id = 'comment_' . $post_id->comment_ID;

			// default
		} else {
			$post_id = 0;
		}
	}

	// allow for option == options
	if ( $post_id === 'option' ) {
		$post_id = 'options';
	}

	// append language code
	if ( $post_id == 'options' ) {
		$dl = acf_get_setting( 'default_language' );
		$cl = acf_get_setting( 'current_language' );

		if ( $cl && $cl !== $dl ) {
			$post_id .= '_' . $cl;
		}
	}

	// filter for 3rd party
	$post_id = apply_filters( 'acf/validate_post_id', $post_id, $_post_id );

	// return
	return $post_id;
}

/**
 * Checks whether field values stored on an ACF post ID belong to a password-protected
 * post whose password has not been entered yet.
 *
 * @since 6.8.11
 *
 * @param integer|string $post_id ACF encoded post ID.
 * @return boolean
 */
function acf_post_password_required( $post_id ) {
	$decoded = acf_decode_post_id( $post_id );

	return 'post' === $decoded['type'] && (int) $decoded['id'] > 0 && post_password_required( (int) $decoded['id'] );
}



/**
 * acf_get_post_id_info
 *
 * This function will return the type and id for a given $post_id string
 *
 * @since   5.4.0
 *
 * @param   $post_id (mixed)
 * @return  $info (array)
 */
function acf_get_post_id_info( $post_id = 0 ) {

	// vars
	$info = array(
		'type' => 'post',
		'id'   => 0,
	);

	// bail early if no $post_id
	if ( ! $post_id ) {
		return $info;
	}

	// check cache
	// - this function will most likely be called multiple times (saving loading fields from post)
	// $cache_key = "get_post_id_info/post_id={$post_id}";
	// if( acf_isset_cache($cache_key) ) return acf_get_cache($cache_key);
	// numeric
	if ( is_numeric( $post_id ) ) {
		$info['id'] = (int) $post_id;

		// string
	} elseif ( is_string( $post_id ) ) {

		// vars
		$glue = '_';
		$type = explode( $glue, $post_id );
		$id   = array_pop( $type );
		$type = implode( $glue, $type );
		$meta = array( 'post', 'user', 'comment', 'term' );

		// check if is taxonomy (ACF < 5.5)
		// - avoid scenario where taxonomy exists with name of meta type
		if ( ! in_array( $type, $meta ) && acf_isset_termmeta( $type ) ) {
			$type = 'term';
		}

		// meta
		if ( is_numeric( $id ) && in_array( $type, $meta ) ) {
			$info['type'] = $type;
			$info['id']   = (int) $id;

			// option
		} else {
			$info['type'] = 'option';
			$info['id']   = $post_id;
		}
	}

	// update cache
	// acf_set_cache($cache_key, $info);
	// filter
	$info = apply_filters( 'acf/get_post_id_info', $info, $post_id );

	// return
	return $info;
}

/**
 * acf_isset_termmeta
 *
 * This function will return true if the termmeta table exists
 * https://developer.wordpress.org/reference/functions/get_term_meta/
 *
 * @since   5.4.0
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_isset_termmeta( $taxonomy = '' ) {

	// bail early if no table
	if ( get_option( 'db_version' ) < 34370 ) {
		return false;
	}

	// check taxonomy
	if ( $taxonomy && ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	// return
	return true;
}

/**
 * This function will walk through the $_FILES data and upload each found.
 *
 * @since   5.0.9
 * @since   6.8.11 Uploader nonces are verified against the render IDs set by acf_set_upload_render_ids() for
 *                 this save, or the post ID being saved. See _acf_verify_file_uploader_nonce().
 *
 * @param array $ancestors An internal parameter, not required.
 */
function acf_upload_files( $ancestors = array() ) {

	if ( empty( $_FILES['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Each file's uploader nonce is verified below.
		return;
	}

	$file = acf_sanitize_files_array( $_FILES['acf'] ); // phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified upstream.

	// walk through ancestors.
	if ( ! empty( $ancestors ) ) {
		foreach ( $ancestors as $a ) {
			foreach ( array_keys( $file ) as $k ) {
				$file[ $k ] = $file[ $k ][ $a ];
			}
		}
	}

	// is array?
	if ( is_array( $file['name'] ) ) {
		foreach ( array_keys( $file['name'] ) as $k ) {
			$_ancestors = array_merge( $ancestors, array( $k ) );

			acf_upload_files( $_ancestors );
		}

		return;
	}

	// Bail early if file has error (no file uploaded).
	if ( $file['error'] ) {
		return;
	}

	$field_key = end( $ancestors );

	if ( ! _acf_verify_file_uploader_nonce( $field_key ) ) {
		return;
	}

	// Assign global _acfuploader for media validation.
	$_POST['_acfuploader'] = $field_key;

	// file found!
	$attachment_id = acf_upload_file( $file );

	// update $_POST
	array_unshift( $ancestors, 'acf' );
	acf_update_nested_array( $_POST, $ancestors, $attachment_id );
}

/**
 * Returns true if the basic uploader nonce submitted for a field verifies for the object being saved.
 *
 * The nonce is verified against the render IDs set by acf_set_upload_render_ids() for this save, or
 * the post ID being saved. Saves that never set render IDs, such as other plugins' form handlers,
 * also accept the render IDs of the field lists in the request. The nonce itself is signed for the
 * current user and render, so this only accepts nonces the user was served.
 *
 * @since 6.8.11
 * @access private
 *
 * @param string $field_key The field key.
 * @return boolean
 */
function _acf_verify_file_uploader_nonce( $field_key ) {
	$nonce_name = $field_key . '_file_nonce';

	if ( empty( $_REQUEST['acf'][ $nonce_name ] ) || ! is_string( $_REQUEST['acf'][ $nonce_name ] ) ) {
		return false;
	}

	$nonce = sanitize_text_field( wp_unslash( $_REQUEST['acf'][ $nonce_name ] ) );

	$upload_render_ids = acf_get_form_data( 'upload_render_ids' );
	$render_ids        = is_array( $upload_render_ids ) && $upload_render_ids ? $upload_render_ids : array( (string) acf_get_form_data( 'post_id' ) );

	foreach ( $render_ids as $render_id ) {
		if ( '' !== (string) $render_id && wp_verify_nonce( $nonce, acf_get_file_uploader_nonce_action( (string) $render_id, $field_key ) ) ) {
			return true;
		}
	}

	if ( null !== $upload_render_ids || ! isset( $_POST['_acf_fields'] ) || ! is_array( $_POST['_acf_fields'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Only the keys are read, as render IDs to verify the uploader nonce against.
		return false;
	}

	foreach ( array_map( 'strval', array_keys( $_POST['_acf_fields'] ) ) as $render_id ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only the keys are read.
		if ( '' !== $render_id && wp_verify_nonce( $nonce, acf_get_file_uploader_nonce_action( $render_id, $field_key ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * acf_upload_file
 *
 * This function will uploade a $_FILE
 *
 * @since   5.0.9
 *
 * @param   $uploaded_file (array) array found from $_FILE data
 * @return  $id (int) new attachment ID
 */
function acf_upload_file( $uploaded_file ) {

	// required
	// require_once( ABSPATH . "/wp-load.php" ); // WP should already be loaded
	require_once ABSPATH . '/wp-admin/includes/media.php'; // video functions
	require_once ABSPATH . '/wp-admin/includes/file.php';
	require_once ABSPATH . '/wp-admin/includes/image.php';

	// required for wp_handle_upload() to upload the file
	$upload_overrides = array( 'test_form' => false );

	// Restrict uploads to image MIME types for image and gallery fields.
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Field key is used for validation context only.
	if ( ! empty( $_POST['_acfuploader'] ) ) {
		$field = acf_get_field( sanitize_text_field( wp_unslash( $_POST['_acfuploader'] ) ) );
		if ( acf_is_image_field( $field ) ) {
			$upload_overrides['mimes'] = acf_get_image_mime_types( $field );
		}
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	// upload
	$file = wp_handle_upload( $uploaded_file, $upload_overrides );

	// bail early if upload failed
	if ( isset( $file['error'] ) ) {
		return $file['error'];
	}

	// vars
	$url      = $file['url'];
	$type     = $file['type'];
	$file     = $file['file'];
	$filename = basename( $file );

	// Reject any PDF without a %PDF- header.
	if ( 'application/pdf' === $type ) {
		$head = file_get_contents( $file, false, null, 0, 1024 );
		if ( false === $head || 0 !== strpos( ltrim( $head, "\x00..\x20" ), '%PDF-' ) ) {
			wp_delete_file( $file );
			return __( 'Sorry, this file could not be uploaded.', 'acf' );
		}
	}

	// Construct the object array
	$object = array(
		'post_title'     => $filename,
		'post_mime_type' => $type,
		'guid'           => $url,
	);

	// Save the data
	$id = wp_insert_attachment( $object, $file );

	/**
	 * Filters whether a file uploaded through a basic uploader gets preview images.
	 *
	 * Applies to File, Image and Gallery fields using the basic uploader, such as in
	 * front-end forms. When false, WordPress won't render PDF previews or save cover
	 * art embedded in audio and video files while generating the attachment metadata.
	 *
	 * @since 6.8.11
	 *
	 * @param boolean $generate_previews Whether to generate preview images. Default true only for users who can upload files.
	 * @param string  $file              Path to the uploaded file.
	 * @param integer $id                Attachment ID.
	 */
	$skip_previews = ! apply_filters( 'acf/upload/generate_previews', current_user_can( 'upload_files' ), $file, $id );

	if ( $skip_previews ) {
		// Dedicated callbacks, so removing them can't also remove a matching filter added by a site.
		$no_preview_sizes = static function () {
			return array();
		};
		$no_cover_art     = static function ( $metadata ) {
			if ( is_array( $metadata ) ) {
				unset( $metadata['image'] );
			}
			return $metadata;
		};

		add_filter( 'fallback_intermediate_image_sizes', $no_preview_sizes, PHP_INT_MAX );
		add_filter( 'wp_read_audio_metadata', $no_cover_art, PHP_INT_MAX );
		add_filter( 'wp_read_video_metadata', $no_cover_art, PHP_INT_MAX );
	}

	try {
		// Add the meta-data
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	} finally {
		// Always detach the temporary filters so they can't affect anything else in this request.
		if ( $skip_previews ) {
			remove_filter( 'fallback_intermediate_image_sizes', $no_preview_sizes, PHP_INT_MAX );
			remove_filter( 'wp_read_audio_metadata', $no_cover_art, PHP_INT_MAX );
			remove_filter( 'wp_read_video_metadata', $no_cover_art, PHP_INT_MAX );
		}
	}

	/** This action is documented in wp-admin/custom-header.php */
	do_action( 'wp_create_file_in_uploads', $file, $id ); // For replication

	// return new ID
	return $id;
}

/**
 * acf_update_nested_array
 *
 * This function will update a nested array value. Useful for modifying the $_POST array
 *
 * @since   5.0.9
 *
 * @param   $array (array) target array to be updated
 * @param   $ancestors (array) array of keys to navigate through to find the child
 * @param   $value (mixed) The new value
 * @return  (boolean)
 */
function acf_update_nested_array( &$array, $ancestors, $value ) {

	// if no more ancestors, update the current var
	if ( empty( $ancestors ) ) {
		$array = $value;

		// return
		return true;
	}

	// shift the next ancestor from the array
	$k = array_shift( $ancestors );

	// if exists
	if ( isset( $array[ $k ] ) ) {
		return acf_update_nested_array( $array[ $k ], $ancestors, $value );
	}

	// return
	return false;
}

/**
 * acf_is_screen
 *
 * This function will return true if all args are matched for the current screen
 *
 * @since   5.1.5
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_is_screen( $id = '' ) {

	// bail early if not defined
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	// vars
	$current_screen = get_current_screen();

	// no screen
	if ( ! $current_screen ) {
		return false;

		// array
	} elseif ( is_array( $id ) ) {
		return in_array( $current_screen->id, $id );

		// string
	} else {
		return ( $id === $current_screen->id );
	}
}

/**
 * Check if we're in an ACF admin screen
 *
 * @since  6.2.2
 *
 * @return boolean Returns true if the current screen is an ACF admin screen.
 */
function acf_is_acf_admin_screen() {
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return false;
	}
	$screen = get_current_screen();
	if ( $screen && ! empty( $screen->post_type ) && substr( $screen->post_type, 0, 4 ) === 'acf-' ) {
		return true;
	}

	return false;
}

/**
 * acf_maybe_get
 *
 * This function will return a var if it exists in an array
 *
 * @since   5.1.5
 *
 * @param   $array (array) the array to look within
 * @param   $key (key) the array key to look for. Nested values may be found using '/'
 * @param   $default (mixed) the value returned if not found
 * @return  $post_id (int)
 */
function acf_maybe_get( $array = array(), $key = 0, $default = null ) {

	return isset( $array[ $key ] ) ? $array[ $key ] : $default;
}

function acf_maybe_get_POST( $key = '', $default = null ) {

	return isset( $_POST[ $key ] ) ? acf_sanitize_request_args( $_POST[ $key ] ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- Checked elsewhere.
}

function acf_maybe_get_GET( $key = '', $default = null ) {

	return isset( $_GET[ $key ] ) ? acf_sanitize_request_args( $_GET[ $key ] ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checked elsewhere.
}

/**
 * Returns an array of attachment data.
 *
 * @since   5.1.5
 *
 * @param   integer|WP_Post The attachment ID or object
 * @return  array|false
 */
function acf_get_attachment( $attachment ) {

	// Allow filter to short-circuit load attachment logic.
	// Alternatively, this filter may be used to switch blogs for multisite media functionality.
	$response = apply_filters( 'acf/pre_load_attachment', null, $attachment );
	if ( $response !== null ) {
		return $response;
	}

	// Get the attachment post object.
	$attachment = get_post( $attachment );
	if ( ! $attachment ) {
		return false;
	}
	if ( $attachment->post_type !== 'attachment' ) {
		return false;
	}

	// Load various attachment details.
	$meta          = wp_get_attachment_metadata( $attachment->ID );
	$attached_file = get_attached_file( $attachment->ID );
	if ( strpos( $attachment->post_mime_type, '/' ) !== false ) {
		list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	} else {
		list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
	}

	// Generate response.
	$response = array(
		'ID'          => $attachment->ID,
		'id'          => $attachment->ID,
		'title'       => $attachment->post_title,
		'filename'    => wp_basename( $attached_file ),
		'filesize'    => 0,
		'url'         => wp_get_attachment_url( $attachment->ID ),
		'link'        => get_attachment_link( $attachment->ID ),
		'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'author'      => $attachment->post_author,
		'description' => $attachment->post_content,
		'caption'     => $attachment->post_excerpt,
		'name'        => $attachment->post_name,
		'status'      => $attachment->post_status,
		'uploaded_to' => $attachment->post_parent,
		'date'        => $attachment->post_date_gmt,
		'modified'    => $attachment->post_modified_gmt,
		'menu_order'  => $attachment->menu_order,
		'mime_type'   => $attachment->post_mime_type,
		'type'        => $type,
		'subtype'     => $subtype,
		'icon'        => wp_mime_type_icon( $attachment->ID ),
	);

	// Append filesize data.
	if ( isset( $meta['filesize'] ) ) {
		$response['filesize'] = $meta['filesize'];
	} else {
		/**
		 * Allows shortcutting our ACF's `filesize` call to prevent us making filesystem calls.
		 * Mostly useful for third party plugins which may offload media to other services, and filesize calls will induce a remote download.
		 *
		 * @since 6.2.2
		 *
		 * @param int|null The default filesize.
		 * @param WP_Post $attachment The attachment post object we're looking for the filesize for.
		 */
		$shortcut_filesize = apply_filters( 'acf/filesize', null, $attachment );
		if ( $shortcut_filesize ) {
			$response['filesize'] = intval( $shortcut_filesize );
		} elseif ( file_exists( $attached_file ) ) {
			$response['filesize'] = filesize( $attached_file );
		}
	}

	// Restrict the loading of image "sizes".
	$sizes_id = 0;

	// Type specific logic.
	switch ( $type ) {
		case 'image':
			$sizes_id = $attachment->ID;
			$src      = wp_get_attachment_image_src( $attachment->ID, 'full' );
			if ( $src ) {
				$response['url']    = $src[0];
				$response['width']  = $src[1];
				$response['height'] = $src[2];
			}
			break;
		case 'video':
			$response['width']  = acf_maybe_get( $meta, 'width', 0 );
			$response['height'] = acf_maybe_get( $meta, 'height', 0 );
			if ( $featured_id = get_post_thumbnail_id( $attachment->ID ) ) {
				$sizes_id = $featured_id;
			}
			break;
		case 'audio':
			if ( $featured_id = get_post_thumbnail_id( $attachment->ID ) ) {
				$sizes_id = $featured_id;
			}
			break;
	}

	// Load array of image sizes.
	if ( $sizes_id ) {
		$sizes      = get_intermediate_image_sizes();
		$sizes_data = array();
		foreach ( $sizes as $size ) {
			$src = wp_get_attachment_image_src( $sizes_id, $size );
			if ( $src ) {
				$sizes_data[ $size ]             = $src[0];
				$sizes_data[ $size . '-width' ]  = $src[1];
				$sizes_data[ $size . '-height' ] = $src[2];
			}
		}
		$response['sizes'] = $sizes_data;
	}

	/**
	 * Filters the attachment $response after it has been loaded.
	 *
	 * @since   5.9.0
	 *
	 * @param   array $response Array of loaded attachment data.
	 * @param   WP_Post $attachment Attachment object.
	 * @param   array|false $meta Array of attachment meta data, or false if there is none.
	 */
	return apply_filters( 'acf/load_attachment', $response, $attachment, $meta );
}

/**
 * This function will truncate and return a string
 *
 * @since   5.0.0
 *
 * @param string  $text   The text to truncate.
 * @param integer $length The number of characters to allow in the string.
 *
 * @return  string
 */
function acf_get_truncated( $text, $length = 64 ) {
	$text       = trim( $text );
	$the_length = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );

	$cut_length = $length - 3;
	$return     = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $cut_length ) : substr( $text, 0, $cut_length );

	if ( $the_length > $cut_length ) {
		$return .= '...';
	}

	return $return;
}

/**
 * acf_current_user_can_admin
 *
 * This function will return true if the current user can administrate the ACF field groups
 *
 * @since   5.1.5
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_current_user_can_admin() {

	if ( acf_get_setting( 'show_admin' ) && current_user_can( acf_get_setting( 'capability' ) ) ) {
		return true;
	}

	// return
	return false;
}

/**
 * Wrapper function for current_user_can( 'edit_post', $post_id ).
 *
 * @since 6.3.4
 *
 * @param integer $post_id The post ID to check.
 * @return boolean
 */
function acf_current_user_can_edit_post( int $post_id ): bool {
	/**
	 * The `edit_post` capability is a meta capability, which
	 * gets converted to the correct post type object `edit_post`
	 * equivalent.
	 *
	 * If the post type does not have `map_meta_cap` enabled and the user is
	 * not manually mapping the `edit_post` capability, this will fail
	 * unless the role has the `edit_post` capability added to a user/role.
	 *
	 * However, more (core) stuff will likely break in this scenario.
	 */
	$user_can_edit = current_user_can( 'edit_post', $post_id );

	return (bool) apply_filters( 'acf/current_user_can_edit_post', $user_can_edit, $post_id );
}

/**
 * Checks if the current user can edit a given ACF context.
 *
 * Handles post, user, term, comment, woo_order, block, and option contexts returned by acf_decode_post_id().
 *
 * @since 6.7.2
 *
 * @param array  $post_id_info      The result of acf_decode_post_id(), containing 'type' and 'id'.
 * @param string $options_page_slug Optional. The options page menu slug, used to look up the page's capability.
 * @return boolean
 */
function acf_current_user_can_edit_in_context( array $post_id_info, string $options_page_slug = '' ): bool {
	$type = $post_id_info['type'] ?? '';
	$id   = $post_id_info['id'] ?? 0;

	switch ( $type ) {
		case 'post':
			return acf_current_user_can_edit_post( (int) $id );

		case 'user':
			return current_user_can( 'edit_user', (int) $id );

		case 'term':
			return current_user_can( 'edit_term', (int) $id );

		case 'comment':
			return current_user_can( 'edit_comment', (int) $id );

		case 'woo_order':
			return current_user_can( 'edit_shop_orders' ); // phpcs:ignore

		case 'block':
			return current_user_can( 'edit_posts' );

		case 'option':
			if ( ! empty( $options_page_slug ) && function_exists( 'acf_get_options_page' ) ) {
				$page = acf_get_options_page( $options_page_slug );

				if ( ! empty( $page['capability'] ) && ! empty( $page['post_id'] ) ) {
					// Ensure the page's post_id matches the requested post_id.
					if ( acf_get_valid_post_id( $page['post_id'] ) !== $id ) {
						return false;
					}

					return current_user_can( $page['capability'] );
				}
			}

			return current_user_can( 'manage_options' );

		default:
			return (bool) apply_filters( 'acf/current_user_can_edit_in_context', false, $post_id_info );
	}
}

/**
 * acf_get_filesize
 *
 * This function will return a numeric value of bytes for a given filesize string
 *
 * @since   5.1.5
 *
 * @param   $size (mixed)
 * @return  (int)
 */
function acf_get_filesize( $size = 1 ) {

	// vars
	$unit  = 'MB';
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);

	// look for $unit within the $size parameter (123 KB)
	if ( is_string( $size ) ) {

		// vars
		$custom = strtoupper( substr( $size, -2 ) );

		foreach ( $units as $k => $v ) {
			if ( $custom === $k ) {
				$unit = $k;
				$size = substr( $size, 0, -2 );
			}
		}
	}

	// calc bytes
	$bytes = floatval( $size ) * pow( 1024, $units[ $unit ] );

	// return
	return $bytes;
}

/**
 * acf_format_filesize
 *
 * This function will return a formatted string containing the filesize and unit
 *
 * @since   5.1.5
 *
 * @param   $size (mixed)
 * @return  (int)
 */
function acf_format_filesize( $size = 1 ) {

	// convert
	$bytes = acf_get_filesize( $size );

	// vars
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);

	// loop through units
	foreach ( $units as $k => $v ) {
		$result = $bytes / pow( 1024, $v );

		if ( $result >= 1 ) {
			return $result . ' ' . $k;
		}
	}

	// return
	return $bytes . ' B';
}

/**
 * acf_get_valid_terms
 *
 * This function will replace old terms with new split term ids
 *
 * @since   5.1.5
 *
 * @param   $terms (int|array)
 * @param   $taxonomy (string)
 * @return  $terms
 */
function acf_get_valid_terms( $terms = false, $taxonomy = 'category' ) {

	// force into array
	$terms = acf_get_array( $terms );

	// force ints
	$terms = array_map( 'intval', $terms );

	// bail early if function does not yet exist or
	if ( ! function_exists( 'wp_get_split_term' ) || empty( $terms ) ) {
		return $terms;
	}

	// attempt to find new terms
	foreach ( $terms as $i => $term_id ) {
		$new_term_id = wp_get_split_term( $term_id, $taxonomy );

		if ( $new_term_id ) {
			$terms[ $i ] = $new_term_id;
		}
	}

	// return
	return $terms;
}

/**
 * Returns true when the field only accepts image uploads.
 *
 * @since 6.8.7
 *
 * @param array $field The field array.
 * @return boolean
 */
function acf_is_image_field( $field ) {
	if ( ! is_array( $field ) ) {
		/**
		 * Filters whether the supplied field should be treated as an image field.
		 *
		 * @since 6.8.7
		 *
		 * @param bool  $is_image_field Whether the field only accepts image uploads.
		 * @param array $field          The field array.
		 */
		return (bool) apply_filters( 'acf/is_image_field', false, $field );
	}

	$is_image_field = in_array( acf_maybe_get( $field, 'type', '' ), array( 'image', 'gallery' ), true );

	/**
	 * Filters whether the supplied field should be treated as an image field.
	 *
	 * @since 6.8.7
	 *
	 * @param bool  $is_image_field Whether the field only accepts image uploads.
	 * @param array $field          The field array.
	 */
	$field_type = acf_maybe_get( $field, 'type', '' );
	$field_name = acf_maybe_get( $field, '_name', '' );
	$field_key  = acf_maybe_get( $field, 'key', '' );

	$is_image_field = apply_filters( "acf/is_image_field/type={$field_type}", $is_image_field, $field );
	$is_image_field = apply_filters( "acf/is_image_field/name={$field_name}", $is_image_field, $field );
	$is_image_field = apply_filters( "acf/is_image_field/key={$field_key}", $is_image_field, $field );
	$is_image_field = apply_filters( 'acf/is_image_field', $is_image_field, $field );

	return $is_image_field;
}

/**
 * Returns allowed MIME types restricted to images.
 *
 * @since 6.8.7
 *
 * @param array|false $field Optional field array for field-specific filtering.
 * @return array
 */
function acf_get_image_mime_types( $field = false ) {
	static $image_mimes = null;

	if ( null === $image_mimes ) {
		$image_mimes   = array();
		$allowed_mimes = get_allowed_mime_types();

		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( wp_match_mime_types( 'image', $mime ) ) {
				$image_mimes[ $ext ] = $mime;
			}
		}

		/**
		 * Filters the image MIME types allowed for image and gallery fields.
		 *
		 * @since 6.8.7
		 *
		 * @param array $image_mimes The allowed image MIME types keyed by extension.
		 */
		$image_mimes = apply_filters( 'acf/get_image_mime_types', $image_mimes );
	}

	if ( ! is_array( $field ) || empty( $field['type'] ) ) {
		return $image_mimes;
	}

	/**
	 * Filters the image MIME types allowed for a specific field.
	 *
	 * @since 6.8.7
	 *
	 * @param array $image_mimes The allowed image MIME types keyed by extension.
	 * @param array $field         The field array.
	 */
	$field_type = acf_maybe_get( $field, 'type', '' );
	$field_name = acf_maybe_get( $field, '_name', '' );
	$field_key  = acf_maybe_get( $field, 'key', '' );

	$field_mimes = apply_filters( "acf/get_image_mime_types/type={$field_type}", $image_mimes, $field );
	$field_mimes = apply_filters( "acf/get_image_mime_types/name={$field_name}", $field_mimes, $field );
	$field_mimes = apply_filters( "acf/get_image_mime_types/key={$field_key}", $field_mimes, $field );

	return $field_mimes;
}

/**
 * Applies filters to image attachment validation errors.
 *
 * @since 6.8.7
 *
 * @param array  $errors     Validation errors.
 * @param array  $file       Normalized file data.
 * @param array  $attachment Raw attachment data for the current context.
 * @param array  $field      The field array.
 * @param string $context    The validation context.
 * @return array
 */
function acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context ) {
	/**
	 * Filters the errors for image attachment validation.
	 *
	 * @since 6.8.7
	 *
	 * @param array  $errors     An array of errors.
	 * @param array  $file       An array of data for a single file.
	 * @param array  $attachment An array of attachment data which differs based on the context.
	 * @param array  $field      The field array.
	 * @param string $context    The current context (uploading, preparing, basic_upload).
	 */
	$field_type = acf_maybe_get( $field, 'type', '' );
	$field_name = acf_maybe_get( $field, '_name', '' );
	$field_key  = acf_maybe_get( $field, 'key', '' );

	$errors = apply_filters( "acf/validate_is_image_attachment/type={$field_type}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( "acf/validate_is_image_attachment/name={$field_name}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( "acf/validate_is_image_attachment/key={$field_key}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( 'acf/validate_is_image_attachment', $errors, $file, $attachment, $field, $context );

	return $errors;
}

/**
 * Validates image attachment data during upload.
 *
 * @since 6.8.7
 *
 * @param array  $errors        Validation errors.
 * @param array  $file          Normalized file data.
 * @param array  $attachment    Raw attachment data for the current context.
 * @param array  $field         The field array.
 * @param string $context       The validation context.
 * @param string $error_message The error message to use when validation fails.
 * @return array
 */
function acf_validate_is_image_attachment_upload( $errors, $file, $attachment, $field, $context, $error_message ) {
	if ( empty( $attachment['tmp_name'] ) || ! file_exists( $attachment['tmp_name'] ) ) {
		$errors['invalid_image'] = $error_message;

		return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
	}

	$checked = wp_check_filetype_and_ext( $attachment['tmp_name'], $attachment['name'], acf_get_image_mime_types( $field ) );

	if ( empty( $checked['ext'] ) || empty( $checked['type'] ) || empty( wp_match_mime_types( 'image', $checked['type'] ) ) ) {
		$errors['invalid_image'] = $error_message;

		return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
	}

	/**
	 * Filters the list of image MIME types that cannot be validated by
	 * wp_get_image_mime(), which reads a binary magic number and only
	 * recognizes raster formats. Vector / XML image types (e.g. SVG)
	 * always fail that check by design and must be excluded from it.
	 *
	 * @since 6.8.8
	 *
	 * @param array $vector_mime_types MIME types to skip the binary magic-number check for.
	 */
	$vector_mime_types = (array) apply_filters( 'acf/validate_is_image_attachment/vector_mime_types', array( 'image/svg+xml' ) );

	if ( ! in_array( $checked['type'], $vector_mime_types, true ) ) {
		$real_mime = wp_get_image_mime( $attachment['tmp_name'] );

		if ( ! $real_mime || empty( wp_match_mime_types( 'image', $real_mime ) ) ) {
			$errors['invalid_image'] = $error_message;
		}
	}

	return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
}

/**
 * Validates image attachment data when preparing for the media library.
 *
 * @since 6.8.7
 *
 * @param array  $errors        Validation errors.
 * @param array  $file          Normalized file data.
 * @param array  $attachment    Raw attachment data for the current context.
 * @param array  $field         The field array.
 * @param string $context       The validation context.
 * @param string $error_message The error message to use when validation fails.
 * @return array
 */
function acf_validate_is_image_attachment_prepare( $errors, $file, $attachment, $field, $context, $error_message ) {
	$attachment_id = (int) acf_maybe_get( $attachment, 'id', 0 );

	if ( $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			$errors['invalid_image'] = $error_message;
		}

		return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
	}

	$mime = acf_maybe_get( $attachment, 'mime', acf_maybe_get( $attachment, 'type', '' ) );

	if ( $mime && empty( wp_match_mime_types( 'image', $mime ) ) ) {
		$errors['invalid_image'] = $error_message;
	}

	return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
}

/**
 * Validates image attachment data during basic uploader pre-save checks.
 *
 * @since 6.8.7
 *
 * @param array  $errors        Validation errors.
 * @param array  $file          Normalized file data.
 * @param array  $attachment    Raw attachment data for the current context.
 * @param array  $field         The field array.
 * @param string $context       The validation context.
 * @param string $error_message The error message to use when validation fails.
 * @return array
 */
function acf_validate_is_image_attachment_basic_upload( $errors, $file, $attachment, $field, $context, $error_message ) {
	$mime = acf_maybe_get( $attachment, 'type', '' );

	if ( $mime && empty( wp_match_mime_types( 'image', $mime ) ) ) {
		$errors['invalid_image'] = $error_message;
	}

	return acf_apply_validate_is_image_attachment_filters( $errors, $file, $attachment, $field, $context );
}

/**
 * Validates that an attachment is an image for image and gallery fields.
 *
 * @since 6.8.7
 *
 * @param array  $errors     Existing validation errors.
 * @param array  $file       Normalized file data.
 * @param array  $attachment Raw attachment data for the current context.
 * @param array  $field      The field array.
 * @param string $context    The validation context.
 * @return array
 */
function acf_validate_is_image_attachment( $errors, $file, $attachment, $field, $context ) {
	if ( ! acf_is_image_field( $field ) ) {
		return $errors;
	}

	/**
	 * Filters the error message used when an attachment is not a valid image.
	 *
	 * @since 6.8.7
	 *
	 * @param string $error_message The default error message.
	 * @param array  $field         The field array.
	 * @param string $context       The current context (uploading, preparing, basic_upload).
	 * @param array  $file          An array of data for a single file.
	 * @param array  $attachment    An array of attachment data which differs based on the context.
	 */
	$error_message = apply_filters( 'acf/validate_is_image_attachment/error_message', __( 'File must be a valid image.', 'acf' ), $field, $context, $file, $attachment );

	if ( 'upload' === $context ) {
		return acf_validate_is_image_attachment_upload( $errors, $file, $attachment, $field, $context, $error_message );
	}

	if ( 'prepare' === $context ) {
		return acf_validate_is_image_attachment_prepare( $errors, $file, $attachment, $field, $context, $error_message );
	}

	return acf_validate_is_image_attachment_basic_upload( $errors, $file, $attachment, $field, $context, $error_message );
}

/**
 * acf_validate_attachment
 *
 * This function will validate an attachment based on a field's restrictions and return an array of errors
 *
 * @since   5.2.3
 *
 * @param   $attachment (array) attachment data. Changes based on context
 * @param   $field (array) field settings containing restrictions
 * @param   context (string)                                     $file is different when uploading / preparing
 * @return  $errors (array)
 */
function acf_validate_attachment( $attachment, $field, $context = 'prepare' ) {

	// vars
	$errors = array();
	$file   = array(
		'type'   => '',
		'width'  => 0,
		'height' => 0,
		'size'   => 0,
	);

	// upload
	if ( $context == 'upload' ) {

		// vars
		$file['type'] = pathinfo( $attachment['name'], PATHINFO_EXTENSION );
		$file['size'] = filesize( $attachment['tmp_name'] );

		if ( acf_is_image_field( $field ) || strpos( $attachment['type'], 'image' ) !== false ) {
			$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $attachment['tmp_name'] ) : getimagesize( $attachment['tmp_name'] );

			if ( $size ) {
				$file['width']  = acf_maybe_get( $size, 0 );
				$file['height'] = acf_maybe_get( $size, 1 );
			}
		}

		// prepare
	} elseif ( $context == 'prepare' ) {
		$use_path       = isset( $attachment['filename'] ) ? $attachment['filename'] : $attachment['url'];
		$file['type']   = pathinfo( $use_path, PATHINFO_EXTENSION );
		$file['size']   = acf_maybe_get( $attachment, 'filesizeInBytes', 0 );
		$file['width']  = acf_maybe_get( $attachment, 'width', 0 );
		$file['height'] = acf_maybe_get( $attachment, 'height', 0 );

		// custom
	} else {
		$file         = array_merge( $file, $attachment );
		$use_path     = isset( $attachment['filename'] ) ? $attachment['filename'] : $attachment['url'];
		$file['type'] = pathinfo( $use_path, PATHINFO_EXTENSION );
	}

	// image
	if ( $file['width'] || $file['height'] ) {

		// width
		$min_width = (int) acf_maybe_get( $field, 'min_width', 0 );
		$max_width = (int) acf_maybe_get( $field, 'max_width', 0 );

		if ( $file['width'] ) {
			if ( $min_width && $file['width'] < $min_width ) {

				// min width
				$errors['min_width'] = sprintf( __( 'Image width must be at least %dpx.', 'acf' ), $min_width );
			} elseif ( $max_width && $file['width'] > $max_width ) {

				// min width
				$errors['max_width'] = sprintf( __( 'Image width must not exceed %dpx.', 'acf' ), $max_width );
			}
		}

		// height
		$min_height = (int) acf_maybe_get( $field, 'min_height', 0 );
		$max_height = (int) acf_maybe_get( $field, 'max_height', 0 );

		if ( $file['height'] ) {
			if ( $min_height && $file['height'] < $min_height ) {

				// min height
				$errors['min_height'] = sprintf( __( 'Image height must be at least %dpx.', 'acf' ), $min_height );
			} elseif ( $max_height && $file['height'] > $max_height ) {

				// min height
				$errors['max_height'] = sprintf( __( 'Image height must not exceed %dpx.', 'acf' ), $max_height );
			}
		}
	}

	// file size
	if ( $file['size'] ) {
		$min_size = acf_maybe_get( $field, 'min_size', 0 );
		$max_size = acf_maybe_get( $field, 'max_size', 0 );

		if ( $min_size && $file['size'] < acf_get_filesize( $min_size ) ) {

			// min width
			$errors['min_size'] = sprintf( __( 'File size must be at least %s.', 'acf' ), acf_format_filesize( $min_size ) );
		} elseif ( $max_size && $file['size'] > acf_get_filesize( $max_size ) ) {

			// min width
			$errors['max_size'] = sprintf( __( 'File size must not exceed %s.', 'acf' ), acf_format_filesize( $max_size ) );
		}
	}

	// file type
	if ( $file['type'] ) {
		$mime_types = acf_maybe_get( $field, 'mime_types', '' );

		// lower case
		$file['type'] = strtolower( $file['type'] );
		$mime_types   = strtolower( $mime_types );

		// explode
		$mime_types = str_replace( array( ' ', '.' ), '', $mime_types );
		$mime_types = explode( ',', $mime_types ); // split pieces
		$mime_types = array_filter( $mime_types ); // remove empty pieces

		if ( ! empty( $mime_types ) && ! in_array( $file['type'], $mime_types ) ) {

			// glue together last 2 types
			if ( count( $mime_types ) > 1 ) {
				$last1 = array_pop( $mime_types );
				$last2 = array_pop( $mime_types );

				$mime_types[] = $last2 . ' ' . __( 'or', 'acf' ) . ' ' . $last1;
			}

			$errors['mime_types'] = sprintf( __( 'File type must be %s.', 'acf' ), implode( ', ', $mime_types ) );
		}
	}

	/**
	* Filters the errors for a file before it is uploaded or displayed in the media modal.
	*
	* @since   5.2.3
	*
	* @param   array $errors An array of errors.
	* @param   array $file An array of data for a single file.
	* @param   array $attachment An array of attachment data which differs based on the context.
	* @param   array $field The field array.
	* @param   string $context The curent context (uploading, preparing)
	*/
	$errors = apply_filters( "acf/validate_attachment/type={$field['type']}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( "acf/validate_attachment/name={$field['_name']}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( "acf/validate_attachment/key={$field['key']}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters( 'acf/validate_attachment', $errors, $file, $attachment, $field, $context );

	// return
	return $errors;
}

/**
* _acf_settings_uploader
 *
 * Dynamic logic for uploader setting
 *
 * @since   5.2.3
 *
 * @param   $uploader (string)
 * @return  $uploader
 */

add_filter( 'acf/settings/uploader', '_acf_settings_uploader' );

function _acf_settings_uploader( $uploader ) {

	// if can't upload files
	if ( ! current_user_can( 'upload_files' ) ) {
		$uploader = 'basic';
	}

	// return
	return $uploader;
}

/**
 * acf_translate
 *
 * This function will translate a string using the new 'l10n_textdomain' setting
 * Also works for arrays which is great for fields - select -> choices
 *
 * @since   5.3.2
 *
 * @param   $string (mixed) string or array containins strings to be translated
 * @return  $string
 */
function acf_translate( $string ) {

	// vars
	$l10n       = acf_get_setting( 'l10n' );
	$textdomain = acf_get_setting( 'l10n_textdomain' );

	// bail early if not enabled
	if ( ! $l10n ) {
		return $string;
	}

	// bail early if no textdomain
	if ( ! $textdomain ) {
		return $string;
	}

	// is array
	if ( is_array( $string ) ) {
		return array_map( 'acf_translate', $string );
	}

	// bail early if not string
	if ( ! is_string( $string ) ) {
		return $string;
	}

	// bail early if empty
	if ( $string === '' ) {
		return $string;
	}

	// allow for var_export export
	if ( acf_get_setting( 'l10n_var_export' ) ) {

		// bail early if already translated
		if ( substr( $string, 0, 7 ) === '!!__(!!' ) {
			return $string;
		}

		// return
		return "!!__(!!'" . $string . "!!', !!'" . $textdomain . "!!')!!";
	}

	// vars
	return __( $string, $textdomain );
}

/**
 * acf_maybe_add_action
 *
 * This function will determine if the action has already run before adding / calling the function
 *
 * @since   5.3.2
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_maybe_add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {

	// if action has already run, execute it
	// - if currently doing action, allow $tag to be added as per usual to allow $priority ordering needed for 3rd party asset compatibility
	if ( did_action( $tag ) && ! doing_action( $tag ) ) {
		call_user_func( $function_to_add );

		// if action has not yet run, add it
	} else {
		add_action( $tag, $function_to_add, $priority, $accepted_args );
	}
}

/**
 * acf_is_row_collapsed
 *
 * This function will return true if the field's row is collapsed
 *
 * @since   5.3.2
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_is_row_collapsed( $field_key = '', $row_index = 0 ) {

	// collapsed
	$collapsed = acf_get_user_setting( 'collapsed_' . $field_key, '' );

	// cookie fallback ( version < 5.3.2 )
	if ( $collapsed === '' ) {
		$collapsed = acf_extract_var( $_COOKIE, "acf_collapsed_{$field_key}", '' );
		$collapsed = str_replace( '|', ',', $collapsed );

		// update
		acf_update_user_setting( 'collapsed_' . $field_key, $collapsed );
	}

	// explode
	$collapsed = explode( ',', $collapsed );
	$collapsed = array_filter( $collapsed, 'is_numeric' );

	// collapsed class
	return in_array( $row_index, $collapsed );
}

/**
 * Return an image tag for the provided attachment ID
 *
 * @since 5.5.0
 * @deprecated 6.3.2
 *
 * @param integer $attachment_id The attachment ID
 * @param string  $size          The image size to use in the image tag.
 * @return false
 */
function acf_get_attachment_image( $attachment_id = 0, $size = 'thumbnail' ) {
	// report function as deprecated
	_deprecated_function( __FUNCTION__, '6.3.2' );
	return false;
}

/**
 * acf_get_post_thumbnail
 *
 * This function will return a thumbail image url for a given post
 *
 * @since   5.3.8
 *
 * @param   $post (obj)
 * @param   $size (mixed)
 * @return  (string)
 */
function acf_get_post_thumbnail( $post = null, $size = 'thumbnail' ) {

	// vars
	$data = array(
		'url'  => '',
		'type' => '',
		'html' => '',
	);

	// post
	$post = get_post( $post );

	// bail early if no post
	if ( ! $post ) {
		return $data;
	}

	// vars
	$thumb_id  = $post->ID;
	$mime_type = acf_maybe_get( explode( '/', $post->post_mime_type ), 0 );

	// attachment
	if ( $post->post_type === 'attachment' ) {

		// change $thumb_id
		if ( $mime_type === 'audio' || $mime_type === 'video' ) {
			$thumb_id = get_post_thumbnail_id( $post->ID );
		}

		// post
	} else {
		$thumb_id = get_post_thumbnail_id( $post->ID );
	}

	// try url
	$data['url'] = wp_get_attachment_image_src( $thumb_id, $size );
	$data['url'] = acf_maybe_get( $data['url'], 0 );

	// default icon
	if ( ! $data['url'] && $post->post_type === 'attachment' ) {
		$data['url']  = wp_mime_type_icon( $post->ID );
		$data['type'] = 'icon';
	}

	// html
	$data['html'] = '<img src="' . $data['url'] . '" alt="" />';

	// return
	return $data;
}

/**
 * acf_get_browser
 *
 * Returns the name of the current browser.
 *
 * @since   5.0.0
 *
 * @param   void
 * @return  string
 */
function acf_get_browser() {

	// Check server var.
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );

		// Loop over search terms.
		$browsers = array(
			'Firefox' => 'firefox',
			'Trident' => 'msie',
			'MSIE'    => 'msie',
			'Edge'    => 'edge',
			'Chrome'  => 'chrome',
			'Safari'  => 'safari',
		);
		foreach ( $browsers as $k => $v ) {
			if ( strpos( $agent, $k ) !== false ) {
				return $v;
			}
		}
	}

	// Return default.
	return '';
}

/**
 * acf_is_ajax
 *
 * This function will reutrn true if performing a wp ajax call
 *
 * @since   5.3.8
 *
 * @param   n/a
 * @return  (boolean)
 */
function acf_is_ajax( $action = '' ) {

	// vars
	$is_ajax = false;

	// check if is doing ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$is_ajax = true;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	// check $action
	if ( $action && acf_maybe_get( $_POST, 'action' ) !== $action ) {
	// phpcs:enable WordPress.Security.NonceVerification.Missing
		$is_ajax = false;
	}

	// return
	return $is_ajax;
}

/**
 * Returns a date value in a formatted string.
 *
 * @since 5.3.8
 *
 * @param string $value  The date value to format.
 * @param string $format The format to use.
 * @return string
 */
function acf_format_date( $value, $format ) {
	// Bail early if no value or value is not what we expect.
	if ( ! $value || ( ! is_string( $value ) && ! is_int( $value ) ) ) {
		return $value;
	}

	// Numeric (either unix or YYYYMMDD).
	if ( is_numeric( $value ) && strlen( $value ) !== 8 ) {
		$unixtimestamp = $value;
	} else {
		$unixtimestamp = strtotime( $value );
	}

	return date_i18n( $format, $unixtimestamp );
}

/**
 * Previously, deletes the debug.log file.
 *
 * @since      5.7.10
 * @deprecated 6.2.7
 */
function acf_clear_log() {
	_deprecated_function( __FUNCTION__, '6.2.7' );
	return false;
}

/**
 * acf_log
 *
 * description
 *
 * @since   5.3.8
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_log() {

	// vars
	$args = func_get_args();

	// loop
	foreach ( $args as $i => $arg ) {

		// array | object
		if ( is_array( $arg ) || is_object( $arg ) ) {
			$arg = print_r( $arg, true );

			// bool
		} elseif ( is_bool( $arg ) ) {
			$arg = 'bool(' . ( $arg ? 'true' : 'false' ) . ')';
		}

		// update
		$args[ $i ] = $arg;
	}

	// log
	error_log( implode( ' ', $args ) );
}

/**
 * acf_dev_log
 *
 * Used to log variables only if ACF_DEV is defined
 *
 * @since   5.7.4
 *
 * @param   mixed
 * @return  void
 */
function acf_dev_log() {
	if ( defined( 'ACF_DEV' ) && ACF_DEV ) {
		call_user_func_array( 'acf_log', func_get_args() );
	}
}

/**
 * acf_doing
 *
 * This function will tell ACF what task it is doing
 *
 * @since   5.3.8
 *
 * @param   $event (string)
 * @param   context (string)
 * @return  n/a
 */
function acf_doing( $event = '', $context = '' ) {

	acf_update_setting( 'doing', $event );
	acf_update_setting( 'doing_context', $context );
}

/**
 * acf_is_doing
 *
 * This function can be used to state what ACF is doing, or to check
 *
 * @since   5.3.8
 *
 * @param   $event (string)
 * @param   context (string)
 * @return  (boolean)
 */
function acf_is_doing( $event = '', $context = '' ) {

	// vars
	$doing = false;

	// task
	if ( acf_get_setting( 'doing' ) === $event ) {
		$doing = true;
	}

	// context
	if ( $context && acf_get_setting( 'doing_context' ) !== $context ) {
		$doing = false;
	}

	// return
	return $doing;
}

/**
 * acf_is_plugin_active
 *
 * This function will return true if the ACF plugin is active
 * - May be included within a theme or other plugin
 *
 * @since   5.4.0
 *
 * @param   $basename (int)
 * @return  $post_id (int)
 */
function acf_is_plugin_active() {

	// vars
	$basename = acf_get_setting( 'basename' );

	// ensure is_plugin_active() exists (not on frontend)
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// return
	return is_plugin_active( $basename );
}

/**
 * acf_send_ajax_results
 *
 * This function will print JSON data for a Select2 AJAX query
 *
 * @since   5.4.0
 *
 * @param   $response (array)
 * @return  n/a
 */
function acf_send_ajax_results( $response ) {

	// validate
	$response = wp_parse_args(
		$response,
		array(
			'results' => array(),
			'more'    => false,
			'limit'   => 0,
		)
	);

	// limit
	if ( $response['limit'] && $response['results'] ) {

		// vars
		$total = 0;

		foreach ( $response['results'] as $result ) {

			// parent
			++$total;

			// children
			if ( ! empty( $result['children'] ) ) {
				$total += count( $result['children'] );
			}
		}

		// calc
		if ( $total >= $response['limit'] ) {
			$response['more'] = true;
		}
	}

	// return
	wp_send_json( $response );
}

/**
 * acf_is_sequential_array
 *
 * This function will return true if the array contains only numeric keys
 *
 * @source  http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
 *
 * @since   5.4.0
 *
 * @param   $array (array)
 * @return  (boolean)
 */
function acf_is_sequential_array( $array ) {

	// bail early if not array
	if ( ! is_array( $array ) ) {
		return false;
	}

	// loop
	foreach ( $array as $key => $value ) {

		// bail early if is string
		if ( is_string( $key ) ) {
			return false;
		}
	}

	// return
	return true;
}

/**
 * acf_is_associative_array
 *
 * This function will return true if the array contains one or more string keys
 *
 * @source  http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
 *
 * @since   5.4.0
 *
 * @param   $array (array)
 * @return  (boolean)
 */
function acf_is_associative_array( $array ) {

	// bail early if not array
	if ( ! is_array( $array ) ) {
		return false;
	}

	// loop
	foreach ( $array as $key => $value ) {

		// bail early if is string
		if ( is_string( $key ) ) {
			return true;
		}
	}

	// return
	return false;
}

/**
 * acf_add_array_key_prefix
 *
 * This function will add a prefix to all array keys
 * Useful to preserve numeric keys when performing array_multisort
 *
 * @since   5.4.0
 *
 * @param   $array (array)
 * @param   $prefix (string)
 * @return  (array)
 */
function acf_add_array_key_prefix( $array, $prefix ) {

	// vars
	$array2 = array();

	// loop
	foreach ( $array as $k => $v ) {
		$k2            = $prefix . $k;
		$array2[ $k2 ] = $v;
	}

	// return
	return $array2;
}

/**
 * acf_remove_array_key_prefix
 *
 * This function will remove a prefix to all array keys
 * Useful to preserve numeric keys when performing array_multisort
 *
 * @since   5.4.0
 *
 * @param   $array (array)
 * @param   $prefix (string)
 * @return  (array)
 */
function acf_remove_array_key_prefix( $array, $prefix ) {

	// vars
	$array2 = array();
	$l      = strlen( $prefix );

	// loop
	foreach ( $array as $k => $v ) {
		$k2            = ( substr( $k, 0, $l ) === $prefix ) ? substr( $k, $l ) : $k;
		$array2[ $k2 ] = $v;
	}

	// return
	return $array2;
}

/**
 * This function will remove the proticol from a url
 * Used to allow licenses to remain active if a site is switched to https
 *
 * @since   5.5.4
 *
 * @param   string $url The URL to strip the protocol from.
 * @return  string
 */
function acf_strip_protocol( $url ) {

	// strip the protocol
	return str_replace( array( 'http://', 'https://' ), '', $url );
}

/**
 * This function will connect an attacment (image etc) to the post
 * Used to connect attachements uploaded directly to media that have not been attaced to a post
 *
 * @since   5.8.0 Added filter to prevent connection.
 * @since   5.5.4
 *
 * @param   integer $attachment_id The attachment ID.
 * @param   integer $post_id       The post ID.
 * @return  boolean True if attachment was connected.
 */
function acf_connect_attachment_to_post( $attachment_id = 0, $post_id = 0 ) {

	// bail early if $attachment_id is not valid.
	if ( ! $attachment_id || ! is_numeric( $attachment_id ) ) {
		return false;
	}

	// bail early if $post_id is not valid.
	if ( ! $post_id || ! is_numeric( $post_id ) ) {
		return false;
	}

	/**
	* Filters whether or not to connect the attachment.
	*
	* @since   5.8.0
	*
	* @param   bool $bool Returning false will prevent the connection. Default true.
	* @param   int $attachment_id The attachment ID.
	* @param   int $post_id The post ID.
	*/
	if ( ! apply_filters( 'acf/connect_attachment_to_post', true, $attachment_id, $post_id ) ) {
		return false;
	}

	// vars
	$post = get_post( $attachment_id );

	// Check if is valid post.
	if ( $post && $post->post_type == 'attachment' && $post->post_parent == 0 ) {

		// update
		wp_update_post(
			array(
				'ID'          => $post->ID,
				'post_parent' => $post_id,
			)
		);

		// return
		return true;
	}

	// return
	return true;
}

/**
 * Encrypts a string using PHP.
 * https://bhoover.com/using-php-openssl_encrypt-openssl_decrypt-encrypt-decrypt-data/
 *
 * @since 5.5.8
 *
 * @param string $data    The data to encrypt.
 * @param string $context Optional domain-separation label. When non-empty, the key derivation
 *                        and HMAC input are bound to this label so a token minted for one
 *                        context cannot be verified as belonging to another. When empty, the
 *                        legacy key derivation is used for back-compat with existing callers.
 * @return string|false Encrypted string, or false if encryption fails.
 */
function acf_encrypt( $data = '', string $context = '' ) {

	if ( ! function_exists( 'openssl_encrypt' ) ) {
		return false;
	}

	if ( '' === $context ) {
		$key     = wp_hash( 'acf_encrypt' );
		$mac_key = wp_hash( 'acf_encrypt_mac' );
	} else {
		$key     = wp_hash( 'acf_encrypt|' . $context );
		$mac_key = wp_hash( 'acf_encrypt_mac|' . $context );
	}

	$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
	if ( false === $iv ) {
		return false;
	}

	$encrypted_data = openssl_encrypt( $data, 'aes-256-cbc', $key, 0, $iv );
	if ( false === $encrypted_data ) {
		return false;
	}

	$payload  = $encrypted_data . '::' . $iv;
	$mac_data = '' === $context ? $payload : ( $context . '|' . $payload );
	$hmac     = hash_hmac( 'sha256', $mac_data, $mac_key, true );

	return base64_encode( $payload . $hmac );
}

/**
 * Decrypts an encrypted string using PHP.
 * https://bhoover.com/using-php-openssl_encrypt-openssl_decrypt-encrypt-decrypt-data/
 *
 * @since 5.5.8
 *
 * @param string $data    The string to decrypt.
 * @param string $context Optional domain-separation label. Must match the context passed to
 *                        acf_encrypt() at mint time. When empty, the legacy key derivation is
 *                        used for back-compat with existing callers.
 * @return string|false Decrypted string, or false if the payload is malformed or decryption fails.
 */
function acf_decrypt( $data = '', string $context = '' ) {

	if ( ! function_exists( 'openssl_decrypt' ) ) {
		return false;
	}

	$raw = base64_decode( (string) $data, true );
	if ( false === $raw ) {
		return false;
	}

	if ( strlen( $raw ) <= 32 ) {
		return false;
	}

	if ( '' === $context ) {
		$key     = wp_hash( 'acf_encrypt' );
		$mac_key = wp_hash( 'acf_encrypt_mac' );
	} else {
		$key     = wp_hash( 'acf_encrypt|' . $context );
		$mac_key = wp_hash( 'acf_encrypt_mac|' . $context );
	}

	$hmac    = substr( $raw, -32 );
	$payload = substr( $raw, 0, -32 );

	$mac_data = '' === $context ? $payload : ( $context . '|' . $payload );
	$expected = hash_hmac( 'sha256', $mac_data, $mac_key, true );
	if ( ! hash_equals( $expected, $hmac ) ) {
		return false;
	}

	if ( strpos( $payload, '::' ) === false ) {
		return false;
	}

	list( $encrypted_data, $iv ) = explode( '::', $payload, 2 );

	return openssl_decrypt( $encrypted_data, 'aes-256-cbc', $key, 0, $iv );
}

/**
 * acf_parse_markdown
 *
 * A very basic regex-based Markdown parser function based off [slimdown](https://gist.github.com/jbroadway/2836900).
 *
 * @since   5.7.2
 *
 * @param   string $text The string to parse.
 * @return  string
 */
function acf_parse_markdown( $text = '' ) {

	// trim
	$text = trim( $text );

	// rules
	$rules = array(
		'/=== (.+?) ===/'            => '<h2>$1</h2>',                   // headings
		'/== (.+?) ==/'              => '<h3>$1</h3>',                   // headings
		'/= (.+?) =/'                => '<h4>$1</h4>',                   // headings
		'/\[([^\[]+)\]\(([^\)]+)\)/' => '<a href="$2">$1</a>',           // links
		'/(\*\*)(.*?)\1/'            => '<strong>$2</strong>',           // bold
		'/(\*)(.*?)\1/'              => '<em>$2</em>',                   // intalic
		'/`(.*?)`/'                  => '<code>$1</code>',               // inline code
		'/\n\*(.*)/'                 => "\n<ul>\n\t<li>$1</li>\n</ul>",  // ul lists
		'/\n[0-9]+\.(.*)/'           => "\n<ol>\n\t<li>$1</li>\n</ol>",  // ol lists
		'/<\/ul>\s?<ul>/'            => '',                              // fix extra ul
		'/<\/ol>\s?<ol>/'            => '',                              // fix extra ol
	);
	foreach ( $rules as $k => $v ) {
		$text = preg_replace( $k, $v, $text );
	}

	// autop
	$text = wpautop( $text );

	// return
	return $text;
}

/**
 * acf_get_sites
 *
 * Returns an array of sites for a network.
 *
 * @since   5.4.0
 *
 * @param   void
 * @return  array
 */
function acf_get_sites() {
	$results = array();
	$sites   = get_sites( array( 'number' => 0 ) );
	if ( $sites ) {
		foreach ( $sites as $site ) {
			$results[] = get_site( $site )->to_array();
		}
	}
	return $results;
}

/**
 * acf_convert_rules_to_groups
 *
 * Converts an array of rules from ACF4 to an array of groups for ACF5
 *
 * @since   5.7.4
 *
 * @param   array  $rules    An array of rules.
 * @param   string $anyorall The anyorall setting used in ACF4. Defaults to 'any'.
 * @return  array
 */
function acf_convert_rules_to_groups( $rules, $anyorall = 'any' ) {

	// vars
	$groups = array();
	$index  = 0;

	// loop
	foreach ( $rules as $rule ) {

		// extract vars
		$group = acf_extract_var( $rule, 'group_no' );
		$order = acf_extract_var( $rule, 'order_no' );

		// calculate group if not defined
		if ( $group === null ) {
			$group = $index;

			// use $anyorall to determine if a new group is needed
			if ( $anyorall == 'any' ) {
				++$index;
			}
		}

		// calculate order if not defined
		if ( $order === null ) {
			$order = isset( $groups[ $group ] ) ? count( $groups[ $group ] ) : 0;
		}

		// append to group
		$groups[ $group ][ $order ] = $rule;

		// sort groups
		ksort( $groups[ $group ] );
	}

	// sort groups
	ksort( $groups );

	// return
	return $groups;
}

/**
 * acf_register_ajax
 *
 * Regsiters an ajax callback.
 *
 * @since   5.7.7
 *
 * @param   string  $name     The ajax action name.
 * @param   array   $callback The callback function or array.
 * @param   boolean $public   Whether to allow access to non logged in users.
 * @return  void
 */
function acf_register_ajax( $name = '', $callback = false, $public = false ) {

	// vars
	$action = "acf/ajax/$name";

	// add action for logged-in users
	add_action( "wp_ajax_$action", $callback );

	// add action for non logged-in users
	if ( $public ) {
		add_action( "wp_ajax_nopriv_$action", $callback );
	}
}

/**
 * acf_str_camel_case
 *
 * Converts a string into camelCase.
 * Thanks to https://stackoverflow.com/questions/31274782/convert-array-keys-from-underscore-case-to-camelcase-recursively
 *
 * @since   5.8.0
 *
 * @param   string $string The string ot convert.
 * @return  string
 */
function acf_str_camel_case( $string = '' ) {
	return lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', $string ) ) ) );
}

/**
 * acf_array_camel_case
 *
 * Converts all aray keys to camelCase.
 *
 * @since   5.8.0
 *
 * @param   array $array The array to convert.
 * @return  array
 */
function acf_array_camel_case( $array = array() ) {
	$array2 = array();
	foreach ( $array as $k => $v ) {
		$array2[ acf_str_camel_case( $k ) ] = $v;
	}
	return $array2;
}

/**
 * Returns true if the current screen is using the block editor.
 *
 * @since 5.8.0
 *
 * @return boolean
 */
function acf_is_block_editor() {
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && method_exists( $screen, 'is_block_editor' ) ) {
			return $screen->is_block_editor();
		}
	}
	return false;
}

/**
 * Return an array of the WordPress reserved terms
 *
 * @since 6.1
 *
 * @return array The WordPress reserved terms list.
 */
function acf_get_wp_reserved_terms() {
	return array( 'action', 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'custom', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'embed', 'error', 'exact', 'feed', 'fields', 'hour', 'link', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'status', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'terms', 'theme', 'themes', 'title', 'type', 'types', 'w', 'withcomments', 'withoutcomments', 'year' );
}

/**
 * Detect if we're on a multisite subsite.
 *
 * @since 6.2.4
 *
 * @return boolean true if we're in a multisite install and not on the main site
 */
function acf_is_multisite_sub_site() {
	if ( is_multisite() && ! is_main_site() ) {
		return true;
	}
	return false;
}

/**
 * Detect if we're on a multisite main site.
 *
 * @since 6.2.4
 *
 * @return boolean true if we're in a multisite install and on the main site
 */
function acf_is_multisite_main_site() {
	if ( is_multisite() && is_main_site() ) {
		return true;
	}
	return false;
}

/**
 * Allow filterable permissions metabox callbacks.
 *
 * @since   6.3.10
 *
 * @param   boolean $enable_meta_box_cb_edit Can the current user edit metabox callbacks.
 * @return  boolean
 */
function acf_settings_enable_meta_box_cb_edit( $enable_meta_box_cb_edit ): bool {
	if ( ! is_super_admin() ) {
		return false;
	}

	return (bool) $enable_meta_box_cb_edit;
}
add_filter( 'acf/settings/enable_meta_box_cb_edit', 'acf_settings_enable_meta_box_cb_edit', 1 );
