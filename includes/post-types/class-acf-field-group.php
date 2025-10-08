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
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Field_Group' ) ) {
	class ACF_Field_Group extends ACF_Internal_Post_Type {

		/**
		 * The ACF internal post type name.
		 *
		 * @var string
		 */
		public $post_type = 'acf-field-group';

		/**
		 * The prefix for the key used in the main post array.
		 *
		 * @var string
		 */
		public $post_key_prefix = 'group_';

		/**
		 * The cache key for a singular post.
		 *
		 * @var string
		 */
		public $cache_key = 'acf_get_field_group_post:key:';

		/**
		 * The cache key for a collection of posts.
		 *
		 * @var string
		 */
		public $cache_key_plural = 'acf_get_field_group_posts';

		/**
		 * The hook name for a singular post.
		 *
		 * @var string
		 */
		public $hook_name = 'field_group';

		/**
		 * The hook name for a collection of posts.
		 *
		 * @var string
		 */
		public $hook_name_plural = 'field_groups';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = 'field-groups';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			// Include admin classes in admin.
			if ( is_admin() ) {
				acf_include( 'includes/admin/admin-internal-post-type-list.php' );
				acf_include( 'includes/admin/admin-internal-post-type.php' );
				acf_include( 'includes/admin/post-types/admin-field-group.php' );
				acf_include( 'includes/admin/post-types/admin-field-groups.php' );
			}

			parent::__construct();
			add_filter( 'acf/pre_update_field_group', array( $this, 'pre_update_field_group' ), 1 );
		}

		/**
		 * Gets the default settings array for an ACF field group.
		 *
		 * @return array
		 */
		public function get_settings_array() {
			return array(
				'ID'                    => 0,
				'key'                   => '',
				'title'                 => '',
				'fields'                => array(),
				'location'              => array(),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => array(),
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => false,
				'display_title'         => '',
			);
		}

		/**
		 * Get an ACF CPT object as an array.
		 *
		 * @since 6.1
		 *
		 * @param integer|WP_Post $id The post ID being queried.
		 * @return array|boolean The main ACF array for the post, or false on failure.
		 */
		public function get_post( $id = 0 ) {
			// Allow WP_Post to be passed.
			if ( is_object( $id ) ) {
				$id = $id->ID;
			}

			// Check store.
			$store = acf_get_store( $this->store );
			if ( $store->has( $id ) ) {
				return $store->get( $id );
			}

			if ( acf_is_local_field_group( $id ) ) {
				$post = acf_get_local_field_group( $id );
			} else {
				$post = $this->get_raw_post( $id );
			}

			// Bail early if no post.
			if ( ! $post ) {
				return false;
			}

			$post = $this->validate_post( $post );

			/**
			 * Filters the post array after it has been loaded.
			 *
			 * @date  12/02/2014
			 * @since 5.0.0
			 *
			 * @param array $post The post array.
			 */
			$post = apply_filters( "acf/load_{$this->hook_name}", $post );

			// Store field group using aliasses to also find via key, ID and name.
			$store->set( $post['key'], $post );
			$store->alias( $post['key'], $post['ID'] );

			return $post;
		}

		/**
		 * Filter the posts returned by $this->get_posts().
		 *
		 * @since 6.1
		 *
		 * @param array $posts An array of posts to filter.
		 * @param array $args  An array of args to filter by.
		 * @return array
		 */
		public function filter_posts( $posts, $args = array() ) {
			// Loop over field groups and check visibility.
			$filtered = array();
			if ( $posts ) {
				foreach ( $posts as $post ) {
					if ( acf_get_field_group_visibility( $post, $args ) ) {
						$filtered[] = $post;
					}
				}
			}

			return $filtered;
		}

		/**
		 * Filters the field group data before it is updated in the database.
		 *
		 * @since 6.1
		 *
		 * @param array $field_group The field group being updated.
		 * @return array
		 */
		public function pre_update_field_group( $field_group ) {
			// Remove empty values and convert to associated array.
			if ( $field_group['location'] ) {
				$field_group['location'] = array_filter( $field_group['location'] );
				$field_group['location'] = array_values( $field_group['location'] );
				$field_group['location'] = array_map( 'array_filter', $field_group['location'] );
				$field_group['location'] = array_map( 'array_values', $field_group['location'] );
			}

			return $field_group;
		}

		/**
		 * Deletes an ACF field group and related fields.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The ID of the field group to delete.
		 * @return boolean
		 */
		public function delete_post( $id = 0 ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			// Get the post.
			$post = $this->get_post( $id );

			// Bail early if post was not found.
			if ( ! $post || ! $post['ID'] ) {
				return false;
			}

			// Delete the fields.
			$fields = acf_get_fields( $post );
			if ( $fields ) {
				foreach ( $fields as $field ) {
					acf_delete_field( $field['ID'] );
				}
			}

			// Delete post and flush cache.
			wp_delete_post( $post['ID'], true );
			$this->flush_post_cache( $post );

			/**
			 * Fires immediately after an ACF post has been deleted.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param array $post The ACF post array.
			 */
			do_action( "acf/delete_{$this->hook_name}", $post );

			return true;
		}

		/**
		 * Trashes an ACF field group and related fields.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The ID of the field group to trash.
		 * @return boolean
		 */
		public function trash_post( $id = 0 ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			$post = $this->get_post( $id );
			if ( ! $post || ! $post['ID'] ) {
				return false;
			}

			// Trash fields.
			$fields = acf_get_fields( $post );
			if ( $fields ) {
				foreach ( $fields as $field ) {
					acf_trash_field( $field['ID'] );
				}
			}

			wp_trash_post( $post['ID'] );
			$this->flush_post_cache( $post );

			/**
			 * Fires immediately after a field_group has been trashed.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param array $post The ACF post array.
			 */
			do_action( "acf/trash_{$this->hook_name}", $post );

			return true;
		}

		/**
		 * Restores an ACF field group and related fields from the trash.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The ID of the ACF post to untrash.
		 * @return boolean
		 */
		public function untrash_post( $id = 0 ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			$post = $this->get_post( $id );
			if ( ! $post || ! $post['ID'] ) {
				return false;
			}

			$fields = acf_get_fields( $post );
			if ( $fields ) {
				foreach ( $fields as $field ) {
					acf_untrash_field( $field['ID'] );
				}
			}

			wp_untrash_post( $post['ID'] );
			$this->flush_post_cache( $post );

			/**
			 * Fires immediately after an ACF post has been untrashed.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param array $post The ACF post array.
			 */
			do_action( "acf/untrash_{$this->hook_name}", $post );

			return true;
		}

		/**
		 * Duplicates an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id          The ID of the post to duplicate.
		 * @param integer        $new_post_id Optional post ID to override.
		 * @return array The new ACF post array.
		 */
		public function duplicate_post( $id = 0, $new_post_id = 0 ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			$post = $this->get_post( $id );
			if ( ! $post || ! $post['ID'] ) {
				return false;
			}

			// Get fields before updating field group attributes.
			$fields = acf_get_fields( $post['ID'] );

			// Update attributes.
			$post['ID']  = $new_post_id;
			$post['key'] = uniqid( 'group_' );

			// Add (copy) to title when appropriate.
			if ( ! $new_post_id ) {
				$post['title'] .= ' (' . __( 'copy', 'acf' ) . ')';
			}

			// When duplicating a field group, insert a temporary post and set the field group's ID.
			// This allows fields to be updated before the field group (field group ID is needed for field parent setting).
			if ( ! $post['ID'] ) {
				$post['ID'] = wp_insert_post(
					array(
						'post_title' => $post['key'],
						'post_type'  => $this->post_type,
					)
				);
			}

			// Duplicate fields and update post.
			acf_duplicate_fields( $fields, $post['ID'] );
			$post = $this->update_post( $post );

			/**
			 * Fires immediately after an ACF post has been duplicated.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param   array $post The ACF post array.
			 */
			do_action( "acf/duplicate_{$this->hook_name}", $post );

			return $post;
		}

		/**
		 * Returns a modified ACF field group array ready for export.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post array.
		 * @return array
		 */
		public function prepare_post_for_export( $post = array() ) {
			// Remove args.
			acf_extract_vars( $post, array( 'ID', 'local', '_valid' ) );

			// Prepare fields.
			$post['fields'] = acf_prepare_fields_for_export( $post['fields'] );

			/**
			 * Filters the ACF post array before being returned to the export tool.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param array $post The ACF post array.
			 */
			return apply_filters( "acf/prepare_{$this->hook_name}_for_export", $post );
		}

		/**
		 * Prepares an ACF field group for import.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF field group array.
		 * @return array
		 */
		public function prepare_post_for_import( $post ) {
			// Update parent and menu_order properties for all fields.
			if ( ! empty( $post['fields'] ) ) {
				foreach ( $post['fields'] as $i => &$field ) {
					$field['parent']     = $post['key'];
					$field['menu_order'] = $i;
				}
			}

			/**
			 * Filters the ACF post array before being returned to the import tool.
			 *
			 * @date 21/11/19
			 * @since 5.8.8
			 *
			 * @param array $post The ACF post array.
			 */
			return apply_filters( "acf/prepare_{$this->hook_name}_for_import", $post );
		}

		/**
		 * Returns a string that can be used to create a field group with PHP.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main field group array.
		 * @return string
		 */
		public function export_post_as_php( $post = array() ) {
			$return = '';
			if ( empty( $post ) ) {
				return $return;
			}

			$code = var_export( $post, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Used for PHP export.
			if ( ! $code ) {
				return $return;
			}

			$code    = $this->format_code_for_export( $code );
			$return .= "acf_add_local_field_group( {$code} );\r\n";

			return esc_textarea( $return );
		}

		/**
		 * Imports an ACF post into the database.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post array.
		 * @return array
		 */
		public function import_post( $post ) {
			// Disable filters to ensure data is not modified by local, clone, etc.
			$filters = acf_disable_filters();

			// Validate the post (ensures all settings exist).
			$post = $this->get_valid_post( $post );

			// Prepare post for import (modifies settings).
			$post = $this->prepare_post_for_import( $post );

			// Prepare fields for import (modifies settings).
			$fields = acf_prepare_fields_for_import( $post['fields'] );

			// Stores a map of field "key" => "ID".
			$ids = array();

			// If the field group has an ID, review and delete stale fields in the database.
			if ( $post['ID'] ) {

				// Load database fields.
				$db_fields = acf_prepare_fields_for_import( acf_get_fields( $post ) );

				// Generate map of "index" => "key" data.
				$keys = wp_list_pluck( $fields, 'key' );

				// Loop over db fields and delete those who don't exist in $new_fields.
				foreach ( $db_fields as $field ) {
					// Add field data to $ids map.
					$ids[ $field['key'] ] = $field['ID'];

					// Delete field if not in $keys.
					if ( ! in_array( $field['key'], $keys, true ) ) {
						acf_delete_field( $field['ID'] );
					}
				}
			}

			// When importing a new field group, insert a temporary post and set the field group's ID.
			// This allows fields to be updated before the field group (field group ID is needed for field parent setting).
			if ( ! $post['ID'] ) {
				$post['ID'] = wp_insert_post(
					array(
						'post_title' => $post['key'],
						'post_type'  => $this->post_type,
					)
				);
			}
			// Add field group data to $ids map.
			$ids[ $post['key'] ] = $post['ID'];

			// Loop over and add fields.
			if ( $fields ) {
				foreach ( $fields as $field ) {

					// Replace any "key" references with "ID".
					if ( isset( $ids[ $field['key'] ] ) ) {
						$field['ID'] = $ids[ $field['key'] ];
					}
					if ( isset( $ids[ $field['parent'] ] ) ) {
						$field['parent'] = $ids[ $field['parent'] ];
					}

					// Save field.
					$field = acf_update_field( $field );

					// Add field data to $ids map for children.
					$ids[ $field['key'] ] = $field['ID'];
				}
			}

			// Save field group.
			$post = $this->update_post( $post );

			// Enable filters again.
			acf_enable_filters( $filters );

			/**
			 * Fires immediately after an ACF post has been imported.
			 *
			 * @date 12/02/2014
			 * @since 5.0.0
			 *
			 * @param   array $post The ACF post array.
			 */
			do_action( "acf/import_{$this->hook_name}", $post );

			return $post;
		}
	}

}

acf_new_instance( 'ACF_Field_Group' );
