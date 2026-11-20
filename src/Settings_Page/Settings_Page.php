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

namespace ACF\Settings_Page;

use function acf_get_setting;
use function acf_update_setting;
use function acf_get_allowed_db_settings;
use function acf_is_setting_managed;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles all ACF settings tabs.
 *
 * Registers a submenu page for every tab returned by the
 * 'acf/settings_page/tabs' filter. Tabs can specify a custom
 * JS/CSS asset bundle and additional strings.
 */
class Settings_Page {

	/**
	 * The settings app's script and style handle and bundle name, also used as the AJAX action for any tab.
	 * Tab scripts and styles registered with 'js' and 'css' depend on this handle.
	 */
	const DEFAULT_ASSET = 'acf-settings';

	/**
	 * Tab keys the settings app renders with ACF's own pages (ACF PRO's License and Updates tabs).
	 *
	 * @since 6.8.12
	 */
	const RESERVED_TAB_KEYS = array( 'license', 'updates' );

	/**
	 * Prefix of each tab's admin page slug, "acf-settings-tab-{key}". It can't produce "acf-settings-updates",
	 * the old Updates page, and it keeps the slugs from matching other plugins' pages.
	 *
	 * @since 6.8.12
	 */
	const PAGE_SLUG_PREFIX = 'acf-settings-tab-';

	/**
	 * Memoized tabs array, populated on first call to get_tabs().
	 *
	 * @since 6.8.2
	 * @var array[]|null
	 */
	protected $tabs = null;

	/**
	 * Sets up the class functionality.
	 *
	 * @since 6.8.2
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_page_access_denied', array( $this, 'redirect_legacy_pages' ), 1 ); // Priority 1 = very early
		add_action( 'wp_admin_page_not_found', array( $this, 'redirect_legacy_pages' ), 1 ); // WordPress 7.2+ fires this instead for unregistered pages.
		add_action( 'wp_ajax_' . self::DEFAULT_ASSET, array( $this, 'ajax_request' ) );
		add_action( 'wp_ajax_acf_settings_tab_data', array( $this, 'ajax_get_tab_data' ) );
	}

	/**
	 * Redirects the old standalone Updates page URL to the License tab, or to the Updates tab
	 * when it asks for a fresh update check with "force-check".
	 *
	 * Runs on "admin_page_access_denied", and on "wp_admin_page_not_found", which WordPress 7.2+
	 * fires instead for unregistered pages. Only one of them fires on a request.
	 *
	 * @since 6.8.6
	 *
	 * @return void
	 */
	public function redirect_legacy_pages() {
		if ( 'acf-settings-updates' !== acf_maybe_get_GET( 'page' ) ) {
			return;
		}
		// Keep any other query args, e.g. "force-check" or "force-license-check", which the tabs read on load.
		$args = array_diff_key( wp_unslash( $_GET ), array_flip( array( 'page', 'post_type' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Redirect only.
		$tab  = empty( $args['force-check'] ) ? 'license' : 'updates';
		$url  = add_query_arg( urlencode_deep( $args ), admin_url( 'edit.php?post_type=acf-field-group&page=' . self::PAGE_SLUG_PREFIX . $tab ) );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Registers a submenu page for every tab. Only the "Settings" tab
	 * gets a visible menu title in the admin sidebar.
	 *
	 * @since 6.8.2
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Bail early if no show_admin.
		if ( ! acf_get_setting( 'show_admin' ) ) {
			return;
		}

		$tabs       = $this->get_tabs();
		$capability = acf_get_setting( 'capability' );
		$is_first   = true;

		$parent      = 'edit.php?post_type=acf-field-group';
		$first_tab   = '';
		$hidden_tabs = array();

		foreach ( $tabs as $tab ) {
			$slug = self::PAGE_SLUG_PREFIX . $tab['key'];

			$page = add_submenu_page(
				$parent,
				$tab['label'],
				$is_first ? __( 'Settings', 'acf' ) : $tab['label'],
				$capability,
				$slug,
				array( $this, 'admin_html' )
			);

			add_action( "load-$page", array( $this, 'load' ) );

			if ( $is_first ) {
				$first_tab = $slug;
			} else {
				$hidden_tabs[] = $slug;
			}

			$is_first = false;
		}

		// Remove extra tabs from the sidebar after the header has read the menu.
		add_action(
			'admin_head',
			function () use ( $parent, $hidden_tabs ) {
				foreach ( $hidden_tabs as $slug ) {
					remove_submenu_page( $parent, $slug );
				}
			}
		);

		// Highlight the "Settings" sidebar item while viewing a hidden tab.
		add_filter(
			'submenu_file',
			function ( $submenu_file ) use ( $first_tab, $hidden_tabs ) {
				global $plugin_page;

				if ( in_array( $plugin_page, $hidden_tabs, true ) ) {
					return $first_tab;
				}

				return $submenu_file;
			}
		);
	}

	/**
	 * Returns the admin page slug ("acf-settings-tab-{key}") of every registered tab.
	 *
	 * @since 6.8.12
	 *
	 * @return string[]
	 */
	public function get_page_slugs(): array {
		return array_map(
			function ( $tab ) {
				return self::PAGE_SLUG_PREFIX . $tab['key'];
			},
			$this->get_tabs()
		);
	}

	/**
	 * Determines whether a setting is managed via code (a filter, or acf_update_setting()).
	 *
	 * @param string $key The setting key.
	 * @return boolean
	 */
	protected function is_setting_managed( string $key ): bool {
		return acf_is_setting_managed( $key );
	}

	/**
	 * Enqueues assets for the settings page.
	 *
	 * @since 6.8.2
	 *
	 * @return void
	 */
	public function load() {
		$version = acf_get_setting( 'version' );
		$min     = defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ? '' : '.min';

		$active_tab    = $this->get_active_tab();
		$active_config = $this->get_tab_config( $active_tab );
		// One handle on every tab. A per-tab handle ("acf-settings-{tab}") could match a tab script's
		// "acf-settings-tab-{key}" handle, e.g. tabs "tab-a" and "a", and stop that script loading.
		$handle = self::DEFAULT_ASSET;

		// PRO's bundle is the same app plus the License and Updates tabs, which the free bundle leaves out.
		$bundle = acf_is_pro() ? 'pro/acf-pro-settings' : self::DEFAULT_ASSET;

		$css_file = "assets/build/css/{$bundle}{$min}.css";
		$js_file  = "assets/build/js/{$bundle}{$min}.js";

		// In development, version the bundle by its build time so the browser doesn't keep a stale copy between builds.
		$css_version = $version;
		$js_version  = $version;
		if ( defined( 'ACF_DEVELOPMENT_MODE' ) && ACF_DEVELOPMENT_MODE ) {
			$css_version = file_exists( acf_get_path( $css_file ) ) ? (string) filemtime( acf_get_path( $css_file ) ) : $version;
			$js_version  = file_exists( acf_get_path( $js_file ) ) ? (string) filemtime( acf_get_path( $js_file ) ) : $version;
		}

		// Depend on the admin colour scheme (which depends on wp-admin, including forms.css) so this prints after WordPress's
		// admin CSS. This hook runs before admin-header.php enqueues those, and their checkbox rules otherwise win ties
		// with the Base Design System's.
		wp_enqueue_style( $handle, acf_get_url( $css_file ), array( 'colors' ), $css_version );

		$this->register_jsx_runtime_fallback( $version );

		wp_enqueue_script(
			$handle,
			acf_get_url( $js_file ),
			array( 'react', 'react-dom', 'react-jsx-runtime' ),
			$js_version,
			true
		);

		// Load every tab's assets, not just the active tab's, so they're available when switching tabs in-page without a reload.
		foreach ( $this->get_tabs() as $tab ) {
			// A tab's own script registers its UI with acf.settingsPage.registerTab(), so it loads after ACF's app.
			$tab_version = isset( $tab['version'] ) ? (string) $tab['version'] : $version;

			if ( ! empty( $tab['js'] ) ) {
				wp_enqueue_script( 'acf-settings-tab-' . $tab['key'], $tab['js'], array( $handle ), $tab_version, true );
			}

			if ( ! empty( $tab['css'] ) ) {
				wp_enqueue_style( 'acf-settings-tab-' . $tab['key'], $tab['css'], array( $handle ), $tab_version );
			}

			if ( isset( $tab['enqueue'] ) && is_callable( $tab['enqueue'] ) ) {
				call_user_func( $tab['enqueue'] );
			}
		}

		$strings = $this->get_strings();
		if ( ! empty( $active_config['strings'] ) ) {
			$strings = array_merge( $strings, $active_config['strings'] );
		}

		$tab_data = $this->get_tab_data( $active_config );

		$logo_file = acf_is_pro() ? 'assets/images/acf-pro-logo.svg' : 'assets/images/acf-logo.svg';

		wp_localize_script(
			$handle,
			'acfSettingsPage',
			array(
				'ajaxUrl'   => esc_url( admin_url( 'admin-ajax.php' ) ),
				'nonce'     => esc_html( wp_create_nonce( 'acf_nonce' ) ),
				'action'    => esc_html( self::DEFAULT_ASSET ),
				'activeTab' => esc_attr( $active_tab ),
				'strings'   => $strings,
				'tabData'   => $tab_data,
				'tabs'      => array_map(
					function ( $tab ) {
						// Not HTML-escaped: these are JSON-encoded and rendered by React, which escapes them.
						return array(
							'key'   => $tab['key'],
							'label' => wp_strip_all_tags( $tab['label'] ),
							'url'   => esc_url_raw( $this->get_tab_url( $tab['key'] ) ),
							'count' => isset( $tab['count'] ) ? (int) ( is_callable( $tab['count'] ) ? call_user_func( $tab['count'] ) : $tab['count'] ) : 0,
						);
					},
					$this->get_tabs()
				),
				'logoUrl'   => esc_url( acf_get_url( $logo_file ) ),
			)
		);

		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	/**
	 * Registers a fallback for WordPress's react-jsx-runtime script, which WordPress only provides from 6.6.
	 *
	 * The settings bundle loads react/jsx-runtime from WordPress instead of bundling it, so elements are created by
	 * the same React that renders them.
	 *
	 * @since 6.8.12
	 *
	 * @param string $version The script version.
	 * @return void
	 */
	protected function register_jsx_runtime_fallback( string $version ) {
		if ( wp_script_is( 'react-jsx-runtime', 'registered' ) ) {
			return;
		}

		wp_register_script( 'react-jsx-runtime', acf_get_url( 'src/Settings_Page/react-jsx-runtime.js' ), array( 'react' ), $version, true );
	}

	/**
	 * Modifies the admin body class.
	 *
	 * @since 6.8.2
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		$classes .= ' acf-admin-page';
		return $classes;
	}

	/**
	 * Returns localized tab data for SPA tab switching (no page reload).
	 *
	 * @since 6.8.7
	 *
	 * @return void
	 */
	public function ajax_get_tab_data() {
		if ( ! acf_verify_ajax() ) {
			wp_send_json_error( __( 'Invalid nonce.', 'acf' ), 403 );
		}

		if ( ! acf_current_user_can_admin() ) {
			wp_send_json_error( __( 'Sorry, you do not have permission to do that.', 'acf' ), 403 );
		}

		$tab_key    = acf_maybe_get_POST( 'tab', '' );
		$tab_key    = is_string( $tab_key ) ? sanitize_key( $tab_key ) : '';
		$tab_config = $this->get_tab_config( $tab_key );

		if ( empty( $tab_config ) ) {
			wp_send_json_error( __( 'Invalid tab.', 'acf' ), 400 );
		}

		$strings = $this->get_strings();
		if ( ! empty( $tab_config['strings'] ) ) {
			$strings = array_merge( $strings, $tab_config['strings'] );
		}

		$tab_data = $this->get_tab_data( $tab_config );

		wp_send_json_success(
			array(
				'activeTab' => $tab_key,
				'strings'   => $strings,
				'tabData'   => $tab_data,
				'nonce'     => wp_create_nonce( 'acf_nonce' ),
			)
		);
	}

	/**
	 * Handles the AJAX request for saving settings.
	 *
	 * Verifies the nonce and user permissions, then checks the active tab
	 * for a custom 'ajax_callback'. If one is provided, it is called with
	 * no arguments and should read what it needs from $_POST. Otherwise,
	 * falls back to the default handler.
	 *
	 * @return void
	 */
	public function ajax_request() {
		if ( ! acf_verify_ajax() ) {
			wp_send_json_error( __( 'Invalid nonce.', 'acf' ), 403 );
		}

		if ( ! acf_current_user_can_admin() ) {
			wp_send_json_error( __( 'Sorry, you do not have permission to do that.', 'acf' ), 403 );
		}

		$tab_key    = acf_maybe_get_POST( 'tab', '' );
		$tab_key    = is_string( $tab_key ) ? sanitize_key( $tab_key ) : '';
		$tab_config = $this->get_tab_config( $tab_key );

		if ( ! empty( $tab_config['ajax_callback'] ) && is_callable( $tab_config['ajax_callback'] ) ) {
			$response = call_user_func( $tab_config['ajax_callback'] );
		} elseif ( ! acf_get_setting( 'show_settings' ) && ! $this->is_saving_own_fields( $tab_config ) ) {
			// The Features tab is hidden, so a tab can only save the settings it shows itself.
			$response = new \WP_Error( 'acf_settings_disabled', __( 'The settings page has been disabled.', 'acf' ), array( 'status' => 403 ) );
		} else {
			$response = $this->get_ajax_response();
		}

		if ( is_wp_error( $response ) ) {
			$error_data = $response->get_error_data();
			$status     = 400;

			if ( is_array( $error_data ) && isset( $error_data['status'] ) && is_numeric( $error_data['status'] ) ) {
				$status = (int) $error_data['status'];
			}

			wp_send_json_error( $response->get_error_message(), $status );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Returns the data to pass to JS for a tab, with any sections sanitized and empty ones removed.
	 *
	 * @since 6.8.12
	 *
	 * @param array $tab_config The tab config.
	 * @return mixed
	 */
	protected function get_tab_data( array $tab_config ) {
		$tab_data = isset( $tab_config['get_tab_data'] ) && is_callable( $tab_config['get_tab_data'] )
			? call_user_func( $tab_config['get_tab_data'] )
			: ( $tab_config['tab_data'] ?? new \stdClass() );

		if ( is_array( $tab_data ) && ! empty( $tab_data['sections'] ) && is_array( $tab_data['sections'] ) ) {
			$tab_data['sections'] = $this->remove_empty_sections( $this->sanitize_sections( $tab_data['sections'] ) );
		}

		return $tab_data;
	}

	/**
	 * Checks that every setting in the save request is one of the tab's own fields.
	 *
	 * @since 6.8.12
	 *
	 * @param array $tab_config The tab config.
	 * @return boolean
	 */
	protected function is_saving_own_fields( array $tab_config ): bool {
		// ACF's own tabs don't save through the default handler, and loading their data can make remote requests.
		if ( empty( $tab_config ) || ! empty( $tab_config['_builtin'] ) ) {
			return false;
		}

		$raw_settings = acf_maybe_get_POST( 'acfSettings', '' );
		$incoming     = is_string( $raw_settings ) ? json_decode( wp_unslash( $raw_settings ), true ) : null;

		// Leave missing or invalid data to the default handler, which returns its own error.
		if ( ! is_array( $incoming ) ) {
			return true;
		}

		$tab_data   = $this->get_tab_data( $tab_config );
		$field_keys = array();

		if ( is_array( $tab_data ) && ! empty( $tab_data['sections'] ) && is_array( $tab_data['sections'] ) ) {
			foreach ( $tab_data['sections'] as $section ) {
				$field_keys = array_merge( $field_keys, array_column( $section['fields'], 'key' ) );
			}
		}

		return empty( array_diff( array_map( 'strval', array_keys( $incoming ) ), $field_keys ) );
	}

	/**
	 * Returns the active tab key from the current page query parameter.
	 *
	 * @return string
	 */
	protected function get_active_tab() {
		$page    = sanitize_key( acf_maybe_get_GET( 'page', '' ) );
		$tab_key = 0 === strpos( $page, self::PAGE_SLUG_PREFIX ) ? substr( $page, strlen( self::PAGE_SLUG_PREFIX ) ) : $page;
		$tabs    = $this->get_tabs();

		foreach ( $tabs as $tab ) {
			if ( $tab['key'] === $tab_key ) {
				return $tab_key;
			}
		}

		return $tabs[0]['key'] ?? '';
	}

	/**
	 * Returns the config for a specific tab by key.
	 *
	 * @param string $key The tab key.
	 * @return array The tab config, or an empty array if not found.
	 */
	protected function get_tab_config( string $key ): array {
		foreach ( $this->get_tabs() as $tab ) {
			if ( $tab['key'] === $key ) {
				return $tab;
			}
		}

		return array();
	}

	/**
	 * Returns translated strings to pass to JavaScript.
	 *
	 * @return array Associative array of translated strings.
	 */
	protected function get_strings() {
		return array(
			'fieldGroups'           => __( 'Field Groups', 'acf' ),
			'postTypes'             => __( 'Post Types', 'acf' ),
			'taxonomies'            => __( 'Taxonomies', 'acf' ),
			'settings'              => __( 'Settings', 'acf' ),
			'settingsTabs'          => __( 'Settings sections', 'acf' ),
			'save'                  => __( 'Save', 'acf' ),
			'saving'                => __( 'Saving...', 'acf' ),
			'opensInNewTab'         => __( 'Opens in a new tab', 'acf' ),
			'learnMore'             => __( 'Learn more', 'acf' ),
			/* translators: %s: the setting label */
			'learnMoreAbout'        => __( 'Learn more about %s', 'acf' ),
			'managedViaCode'        => __( 'Managed via code', 'acf' ),
			'managedViaCodeTooltip' => __( 'This setting cannot be changed via the UI as it is set in your codebase.', 'acf' ),
			'pro'                   => __( 'PRO', 'acf' ),
			'experimental'          => __( 'Experimental', 'acf' ),
			'licenseExpired'        => __( 'License expired', 'acf' ),
			'saveSuccessMessage'    => __( 'Your changes have been saved successfully.', 'acf' ),
			'saveErrorMessage'      => __( 'Something went wrong while saving your settings. Please try again.', 'acf' ),
			/* translators: %s: comma-separated list of setting keys */
			'settingsNotSaved'      => __( 'These settings could not be saved: %s.', 'acf' ),
			'unsavedChanges'        => __( 'The changes you made will be lost if you navigate away from this page', 'acf' ),
		);
	}

	/**
	 * Returns all settings page tabs.
	 *
	 * Each tab is an associative array with:
	 *   - 'key'           (string)   Tab identifier, used to build the page slug "acf-settings-tab-{key}". Use lowercase.
	 *   - 'label'         (string)   Translated display label.
	 *   - 'order'         (int)      Optional. Sort order, lower comes first. Defaults to 100.
	 *   - 'count'         (int|callable) Optional. Badge count shown on the tab, or a callable returning it.
	 *   - 'get_tab_data'  (callable) Optional. Returns the data passed to JS as acfSettingsPage.tabData.
	 *                                Only called when the tab is loaded. To render fields with the default
	 *                                UI, return array( 'sections' => array( ... ) ).
	 *   - 'tab_data'      (array)    Optional. Static alternative to 'get_tab_data'.
	 *   - 'js'            (string)   Optional. Full URL to a script that renders the tab's own UI inside the
	 *                                settings page, by calling acf.settingsPage.registerTab( key, { mount, unmount } ).
	 *                                Loaded after ACF's settings app on every settings page.
	 *   - 'css'           (string)   Optional. Full URL to a stylesheet for the tab's own UI, loaded after ACF's.
	 *   - 'strings'       (array)    Optional. Extra translated strings to merge for this tab.
	 *   - 'ajax_callback' (callable) Optional. Custom AJAX handler for this tab, called with no arguments.
	 *                                Should return a response array or WP_Error. When not set,
	 *                                the default settings save handler is used.
	 *   - 'enqueue'       (callable) Optional. Enqueues extra scripts or styles the tab needs. Called on
	 *                                every settings page load, whichever tab is active.
	 *   - 'version'       (string)   Optional. Version for the 'js' and 'css' assets. Defaults to ACF's version.
	 *
	 * The keys in RESERVED_TAB_KEYS ('license' and 'updates') belong to ACF PRO's own tabs, which mark themselves
	 * with the internal '_builtin' flag. Other tabs using those keys are not added.
	 *
	 * Sections with no fields, on any tab, are not rendered.
	 *
	 * Each section is an array with 'title' (string) and 'fields' (array). Each field supports:
	 *   - 'key'         (string)  Setting key, used for saving and initial value. Must be lowercase (fields with
	 *                             other keys aren't rendered), and registered with the
	 *                             'acf/settings/allowed_db_settings' filter so it can be saved.
	 *   - 'type'        (string)  Field type: 'toggle', 'select' or 'text'.
	 *   - 'label'       (string)  Translated field label.
	 *   - 'value'       (mixed)   Current value of the setting. A null value (e.g. a new setting that hasn't
	 *                             been saved yet) is shown as off for toggles and empty otherwise.
	 *   - 'options'     (array)   Required for 'select'. Each option has 'label', 'value' and optional 'disabled'.
	 *   - 'description' (string)  Optional. Translated description text. Shown under select and text controls.
	 *   - 'managed'     (bool)    Optional. Whether the setting is locked in the UI. Settings managed via code
	 *                             (see acf_is_setting_managed()) are always locked, so this is only needed
	 *                             for settings locked for some other reason.
	 *   - 'pro'         (bool)    Optional. Whether to show a "PRO" pill next to the setting.
	 *   - 'proExpired'  (bool)    Optional. Whether the "PRO" pill should show the license as expired.
	 *   - 'proLocked'   (bool)    Optional. Whether the setting is disabled because it requires ACF PRO.
	 *   - 'proUrl'      (string)  Optional. URL the "PRO" pill links to.
	 *   - 'proTooltip'  (string)  Optional. Tooltip text shown when hovering the "PRO" pill.
	 *   - 'experimental' (bool)   Optional. Whether to show an "Experimental" pill next to the setting, for
	 *                             features that may change in a future release.
	 *   - 'learnMoreUrl' (string) Optional. URL for a "Learn more" link, after the label on toggles and under the control otherwise.
	 *
	 * @return array[] Array of tab definitions.
	 */
	protected function get_tabs() {
		if ( $this->tabs !== null ) {
			return $this->tabs;
		}

		$tabs = array(
			array(
				'key'          => 'general',
				'label'        => __( 'Features', 'acf' ),
				'order'        => 10,
				// We pass the tab data as an anonymous function so it only gets executed when we are on that tab, instead of every admin page.
				'get_tab_data' => function () {
					return array(
						'sections' => $this->get_general_sections(
							array(
								array(
									'title'  => __( 'Interface', 'acf' ),
									'fields' => $this->get_section_fields(
										'interface',
										array(
											array(
												'key'         => 'enable_post_types',
												'type'        => 'toggle',
												'label'       => __( 'Post Types & Taxonomies UI', 'acf' ),
												'description' => __( 'Create and manage custom post types and taxonomies in ACF.', 'acf' ),
												'value'       => acf_get_setting( 'enable_post_types' ),
												'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/post-types-and-taxonomies/', 'docs', 'settings-page' ),
											),
											array(
												'key'         => 'remove_wp_meta_box',
												'type'        => 'toggle',
												'label'       => __( 'Remove WP Meta Box', 'acf' ),
												'description' => __( 'Hide the WordPress Custom Fields panel on edit screens.', 'acf' ),
												'value'       => acf_get_setting( 'remove_wp_meta_box' ),
											),
										)
									),
								),
								array(
									'title'  => __( 'API & Scripts', 'acf' ),
									'fields' => $this->get_section_fields(
										'api_scripts',
										array(
											array(
												'key'         => 'enable_shortcode',
												'type'        => 'toggle',
												'label'       => __( 'ACF Shortcode', 'acf' ),
												'description' => __( 'Display field values with the [acf] shortcode. Off by default for security.', 'acf' ),
												'value'       => acf_get_setting( 'enable_shortcode' ),
												'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/shortcode/', 'docs', 'settings-page' ),
											),
											array(
												'key'         => 'rest_api_enabled',
												'type'        => 'toggle',
												'label'       => __( 'ACF REST API', 'acf' ),
												'description' => __( 'Include field values in REST API responses.', 'acf' ),
												'value'       => acf_get_setting( 'rest_api_enabled' ),
												'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/wp-rest-api-integration/', 'docs', 'settings-page' ),
											),
											array(
												'key'          => 'rest_api_format',
												'type'         => 'select',
												'label'        => __( 'REST API Format', 'acf' ),
												'value'        => acf_get_setting( 'rest_api_format' ),
												'options'      => array(
													array(
														'label'    => __( 'Light', 'acf' ),
														'value'    => 'light',
														'disabled' => false,
													),
													array(
														'label'    => __( 'Standard', 'acf' ),
														'value'    => 'standard',
														'disabled' => false,
													),
												),
											),
										)
									),
								),
								array(
									'title'  => __( 'AI & GEO', 'acf' ),
									'fields' => $this->get_section_fields(
										'ai',
										array(
											array(
												'key'         => 'enable_abilities',
												'type'        => 'toggle',
												'label'       => __( 'Abilities API', 'acf' ),
												'description' => __( 'Let AI tools manage ACF data via the Abilities API.', 'acf' ),
												'value'       => acf_get_setting( 'enable_abilities' ),
												'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/abilities-api/', 'docs', 'settings-page' ),
											),
											array(
												'key'         => 'enable_schema',
												'type'        => 'toggle',
												'label'       => __( 'JSON-LD Schema Support', 'acf' ),
												'description' => __( 'Output fields as schema.org structured data.', 'acf' ),
												'value'       => acf_get_setting( 'enable_schema' ),
												'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/automatic-structured-data-with-schema-org/', 'docs', 'settings-page' ),
											),
										)
									),
								),
								array(
									'title'  => __( 'Field Specific Settings', 'acf' ),
									'fields' => $this->get_section_fields(
										'field_specific',
										array(
											array_merge(
												$this->get_google_map_field( 'google_api_key', 'key', __( 'Google Maps API Key', 'acf' ) ),
												array(
													'description'  => __( 'Google Maps fields require an API key with the Maps JavaScript, Geocoding and Places APIs enabled.', 'acf' ),
													'learnMoreUrl' => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/google-map/', 'docs', 'settings-page' ),
												)
											),
											array_merge(
												$this->get_google_map_field( 'google_api_client', 'client', __( 'Google Maps API Client', 'acf' ) ),
												array(
													'description' => __( 'For legacy Google Maps Premium Plan accounts only.', 'acf' ),
												)
											),
										)
									),
								),
							)
						),
					);
				},
			),
		);

		// Remove the Features tab when the settings UI is disabled with the "acf/settings/show_settings" filter.
		if ( ! acf_get_setting( 'show_settings' ) ) {
			$tabs = array();
		}

		/**
		 * Filters the settings page tabs.
		 *
		 * Each tab is an associative array with 'key', 'label', and optionally
		 * 'order', 'count', 'get_tab_data', 'tab_data', 'js', 'css', 'version',
		 * 'strings', 'ajax_callback' and 'enqueue'. See get_tabs() for details.
		 *
		 * @param array[] $tabs Array of tab definitions.
		 */
		$default_tabs = $tabs;
		$tabs         = apply_filters( 'acf/settings_page/tabs', $tabs );

		// A callback that forgets to return the tabs would otherwise remove every tab, including ACF's own.
		if ( ! is_array( $tabs ) ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html__( 'The "acf/settings_page/tabs" filter must return an array of tabs.', 'acf' ),
				'6.8.12'
			);
			$tabs = $default_tabs;
		}

		// Tab keys are used in URLs and compared against sanitized request values, so normalize them once here.
		foreach ( $tabs as $index => $tab ) {
			$key = isset( $tab['key'] ) && is_string( $tab['key'] ) ? sanitize_key( $tab['key'] ) : '';

			// A tab without a usable key would register an admin page with the slug "acf-settings-tab-".
			if ( '' === $key ) {
				_doing_it_wrong(
					__METHOD__,
					esc_html__( 'Settings page tabs need a "key" of lowercase alphanumeric characters, dashes and underscores. The tab has not been added.', 'acf' ),
					'6.8.12'
				);
				unset( $tabs[ $index ] );
				continue;
			}

			// The settings app renders ACF's own pages for these keys, so another tab using one would lose its fields.
			// They're reserved even when ACF's own tab isn't registered, e.g. in ACF Free or with "show_updates" off.
			if ( in_array( $key, self::RESERVED_TAB_KEYS, true ) && empty( $tab['_builtin'] ) ) {
				_doing_it_wrong(
					__METHOD__,
					sprintf(
						/* translators: %s: the reserved tab key */
						esc_html__( 'The settings page tab key "%s" is reserved for ACF. The tab has not been added.', 'acf' ),
						esc_html( $key )
					),
					'6.8.12'
				);
				unset( $tabs[ $index ] );
				continue;
			}

			if ( $key !== $tab['key'] ) {
				_doing_it_wrong(
					__METHOD__,
					sprintf(
						/* translators: 1: the tab key as registered, 2: the tab key it was changed to */
						esc_html__( 'Settings page tab keys should be lowercase alphanumeric characters, dashes and underscores. "%1$s" has been changed to "%2$s".', 'acf' ),
						esc_html( $tab['key'] ),
						esc_html( $key )
					),
					'6.8.12'
				);
				$tabs[ $index ]['key'] = $key;
			}
		}

		// Sort tabs by optional 'order' (int; lower comes first). Tabs without an order default to 100.
		usort(
			$tabs,
			function ( $a, $b ) {
				$order_a = isset( $a['order'] ) ? (int) $a['order'] : 100;
				$order_b = isset( $b['order'] ) ? (int) $b['order'] : 100;
				return $order_a - $order_b;
			}
		);

		$this->tabs = $tabs;

		return $this->tabs;
	}

	/**
	 * Returns a Google Maps setting field showing the value the Google Map field actually uses.
	 *
	 * The "acf/fields/google_map/api" filter runs after acf_get_setting() and wins over it, so when it
	 * changes the value, show that value and lock the field.
	 *
	 * @since 6.8.12
	 *
	 * @param string $setting The setting key.
	 * @param string $api_key The matching key in the Google Maps API args.
	 * @param string $label   The field label.
	 * @return array
	 */
	protected function get_google_map_field( string $setting, string $api_key, string $label ): array {
		$value = acf_get_setting( $setting );

		// Match the args the Google Map field passes to the filter.
		$api = apply_filters(
			'acf/fields/google_map/api',
			array(
				'key'       => acf_get_setting( 'google_api_key' ),
				'client'    => acf_get_setting( 'google_api_client' ),
				'libraries' => 'places',
				'ver'       => 3,
				'callback'  => 'Function.prototype',
				'language'  => acf_get_locale(),
			)
		);

		$effective = is_array( $api ) && isset( $api[ $api_key ] ) ? $api[ $api_key ] : $value;

		return array(
			'key'     => $setting,
			'type'    => 'text',
			'label'   => $label,
			'value'   => $effective,
			'managed' => $effective !== $value,
		);
	}

	/**
	 * Returns the fields for a section of the General tab, allowing them to be filtered.
	 *
	 * @since 6.8.9
	 *
	 * @param string  $section The section key.
	 * @param array[] $fields  The section fields.
	 * @return array[]
	 */
	protected function get_section_fields( string $section, array $fields ): array {
		/**
		 * Filters the fields in a section of the General settings tab.
		 *
		 * The dynamic portion of the hook name, `$section`, refers to the section key:
		 * 'interface', 'api_scripts', 'ai', or 'field_specific'.
		 * Sections left with no fields are not rendered.
		 *
		 * See the get_tabs() docblock for the supported field keys. For example, set
		 * 'experimental' to true to show an "Experimental" pill next to the setting.
		 *
		 * @since 6.8.9
		 *
		 * @param array[] $fields The section fields.
		 */
		return apply_filters( "acf/settings_page/general/{$section}/fields", $fields );
	}

	/**
	 * Returns the sections of the General tab, allowing new sections to be added.
	 *
	 * @since 6.8.12
	 *
	 * @param array[] $sections The built-in sections.
	 * @return array[]
	 */
	protected function get_general_sections( array $sections ): array {
		/**
		 * Filters the sections of the General settings tab.
		 *
		 * Use this to add a new section. To add fields to a built-in section, use the
		 * "acf/settings_page/general/{$section}/fields" filter instead. Each section is
		 * an array with 'title' (string) and 'fields' (array). Sections with no fields
		 * are not rendered.
		 *
		 * @since 6.8.12
		 *
		 * @param array[] $sections The sections.
		 */
		$sections = apply_filters( 'acf/settings_page/general/sections', $sections );

		return is_array( $sections ) ? $sections : array();
	}

	/**
	 * Removes sections that have no fields.
	 *
	 * @since 6.8.12
	 *
	 * @param array[] $sections The sections.
	 * @return array[]
	 */
	protected function remove_empty_sections( array $sections ): array {
		return array_values(
			array_filter(
				$sections,
				function ( $section ) {
					return ! empty( $section['fields'] );
				}
			)
		);
	}

	/**
	 * Returns the admin URL for a tab based on its key.
	 *
	 * @param string $key The tab key.
	 * @return string
	 */
	protected function get_tab_url( string $key ): string {
		return 'edit.php?post_type=acf-field-group&page=' . self::PAGE_SLUG_PREFIX . $key;
	}

	/**
	 * Sanitizes sections data for localization.
	 *
	 * @param array[] $sections Raw sections array.
	 * @return array[]
	 */
	protected function sanitize_sections( array $sections ): array {
		// Sections come from filters, so skip any that aren't an array with an array of fields. They'd have no fields to show.
		// Re-index so the sections JSON-encode as an array.
		$sections = array_values(
			array_filter(
				$sections,
				function ( $section ) {
					return is_array( $section ) && isset( $section['fields'] ) && is_array( $section['fields'] );
				}
			)
		);

		return array_map(
			function ( $section ) {
				return array(
					'title'  => isset( $section['title'] ) && is_string( $section['title'] ) ? wp_strip_all_tags( $section['title'] ) : '',
					'fields' => array_values(
						array_filter(
							array_map(
								function ( $field ) {
									if ( ! is_array( $field ) || ! isset( $field['key'], $field['type'], $field['label'] ) || ! array_key_exists( 'value', $field ) ) {
										return null;
									}

									// The key is sent back as-is when saving, so it must already match the registered setting name.
									if ( sanitize_key( $field['key'] ) !== $field['key'] ) {
										_doing_it_wrong(
											self::class . '::sanitize_sections',
											sprintf(
												/* translators: %s: the settings field key */
												esc_html__( 'Settings page field keys must be lowercase alphanumeric characters, dashes and underscores. The field "%s" has not been rendered.', 'acf' ),
												esc_html( $field['key'] )
											),
											'6.8.12'
										);
										return null;
									}

									// A new setting has no value until it's saved, so fall back to an empty value for its type.
									$value = $field['value'];
									if ( null === $value ) {
										$value = 'toggle' === $field['type'] ? false : '';
									}

									$sanitized = array(
										'key'          => $field['key'],
										'type'         => sanitize_key( $field['type'] ),
										'label'        => wp_strip_all_tags( $field['label'] ),
										// Cast toggle values to proper PHP booleans so they JSON-encode as true/false.
										// get_option returns strings ("0"/"1") from DB; Boolean("0") is true in JS.
										'value'        => 'toggle' === $field['type'] ? wp_validate_boolean( $value ) : $value,
										'managed'      => ! empty( $field['managed'] ) || $this->is_setting_managed( $field['key'] ),
										'pro'          => ! empty( $field['pro'] ),
										'proExpired'   => ! empty( $field['proExpired'] ),
										'proLocked'    => ! empty( $field['proLocked'] ),
										'experimental' => ! empty( $field['experimental'] ),
										'disabled'     => ! empty( $field['disabled'] ),
									);

									if ( ! empty( $field['proTooltip'] ) ) {
										$sanitized['proTooltip'] = wp_strip_all_tags( $field['proTooltip'] );
									}

									if ( ! empty( $field['proUrl'] ) ) {
										// URLs from acf_add_url_utm_tags() are HTML-escaped, so decode them for use in JS.
										$sanitized['proUrl'] = esc_url_raw( html_entity_decode( $field['proUrl'] ) );
									}

									if ( ! empty( $field['learnMoreUrl'] ) ) {
										$sanitized['learnMoreUrl'] = esc_url_raw( html_entity_decode( $field['learnMoreUrl'] ) );
									}

									if ( ! empty( $field['description'] ) ) {
										$sanitized['description'] = wp_strip_all_tags( $field['description'] );
									}

									if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
										$options = array();

										foreach ( $field['options'] as $option ) {
											// An option can't be selected without a value, so skip it. A missing label falls back to the value.
											if ( ! is_array( $option ) || ! isset( $option['value'] ) || ! is_scalar( $option['value'] ) ) {
												continue;
											}

											$label = isset( $option['label'] ) && is_scalar( $option['label'] ) ? (string) $option['label'] : (string) $option['value'];

											$options[] = array(
												'label'    => wp_strip_all_tags( $label ),
												'value'    => sanitize_text_field( (string) $option['value'] ),
												'disabled' => (bool) ( $option['disabled'] ?? false ),
											);
										}

										$sanitized['options'] = $options;
									}

									return $sanitized;
								},
								$section['fields']
							)
						)
					),
				);
			},
			$sections
		);
	}

	/**
	 * Handles AJAX requests for saving settings.
	 *
	 * @return mixed The response data or WP_Error.
	 */
	public function get_ajax_response() {

		try {
			$raw_settings = acf_maybe_get_POST( 'acfSettings', '' );

			if ( empty( $raw_settings ) ) {
				return new \WP_Error( 'acf_missing_settings', __( 'No settings provided.', 'acf' ), array( 'status' => 400 ) );
			}

			$incoming = json_decode( wp_unslash( $raw_settings ), true );

			if ( ! is_array( $incoming ) ) {
				return new \WP_Error( 'acf_invalid_settings', __( 'Invalid settings format.', 'acf' ), array( 'status' => 400 ) );
			}

			$allowed_settings = acf_get_allowed_db_settings();
			$updated_values   = array();
			$skipped          = array();
			$failed           = array();

			foreach ( $incoming as $key => $value ) {
				if ( ! isset( $allowed_settings[ $key ] ) ) {
					$skipped[] = (string) $key;
					continue;
				}

				// Skip settings overridden by a filter — they cannot be changed via the UI.
				if ( $this->is_setting_managed( $key ) ) {
					$skipped[] = (string) $key;
					continue;
				}

				// Save each setting on its own, so one that fails doesn't stop the rest or hide which were saved.
				try {
					$update_success = acf_update_setting( $key, $value, true );
				} catch ( \Throwable $e ) {
					// E.g. a sanitize callback that throws.
					$update_success = new \WP_Error( 'acf_update_setting_failed', $e->getMessage() );
				}

				if ( is_wp_error( $update_success ) ) {
					$failed[] = (string) $key;
					continue;
				}

				$raw                    = acf_get_setting( $key );
				$sanitizer              = $allowed_settings[ $key ]['sanitize_callback'] ?? null;
				$updated_values[ $key ] = $sanitizer ? call_user_func( $sanitizer, $raw ) : $raw;
			}

			$not_saved = array_merge( $skipped, $failed );

			// Don't report success when nothing could be saved, e.g. a setting that isn't registered with "acf/settings/allowed_db_settings".
			if ( empty( $updated_values ) && ! empty( $not_saved ) ) {
				return new \WP_Error(
					'acf_settings_not_saved',
					/* translators: %s: comma-separated list of setting keys */
					sprintf( __( 'These settings could not be saved: %s.', 'acf' ), implode( ', ', $not_saved ) ),
					array(
						// Skipped settings need a code change, while a failed save may work if retried.
						'status'  => empty( $failed ) ? 400 : 500,
						'skipped' => $skipped,
						'failed'  => $failed,
					)
				);
			}

			// The settings page warns about any settings in 'skipped' or 'failed', as those weren't saved.
			return array(
				'message'  => __( 'Settings saved.', 'acf' ),
				'settings' => $updated_values,
				'skipped'  => $skipped,
				'failed'   => $failed,
			);
		} catch ( \Throwable $e ) {
			return new \WP_Error( 'error', __( 'Something went wrong', 'acf' ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Displays the HTML for the admin page.
	 *
	 * @since 6.8.2
	 *
	 * @return void
	 */
	public function admin_html() {
		echo '<style>
			#wpcontent { padding-left: 0 !important; }
			@keyframes acf-page-in { from { opacity: 0; } to { opacity: 1; } }
			#acf-settings-app { animation: 150ms ease acf-page-in; }
			#wpfooter a { color: #0059b2; }
		</style>';
		echo '<div id="acf-settings-app" data-bds-theme="light"></div>';
	}
}
