<?php

// Register store.
acf_register_store( 'hook-variations' );

/**
 * acf_add_filter_variations
 *
 * Registers variations for the given filter.
 *
 * @date    26/1/19
 * @since   5.7.11
 *
 * @param   string $filter The filter name.
 * @param   array  $variations An array variation keys.
 * @param   int    $index The param index to find variation values.
 * @return  void
 */
function acf_add_filter_variations( $filter = '', $variations = array(), $index = 0 ) {

	// Store replacement data.
	acf_get_store( 'hook-variations' )->set(
		$filter,
		array(
			'type'       => 'filter',
			'variations' => $variations,
			'index'      => $index,
		)
	);

	// Add generic handler.
	// Use a priotiry of 10, and accepted args of 10 (ignored by WP).
	add_filter( $filter, '_acf_apply_hook_variations', 10, 10 );
}

/**
 * acf_add_action_variations
 *
 * Registers variations for the given action.
 *
 * @date    26/1/19
 * @since   5.7.11
 *
 * @param   string $action The action name.
 * @param   array  $variations An array variation keys.
 * @param   int    $index The param index to find variation values.
 * @return  void
 */
function acf_add_action_variations( $action = '', $variations = array(), $index = 0 ) {

	// Store replacement data.
	acf_get_store( 'hook-variations' )->set(
		$action,
		array(
			'type'       => 'action',
			'variations' => $variations,
			'index'      => $index,
		)
	);

	// Add generic handler.
	// Use a priotiry of 10, and accepted args of 10 (ignored by WP).
	add_action( $action, '_acf_apply_hook_variations', 10, 10 );
}

/**
 * _acf_apply_hook_variations
 *
 * Applys hook variations during apply_filters() or do_action().
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   mixed
 * @return  mixed
 */
function _acf_apply_hook_variations() {

	// Get current filter.
	$filter = current_filter();

	// Get args provided.
	$args = func_get_args();

	// Get variation information.
	$variations = acf_get_store( 'hook-variations' )->get( $filter );
	extract( $variations );

	// Find field in args using index.
	$field = $args[ $index ];

	// Loop over variations and apply filters.
	foreach ( $variations as $variation ) {

		// Get value from field.
		// First look for "backup" value ("_name", "_key").
		if ( isset( $field[ "_$variation" ] ) ) {
			$value = $field[ "_$variation" ];
		} elseif ( isset( $field[ $variation ] ) ) {
			$value = $field[ $variation ];
		} else {
			continue;
		}

		// Apply filters.
		if ( $type === 'filter' ) {
			$args[0] = apply_filters_ref_array( "$filter/$variation=$value", $args );

			// Or do action.
		} else {
			do_action_ref_array( "$filter/$variation=$value", $args );
		}
	}

	// Return first arg.
	return $args[0];
}

// Register store.
acf_register_store( 'deprecated-hooks' );

/**
 * acf_add_deprecated_filter
 *
 * Registers a deprecated filter to run during the replacement.
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   string $deprecated The deprecated hook.
 * @param   string $version The version this hook was deprecated.
 * @param   string $replacement The replacement hook.
 * @return  void
 */
function acf_add_deprecated_filter( $deprecated, $version, $replacement ) {

	// Store replacement data.
	acf_get_store( 'deprecated-hooks' )->append(
		array(
			'type'        => 'filter',
			'deprecated'  => $deprecated,
			'replacement' => $replacement,
			'version'     => $version,
		)
	);

	// Add generic handler.
	// Use a priotiry of 10, and accepted args of 10 (ignored by WP).
	add_filter( $replacement, '_acf_apply_deprecated_hook', 10, 10 );
}

/**
 * acf_add_deprecated_action
 *
 * Registers a deprecated action to run during the replacement.
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   string $deprecated The deprecated hook.
 * @param   string $version The version this hook was deprecated.
 * @param   string $replacement The replacement hook.
 * @return  void
 */
function acf_add_deprecated_action( $deprecated, $version, $replacement ) {

	// Store replacement data.
	acf_get_store( 'deprecated-hooks' )->append(
		array(
			'type'        => 'action',
			'deprecated'  => $deprecated,
			'replacement' => $replacement,
			'version'     => $version,
		)
	);

	// Add generic handler.
	// Use a priotiry of 10, and accepted args of 10 (ignored by WP).
	add_filter( $replacement, '_acf_apply_deprecated_hook', 10, 10 );
}

/**
 * _acf_apply_deprecated_hook
 *
 * Applys a deprecated filter during apply_filters() or do_action().
 *
 * @date    25/1/19
 * @since   5.7.11
 *
 * @param   mixed
 * @return  mixed
 */
function _acf_apply_deprecated_hook() {

	// Get current hook.
	$hook = current_filter();

	// Get args provided.
	$args = func_get_args();

	// Get deprecated items for this hook.
	$items = acf_get_store( 'deprecated-hooks' )->query( array( 'replacement' => $hook ) );

	// Loop over results.
	foreach ( $items as $item ) {

		// Extract data.
		extract( $item );

		// Check if anyone is hooked into this deprecated hook.
		if ( has_filter( $deprecated ) ) {

			// Log warning.
			// _deprecated_hook( $deprecated, $version, $hook );

			// Apply filters.
			if ( $type === 'filter' ) {
				$args[0] = apply_filters_ref_array( $deprecated, $args );

				// Or do action.
			} else {
				do_action_ref_array( $deprecated, $args );
			}
		}
	}

	// Return first arg.
	return $args[0];
}

