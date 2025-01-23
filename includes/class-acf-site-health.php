<?php
/**
 * Adds helpful debugging information to a new "Advanced Custom Fields"
 * panel in the WordPress Site Health screen.
 *
 * @package ACF
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACF_Site_Health' ) ) {

	/**
	 * The ACF Site Health class responsible for populating ACF debug information in WordPress Site Health.
	 */
	class ACF_Site_Health {
		/**
		 * The option name used to store site health data.
		 *
		 * @var string
		 */
		public string $option_name = 'acf_site_health';

		/**
		 * Constructs the ACF_Site_Health class.
		 *
		 * @since 6.3
		 */
		public function __construct() {
			add_action( 'debug_information', array( $this, 'render_tab_content' ) );
			add_action( 'acf_update_site_health_data', array( $this, 'update_site_health_data' ) );

			if ( ! wp_next_scheduled( 'acf_update_site_health_data' ) ) {
				wp_schedule_event( time(), 'weekly', 'acf_update_site_health_data' );
			}

			// ACF events.
			add_action( 'acf/first_activated', array( $this, 'add_activation_event' ) );
			add_action( 'acf/activated_pro', array( $this, 'add_activation_event' ) );
			add_filter( 'acf/pre_update_field_group', array( $this, 'pre_update_acf_internal_cpt' ) );
			add_filter( 'acf/pre_update_post_type', array( $this, 'pre_update_acf_internal_cpt' ) );
			add_filter( 'acf/pre_update_taxonomy', array( $this, 'pre_update_acf_internal_cpt' ) );
			add_filter( 'acf/pre_update_ui_options_page', array( $this, 'pre_update_acf_internal_cpt' ) );
		}

		/**
		 * Gets the stored site health information.
		 *
		 * @since 6.3
		 *
		 * @return array
		 */
		public function get_site_health(): array {
			$site_health = get_option( $this->option_name, '' );

			if ( is_string( $site_health ) ) {
				$site_health = json_decode( $site_health, true );
			}

			return is_array( $site_health ) ? $site_health : array();
		}

		/**
		 * Updates the site health information.
		 *
		 * @since 6.3
		 *
		 * @param array $data An array of site health information to update.
		 * @return boolean
		 */
		public function update_site_health( array $data = array() ): bool {
			return update_option( $this->option_name, wp_json_encode( $data ), false );
		}

		/**
		 * Stores debug data in the ACF site health option.
		 *
		 * @since 6.3
		 *
		 * @param array $data Data to update with (optional).
		 * @return boolean
		 */
		public function update_site_health_data( array $data = array() ): bool {
			if ( wp_doing_cron() ) {
				// Bootstrap wp-admin, as WP_Cron doesn't do this for us.
				require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/admin.php';
			}

			$site_health = $this->get_site_health();
			$values      = ! empty( $data ) ? $data : $this->get_site_health_values();
			$updated     = array();

			if ( ! empty( $values ) ) {
				foreach ( $values as $key => $value ) {
					$updated[ $key ] = $value['debug'] ?? $value['value'];
				}
			}

			foreach ( $site_health as $key => $value ) {
				if ( 'event_' === substr( $key, 0, 6 ) ) {
					$updated[ $key ] = $value;
				}
			}

			$updated['last_updated'] = time();

			return $this->update_site_health( $updated );
		}

		/**
		 * Pushes an event to the ACF site health option.
		 *
		 * @since 6.3
		 *
		 * @param string $event_name The name of the event to push.
		 * @return boolean
		 */
		public function add_site_health_event( string $event_name = '' ): bool {
			$site_health = $this->get_site_health();

			// Allow using action/filter hooks to set events.
			if ( empty( $event_name ) ) {
				$current_filter = current_filter();

				if ( strpos( $current_filter, 'acf/' ) !== false ) {
					$event_name = str_replace( 'acf/', '', $current_filter );
				}
			}

			// Bail if this event was already stored.
			if ( empty( $event_name ) || ! empty( $site_health[ 'event_' . $event_name ] ) ) {
				return false;
			}

			$time = time();

			$site_health[ 'event_' . $event_name ] = $time;
			$site_health['last_updated']           = $time;

			return $this->update_site_health( $site_health );
		}

		/**
		 * Logs activation events for free/pro.
		 *
		 * @since 6.3
		 *
		 * @return boolean
		 */
		public function add_activation_event() {
			$event_name = 'first_activated';

			if ( acf_is_pro() ) {
				$event_name = 'first_activated_pro';

				if ( 'acf/first_activated' !== current_filter() ) {
					$site_health = $this->get_site_health();

					/**
					 * We already have an event for when pro was first activated,
					 * so we don't need to log an additional event here.
					 */
					if ( ! empty( $site_health[ 'event_' . $event_name ] ) ) {
						return false;
					}

					$event_name = 'activated_pro';
				}
			}

			return $this->add_site_health_event( $event_name );
		}

		/**
		 * Adds events when ACF internal post types are created.
		 *
		 * @since 6.3
		 *
		 * @param array $post The post about to be updated.
		 * @return array
		 */
		public function pre_update_acf_internal_cpt( array $post = array() ): array {
			if ( empty( $post['key'] ) ) {
				return $post;
			}

			$post_type = acf_determine_internal_post_type( $post['key'] );

			if ( $post_type ) {
				$posts = acf_get_internal_post_type_posts( $post_type );

				if ( empty( $posts ) ) {
					$post_type = str_replace(
						array(
							'acf-',
							'-',
						),
						array(
							'',
							'_',
						),
						$post_type
					);
					$this->add_site_health_event( 'first_created_' . $post_type );
				}
			}

			return $post;
		}

		/**
		 * Appends the ACF section to the "Info" tab of the WordPress Site Health screen.
		 *
		 * @since 6.3
		 *
		 * @param array $debug_info The current debug info for site health.
		 * @return array The debug info appended with the ACF section.
		 */
		public function render_tab_content( array $debug_info ): array {
			$data = $this->get_site_health_values();

			$this->update_site_health_data( $data );

			// Unset values we don't want to display yet.
			$fields_to_unset = array(
				'wp_version',
				'mysql_version',
				'is_multisite',
				'active_theme',
				'parent_theme',
				'active_plugins',
				'number_of_fields_by_type',
				'number_of_third_party_fields_by_type',
			);

			foreach ( $fields_to_unset as $field ) {
				if ( isset( $data[ $field ] ) ) {
					unset( $data[ $field ] );
				}
			}

			foreach ( $data as $key => $value ) {
				if ( 'event_' === substr( $key, 0, 6 ) ) {
					unset( $data[ $key ] );
				}
			}

			$debug_info['acf'] = array(
				'label'       => __( 'ACF', 'acf' ),
				'description' => __( 'This section contains debug information about your ACF configuration which can be useful to provide to support.', 'acf' ),
				'fields'      => $data,
			);

			return $debug_info;
		}

		/**
		 * Gets the values for all data in the ACF site health section.
		 *
		 * @since 6.3
		 *
		 * @return array
		 */
		public function get_site_health_values(): array {
			global $wpdb;

			$fields         = array();
			$is_pro         = acf_is_pro();
			$license        = $is_pro ? acf_pro_get_license() : array();
			$license_status = $is_pro ? acf_pro_get_license_status() : array();
			$field_groups   = acf_get_field_groups();
			$post_types     = acf_get_post_types();
			$taxonomies     = acf_get_taxonomies();

			$yes = __( 'Yes', 'acf' );
			$no  = __( 'No', 'acf' );

			$fields['version'] = array(
				'label' => __( 'Plugin Version', 'acf' ),
				'value' => defined( 'ACF_VERSION' ) ? ACF_VERSION : '',
			);

			$fields['plugin_type'] = array(
				'label' => __( 'Plugin Type', 'acf' ),
				'value' => $is_pro ? __( 'PRO', 'acf' ) : __( 'Free', 'acf' ),
				'debug' => $is_pro ? 'PRO' : 'Free',
			);

			$fields['update_source'] = array(
				'label' => __( 'Update Source', 'acf' ),
				'value' => apply_filters( 'acf/site_health/update_source', __( 'wordpress.org', 'acf' ) ),
			);

			if ( $is_pro ) {
				$fields['activated'] = array(
					'label' => __( 'License Activated', 'acf' ),
					'value' => ! empty( $license ) ? $yes : $no,
					'debug' => ! empty( $license ),
				);

				$fields['activated_url'] = array(
					'label' => __( 'Licensed URL', 'acf' ),
					'value' => ! empty( $license['url'] ) ? $license['url'] : '',
				);

				$fields['license_type'] = array(
					'label' => __( 'License Type', 'acf' ),
					'value' => $license_status['name'],
				);

				$fields['license_status'] = array(
					'label' => __( 'License Status', 'acf' ),
					'value' => $license_status['status'],
				);

				$expiry = ! empty( $license_status['expiry'] ) ? $license_status['expiry'] : '';
				$format = get_option( 'date_format', 'F j, Y' );

				$fields['subscription_expires'] = array(
					'label' => __( 'Subscription Expiry Date', 'acf' ),
					'value' => is_numeric( $expiry ) ? date_i18n( $format, $expiry ) : '',
					'debug' => $expiry,
				);
			}

			$fields['wp_version'] = array(
				'label' => __( 'WordPress Version', 'acf' ),
				'value' => get_bloginfo( 'version' ),
			);

			$fields['mysql_version'] = array(
				'label' => __( 'MySQL Version', 'acf' ),
				'value' => $wpdb->db_server_info(),
			);

			$fields['is_multisite'] = array(
				'label' => __( 'Is Multisite', 'acf' ),
				'value' => is_multisite() ? __( 'Yes', 'acf' ) : __( 'No', 'acf' ),
				'debug' => is_multisite(),
			);

			$active_theme = wp_get_theme();
			$parent_theme = $active_theme->parent();

			$fields['active_theme'] = array(
				'label' => __( 'Active Theme', 'acf' ),
				'value' => array(
					'name'       => $active_theme->get( 'Name' ),
					'version'    => $active_theme->get( 'Version' ),
					'theme_uri'  => $active_theme->get( 'ThemeURI' ),
					'stylesheet' => $active_theme->get( 'Stylesheet' ),
				),
			);

			if ( $parent_theme ) {
				$fields['parent_theme'] = array(
					'label' => __( 'Parent Theme', 'acf' ),
					'value' => array(
						'name'       => $parent_theme->get( 'Name' ),
						'version'    => $parent_theme->get( 'Version' ),
						'theme_uri'  => $parent_theme->get( 'ThemeURI' ),
						'stylesheet' => $parent_theme->get( 'Stylesheet' ),
					),
				);
			}

			$active_plugins = array();
			$plugins        = get_plugins();

			foreach ( $plugins as $plugin_path => $plugin ) {
				if ( ! is_plugin_active( $plugin_path ) ) {
					continue;
				}

				$active_plugins[ $plugin_path ] = array(
					'name'       => $plugin['Name'],
					'version'    => $plugin['Version'],
					'plugin_uri' => empty( $plugin['PluginURI'] ) ? '' : $plugin['PluginURI'],
				);
			}

			$fields['active_plugins'] = array(
				'label' => __( 'Active Plugins', 'acf' ),
				'value' => $active_plugins,
			);

			$ui_field_groups = array_filter(
				$field_groups,
				function ( $field_group ) {
					return empty( $field_group['local'] );
				}
			);

			$fields['ui_field_groups'] = array(
				'label' => __( 'Registered Field Groups (UI)', 'acf' ),
				'value' => number_format_i18n( count( $ui_field_groups ) ),
			);

			$php_field_groups = array_filter(
				$field_groups,
				function ( $field_group ) {
					return ! empty( $field_group['local'] ) && 'PHP' === $field_group['local'];
				}
			);

			$fields['php_field_groups'] = array(
				'label' => __( 'Registered Field Groups (PHP)', 'acf' ),
				'value' => number_format_i18n( count( $php_field_groups ) ),
			);

			$json_field_groups = array_filter(
				$field_groups,
				function ( $field_group ) {
					return ! empty( $field_group['local'] ) && 'json' === $field_group['local'];
				}
			);

			$fields['json_field_groups'] = array(
				'label' => __( 'Registered Field Groups (JSON)', 'acf' ),
				'value' => number_format_i18n( count( $json_field_groups ) ),
			);

			$rest_field_groups = array_filter(
				$field_groups,
				function ( $field_group ) {
					return ! empty( $field_group['show_in_rest'] );
				}
			);

			$fields['rest_field_groups'] = array(
				'label' => __( 'Field Groups Enabled for REST API', 'acf' ),
				'value' => number_format_i18n( count( $rest_field_groups ) ),
			);

			$graphql_field_groups = array_filter(
				$field_groups,
				function ( $field_group ) {
					return ! empty( $field_group['show_in_graphql'] );
				}
			);

			if ( is_plugin_active( 'wpgraphql-acf/wpgraphql-acf.php' ) ) {
				$fields['graphql_field_groups'] = array(
					'label' => __( 'Field Groups Enabled for GraphQL', 'acf' ),
					'value' => number_format_i18n( count( $graphql_field_groups ) ),
				);
			}

			$all_fields = array();
			foreach ( $field_groups as $field_group ) {
				$all_fields = array_merge( $all_fields, acf_get_fields( $field_group ) );
			}

			$fields_by_type             = array();
			$third_party_fields_by_type = array();
			$core_field_types           = array_keys( acf_get_field_types() );

			foreach ( $all_fields as $field ) {
				if ( in_array( $field['type'], $core_field_types, true ) ) {
					if ( ! isset( $fields_by_type[ $field['type'] ] ) ) {
						$fields_by_type[ $field['type'] ] = 0;
					}

					++$fields_by_type[ $field['type'] ];

					continue;
				}

				if ( ! isset( $third_party_fields_by_type[ $field['type'] ] ) ) {
					$third_party_fields_by_type[ $field['type'] ] = 0;
				}

				++$third_party_fields_by_type[ $field['type'] ];
			}

			$fields['number_of_fields_by_type'] = array(
				'label' => __( 'Number of Fields by Field Type', 'acf' ),
				'value' => $fields_by_type,
			);

			$fields['number_of_third_party_fields_by_type'] = array(
				'label' => __( 'Number of Third Party Fields by Field Type', 'acf' ),
				'value' => $third_party_fields_by_type,
			);

			$enable_post_types = acf_get_setting( 'enable_post_types' );

			$fields['post_types_enabled'] = array(
				'label' => __( 'Post Types and Taxonomies Enabled', 'acf' ),
				'value' => $enable_post_types ? $yes : $no,
				'debug' => $enable_post_types,
			);

			$ui_post_types = array_filter(
				$post_types,
				function ( $post_type ) {
					return empty( $post_type['local'] );
				}
			);

			$fields['ui_post_types'] = array(
				'label' => __( 'Registered Post Types (UI)', 'acf' ),
				'value' => number_format_i18n( count( $ui_post_types ) ),
			);

			$json_post_types = array_filter(
				$post_types,
				function ( $post_type ) {
					return ! empty( $post_type['local'] ) && 'json' === $post_type['local'];
				}
			);

			$fields['json_post_types'] = array(
				'label' => __( 'Registered Post Types (JSON)', 'acf' ),
				'value' => number_format_i18n( count( $json_post_types ) ),
			);

			$ui_taxonomies = array_filter(
				$taxonomies,
				function ( $taxonomy ) {
					return empty( $taxonomy['local'] );
				}
			);

			$fields['ui_taxonomies'] = array(
				'label' => __( 'Registered Taxonomies (UI)', 'acf' ),
				'value' => number_format_i18n( count( $ui_taxonomies ) ),
			);

			$json_taxonomies = array_filter(
				$taxonomies,
				function ( $taxonomy ) {
					return ! empty( $taxonomy['local'] ) && 'json' === $taxonomy['local'];
				}
			);

			$fields['json_taxonomies'] = array(
				'label' => __( 'Registered Taxonomies (JSON)', 'acf' ),
				'value' => number_format_i18n( count( $json_taxonomies ) ),
			);

			if ( $is_pro ) {
				$enable_options_pages_ui = acf_get_setting( 'enable_options_pages_ui' );

				$fields['ui_options_pages_enabled'] = array(
					'label' => __( 'Options Pages UI Enabled', 'acf' ),
					'value' => $enable_options_pages_ui ? $yes : $no,
					'debug' => $enable_options_pages_ui,
				);

				$options_pages    = acf_get_options_pages();
				$ui_options_pages = array();

				if ( empty( $options_pages ) || ! is_array( $options_pages ) ) {
					$options_pages = array();
				}

				if ( $enable_options_pages_ui ) {
					$ui_options_pages = acf_get_ui_options_pages();

					$ui_options_pages_in_ui = array_filter(
						$ui_options_pages,
						function ( $ui_options_page ) {
							return empty( $ui_options_page['local'] );
						}
					);

					$json_options_pages = array_filter(
						$ui_options_pages,
						function ( $ui_options_page ) {
							return ! empty( $ui_options_page['local'] );
						}
					);

					$fields['ui_options_pages'] = array(
						'label' => __( 'Registered Options Pages (UI)', 'acf' ),
						'value' => number_format_i18n( count( $ui_options_pages_in_ui ) ),
					);

					$fields['json_options_pages'] = array(
						'label' => __( 'Registered Options Pages (JSON)', 'acf' ),
						'value' => number_format_i18n( count( $json_options_pages ) ),
					);
				}

				$ui_options_page_slugs = array_column( $ui_options_pages, 'menu_slug' );
				$php_options_pages     = array_filter(
					$options_pages,
					function ( $options_page ) use ( $ui_options_page_slugs ) {
						return ! in_array( $options_page['menu_slug'], $ui_options_page_slugs, true );
					}
				);

				$fields['php_options_pages'] = array(
					'label' => __( 'Registered Options Pages (PHP)', 'acf' ),
					'value' => number_format_i18n( count( $php_options_pages ) ),
				);
			}

			$rest_api_format = acf_get_setting( 'rest_api_format' );

			$fields['rest_api_format'] = array(
				'label' => __( 'REST API Format', 'acf' ),
				'value' => 'standard' === $rest_api_format ? __( 'Standard', 'acf' ) : __( 'Light', 'acf' ),
				'debug' => $rest_api_format,
			);

			if ( $is_pro ) {
				$fields['registered_acf_blocks'] = array(
					'label' => __( 'Registered ACF Blocks', 'acf' ),
					'value' => number_format_i18n( acf_pro_get_registered_block_count() ),
				);

				$blocks                 = acf_get_block_types();
				$block_api_versions     = array();
				$acf_block_versions     = array();
				$blocks_using_post_meta = 0;

				foreach ( $blocks as $block ) {
					if ( ! isset( $block_api_versions[ 'v' . $block['api_version'] ] ) ) {
						$block_api_versions[ 'v' . $block['api_version'] ] = 0;
					}

					if ( ! isset( $acf_block_versions[ 'v' . $block['acf_block_version'] ] ) ) {
						$acf_block_versions[ 'v' . $block['acf_block_version'] ] = 0;
					}

					if ( ! empty( $block['use_post_meta'] ) ) {
						++$blocks_using_post_meta;
					}

					++$block_api_versions[ 'v' . $block['api_version'] ];
					++$acf_block_versions[ 'v' . $block['acf_block_version'] ];
				}

				$fields['blocks_per_api_version'] = array(
					'label' => __( 'Blocks Per API Version', 'acf' ),
					'value' => $block_api_versions,
				);

				$fields['blocks_per_acf_block_version'] = array(
					'label' => __( 'Blocks Per ACF Block Version', 'acf' ),
					'value' => $acf_block_versions,
				);

				$fields['blocks_using_post_meta'] = array(
					'label' => __( 'Blocks Using Post Meta', 'acf' ),
					'value' => number_format_i18n( $blocks_using_post_meta ),
				);

				$preload_blocks = acf_get_setting( 'preload_blocks' );

				$fields['preload_blocks'] = array(
					'label' => __( 'Block Preloading Enabled', 'acf' ),
					'value' => ! empty( $preload_blocks ) ? $yes : $no,
					'debug' => $preload_blocks,
				);
			}

			$show_admin = acf_get_setting( 'show_admin' );

			$fields['admin_ui_enabled'] = array(
				'label' => __( 'Admin UI Enabled', 'acf' ),
				'value' => $show_admin ? $yes : $no,
				'debug' => $show_admin,
			);

			$field_type_modal_enabled = apply_filters( 'acf/field_group/enable_field_browser', true );

			$fields['field_type-modal_enabled'] = array(
				'label' => __( 'Field Type Modal Enabled', 'acf' ),
				'value' => ! empty( $field_type_modal_enabled ) ? $yes : $no,
				'debug' => $field_type_modal_enabled,
			);

			$field_settings_tabs_enabled = apply_filters( 'acf/field_group/disable_field_settings_tabs', false );

			$fields['field_settings_tabs_enabled'] = array(
				'label' => __( 'Field Settings Tabs Enabled', 'acf' ),
				'value' => empty( $field_settings_tabs_enabled ) ? $yes : $no,
				'debug' => $field_settings_tabs_enabled,
			);

			$shortcode_enabled = acf_get_setting( 'enable_shortcode' );

			$fields['shortcode_enabled'] = array(
				'label' => __( 'Shortcode Enabled', 'acf' ),
				'value' => ! empty( $shortcode_enabled ) ? $yes : $no,
				'debug' => $shortcode_enabled,
			);

			$fields['registered_acf_forms'] = array(
				'label' => __( 'Registered ACF Forms', 'acf' ),
				'value' => number_format_i18n( count( acf_get_forms() ) ),
			);

			$local_json = acf_get_instance( 'ACF_Local_JSON' );
			$save_paths = $local_json->get_save_paths();
			$load_paths = $local_json->get_load_paths();

			$fields['json_save_paths'] = array(
				'label' => __( 'JSON Save Paths', 'acf' ),
				'value' => number_format_i18n( count( $save_paths ) ),
				'debug' => count( $save_paths ),
			);

			$fields['json_load_paths'] = array(
				'label' => __( 'JSON Load Paths', 'acf' ),
				'value' => number_format_i18n( count( $load_paths ) ),
				'debug' => count( $load_paths ),
			);

			return $fields;
		}
	}

	acf_new_instance( 'ACF_Site_Health' );
}
