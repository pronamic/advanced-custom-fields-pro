<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Admin_UI_Options_Pages' ) ) :

	/**
	 * The ACF Post Types admin controller class
	 */
	#[AllowDynamicProperties]
	class ACF_Admin_UI_Options_Pages extends ACF_Admin_Internal_Post_Type_List {

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
		public $admin_body_class = 'acf-admin-options-pages';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = 'options-pages';

		/**
		 * If this is a pro feature or not.
		 *
		 * @var boolean
		 */
		public $is_pro_feature = true;

		/**
		 * Constructor.
		 *
		 * @since   6.2
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			parent::__construct();
		}

		/**
		 * Current screen actions for the post types list admin page.
		 *
		 * @since   6.1
		 */
		public function current_screen() {
			// Bail early if not post types admin page.
			if ( ! acf_is_screen( "edit-{$this->post_type}" ) ) {
				return;
			}

			parent::current_screen();

			// Run a first-run routine to set some defaults which are stored in user preferences.
			if ( ! acf_get_user_setting( 'options-pages-first-run', false ) ) {
				$option_key   = 'manageedit-' . $this->post_type . 'columnshidden';
				$hidden_items = get_user_option( $option_key );

				if ( ! is_array( $hidden_items ) ) {
					$hidden_items = array();
				}

				if ( ! in_array( 'acf-key', $hidden_items ) ) {
					$hidden_items[] = 'acf-key';
				}
				update_user_option( get_current_user_id(), $option_key, $hidden_items, true );

				acf_update_user_setting( 'options-pages-first-run', true );
			}
		}

		/**
		 * Add any menu items required for post types.
		 *
		 * @since 6.1
		 */
		public function admin_menu() {
			$parent_slug = 'edit.php?post_type=acf-field-group';
			$cap         = acf_get_setting( 'capability' );
			add_submenu_page( $parent_slug, __( 'Options Pages', 'acf' ), __( 'Options Pages', 'acf' ), $cap, 'edit.php?post_type=acf-ui-options-page' );
		}

		/**
		 * Customizes the admin table columns.
		 *
		 * @date    1/4/20
		 * @since   5.9.0
		 *
		 * @param array $_columns The columns array.
		 * @return array
		 */
		public function admin_table_columns( $_columns ) {
			// Set the "no found" label to be our custom HTML for no results.
			if ( empty( acf_request_arg( 's' ) ) ) {
				global $wp_post_types;
				$this->not_found_label                                = $wp_post_types[ $this->post_type ]->labels->not_found;
				$wp_post_types[ $this->post_type ]->labels->not_found = $this->get_not_found_html();
			}

			$columns = array(
				'cb'              => $_columns['cb'],
				'title'           => $_columns['title'],
				'acf-description' => __( 'Description', 'acf' ),
				'acf-key'         => __( 'Key', 'acf' ),
			);

			if ( acf_get_local_json_files( $this->post_type ) ) {
				$columns['acf-json'] = __( 'Local JSON', 'acf' );
			}

			return $columns;
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
		public function render_admin_table_column( $column_name, $post ) {
			switch ( $column_name ) {
				case 'acf-key':
					echo '<i class="acf-icon acf-icon-key-solid"></i>';
					echo esc_html( $post['key'] );
					break;

				// Description.
				case 'acf-description':
					if ( ! empty( $post['description'] ) && ( is_string( $post['description'] ) || is_numeric( $post['description'] ) ) ) {
						echo '<span class="acf-description">' . acf_esc_html( $post['description'] ) . '</span>';
					} else {
						echo '<span class="acf-emdash" aria-hidden="true">—</span>';
						echo '<span class="screen-reader-text">' . esc_html__( 'No description', 'acf' ) . '</span>';
					}
					break;

				// Local JSON.
				case 'acf-json':
					$this->render_admin_table_column_local_status( $post );
					break;
			}
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
			$text  = '';
			$count = (int) $count;

			switch ( $action ) {
				case 'acfactivatecomplete':
					$text = sprintf(
						/* translators: %s number of post types activated */
						_n( 'Options page activated.', '%s options pages activated.', $count, 'acf' ),
						$count
					);
					break;
				case 'acfdeactivatecomplete':
					$text = sprintf(
						/* translators: %s number of post types deactivated */
						_n( 'Options page deactivated.', '%s options pages deactivated.', $count, 'acf' ),
						$count
					);
					break;
				case 'acfduplicatecomplete':
					$text = sprintf(
						/* translators: %s number of post types duplicated */
						_n( 'Options page duplicated.', '%s options pages duplicated.', $count, 'acf' ),
						$count
					);
					break;
				case 'acfsynccomplete':
					$text = sprintf(
						/* translators: %s number of post types synchronized */
						_n( 'Options page synchronized.', '%s options pages synchronized.', $count, 'acf' ),
						$count
					);
					break;
			}

			return $text;
		}
	}

	// Instantiate.
	acf_new_instance( 'ACF_Admin_UI_Options_Pages' );
endif; // Class exists check.
