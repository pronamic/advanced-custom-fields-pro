<?php
/**
 * ACF Internal Post Type List class
 *
 * Base class to add functionality to ACF internal post type list pages.
 *
 * @package ACF
 * @subpackage Admin
 * @since 6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Admin_Internal_Post_Type_List' ) ) :

	class ACF_Admin_Internal_Post_Type_List {

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
		 * Array of post objects available for sync.
		 *
		 * @since 5.9.0
		 * @var array
		 */
		public $sync = array();

		/**
		 * The current view (post_status).
		 *
		 * @since 5.9.0
		 * @var string
		 */
		public $view = '';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = '';

		/**
		 * If this is a pro feature or not.
		 *
		 * @var boolean
		 */
		public $is_pro_feature = false;

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'current_screen' ) );
			add_action( 'admin_footer', array( $this, 'include_pro_features' ) );

			// Handle post status change events.
			add_action( 'trashed_post', array( $this, 'trashed_post' ) );
			add_action( 'untrashed_post', array( $this, 'untrashed_post' ) );
			add_action( 'deleted_post', array( $this, 'deleted_post' ) );
		}

		/**
		 * Renders HTML for the ACF PRO features upgrade notice.
		 */
		public function include_pro_features() {
			// Bail if not the edit screen
			if ( ! acf_is_screen( 'edit-' . $this->post_type ) ) {
				return;
			}

			// Bail if on PRO.
			if ( acf_is_pro() && acf_pro_is_license_active() ) {
				return;
			}

			acf_get_view( 'acf-field-group/pro-features' );
		}

		/**
		 * Add any menu items required for post types.
		 *
		 * @since 6.1
		 */
		public function admin_menu() {}

		/**
		 * Returns the admin URL for the current post type edit page.
		 *
		 * @date    27/3/20
		 * @since   5.9.0
		 *
		 * @param   string $params Extra URL params.
		 * @return  string
		 */
		public function get_admin_url( $params = '' ) {
			if ( isset( $_GET['paged'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.
				$params .= '&paged=' . intval( $_GET['paged'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.
			}
			return admin_url( "edit.php?post_type={$this->post_type}{$params}" );
		}

		/**
		 * Returns the post type admin URL taking into account the current view.
		 *
		 * @date    27/3/20
		 * @since   5.9.0
		 *
		 * @param   string $params Extra URL params.
		 * @return  string
		 */
		public function get_current_admin_url( $params = '' ) {
			return $this->get_admin_url( ( $this->view ? '&post_status=' . $this->view : '' ) . $params );
		}

		/**
		 * Constructor for all ACF internal post type admin list pages.
		 *
		 * @since   5.0.0
		 */
		public function current_screen() {
			// Bail early if not the list admin page.
			if ( ! acf_is_screen( "edit-{$this->post_type}" ) ) {
				return;
			}

			// Get the current view.
			$this->view = acf_request_arg( 'post_status', '' );

			// Setup and check for custom actions.
			$this->setup_sync();
			$this->check_sync();
			$this->check_duplicate();
			$this->check_activate();
			$this->check_deactivate();

			// Modify publish post status text and order.
			global $wp_post_statuses;
			$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'acf' );
			$wp_post_statuses['trash']                = acf_extract_var( $wp_post_statuses, 'trash' );

			// Add hooks.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
			add_filter( "views_edit-{$this->post_type}", array( $this, 'admin_table_views' ), 10, 1 );
			add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'admin_table_columns' ), 10, 1 );
			add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'admin_table_columns_html' ), 10, 2 );
			add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
			add_filter( "bulk_actions-edit-{$this->post_type}", array( $this, 'admin_table_bulk_actions' ), 10, 1 );
			add_action( 'admin_footer', array( $this, 'admin_footer' ), 1 );

			if ( $this->view !== 'trash' ) {
				add_filter( 'page_row_actions', array( $this, 'page_row_actions' ), 10, 2 );
			}

			// Add hooks for "sync" view.
			if ( $this->view === 'sync' ) {
				add_action( 'admin_footer', array( $this, 'admin_footer__sync' ), 1 );
			}

			do_action( 'acf/internal_post_type_list/current_screen', $this->post_type );
		}

		/**
		 * Sets up the field groups ready for sync.
		 *
		 * @since   5.9.0
		 */
		public function setup_sync() {
			// Review local json files.
			if ( acf_get_local_json_files( $this->post_type ) ) {

				// Get all posts in a single cached query to check if sync is available.
				$all_posts = acf_get_internal_post_type_posts( $this->post_type );
				foreach ( $all_posts as $post ) {

					// Extract vars.
					$local    = acf_maybe_get( $post, 'local' );
					$modified = acf_maybe_get( $post, 'modified' );
					$private  = acf_maybe_get( $post, 'private' );

					// Ignore if is private.
					if ( $private ) {
						continue;

						// Ignore not local "json".
					} elseif ( $local !== 'json' ) {
						continue;

						// Append to sync if not yet in database.
					} elseif ( ! $post['ID'] ) {
						$this->sync[ $post['key'] ] = $post;

						// Append to sync if "json" modified time is newer than database.
					} elseif ( $modified && $modified > get_post_modified_time( 'U', true, $post['ID'] ) ) {
						$this->sync[ $post['key'] ] = $post;
					}
				}
			}
		}

		/**
		 * Enqueues admin scripts.
		 *
		 * @since   5.9.0
		 */
		public function admin_enqueue_scripts() {
			acf_enqueue_script( 'acf' );

			acf_localize_text(
				array(
					'Review local JSON changes' => esc_html__( 'Review local JSON changes', 'acf' ),
					'Loading diff'              => esc_html__( 'Loading diff', 'acf' ),
					'Sync changes'              => esc_html__( 'Sync changes', 'acf' ),
					'Please activate your ACF PRO license to edit this options page.' => esc_html__( 'Please activate your ACF PRO license to edit this options page.', 'acf' ),
					'Please activate your ACF PRO license to edit field groups assigned to an ACF Block.' => esc_html__( 'Please activate your ACF PRO license to edit field groups assigned to an ACF Block.', 'acf' ),
				)
			);
		}

		/**
		 * Modifies the admin body class.
		 *
		 * @date    18/4/20
		 * @since   5.9.0
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			$classes .= ' acf-admin-page acf-internal-post-type ' . esc_attr( $this->admin_body_class );

			if ( $this->view ) {
				$classes .= ' view-' . esc_attr( $this->view );
			}

			return apply_filters( 'acf/internal_post_type_list/admin_body_classes', $classes, $this->post_type );
		}

		/**
		 * Returns the disabled post state HTML.
		 *
		 * @since 5.9.0
		 *
		 * @return string
		 */
		public function get_disabled_post_state() {
			return '<span class="dashicons dashicons-hidden"></span> ' . _x( 'Inactive', 'post status', 'acf' );
		}

		/**
		 * Returns the registration error state.
		 *
		 * @since 6.1
		 *
		 * @return string
		 */
		public function get_registration_error_state() {
			return '<span class="acf-js-tooltip dashicons dashicons-warning" title="' .
			__( 'This item could not be registered because its key is in use by another item registered by another plugin or theme.', 'acf' ) .
			'"></span> ' . _x( 'Registration Failed', 'post status', 'acf' );
		}

		/**
		 * Adds the "disabled" post state for the admin table title.
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param array   $post_states An array of post display states.
		 * @param WP_Post $post        The current post object.
		 * @return array
		 */
		public function display_post_states( $post_states, $post ) {
			if ( $post->post_status === 'acf-disabled' ) {
				$post_states['acf-disabled'] = $this->get_disabled_post_state();
			}

			// Check the post store to see if this failed registration.
			if ( ! empty( $this->store ) && ! empty( $post->ID ) ) {
				$store = acf_get_store( $this->store );

				if ( $store ) {
					$store_item = $store->get( $post->ID );
					if ( ! empty( $store_item ) && ! empty( $store_item['not_registered'] ) ) {
						$post_states['acf-registration-warning'] = $this->get_registration_error_state();
					}
				}
			}

			return $post_states;
		}

		/**
		 * Get the HTML for when there are no post objects found.
		 *
		 * @since 6.0.0
		 *
		 * @return string
		 */
		public function get_not_found_html() {
			ob_start();

			$view = $this->post_type . '/list-empty';

			if ( $this->is_pro_feature ) {
				$view = ACF_PATH . 'pro/admin/views/' . $view . '.php';
			}

			acf_get_view( $view );

			return ob_get_clean();
		}

		/**
		 * Customizes the admin table columns.
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param   array $_columns The columns array.
		 * @return  array
		 */
		public function admin_table_columns( $_columns ) {
			return $_columns;
		}

		/**
		 * Renders the admin table column HTML
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param   string  $column_name The name of the column to display.
		 * @param   integer $post_id     The current post ID.
		 * @return  void
		 */
		public function admin_table_columns_html( $column_name, $post_id ) {
			$post = acf_get_internal_post_type( $post_id, $this->post_type );

			if ( $post ) {
				$this->render_admin_table_column( $column_name, $post );
			}
		}

		/**
		 * Renders a specific admin table column.
		 *
		 * @date    17/4/20
		 * @since   5.9.0
		 *
		 * @param string $column_name The name of the column to display.
		 * @param array  $post        The main ACF post array.
		 * @return void
		 */
		public function render_admin_table_column( $column_name, $post ) {}

		/**
		 * Returns a human-readable file location.
		 *
		 * @date    17/4/20
		 * @since   5.9.0
		 *
		 * @param   string $file The full file path.
		 * @return  string
		 */
		public function get_human_readable_file_location( $file ) {
			// Generate friendly file path.
			$theme_path = get_stylesheet_directory();
			if ( strpos( $file, $theme_path ) !== false ) {
				$rel_file = str_replace( $theme_path, '', $file );
				$located  = sprintf( __( 'Located in theme: %s', 'acf' ), $rel_file );
			} elseif ( strpos( $file, WP_PLUGIN_DIR ) !== false ) {
				$rel_file = str_replace( WP_PLUGIN_DIR, '', $file );
				$located  = sprintf( __( 'Located in plugin: %s', 'acf' ), $rel_file );
			} else {
				$rel_file = str_replace( ABSPATH, '', $file );
				$located  = sprintf( __( 'Located in: %s', 'acf' ), $rel_file );
			}

			return $located;
		}

		/**
		 * Displays the local JSON status of an ACF post.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param array $post The main ACF post array.
		 * @return void
		 */
		public function render_admin_table_column_local_status( $post ) {
			$json = acf_get_local_json_files( $this->post_type );
			if ( isset( $json[ $post['key'] ] ) ) {
				if ( isset( $this->sync[ $post['key'] ] ) ) {
					$url = $this->get_admin_url( '&acfsync=' . $post['key'] . '&_wpnonce=' . wp_create_nonce( 'bulk-posts' ) );
					echo '<strong>' . esc_html__( 'Sync available', 'acf' ) . '</strong>';
					if ( $post['ID'] ) {
						echo '<div class="row-actions">
                            <span class="sync"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Sync', 'acf' ) . '</a> | </span>
                            <span class="review"><a href="#" data-event="review-sync" data-id="' . esc_attr( $post['ID'] ) . '" data-href="' . esc_url( $url ) . '">' . esc_html__( 'Review changes', 'acf' ) . '</a></span>
                        </div>';
					} else {
						echo '<div class="row-actions">
                            <span class="sync"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Import', 'acf' ) . '</a></span>
                        </div>';
					}
				} else {
					echo esc_html__( 'Saved', 'acf' );
				}
			} else {
				echo '<span class="acf-secondary-text">' . esc_html__( 'Awaiting save', 'acf' ) . '</span>';
			}
		}

		/**
		 * Customizes the page row actions visible on hover.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param   array   $actions The array of actions HTML.
		 * @param   WP_Post $post    The post.
		 * @return  array
		 */
		public function page_row_actions( $actions, $post ) {
			// Remove "Quick Edit" action.
			unset( $actions['inline'], $actions['inline hide-if-no-js'] );

			$duplicate_action_url = '';

			// Append "Duplicate" action.
			if ( 'acf-field-group' === $this->post_type ) {
				$duplicate_action_url = $this->get_admin_url( '&acfduplicate=' . $post->ID . '&_wpnonce=' . wp_create_nonce( 'bulk-posts' ) );
			} elseif ( 'acf-post-type' === $this->post_type ) {
				$duplicate_action_url = wp_nonce_url( admin_url( 'post-new.php?post_type=acf-post-type&use_post_type=' . $post->ID ), 'acfduplicate-' . $post->ID );
			} elseif ( 'acf-taxonomy' === $this->post_type ) {
				$duplicate_action_url = wp_nonce_url( admin_url( 'post-new.php?post_type=acf-taxonomy&use_taxonomy=' . $post->ID ), 'acfduplicate-' . $post->ID );
			} elseif ( 'acf-ui-options-page' === $this->post_type ) {
				$duplicate_action_url = wp_nonce_url( admin_url( 'post-new.php?post_type=acf-ui-options-page&use_options_page=' . $post->ID ), 'acfduplicate-' . $post->ID );
			}

			$actions['acfduplicate'] = '<a href="' . esc_url( $duplicate_action_url ) . '" aria-label="' . esc_attr__( 'Duplicate this item', 'acf' ) . '">' . __( 'Duplicate', 'acf' ) . '</a>';

			// Append the "Activate" or "Deactivate" actions.
			if ( 'acf-disabled' === $post->post_status ) {
				$activate_deactivate_action = 'acfactivate';
				$activate_action_url        = $this->get_admin_url( '&acfactivate=' . $post->ID . '&_wpnonce=' . wp_create_nonce( 'bulk-posts' ) );
				$actions['acfactivate']     = '<a href="' . esc_url( $activate_action_url ) . '" aria-label="' . esc_attr__( 'Activate this item', 'acf' ) . '">' . __( 'Activate', 'acf' ) . '</a>';
			} else {
				$activate_deactivate_action = 'acfdeactivate';
				$deactivate_action_url      = $this->get_admin_url( '&acfdeactivate=' . $post->ID . '&_wpnonce=' . wp_create_nonce( 'bulk-posts' ) );
				$actions['acfdeactivate']   = '<a href="' . esc_url( $deactivate_action_url ) . '" aria-label="' . esc_attr__( 'Deactivate this item', 'acf' ) . '">' . __( 'Deactivate', 'acf' ) . '</a>';
			}

			// Return actions in custom order.
			$order = array( 'edit', 'acfduplicate', $activate_deactivate_action, 'trash' );

			return array_merge( array_flip( $order ), $actions );
		}

		/**
		 * Modifies the admin table bulk actions dropdown.
		 *
		 * @date    15/4/20
		 * @since   5.9.0
		 *
		 * @param   array $actions The actions array.
		 * @return  array
		 */
		public function admin_table_bulk_actions( $actions ) {
			if ( ! in_array( $this->view, array( 'sync', 'trash' ), true ) ) {
				// TODO: We'll likely have to add support for CPTs/Taxonomies!
				if ( 'acf-field-group' === $this->post_type ) {
					$actions['acfduplicate'] = __( 'Duplicate', 'acf' );
				}

				$actions['acfactivate']   = __( 'Activate', 'acf' );
				$actions['acfdeactivate'] = __( 'Deactivate', 'acf' );
			}

			if ( $this->sync ) {
				if ( $this->view === 'sync' ) {
					$actions = array();
				}
				$actions['acfsync'] = __( 'Sync changes', 'acf' );
			}

			return $actions;
		}

		/**
		 * Gets the translated action notice text for list table actions (activate, deactivate, sync, etc.).
		 *
		 * @since 6.1
		 *
		 * @param string  $action The action being performed.
		 * @param integer $count  The number of items the action was performed on.
		 * @return string
		 */
		public function get_action_notice_text( $action, $count = 1 ) {
			return '';
		}

		/**
		 * Checks for the custom "Activate" bulk action.
		 *
		 * @since 6.0
		 */
		public function check_activate() {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Used for redirect notice.
			// Display notice on success redirect.
			if ( isset( $_GET['acfactivatecomplete'] ) ) {
				$ids = array_map( 'intval', explode( ',', $_GET['acfactivatecomplete'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with intval().
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
				// Generate text.
				$text = $this->get_action_notice_text( 'acfactivatecomplete', count( $ids ) );

				// Append links to text.
				$links = array();
				foreach ( $ids as $id ) {
					$links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
				}
				$text .= ' ' . implode( ', ', $links );

				// Add notice.
				acf_add_admin_notice( $text, 'success' );
				return;
			}

			// Find items to activate.
			$ids = array();
			if ( isset( $_GET['acfactivate'] ) ) {
				$ids[] = intval( $_GET['acfactivate'] );
			} elseif ( isset( $_GET['post'], $_GET['action2'] ) && $_GET['action2'] === 'acfactivate' ) {
				$ids = array_map( 'intval', $_GET['post'] );
			}

			if ( $ids ) {
				check_admin_referer( 'bulk-posts' );

				// Activate the field groups and return an array of IDs that were activated.
				$new_ids = array();
				foreach ( $ids as $id ) {
					$post_type = get_post_type( $id );
					if ( $post_type && acf_update_internal_post_type_active_status( $id, true, $post_type ) ) {
						$new_ids[] = $id;
					}
				}

				wp_safe_redirect( $this->get_admin_url( '&acfactivatecomplete=' . implode( ',', $new_ids ) ) );
				exit;
			}
		}

		/**
		 * Checks for the custom "Deactivate" bulk action.
		 *
		 * @since 6.0
		 */
		public function check_deactivate() {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Used for redirect notice.
			// Display notice on success redirect.
			if ( isset( $_GET['acfdeactivatecomplete'] ) ) {
				$ids = array_map( 'intval', explode( ',', $_GET['acfdeactivatecomplete'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with intval().
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
				// Generate text.
				$text = $this->get_action_notice_text( 'acfdeactivatecomplete', count( $ids ) );

				// Append links to text.
				$links = array();
				foreach ( $ids as $id ) {
					$links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
				}
				$text .= ' ' . implode( ', ', $links );

				// Add notice.
				acf_add_admin_notice( $text, 'success' );
				return;
			}

			// Find items to activate.
			$ids = array();
			if ( isset( $_GET['acfdeactivate'] ) ) {
				$ids[] = intval( $_GET['acfdeactivate'] );
			} elseif ( isset( $_GET['post'], $_GET['action2'] ) && $_GET['action2'] === 'acfdeactivate' ) {
				$ids = array_map( 'intval', $_GET['post'] );
			}

			if ( $ids ) {
				check_admin_referer( 'bulk-posts' );

				// Activate the field groups and return an array of IDs.
				$new_ids = array();
				foreach ( $ids as $id ) {
					$post_type = get_post_type( $id );
					if ( $post_type && acf_update_internal_post_type_active_status( $id, false, $post_type ) ) {
						$new_ids[] = $id;
					}
				}

				wp_safe_redirect( $this->get_admin_url( '&acfdeactivatecomplete=' . implode( ',', $new_ids ) ) );
				exit;
			}
		}

		/**
		 * Checks for the custom "duplicate" action.
		 *
		 * @since   5.9.0
		 */
		public function check_duplicate() {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Used for redirect notice.
			// Display notice on success redirect.
			if ( isset( $_GET['acfduplicatecomplete'] ) ) {
				$ids = array_map( 'intval', explode( ',', $_GET['acfduplicatecomplete'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with intval().
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
				// Generate text.
				$text = $this->get_action_notice_text( 'acfduplicatecomplete', count( $ids ) );

				// Append links to text.
				$links = array();
				foreach ( $ids as $id ) {
					$links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
				}
				$text .= ' ' . implode( ', ', $links );

				// Add notice.
				acf_add_admin_notice( $text, 'success' );
				return;
			}

			// Find items to duplicate.
			$ids = array();
			if ( isset( $_GET['acfduplicate'] ) ) {
				$ids[] = intval( $_GET['acfduplicate'] );
			} elseif ( isset( $_GET['post'], $_GET['action2'] ) && $_GET['action2'] === 'acfduplicate' ) {
				$ids = array_map( 'intval', $_GET['post'] );
			}

			if ( $ids ) {
				check_admin_referer( 'bulk-posts' );

				// Duplicate field groups and generate array of new IDs.
				$new_ids = array();
				foreach ( $ids as $id ) {
					$field_group = acf_duplicate_field_group( $id );
					$new_ids[]   = $field_group['ID'];
				}

				// Redirect.
				wp_safe_redirect( $this->get_admin_url( '&acfduplicatecomplete=' . implode( ',', $new_ids ) ) );
				exit;
			}
		}

		/**
		 * Checks for the custom "acfsync" action.
		 *
		 * @since   5.9.0
		 */
		public function check_sync() {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended
			// Display notice on success redirect.
			if ( isset( $_GET['acfsynccomplete'] ) ) {
				$ids = array_map( 'intval', explode( ',', $_GET['acfsynccomplete'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with intval().
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
				// Generate text.
				$text = $this->get_action_notice_text( 'acfsynccomplete', count( $ids ) );

				// Append links to text.
				$links = array();
				foreach ( $ids as $id ) {
					$links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
				}
				$text .= ' ' . implode( ', ', $links );

				// Add notice.
				acf_add_admin_notice( $text, 'success' );
				return;
			}

			// Find items to sync.
			$keys = array();
			if ( isset( $_GET['acfsync'] ) ) {
				$keys[] = sanitize_text_field( $_GET['acfsync'] );
			} elseif ( isset( $_GET['post'], $_GET['action2'] ) && $_GET['action2'] === 'acfsync' ) {
				$keys = array_map( 'sanitize_text_field', $_GET['post'] );
			}

			if ( $keys && $this->sync ) {
				check_admin_referer( 'bulk-posts' );

				// Disabled "Local JSON" controller to prevent the .json file from being modified during import.
				acf_update_setting( 'json', false );

				// Sync field groups and generate array of new IDs.
				$files   = acf_get_local_json_files( $this->post_type );
				$new_ids = array();
				foreach ( $this->sync as $key => $post ) {
					if ( $post['key'] && in_array( $post['key'], $keys ) ) {
						// Import.
					} elseif ( $post['ID'] && in_array( $post['ID'], $keys ) ) {
						// Import.
					} else {
						// Ignore.
						continue;
					}
					$local_post       = json_decode( file_get_contents( $files[ $key ] ), true );
					$local_post['ID'] = $post['ID'];
					$result           = acf_import_internal_post_type( $local_post, $this->post_type );
					$new_ids[]        = $result['ID'];
				}

				// Redirect.
				wp_safe_redirect( $this->get_current_admin_url( '&acfsynccomplete=' . implode( ',', $new_ids ) ) );
				exit;
			}
		}

		/**
		 * Customizes the admin table subnav.
		 *
		 * @date    17/4/20
		 * @since   5.9.0
		 *
		 * @param   array $views The available views.
		 * @return  array
		 */
		public function admin_table_views( $views ) {
			global $wp_list_table, $wp_query;

			// Count items.
			$count = count( $this->sync );

			// Append "sync" link to subnav.
			if ( $count ) {
				$views['sync'] = sprintf(
					'<a %s href="%s">%s <span class="count">(%s)</span></a>',
					( $this->view === 'sync' ? 'class="current"' : '' ),
					esc_url( $this->get_admin_url( '&post_status=sync' ) ),
					esc_html( __( 'Sync available', 'acf' ) ),
					$count
				);
			}

			// Modify table pagination args to match JSON data.
			if ( $this->view === 'sync' ) {
				$wp_list_table->set_pagination_args(
					array(
						'total_items' => $count,
						'total_pages' => 1,
						'per_page'    => $count,
					)
				);
				$wp_query->post_count = 1; // At least one post is needed to render bulk drop-down.
			}
			return $views;
		}

		/**
		 * Prints scripts into the admin footer.
		 *
		 * @since   5.9.0
		 */
		public function admin_footer() {
			?>
			<script type="text/javascript">
				(function($){

					// Displays a modal comparing local changes.
					function reviewSync( props ) {
						var modal = acf.newModal({
							title: acf.__('Review local JSON changes'),
							content: '<p class="acf-modal-feedback"><i class="acf-loading"></i> ' + acf.__('Loading diff') + '</p>',
							toolbar: '<a href="' + props.href + '" class="button button-primary button-sync-changes disabled">' + acf.__('Sync changes') + '</a>',
						});

						// Call AJAX.
						var xhr = $.ajax({
							url: acf.get('ajaxurl'),
							method: 'POST',
							dataType: 'json',
							data: acf.prepareForAjax({
								action:	'acf/ajax/local_json_diff',
								id: props.id
							})
						})
							.done(function( data, textStatus, jqXHR ) {
								modal.content( data.html );
								modal.$('.button-sync-changes').removeClass('disabled');
							})
							.fail(function( jqXHR, textStatus, errorThrown ) {
								if( error = acf.getXhrError(jqXHR) ) {
									modal.content( '<p class="acf-modal-feedback error">' + error + '</p>' );
								}
							});
					}

					$ (document ).on( 'ready', function( e ) {
						if ( ! acf.get( 'is_pro' ) || acf.get( 'isLicenseActive' ) || acf.get( 'isLicenseExpired' ) ) {
							return;
						}

						$( '.acf-has-block-location .column-title strong' )
							.addClass( 'acf-js-tooltip' )
							.attr( 'title', acf.__( 'Field groups for blocks cannot be edited without a license.', 'acf' ) );

						$( '.acf-admin-options-pages .column-title strong' )
							.addClass( 'acf-js-tooltip' )
							.attr( 'title', acf.__( 'Options pages cannot be edited without a license.', 'acf' ) );
					} );

					// Add event listener.
					$(document).on('click', 'a[data-event="review-sync"]', function( e ){
						e.preventDefault();
						reviewSync( $(this).data() );
					});
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Customizes the admin table HTML when viewing "sync" post_status.
		 *
		 * @since   5.9.0
		 */
		public function admin_footer__sync() {
			global $wp_list_table;

			// Get table columns.
			$columns = $wp_list_table->get_columns();
			$hidden  = get_hidden_columns( $wp_list_table->screen );
			?>
			<div style="display: none;">
				<table>
					<tbody id="acf-the-list">
					<?php
					foreach ( $this->sync as $k => $field_group ) {
						echo '<tr>';
						foreach ( $columns as $column_name => $column_label ) {
							$el = 'td';
							if ( $column_name === 'cb' ) {
								$el           = 'th';
								$classes      = 'check-column';
								$column_label = '';
							} elseif ( $column_name === 'title' ) {
								$classes = "$column_name column-$column_name column-primary";
							} else {
								$classes = "$column_name column-$column_name";
							}
							if ( in_array( $column_name, $hidden, true ) ) {
								$classes .= ' hidden';
							}

							printf(
								'<%s class="%s" data-colname="%s">',
								esc_attr( $el ),
								esc_attr( $classes ),
								esc_attr( $column_label )
							);

							switch ( $column_name ) {

								// Checkbox.
								case 'cb':
									echo '<label for="cb-select-' . esc_attr( $k ) . '" class="screen-reader-text">';
									/* translators: %s: field group title */
									echo esc_html( sprintf( __( 'Select %s', 'acf' ), $field_group['title'] ) );
									echo '</label>';
									echo '<input id="cb-select-' . esc_attr( $k ) . '" type="checkbox" value="' . esc_attr( $k ) . '" name="post[]">';
									break;

								// Title.
								case 'title':
									$post_state = '';
									if ( ! $field_group['active'] ) {
										$post_state = ' — <span class="post-state">' . $this->get_disabled_post_state() . '</span>';
									}
									echo '<strong><span class="row-title">' . esc_html( $field_group['title'] ) . '</span>' . acf_esc_html( $post_state ) . '</strong>';
									echo '<div class="row-actions"><span class="file acf-secondary-text">' . esc_html( $this->get_human_readable_file_location( $field_group['local_file'] ) ) . '</span></div>';
									echo '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>';
									break;

								// All other columns.
								default:
									$this->render_admin_table_column( $column_name, $field_group );
									break;
							}

							printf( '</%s>', esc_attr( $el ) );
						}
						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
				(function($){
					$('#the-list').html( $('#acf-the-list').children() );
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Fires when trashing an internal post type.
		 *
		 * @since 5.0.0
		 *
		 * @param integer $post_id The post ID.
		 */
		public function trashed_post( $post_id ) {
			if ( get_post_type( $post_id ) === $this->post_type ) {
				acf_trash_internal_post_type( $post_id, $this->post_type );
			}
		}

		/**
		 * Fires when untrashing an internal post type.
		 *
		 * @date    8/01/2014
		 * @since   5.0.0
		 *
		 * @param   integer $post_id The post ID.
		 * @return  void
		 */
		public function untrashed_post( $post_id ) {
			if ( get_post_type( $post_id ) === $this->post_type ) {
				acf_untrash_internal_post_type( $post_id, $this->post_type );
			}
		}

		/**
		 * Fires when deleting an internal post type.
		 *
		 * @date    8/01/2014
		 * @since   5.0.0
		 *
		 * @param   integer $post_id The post ID.
		 * @return  void
		 */
		public function deleted_post( $post_id ) {
			if ( get_post_type( $post_id ) === $this->post_type ) {
				acf_delete_internal_post_type( $post_id, $this->post_type );
			}
		}
	}

endif; // Class exists check.
