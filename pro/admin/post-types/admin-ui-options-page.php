<?php
/**
 * ACF Admin Post Type Class
 *
 * @class ACF_Admin_Post_Type
 *
 * @package    ACF
 * @subpackage Admin
 */
if ( ! class_exists( 'ACF_Admin_UI_Options_Page' ) ) :

	/**
	 * ACF Admin UI Options Page Class
	 *
	 * All the logic for editing an options page in the UI.
	 */
	class ACF_Admin_UI_Options_Page extends ACF_Admin_Internal_Post_Type {

		/**
		 * The slug for the internal post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $post_type = 'acf-ui-options-page';

		/**
		 * The admin body class used for the post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $admin_body_class = 'acf-admin-single-options-page';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			add_action( 'wp_ajax_acf/create_options_page', array( $this, 'ajax_create_options_page' ) );
			parent::__construct();
		}

		/**
		 * This function will customize the message shown when editing a post type.
		 *
		 * @since 5.0.0
		 *
		 * @param array $messages Post type messages.
		 * @return array
		 */
		public function post_updated_messages( $messages ) {
			$messages['acf-ui-options-page'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => $this->options_page_created_message(), // Updated.
				2  => $this->options_page_created_message(),
				3  => __( 'Options page deleted.', 'acf' ),
				4  => __( 'Options page updated.', 'acf' ),
				5  => false, // Post type does not support revisions.
				6  => $this->options_page_created_message( true ), // Created.
				7  => __( 'Options page saved.', 'acf' ),
				8  => __( 'Options page submitted.', 'acf' ),
				9  => __( 'Options page scheduled for.', 'acf' ),
				10 => __( 'Options page draft updated.', 'acf' ),
			);

			return $messages;
		}

		/**
		 * Renders the options page created message.
		 *
		 * @since 6.1
		 *
		 * @param bool $created True if the options page was just created.
		 * @return string
		 */
		public function options_page_created_message( $created = false ) {
			global $post_id;

			$title = get_the_title( $post_id );

			/* translators: %s options page name */
			$item_saved_text = sprintf( __( '%s options page updated', 'acf' ), $title );
			/* translators: %s options page name */
			$add_fields_text = sprintf( __( 'Add fields to %s', 'acf' ), $title );

			if ( $created ) {
				/* translators: %s options page name */
				$item_saved_text = sprintf( __( '%s options page created', 'acf' ), $title );
			}

			$add_fields_link = wp_nonce_url(
				admin_url( 'post-new.php?post_type=acf-field-group&use_options_page=' . $post_id ),
				'add-fields-' . $post_id
			);

			ob_start();
			?>
			<p class="acf-item-saved-text"><?php echo esc_html( $item_saved_text ); ?></p>
			<div class="acf-item-saved-links">
				<a href="<?php echo esc_url( $add_fields_link ); ?>"><?php echo esc_html( $add_fields_text ); ?></a>
				<a class="acf-link-field-groups" href="#"><?php esc_html_e( 'Link existing field groups', 'acf' ); ?></a>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Enqueues any scripts necessary for internal post type.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_style( 'acf-field-group' );

			acf_localize_text(
				array(
					'Post'    => __( 'Post', 'acf' ),
					'Posts'   => __( 'Posts', 'acf' ),
					'Page'    => __( 'Page', 'acf' ),
					'Pages'   => __( 'Pages', 'acf' ),
					'Default' => __( 'Default', 'acf' ),
				)
			);

			parent::admin_enqueue_scripts();

			do_action( 'acf/ui_options_page/admin_enqueue_scripts' );
		}

		/**
		 * Sets up all functionality for the post type edit page to work.
		 *
		 * @since   3.1.8
		 *
		 * @return  void
		 */
		public function admin_head() {
			// global.
			global $post, $acf_ui_options_page;

			// set global var.
			$acf_ui_options_page = acf_get_internal_post_type( $post->ID, $this->post_type );

			// metaboxes.
			add_meta_box( 'acf-basic-settings', __( 'Basic Settings', 'acf' ), array( $this, 'mb_basic_settings' ), 'acf-ui-options-page', 'normal', 'high' );
			add_meta_box( 'acf-advanced-settings', __( 'Advanced Settings', 'acf' ), array( $this, 'mb_advanced_settings' ), 'acf-ui-options-page', 'normal', 'high' );

			// actions.
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ), 10, 0 );
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 10, 0 );

			// filters.
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 1 );
			add_filter( 'get_user_option_screen_layout_acf-ui-options-page', array( $this, 'screen_layout' ), 10, 1 );
			add_filter( 'get_user_option_metaboxhidden_acf-ui-options-page', array( $this, 'force_basic_settings' ), 10, 1 );
			add_filter( 'get_user_option_closedpostboxes_acf-ui-options-page', array( $this, 'force_basic_settings' ), 10, 1 );
			add_filter( 'get_user_option_closedpostboxes_acf-ui-options-page', array( $this, 'force_advanced_settings' ), 10, 1 );

			// 3rd party hook.
			do_action( 'acf/ui_options_page/admin_head' );
		}

		/**
		 * This action will allow ACF to render metaboxes after the title.
		 *
		 * @return void
		 */
		public function edit_form_after_title() {

			// globals.
			global $post;

			// render post data.
			acf_form_data(
				array(
					'screen'        => 'ui_options_page',
					'post_id'       => $post->ID,
					'delete_fields' => 0,
					'validation'    => 1,
				)
			);
		}

		/**
		 * This function will add extra HTML to the acf form data element
		 *
		 *  @since   5.3.8
		 *
		 *  @param array $args Arguments array to pass through to action.
		 *  @return void
		 */
		public function form_data( $args ) {
			do_action( 'acf/ui_options_page/form_data', $args );
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
			return apply_filters( 'acf/ui_options_page/admin_l10n', $l10n );
		}

		/**
		 * Admin footer third party hook support
		 *
		 * @since   5.3.2
		 *
		 * @return void
		 */
		public function admin_footer() {
			do_action( 'acf/ui_options_page/admin_footer' );
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
		 * Sets the "Edit Post Type" screen to use a one-column layout.
		 *
		 * @param int $columns Number of columns for layout.
		 *
		 * @return int
		 */
		public function screen_layout( $columns = 0 ) {
			return 1;
		}

		/**
		 * Force basic settings to always be visible
		 *
		 * @param array $hidden_metaboxes The metaboxes hidden on this page.
		 *
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
		 * @param array $hidden_metaboxes The metaboxes hidden on this page.
		 *
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
		 * @since   5.2.9
		 *
		 * @return void
		 */
		public function post_submitbox_misc_actions() {
			global $acf_ui_options_page;

			$status_label = $acf_ui_options_page['active'] ? _x( 'Active', 'post status', 'acf' ) : _x( 'Inactive', 'post status', 'acf' );
			?>
			<script type="text/javascript">
				(function($) {
					$('#post-status-display').html( '<?php echo esc_html( $status_label ); ?>' );
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Saves post type data.
		 *
		 * @since 1.0.0
		 *
		 * @param int     $post_id The post ID.
		 * @param WP_Post $post    The post object.
		 *
		 * @return int $post_id
		 */
		public function save_post( $post_id, $post ) {
			if ( ! $this->verify_save_post( $post_id, $post ) ) {
				return $post_id;
			}

			// Disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Validated in $this->verify_save_post() above.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized when saved.
			$_POST['acf_ui_options_page']['ID']    = $post_id;
			$_POST['acf_ui_options_page']['title'] = isset( $_POST['acf_ui_options_page']['page_title'] ) ? $_POST['acf_ui_options_page']['page_title'] : '';

			// Save the post type.
			acf_update_internal_post_type( $_POST['acf_ui_options_page'], $this->post_type ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Validated in verify_save_post
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			return $post_id;
		}

		/**
		 * Renders HTML for the basic settings metabox.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function mb_basic_settings() {
			global $acf_ui_options_page;

			if ( ! acf_is_internal_post_type_key( $acf_ui_options_page['key'], 'acf-ui-options-page' ) ) {
				$acf_ui_options_page['key'] = uniqid( 'ui_options_page_' );
			}

			acf_get_view( dirname( __FILE__ ) . '/../views/acf-ui-options-page/basic-settings.php' );
		}


		/**
		 * Renders the HTML for the advanced settings metabox.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function mb_advanced_settings() {
			acf_get_view( dirname( __FILE__ ) . '/../views/acf-ui-options-page/advanced-settings.php' );
		}

		/**
		 * Iterates through the registered options pages and finds eligible parent pages.
		 *
		 * @since 6.2
		 *
		 * @param string $menu_slug Optional menu_slug of an existing options page.
		 * @return array
		 */
		public static function get_parent_page_choices( $current_slug = '' ) {
			global $menu;
			$acf_all_options_pages   = acf_get_options_pages();
			$acf_parent_page_choices = array( 'None' => array( 'none' => __( 'No Parent', 'acf' ) ) );
			if ( is_array( $acf_all_options_pages ) ) {
				foreach ( $acf_all_options_pages as $options_page ) {
					// Can't assign to child pages.
					if ( ! empty( $options_page['parent_slug'] ) ) {
						continue;
					}

					// Can't be a child of itself.
					if ( $current_slug === $options_page['menu_slug'] ) {
						continue;
					}

					$acf_parent_menu_slug = ! empty( $options_page['menu_slug'] ) ? $options_page['menu_slug'] : '';

					// ACF overrides the `menu_slug` of parent pages with one child so they redirect to the child.
					if ( ! empty( $options_page['_menu_slug'] ) ) {
						$acf_parent_menu_slug = $options_page['_menu_slug'];
					}

					$acf_parent_page_choices['acfOptionsPages'][ $acf_parent_menu_slug ] = ! empty( $options_page['page_title'] ) ? $options_page['page_title'] : $options_page['menu_slug'];
				}
			}

			foreach ( $menu as $item ) {
				if ( ! empty( $item[0] ) ) {
					$page_name      = $item[0];
					$markup         = '/<[^>]+>.*<\/[^>]+>/';
					$sanitized_name = preg_replace( $markup, '', $page_name );

					// Can't be a child of itself.
					if ( $current_slug === $item[2] ) {
						continue;
					}

					// Ensure that the current item is not an ACF page or that ACF pages are an empty array before adding to others.
					if ( ! empty( $acf_parent_page_choices['acfOptionsPages'] ) && ! in_array( $page_name, $acf_parent_page_choices['acfOptionsPages'], true ) || empty( $acf_parent_page_choices['acfOptionsPages'] ) ) {
						// If matched menu slug is not in the list add it to others.
						$acf_parent_page_choices['Others'][ $item[2] ] = acf_esc_html( $sanitized_name );
					}
				}
			}
			return $acf_parent_page_choices;
		}

		/**
		 * Creates a simple options page over AJAX.
		 *
		 * @since 6.2
		 * @return void
		 */
		public function ajax_create_options_page() {
			// Disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$args = acf_parse_args(
				$_POST,
				array(
					'nonce'                   => '',
					'post_id'                 => 0,
					'acf_ui_options_page'     => array(),
					'field_group_title'       => '',
					'acf_parent_page_choices' => array(),
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			// Verify nonce and user capability.
			if ( ! wp_verify_nonce( $args['nonce'], 'acf_nonce' ) || ! acf_current_user_can_admin() || ! $args['post_id'] ) {
				die();
			}

			// Process form data.
			if ( ! empty( $args['acf_ui_options_page'] ) ) {
				// Prepare for save.
				$options_page           = acf_validate_internal_post_type( $args['acf_ui_options_page'], 'acf-ui-options-page' );
				$options_page['key']    = uniqid( 'ui_options_page_' );
				$options_page['title']  = ! empty( $args['acf_ui_options_page']['page_title'] ) ? $args['acf_ui_options_page']['page_title'] : '';
				$existing_options_pages = acf_get_options_pages();

				// Check for duplicates.
				if ( ! empty( $existing_options_pages ) ) {
					foreach ( $existing_options_pages as $existing_options_page ) {
						if ( $existing_options_page['menu_slug'] === $options_page['menu_slug'] ) {
							wp_send_json_error(
								array(
									'error' => __( 'The provided Menu Slug already exists.', 'acf' ),
								)
							);
						}
					}
				}

				// Save the options page.
				acf_update_internal_post_type( $options_page, 'acf-ui-options-page' );

				wp_send_json_success(
					array(
						'page_title' => esc_html( $options_page['page_title'] ),
						'menu_slug'  => esc_attr( $options_page['menu_slug'] ),
					)
				);
			}

			// Render the form.
			ob_start();
			acf_get_view(
				dirname( __FILE__ ) . '/../views/acf-ui-options-page/create-options-page-modal.php',
				array(
					'field_group_title'       => $args['field_group_title'],
					'acf_parent_page_choices' => $args['acf_parent_page_choices'],
				)
			);
			$content = ob_get_clean();

			wp_send_json_success(
				array(
					'content' => $content,
					'title'   => esc_html__( 'Add New Options Page', 'acf' ),
				)
			);
		}

	}

	new ACF_Admin_UI_Options_Page();
endif; // Class exists check.
