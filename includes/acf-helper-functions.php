<?php

/*
 * acf_is_empty
 *
 * Returns true if the value provided is considered "empty". Allows numbers such as 0.
 *
 * @date    6/7/16
 * @since   5.4.0
 *
 * @param   mixed $var The value to check.
 * @return  bool
 */
function acf_is_empty( $var ) {
	return ( ! $var && ! is_numeric( $var ) );
}

/**
 * acf_not_empty
 *
 * Returns true if the value provided is considered "not empty". Allows numbers such as 0.
 *
 * @date    15/7/19
 * @since   5.8.1
 *
 * @param   mixed $var The value to check.
 * @return  bool
 */
function acf_not_empty( $var ) {
	return ( $var || is_numeric( $var ) );
}

/**
 * acf_uniqid
 *
 * Returns a unique numeric based id.
 *
 * @date    9/1/19
 * @since   5.7.10
 *
 * @param   string $prefix The id prefix. Defaults to 'acf'.
 * @return  string
 */
function acf_uniqid( $prefix = 'acf' ) {

	// Instantiate global counter.
	global $acf_uniqid;
	if ( ! isset( $acf_uniqid ) ) {
		$acf_uniqid = 1;
	}

	// Return id.
	return $prefix . '-' . $acf_uniqid++;
}

/**
 * acf_merge_attributes
 *
 * Merges together two arrays but with extra functionality to append class names.
 *
 * @date    22/1/19
 * @since   5.7.10
 *
 * @param   array $array1 An array of attributes.
 * @param   array $array2 An array of attributes.
 * @return  array
 */
function acf_merge_attributes( $array1, $array2 ) {

	// Merge together attributes.
	$array3 = array_merge( $array1, $array2 );

	// Append together special attributes.
	foreach ( array( 'class', 'style' ) as $key ) {
		if ( isset( $array1[ $key ] ) && isset( $array2[ $key ] ) ) {
			$array3[ $key ] = trim( $array1[ $key ] ) . ' ' . trim( $array2[ $key ] );
		}
	}

	// Return.
	return $array3;
}

/**
 * acf_cache_key
 *
 * Returns a filtered cache key.
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   string $key The cache key.
 * @return  string
 */
function acf_cache_key( $key = '' ) {

	/**
	 * Filters the cache key.
	 *
	 * @date    25/1/19
	 * @since   5.7.11
	 *
	 * @param   string $key The cache key.
	 * @param   string $original_key The original cache key.
	 */
	return apply_filters( 'acf/get_cache_key', $key, $key );
}

/**
 * acf_request_args
 *
 * Returns an array of $_REQUEST values using the provided defaults.
 *
 * @date    28/2/19
 * @since   5.7.13
 *
 * @param   array $args An array of args.
 * @return  array
 */
function acf_request_args( $args = array() ) {
	foreach ( $args as $k => $v ) {
		$args[ $k ] = isset( $_REQUEST[ $k ] ) ? $_REQUEST[ $k ] : $args[ $k ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	}
	return $args;
}

/**
 * Returns a single $_REQUEST arg with fallback.
 *
 * @date    23/10/20
 * @since   5.9.2
 *
 * @param   string $key The property name.
 * @param   mixed  $default The default value to fallback to.
 * @return  mixed
 */
function acf_request_arg( $name = '', $default = null ) {
	return isset( $_REQUEST[ $name ] ) ? $_REQUEST[ $name ] : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

// Register store.
acf_register_store( 'filters' );

/**
 * acf_enable_filter
 *
 * Enables a filter with the given name.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   string name The modifer name.
 * @return  void
 */
function acf_enable_filter( $name = '' ) {
	acf_get_store( 'filters' )->set( $name, true );
}

/**
 * acf_disable_filter
 *
 * Disables a filter with the given name.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   string name The modifer name.
 * @return  void
 */
function acf_disable_filter( $name = '' ) {
	acf_get_store( 'filters' )->set( $name, false );
}

/**
 * acf_is_filter_enabled
 *
 * Returns the state of a filter for the given name.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   string name The modifer name.
 * @return  array
 */
function acf_is_filter_enabled( $name = '' ) {
	return acf_get_store( 'filters' )->get( $name );
}

/**
 * acf_get_filters
 *
 * Returns an array of filters in their current state.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   void
 * @return  array
 */
function acf_get_filters() {
	return acf_get_store( 'filters' )->get();
}

/**
 * acf_set_filters
 *
 * Sets an array of filter states.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   array $filters An Array of modifers
 * @return  array
 */
function acf_set_filters( $filters = array() ) {
	acf_get_store( 'filters' )->set( $filters );
}

/**
 * acf_disable_filters
 *
 * Disables all filters and returns the previous state.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   void
 * @return  array
 */
function acf_disable_filters() {

	// Get state.
	$prev_state = acf_get_filters();

	// Set all modifers as false.
	acf_set_filters( array_map( '__return_false', $prev_state ) );

	// Return prev state.
	return $prev_state;
}

/**
 * acf_enable_filters
 *
 * Enables all or an array of specific filters and returns the previous state.
 *
 * @date    14/7/16
 * @since   5.4.0
 *
 * @param   array $filters An Array of modifers
 * @return  array
 */
function acf_enable_filters( $filters = array() ) {

	// Get state.
	$prev_state = acf_get_filters();

	// Allow specific filters to be enabled.
	if ( $filters ) {
		acf_set_filters( $filters );

		// Set all modifers as true.
	} else {
		acf_set_filters( array_map( '__return_true', $prev_state ) );
	}

	// Return prev state.
	return $prev_state;
}

/**
 * acf_idval
 *
 * Parses the provided value for an ID.
 *
 * @date    29/3/19
 * @since   5.7.14
 *
 * @param   mixed $value A value to parse.
 * @return  int
 */
function acf_idval( $value ) {

	// Check if value is numeric.
	if ( is_numeric( $value ) ) {
		return (int) $value;

		// Check if value is array.
	} elseif ( is_array( $value ) ) {
		return (int) isset( $value['ID'] ) ? $value['ID'] : 0;

		// Check if value is object.
	} elseif ( is_object( $value ) ) {
		return (int) isset( $value->ID ) ? $value->ID : 0;
	}

	// Return default.
	return 0;
}

/**
 * acf_maybe_idval
 *
 * Checks value for potential id value.
 *
 * @date    6/4/19
 * @since   5.7.14
 *
 * @param   mixed $value A value to parse.
 * @return  mixed
 */
function acf_maybe_idval( $value ) {
	if ( $id = acf_idval( $value ) ) {
		return $id;
	}
	return $value;
}

/**
 * Convert any numeric strings into their equivalent numeric type. This function will
 * work with both single values and arrays.
 *
 * @param mixed $value Either a single value or an array of values.
 * @return mixed
 */
function acf_format_numerics( $value ) {
	if ( is_array( $value ) ) {
		return array_map(
			function ( $v ) {
				return is_numeric( $v ) ? $v + 0 : $v;
			},
			$value
		);
	}

	return is_numeric( $value ) ? $value + 0 : $value;
}

/**
 * acf_numval
 *
 * Casts the provided value as eiter an int or float using a simple hack.
 *
 * @date    11/4/19
 * @since   5.7.14
 *
 * @param   mixed $value A value to parse.
 * @return  (int|float)
 */
function acf_numval( $value ) {
	return ( intval( $value ) == floatval( $value ) ) ? intval( $value ) : floatval( $value );
}

/**
 * acf_idify
 *
 * Returns an id attribute friendly string.
 *
 * @date    24/12/17
 * @since   5.6.5
 *
 * @param   string $str The string to convert.
 * @return  string
 */
function acf_idify( $str = '' ) {
	return str_replace( array( '][', '[', ']' ), array( '-', '-', '' ), strtolower( $str ) );
}

/**
 * Returns a slug friendly string.
 *
 * @date    24/12/17
 * @since   5.6.5
 *
 * @param   string $str The string to convert.
 * @param   string $glue The glue between each slug piece.
 * @return  string
 */
function acf_slugify( $str = '', $glue = '-' ) {
	$raw  = $str;
	$slug = str_replace( array( '_', '-', '/', ' ' ), $glue, strtolower( remove_accents( $raw ) ) );
	$slug = preg_replace( '/[^A-Za-z0-9' . preg_quote( $glue ) . ']/', '', $slug );

	/**
	 * Filters the slug created by acf_slugify().
	 *
	 * @since 5.11.4
	 *
	 * @param string $slug The newly created slug.
	 * @param string $raw  The original string.
	 * @param string $glue The separator used to join the string into a slug.
	 */
	return apply_filters( 'acf/slugify', $slug, $raw, $glue );
}

/**
 * Returns a string with correct full stop punctuation.
 *
 * @date    12/7/19
 * @since   5.8.2
 *
 * @param   string $str The string to format.
 * @return  string
 */
function acf_punctify( $str = '' ) {
	if ( substr( trim( strip_tags( $str ) ), -1 ) !== '.' ) {
		return trim( $str ) . '.';
	}
	return trim( $str );
}

/**
 * acf_did
 *
 * Returns true if ACF already did an event.
 *
 * @date    30/8/19
 * @since   5.8.1
 *
 * @param   string $name The name of the event.
 * @return  bool
 */
function acf_did( $name ) {

	// Return true if already did the event (preventing event).
	if ( acf_get_data( "acf_did_$name" ) ) {
		return true;

		// Otherwise, update store and return false (alowing event).
	} else {
		acf_set_data( "acf_did_$name", true );
		return false;
	}
}

/**
 * Returns the length of a string that has been submitted via $_POST.
 *
 * Uses the following process:
 * 1. Unslash the string because posted values will be slashed.
 * 2. Decode special characters because wp_kses() will normalize entities.
 * 3. Treat line-breaks as a single character instead of two.
 * 4. Use mb_strlen() to accomodate special characters.
 *
 * @date    04/06/2020
 * @since   5.9.0
 *
 * @param   string $str The string to review.
 * @return  int
 */
function acf_strlen( $str ) {
	return mb_strlen( str_replace( "\r\n", "\n", wp_specialchars_decode( wp_unslash( $str ) ) ) );
}

/**
 * Returns a value with default fallback.
 *
 * @date    6/4/20
 * @since   5.9.0
 *
 * @param   mixed $value The value.
 * @param   mixed $default_value The default value.
 * @return  mixed
 */
function acf_with_default( $value, $default_value ) {
	return $value ? $value : $default_value;
}

/**
 * Returns the current priority of a running action.
 *
 * @date    14/07/2020
 * @since   5.9.0
 *
 * @param   string $action The action name.
 * @return  int|bool
 */
function acf_doing_action( $action ) {
	global $wp_filter;
	if ( isset( $wp_filter[ $action ] ) ) {
		return $wp_filter[ $action ]->current_priority();
	}
	return false;
}

/**
 * Returns the current URL.
 *
 * @date    23/01/2015
 * @since   5.1.5
 *
 * @param   void
 * @return  string
 */
function acf_get_current_url() {
	// Ensure props exist to avoid PHP Notice during CLI commands.
	if ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
		return ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	return '';
}

/**
 * Add UTM tracking tags to internal ACF URLs
 *
 * @since 6.0.0
 *
 * @param string $url The URL to be tagged.
 * @param string $campaign The campaign tag.
 * @param string $content The UTM content tag.
 * @return string
 */
function acf_add_url_utm_tags( $url, $campaign, $content, $anchor = false ) {
	$anchor_url = $anchor ? '#' . $anchor : '';
	$query      = http_build_query(
		apply_filters(
			'acf/admin/acf_url_utm_parameters',
			array(
				'utm_source'   => ( defined( 'ACF_PRO' ) && ACF_PRO ) ? 'ACF PRO' : 'ACF Free',
				'utm_medium'   => 'insideplugin',
				'utm_campagin' => $campaign,
				'utm_content'  => $content,
			)
		)
	);
	if ( $query ) {
		$query = '?' . $query;
	}
	return esc_url( $url . $query . $anchor_url );
}
