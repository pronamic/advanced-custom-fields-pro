<?php
/**
 * Adds ACF functionality to WooCommerce HPOS order pages.
 *
 * @package    ACF
 * @subpackage Pro\Forms
 * @author     WP Engine
 */

namespace ACF\Pro\Forms;

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Adds ACF metaboxes to the new WooCommerce order screen.
 */
class WC_Order {

	/**
	 * Constructs the ACF_Form_WC_Order class.
	 *
	 * @since 6.4
	 */
	public function __construct() {
		add_action( 'load-woocommerce_page_wc-orders', array( $this, 'initialize' ) );
		add_action( 'load-woocommerce_page_wc-orders--shop_subscription', array( $this, 'initialize' ) );
		add_action( 'woocommerce_update_order', array( $this, 'save_order' ), 10, 1 );
	}

	/**
	 * Enqueues ACF scripts on the WooCommerce order page and
	 * registers actions specific to that page.
	 *
	 * @since 6.4
	 *
	 * @return void
	 */
	public function initialize() {
		acf_enqueue_scripts( array( 'uploader' => true ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
	}

	/**
	 * Adds ACF metaboxes to the WooCommerce Order pages.
	 *
	 * @since 6.4
	 *
	 * @param string   $post_type The current post type.
	 * @param \WP_Post $post      The WP_Post object or the WC_Order object.
	 * @return void
	 */
	public function add_meta_boxes( $post_type, $post ) {
		// Storage for localized postboxes.
		$postboxes = array();

		$location = 'shop_order';
		$order    = ( $post instanceof \WP_Post ) ? wc_get_order( $post->ID ) : $post;
		$screen   = $this->is_hpos_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';

		if ( $order instanceof \WC_Subscription ) {
			$location = 'shop_subscription';
			$screen   = function_exists( 'wcs_get_page_screen_id' ) ? wcs_get_page_screen_id( 'shop_subscription' ) : 'shop_subscription';
		}

		// Get field groups for this screen.
		$field_groups = acf_get_field_groups(
			array(
				'post_id'   => $order->get_id(),
				'post_type' => $location,
			)
		);

		// Loop over field groups.
		if ( $field_groups ) {
			foreach ( $field_groups as $field_group ) {
				$id       = "acf-{$field_group['key']}"; // acf-group_123
				$title    = $field_group['title'];       // Group 1
				$context  = $field_group['position'];    // normal, side, acf_after_title
				$priority = 'core';                      // high, core, default, low

				// Allow field groups assigned to after title to still be rendered.
				if ( 'acf_after_title' === $context ) {
					$context = 'normal';
				}

				/**
				 * Filters the metabox priority.
				 *
				 * @since 6.4
				 *
				 * @param string $priority    The metabox priority (high, core, default, low).
				 * @param array  $field_group The field group array.
				 */
				$priority = apply_filters( 'acf/input/meta_box_priority', $priority, $field_group );

				// Localize data
				$postboxes[] = array(
					'id'    => $id,
					'key'   => $field_group['key'],
					'style' => $field_group['style'],
					'label' => $field_group['label_placement'],
					'edit'  => acf_get_field_group_edit_link( $field_group['ID'] ),
				);

				// Add the meta box.
				add_meta_box(
					$id,
					acf_esc_html( $title ),
					array( $this, 'render_meta_box' ),
					$screen,
					$context,
					$priority,
					array( 'field_group' => $field_group )
				);
			}

			// Localize postboxes.
			acf_localize_data(
				array(
					'postboxes' => $postboxes,
				)
			);
		}

		// Removes the WordPress core "Custom Fields" meta box.
		if ( acf_get_setting( 'remove_wp_meta_box' ) ) {
			remove_meta_box( 'order_custom', $screen, 'normal' );
		}

		// Add hidden input fields.
		add_action( 'order_edit_form_top', array( $this, 'order_edit_form_top' ) );

		/**
		 * Fires after metaboxes have been added.
		 *
		 * @date    13/12/18
		 * @since   5.8.0
		 *
		 * @param string   $post_type    The post type.
		 * @param \WP_Post $post         The post being edited.
		 * @param array    $field_groups The field groups added.
		 */
		do_action( 'acf/add_meta_boxes', $post_type, $post, $field_groups );
	}

	/**
	 * Renders hidden fields.
	 *
	 * @since 6.4
	 *
	 * @param \WC_Order $order The WooCommerce order object.
	 * @return void
	 */
	public function order_edit_form_top( $order ) {
		// Render post data.
		acf_form_data(
			array(
				'screen'  => 'post',
				'post_id' => 'woo_order_' . $order->get_id(),
			)
		);
	}

	/**
	 * Renders the ACF metabox HTML.
	 *
	 * @since 6.4
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Can be a standard \WP_Post object or the \WC_Order object.
	 * @param array              $metabox       The add_meta_box() args.
	 * @return  void
	 */
	public function render_meta_box( $post_or_order, $metabox ) {
		$order       = ( $post_or_order instanceof \WP_Post ) ? wc_get_order( $post_or_order->ID ) : $post_or_order;
		$field_group = $metabox['args']['field_group'];

		// Render fields.
		$fields = acf_get_fields( $field_group );
		acf_render_fields( $fields, 'woo_order_' . $order->get_id(), 'div', $field_group['instruction_placement'] );
	}

	/**
	 * Checks if WooCommerce HPOS is enabled.
	 *
	 * @since 6.4.2
	 *
	 * @return boolean
	 */
	public function is_hpos_enabled(): bool {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return true;
		}

		return false;
	}

	/**
	 * Saves ACF fields to the current order.
	 *
	 * @since 6.4
	 *
	 * @param integer $order_id The order ID.
	 * @return void
	 */
	public function save_order( int $order_id ) {
		// Bail if not using HPOS to prevent a double-save.
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Remove the action to prevent an infinite loop via $order->save().
		remove_action( 'woocommerce_update_order', array( $this, 'save_order' ), 10 );
		acf_save_post( 'woo_order_' . $order_id );
	}
}
