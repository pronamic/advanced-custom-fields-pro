<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register store.
acf_register_store( 'location-types' );

/**
 * Registers a location type.
 *
 * @date    8/4/20
 * @since   5.9.0
 *
 * @param   string $class_name The location class name.
 * @return  (ACF_Location|false)
 */
function acf_register_location_type( $class_name ) {
	$store = acf_get_store( 'location-types' );

	// Check class exists.
	if ( ! class_exists( $class_name ) ) {
		/* translators: %s class name for a location that could not be found */
		$message = sprintf( __( 'Class "%s" does not exist.', 'acf' ), $class_name );
		_doing_it_wrong( __FUNCTION__, esc_html( $message ), '5.9.0' );
		return false;
	}

	// Create instance.
	$location_type = new $class_name();
	$name          = $location_type->name;

	// Check location type is unique.
	if ( $store->has( $name ) ) {
		/* translators: %s the name of the location type */
		$message = sprintf( __( 'Location type "%s" is already registered.', 'acf' ), $name );
		_doing_it_wrong( __FUNCTION__, esc_html( $message ), '5.9.0' );
		return false;
	}

	// Add to store.
	$store->set( $name, $location_type );

	/**
	 * Fires after a location type is registered.
	 *
	 * @date    8/4/20
	 * @since   5.9.0
	 *
	 * @param   string $name The location type name.
	 * @param   ACF_Location $location_type The location type instance.
	 */
	do_action( 'acf/registered_location_type', $name, $location_type );

	// Return location type instance.
	return $location_type;
}

/**
 * Returns an array of all registered location types.
 *
 * @date    8/4/20
 * @since   5.9.0
 *
 * @param   void
 * @return  array
 */
function acf_get_location_types() {
	return acf_get_store( 'location-types' )->get();
}

/**
 * Returns a location type for the given name.
 *
 * @date    18/2/19
 * @since   5.7.12
 *
 * @param   string $name The location type name.
 * @return  (ACF_Location|null)
 */
function acf_get_location_type( $name ) {
	return acf_get_store( 'location-types' )->get( $name );
}

/**
 * Returns a grouped array of all location rule types.
 *
 * @date    8/4/20
 * @since   5.9.0
 *
 * @param   void
 * @return  array
 */
function acf_get_location_rule_types() {
	$types = array();

	// Default categories.
	$categories = array(
		'post'  => __( 'Post', 'acf' ),
		'page'  => __( 'Page', 'acf' ),
		'user'  => __( 'User', 'acf' ),
		'forms' => __( 'Forms', 'acf' ),
	);

	// Loop over all location types and append to $type.
	$location_types = acf_get_location_types();
	foreach ( $location_types as $location_type ) {

		// Ignore if not public.
		if ( ! $location_type->public ) {
			continue;
		}

		// Find category label from category name.
		$category = $location_type->category;
		if ( isset( $categories[ $category ] ) ) {
			$category = $categories[ $category ];
		}

		// Append
		$types[ $category ][ $location_type->name ] = esc_html( $location_type->label );
	}

	/**
	 * Filters the location rule types.
	 *
	 * @date    8/4/20
	 * @since   5.9.0
	 *
	 * @param   array $types The location rule types.
	 */
	return apply_filters( 'acf/location/rule_types', $types );
}

/**
 * Returns a validated location rule with all props.
 *
 * @date    8/4/20
 * @since   5.9.0
 *
 * @param   array $rule The location rule.
 * @return  array
 */
function acf_validate_location_rule( $rule = array() ) {

	// Apply defaults.
	$rule = wp_parse_args(
		$rule,
		array(
			'id'       => '',
			'group'    => '',
			'param'    => '',
			'operator' => '==',
			'value'    => '',
		)
	);

	/**
	 * Filters the location rule to ensure is valid.
	 *
	 * @date    8/4/20
	 * @since   5.9.0
	 *
	 * @param   array $rule The location rule.
	 */
	$rule = apply_filters( "acf/location/validate_rule/type={$rule['param']}", $rule );
	$rule = apply_filters( 'acf/location/validate_rule', $rule );
	return $rule;
}

/**
 * Returns an array of operators for a given rule.
 *
 * @date    30/5/17
 * @since   5.6.0
 *
 * @param   array $rule The location rule.
 * @return  array
 */
function acf_get_location_rule_operators( $rule ) {
	$operators = ACF_Location::get_operators( $rule );

	// Get operators from location type since 5.9.
	$location_type = acf_get_location_type( $rule['param'] );
	if ( $location_type ) {
		$operators = $location_type->get_operators( $rule );
	}

	/**
	 * Filters the location rule operators.
	 *
	 * @date    30/5/17
	 * @since   5.6.0
	 *
	 * @param   array $types The location rule operators.
	 */
	$operators = apply_filters( "acf/location/rule_operators/type={$rule['param']}", $operators, $rule );
	$operators = apply_filters( "acf/location/rule_operators/{$rule['param']}", $operators, $rule );
	$operators = apply_filters( 'acf/location/rule_operators', $operators, $rule );
	return $operators;
}

/**
 * Returns an array of values for a given rule.
 *
 * @date    30/5/17
 * @since   5.6.0
 *
 * @param   array $rule The location rule.
 * @return  array
 */
function acf_get_location_rule_values( $rule ) {
	$values = array();

	// Get values from location type since 5.9.
	$location_type = acf_get_location_type( $rule['param'] );
	if ( $location_type ) {
		$values = $location_type->get_values( $rule );
	}

	/**
	 * Filters the location rule values.
	 *
	 * @date    30/5/17
	 * @since   5.6.0
	 *
	 * @param   array $types The location rule values.
	 */
	$values = apply_filters( "acf/location/rule_values/type={$rule['param']}", $values, $rule );
	$values = apply_filters( "acf/location/rule_values/{$rule['param']}", $values, $rule );
	$values = apply_filters( 'acf/location/rule_values', $values, $rule );
	return $values;
}

/**
 * Returns true if the provided rule matches the screen args.
 *
 * @date    30/5/17
 * @since   5.6.0
 *
 * @param   array $rule   The location rule.
 * @param   array $screen The screen args.
 * @param   array $field  The field group array.
 * @return  boolean
 */
function acf_match_location_rule( $rule, $screen, $field_group ) {
	$result = false;

	// Get result from location type since 5.9.
	$location_type = acf_get_location_type( $rule['param'] );
	if ( $location_type ) {
		$result = $location_type->match( $rule, $screen, $field_group );
	}

	/**
	 * Filters the result.
	 *
	 * @date    30/5/17
	 * @since   5.6.0
	 *
	 * @param   bool $result The match result.
	 * @param   array $rule The location rule.
	 * @param   array $screen The screen args.
	 * @param   array $field_group The field group array.
	 */
	$result = apply_filters( "acf/location/match_rule/type={$rule['param']}", $result, $rule, $screen, $field_group );
	$result = apply_filters( 'acf/location/match_rule', $result, $rule, $screen, $field_group );
	$result = apply_filters( "acf/location/rule_match/{$rule['param']}", $result, $rule, $screen, $field_group );
	$result = apply_filters( 'acf/location/rule_match', $result, $rule, $screen, $field_group );
	return $result;
}

/**
 * Returns ann array of screen args to be used against matching rules.
 *
 * @date    8/4/20
 * @since   5.9.0
 *
 * @param   array $screen     The screen args.
 * @param   array $deprecated The field group array.
 * @return  array
 */
function acf_get_location_screen( $screen = array(), $deprecated = false ) {

	// Apply defaults.
	$screen = wp_parse_args(
		$screen,
		array(
			'lang' => acf_get_setting( 'current_language' ),
			'ajax' => false,
		)
	);

	/**
	 * Filters the result.
	 *
	 * @date    30/5/17
	 * @since   5.6.0
	 *
	 * @param   array $screen The screen args.
	 * @param   array $deprecated The field group array.
	 */
	return apply_filters( 'acf/location/screen', $screen, $deprecated );
}

/**
 * Alias of acf_register_location_type().
 *
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   string $class_name The location class name.
 * @return  (ACF_Location|false)
 */
function acf_register_location_rule( $class_name ) {
	return acf_register_location_type( $class_name );
}

/**
 * Alias of acf_get_location_type().
 *
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   string $class_name The location class name.
 * @return  (ACF_Location|false)
 */
function acf_get_location_rule( $name ) {
	return acf_get_location_type( $name );
}

/**
 * Alias of acf_validate_location_rule().
 *
 * @date    30/5/17
 * @since   5.6.0
 *
 * @param   array $rule The location rule.
 * @return  array
 */
function acf_get_valid_location_rule( $rule ) {
	return acf_validate_location_rule( $rule );
}
