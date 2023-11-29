<?php
/**
 * ACF Internal Post Type class
 *
 * Base class to add functionality to ACF internal post types.
 *
 * @package ACF
 * @subpackage Admin
 * @since 6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Admin_Internal_Post_Type' ) ) :

	/**
	 * ACF Internal Post Type class.
	 *
	 * Adds logic to the edit page for ACF internal post types.
	 */
	class ACF_Admin_Internal_Post_Type {

		/**
		 * The slug for the internal post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $post_type = '';

		/**
		 * The admin body class used for the post type.
		 *
		 * @since 6.1
		 * @var string
		 */
		public $admin_body_class = '';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'current_screen' ) );
			add_action( 'save_post_' . $this->post_type, array( $this, 'save_post' ), 10, 2 );
			add_action( 'wp_ajax_acf/link_field_groups', array( $this, 'ajax_link_field_groups' ) );
			add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
			add_filter( 'use_block_editor_for_post_type', array( $this, 'use_block_editor_for_post_type' ), 10, 2 );
		}

		/**
		 * Prevents the block editor from loading when editing an ACF field group.
		 *
		 * @since   5.8.0
		 *
		 * @param bool   $use_block_editor Whether the post type can be edited or not. Default true.
		 * @param string $post_type        The post type being checked.
		 * @return bool
		 */
		public function use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			if ( $post_type === $this->post_type ) {
				return false;
			}

			return $use_block_editor;
		}

		/**
		 * This function will customize the message shown when editing a field group
		 *
		 * @since 5.0.0
		 *
		 * @param array $messages Post type messages.
		 * @return array
		 */
		public function post_updated_messages( $messages ) {
			return $messages;
		}

		/**
		 * This function is fired when loading the admin page before HTML has been rendered.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function current_screen() {
			if ( ! acf_is_screen( $this->post_type ) ) {
				return;
			}

			acf_disable_filters();
			acf_enqueue_scripts();

			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
			add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'acf/input/admin_head', array( $this, 'admin_head' ) );
			add_action( 'acf/input/form_data', array( $this, 'form_data' ) );
			add_action( 'acf/input/admin_footer', array( $this, 'admin_footer' ) );

			add_filter( 'acf/input/admin_l10n', array( $this, 'admin_l10n' ) );
		}

		/**
		 * Modifies the admin body class.
		 *
		 * @since 6.0.0
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			return $classes . ' acf-admin-page acf-internal-post-type ' . esc_attr( $this->admin_body_class );
		}

		/**
		 * Enqueues any scripts necessary for internal post type.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'acf-internal-post-type' );

			wp_dequeue_script( 'autosave' );
			wp_enqueue_style( $this->post_type );
			wp_enqueue_script( $this->post_type );
		}

		/**
		 * Set up functionality for the field group edit page.
		 *
		 * @since 3.1.8
		 *
		 * @return void
		 */
		public function admin_head() {
			// Override as necessary.
		}

		/**
		 * Adds extra HTML to the acf form data element.
		 *
		 *  @since 5.3.8
		 *
		 *  @param array $args Arguments array to pass through to action.
		 *  @return void
		 */
		public function form_data( $args ) {
			// Override as necessary.
		}

		/**
		 * Admin footer third party hook support
		 *
		 * @since 5.3.2
		 *
		 * @return void
		 */
		public function admin_footer() {
			// Override as necessary.
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
			// Override as necessary.
		}

		/**
		 * Ran during the `save_post` hook to verify that the post should be saved.
		 *
		 * @since 6.1
		 *
		 * @param int     $post_id The ID of the post being saved.
		 * @param WP_Post $post    The post object.
		 *
		 * @return bool
		 */
		public function verify_save_post( $post_id, $post ) {
			// Do not save if this is an auto save routine.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return false;
			}

			// Bail early if not an ACF internal post type.
			if ( $post->post_type !== $this->post_type ) {
				return false;
			}

			// Only save once! WordPress saves a revision as well.
			if ( wp_is_post_revision( $post_id ) ) {
				return false;
			}

			// Verify nonce.
			$nonce_name = str_replace(
				array( 'acf-', '-' ),
				array( '', '_' ),
				$this->post_type
			);

			if ( ! acf_verify_nonce( $nonce_name ) ) {
				return false;
			}

			// Bail early if request came from an unauthorised user.
			if ( ! current_user_can( acf_get_setting( 'capability' ) ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Powers the modal for linking field groups to newly-created CPTs/taxonomies.
		 *
		 * @since 6.1
		 *
		 * @return void
		 */
		public function ajax_link_field_groups() {
			// Disable filters to ensure ACF loads raw data from DB.
			acf_disable_filters();

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$args = acf_parse_args(
				$_POST,
				array(
					'nonce'        => '',
					'post_id'      => 0,
					'field_groups' => array(),
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			// Verify nonce and user capability.
			if ( ! wp_verify_nonce( $args['nonce'], 'acf_nonce' ) || ! acf_current_user_can_admin() || ! $args['post_id'] ) {
				die();
			}

			$post_type  = get_post_type( $args['post_id'] );
			$saved_post = acf_get_internal_post_type( $args['post_id'], $post_type );

			// Link the selected field groups.
			if ( is_array( $args['field_groups'] ) && ! empty( $args['field_groups'] ) && $saved_post ) {
				foreach ( $args['field_groups'] as $field_group_id ) {
					$field_group = acf_get_field_group( $field_group_id );

					if ( ! is_array( $field_group ) ) {
						continue;
					}

					if ( 'acf-post-type' === $post_type ) {
						$param = 'post_type';
						$value = $saved_post['post_type'];
					} elseif ( 'acf-taxonomy' === $post_type ) {
						$param = 'taxonomy';
						$value = $saved_post['taxonomy'];
					} else {
						$param = 'options_page';
						$value = $saved_post['menu_slug'];
					}

					$field_group['location'][] = array(
						array(
							'param'    => $param,
							'operator' => '==',
							'value'    => $value,
						),
					);

					acf_update_field_group( $field_group );
				}

				ob_start();
				?>
				<p class="acf-link-successful">
					<?php
					$link_successful_text = _n(
						'Field group linked successfully.',
						'Field groups linked successfully.',
						count( $args['field_groups'] ),
						'acf'
					);
					echo esc_html( $link_successful_text );
					?>
				</p>
				<div class="acf-actions">
					<button type="button" class="acf-btn acf-btn-secondary acf-close-popup"><?php esc_html_e( 'Close Modal', 'acf' ); ?></button>
				</div>
				<?php
				$content = ob_get_clean();
				wp_send_json_success( array( 'content' => $content ) );
			}

			// Render the field group select.
			$field_groups = acf_get_field_groups();
			$choices      = array();

			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $field_group ) {
					if ( ! $field_group['ID'] ) {
						continue;
					}

					$choices[ $field_group['ID'] ] = $field_group['title'];
				}
			}

			$instructions = sprintf(
				/* translators: %s - either "post type" or "taxonomy" */
				__( 'Add this %s to the location rules of the selected field groups.', 'acf' ),
				'acf-post-type' === $post_type ? __( 'post type', 'acf' ) : __( 'taxonomy', 'acf' )
			);

			$field = acf_get_valid_field(
				array(
					'type'         => 'select',
					'name'         => 'acf_field_groups',
					'choices'      => $choices,
					'aria-label'   => __( 'Please select the field groups to link.', 'acf' ),
					'placeholder'  => __( 'Select one or many field groups...', 'acf' ),
					'label'        => __( 'Field Group(s)', 'acf' ),
					'instructions' => $instructions,
					'ui'           => true,
					'multiple'     => true,
					'allow_null'   => true,
				)
			);

			ob_start();
			?>
			<form id="acf-link-field-groups-form">
				<?php acf_render_field_wrap( $field, 'div', 'field' ); ?>
				<div class="acf-actions">
					<button type="button" class="acf-btn acf-btn-secondary acf-close-popup"><?php esc_html_e( 'Cancel', 'acf' ); ?></button>
					<button type="submit" class="acf-btn acf-btn-primary"><?php esc_html_e( 'Done', 'acf' ); ?></button>
				</div>
			</form>
			<?php
			$content = ob_get_clean();

			wp_send_json_success(
				array(
					'content' => $content,
					'title'   => esc_html__( 'Link Existing Field Groups', 'acf' ),
				)
			);
		}
	}

endif; // Class exists check.
