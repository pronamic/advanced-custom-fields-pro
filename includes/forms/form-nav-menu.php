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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'acf_form_nav_menu' ) ) :

	class acf_form_nav_menu {

		/**
		 * This function will setup the class functionality
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function __construct() {

			// actions
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'wp_update_nav_menu', array( $this, 'update_nav_menu' ) );
			add_action( 'acf/validate_save_post', array( $this, 'acf_validate_save_post' ), 5 );
			add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'wp_nav_menu_item_custom_fields' ), 10, 5 );

			// filters
			add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 10, 3 );
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'wp_edit_nav_menu_walker' ), 10, 2 );
		}


		/**
		 * This action is run after post query but before any admin script / head actions.
		 * It is a good place to register all actions.
		 *
		 * @type    action (admin_enqueue_scripts)
		 * @date    26/01/13
		 * @since   3.6.0
		 *
		 * @param   N/A
		 * @return  N/A
		 */
		function admin_enqueue_scripts() {

			// validate screen
			if ( ! acf_is_screen( 'nav-menus' ) ) {
				return;
			}

			// load acf scripts
			acf_enqueue_scripts();

			// actions
			add_action( 'admin_footer', array( $this, 'admin_footer' ), 1 );
		}


		/**
		 * wp_nav_menu_item_custom_fields
		 *
		 * description
		 *
		 * @date    30/7/18
		 * @since   5.6.9
		 *
		 * @param   type $var Description. Default.
		 * @return  type Description.
		 */
		function wp_nav_menu_item_custom_fields( $item_id, $item, $depth, $args, $id = '' ) {

			// vars
			$prefix = "menu-item-acf[$item_id]";

			// get field groups
			$field_groups = acf_get_field_groups(
				array(
					'nav_menu_item'       => $item->type,
					'nav_menu_item_id'    => $item_id,
					'nav_menu_item_depth' => $depth,
				)
			);

			// render
			if ( ! empty( $field_groups ) ) {

				// open
				echo '<div class="acf-menu-item-fields acf-fields -clear">';

				// loop
				foreach ( $field_groups as $field_group ) {

					// load fields
					$fields = acf_get_fields( $field_group );

					// bail if not fields
					if ( empty( $fields ) ) {
						continue;
					}

					// change prefix
					acf_prefix_fields( $fields, $prefix );

					// render
					acf_render_fields( $fields, $item_id, 'div', $field_group['instruction_placement'], array( 'prefix' => $prefix ) );
				}

				// close
				echo '</div>';

				// Trigger append for newly created menu item (via AJAX)
				if ( acf_is_ajax( 'add-menu-item' ) ) : ?>
			<script type="text/javascript">
			(function($) {
				acf.doAction('append', $('#menu-item-settings-<?php echo esc_attr( $item_id ); ?>') );
			})(jQuery);
			</script>
					<?php
			endif;
			}
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    26/5/17
		 * @since   5.6.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function update_nav_menu( $menu_id ) {

			// vars
			$post_id = 'term_' . $menu_id;

			// verify and remove nonce
			if ( ! acf_verify_nonce( 'nav_menu' ) ) {
				return $menu_id;
			}

			// Restrict submitted values to fields rendered for the menu and its items, or that apply to them (before validation).
			$this->restrict_save_input( $menu_id );

			// validate and show errors
			acf_validate_save_post( true );

			// save
			acf_save_post( $post_id );

			// Menu items don't submit files; only the menu's own fields use $_FILES['acf'].
			acf_discard_uploaded_field_values();

			// save nav menu items
			$this->update_nav_menu_items( $menu_id );
		}

		/**
		 * Restricts the menu's $_POST['acf'] and $_FILES['acf'] values, and each item's
		 * $_POST['menu-item-acf'] values, to the fields rendered for them or that apply to them.
		 *
		 * Each menu item is rendered for its own ID, so an item only accepts its own field list.
		 *
		 * @since 6.8.11
		 *
		 * @param integer $menu_id The menu ID.
		 * @return void
		 */
		private function restrict_save_input( $menu_id ) {
			$menu_id = (int) $menu_id;

			acf_restrict_save_input(
				array(
					'context'    => 'nav_menu',
					'post_id'    => "term_{$menu_id}",
					'render_ids' => array( "term_{$menu_id}" ),
					'screen'     => array( 'nav_menu' => $menu_id ),
				)
			);

			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Verified in update_nav_menu(); sanitized when saved.
			if ( ! isset( $_POST['menu-item-acf'] ) ) {
				return;
			}

			$submitted = is_array( $_POST['menu-item-acf'] ) ? $_POST['menu-item-acf'] : array();
			$lists     = isset( $_POST['_acf_fields'] ) && is_array( $_POST['_acf_fields'] ) ? wp_unslash( $_POST['_acf_fields'] ) : array();
			$items     = array();
			// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			// The menu item location rule reads the menu being edited from this data, as while the menu editor renders.
			$previous_menu_id = acf_get_data( 'nav_menu_id' );
			acf_set_data( 'nav_menu_id', $menu_id );

			try {
				foreach ( $this->get_nav_menu_item_ids( $submitted ) as $item_id ) {
					$screen = $this->get_nav_menu_item_screen( $item_id );

					// Only save to IDs that are menu items.
					if ( ! $screen ) {
						continue;
					}

					$items[ $item_id ] = acf_filter_save_values(
						$submitted[ $item_id ],
						array(
							'context'    => 'nav_menu_item',
							'post_id'    => $item_id,
							'render_ids' => array( $item_id ),
							'lists'      => $lists,
							'screen'     => $screen,
						)
					);
				}
			} finally {
				acf_set_data( 'nav_menu_id', $previous_menu_id );
			}

			$_POST['menu-item-acf'] = $items;
		}

		/**
		 * Returns the menu item IDs among the keys of submitted menu item values, and primes their caches.
		 *
		 * update_nav_menu_items() saves to the submitted keys, so only keys that are item IDs are returned.
		 *
		 * @since 6.8.11
		 *
		 * @param array $submitted The submitted menu item values, keyed by item ID.
		 * @return integer[]
		 */
		private function get_nav_menu_item_ids( $submitted ) {
			$item_ids = array_values(
				array_filter(
					array_keys( $submitted ),
					function ( $item_id ) {
						return is_int( $item_id ) && $item_id > 0;
					}
				)
			);

			_prime_post_caches( $item_ids, false, true );

			return $item_ids;
		}

		/**
		 * Returns the location screen args a menu item is rendered with, or false if the ID isn't a menu item.
		 *
		 * As in wp_nav_menu_item_custom_fields(), the menu isn't part of the screen: the menu item rule reads
		 * it from the `nav_menu_id` data, so rules for the menu itself don't match its items.
		 *
		 * @since 6.8.11
		 *
		 * @param integer $item_id The menu item ID.
		 * @return array|false
		 */
		private function get_nav_menu_item_screen( $item_id ) {
			if ( ! $item_id || get_post_type( $item_id ) !== 'nav_menu_item' ) {
				return false;
			}

			// Walk up the item's parents to find its depth, as the menu editor's walker does.
			$depth  = 0;
			$seen   = array( $item_id => true );
			$parent = (int) get_post_meta( $item_id, '_menu_item_menu_item_parent', true );
			while ( $parent && ! isset( $seen[ $parent ] ) ) {
				$seen[ $parent ] = true;
				++$depth;
				$parent = (int) get_post_meta( $parent, '_menu_item_menu_item_parent', true );
			}

			return array(
				'nav_menu_item'       => (string) get_post_meta( $item_id, '_menu_item_type', true ),
				'nav_menu_item_id'    => $item_id,
				'nav_menu_item_depth' => $depth,
			);
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    26/5/17
		 * @since   5.6.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function update_nav_menu_items( $menu_id ) {

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
			if ( empty( $_POST['menu-item-acf'] ) ) {
				return;
			}

			$posted_values = acf_sanitize_request_args( $_POST['menu-item-acf'] );

			foreach ( $posted_values as $post_id => $values ) {
				acf_save_post( $post_id, $values );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}


		/**
		 * wp_get_nav_menu_items
		 *
		 * WordPress does not provide an easy way to find the current menu being edited.
		 * This function listens to when a menu's items are loaded and stores the menu.
		 * Needed on nav-menus.php page for new menu with no items
		 *
		 * @date    23/2/18
		 * @since   5.6.9
		 *
		 * @param   type $var Description. Default.
		 * @return  type Description.
		 */
		function wp_get_nav_menu_items( $items, $menu, $args ) {
			acf_set_data( 'nav_menu_id', $menu->term_id );
			return $items;
		}

		/**
		 * Called when WP renders a menu edit form.
		 * Used to set global data and customize the Walker class.
		 *
		 * @date    26/5/17
		 * @since   5.6.0
		 *
		 * @param   string  $class   The walker class to use. Default 'Walker_Nav_Menu_Edit'.
		 * @param   integer $menu_id ID of the menu being rendered.
		 * @return  string
		 */
		function wp_edit_nav_menu_walker( $class, $menu_id = 0 ) {

			// update data (needed for ajax location rules to work)
			acf_set_data( 'nav_menu_id', $menu_id );

			// Return class.
			return $class;
		}


		/**
		 * This function will loop over $_POST data and validate
		 *
		 * @type    action 'acf/validate_save_post' 5
		 * @date    7/09/2016
		 * @since   5.4.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function acf_validate_save_post() {

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
			if ( empty( $_POST['menu-item-acf'] ) ) {
				return;
			}

			$posted_values = acf_sanitize_request_args( $_POST['menu-item-acf'] );

			foreach ( $posted_values as $post_id => $values ) {

				// vars
				$prefix = 'menu-item-acf[' . $post_id . ']';

				// validate
				acf_validate_values( $values, $prefix );
			}
			// phpcs:enable // phpcs:disable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * This function will add some custom HTML to the footer of the edit page
		 *
		 * @type    function
		 * @date    11/06/2014
		 * @since   5.0.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function admin_footer() {

			// vars
			$nav_menu_id = acf_get_data( 'nav_menu_id' );
			$post_id     = 'term_' . $nav_menu_id;

			// get field groups
			$field_groups = acf_get_field_groups(
				array(
					'nav_menu' => $nav_menu_id,
				)
			);

			?>
<div id="tmpl-acf-menu-settings" style="display: none;">
			<?php

			// data (always needed to save nav menu items)
			acf_form_data(
				array(
					'screen'  => 'nav_menu',
					'post_id' => $post_id,
					'ajax'    => 1,
				)
			);

			// render
			if ( ! empty( $field_groups ) ) {

				// loop
				foreach ( $field_groups as $field_group ) {
					$fields = acf_get_fields( $field_group );

					echo '<div class="acf-menu-settings -' . esc_attr( $field_group['style'] ) . '">';

					echo '<h2>' . acf_esc_html( acf_get_field_group_title( $field_group ) ) . '</h2>';

					echo '<div class="acf-fields -left -clear">';

					acf_render_fields( $fields, $post_id, 'div', $field_group['instruction_placement'] );

					echo '</div>';

					echo '</div>';
				}
			}

			?>
</div>
<script type="text/javascript">
(function($) {
	
	// append html
	var html = $('#tmpl-acf-menu-settings').html();
	$('#tmpl-acf-menu-settings').remove();
	$('#post-body-content').append( html );
	
	
	// avoid WP over-writing $_POST data
	// - https://core.trac.wordpress.org/ticket/41502#ticket
	$(document).on('submit', '#update-nav-menu', function() {

		// vars
		var $form = $(this);
		var $input = $('input[name="nav-menu-data"]');
		
		
		// decode json
		var json = $form.serializeArray();
		var json2 = [];
		
		
		// loop
		$.each( json, function( i, pair ) {
			
			// avoid nesting (unlike WP)
			if( pair.name === 'nav-menu-data' ) return;
			
			
			// bail early if is 'acf[' input
			if( pair.name.indexOf('acf[') > -1 ) return;
			
			
			// field lists use `[]` names, which nav-menu-data can't expand
			if( pair.name.indexOf('_acf_fields[') === 0 ) return;
						
			
			// append
			json2.push( pair );
			
		});
		
		
		// update
		$input.val( JSON.stringify(json2) );
		
	});
		
		
})(jQuery);	
</script>
			<?php
		}
	}

	acf_new_instance( 'acf_form_nav_menu' );
endif;
?>
