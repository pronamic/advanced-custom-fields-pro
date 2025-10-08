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
 * The MetaType base class.
 */
class MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = '';

	/**
	 * The prefix to use for ACF reference keys/hidden meta.
	 *
	 * @var string
	 */
	public string $reference_prefix = '_';

	/**
	 * Constructs the location.
	 *
	 * @since 6.4
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Registers the meta location with ACF, so it can be used by
	 * various CRUD helper functions.
	 *
	 * @since 6.4
	 *
	 * @return void
	 */
	public function register() {
		if ( empty( $this->location_type ) ) {
			return;
		}

		$store = acf_get_store( 'acf-meta-locations' );

		if ( ! $store ) {
			$store = acf_register_store( 'acf-meta-locations' );
		}

		$store->set( $this->location_type, get_class( $this ) );
	}

	/**
	 * Retrieves all ACF meta for the provided object ID.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id The ID of the object to get meta from.
	 * @return array
	 */
	public function get_meta( $object_id = 0 ): array {
		$meta     = array();
		$all_meta = get_metadata( $this->location_type, $object_id );

		if ( $all_meta ) {
			foreach ( $all_meta as $key => $value ) {
				// If a reference exists for this value, add it to the meta array.
				if ( isset( $all_meta[ $this->reference_prefix . $key ] ) ) {
					$meta[ $key ]                           = $value[0];
					$meta[ $this->reference_prefix . $key ] = $all_meta[ $this->reference_prefix . $key ][0];
				}
			}
		}

		// Unserialize results and return.
		return array_map( 'acf_maybe_unserialize', $meta );
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
		$meta = get_metadata( $this->location_type, $object_id, $field['name'] );
		return $meta[0] ?? null;
	}

	/**
	 * Gets a reference key for the provided field name.
	 *
	 * @since 6.4
	 *
	 * @param integer|string $object_id  The ID of the object to get the reference key from.
	 * @param string         $field_name The name of the field to get the reference for.
	 * @return string|null
	 */
	public function get_reference( $object_id = 0, $field_name = '' ) {
		$reference = get_metadata( $this->location_type, $object_id, $this->reference_prefix . $field_name );
		return $reference[0] ?? null;
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
		// Slash data. WP expects all data to be slashed and will unslash it (fixes '\' character issues).
		$meta = wp_slash( $meta );

		foreach ( $meta as $name => $value ) {
			update_metadata( $this->location_type, $object_id, $name, $value );
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
		return update_metadata( $this->location_type, $object_id, $field['name'], $value );
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
		return update_metadata( $this->location_type, $object_id, $this->reference_prefix . $field_name, $value );
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
		return delete_metadata( $this->location_type, $object_id, $field['name'] );
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
		return delete_metadata( $this->location_type, $object_id, $this->reference_prefix . $field_name );
	}
}
