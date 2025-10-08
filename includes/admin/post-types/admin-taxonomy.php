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

if ( ! class_exists( 'ACF_Admin_Taxonomy' ) ) :

	/**
	 * ACF Admin Field Group Class
	 *
	 * All the logic for editing a taxonomy.
	 */
	class ACF_Admin_Taxonomy extends ACF_Admin_Internal_Post_Type {

		/**
		 * The slug for the internal post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $post_type = 'acf-taxonomy';

		/**
		 * The admin body class used for the post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $admin_body_class = 'acf-admin-single-taxonomy';

		/**
		 * This function will customize the message shown when editing a field group
		 *
		 * @since   5.0.0
		 *
		 * @param array $messages Post type messages.
		 * @return array
		 */
		public function post_updated_messages( $messages ) {
			$messages['acf-taxonomy'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => $this->taxonomy_saved_message(),
				2  => __( 'Taxonomy updated.', 'acf' ),
				3  => __( 'Taxonomy deleted.', 'acf' ),
				4  => $this->taxonomy_saved_message(),
				5  => false, // taxonomy does not support revisions.
				6  => $this->taxonomy_saved_message( true ),
				7  => __( 'Taxonomy saved.', 'acf' ),
				8  => __( 'Taxonomy submitted.', 'acf' ),
				9  => __( 'Taxonomy scheduled for.', 'acf' ),
				10 => __( 'Taxonomy draft updated.', 'acf' ),
			);

			return $messages;
		}

		/**
		 * Renders the post type created message.
		 *
		 * @since 6.1
		 *
		 * @param boolean $created True if the post was just created.
		 * @return string
		 */
		public function taxonomy_saved_message( $created = false ) {
			global $post_id;

			$title = get_the_title( $post_id );

			/* translators: %s taxonomy name */
			$item_saved_text = sprintf( __( '%s taxonomy updated', 'acf' ), $title );
			/* translators: %s taxonomy name */
			$add_fields_text = sprintf( __( 'Add fields to %s', 'acf' ), $title );

			if ( $created ) {
				/* translators: %s taxonomy name */
				$item_saved_text = sprintf( __( '%s taxonomy created', 'acf' ), $title );
			}

			$add_fields_link = wp_nonce_url(
				admin_url( 'post-new.php?post_type=acf-field-group&use_taxonomy=' . $post_id ),
				'add-fields-' . $post_id
			);

			$create_taxonomy_link    = admin_url( 'post-new.php?post_type=acf-taxonomy' );
			$duplicate_taxonomy_link = wp_nonce_url(
				admin_url( 'post-new.php?post_type=acf-taxonomy&use_taxonomy=' . $post_id ),
				'acfduplicate-' . $post_id
			);

			$create_post_type_link = wp_nonce_url(
				admin_url( 'post-new.php?post_type=acf-post-type&use_taxonomy=' . $post_id ),
				'create-post-type-' . $post_id
			);

			ob_start(); ?>
			<p class="acf-item-saved-text"><?php echo esc_html( $item_saved_text ); ?></p>
			<div class="acf-item-saved-links">
				<a href="<?php echo esc_url( $add_fields_link ); ?>"><?php esc_html_e( 'Add fields', 'acf' ); ?></a>
				<a class="acf-link-field-groups" href="#"><?php esc_html_e( 'Link field groups', 'acf' ); ?></a>
				<a href="<?php echo esc_url( $create_taxonomy_link ); ?>"><?php esc_html_e( 'Create taxonomy', 'acf' ); ?></a>
				<a href="<?php echo esc_url( $duplicate_taxonomy_link ); ?>"><?php esc_html_e( 'Duplicate taxonomy', 'acf' ); ?></a>
				<a href="<?php echo esc_url( $create_post_type_link ); ?>"><?php esc_html_e( 'Create post type', 'acf' ); ?></a>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Enqueues any scripts necessary for internal post type.
		 *
		 * @since 5.0.0
		 */
		public function admin_enqueue_scripts() {

			wp_enqueue_style( 'acf-field-group' );

			acf_localize_text(
				array(
					'Tag'        => __( 'Tag', 'acf' ),
					'Tags'       => __( 'Tags', 'acf' ),
					'Category'   => __( 'Category', 'acf' ),
					'Categories' => __( 'Categories', 'acf' ),
					'Default'    => __( 'Default', 'acf' ),
				)
			);

			parent::admin_enqueue_scripts();

			do_action( 'acf/taxonomy/admin_enqueue_scripts' );
		}

		/**
		 * Sets up all functionality for the taxonomy edit page to work.
		 *
		 * @since   3.1.8
		 */
		public function admin_head() {

			// global.
			global $post, $acf_taxonomy;

			// set global var.
			$acf_taxonomy = acf_get_internal_post_type( $post->ID, $this->post_type );

			if ( ! empty( $acf_taxonomy['not_registered'] ) ) {
				acf_add_admin_notice(
					__( 'This taxonomy could not be registered because its key is in use by another taxonomy registered by another plugin or theme.', 'acf' ),
					'error'
				);
			}

			// metaboxes.
			add_meta_box( 'acf-basic-settings', __( 'Basic Settings', 'acf' ), array( $this, 'mb_basic_settings' ), 'acf-taxonomy', 'normal', 'high' );
			add_meta_box( 'acf-advanced-settings', __( 'Advanced Settings', 'acf' ), array( $this, 'mb_advanced_settings' ), 'acf-taxonomy', 'normal', 'high' );

			// actions.
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ), 10, 0 );
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 10, 0 );

			// filters.
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 1 );
			add_filter( 'get_user_option_screen_layout_acf-taxonomy', array( $this, 'screen_layout' ), 10, 1 );
			add_filter( 'get_user_option_metaboxhidden_acf-taxonomy', array( $this, 'force_basic_settings' ), 10, 1 );
			add_filter( 'get_user_option_closedpostboxes_acf-taxonomy', array( $this, 'force_basic_settings' ), 10, 1 );
			add_filter( 'get_user_option_closedpostboxes_acf-taxonomy', array( $this, 'force_advanced_settings' ), 10, 1 );

			// 3rd party hook.
			do_action( 'acf/taxonomy/admin_head' );
		}

		/**
		 * This action will allow ACF to render metaboxes after the title.
		 */
		public function edit_form_after_title() {

			// globals.
			global $post;

			// render post data.
			acf_form_data(
				array(
					'screen'        => 'taxonomy',
					'post_id'       => $post->ID,
					'delete_fields' => 0,
					'validation'    => 1,
				)
			);
		}

		/**
		 * This function will add extra HTML to the acf form data element
		 *
		 * @since   5.3.8
		 *
		 * @param array $args Arguments array to pass through to action.
		 * @return void
		 */
		public function form_data( $args ) {
			do_action( 'acf/taxonomy/form_data', $args );
		}

		/**
		 * This function will append extra l10n strings to the acf JS object
		 *
		 * @since   5.3.8
		 *
		 * @param array $l10n The array of translated strings.
		 * @return array $l10n
		 */
		public function admin_l10n( $l10n ) {
			return apply_filters( 'acf/taxonomy/admin_l10n', $l10n );
		}

		/**
		 * Admin footer third party hook support
		 *
		 * @since 5.3.2
		 */
		public function admin_footer() {
			do_action( 'acf/taxonomy/admin_footer' );
		}

		/**
		 * Screen settings html output
		 *
		 * @since   3.6.0
		 *
		 * @param string $html Current screen settings HTML.
		 * @return string $html
		 */
		public function screen_settings( $html ) {
			return $html;
		}

		/**
		 * Sets the "Edit Field Group" screen to use a one-column layout.
		 *
		 * @param integer $columns Number of columns for layout.
		 * @return integer
		 */
		public function screen_layout( $columns = 0 ) {
			return 1;
		}

		/**
		 * Force basic settings to always be visible
		 *
		 * @param  array $hidden_metaboxes The metaboxes hidden on this page.
		 * @return array
		 */
		public function force_basic_settings( $hidden_metaboxes ) {
			if ( ! is_array( $hidden_metaboxes ) ) {
				return $hidden_metaboxes;
			}
			return array_diff( $hidden_metaboxes, array( 'acf-basic-settings' ) );
		}

		/**
		 * Force advanced settings to be visible
		 *
		 * @param  array $hidden_metaboxes The metaboxes hidden on this page.
		 * @return array
		 */
		public function force_advanced_settings( $hidden_metaboxes ) {
			if ( ! is_array( $hidden_metaboxes ) ) {
				return $hidden_metaboxes;
			}
			return array_diff( $hidden_metaboxes, array( 'acf-advanced-settings' ) );
		}

		/**
		 * This function will customize the publish metabox
		 *
		 * @since 5.2.9
		 */
		public function post_submitbox_misc_actions() {
			global $acf_taxonomy;
			$status_label = $acf_taxonomy['active'] ? _x( 'Active', 'post status', 'acf' ) : _x( 'Inactive', 'post status', 'acf' );

			?>
			<script type="text/javascript">
			(function($) {
				$('#post-status-display').html( '<?php echo esc_html( $status_label ); ?>' );
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * Saves taxonomy data.
		 *
		 * @since 1.0.0
		 *
		 * @param  integer $post_id The post ID.
		 * @param  WP_Post $post    The post object.
		 * @return integer $post_id
		 */
		public function save_post( $post_id, $post ) {
			if ( ! $this->verify_save_post( $post_id, $post ) ) {
				return $post_id;
			}

			// Disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Validated in $this->verify_save_post() above.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized when saved.
			$_POST['acf_taxonomy']['ID']    = $post_id;
			$_POST['acf_taxonomy']['title'] = isset( $_POST['acf_taxonomy']['labels']['name'] ) ? $_POST['acf_taxonomy']['labels']['name'] : '';

			if ( ! acf_get_setting( 'enable_meta_box_cb_edit' ) ) {
				$_POST['acf_taxonomy']['meta_box_cb']          = '';
				$_POST['acf_taxonomy']['meta_box_sanitize_cb'] = '';

				if ( ! empty( $_POST['acf_taxonomy']['meta_box'] ) && 'custom' === $_POST['acf_taxonomy']['meta_box'] ) {
					$_POST['acf_taxonomy']['meta_box'] = 'default';
				}

				$existing_post = acf_maybe_unserialize( $post->post_content );

				if ( ! empty( $existing_post['meta_box'] ) ) {
					$_POST['acf_taxonomy']['meta_box'] = $existing_post['meta_box'];
				}

				if ( ! empty( $existing_post['meta_box_cb'] ) ) {
					$_POST['acf_taxonomy']['meta_box_cb'] = $existing_post['meta_box_cb'];
				}

				if ( ! empty( $existing_post['meta_box_sanitize_cb'] ) ) {
					$_POST['acf_taxonomy']['meta_box_sanitize_cb'] = $existing_post['meta_box_sanitize_cb'];
				}
			}

			// Save the taxonomy.
			acf_update_internal_post_type( $_POST['acf_taxonomy'], $this->post_type ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Validated in verify_save_post
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			return $post_id;
		}

		/**
		 * Renders HTML for the 'acf-taxonomy-fields' metabox.
		 *
		 * @since 5.0.0
		 */
		public function mb_basic_settings() {
			global $acf_taxonomy;

			if ( ! acf_is_internal_post_type_key( $acf_taxonomy['key'], 'acf-taxonomy' ) ) {
				$acf_taxonomy['key'] = uniqid( 'taxonomy_' );
			}

			acf_get_view( $this->post_type . '/basic-settings' );
		}


		/**
		 * Renders the HTML for the 'acf-taxonomy-options' metabox.
		 *
		 * @since 5.0.0
		 */
		public function mb_advanced_settings() {
			acf_get_view( $this->post_type . '/advanced-settings' );
		}
	}

	new ACF_Admin_Taxonomy();
endif; // Class exists check.

?>
