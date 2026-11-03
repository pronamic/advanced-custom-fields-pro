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

// Register store for form data.
acf_register_store( 'form' );

/**
 * acf_set_form_data
 *
 * Sets data about the current form.
 *
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   string $name The store name.
 * @param   array  $data Array of data to start the store with.
 * @return  ACF_Data
 */
function acf_set_form_data( $name = '', $data = false ) {
	return acf_get_store( 'form' )->set( $name, $data );
}

/**
 * acf_get_form_data
 *
 * Gets data about the current form.
 *
 * @date    6/10/13
 * @since   5.0.0
 *
 * @param   string $name The store name.
 * @return  mixed
 */
function acf_get_form_data( $name = '' ) {
	return acf_get_store( 'form' )->get( $name );
}

/**
 * acf_form_data
 *
 * Called within a form to set important information and render hidden inputs.
 *
 * @date    15/10/13
 * @since   5.0.0
 *
 * @param   void
 * @return  void
 */
function acf_form_data( $data = array() ) {

	// Apply defaults.
	$data = wp_parse_args(
		$data,
		array(

			/** @type string The current screen (post, user, taxonomy, etc). */
			'screen'     => 'post',

			/** @type int|string The ID of current post being edited. */
			'post_id'    => 0,

			/** @type bool Enables AJAX validation. */
			'validation' => true,
		)
	);

	// Create nonce using screen.
	$data['nonce'] = wp_create_nonce( $data['screen'] );

	// Append "changed" input used within "_wp_post_revision_fields" action.
	$data['changed'] = 0;

	// Set data.
	acf_set_form_data( $data );

	// Render HTML.
	?>
	<div id="acf-form-data" class="acf-hidden">
		<?php

		// Create hidden inputs from $data
		foreach ( $data as $name => $value ) {
			acf_hidden_input(
				array(
					'id'    => '_acf_' . $name,
					'name'  => '_acf_' . $name,
					'value' => $value,
				)
			);
		}

		/**
		 * Fires within the #acf-form-data element to add extra HTML.
		 *
		 * @date    15/10/13
		 * @since   5.0.0
		 *
		 * @param   array $data The form data.
		 */
		do_action( 'acf/form_data', $data );
		do_action( 'acf/input/form_data', $data );

		?>
	</div>
	<?php
}


/**
 * acf_save_post
 *
 * Saves the $_POST data.
 *
 * @date    15/10/13
 * @since   5.0.0
 *
 * @param   integer|string $post_id The post id.
 * @param   array          $values  An array of values to override $_POST.
 * @return  boolean True if save was successful.
 */
function acf_save_post( $post_id = 0, $values = null ) {

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
	// Override $_POST data with $values.
	if ( $values !== null ) {
		$_POST['acf'] = $values;
	}

	// Bail early if no data to save.
	if ( empty( $_POST['acf'] ) ) {
		return false;
	}

	// Set form data (useful in various filters/actions).
	acf_set_form_data( 'post_id', $post_id );

	// Filter $_POST data for users without the 'unfiltered_html' capability.
	if ( ! acf_allow_unfiltered_html() ) {
		$_POST['acf'] = wp_kses_post_deep( $_POST['acf'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	// Do generic action.
	do_action( 'acf/save_post', $post_id );

	// Return true.
	return true;
}

/**
 * Returns whether ACF is saving from a trusted context, WP-CLI or cron, where saved values come from
 * code rather than from a request.
 *
 * @since 6.8.11
 *
 * @return boolean
 */
function acf_is_trusted_save_context() {
	$trusted = ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron();

	/**
	 * Filters whether ACF is saving from a trusted context. Values saved from a trusted context
	 * aren't permission checked, for example when bidirectional fields update the other side.
	 *
	 * @since 6.8.11
	 *
	 * @param boolean $trusted True for WP-CLI and cron.
	 */
	return (bool) apply_filters( 'acf/is_trusted_save_context', $trusted );
}

/**
 * The internal ACF function to run a callback while saving values submitted through a form or the
 * REST API. Unless the context is trusted, those values are permission checked.
 *
 * @since 6.8.11
 *
 * @param callable $callback The callback that saves the values.
 * @return mixed The callback's return value.
 */
function _acf_save_submitted_values( callable $callback ) {
	$was_saving_submitted = acf_get_data( 'acf_saving_submitted_values' );
	acf_set_data( 'acf_saving_submitted_values', ! acf_is_trusted_save_context() );

	try {
		return $callback();
	} finally {
		acf_set_data( 'acf_saving_submitted_values', $was_saving_submitted );
	}
}

/**
 * _acf_do_save_post
 *
 * Private function hooked into 'acf/save_post' to actually save the $_POST data.
 * This allows developers to hook in before and after ACF has actually saved the data.
 *
 * @date    11/1/19
 * @since   5.7.10
 *
 * @param   integer|string $post_id The post id.
 * @return  void
 */
function _acf_do_save_post( $post_id = 0 ) {

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
	if ( ! empty( $_POST['acf'] ) ) {
		$values = $_POST['acf']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by WP when saved.
		_acf_save_submitted_values(
			function () use ( $values, $post_id ) {
				acf_update_values( $values, $post_id );
			}
		);
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}

// Run during generic action.
add_action( 'acf/save_post', '_acf_do_save_post' );

/**
 * Returns the top-level key a field's input is submitted under, relative to a form's base prefix.
 *
 * Fields rendered with the base prefix submit under their own key. Seamless clone sub fields
 * are rendered with the prefix `{base}[{clone_key}]`, so they submit under the clone's key.
 * Fields nested deeper (repeater rows, group sub fields) don't have a top-level key of their own.
 *
 * @since 6.8.11
 *
 * @param array  $field The field array, before acf_prepare_field() (which can change the key of clone sub fields).
 * @param string $base  The form's base input prefix, such as `acf`, `widget-text[2][acf]` or `menu-item-acf[12]`.
 * @return string The top-level key, or an empty string if the field doesn't submit directly under the base prefix.
 */
function acf_get_field_root_key( $field, $base = 'acf' ) {
	$prefix = $field['prefix'] ?? 'acf';

	if ( ! is_string( $prefix ) || '' === $base ) {
		return '';
	}

	if ( $prefix === $base ) {
		return empty( $field['key'] ) ? '' : (string) $field['key'];
	}

	if ( preg_match( '/^' . preg_quote( $base, '/' ) . '\[([^\]]+)\]$/', $prefix, $matches ) ) {
		return $matches[1];
	}

	return '';
}

/**
 * Returns the nonce action that signs a list of rendered field keys.
 *
 * @since 6.8.11
 * @access private
 *
 * @param string $render_id The render ID the list was rendered for.
 * @param string $csv       The comma separated, sorted field keys.
 * @return string
 */
function _acf_get_field_list_nonce_action( $render_id, $csv ) {
	return 'acf/fields/' . $render_id . '/' . hash( 'sha256', $csv );
}

/**
 * Returns the render state shared by acf_render_fields() and acf_render_field_wrap(), by reference.
 *
 * `depth` counts the nested render calls in progress, so only the outermost call outputs a field
 * list. `render_id` is the render ID of the outermost call, used by inputs that sign themselves for
 * the object (such as the basic uploader nonce).
 *
 * @since 6.8.11
 * @access private
 *
 * @return array
 */
function &_acf_field_render_state() {
	static $state = array(
		'depth'     => 0,
		'render_id' => false,
	);

	return $state;
}

/**
 * Runs a render callback, tracking whether it is the outermost render call.
 *
 * @since 6.8.11
 * @access private
 *
 * @param string|false $render_id The render ID, set for the outermost call only.
 * @param callable     $callback  The callback that renders.
 * @return boolean True if this was the outermost render call.
 */
function _acf_in_field_render( $render_id, $callback ) {
	$state    = &_acf_field_render_state();
	$is_outer = 0 === $state['depth'];

	++$state['depth'];

	if ( $is_outer ) {
		$state['render_id'] = $render_id;
	}

	try {
		$callback();
	} finally {
		--$state['depth'];

		if ( $is_outer ) {
			$state['render_id'] = false;
		}
	}

	return $is_outer;
}

/**
 * Returns the render ID of the fields being rendered, for inputs that sign themselves for the object.
 *
 * Falls back to the post ID set by acf_form_data() when fields are rendered outside acf_render_fields().
 *
 * @since 6.8.11
 *
 * @return string
 */
function acf_get_current_render_id() {
	$state = &_acf_field_render_state();

	if ( is_string( $state['render_id'] ) && '' !== $state['render_id'] ) {
		return $state['render_id'];
	}

	return (string) acf_get_form_data( 'post_id' );
}

/**
 * Outputs the hidden input listing the top-level field keys a render call output.
 *
 * The list is signed for the current user and the render ID (the object the fields were rendered
 * for), so save handlers can tell which submitted keys were rendered without reconstructing the
 * form. It is output outside the field wrappers so conditional logic doesn't disable it. Postboxes
 * hidden by the location rule check disable it with their fields.
 *
 * The signature is a nonce, valid for the same 12–24 hours as `_acf_nonce`. If ACF ever refreshes
 * `_acf_nonce` on long-lived screens, these lists must be refreshed with it.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array  $keys      The top-level keys.
 * @param string $render_id The render ID.
 * @param string $list_name The root name of the input, such as `_acf_fields`.
 * @return void
 */
function _acf_render_field_list( $keys, $render_id, $list_name ) {
	// Commas and pipes delimit the list.
	$keys = array_filter(
		array_unique( array_map( 'strval', $keys ) ),
		function ( $key ) {
			return '' !== $key && strpbrk( $key, ',|' ) === false;
		}
	);

	if ( ! $keys || ! is_string( $render_id ) || '' === $render_id || ! is_string( $list_name ) || '' === $list_name ) {
		return;
	}

	sort( $keys );
	$csv = implode( ',', $keys );

	acf_hidden_input(
		array(
			'name'  => $list_name . '[' . $render_id . '][]',
			'value' => $csv . '|' . wp_create_nonce( _acf_get_field_list_nonce_action( $render_id, $csv ) ),
		)
	);
}

/**
 * Returns the top-level field keys from submitted field lists that verify for any of the given render IDs.
 *
 * A verified key proves the server rendered that field for the current user and object. It is
 * evidence of rendering, not authorisation: the save handler still decides which render IDs and
 * keys it accepts. No field lookups are made, so fields registered late (after the save handler
 * runs) are still accepted.
 *
 * @since 6.8.11
 *
 * @param mixed $lists      The submitted lists, keyed by render ID (usually `$_POST['_acf_fields']`, unslashed).
 * @param array $render_ids The render IDs the save accepts.
 * @return array The verified top-level keys.
 */
function acf_get_verified_field_keys( $lists, array $render_ids ) {
	$keys = array();

	if ( ! is_array( $lists ) ) {
		return $keys;
	}

	foreach ( array_unique( array_map( 'strval', $render_ids ) ) as $render_id ) {
		if ( '' === $render_id || empty( $lists[ $render_id ] ) || ! is_array( $lists[ $render_id ] ) ) {
			continue;
		}

		foreach ( $lists[ $render_id ] as $field_list ) {
			$keys = array_merge( $keys, _acf_get_verified_list_keys( $field_list, $render_id ) );
		}
	}

	return array_values( array_unique( $keys ) );
}

/**
 * Returns the keys of a submitted field list (`{csv}|{nonce}`) if it verifies for the render ID.
 *
 * @since 6.8.11
 * @access private
 *
 * @param mixed  $field_list The submitted list.
 * @param string $render_id  The render ID.
 * @return array The keys, or an empty array if the list doesn't verify.
 */
function _acf_get_verified_list_keys( $field_list, $render_id ) {
	$pos = is_string( $field_list ) ? strrpos( $field_list, '|' ) : false;

	if ( ! $pos ) {
		return array();
	}

	$csv   = substr( $field_list, 0, $pos );
	$nonce = substr( $field_list, $pos + 1 );

	if ( ! wp_verify_nonce( $nonce, _acf_get_field_list_nonce_action( $render_id, $csv ) ) ) {
		return array();
	}

	return explode( ',', $csv );
}

/**
 * Returns the keys a seamless clone sub field may be submitted under when values are keyed per field
 * rather than like $_POST['acf']: its cloned key (`{clone_key}_{field_key}`, as from acf_get_fields())
 * and its original key, which the clone field restores in acf/prepare_field and which the datastore uses.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $field The field array.
 * @return array
 */
function _acf_get_flat_clone_keys( $field ) {
	if ( ( $field['prefix'] ?? 'acf' ) === 'acf' ) {
		return array();
	}

	return array_values( array_filter( array_unique( array( (string) ( $field['key'] ?? '' ), (string) ( $field['__key'] ?? '' ) ) ) ) );
}

/**
 * Returns the submitted keys that belong to a top-level field of a field group located on the object.
 *
 * Keys are matched by top-level input key only. Field names aren't accepted: acf_update_values() looks a
 * name up across every field, so it could save as a different field with the same name.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array   $candidates      The submitted top-level keys to check.
 * @param array   $screen          The location screen args describing the object. Empty matches nothing.
 * @param boolean $flat_clone_keys Whether seamless clone sub fields may also be submitted under their own keys (the datastore).
 * @return array
 */
function _acf_get_located_field_keys( $candidates, $screen, $flat_clone_keys = false ) {
	// An empty screen would match every field group.
	if ( ! $candidates || empty( $screen ) || ! is_array( $screen ) ) {
		return array();
	}

	$located = array();

	foreach ( acf_get_field_groups( $screen ) as $field_group ) {
		foreach ( (array) acf_get_fields( $field_group ) as $field ) {
			$located[ acf_get_field_root_key( $field ) ] = true;

			foreach ( $flat_clone_keys ? _acf_get_flat_clone_keys( $field ) : array() as $key ) {
				$located[ $key ] = true;
			}
		}
	}

	return array_values( array_intersect( $candidates, array_map( 'strval', array_keys( $located ) ) ) );
}

/**
 * Returns the flat keys of the sub fields of verified seamless clones that weren't submitted under the clone's key.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $verified  The verified top-level keys.
 * @param array $submitted The submitted top-level keys.
 * @return array
 */
function _acf_get_verified_flat_clone_keys( $verified, $submitted ) {
	$keys = array();

	foreach ( array_diff( $verified, $submitted ) as $key ) {
		$field = acf_get_field( $key );

		if ( $field && _acf_is_seamless_clone( $field ) ) {
			$keys = array_merge( $keys, _acf_get_seamless_clone_leaf_keys( $field['sub_fields'] ) );
		}
	}

	return $keys;
}

/**
 * Returns true if a field is a seamless clone with sub fields.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $field The field array.
 * @return boolean
 */
function _acf_is_seamless_clone( $field ) {
	return 'clone' === ( $field['type'] ?? '' ) && 'seamless' === ( $field['display'] ?? '' ) && ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] );
}

/**
 * Returns the flat keys of a seamless clone's sub fields, through any seamless clones it clones.
 *
 * acf_get_fields() flattens nested seamless clones to their innermost fields, which is what the datastore sends.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $sub_fields The clone's sub fields.
 * @return array
 */
function _acf_get_seamless_clone_leaf_keys( $sub_fields ) {
	$keys = array();

	foreach ( $sub_fields as $sub_field ) {
		$keys = array_merge( $keys, _acf_is_seamless_clone( $sub_field ) ? _acf_get_seamless_clone_leaf_keys( $sub_field['sub_fields'] ) : _acf_get_flat_clone_keys( $sub_field ) );
	}

	return $keys;
}

/**
 * Normalizes a list of allowed field keys, typically after it has been filtered.
 *
 * Drops non-scalar and empty values, casts to strings and removes duplicates so a
 * misbehaving filter callback can't break array_flip() downstream.
 *
 * @since 6.8.11
 *
 * @param mixed $keys The keys to normalize.
 * @return array
 */
function acf_normalize_field_keys( $keys ) {
	$keys = array_filter( (array) $keys, 'is_scalar' );
	return array_values( array_unique( array_filter( array_map( 'strval', $keys ) ) ) );
}

/**
 * Returns the submitted top-level keys a save may accept for an object.
 *
 * A key is accepted if it is in a verified field list for one of the render IDs (it was rendered
 * for this user and object), or, without a list, if it belongs to a top-level field of a field group
 * whose location rules match the object. Everything else is dropped, and logged when WP_DEBUG is on.
 *
 * Fields hidden only by acf/prepare_field can still be submitted without a list. Conditional logic is
 * not evaluated.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $submitted The submitted top-level keys.
 * @param array $args      See acf_filter_save_values().
 * @return array The allowed top-level keys.
 */
function _acf_get_allowed_save_keys( $submitted, $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'context'         => '',
			'post_id'         => 0,
			'render_ids'      => array(),
			'lists'           => null,
			'screen'          => array(),
			'filter_arg'      => null,
			'flat_clone_keys' => false,
		)
	);

	if ( null === $args['lists'] ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by the caller; each list is verified as a nonce.
		$args['lists'] = isset( $_POST['_acf_fields'] ) ? wp_unslash( $_POST['_acf_fields'] ) : array();
	}

	$submitted = array_values( array_unique( array_map( 'strval', $submitted ) ) );
	$verified  = acf_get_verified_field_keys( $args['lists'], (array) $args['render_ids'] );

	if ( $args['flat_clone_keys'] ) {
		$verified = array_merge( $verified, _acf_get_verified_flat_clone_keys( $verified, $submitted ) );
	}

	$located = _acf_get_located_field_keys( array_values( array_diff( $submitted, $verified ) ), $args['screen'], $args['flat_clone_keys'] );

	/**
	 * Filters the top-level field keys a save is allowed to persist.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to the save context: `post`, `user`,
	 * `taxonomy`, `comment`, `attachment`, `nav_menu`, `nav_menu_item`, `widget`, `options_page`,
	 * `woo_order` or `datastore`.
	 *
	 * Use this to permit additional field keys, for example when inputs are added to a form by
	 * JavaScript, or for fields rendered outside a field group whose location rules match the object.
	 *
	 * @since 6.8.7 For the `user` and `comment` contexts.
	 * @since 6.8.11 For the other contexts.
	 *
	 * @param array                $keys    The allowed top-level keys.
	 * @param integer|string|array $post_id The object being saved. The user or comment ID for the `user` and
	 *                                      `comment` contexts, the options page settings for `options_page`;
	 *                                      the ACF post ID otherwise.
	 */
	$filter_arg = null === $args['filter_arg'] ? $args['post_id'] : $args['filter_arg'];
	$keys       = apply_filters( "acf/form/{$args['context']}/allowed_field_keys", array_merge( $verified, $located ), $filter_arg );

	$keys = acf_normalize_field_keys( $keys );

	_acf_log_dropped_field_keys( $submitted, $keys, $args );

	return $keys;
}

/**
 * Logs dropped keys when WP_DEBUG is on.
 *
 * @since 6.8.11
 * @access private
 *
 * @param array $submitted The submitted top-level keys.
 * @param array $allowed   The allowed top-level keys.
 * @param array $args      The save args.
 * @return void
 */
function _acf_log_dropped_field_keys( $submitted, $allowed, $args ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	$submitted = array_filter(
		$submitted,
		function ( $key ) {
			// Basic uploader nonces are submitted as acf[{field_key}_file_nonce] and are read from $_REQUEST instead.
			return substr( $key, -11 ) !== '_file_nonce';
		}
	);

	$dropped = array_diff( $submitted, $allowed );
	if ( ! $dropped ) {
		return;
	}

	$details = sprintf(
		'context=%s object=%s render_ids=%s user=%d',
		$args['context'],
		is_scalar( $args['post_id'] ) ? $args['post_id'] : '',
		implode( ',', array_map( 'strval', (array) $args['render_ids'] ) ),
		get_current_user_id()
	);

	acf_log( 'ACF: dropped field keys not rendered for this object', $details, 'keys=' . implode( ',', $dropped ) );
}

/**
 * Returns the save args for a post. Previews and autosaves save to a revision, so they are matched as the post they belong to.
 *
 * @since 6.8.11
 * @access private
 *
 * @param WP_Post $post    The post or revision being saved.
 * @param string  $context The save context.
 * @return array See acf_filter_save_values().
 */
function _acf_get_post_save_args( $post, $context ) {
	$parent_id = wp_is_post_revision( $post );
	$parent    = $parent_id ? get_post( $parent_id ) : null;
	$target    = $parent ? $parent : $post;

	return array(
		'context'    => $context,
		'post_id'    => $post->ID,
		'render_ids' => array( $target->ID ),
		'screen'     => array(
			'post_id'   => $target->ID,
			'post_type' => $target->post_type,
		),
	);
}

/**
 * Returns the save args for an attachment.
 *
 * @since 6.8.11
 * @access private
 *
 * @param integer $attachment_id The attachment ID.
 * @return array See acf_filter_save_values().
 */
function _acf_get_attachment_save_args( $attachment_id ) {
	return array(
		'context'    => 'attachment',
		'post_id'    => $attachment_id,
		'render_ids' => array( $attachment_id ),
		'screen'     => array(
			'attachment_id' => $attachment_id,
			'attachment'    => $attachment_id,
		),
	);
}

/**
 * Returns submitted values restricted to the top-level keys a save may accept for an object.
 *
 * Use for saves that pass values to acf_save_post() rather than saving $_POST['acf']. The save args are:
 *
 * - `context` (string): The save context, used in the `acf/form/{$context}/allowed_field_keys` filter.
 * - `post_id` (integer|string): The ACF post ID being saved.
 * - `render_ids` (array): The render IDs whose field lists the save accepts.
 * - `lists` (array|null): The submitted field lists, keyed by render ID. Defaults to `$_POST['_acf_fields']`.
 * - `screen` (array): The location screen args for keys submitted without a list. Empty accepts listed keys only.
 * - `filter_arg` (mixed): The second argument of the filter. Defaults to `post_id`.
 * - `flat_clone_keys` (boolean): Whether seamless clone sub fields may be submitted under their own keys (the datastore).
 *
 * @since 6.8.11
 *
 * @param mixed $values The submitted values, keyed by top-level key.
 * @param array $args   The save args.
 * @return array
 */
function acf_filter_save_values( $values, array $args ) {
	if ( ! is_array( $values ) ) {
		return array();
	}

	return array_intersect_key( $values, array_flip( _acf_get_allowed_save_keys( array_keys( $values ), $args ) ) );
}

/**
 * Restricts $_POST['acf'] and $_FILES['acf'] to the top-level keys a save may accept for an object, in place.
 *
 * Call after verifying the nonce and before acf_validate_save_post(), so dropped keys can't cause
 * validation errors. Also records the render IDs that basic uploader nonces are verified against.
 *
 * @since 6.8.11
 *
 * @param array $args See acf_filter_save_values().
 * @return void
 */
function acf_restrict_save_input( array $args ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified by the caller.
	$values = isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ? $_POST['acf'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Only keys are read here.
	$keys   = array_keys( $values );

	if ( isset( $_FILES['acf']['name'] ) && is_array( $_FILES['acf']['name'] ) ) {
		$keys = array_merge( $keys, array_keys( $_FILES['acf']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only keys are read here.
	}

	$allowed = _acf_get_allowed_save_keys( $keys, $args );

	if ( isset( $_POST['acf'] ) ) {
		$_POST['acf'] = array_intersect_key( $values, array_flip( $allowed ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	acf_restrict_uploaded_field_keys( $allowed );
	acf_set_upload_render_ids( $args['render_ids'] ?? array() );
}

/**
 * Sets the render IDs that basic uploader nonces are verified against in this save.
 *
 * @since 6.8.11
 *
 * @param array $render_ids The render IDs.
 * @return void
 */
function acf_set_upload_render_ids( $render_ids ) {
	acf_set_form_data( 'upload_render_ids', array_values( array_filter( array_map( 'strval', (array) $render_ids ) ) ) );
}

/**
 * Restricts $_FILES['acf'] to the given top-level keys, in place.
 *
 * ACF_Media::save_files() uploads every file in $_FILES['acf'] on each acf/save_post and
 * writes the attachment ID back into $_POST['acf'], so uploads must be restricted to the
 * same keys as $_POST['acf'].
 *
 * @since 6.8.11
 *
 * @param array $allowed_keys The allowed top-level keys.
 * @return void
 */
function acf_restrict_uploaded_field_keys( array $allowed_keys ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by the caller; only filters keys.
	if ( ! isset( $_FILES['acf'] ) ) {
		return;
	}

	if ( ! is_array( $_FILES['acf'] ) ) {
		$_FILES['acf'] = array();
		return;
	}

	$allowed_keys = array_flip( acf_normalize_field_keys( $allowed_keys ) );

	foreach ( $_FILES['acf'] as $attribute => $branches ) {
		$_FILES['acf'][ $attribute ] = is_array( $branches ) ? array_intersect_key( $branches, $allowed_keys ) : array();
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
}

/**
 * Empties $_FILES['acf'] for saves that take their values from an array rather than $_POST['acf'].
 *
 * @since 6.8.11
 *
 * @return void
 */
function acf_discard_uploaded_field_values() {
	if ( isset( $_FILES['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Not reading input.
		$_FILES['acf'] = array();
	}
}
