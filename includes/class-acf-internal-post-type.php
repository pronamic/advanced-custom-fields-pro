<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ACF_Internal_Post_Type' ) ) {
	abstract class ACF_Internal_Post_Type {

		/**
		 * The ACF internal post type name.
		 *
		 * @var string
		 */
		public $post_type = '';

		/**
		 * The prefix for the key used in the main post array.
		 *
		 * @var string
		 */
		public $post_key_prefix = '';

		/**
		 * The cache key for a singular post.
		 *
		 * @var string
		 */
		public $cache_key = '';

		/**
		 * The cache key for a collection of posts.
		 *
		 * @var string
		 */
		public $cache_key_plural = '';

		/**
		 * The hook name for a singular post.
		 *
		 * @var string
		 */
		public $hook_name = '';

		/**
		 * The hook name for a collection of posts.
		 *
		 * @var string
		 */
		public $hook_name_plural = '';

		/**
		 * The name of the store used for the post type.
		 *
		 * @var string
		 */
		public $store = '';

		/**
		 * Constructs the class.
		 */
		public function __construct() {
			acf_register_store( $this->store )->prop( 'multisite', true );

			$internal_post_types_store = acf_get_store( 'internal-post-types' );

			if ( ! $internal_post_types_store ) {
				$internal_post_types_store = acf_register_store( 'internal-post-types' );
			}

			$internal_post_types_store->set( $this->post_type, get_class( $this ) );

			add_action( "acf/validate_{$this->hook_name}", array( $this, 'translate_post' ) );

			add_filter( 'wp_unique_post_slug', array( $this, 'apply_unique_post_slug' ), 999, 6 );
			add_action( 'wp_untrash_post_status', array( $this, 'untrash_post_status' ), 10, 3 );
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

			if ( acf_is_local_internal_post_type( $id, $this->post_type ) ) {
				$post = acf_get_local_internal_post_type( $id, $this->post_type );
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

			// Store field group using aliases to also find via key, ID and name.
			$store->set( $post['key'], $post );
			$store->alias( $post['key'], $post['ID'] );

			return $post;
		}

		/**
		 * Retrieves raw post data for the given identifier.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The field ID, key or name.
		 * @return array|false The field group array, or false on failure.
		 */
		public function get_raw_post( $id = 0 ) {
			// Get raw internal post from database.
			$post = $this->get_post_object( $id );
			if ( ! $post ) {
				return false;
			}

			// Bail early if incorrect post type.
			if ( $post->post_type !== $this->post_type ) {
				return false;
			}

			// Unserialize post_content.
			$raw_post = (array) acf_maybe_unserialize( $post->post_content );

			// Update attributes.
			$raw_post['ID']         = $post->ID;
			$raw_post['title']      = $post->post_title;
			$raw_post['key']        = $post->post_name;
			$raw_post['menu_order'] = $post->menu_order;
			$raw_post['active']     = in_array( $post->post_status, array( 'publish', 'auto-draft' ) );

			return $raw_post;
		}

		/**
		 * Retrieves the WP_Post object for an ACF internal CPT.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The post ID, key, or name.
		 * @return WP_Post|bool The post object, or false on failure.
		 */
		public function get_post_object( $id = 0 ) {
			if ( is_numeric( $id ) ) {
				$post_object = get_post( $id );

				if ( $post_object && $post_object->post_type === $this->post_type ) {
					return $post_object;
				}

				return false;
			}

			if ( is_string( $id ) ) {
				// Try cache.
				$cache_key = $this->cache_key . $id;
				$post_id   = wp_cache_get( $cache_key, 'acf' );

				if ( $post_id === false ) {
					$query_key = 'acf_' . $this->post_key_prefix . 'key';

					// Query posts.
					$posts = get_posts(
						array(
							'posts_per_page'         => 1,
							'post_type'              => $this->post_type,
							'post_status'            => array( 'publish', 'acf-disabled', 'trash' ),
							'orderby'                => 'menu_order title',
							'order'                  => 'ASC',
							'suppress_filters'       => false,
							'cache_results'          => true,
							'update_post_meta_cache' => false,
							'update_post_term_cache' => false,
							$query_key               => $id, // Used to check post_name for field group/post type/taxonomy key.
						)
					);

					// Update $post_id with a non-false value.
					$post_id = $posts ? $posts[0]->ID : 0;

					// Update cache.
					wp_cache_set( $cache_key, $post_id, 'acf' );
				}

				// Check $post_id and return the post when possible.
				if ( $post_id ) {
					return get_post( $post_id );
				}
			}

			return false;
		}

		/**
		 * Returns true if the given identifier is an ACF post key.
		 *
		 * @since 6.1
		 *
		 * @param string $id The identifier.
		 * @return boolean
		 */
		public function is_post_key( $id = '' ) {
			// Check if $id is a string starting with $this->post_key.
			if ( is_string( $id ) && substr( $id, 0, strlen( $this->post_key_prefix ) ) === $this->post_key_prefix ) {
				return true;
			}

			/**
			 * Filters whether the $id is an ACF post key.
			 *
			 * @date    23/1/19
			 * @since   5.7.10
			 *
			 * @param bool $bool The result.
			 * @param string $id The identifier.
			 */
			return apply_filters( "acf/is_{$this->hook_name}_key", false, $id, $this->post_type );
		}

		/**
		 * Validates an ACF internal post type.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post array.
		 * @return array
		 */
		public function validate_post( $post = array() ) {
			// Bail early if already valid.
			if ( is_array( $post ) && ! empty( $post['_valid'] ) ) {
				return $post;
			}

			$post = wp_parse_args(
				$post,
				$this->get_settings_array()
			);

			// Convert types.
			$post['ID']         = (int) $post['ID'];
			$post['menu_order'] = (int) $post['menu_order'];
			$post['active']     = (bool) $post['active'];

			// Post is now valid.
			$post['_valid'] = true;

			/**
			 * Filters the ACF post array to validate settings.
			 *
			 * @date    12/02/2014
			 * @since   5.0.0
			 *
			 * @param   array $post The post array.
			 */
			return apply_filters( "acf/validate_{$this->hook_name}", $post );
		}

		/**
		 * Validates post type values before allowing save from the global $_POST object.
		 * Errors are added to the form using acf_add_internal_post_type_validation_error().
		 *
		 * @since 6.1
		 */
		public function ajax_validate_values() {}

		/**
		 * Ensures the given ACF post is valid.
		 *
		 * @since 6.1
		 *
		 * @param array $post The main post array.
		 * @return array
		 */
		public function get_valid_post( $post = false ) {
			return $this->validate_post( $post );
		}

		/**
		 * Gets the default settings array for an ACF post type.
		 *
		 * @return array
		 */
		public function get_settings_array() {
			return array();
		}

		/**
		 * Translates an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param array $post The field group array.
		 * @return array
		 */
		public function translate_post( $post = array() ) {
			// Get settings.
			$l10n            = acf_get_setting( 'l10n' );
			$l10n_textdomain = acf_get_setting( 'l10n_textdomain' );

			// Translate field settings if textdomain is set.
			if ( $l10n && $l10n_textdomain ) {
				$post['title'] = acf_translate( $post['title'] );

				/**
				 * Filters the post array to translate strings.
				 *
				 * @date    12/02/2014
				 * @since   5.0.0
				 *
				 * @param   array $post The post array.
				 */
				$post = apply_filters( "acf/translate_{$this->hook_name}", $post );
			}

			return $post;
		}

		/**
		 * Returns an array of ACF posts for the given $filter.
		 *
		 * @since 6.1
		 *
		 * @param array $filter An array of args to filter by.
		 * @return array
		 */
		public function get_posts( $filter = array() ) {
			$posts = array();

			// Check database.
			$raw_posts = $this->get_raw_posts();
			if ( $raw_posts ) {
				foreach ( $raw_posts as $raw_post ) {
					$posts[] = $this->get_post( $raw_post['ID'] );
				}
			}

			/**
			 * Filters the posts array.
			 *
			 * @date    12/02/2014
			 * @since   5.0.0
			 *
			 * @param array $posts The array of ACF posts.
			 */
			$posts = apply_filters( "acf/load_{$this->hook_name_plural}", $posts, $this->post_type );

			// Filter results.
			if ( $filter && $posts ) {
				return $this->filter_posts( $posts, $filter );
			}

			return $posts;
		}

		/**
		 * Returns an array of raw ACF post data.
		 *
		 * @since 6.1
		 *
		 * @return array
		 */
		public function get_raw_posts() {
			// Try cache.
			$cache_key = acf_cache_key( $this->cache_key_plural );
			$post_ids  = wp_cache_get( $cache_key, 'acf' ); // TODO: Do we need to change the group at all?

			if ( $post_ids === false ) {

				// Query posts.
				$posts = get_posts(
					array(
						'posts_per_page'         => -1,
						'post_type'              => $this->post_type,
						'orderby'                => 'menu_order title',
						'order'                  => 'ASC',
						'suppress_filters'       => false, // Allow WPML to modify the query.
						'cache_results'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'post_status'            => array( 'publish', 'acf-disabled' ),
					)
				);

				// Update $post_ids with a non-false value.
				$post_ids = array();
				foreach ( $posts as $post ) {
					$post_ids[] = $post->ID;
				}

				// Update cache.
				wp_cache_set( $cache_key, $post_ids, 'acf' );
			}

			// Loop over ids and populate array of ACF posts.
			$return = array();
			foreach ( $post_ids as $post_id ) {
				$raw_post = $this->get_raw_post( $post_id );
				if ( $raw_post ) {
					$return[] = $raw_post;
				}
			}

			return $return;
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
			if ( ! empty( $args['active'] ) ) {
				$posts = array_filter(
					$posts,
					function ( $post ) {
						return $post['active'];
					}
				);
			}

			return $posts;
		}

		/**
		 * Updates an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post to update.
		 * @return array
		 */
		public function update_post( $post ) {
			// Validate internal post type.
			$post = $this->validate_post( $post );

			// May have been posted. Remove slashes.
			$post = wp_unslash( $post );

			// Parse types (converts string '0' to int 0).
			$post = acf_parse_types( $post );

			/**
			 * Fires before updating an ACF post in the database.
			 *
			 * @since 6.1
			 *
			 * @param array $post The main ACF post array.
			 */
			$post = apply_filters( "acf/pre_update_{$this->hook_name}", $post );

			// Make a backup of internal post type data and remove some args.
			$_post = $post;
			acf_extract_vars( $_post, array( 'ID', 'key', 'title', 'menu_order', 'fields', 'active', '_valid', '_parent' ) );

			// Create array of data to save.
			$save = array(
				'ID'             => $post['ID'],
				'post_status'    => $post['active'] ? 'publish' : 'acf-disabled',
				'post_type'      => $this->post_type,
				'post_title'     => $post['title'],
				'post_name'      => $post['key'],
				'post_excerpt'   => sanitize_title( $post['title'] ),
				'post_content'   => maybe_serialize( $_post ),
				'menu_order'     => $post['menu_order'],
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_parent'    => ! empty( $post['_parent'] ) ? (int) $post['_parent'] : 0,
			);

			// Unhook wp_targeted_link_rel() filter from WP 5.1 corrupting serialized data.
			remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );

			// Slash data.
			// WP expects all data to be slashed and will unslash it (fixes '\' character issues).
			$save = wp_slash( $save );

			// Update or Insert.
			if ( $post['ID'] ) {
				wp_update_post( $save );
			} else {
				$post['ID'] = wp_insert_post( $save );
			}

			$this->flush_post_cache( $post );

			/**
			 * Fires immediately after an ACF post has been updated.
			 *
			 * @since   6.1
			 *
			 * @param array $post The main ACF post array.
			 */
			do_action( "acf/update_{$this->hook_name}", $post );

			return $post;
		}

		/**
		 * Allows full control over ACF post slugs.
		 *
		 * @since 6.1
		 *
		 * @param string  $slug          The post slug.
		 * @param integer $post_ID       Post ID.
		 * @param string  $post_status   The post status.
		 * @param string  $post_type     Post type.
		 * @param integer $post_parent   Post parent ID.
		 * @param string  $original_slug The original post slug.
		 * @return string
		 */
		public function apply_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
			// Check post type and reset to original value.
			if ( $post_type === $this->post_type ) {
				return $original_slug;
			}

			return $slug;
		}

		/**
		 * Deletes all caches for this ACF post.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post array.
		 * @return void
		 */
		public function flush_post_cache( $post ) {
			// Delete stored data.
			acf_get_store( $this->store )->remove( $post['key'] );

			// Flush cached post_id for this field group's key.
			wp_cache_delete( acf_cache_key( $this->cache_key . $post['key'] ), 'acf' );

			// Flush cached array of post_ids for collection of field groups.
			wp_cache_delete( acf_cache_key( $this->cache_key_plural ), 'acf' );
		}

		/**
		 * Deletes an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The ID of the ACF post to delete.
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
		 * Trashes an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id The ID of the ACF post to trash.
		 * @return boolean
		 */
		public function trash_post( $id = 0 ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			$post = $this->get_post( $id );
			if ( ! $post || ! $post['ID'] ) {
				return false;
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
		 * Restores an ACF post from the trash.
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
		 * Returns the previous post_status instead of "draft" for the ACF internal post types.
		 * Prior to WordPress 5.6.0, this filter was not needed as restored posts were always assigned their original status.
		 *
		 * @since 6.1
		 *
		 * @param string  $new_status      The new status of the post being restored.
		 * @param integer $post_id         The ID of the post being restored.
		 * @param string  $previous_status The status of the post at the point where it was trashed.
		 * @return string
		 */
		public function untrash_post_status( $new_status, $post_id, $previous_status ) {
			return ( get_post_type( $post_id ) === $this->post_type ) ? $previous_status : $new_status;
		}

		/**
		 * Returns true if the given params match an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param array $post The post array to check.
		 * @return boolean
		 */
		public function is_post( $post = false ) {
			return (
				is_array( $post )
				&& isset( $post['key'] )
				&& isset( $post['title'] )
			);
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

			// Update attributes.
			$post['ID']  = $new_post_id;
			$post['key'] = uniqid( 'group_' );

			// Add (copy) to title when appropriate.
			if ( ! $new_post_id ) {
				$post['title'] .= ' (' . __( 'copy', 'acf' ) . ')';
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
		 * Activates or deactivates an ACF post.
		 *
		 * @since 6.1
		 *
		 * @param integer|string $id       The ID of the ACF post to activate/deactivate.
		 * @param boolean        $activate True if the post should be activated.
		 * @return boolean
		 */
		public function update_post_active_status( $id, $activate = true ) {
			// Disable filters to ensure ACF loads data from DB.
			acf_disable_filters();

			$post = $this->get_post( $id );
			if ( ! $post || ! $post['ID'] ) {
				return false;
			}

			$post['active'] = (bool) $activate;
			$updated_post   = $this->update_post( $post );

			/**
			 * Fires immediately after an ACF post has been made active/inactive.
			 *
			 * @since 6.0.0
			 *
			 * @param array $updated_post The updated ACF post array.
			 */
			do_action( "acf/update_{$this->hook_name}_active_status", $updated_post );

			if ( ! isset( $updated_post['active'] ) || $activate !== $updated_post['active'] ) {
				return false;
			}

			return true;
		}

		/**
		 * Checks if the current user can edit ACF posts and returns the edit url.
		 *
		 * @since 6.1
		 *
		 * @param integer $post_id The ACF post ID.
		 * @return string
		 */
		public function get_post_edit_link( $post_id ) {
			if ( $post_id && acf_current_user_can_admin() ) {
				return admin_url( 'post.php?post=' . $post_id . '&action=edit' );
			}

			return '';
		}

		/**
		 * Returns a modified ACF post array ready for export.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post array.
		 * @return array
		 */
		public function prepare_post_for_export( $post = array() ) {
			// Remove args.
			acf_extract_vars( $post, array( 'ID', 'local', '_valid' ) );

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
		 * Returns a string containing PHP code that can be used to create the post in ACF.
		 *
		 * @since 6.1
		 *
		 * @param array $post The post being exported.
		 * @return string
		 */
		public function export_post_as_php( $post = array() ) {
			return '';
		}

		/**
		 * Formats code used for PHP exports.
		 *
		 * @since 6.1
		 *
		 * @param string $code The code being formatted.
		 * @return string
		 */
		public function format_code_for_export( $code = '' ) {
			if ( ! is_string( $code ) ) {
				return '';
			}

			$str_replace = array(
				'  '         => "\t",
				"'!!__(!!\'" => "__( '",
				"!!\', !!\'" => "', '",
				"!!\')!!'"   => "' )",
				'array ('    => 'array(',
			);

			$preg_replace = array(
				'/([\t\r\n]+?)array/' => 'array',
				'/[0-9]+ => array/'   => 'array',
			);

			$code = str_replace( array_keys( $str_replace ), array_values( $str_replace ), $code );
			$code = preg_replace( array_keys( $preg_replace ), array_values( $preg_replace ), $code );

			return $code;
		}

		/**
		 * Prepares an ACF post for import.
		 *
		 * @since 6.1
		 *
		 * @param array $post The ACF post array.
		 * @return array
		 */
		public function prepare_post_for_import( $post ) {
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
