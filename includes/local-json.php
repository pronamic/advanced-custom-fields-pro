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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Local_JSON' ) ) :

	class ACF_Local_JSON {

		/**
		 * The found JSON field group files.
		 *
		 * @since 5.9.0
		 * @var array
		 */
		private $files = array();

		/**
		 * Constructor.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function __construct() {

			// Update settings.
			acf_update_setting( 'save_json', get_stylesheet_directory() . '/acf-json' );
			acf_append_setting( 'load_json', get_stylesheet_directory() . '/acf-json' );

			// Add listeners.
			add_action( 'acf/update_field_group', array( $this, 'update_field_group' ) );
			add_action( 'acf/untrash_field_group', array( $this, 'update_field_group' ) );
			add_action( 'acf/trash_field_group', array( $this, 'delete_field_group' ) );
			add_action( 'acf/delete_field_group', array( $this, 'delete_field_group' ) );
			add_action( 'acf/update_post_type', array( $this, 'update_internal_post_type' ) );
			add_action( 'acf/untrash_post_type', array( $this, 'update_internal_post_type' ) );
			add_action( 'acf/trash_post_type', array( $this, 'delete_internal_post_type' ) );
			add_action( 'acf/delete_post_type', array( $this, 'delete_internal_post_type' ) );
			add_action( 'acf/update_taxonomy', array( $this, 'update_internal_post_type' ) );
			add_action( 'acf/untrash_taxonomy', array( $this, 'update_internal_post_type' ) );
			add_action( 'acf/trash_taxonomy', array( $this, 'delete_internal_post_type' ) );
			add_action( 'acf/delete_taxonomy', array( $this, 'delete_internal_post_type' ) );

			// Include fields.
			add_action( 'acf/include_fields', array( $this, 'include_fields' ) );
			add_action( 'acf/include_post_types', array( $this, 'include_post_types' ) );
			add_action( 'acf/include_taxonomies', array( $this, 'include_taxonomies' ) );
		}

		/**
		 * Returns true if this component is enabled.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  boolean
		 */
		public function is_enabled() {
			return (bool) acf_get_setting( 'json' );
		}

		/**
		 * Gets the path(s) to load JSON from.
		 *
		 * @since 6.2
		 *
		 * @return array
		 */
		public function get_load_paths() {
			$paths = (array) acf_get_setting( 'load_json' );

			/**
			 * Filters the path(s) used to load JSON from.
			 *
			 * @since 6.2
			 *
			 * @param array $paths An array of potential paths to load JSON from.
			 * @return array
			 */
			return (array) apply_filters( 'acf/json/load_paths', $paths );
		}

		/**
		 * Gets the path(s) to save JSON to.
		 *
		 * @since 6.2
		 *
		 * @param string $key  The key to get paths for (optional).
		 * @param array  $post The main ACF post array (optional).
		 * @return array
		 */
		public function get_save_paths( $key = '', $post = array() ) {
			$name      = ! empty( $post['title'] ) ? (string) $post['title'] : '';
			$post_type = acf_determine_internal_post_type( $key );
			$paths     = array();

			// Paths are sorted by priority, with key overriding name, etc.
			$paths[] = acf_get_setting( "save_json/key={$key}" );
			$paths[] = acf_get_setting( "save_json/name={$name}" );
			$paths[] = acf_get_setting( "save_json/type={$post_type}" );
			$paths[] = acf_get_setting( 'save_json' );
			$paths   = array_values( array_filter( $paths ) );

			/**
			 * Filters the paths used to save JSON.
			 *
			 * @since 6.2
			 *
			 * @param array $paths An array of the potential paths to save JSON to.
			 * @param array $post  The ACF field group, post type, or taxonomy array.
			 * @return array
			 */
			return (array) apply_filters( 'acf/json/save_paths', $paths, $post );
		}

		/**
		 * Writes field group data to JSON file.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param   array $field_group The field group.
		 * @return  void
		 */
		public function update_field_group( $field_group ) {

			// Bail early if disabled.
			if ( ! $this->is_enabled() ) {
				return false;
			}

			// Append fields.
			$field_group['fields'] = acf_get_fields( $field_group );

			// Save to file.
			$this->save_file( $field_group['key'], $field_group );
		}

		/**
		 * Writes ACF posts to the JSON file.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main ACF post array.
		 * @return boolean
		 */
		public function update_internal_post_type( $post ) {
			if ( ! $this->is_enabled() ) {
				return false;
			}

			/**
			 * Filters the ACF post before saving it to the file.
			 *
			 * @since 6.1
			 *
			 * @param array $post The main ACF post array
			 */
			$post = apply_filters( 'acf/pre_save_json_file', $post );

			return $this->save_file( $post['key'], $post );
		}

		/**
		 * Deletes a field group JSON file.
		 *
		 * @date 14/4/20
		 * @since 5.9.0
		 *
		 * @param  array $field_group The field group.
		 * @return boolean
		 */
		public function delete_field_group( $field_group ) {
			return $this->delete_internal_post_type( $field_group );
		}

		/**
		 * Deletes an ACF JSON file.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main ACF post array.
		 * @return boolean
		 */
		public function delete_internal_post_type( $post ) {
			if ( ! $this->is_enabled() ) {
				return false;
			}

			// WP appends '__trashed' to the end of 'key' (post_name).
			$key = str_replace( '__trashed', '', $post['key'] );

			return $this->delete_file( $key, $post );
		}

		/**
		 * Includes all local JSON fields.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @param   void
		 * @return  void
		 */
		public function include_fields() {

			// Bail early if disabled.
			if ( ! $this->is_enabled() ) {
				return false;
			}

			// Get load paths.
			$files = $this->scan_files( 'acf-field-group' );
			foreach ( $files as $key => $file ) {
				$json               = json_decode( file_get_contents( $file ), true );
				$json['local']      = 'json';
				$json['local_file'] = $file;
				acf_add_local_field_group( $json );
			}
		}

		/**
		 * Includes all local JSON post types.
		 *
		 * @since 6.1
		 */
		public function include_post_types() {
			// Bail early if disabled.
			if ( ! $this->is_enabled() ) {
				return false;
			}

			// Get load paths.
			$files = $this->scan_files( 'acf-post-type' );
			foreach ( $files as $key => $file ) {
				$json               = json_decode( file_get_contents( $file ), true );
				$json['local']      = 'json';
				$json['local_file'] = $file;
				acf_add_local_internal_post_type( $json, 'acf-post-type' );
			}
		}

		/**
		 * Includes all local JSON taxonomies.
		 *
		 * @since 6.1
		 */
		public function include_taxonomies() {
			// Bail early if disabled.
			if ( ! $this->is_enabled() ) {
				return false;
			}

			// Get load paths.
			$files = $this->scan_files( 'acf-taxonomy' );
			foreach ( $files as $key => $file ) {
				$json               = json_decode( file_get_contents( $file ), true );
				$json['local']      = 'json';
				$json['local_file'] = $file;
				acf_add_local_internal_post_type( $json, 'acf-taxonomy' );
			}
		}

		/**
		 * Scans for JSON field groups.
		 *
		 * @date    14/4/20
		 * @since   5.9.0
		 *
		 * @return array
		 */
		function scan_field_groups() {
			return $this->scan_files( 'acf-field-group' );
		}

		/**
		 * Scans for JSON files.
		 *
		 * @since 6.1
		 *
		 * @param string $post_type The ACF post type to scan for.
		 * @return array
		 */
		function scan_files( $post_type = 'acf-field-group' ) {
			$json_files = array();

			// Loop over "local_json" paths and parse JSON files.
			foreach ( $this->get_load_paths() as $path ) {
				if ( is_dir( $path ) ) {
					$files = scandir( $path );
					if ( $files ) {
						foreach ( $files as $filename ) {

							// Ignore hidden files.
							if ( $filename[0] === '.' ) {
								continue;
							}

							// Ignore sub directories.
							$file = untrailingslashit( $path ) . '/' . $filename;
							if ( is_dir( $file ) ) {
								continue;
							}

							// Ignore non JSON files.
							$ext = pathinfo( $filename, PATHINFO_EXTENSION );
							if ( $ext !== 'json' ) {
								continue;
							}

							// Read JSON data.
							$json = json_decode( file_get_contents( $file ), true );
							if ( ! is_array( $json ) || ! isset( $json['key'] ) ) {
								continue;
							}

							// Append data.
							$json_files[ $json['key'] ] = $file;
						}
					}
				}
			}

			// Store data and return.
			$this->files = $json_files;
			return $this->get_files( $post_type );
		}

		/**
		 * Returns an array of found JSON files.
		 *
		 * @date 14/4/20
		 * @since 5.9.0
		 *
		 * @param string $post_type The ACF post type to get files for.
		 * @return array
		 */
		public function get_files( $post_type = 'acf-field-group' ) {
			$files = array();

			foreach ( $this->files as $key => $path ) {
				$internal_post_type = acf_determine_internal_post_type( $key );

				if ( $internal_post_type === $post_type ) {
					$files[ $key ] = $path;
				} elseif ( 'acf-field-group' === $post_type ) {
					// If we can't figure out the ACF post type, make an educated guess that it's a field group.
					$json = json_decode( file_get_contents( $path ), true );
					if ( ! is_array( $json ) ) {
						continue;
					}

					if ( isset( $json['fields'] ) ) {
						$files[ $key ] = $path;
					}
				}
			}

			return $files;
		}

		/**
		 * Gets the filename for an ACF JSON file.
		 *
		 * @since 6.3
		 *
		 * @param string $key  The ACF post key.
		 * @param array  $post The main ACF post array.
		 * @return string|boolean
		 */
		public function get_filename( $key, $post ) {
			$load_path = '';

			if ( is_array( $this->files ) && isset( $this->files[ $key ] ) ) {
				$load_path = $this->files[ $key ];
			}

			/**
			 * Filters the filename used when saving JSON.
			 *
			 * @since 6.2
			 *
			 * @param string $filename  The default filename.
			 * @param array  $post      The main post array for the item being saved.
			 * @param string $load_path The path that the item was loaded from.
			 */
			$filename = apply_filters( 'acf/json/save_file_name', $key . '.json', $post, $load_path );

			if ( ! is_string( $filename ) ) {
				return false;
			}

			$filename = sanitize_file_name( $filename );

			// sanitize_file_name() can potentially remove all characters.
			if ( empty( $filename ) ) {
				return false;
			}

			return $filename;
		}

		/**
		 * Saves an ACF JSON file.
		 *
		 * @date 17/4/20
		 * @since 5.9.0
		 *
		 * @param string $key  The ACF post key.
		 * @param array  $post The main ACF post array.
		 * @return boolean
		 */
		public function save_file( $key, $post ) {
			$paths          = $this->get_save_paths( $key, $post );
			$filename       = $this->get_filename( $key, $post );
			$file           = false;
			$first_writable = false;

			if ( ! $filename ) {
				return false;
			}

			foreach ( $paths as $path ) {
				if ( ! is_writable( $path ) ) { //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- non-compatible function for this purpose.
					continue;
				}

				if ( false === $first_writable ) {
					$first_writable = $path;
				}

				$file_to_check = trailingslashit( $path ) . $filename;

				if ( is_file( $file_to_check ) ) {
					$file = $file_to_check;
				}
			}

			if ( ! $file ) {
				if ( $first_writable ) {
					$file = trailingslashit( $first_writable ) . $filename;
				} else {
					return false;
				}
			}

			// Make sure this is a valid ACF post type.
			$post_type = acf_determine_internal_post_type( $key );
			if ( ! $post_type ) {
				return false;
			}

			// Append modified time.
			if ( $post['ID'] ) {
				$post['modified'] = get_post_modified_time( 'U', true, $post['ID'] );
			} else {
				$post['modified'] = strtotime( 'now' );
			}

			// Prepare for export and save the file.
			$post   = acf_prepare_internal_post_type_for_export( $post, $post_type );
			$result = file_put_contents( $file, acf_json_encode( $post ) . apply_filters( 'acf/json/eof_newline', PHP_EOL ) ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- potentially could run outside of admin.

			// Return true if bytes were written.
			return is_int( $result );
		}

		/**
		 * Deletes an ACF JSON file.
		 *
		 * @date 17/4/20
		 * @since 5.9.0
		 *
		 * @param string $key  The ACF post key.
		 * @param array  $post The main ACF post array.
		 * @return boolean
		 */
		public function delete_file( $key, $post = array() ) {
			$paths    = $this->get_save_paths( $key, $post );
			$filename = $this->get_filename( $key, $post );

			if ( ! $filename ) {
				return false;
			}

			foreach ( $paths as $path_to_check ) {
				$file = untrailingslashit( $path_to_check ) . '/' . $filename;

				if ( is_writable( $file ) ) { //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- non-compatible function for this purpose.
					wp_delete_file( $file );
				}
			}

			return true;
		}

		/**
		 * Includes all local JSON files.
		 *
		 * @date       10/03/2014
		 * @since      5.0.0
		 * @deprecated 5.9.0
		 */
		public function include_json_folders() {
			_deprecated_function( __METHOD__, '5.9.0', 'ACF_Local_JSON::include_fields()' );
			$this->include_fields();
		}

		/**
		 * Includes local JSON files within a specific folder.
		 *
		 * @date    01/05/2017
		 * @since   5.5.13
		 * @deprecated 5.9.0
		 *
		 * @param   string $path The path to a specific JSON folder.
		 * @return  void
		 */
		public function include_json_folder( $path = '' ) {
			_deprecated_function( __METHOD__, '5.9.0' );
			// Do nothing.
		}
	}

	// Initialize.
	acf_new_instance( 'ACF_Local_JSON' );
endif; // class_exists check

/**
 * Returns an array of found JSON field group files.
 *
 * @date    14/4/20
 * @since   5.9.0
 *
 * @param string $post_type The ACF post type to get files for.
 * @return array
 */
function acf_get_local_json_files( $post_type = 'acf-field-group' ) {
	return acf_get_instance( 'ACF_Local_JSON' )->get_files( $post_type );
}

/**
 * Saves a field group JSON file.
 *
 * @date    5/12/2014
 * @since   5.1.5
 *
 * @param   array $field_group The field group.
 * @return  boolean
 */
function acf_write_json_field_group( $field_group ) {
	return acf_get_instance( 'ACF_Local_JSON' )->save_file( $field_group['key'], $field_group );
}

/**
 * Deletes a field group JSON file.
 *
 * @date    5/12/2014
 * @since   5.1.5
 *
 * @param   string $key The field group key.
 * @return  boolean True on success.
 */
function acf_delete_json_field_group( $key ) {
	return acf_get_instance( 'ACF_Local_JSON' )->delete_file( $key );
}
