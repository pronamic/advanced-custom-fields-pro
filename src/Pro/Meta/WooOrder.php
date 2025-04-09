<?php
/**
 * Adds support for saving/retrieving values from post meta.
 *
 * @package    AdvancedCustomFields
 * @subpackage Meta
 * @author     WP Engine
 */

namespace ACF\Pro\Meta;

use ACF\Meta\MetaLocation;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * A class to add support for saving to WooCommerce order meta.
 */
class WooOrder extends MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = 'woo_order';

	/**
	 * Constructs the location.
	 *
	 * @since 6.4
	 */
	public function __construct() {
		add_filter( 'acf/decode_post_id', array( $this, 'decode_woo_order_id' ), 10, 2 );
		parent::__construct();
	}

	/**
	 * Checks numerical post IDs to see if they belong to a WC order.
	 *
	 * @since 6.4
	 *
	 * @param array          $decoded The decoded post ID props.
	 * @param integer|string $post_id The original post ID.
	 * @return array
	 */
	public function decode_woo_order_id( $decoded, $post_id ) {
		// Bail if not a standard numeric post ID.
		if ( ! is_numeric( $post_id ) || empty( $decoded['type'] ) || 'post' !== $decoded['type'] ) {
			return $decoded;
		}

		// Bail if HPOS isn't enabled (traditional ACF meta methods work otherwise).
		if ( ! method_exists( OrderUtil::class, 'custom_orders_table_usage_is_enabled' ) ||
			! OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return $decoded;
		}

		if ( 'shop_order_placehold' === get_post_type( $post_id ) ) {
			$decoded['type'] = 'woo_order';
		}

		return $decoded;
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
		$meta  = array();
		$order = wc_get_order( $object_id );

		if ( ! $order ) {
			return $meta;
		}

		$all_meta     = $order->get_meta_data();
		$field_names  = wp_list_pluck( $all_meta, 'key' );
		$field_values = wp_list_pluck( $all_meta, 'value' );

		foreach ( $field_names as $key => $field_name ) {
			$reference     = $this->reference_prefix . $field_name;
			$reference_key = array_search( $reference, $field_names, true );

			if ( false !== $reference_key ) {
				$meta[ $field_name ] = $field_values[ $key ];
				$meta[ $reference ]  = $field_values[ $reference_key ];
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
		$order = wc_get_order( $object_id );

		if ( ! $order || ! $order->meta_exists( $field['name'] ) ) {
			return null;
		}

		return $order->get_meta( $field['name'], true, 'edit' );
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
		$order = wc_get_order( $object_id );
		$key   = $this->reference_prefix . $field_name;

		if ( ! $order || ! $order->meta_exists( $key ) ) {
			return null;
		}

		return $order->get_meta( $key, true, 'edit' );
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
		$order = wc_get_order( $object_id );

		if ( ! $order ) {
			return;
		}

		foreach ( $meta as $name => $value ) {
			$value = wp_unslash( $value );
			$order->update_meta_data( $name, $value );
		}

		$order->save();
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
		$order = wc_get_order( $object_id );

		if ( ! $order ) {
			return false;
		}

		$value = wp_unslash( $value );

		$order->update_meta_data( $this->reference_prefix . $field['name'], $field['key'] );
		$order->update_meta_data( $field['name'], $value );

		return $order->save();
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
		// Updated in update_value().
		return true;
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
		$order = wc_get_order( $object_id );

		if ( ! $order ) {
			return false;
		}

		$order->delete_meta_data( $this->reference_prefix . $field['name'] );
		$order->delete_meta_data( $field['name'] );

		return $order->save();
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
		// Deleted in delete_value().
		return true;
	}
}
