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

namespace ACF\Meta;

/**
 * A class to add support for saving to options.
 */
class Option extends MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = 'option';

	/**
	 * Retrieves all ACF meta for the provided object ID.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object to get meta from.
	 * @return array
	 */
	public function get_meta( $object_id = 0 ): array {
		$all_meta = acf_get_option_meta( $object_id );
		$meta     = array();

		foreach ( $all_meta as $key => $value ) {
			// If a reference exists for this value, add it to the meta array.
			if ( isset( $all_meta[ $this->reference_prefix . $key ] ) ) {
				$meta[ $key ]                           = $value[0];
				$meta[ $this->reference_prefix . $key ] = $all_meta[ $this->reference_prefix . $key ][0];
			}
		}

		// Return results.
		return $meta;
	}

	/**
	 * Retrieves a field value from the database.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object the metadata is for.
	 * @param array          $field     The field array.
	 * @return mixed
	 */
	public function get_value( $object_id = 0, array $field = array() ) {
		return get_option( $object_id . '_' . $field['name'], null );
	}

	/**
	 * Gets a reference key for the provided field name.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id  The ID of the object to get the reference key from.
	 * @param string         $field_name The name of the field to get the reference for.
	 * @return string|boolean
	 */
	public function get_reference( $object_id = '', $field_name = '' ) {
		return get_option( $this->reference_prefix . $object_id . '_' . $field_name, null );
	}

	/**
	 * Updates an object ID with the provided meta array.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object the metadata is for.
	 * @param array          $meta      The metadata to save to the object.
	 * @return void
	 */
	public function update_meta( $object_id = 0, array $meta = array() ) {
		$autoload = (bool) acf_get_setting( 'autoload' );

		foreach ( $meta as $name => $value ) {
			$value = wp_unslash( $value );
			update_option( $object_id . '_' . $name, $value, $autoload );
		}
	}

	/**
	 * Updates a field value in the database.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object the metadata is for.
	 * @param array          $field     The field array.
	 * @param mixed          $value     The metadata value.
	 * @return integer|boolean
	 */
	public function update_value( $object_id = 0, array $field = array(), $value = '' ) {
		$value    = wp_unslash( $value );
		$autoload = (bool) acf_get_setting( 'autoload' );

		return update_option( $object_id . '_' . $field['name'], $value, $autoload );
	}

	/**
	 * Updates a reference key in the database.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id  The ID of the object the metadata is for.
	 * @param string         $field_name The name of the field to update the reference for.
	 * @param string         $value      The value of the reference key.
	 * @return integer|boolean
	 */
	public function update_reference( $object_id = 0, string $field_name = '', string $value = '' ) {
		$autoload = (bool) acf_get_setting( 'autoload' );
		return update_option( $this->reference_prefix . $object_id . '_' . $field_name, $value, $autoload );
	}

	/**
	 * Deletes a field value from the database.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object the metadata is for.
	 * @param array          $field     The field array.
	 * @return boolean
	 */
	public function delete_value( $object_id = 0, array $field = array() ): bool {
		return delete_option( $object_id . '_' . $field['name'] );
	}

	/**
	 * Deletes a reference key from the database.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id  The ID of the object the metadata is for.
	 * @param string         $field_name The name of the field to delete the reference from.
	 * @return boolean
	 */
	public function delete_reference( $object_id = 0, string $field_name = '' ): bool {
		return delete_option( $this->reference_prefix . $object_id . '_' . $field_name );
	}
}
