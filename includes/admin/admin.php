<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Admin' ) ) :

	class ACF_Admin {

		/**
		 * Constructor.
		 *
		 * @since 5.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
			add_action( 'current_screen', array( $this, 'current_screen' ) );
			add_action( 'admin_notices', array( $this, 'maybe_show_escaped_html_notice' ) );
			add_action( 'admin_notices', array( $this, 'maybe_show_select2_v3_deprecation_notice' ) );
			add_action( 'admin_init', array( $this, 'dismiss_escaped_html_notice' ) );
			add_action( 'admin_init', array( $this, 'clear_escaped_html_log' ) );
			add_filter( 'parent_file', array( $this, 'ensure_menu_selection' ) );
			add_filter( 'submenu_file', array( $this, 'ensure_submenu_selection' ) );
		}

		/**
		 * Adds the ACF menu item.
		 *
		 * @date    28/09/13
		 * @since   5.0.0
		 */
		public function admin_menu() {

			// Bail early if ACF is hidden.
			if ( ! acf_get_setting( 'show_admin' ) ) {
				return;
			}

			// Vars.
			$cap         = acf_get_setting( 'capability' );
			$parent_slug = 'edit.php?post_type=acf-field-group';

			// Add menu items.
			add_menu_page( __( 'ACF', 'acf' ), __( 'ACF', 'acf' ), $cap, $parent_slug, false, 'dashicons-welcome-widgets-menus', 80 );
		}

		/**
		 * Enqueues global admin styling.
		 *
		 * @since   5.0.0
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_style( 'acf-global' );
			wp_enqueue_script( 'acf-escaped-html-notice' );

			wp_localize_script(
				'acf-escaped-html-notice',
				'acf_escaped_html_notice',
				array(
					'show_details' => __( 'Show&nbsp;details', 'acf' ),
					'hide_details' => __( 'Hide&nbsp;details', 'acf' ),
				)
			);
		}

		/**
		 * Appends custom admin body classes.
		 *
		 * @date    5/11/19
		 * @since   5.8.7
		 *
		 * @param   string $classes Space-separated list of CSS classes.
		 * @return  string
		 */
		public function admin_body_class( $classes ) {
			global $wp_version;

			// Add body class.
			$classes .= ' acf-admin-5-3';

			// Add browser for specific CSS.
			$classes .= ' acf-browser-' . esc_attr( acf_get_browser() );

			// Return classes.
			return $classes;
		}

		/**
		 * Adds custom functionality to "ACF" admin pages.
		 *
		 * @date    7/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function current_screen( $screen ) {
			// Determine if the current page being viewed is "ACF" related.
			if ( isset( $screen->post_type ) && in_array( $screen->post_type, acf_get_internal_post_types(), true ) ) {
				add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );
				add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
				add_filter( 'update_footer', array( $this, 'admin_footer_version_text' ) );
				$this->setup_help_tab();
				$this->maybe_show_import_from_cptui_notice();
			}
		}

		/**
		 * Sets up the admin help tab.
		 *
		 * @date    20/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function setup_help_tab() {
			$screen = get_current_screen();

			// Overview tab.
			$screen->add_help_tab(
				array(
					'id'      => 'overview',
					'title'   => __( 'Overview', 'acf' ),
					'content' =>
						'<p><strong>' . __( 'Overview', 'acf' ) . '</strong></p>' .
						'<p>' . __( 'The Advanced Custom Fields plugin provides a visual form builder to customize WordPress edit screens with extra fields, and an intuitive API to display custom field values in any theme template file.', 'acf' ) . '</p>' .
						'<p>' . sprintf(
							__( 'Before creating your first Field Group, we recommend first reading our <a href="%s" target="_blank">Getting started</a> guide to familiarize yourself with the plugin\'s philosophy and best practises.', 'acf' ),
							acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/getting-started-with-acf/', 'docs', 'help-tab' )
						) . '</p>' .
						'<p>' . __( 'Please use the Help & Support tab to get in touch should you find yourself requiring assistance.', 'acf' ) . '</p>' .
						'',
				)
			);

			// Help tab.
			$screen->add_help_tab(
				array(
					'id'      => 'help',
					'title'   => __( 'Help & Support', 'acf' ),
					'content' =>
						'<p><strong>' . __( 'Help & Support', 'acf' ) . '</strong></p>' .
						'<p>' . __( 'We are fanatical about support, and want you to get the best out of your website with ACF. If you run into any difficulties, there are several places you can find help:', 'acf' ) . '</p>' .
						'<ul>' .
							'<li>' . sprintf(
								__( '<a href="%s" target="_blank">Documentation</a>. Our extensive documentation contains references and guides for most situations you may encounter.', 'acf' ),
								acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/', 'docs', 'help-tab' )
							) . '</li>' .
							'<li>' . sprintf(
								__( '<a href="%s" target="_blank">Discussions</a>. We have an active and friendly community on our Community Forums who may be able to help you figure out the \'how-tos\' of the ACF world.', 'acf' ),
								acf_add_url_utm_tags( 'https://support.advancedcustomfields.com/', 'docs', 'help-tab' )
							) . '</li>' .
							'<li>' . sprintf(
								__( '<a href="%s" target="_blank">Help Desk</a>. The support professionals on our Help Desk will assist with your more in depth, technical challenges.', 'acf' ),
								acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/support/', 'docs', 'help-tab' )
							) . '</li>' .
						'</ul>',
				)
			);

			// Sidebar.
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'Information', 'acf' ) . '</strong></p>' .
				'<p><span class="dashicons dashicons-admin-plugins"></span> ' . sprintf( __( 'Version %s', 'acf' ), ACF_VERSION ) . '</p>' .
				'<p><span class="dashicons dashicons-wordpress"></span> <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">' . __( 'View details', 'acf' ) . '</a></p>' .
				'<p><span class="dashicons dashicons-admin-home"></span> <a href="' . acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/', 'docs', 'help-tab' ) . '" target="_blank" target="_blank">' . __( 'Visit website', 'acf' ) . '</a></p>' .
				''
			);
		}

		/**
		 * Shows a notice to import post types and taxonomies from CPTUI if that plugin is active.
		 *
		 * @since 6.1
		 */
		public function maybe_show_import_from_cptui_notice() {
			global $plugin_page;

			// Only show if CPTUI is active and post types are enabled.
			if ( ! acf_get_setting( 'enable_post_types' ) || ! is_plugin_active( 'custom-post-type-ui/custom-post-type-ui.php' ) ) {
				return;
			}

			// No need to show on the tools page.
			if ( 'acf-tools' === $plugin_page ) {
				return;
			}

			$text = sprintf(
				/* translators: %s - URL to ACF tools page. */
				__( 'Import Post Types and Taxonomies registered with Custom Post Type UI and manage them with ACF. <a href="%s">Get Started</a>.', 'acf' ),
				acf_get_admin_tools_url()
			);

			acf_add_admin_notice( $text, 'success', true, true );
		}

		/**
		 * Notifies the user that fields rendered via shortcode or the_field() have
		 * had HTML removed/altered due to unsafe HTML being escaped.
		 *
		 * @since 6.2.5
		 */
		public function maybe_show_escaped_html_notice() {
			// Only show to editors and above.
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				return;
			}

			// Allow opting-out of the notice.
			if ( apply_filters( 'acf/admin/prevent_escaped_html_notice', true ) ) {
				return;
			}

			if ( get_option( 'acf_escaped_html_notice_dismissed' ) ) {
				return;
			}

			$escaped = _acf_get_escaped_html_log();

			// Notice for when HTML has already been escaped.
			if ( ! empty( $escaped ) ) {
				acf_get_view( 'escaped-html-notice', array( 'acf_escaped' => $escaped ) );
			}
		}

		/**
		 * Dismisses the escaped unsafe HTML notice.
		 *
		 * @since 6.2.5
		 */
		public function dismiss_escaped_html_notice() {
			if ( empty( $_GET['acf-dismiss-esc-html-notice'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_GET['acf-dismiss-esc-html-notice'] ) );

			if (
				! wp_verify_nonce( $nonce, 'acf/dismiss_escaped_html_notice' ) ||
				! current_user_can( acf_get_setting( 'capability' ) )
			) {
				return;
			}

			update_option( 'acf_escaped_html_notice_dismissed', true );

			_acf_delete_escaped_html_log();

			wp_safe_redirect( remove_query_arg( 'acf-dismiss-esc-html-notice' ) );
			exit;
		}

		/**
		 * Clear the escaped unsafe HTML log.
		 *
		 * @since 6.2.5
		 */
		public function clear_escaped_html_log() {
			if ( empty( $_GET['acf-clear-esc-html-log'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_GET['acf-clear-esc-html-log'] ) );

			if (
				! wp_verify_nonce( $nonce, 'acf/clear_escaped_html_log' ) ||
				! current_user_can( acf_get_setting( 'capability' ) )
			) {
				return;
			}

			_acf_delete_escaped_html_log();

			wp_safe_redirect( remove_query_arg( 'acf-clear-esc-html-log' ) );
			exit;
		}

		/**
		 * Notifies the user that Select2 v3 has been deprecated and will be removed.
		 *
		 * @since 6.4.3
		 *
		 * @return void
		 */
		public function maybe_show_select2_v3_deprecation_notice() {
			// Only show to editors and above.
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				return;
			}

			if ( 3 === acf_get_setting( 'select2_version' ) ) {
				$acf_plugin_name = acf_is_pro() ? 'ACF PRO' : 'ACF';
				$acf_plugin_name = '<strong>' . $acf_plugin_name . ' &mdash;</strong>';

				$text = sprintf(
					/* translators: %1$s - Plugin name, %2$s URL to documentation */
					__( '%1$s We have detected that this website is configured to use v3 of the Select2 jQuery library, which has been deprecated in favor of v4 and will be removed in a future version of ACF. <a href="%2$s" target="_blank">Learn more</a>.', 'acf' ),
					$acf_plugin_name,
					acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/select2-v3-deprecation/', 'docs', 'select2-deprecation-notice' ),
				);

				acf_add_admin_notice( $text, 'warning', false );
			}
		}

		/**
		 * Renders the admin navigation element.
		 *
		 * @date    27/3/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		function in_admin_header() {
			acf_get_view( 'global/navigation' );

			$screen = get_current_screen();

			if ( isset( $screen->base ) && 'post' === $screen->base ) {
				acf_get_view( 'global/form-top' );
			}

			do_action( 'acf/in_admin_header' );
		}

		/**
		 * Modifies the admin footer text.
		 *
		 * @date    7/4/20
		 * @since   5.9.0
		 *
		 * @param   string $text The current admin footer text.
		 * @return  string
		 */
		public function admin_footer_text( $text ) {
			$wp_engine_link = acf_add_url_utm_tags( 'https://wpengine.com/', 'bx_prod_referral', acf_is_pro() ? 'acf_pro_plugin_footer_text' : 'acf_free_plugin_footer_text', false, 'acf_plugin', 'referral' );
			$acf_link       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/', 'footer', 'footer' );

			if ( acf_is_pro() ) {
				return sprintf(
					/* translators: This text is prepended by a link to ACF's website, and appended by a link to WP Engine's website. */
					'<a href="%1$s" target="_blank">ACF&#174;</a> and <a href="%1$s" target="_blank">ACF&#174; PRO</a> ' . __( 'are developed and maintained by', 'acf' ) . ' <a href="%2$s" target="_blank">WP Engine</a>.',
					$acf_link,
					$wp_engine_link
				);
			}

			return sprintf(
				/* translators: This text is prepended by a link to ACF's website, and appended by a link to WP Engine's website. */
				'<a href="%1$s" target="_blank">ACF&#174;</a> ' . __( 'is developed and maintained by', 'acf' ) . ' <a href="%2$s" target="_blank">WP Engine</a>.',
				$acf_link,
				$wp_engine_link
			);
		}

		/**
		 * Modifies the admin footer version text.
		 *
		 * @since 6.2
		 *
		 * @param   string $text The current admin footer version text.
		 * @return  string
		 */
		public function admin_footer_version_text( $text ) {
			$documentation_link = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/', 'footer', 'footer' );
			$support_link       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/support/', 'footer', 'footer' );
			$feedback_link      = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/feedback/', 'footer', 'footer' );
			$version_link       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/changelog/', 'footer', 'footer' );

			return sprintf(
				'<a href="%s" target="_blank">%s</a> &#8729; <a href="%s" target="_blank">%s</a> &#8729; <a href="%s" target="_blank">%s</a> &#8729; <a href="%s" target="_blank">%s %s</a>',
				$documentation_link,
				__( 'Documentation', 'acf' ),
				$support_link,
				__( 'Support', 'acf' ),
				$feedback_link,
				__( 'Feedback', 'acf' ),
				$version_link,
				acf_is_pro() ? __( 'ACF PRO', 'acf' ) : __( 'ACF', 'acf' ),
				ACF_VERSION
			);
		}

		/**
		 * Ensure the ACF parent menu is selected for add-new.php
		 *
		 * @since 6.1
		 * @param string $parent_file The parent file checked against menu activation.
		 * @return string The modified parent file
		 */
		public function ensure_menu_selection( $parent_file ) {
			if ( ! is_string( $parent_file ) ) {
				return $parent_file;
			}
			if ( strpos( $parent_file, 'edit.php?post_type=acf-' ) === 0 ) {
				return 'edit.php?post_type=acf-field-group';
			}
			return $parent_file;
		}


		/**
		 * Ensure the correct ACF submenu item is selected when in post-new versions of edit pages
		 *
		 * @since 6.1
		 * @param string $submenu_file The submenu filename.
		 * @return string The modified submenu filename
		 */
		public function ensure_submenu_selection( $submenu_file ) {
			if ( ! is_string( $submenu_file ) ) {
				return $submenu_file;
			}
			if ( strpos( $submenu_file, 'post-new.php?post_type=acf-' ) === 0 ) {
				return str_replace( 'post-new', 'edit', $submenu_file );
			}
			return $submenu_file;
		}
	}

	// Instantiate.
	acf_new_instance( 'ACF_Admin' );
endif; // class_exists check
