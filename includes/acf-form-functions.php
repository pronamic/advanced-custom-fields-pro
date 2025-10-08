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
		acf_update_values( $_POST['acf'], $post_id ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by WP when saved.
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}

// Run during generic action.
add_action( 'acf/save_post', '_acf_do_save_post' );
