<?php
/**
 * ACF Admin Field Group Class
 *
 *  @class       acf_admin_field_group
 *
 *  @package     ACF
 *  @subpackage  Admin
 */

if ( ! class_exists( 'acf_admin_field_group' ) ) :

	/**
	 *  ACF Admin Field Group Class
	 *
	 *  All the logic for editing a field group
	 */
	class acf_admin_field_group extends ACF_Admin_Internal_Post_Type {

		/**
		 * The slug for the internal post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $post_type = 'acf-field-group';

		/**
		 * The admin body class used for the post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $admin_body_class = 'acf-admin-single-field-group';

		/**
		 * Constructs the class.
		 *
		 * @since   5.0.0
		 *
		 * @return  void
		 */
		public function __construct() {
			parent::__construct();

			add_action( 'wp_ajax_acf/field_group/render_field_settings', array( $this, 'ajax_render_field_settings' ) );
			add_action( 'wp_ajax_acf/field_group/render_location_rule', array( $this, 'ajax_render_location_rule' ) );
			add_action( 'wp_ajax_acf/field_group/move_field', array( $this, 'ajax_move_field' ) );
		}

		/**
		 *  This function will customize the message shown when editing a field group
		 *
		 *  @since   5.0.0
		 *
		 *  @param array $messages Post type messages.
		 *  @return array
		 */
		public function post_updated_messages( $messages ) {
			$messages['acf-field-group'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Field group updated.', 'acf' ),
				2  => __( 'Field group updated.', 'acf' ),
				3  => __( 'Field group deleted.', 'acf' ),
				4  => __( 'Field group updated.', 'acf' ),
				5  => false, // field group does not support revisions.
				6  => __( 'Field group published.', 'acf' ),
				7  => __( 'Field group saved.', 'acf' ),
				8  => __( 'Field group submitted.', 'acf' ),
				9  => __( 'Field group scheduled for.', 'acf' ),
				10 => __( 'Field group draft updated.', 'acf' ),
			);

			return $messages;
		}

		/**
		 * Enqueues any scripts necessary for internal post type.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			parent::admin_enqueue_scripts();

			acf_localize_text(
				array(
					'The string "field_" may not be used at the start of a field name' => __( 'The string "field_" may not be used at the start of a field name', 'acf' ),
					'This field cannot be moved until its changes have been saved' => __( 'This field cannot be moved until its changes have been saved', 'acf' ),
					'Field group title is required'     => __( 'Field group title is required', 'acf' ),
					'Move field group to trash?'        => __( 'Move field group to trash?', 'acf' ),
					'No toggle fields available'        => __( 'No toggle fields available', 'acf' ),
					'Move Custom Field'                 => __( 'Move Custom Field', 'acf' ),
					'Close modal'                       => __( 'Close modal', 'acf' ),
					'Field moved to other group'        => __( 'Field moved to other group', 'acf' ),
					'Field groups linked successfully.' => __( 'Field groups linked successfully.', 'acf' ),
					'Checked'                           => __( 'Checked', 'acf' ),
					'(no label)'                        => __( '(no label)', 'acf' ),
					'(this field)'                      => __( '(this field)', 'acf' ),
					'copy'                              => __( 'copy', 'acf' ),
					'or'                                => __( 'or', 'acf' ),
					'Show this field group if'          => __( 'Show this field group if', 'acf' ),
					'Null'                              => __( 'Null', 'acf' ),
					'PRO Only'                          => __( 'PRO Only', 'acf' ),

					// Conditions.
					'Has any value'                     => __( 'Has any value', 'acf' ),
					'Has no value'                      => __( 'Has no value', 'acf' ),
					'Value is equal to'                 => __( 'Value is equal to', 'acf' ),
					'Value is not equal to'             => __( 'Value is not equal to', 'acf' ),
					'Value matches pattern'             => __( 'Value matches pattern', 'acf' ),
					'Value contains'                    => __( 'Value contains', 'acf' ),
					'Value is greater than'             => __( 'Value is greater than', 'acf' ),
					'Value is less than'                => __( 'Value is less than', 'acf' ),
					'Selection is greater than'         => __( 'Selection is greater than', 'acf' ),
					'Selection is less than'            => __( 'Selection is less than', 'acf' ),

					// Custom Select2 templates.
					'Type to search...'                 => __( 'Type to search...', 'acf' ),
					'This Field'                        => __( 'This Field', 'acf' ),
				)
			);

			acf_localize_data(
				array(
					'fieldTypes'          => acf_get_field_types_info(),
					'fieldCategoriesL10n' => acf_get_field_categories_i18n(),
					'PROUpgradeURL'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'field-type-selection' ),
					'PROFieldTypes'       => acf_get_pro_field_types(),
					'PROLocationTypes'    => array(
						'block'        => __( 'Block', 'acf' ),
						'options_page' => __( 'Options Page', 'acf' ),
					),
				)
			);

			do_action( 'acf/field_group/admin_enqueue_scripts' );
		}

		/**
		 * Set up functionality for the field group edit page.
		 *
		 * @since 3.1.8
		 *
		 * @return void
		 */
		public function admin_head() {
			global $post, $field_group;

			// Set global var.
			$field_group = acf_get_field_group( $post->ID );

			// metaboxes.
			add_meta_box( 'acf-field-group-fields', __( 'Fields', 'acf' ), array( $this, 'mb_fields' ), 'acf-field-group', 'normal', 'high' );
			add_meta_box( 'acf-field-group-options', __( 'Settings', 'acf' ), array( $this, 'mb_options' ), 'acf-field-group', 'normal', 'high' );

			// actions.
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ), 10, 0 );
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 10, 0 );

			// filters.
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 1 );
			add_filter( 'get_user_option_screen_layout_acf-field-group', array( $this, 'screen_layout' ), 10, 1 );

			// 3rd party hook.
			do_action( 'acf/field_group/admin_head' );
		}

		/**
		 * This action will allow ACF to render metaboxes after the title.
		 *
		 * @return void
		 */
		public function edit_form_after_title() {
			global $post;

			// Render post data.
			acf_form_data(
				array(
					'screen'        => 'field_group',
					'post_id'       => $post->ID,
					'delete_fields' => 0,
					'validation'    => 0,
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
			do_action( 'acf/field_group/form_data', $args );
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
			return apply_filters( 'acf/field_group/admin_l10n', $l10n );
		}

		/**
		 * Admin footer third party hook support
		 *
		 * @since   5.3.2
		 *
		 * @return void
		 */
		public function admin_footer() {
			$this->include_pro_features();
			do_action( 'acf/field_group/admin_footer' );
		}

		/**
		 * Renders HTML for the ACF PRO features upgrade notice.
		 *
		 * @return void
		 */
		public function include_pro_features() {
			// Bail if on PRO.
			if ( acf_is_pro() && acf_pro_is_license_active() ) {
				return;
			}

			// Bail if not the edit field group screen.
			if ( ! acf_is_screen( 'acf-field-group' ) ) {
				return;
			}

			acf_get_view( 'acf-field-group/pro-features' );
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
			$show_field_keys          = acf_get_user_setting( 'show_field_keys' ) ? 'checked="checked"' : '';
			$show_field_settings_tabs = acf_get_user_setting( 'show_field_settings_tabs', true ) ? 'checked="checked"' : '';
			$hide_field_settings_tabs = apply_filters( 'acf/field_group/disable_field_settings_tabs', false );

			$html .= '<div id="acf-append-show-on-screen" class="acf-hidden">';
			$html .= '<label for="acf-field-key-hide"><input id="acf-field-key-hide" type="checkbox" value="1" name="show_field_keys" ' . $show_field_keys . ' /> ' . __( 'Field Keys', 'acf' ) . '</label>';

			if ( ! $hide_field_settings_tabs ) {
				$html .= '<label for="acf-field-settings-tabs"><input id="acf-field-settings-tabs" type="checkbox" value="1" name="show_field_settings_tabs" ' . $show_field_settings_tabs . ' />' . __( 'Field Settings Tabs', 'acf' ) . '</label>';
			}

			$html .= '</div>';

			return $html;
		}

		/**
		 * Sets the "Edit Field Group" screen to use a one-column layout.
		 *
		 * @param int $columns Number of columns for layout.
		 *
		 * @return int
		 */
		public function screen_layout( $columns = 0 ) {
			return 1;
		}

		/**
		 * This function will customize the publish metabox
		 *
		 * @since   5.2.9
		 *
		 * @return void
		 */
		public function post_submitbox_misc_actions() {
			global $field_group;
			$status_label = $field_group['active'] ? _x( 'Active', 'post status', 'acf' ) : _x( 'Inactive', 'post status', 'acf' );

			?>
			<script type="text/javascript">
			(function($) {
				$('#post-status-display').html( '<?php echo esc_html( $status_label ); ?>' );
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * Saves field group data.
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

			// disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// save fields.
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Validated by WordPress.
			if ( ! empty( $_POST['acf_fields'] ) ) {

				// loop.
				foreach ( $_POST['acf_fields'] as $field ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized when saved.

					if ( ! isset( $field['key'] ) ) {
						continue;
					}

					// vars.
					$specific = false;
					$save     = acf_extract_var( $field, 'save' );

					// only saved field if has changed.
					if ( $save == 'meta' ) {
						$specific = array(
							'menu_order',
							'post_parent',
						);
					}

					// set parent.
					if ( ! $field['parent'] ) {
						$field['parent'] = $post_id;
					}

					// save field.
					acf_update_field( $field, $specific );
				}
			}

			// delete fields.
			if ( $_POST['_acf_delete_fields'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.

				// clean.
				$ids = explode( '|', $_POST['_acf_delete_fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
				$ids = array_map( 'intval', $ids );

				// loop.
				foreach ( $ids as $id ) {

					// bai early if no id.
					if ( ! $id ) {
						continue;
					}

					// delete.
					acf_delete_field( $id );
				}
			}

			$_POST['acf_field_group']['ID'] = $post_id;
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized when saved.
			$_POST['acf_field_group']['title'] = $_POST['post_title'];

			// save field group.
			acf_update_field_group( $_POST['acf_field_group'] );
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			return $post_id;
		}

		/**
		 * This function will render the HTML for the metabox 'acf-field-group-fields'
		 *
		 * @since   5.0.0
		 *
		 * @return  void
		 */
		public function mb_fields() {
			global $field_group;

			$view = array(
				'fields' => acf_get_fields( $field_group ),
				'parent' => 0,
			);

			acf_get_view( $this->post_type . '/fields', $view );
		}

		/**
		 * This function will render the HTML for the metabox 'acf-field-group-pro-features'
		 *
		 * @since 6.0.0
		 *
		 * @return void
		 */
		public function mb_pro_features() {
			acf_get_view( $this->post_type . '/pro-features' );
		}

		/**
		 * This function will render the HTML for the metabox 'acf-field-group-options'
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function mb_options() {
			global $field_group;

			// Field group key (leave in for compatibility).
			if ( ! acf_is_field_group_key( $field_group['key'] ) ) {
				$field_group['key'] = uniqid( 'group_' );
			}

			acf_get_view( $this->post_type . '/options' );
		}

		/**
		 * This function can be accessed via an AJAX action and will return the result from the render_location_value function
		 *
		 * @since   5.0.0
		 *
		 * @return void
		 */
		public function ajax_render_location_rule() {
			// validate.
			if ( ! acf_verify_ajax() ) {
				die();
			}

			// verify user capability.
			if ( ! acf_current_user_can_admin() ) {
				die();
			}

			// validate rule.
			$rule = acf_validate_location_rule( acf_sanitize_request_args( $_POST['rule'] ) );

			acf_get_view(
				'acf-field-group/location-rule',
				array(
					'rule' => $rule,
				)
			);

			die();
		}

		/**
		 * This function will return HTML containing the field's settings based on it's new type
		 *
		 * @since   5.0.0
		 *
		 * @return void
		 */
		public function ajax_render_field_settings() {
			// Verify the current request.
			if ( ! acf_verify_ajax() || ! acf_current_user_can_admin() ) {
				wp_send_json_error();
			}

			// Make sure we have a field.
			$field = acf_maybe_get_POST( 'field' );
			if ( ! $field ) {
				wp_send_json_error();
			}

			$field['prefix'] = acf_maybe_get_POST( 'prefix' );
			$field           = acf_get_valid_field( $field );
			$tabs            = acf_get_combined_field_type_settings_tabs();
			$tab_keys        = array_keys( $tabs );
			$sections        = array();

			foreach ( $tab_keys as $tab ) {
				ob_start();

				if ( 'general' === $tab ) {
					// Back-compat for fields not using tab-specific hooks.
					do_action( "acf/render_field_settings/type={$field['type']}", $field );
				}

				do_action( "acf/field_group/render_field_settings_tab/{$tab}/type={$field['type']}", $field );
				do_action( "acf/render_field_{$tab}_settings/type={$field['type']}", $field );

				$sections[ $tab ] = ob_get_clean();
			}

			wp_send_json_success( $sections );
		}

		/**
		 * Move field AJAX function
		 *
		 * @since   5.0.0
		 *
		 * @return void No return, HTML output for AJAX consumption.
		 */
		public function ajax_move_field() {
			// disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$args = acf_parse_args(
				$_POST,
				array(
					'nonce'          => '',
					'post_id'        => 0,
					'field_id'       => 0,
					'field_group_id' => 0,
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			// verify nonce.
			if ( ! wp_verify_nonce( $args['nonce'], 'acf_nonce' ) ) {
				die();
			}

			// verify user capability.
			if ( ! acf_current_user_can_admin() ) {
				die();
			}

			// confirm?
			if ( $args['field_id'] && $args['field_group_id'] ) {

				// vars.
				$field       = acf_get_field( $args['field_id'] );
				$field_group = acf_get_field_group( $args['field_group_id'] );

				// update parent.
				$field['parent'] = $field_group['ID'];

				// remove conditional logic.
				$field['conditional_logic'] = 0;

				// update field.
				acf_update_field( $field );

				// Output HTML.
				$link = '<a href="' . admin_url( 'post.php?post=' . $field_group['ID'] . '&action=edit' ) . '" target="_blank">' . esc_html( $field_group['title'] ) . '</a>';

				echo '' .
					'<p><strong>' . __( 'Move Complete.', 'acf' ) . '</strong></p>' .
					'<p>' . sprintf(
						/* translators: Confirmation message once a field has been moved to a different field group. */
						acf_punctify( __( 'The %1$s field can now be found in the %2$s field group', 'acf' ) ),
						esc_html( $field['label'] ),
						$link  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					) . '</p>' .
					'<a href="#" class="button button-primary acf-close-popup">' . __( 'Close Modal', 'acf' ) . '</a>';
				die();
			}

			// get all field groups.
			$field_groups = acf_get_field_groups();
			$choices      = array();

			// check.
			if ( ! empty( $field_groups ) ) {

				// loop.
				foreach ( $field_groups as $field_group ) {

					// bail early if no ID.
					if ( ! $field_group['ID'] ) {
						continue;
					}

					// bail early if is current.
					if ( $field_group['ID'] == $args['post_id'] ) {
						continue;
					}

					$choices[ $field_group['ID'] ] = $field_group['title'];
				}
			}

			// render options.
			$field = acf_get_valid_field(
				array(
					'type'       => 'select',
					'name'       => 'acf_field_group',
					'choices'    => $choices,
					'aria-label' => __( 'Please select the destination for this field', 'acf' ),
				)
			);

			echo '<p>' . __( 'Please select the destination for this field', 'acf' ) . '</p>';

			echo '<form id="acf-move-field-form">';

				// render.
				acf_render_field_wrap( $field );

				echo '<button type="submit" class="acf-btn">' . __( 'Move Field', 'acf' ) . '</button>';

			echo '</form>';

			die();
		}
	}

	// initialize.
	new acf_admin_field_group();
endif; // Class exists check.

?>
