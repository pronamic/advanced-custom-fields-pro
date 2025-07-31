<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'acf_revisions' ) ) :
	class acf_revisions {

		/**
		 * An array to cache post IDs for revisions.
		 * @var array
		 */
		public $cache = array();

		/**
		 * Constructs the acf_revisions class.
		 */
		public function __construct() {
			add_action( 'wp_restore_post_revision', array( $this, 'wp_restore_post_revision' ), 10, 2 );
			add_filter( '_wp_post_revision_fields', array( $this, 'wp_preview_post_fields' ), 10, 2 );
			add_filter( '_wp_post_revision_fields', array( $this, 'wp_post_revision_fields' ), 10, 2 );
			add_filter( 'acf/validate_post_id', array( $this, 'acf_validate_post_id' ), 10, 2 );

			// WP 6.4+ handles things differently.
			if ( version_compare( get_bloginfo( 'version' ), '6.4', '>=' ) ) {
				add_action( '_wp_put_post_revision', array( $this, 'maybe_save_revision' ), 10, 2 );
				add_filter( 'wp_save_post_revision_post_has_changed', array( $this, 'check_acf_fields_have_changed' ), 9, 3 );
				add_filter( 'wp_post_revision_meta_keys', array( $this, 'wp_post_revision_meta_keys' ) );

				$this->register_meta();
			} else {
				add_filter( 'wp_save_post_revision_check_for_changes', array( $this, 'wp_save_post_revision_check_for_changes' ), 10, 3 );
			}
		}

		/**
		 * Registers any ACF meta that should be sent the REST/Gutenberg request.
		 * For now, this is just our "_acf_changed" key that we use to detect if ACF fields have changed.
		 *
		 * @since 6.2.6
		 */
		public function register_meta() {
			register_meta(
				'post',
				'_acf_changed',
				array(
					'type'              => 'boolean',
					'single'            => true,
					'show_in_rest'      => true,
					'revisions_enabled' => true,
					'auth_callback'     => '__return_true',
				)
			);
		}

		/**
		 * Lets WordPress know which meta keys to include in revisions.
		 * For now, this is just our "_acf_changed" key, as we still handle revisions ourselves.
		 *
		 * @since 6.2.6
		 *
		 * @param array $keys The meta keys that should be revisioned.
		 * @return array
		 */
		public function wp_post_revision_meta_keys( $keys ) {
			$keys[] = '_acf_changed';
			return $keys;
		}

		/**
		 * Helps WordPress determine if fields have changed, and if in a legacy
		 * metabox AJAX request, copies the metadata to the new revision.
		 *
		 * @since 6.2.6
		 *
		 * @param boolean $post_has_changed True if the post has changed, false if not.
		 * @param WP_Post $last_revision    The WP_Post object for the latest revision.
		 * @param WP_Post $post             The WP_Post object for the parent post.
		 * @return boolean
		 */
		public function check_acf_fields_have_changed( $post_has_changed, $last_revision, $post ) {
			if ( acf_maybe_get_GET( 'meta-box-loader', false ) ) {
				// We're in a legacy AJAX request, so we copy fields over to the latest revision.
				$this->maybe_save_revision( $last_revision->ID, $post->ID );
			} elseif ( acf_maybe_get_POST( '_acf_changed', false ) ) {
				// We're in a classic editor save request, so notify WP that fields have changed.
				$post_has_changed = true;
			}

			// Let WordPress decide for REST/block editor requests.
			return $post_has_changed;
		}

		/**
		 * Copies ACF field data to the latest revision.
		 *
		 * @since 6.2.6
		 *
		 * @param integer $revision_id The ID of the revision that was just created.
		 * @param integer $post_id     The ID of the post being updated.
		 * @return void
		 */
		public function maybe_save_revision( $revision_id, $post_id ) {
			// We don't have anything to copy over yet.
			if ( ! did_action( 'acf/save_post' ) ) {
				delete_metadata( 'post', $post_id, '_acf_changed' );
				delete_metadata( 'post', $revision_id, '_acf_changed' );
				return;
			}

			// Bail if this is an autosave in Classic Editor, it already has the field values.
			if ( acf_maybe_get_POST( '_acf_changed' ) && wp_is_post_autosave( $revision_id ) ) {
				return;
			}

			// Copy the saved meta from the main post to the latest revision.
			acf_save_post_revision( $post_id );
		}

		/**
		 * This function is used to trick WP into thinking that one of the $post's fields has changed and
		 * will allow an autosave to be updated.
		 * Fixes an odd bug causing the preview page to render the non autosave post data on every odd attempt
		 *
		 * @type    function
		 * @date    21/10/2014
		 * @since   5.1.0
		 *
		 * @param   $fields (array)
		 * @return  $fields
		 */
		function wp_preview_post_fields( $fields ) {

			// bail early if not previewing a post
			if ( acf_maybe_get_POST( 'wp-preview' ) !== 'dopreview' ) {
				return $fields;
			}

			// add to fields if ACF has changed
			if ( acf_maybe_get_POST( '_acf_changed' ) ) {
				$fields['_acf_changed'] = 'different than 1';
			}

			// return
			return $fields;
		}


		/**
		 * This filter will return false and force WP to save a revision. This is required due to
		 * WP checking only post_title, post_excerpt and post_content values, not custom fields.
		 *
		 * @type    filter
		 * @date    19/09/13
		 *
		 * @param   boolean $return        defaults to true
		 * @param   object  $last_revision the last revision that WP will compare against
		 * @param   object  $post          the $post object that WP will compare against
		 * @return  boolean $return
		 */
		function wp_save_post_revision_check_for_changes( $return, $last_revision, $post ) {

			// if acf has changed, return false and prevent WP from performing 'compare' logic
			if ( acf_maybe_get_POST( '_acf_changed' ) ) {
				return false;
			}

			// return
			return $return;
		}


		/**
		 * This filter will add the ACF fields to the returned array
		 * Versions 3.5 and 3.6 of WP feature different uses of the revisions filters, so there are
		 * some hacks to allow both versions to work correctly
		 *
		 * @type    filter
		 * @date    11/08/13
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function wp_post_revision_fields( $fields, $post = null ) {

			// validate page
			if ( acf_is_screen( 'revision' ) || acf_is_ajax( 'get-revision-diffs' ) ) {

				// bail early if is restoring
				if ( acf_maybe_get_GET( 'action' ) === 'restore' ) {
					return $fields;
				}

				// allow
			} else {

				// bail early (most likely saving a post)
				return $fields;
			}

			// vars
			$append  = array();
			$order   = array();
			$post_id = acf_maybe_get( $post, 'ID' );

			// compatibility with WP < 4.5 (test)
			if ( ! $post_id ) {
				global $post;
				$post_id = $post->ID;
			}

			// get all postmeta
			$meta = get_post_meta( $post_id );

			// bail early if no meta
			if ( ! $meta ) {
				return $fields;
			}

			// loop
			foreach ( $meta as $name => $value ) {

				// attempt to find key value
				$key = acf_maybe_get( $meta, '_' . $name );

				// bail early if no key
				if ( ! $key ) {
					continue;
				}

				// update vars
				$value = $value[0];
				$key   = $key[0];

				// Load field.
				$field = acf_get_field( $key );
				if ( ! $field ) {
					continue;
				}

				// get field
				$field_title = $field['label'] . ' (' . $name . ')';
				$field_order = $field['menu_order'];
				$ancestors   = acf_get_field_ancestors( $field );

				// ancestors
				if ( ! empty( $ancestors ) ) {

					// vars
					$count  = count( $ancestors );
					$oldest = acf_get_field( $ancestors[ $count - 1 ] );

					// update vars
					$field_title = str_repeat( '- ', $count ) . $field_title;
					$field_order = $oldest['menu_order'] . '.1';
				}

				// append
				$append[ $name ] = $field_title;
				$order[ $name ]  = $field_order;

				// hook into specific revision field filter and return local value
				add_filter( "_wp_post_revision_field_{$name}", array( $this, 'wp_post_revision_field' ), 10, 4 );
			}

			// append
			if ( ! empty( $append ) ) {

				// vars
				$prefix = '_';

				// add prefix
				$append = acf_add_array_key_prefix( $append, $prefix );
				$order  = acf_add_array_key_prefix( $order, $prefix );

				// sort by name (orders sub field values correctly)
				array_multisort( $order, $append );

				// remove prefix
				$append = acf_remove_array_key_prefix( $append, $prefix );

				// append
				$fields = $fields + $append;
			}

			// return
			return $fields;
		}

		/**
		 * Load the value for the given field and return it for rendering.
		 *
		 * @param mixed  $value      Should be false as it has not yet been loaded.
		 * @param string $field_name The name of the field
		 * @param mixed  $post       Holds the $post object to load from - in WP 3.5, this is not passed!
		 * @param string $direction  To / from - not used.
		 * @return string $value
		 */
		public function wp_post_revision_field( $value, $field_name, $post = null, $direction = false ) {
			// Bail early if is empty.
			if ( empty( $value ) ) {
				return '';
			}

			$value   = acf_maybe_unserialize( $value );
			$post_id = $post->ID;

			// load field.
			$field = acf_maybe_get_field( $field_name, $post_id );

			// default formatting.
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			} elseif ( is_object( $value ) ) {
				$value = serialize( $value );
			}

			// image.
			if ( is_array( $field ) && isset( $field['type'] ) && ( $field['type'] === 'image' || $field['type'] === 'file' ) ) {
				$url   = wp_get_attachment_url( $value );
				$value = $value . ' (' . $url . ')';
			}

			return $value;
		}

		/**
		 * This action will copy and paste the metadata from a revision to the post
		 *
		 * @type    action
		 * @date    11/08/13
		 *
		 * @param   $parent_id (int) the destination post
		 * @return  $revision_id (int) the source post
		 */
		function wp_restore_post_revision( $post_id, $revision_id ) {

			// copy postmeta from revision to post (restore from revision)
			acf_copy_postmeta( $revision_id, $post_id );

			// Make sure the latest revision is also updated to match the new $post data
			// get latest revision
			$revision = acf_get_post_latest_revision( $post_id );

			// save
			if ( $revision ) {

				// copy postmeta from revision to latest revision (potentialy may be the same, but most likely are different)
				acf_copy_postmeta( $revision_id, $revision->ID );
			}
		}


		/**
		 * This function will modify the $post_id and allow loading values from a revision
		 *
		 * @type    function
		 * @date    6/3/17
		 * @since   5.5.10
		 *
		 * @param   $post_id (int)
		 * @param   $_post_id (int)
		 * @return  $post_id (int)
		 */
		function acf_validate_post_id( $post_id, $_post_id ) {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			// bail early if no preview in URL
			if ( ! isset( $_GET['preview'] ) ) {
				return $post_id;
			}

			// bail early if $post_id is not numeric
			if ( ! is_numeric( $post_id ) ) {
				return $post_id;
			}

			// vars
			$k          = $post_id;
			$preview_id = 0;

			// check cache
			if ( isset( $this->cache[ $k ] ) ) {
				return $this->cache[ $k ];
			}

			// validate
			if ( isset( $_GET['preview_id'] ) ) {
				$preview_id = (int) $_GET['preview_id'];
			} elseif ( isset( $_GET['p'] ) ) {
				$preview_id = (int) $_GET['p'];
			} elseif ( isset( $_GET['page_id'] ) ) {
				$preview_id = (int) $_GET['page_id'];
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			// bail early id $preview_id does not match $post_id
			if ( $preview_id != $post_id ) {
				return $post_id;
			}

			// attempt find revision
			$revision = acf_get_post_latest_revision( $post_id );

			// save
			if ( $revision && $revision->post_parent == $post_id ) {
				$post_id = (int) $revision->ID;
			}

			// set cache
			$this->cache[ $k ] = $post_id;

			// return
			return $post_id;
		}
	}

	// initialize
	acf()->revisions = new acf_revisions();
endif; // class_exists check


/**
 * This function will copy meta from a post to it's latest revision
 *
 * @type    function
 * @date    26/09/2016
 * @since   5.4.0
 *
 * @param   $post_id (int)
 * @return  n/a
 */
function acf_save_post_revision( $post_id = 0 ) {

	// get latest revision
	$revision = acf_get_post_latest_revision( $post_id );

	// save
	if ( $revision ) {
		acf_copy_postmeta( $post_id, $revision->ID );
	}
}


/**
 * This function will return the latest revision for a given post
 *
 * @type    function
 * @date    25/06/2016
 * @since   5.3.8
 *
 * @param   $post_id (int)
 * @return  $post_id (int)
 */
function acf_get_post_latest_revision( $post_id ) {

	// vars
	$revisions = wp_get_post_revisions( $post_id );

	// shift off and return first revision (will return null if no revisions)
	$revision = array_shift( $revisions );

	// return
	return $revision;
}
