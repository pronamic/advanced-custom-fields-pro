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

if ( ! class_exists( 'acf_third_party' ) ) :

	/**
	 * ACF 3rd Party Compatibility Class
	 */
	class acf_third_party {

		/**
		 * This function will setup the class functionality
		 *
		 * @since   5.0.0
		 */
		public function __construct() {
			// Tabify Edit Screen - http://wordpress.org/extend/plugins/tabify-edit-screen/
			if ( class_exists( 'Tabify_Edit_Screen' ) ) {
				add_filter( 'tabify_posttypes', array( $this, 'tabify_posttypes' ) );
				add_action( 'tabify_add_meta_boxes', array( $this, 'tabify_add_meta_boxes' ) );
			}

			// Post Type Switcher - http://wordpress.org/extend/plugins/post-type-switcher/
			if ( class_exists( 'Post_Type_Switcher' ) ) {
				add_filter( 'pts_allowed_pages', array( $this, 'pts_allowed_pages' ) );
			}

			// Event Espresso - https://wordpress.org/plugins/event-espresso-decaf/
			if ( function_exists( 'espresso_version' ) ) {
				add_filter( 'acf/get_post_types', array( $this, 'ee_get_post_types' ), 10, 2 );
			}

			// Dark Mode
			if ( class_exists( 'Dark_Mode' ) ) {
				add_action( 'doing_dark_mode', array( $this, 'doing_dark_mode' ) );
			}
		}

		/**
		 * Event Espresso post types do not use the native post.php edit page, but instead render their own.
		 * Show the EE post types in lists where 'show_ui' is used.
		 *
		 * @date    24/2/18
		 * @since   5.6.9
		 *
		 * @param   array $post_types Post types array.
		 * @param   array $args       Other arguments array.
		 * @return  array
		 */
		public function ee_get_post_types( $post_types, $args ) {
			if ( ! empty( $args['show_ui'] ) ) {
				$ee_post_types = get_post_types( array( 'show_ee_ui' => 1 ) );
				$ee_post_types = array_keys( $ee_post_types );
				$post_types    = array_merge( $post_types, $ee_post_types );
				$post_types    = array_unique( $post_types );
			}

			return $post_types;
		}

		/**
		 * This function removes ACF post types from the tabify edit screen (post type selection sidebar)
		 *
		 * @since   3.5.1
		 *
		 * @param   array $posttypes An array of post types supported by tabify.
		 * @return  array
		 */
		public function tabify_posttypes( $posttypes ) {
			// unset ACF post types
			unset( $posttypes['acf-field-group'] );
			unset( $posttypes['acf-field'] );

			return $posttypes;
		}


		/**
		 * This function creates dummy metaboxes on the tabify edit screen page
		 *
		 * @since 3.5.1
		 *
		 * @param string $post_type The name of the displayed post type.
		 */
		public function tabify_add_meta_boxes( $post_type ) {
			// get field groups
			$field_groups = acf_get_field_groups();

			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $field_group ) {

					// vars
					$id    = "acf-{$field_group['key']}";
					$title = 'ACF: ' . acf_esc_html( acf_get_field_group_title( $field_group ) );

					// add meta box
					add_meta_box( $id, $title, '__return_true', $post_type );
				}
			}
		}


		/**
		 * This filter will prevent PTS from running on the field group page
		 *
		 * @since   5.0.0
		 *
		 * @param   array $pages An array of pages PTS should run on.
		 * @return  array
		 */
		public function pts_allowed_pages( $pages ) {

			// vars
			$post_type = '';

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
			// check $_GET because it is too early to use functions / global vars.
			if ( ! empty( $_GET['post_type'] ) ) {
				$post_type = sanitize_text_field( $_GET['post_type'] );
			} elseif ( ! empty( $_GET['post'] ) ) {
				$post_type = get_post_type( $_GET['post'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized when get_post_type() calls get_post().
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			// check post type
			if ( $post_type == 'acf-field-group' ) {
				$pages = array();
			}

			// return
			return $pages;
		}

		/**
		 * Runs during 'admin_enqueue_scripts' if dark mode is enabled
		 *
		 * @since   5.7.3
		 */
		public function doing_dark_mode() {
			$min = defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ? '' : '.min';
			wp_enqueue_style( 'acf-dark', acf_get_url( 'assets/css/acf-dark' . $min . '.css' ), array(), ACF_VERSION );
		}
	}

	new acf_third_party();
endif;
