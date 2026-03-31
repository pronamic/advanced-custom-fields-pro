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

namespace ACF\CLI;

use WP_CLI;
use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\get_flag_value;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages ACF JSON import, export, and synchronization.
 *
 * ## EXAMPLES
 *
 *     # Show sync status for all item types (field groups, post types, taxonomies, options pages)
 *     $ wp acf json status
 *
 *     # Sync all pending local JSON changes to database
 *     $ wp acf json sync
 *
 *     # Import from a JSON file
 *     $ wp acf json import ./acf-export.json
 *
 *     # Export all items to a directory
 *     $ wp acf json export --dir=./exports/
 *
 *     # Export to stdout
 *     $ wp acf json export --stdout
 */
class JsonCommand {

	/**
	 * Map of CLI type flags to internal ACF post types.
	 *
	 * @var array
	 */
	private const TYPE_MAP = array(
		'field-group'  => 'acf-field-group',
		'post-type'    => 'acf-post-type',
		'taxonomy'     => 'acf-taxonomy',
		'options-page' => 'acf-ui-options-page',
	);

	/**
	 * Success message when there are no items to sync.
	 *
	 * @var string
	 */
	private const MESSAGE_ALREADY_IN_SYNC = 'Everything is already in sync.';

	/**
	 * Records a first-run event for a CLI sub-command.
	 *
	 * @since 6.8
	 *
	 * @param string $subcommand The sub-command name (e.g., 'status', 'sync', 'import', 'export').
	 */
	private function log_command( $subcommand ) {
		$site_health = acf_get_instance( 'ACF\Site_Health\Site_Health' );

		if ( method_exists( $site_health, 'log_cli_command' ) ) {
			$site_health->log_cli_command( 'acf json ' . $subcommand );
		}
	}

	/**
	 * Shows the sync status for ACF items.
	 *
	 * Displays how many items are pending sync. Items are considered "pending"
	 * when the JSON file is newer than the database entry, or when the item
	 * exists in JSON but not in the database.
	 *
	 * ## OPTIONS
	 *
     * [--type=<type>]
     * : Limit to field groups, post types, taxonomies, or options pages. Defaults to all item types (field groups, post types, taxonomies, options pages).
	 * ---
	 * options:
	 *   - field-group
	 *   - post-type
	 *   - taxonomy
	 *   - options-page
	 * ---
	 *
	 * [--detailed]
	 * : Show detailed list of modified items instead of just counts.
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 *   - csv
	 * ---
	 *
	 * ## EXAMPLES
	 *
     *     # Check all item types
     *     $ wp acf json status
	 *     +---------------+---------+-------+----------------+
	 *     | Type          | Pending | Total | Status         |
	 *     +---------------+---------+-------+----------------+
	 *     | field-group   | 3       | 12    | Sync available |
	 *     | post-type     | 0       | 2     | In sync        |
	 *     | taxonomy      | 1       | 3     | Sync available |
	 *     | options-page  | 0       | 1     | In sync        |
	 *     +---------------+---------+-------+----------------+
	 *
	 *     # Check only field groups
	 *     $ wp acf json status --type=field-group
	 *
	 *     # Show detailed list of pending items
	 *     $ wp acf json status --detailed
	 *     +-------------------+------------------+---------------+--------+
	 *     | Key               | Title            | Type          | Action |
	 *     +-------------------+------------------+---------------+--------+
	 *     | group_abc123      | Product Fields   | field-group   | Update |
	 *     | group_def456      | Homepage         | field-group   | Create |
	 *     | taxonomy_ghi789   | Product Category | taxonomy      | Update |
	 *     +-------------------+------------------+---------------+--------+
	 *
	 *     # Output status as JSON for scripts
	 *     $ wp acf json status --format=json
	 *     [{"Type":"field-group","Pending":3,"Total":12,"Status":"Sync available"}]
	 *
	 * @since 6.8
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function status( $args, $assoc_args ) {
		$this->log_command( 'status' );

		$type_filter = get_flag_value( $assoc_args, 'type' );
		$format      = get_flag_value( $assoc_args, 'format', 'table' );
		$detailed    = get_flag_value( $assoc_args, 'detailed', false );
		$post_types  = $this->get_post_types( $type_filter );

		if ( $detailed ) {
			$this->display_detailed_status( $post_types, $format );
			return;
		}

		$rows          = array();
		$total_pending = 0;

		foreach ( $post_types as $post_type ) {
			$syncable       = $this->get_syncable_items( $post_type );
			$all_items      = acf_get_internal_post_type_posts( $post_type );
			$count          = count( $syncable );
			$total_count    = count( $all_items );
			$total_pending += $count;

			$rows[] = array(
				'Type'    => $this->get_type_label( $post_type ),
				'Pending' => $count,
				'Total'   => $total_count,
				'Status'  => $count > 0 ? 'Sync available' : 'In sync',
			);
		}

		format_items( $format, $rows, array( 'Type', 'Pending', 'Total', 'Status' ) );

		if ( 'table' === $format ) {
			if ( $total_pending > 0 ) {
				WP_CLI::log( sprintf( '%d item(s) pending sync. Run `wp acf json sync` to apply changes.', $total_pending ) );
			} else {
				WP_CLI::success( self::MESSAGE_ALREADY_IN_SYNC );
			}
		}
	}

	/**
	 * Syncs local JSON changes to the database.
	 *
	 * Imports pending JSON changes for ACF items (field groups, post types,
	 * taxonomies, and options pages). This command reads JSON files from your
	 * theme/plugin acf-json directory and creates or updates the corresponding
	 * database entries.
	 *
	 * WARNING: This command modifies your database. Use --dry-run first to
	 * preview changes before running on production.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Limit sync to a specific item type. Defaults to all item types (field groups, post types, taxonomies, options pages).
	 * ---
	 * options:
	 *   - field-group
	 *   - post-type
	 *   - taxonomy
	 *   - options-page
	 * ---
	 *
	 * [--key=<key>]
	 * : Sync a specific item by its ACF key (e.g., group_abc123).
	 *
	 * [--dry-run]
	 * : Preview what would be synced without making changes. Recommended for
	 * production deployments.
	 *
	 * ## EXAMPLES
	 *
	 *     # Preview what will be synced (safe)
	 *     $ wp acf json sync --dry-run
	 *     3 item(s) pending sync:
	 *     +-------------------+------------------+---------------+--------+
	 *     | Key               | Title            | Type          | Action |
	 *     +-------------------+------------------+---------------+--------+
	 *     | group_abc123      | Product Fields   | field-group   | Update |
	 *     +-------------------+------------------+---------------+--------+
	 *
	 *     # Sync all pending changes
	 *     $ wp acf json sync
	 *     Updated field-group: Product Fields (group_abc123)
	 *     Success: 1 item(s) synced.
	 *
	 *     # Sync only field groups (during deployment)
	 *     $ wp acf json sync --type=field-group
	 *
	 *     # Sync a specific field group after manual JSON edit
	 *     $ wp acf json sync --key=group_abc123
	 *
	 *     # CI/CD deployment workflow
	 *     $ wp acf json status --format=json | jq '.[] | select(.Pending > 0)'
	 *     $ wp acf json sync --dry-run
	 *     $ wp acf json sync
	 *
	 * @since 6.8
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function sync( $args, $assoc_args ) {
		$this->log_command( 'sync' );

		$type_filter = get_flag_value( $assoc_args, 'type' );
		$key_filter  = get_flag_value( $assoc_args, 'key' );
		$dry_run     = get_flag_value( $assoc_args, 'dry-run', false );

		$post_types = $this->get_post_types( $type_filter );

		$all_syncable = array();

		foreach ( $post_types as $post_type ) {
			$syncable = $this->get_syncable_items( $post_type );

			foreach ( $syncable as $key => $post ) {
				$all_syncable[ $key ] = array(
					'post'      => $post,
					'post_type' => $post_type,
				);
			}
		}

		if ( $key_filter ) {
			if ( ! isset( $all_syncable[ $key_filter ] ) ) {
				WP_CLI::error(
					sprintf(
						"No syncable item found with key '%s'.\n\n" .
						"Possible reasons:\n" .
						"  - Key does not exist in JSON files\n" .
						"  - Item is already in sync with database\n" .
						"  - Item is marked as private\n\n" .
						"To see all syncable items, run:\n" .
						'  wp acf json sync --dry-run',
						$key_filter
					)
				);
			}
			$all_syncable = array( $key_filter => $all_syncable[ $key_filter ] );
		}

		if ( empty( $all_syncable ) ) {
			WP_CLI::success( self::MESSAGE_ALREADY_IN_SYNC );
			return;
		}

		if ( $dry_run ) {
			$this->display_dry_run( $all_syncable );
			return;
		}

		// Disable Local JSON controller to prevent .json files from being modified during import.
		$json_enabled = acf_get_setting( 'json' );
		acf_update_setting( 'json', false );

		// Build file index per post type before the loop (matches admin UI pattern).
		$files_by_type = array();
		foreach ( $all_syncable as $item ) {
			$pt = $item['post_type'];
			if ( ! isset( $files_by_type[ $pt ] ) ) {
				$files_by_type[ $pt ] = acf_get_local_json_files( $pt );
			}
		}

		$synced_count = 0;

		foreach ( $all_syncable as $key => $item ) {
			$post      = $item['post'];
			$post_type = $item['post_type'];
			$files     = $files_by_type[ $post_type ];

			if ( ! isset( $files[ $key ] ) ) {
				WP_CLI::warning(
					sprintf(
						"JSON file not found for key '%s'. Skipping.\n" .
						'The JSON file may have been deleted or moved.',
						$key
					)
				);
				continue;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$local_post = json_decode( file_get_contents( $files[ $key ] ), true );

			if ( ! is_array( $local_post ) ) {
				WP_CLI::warning( sprintf( "Invalid JSON in file for key '%s'. Skipping.", $key ) );
				continue;
			}

			$local_post['ID'] = $post['ID'];
			$result           = acf_import_internal_post_type( $local_post, $post_type );

			if ( empty( $result ) || ! isset( $result['ID'] ) ) {
				WP_CLI::warning( sprintf( "Failed to sync item with key '%s'.", $key ) );
				continue;
			}

			$action     = $post['ID'] ? 'Updated' : 'Created';
			$type_label = $this->get_type_label( $post_type );
			WP_CLI::log( sprintf( '%s %s: %s (%s)', $action, $type_label, $post['title'], $key ) );
			++$synced_count;
		}

		// Restore Local JSON setting.
		acf_update_setting( 'json', $json_enabled );

		if ( 0 === $synced_count ) {
			WP_CLI::warning( 'No items were synced.' );
			return;
		}

		WP_CLI::success( sprintf( '%d item(s) synced.', $synced_count ) );
	}

	/**
	 * Imports field groups, post types, taxonomies, and options pages from a JSON file.
	 *
	 * Reads an ACF export JSON file and imports the items into the database,
	 * replicating the functionality of the import UI in the WordPress admin.
	 * If an item with the same key already exists, it will be updated.
	 * Options pages require ACF PRO.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the JSON file to import.
	 *
	 * ## EXAMPLES
	 *
     *     # Import field groups, post types, taxonomies, and options pages from a file
     *     $ wp acf json import ./acf-export-2025-01-01.json
     *     Imported field-group: My Field Group (group_abc123)
     *     Imported post-type: Book (post_type_def456)
     *     Success: Imported 2 item(s).
     *
     *     # Import a single field group JSON file
     *     $ wp acf json import ./group_abc123.json
     *
     *     # Re-import to update existing items
     *     $ wp acf json import ./acf-export.json
     *     Updated field-group: My Field Group (group_abc123)
     *     Success: Imported 1 item(s).
	 *
	 * @since 6.8
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function import( $args, $assoc_args ) {
		$this->log_command( 'import' );

		if ( empty( $args[0] ) ) {
			WP_CLI::error(
				"Missing required file argument.\n\n" .
				"Usage: wp acf json import <file>\n\n" .
				"Example:\n" .
				"  wp acf json import ./acf-export.json\n\n" .
				"See: wp help acf json import"
			);
		}

		$file_path = $args[0];

		if ( ! file_exists( $file_path ) ) {
			WP_CLI::error( sprintf( 'File not found: %s', $file_path ) );
		}

		if ( 'json' !== pathinfo( $file_path, PATHINFO_EXTENSION ) ) {
			WP_CLI::error( 'File must have .json extension.' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = file_get_contents( $file_path );
		$json = json_decode( $json, true );

		if ( ! $json || ! is_array( $json ) ) {
			WP_CLI::error( 'Import file is empty or contains invalid JSON.' );
		}

		// Normalize single item to array (matches admin UI behavior).
		if ( isset( $json['key'] ) ) {
			$json = array( $json );
		}

		$ids = array();

		foreach ( $json as $to_import ) {
			if ( ! is_array( $to_import ) ) {
				WP_CLI::warning( 'Skipping invalid item (expected array, got ' . gettype( $to_import ) . ').' );
				continue;
			}

			if ( empty( $to_import['key'] ) ) {
				WP_CLI::warning( 'Skipping item with no key.' );
				continue;
			}

			$post_type = acf_determine_internal_post_type( $to_import['key'] );

			if ( ! $post_type ) {
				WP_CLI::warning( sprintf( "Could not determine post type for key '%s'. Skipping.", $to_import['key'] ) );
				continue;
			}

			$post = acf_get_internal_post_type_post( $to_import['key'], $post_type );

			if ( $post ) {
				$to_import['ID'] = $post->ID;
			}

			$result = acf_import_internal_post_type( $to_import, $post_type );

			if ( empty( $result ) || ! isset( $result['ID'] ) ) {
				WP_CLI::warning( sprintf( "Failed to import item with key '%s'.", $to_import['key'] ) );
				continue;
			}

			$action     = ! empty( $to_import['ID'] ) ? 'Updated' : 'Imported';
			$title      = ! empty( $result['title'] ) ? $result['title'] : $to_import['key'];
			$type_label = $this->get_type_label( $post_type );
			WP_CLI::log( sprintf( '%s %s: %s (%s)', $action, $type_label, $title, $to_import['key'] ) );

			$ids[] = $result['ID'];
		}

		if ( empty( $ids ) ) {
			WP_CLI::warning( 'No items were imported.' );
			return;
		}

		WP_CLI::success( sprintf( 'Imported %d item(s).', count( $ids ) ) );
	}

	/**
	 * Exports field groups, post types, taxonomies, and options pages to a JSON file.
	 *
	 * Exports ACF items to a JSON file, replicating the functionality of
	 * the export tool in the WordPress admin.
	 *
	 * ## OPTIONS
	 *
	 * [--field-groups=<keys>]
	 * : Export specific field groups by key or label, comma separated.
	 *
	 * [--post-types=<keys>]
	 * : Export specific post types by key or label, comma separated.
	 *
	 * [--taxonomies=<keys>]
	 * : Export specific taxonomies by key or label, comma separated.
	 *
	 * [--options-pages=<keys>]
	 * : Export specific options pages by key or label, comma separated. Requires ACF PRO.
	 *
	 * [--dir=<directory>]
	 * : Directory path to write the JSON file to.
	 *
	 * [--stdout]
	 * : Print the JSON to stdout instead of writing to a file.
	 *
	 * ## EXAMPLES
	 *
	 *     # Export all items to a directory
	 *     $ wp acf json export --dir=./exports/
	 *
	 *     # Export specific field groups by key
	 *     $ wp acf json export --field-groups=group_abc123,group_def456 --dir=./
	 *
     *     # Export a field group by label
     *     $ wp acf json export --field-groups="My Field Group" --dir=./
     *
     *     # Export mixed items (field groups and post types)
     *     $ wp acf json export --field-groups=group_abc --post-types=post_type_def --dir=./
     *
     *     # Export to stdout for piping
	 *     $ wp acf json export --stdout
	 *     $ wp acf json export --field-groups=group_abc123 --stdout | jq .
	 *
	 * @since 6.8
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function export( $args, $assoc_args ) {
		$this->log_command( 'export' );

		$field_groups_arg  = get_flag_value( $assoc_args, 'field-groups' );
		$post_types_arg    = get_flag_value( $assoc_args, 'post-types' );
		$taxonomies_arg    = get_flag_value( $assoc_args, 'taxonomies' );
		$options_pages_arg = get_flag_value( $assoc_args, 'options-pages' );
		$output_dir        = get_flag_value( $assoc_args, 'dir' );
		$stdout            = get_flag_value( $assoc_args, 'stdout', false );

		if ( ! $output_dir && ! $stdout ) {
			WP_CLI::error( 'You must specify --dir=<directory> or --stdout.' );
		}

		if ( $output_dir && $stdout ) {
			WP_CLI::error( 'Cannot specify both --dir and --stdout.' );
		}

		if ( $output_dir && ! is_dir( $output_dir ) ) {
			WP_CLI::error( sprintf( 'Directory not found: %s', $output_dir ) );
		}

		if ( $output_dir && ! wp_is_writable( $output_dir ) ) {
			WP_CLI::error( sprintf( 'Directory is not writable: %s', $output_dir ) );
		}

		$keys = $this->resolve_export_keys( $field_groups_arg, $post_types_arg, $taxonomies_arg, $options_pages_arg );

		if ( empty( $keys ) ) {
			WP_CLI::error( 'No items found to export.' );
		}

		$json = array();

		foreach ( $keys as $key ) {
			$post_type = acf_determine_internal_post_type( $key );
			$post      = acf_get_internal_post_type( $key, $post_type );

			if ( empty( $post ) ) {
				WP_CLI::warning( sprintf( "Item not found for key '%s'. Skipping.", $key ) );
				continue;
			}

			if ( 'acf-field-group' === $post_type ) {
				$post['fields'] = acf_get_fields( $post );
			}

			$post   = acf_prepare_internal_post_type_for_export( $post, $post_type );
			$json[] = $post;
		}

		if ( empty( $json ) ) {
			WP_CLI::error( 'No items could be exported.' );
		}

		$encoded = acf_json_encode( $json );

		if ( $stdout ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $encoded . "\n";
			return;
		}

		$file_name = 'acf-export-' . date( 'Y-m-d' ) . '.json';
		$file_path = trailingslashit( $output_dir ) . $file_name;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = file_put_contents( $file_path, $encoded . "\r\n" );

		if ( false === $result ) {
			WP_CLI::error( sprintf( 'Failed to write to %s', $file_path ) );
		}

		WP_CLI::success( sprintf( 'Exported %d item(s) to %s', count( $json ), $file_path ) );
	}

	/**
	 * Resolves export arguments into an array of ACF keys.
	 *
	 * When no arguments are provided, collects all items across all types.
	 * Accepts keys directly (group_xxx) or labels which are matched against
	 * existing items.
	 *
	 * @since 6.8
	 *
	 * @param string|null $field_groups_arg  Comma-separated field group keys/labels.
	 * @param string|null $post_types_arg    Comma-separated post type keys/labels.
	 * @param string|null $taxonomies_arg    Comma-separated taxonomy keys/labels.
	 * @param string|null $options_pages_arg Comma-separated options page keys/labels.
	 * @return array List of ACF keys to export.
	 */
	private function resolve_export_keys( $field_groups_arg, $post_types_arg, $taxonomies_arg, $options_pages_arg ) {
		$no_filters = ! $field_groups_arg && ! $post_types_arg && ! $taxonomies_arg && ! $options_pages_arg;
		$keys       = array();

		if ( $no_filters ) {
			foreach ( $this->get_post_types() as $post_type ) {
				$keys = array_merge( $keys, $this->resolve_keys_for_type( $post_type, null ) );
			}

			return $keys;
		}

		if ( $field_groups_arg ) {
			$keys = array_merge( $keys, $this->resolve_keys_for_type( 'acf-field-group', $field_groups_arg ) );
		}

		if ( $post_types_arg ) {
			$keys = array_merge( $keys, $this->resolve_keys_for_type( 'acf-post-type', $post_types_arg ) );
		}

		if ( $taxonomies_arg ) {
			$keys = array_merge( $keys, $this->resolve_keys_for_type( 'acf-taxonomy', $taxonomies_arg ) );
		}

		if ( $options_pages_arg ) {
			if ( ! acf_is_pro() ) {
				WP_CLI::error(
					"Options pages require ACF PRO.\n\n" .
					"To export options pages, you need:\n" .
					"  - ACF PRO license\n" .
					"  - Active license key\n\n" .
					'See: https://www.advancedcustomfields.com/pro/'
				);
			}

			$keys = array_merge( $keys, $this->resolve_keys_for_type( 'acf-ui-options-page', $options_pages_arg ) );
		}

		return $keys;
	}

	/**
	 * Resolves a comma-separated list of keys or labels into ACF keys for a given post type.
	 *
	 * @since 6.8
	 *
	 * @param string      $post_type The item type (field group, post type, taxonomy, or options page).
	 * @param string|null $arg       Comma-separated keys/labels, or null for all.
	 * @return array List of ACF keys.
	 */
	private function resolve_keys_for_type( $post_type, $arg ) {
		$posts = acf_get_internal_post_type_posts( $post_type );
		$posts = array_filter( $posts, 'acf_internal_post_object_contains_valid_key' );

		if ( ! $arg ) {
			return wp_list_pluck( $posts, 'key' );
		}

		$identifiers = array_filter( array_map( 'trim', explode( ',', $arg ) ) );
		$keys        = array();

		foreach ( $identifiers as $identifier ) {
			$found = false;

			foreach ( $posts as $post ) {
				if ( $post['key'] === $identifier || strcasecmp( $post['title'], $identifier ) === 0 ) {
					$keys[] = $post['key'];
					$found  = true;
					break;
				}
			}

			if ( ! $found ) {
				WP_CLI::warning( sprintf( 'No item found matching "%s". Skipping.', $identifier ) );
			}
		}

		return array_unique( $keys );
	}

	/**
	 * Determines which item types to process.
	 *
	 * @since 6.8
	 *
	 * @param string|null $type_filter The CLI type flag value.
	 * @return array List of item type slugs.
	 */
	private function get_post_types( $type_filter = null ) {
		if ( $type_filter ) {
			if ( ! isset( self::TYPE_MAP[ $type_filter ] ) ) {
				WP_CLI::error(
					sprintf(
						"Unknown type '%s'.\n\n" .
						"Valid types:\n" .
						"  - field-group\n" .
						"  - post-type\n" .
						"  - taxonomy\n" .
						"  - options-page (ACF PRO only)\n\n" .
						'See: wp help acf json',
						$type_filter
					)
				);
			}

			$post_type = self::TYPE_MAP[ $type_filter ];

			if ( 'acf-ui-options-page' === $post_type && ! acf_is_pro() ) {
				WP_CLI::error(
					"Options pages require ACF PRO.\n\n" .
					"To sync options pages, you need:\n" .
					"  - ACF PRO license\n" .
					"  - Active license key\n\n" .
					'See: https://www.advancedcustomfields.com/pro/'
				);
			}

			return array( $post_type );
		}

		$post_types = acf_get_internal_post_types();

		// Remove options pages from non-PRO installs.
		if ( ! acf_is_pro() ) {
			$post_types = array_filter(
				$post_types,
				function ( $pt ) {
					return 'acf-ui-options-page' !== $pt;
				}
			);
		}

		return array_values( $post_types );
	}

	/**
	 * Returns the friendly CLI type label for an internal post type slug.
	 *
	 * @since 6.8
	 *
	 * @param string $post_type The internal post type slug (e.g. 'acf-field-group').
	 * @return string The friendly label (e.g. 'field-group'), or the original slug if not found.
	 */
	private function get_type_label( $post_type ) {
		$label = array_search( $post_type, self::TYPE_MAP, true );

		return $label ? $label : $post_type;
	}

	/**
	 * Finds syncable items for a given item type using the same logic as the admin UI.
	 *
	 * @since 6.8
	 *
	 * @param string $post_type The item type.
	 * @return array Associative array of key => post data for syncable items.
	 */
	private function get_syncable_items( $post_type ) {
		$syncable = array();
		$files    = acf_get_local_json_files( $post_type );

		if ( empty( $files ) ) {
			return $syncable;
		}

		$all_posts = acf_get_internal_post_type_posts( $post_type );

		foreach ( $all_posts as $post ) {
			$local    = acf_maybe_get( $post, 'local' );
			$modified = acf_maybe_get( $post, 'modified' );
			$private  = acf_maybe_get( $post, 'private' );

			if ( $private ) {
				continue;
			}

			if ( 'json' !== $local ) {
				continue;
			}

			// New item (not yet in database).
			if ( ! $post['ID'] ) {
				$syncable[ $post['key'] ] = $post;
				continue;
			}

			// Updated item (JSON is newer than database).
			if ( $modified && $modified > get_post_modified_time( 'U', true, $post['ID'] ) ) {
				$syncable[ $post['key'] ] = $post;
			}
		}

		return $syncable;
	}

	/**
	 * Displays detailed status showing individual items that need syncing.
	 *
	 * @since 6.8
	 *
	 * @param array  $post_types List of post types to check.
	 * @param string $format     Output format.
	 */
	private function display_detailed_status( $post_types, $format ) {
		$rows          = array();
		$total_pending = 0;

		foreach ( $post_types as $post_type ) {
			$syncable = $this->get_syncable_items( $post_type );

			foreach ( $syncable as $key => $post ) {
				$action = $post['ID'] ? 'Update' : 'Create';
				++$total_pending;

				$rows[] = array(
					'Key'    => $key,
					'Title'  => $post['title'],
					'Type'   => $this->get_type_label( $post_type ),
					'Action' => $action,
				);
			}
		}

		if ( empty( $rows ) ) {
			WP_CLI::success( self::MESSAGE_ALREADY_IN_SYNC );
			return;
		}

		format_items( $format, $rows, array( 'Key', 'Title', 'Type', 'Action' ) );

		if ( 'table' === $format ) {
			WP_CLI::log( sprintf( '%d item(s) pending sync. Run `wp acf json sync` to apply changes.', $total_pending ) );
		}
	}

	/**
	 * Displays a table of pending sync items for dry-run mode.
	 *
	 * @since 6.8
	 *
	 * @param array $all_syncable The syncable items.
	 */
	private function display_dry_run( $all_syncable ) {
		$rows = array();

		foreach ( $all_syncable as $key => $item ) {
			$post   = $item['post'];
			$action = $post['ID'] ? 'Update' : 'Create';

			$rows[] = array(
				'Key'    => $key,
				'Title'  => $post['title'],
				'Type'   => $this->get_type_label( $item['post_type'] ),
				'Action' => $action,
			);
		}

		WP_CLI::log( sprintf( '%d item(s) pending sync:', count( $rows ) ) );
		format_items( 'table', $rows, array( 'Key', 'Title', 'Type', 'Action' ) );
	}
}
