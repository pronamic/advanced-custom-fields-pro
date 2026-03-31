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

namespace ACF\AI\GEO\Outputs;

use ACF\AI\GEO\GEO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF GEO Posts Output
 *
 * Handles JSON-LD structured data output for ACF fields on post types.
 */
class Posts {

	/**
	 * Constructor
	 *
	 * @since 6.8.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the GEO Posts extension
	 *
	 * @since 6.8.0
	 */
	public function init() {
		// Add front-end JSON-LD output for posts.
		add_action( 'wp_head', array( $this, 'output_jsonld_data' ) );
	}

	/**
	 * Output JSON-LD structured data for ACF fields on posts
	 *
	 * @since 6.8.0
	 */
	public function output_jsonld_data() {
		/**
		 * Filters whether to output debug comments in HTML
		 *
		 * @param bool $debug Whether to output debug comments. Default false.
		 */
		$debug = apply_filters( 'acf/schema/debug', false );

		// Only output on singular posts/pages.
		if ( ! is_singular() ) {
			if ( $debug ) {
				echo "<!-- ACF AI JSON-LD: Not a singular post/page -->\n";
			}
			return;
		}

		global $post;
		if ( ! $post ) {
			if ( $debug ) {
				echo "<!-- ACF AI JSON-LD: No post object found -->\n";
			}
			return;
		}

		$post_type = get_post_type( $post );
		if ( ! $post_type ) {
			if ( $debug ) {
				echo "<!-- ACF AI JSON-LD: No post type found -->\n";
			}
			return;
		}

		if ( $debug ) {
			echo '<!-- ACF AI JSON-LD: Checking post type: ' . esc_html( $post_type ) . " -->\n";
		}

		// Build list of post types with JSON-LD enabled.
		$enabled_post_types = array();

		// Get ACF custom post types with JSON-LD enabled.
		$acf_post_types = acf_get_acf_post_types();
		foreach ( $acf_post_types as $acf_post_type ) {
			if ( ! empty( $acf_post_type['auto_jsonld'] ) && isset( $acf_post_type['post_type'] ) ) {
				$enabled_post_types[] = $acf_post_type['post_type'];
			}
		}

		/**
		 * Filters the list of post types that have JSON-LD output enabled.
		 *
		 * @param array $enabled_post_types Array of post type names with JSON-LD enabled.
		 */
		$enabled_post_types = apply_filters( 'acf/schema/enabled_post_types', $enabled_post_types );

		// Exit if current post type is not in the enabled list.
		if ( ! in_array( $post_type, $enabled_post_types, true ) ) {
			if ( $debug ) {
				echo '<!-- ACF AI JSON-LD: Post type \'' . esc_html( $post_type ) . "' does not have JSON-LD enabled -->\n";
			}
			return;
		}

		/**
		 * Filters the field objects before retrieval, allowing posts to provide custom data.
		 *
		 * This is useful for posts that need custom field data handling.
		 * Return a non-null value to short-circuit the default get_field_objects() call.
		 *
		 * @since 6.8.0
		 *
		 * @param array|null $field_objects The field objects array, or null to use default behavior.
		 * @param WP_Post    $post          The post object.
		 * @param string     $post_type     The post type.
		 */
		$field_objects = apply_filters( 'acf/schema/post_field_objects', null, $post, $post_type );

		/**
		 * Filters the field objects for a specific post type.
		 *
		 * The dynamic portion of the hook name, `$post_type`, refers to the post type name.
		 * For example, 'acf/schema/post_field_objects/post_type=product' for the product post type.
		 *
		 * @since 6.8.0
		 *
		 * @param array|null $field_objects The field objects array, or null to use default behavior.
		 * @param WP_Post    $post          The post object.
		 */
		$field_objects = apply_filters( 'acf/schema/post_field_objects/post_type=' . $post_type, $field_objects, $post );

		// If no custom field objects were provided, get them from the post.
		if ( null === $field_objects ) {
			// Get all ACF field objects for this post without formatting to get raw ACF storage format.
			$field_objects = get_field_objects( $post->ID, false );
		}

		if ( ! $field_objects || ! is_array( $field_objects ) ) {
			if ( $debug ) {
				echo '<!-- ACF AI JSON-LD: No ACF fields found for post ID ' . absint( $post->ID ) . " -->\n";
			}
			return;
		}

		if ( $debug ) {
			echo '<!-- ACF AI JSON-LD: Found ' . count( $field_objects ) . " ACF fields -->\n";
		}

		// Process ACF fields and extract types from qualified properties.
		// This handles schema_property mapping.
		$processed_fields = GEO::process_fields( $field_objects );

		// Get any explicitly set schema type for this post type.
		$acf_post_type_object = null;
		foreach ( $acf_post_types as $acf_post_type ) {
			if ( isset( $acf_post_type['post_type'] ) && $acf_post_type['post_type'] === $post_type ) {
				$acf_post_type_object = $acf_post_type;
				break;
			}
		}

		$provided_type = $acf_post_type_object['schema_type'] ?? null;
		$field_types   = $processed_fields['field_types'] ?? array();

		// Determine the final @type based on provided type or field types from qualified properties.
		$schema_type = GEO::determine_schema_type( $provided_type, $field_types, 'Article' );

		// Remove internal type data from processed fields.
		unset( $processed_fields['field_types'] );

		// Build base JSON-LD structured data.
		$jsonld_data = array(
			'@context' => 'https://schema.org',
			'@type'    => $schema_type,
			'@id'      => get_permalink( $post ),
			'url'      => get_permalink( $post ),
			'name'     => get_the_title( $post ),
			'headline' => get_the_title( $post ),
		);

		// Add publication date if available.
		if ( $post->post_date ) {
			$jsonld_data['datePublished'] = get_the_date( 'c', $post );
		}

		if ( $post->post_modified ) {
			$jsonld_data['dateModified'] = get_the_modified_date( 'c', $post );
		}

		/**
		 * Filter whether to automatically add featured image to JSON-LD output
		 *
		 * @param bool    $add_image  Whether to add the featured image. Default true.
		 * @param WP_Post $post       The post object.
		 * @param string  $post_type  The post type.
		 */
		$add_image = apply_filters( 'acf/schema/auto_add_image', true, $post, $post_type );

		// Add featured image if available and enabled.
		if ( $add_image && has_post_thumbnail( $post ) ) {
			$thumbnail_id  = get_post_thumbnail_id( $post );
			$thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );

			if ( $thumbnail_url ) {
				// Get image metadata for additional properties.
				$image_meta = wp_get_attachment_metadata( $thumbnail_id );

				$jsonld_data['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $thumbnail_url,
				);
				if ( isset( $image_meta['width'] ) ) {
					$jsonld_data['image']['width'] = $image_meta['width'];
				}
				if ( isset( $image_meta['height'] ) ) {
					$jsonld_data['image']['height'] = $image_meta['height'];
				}
			}
		}

		/**
		 * Filter whether to automatically add author to JSON-LD output
		 *
		 * @param bool    $add_author Whether to add the author. Default true.
		 * @param WP_Post $post       The post object.
		 * @param string  $post_type  The post type.
		 */
		$add_author = apply_filters( 'acf/schema/auto_add_author', true, $post, $post_type );

		// Add author if enabled.
		if ( $add_author && $post->post_author ) {
			$author = get_userdata( $post->post_author );

			if ( $author ) {
				$jsonld_data['author'] = array(
					'@type' => 'Person',
					'name'  => $author->display_name,
				);

				// Add author URL if available.
				$author_url = get_author_posts_url( $post->post_author );
				if ( $author_url ) {
					$jsonld_data['author']['url'] = $author_url;
				}
			}
		}

		// Merge processed fields into JSON-LD data.
		$jsonld_data = array_merge( $jsonld_data, $processed_fields );

		/**
		 * Filters the JSON-LD data before output for a post.
		 *
		 * @param array   $jsonld_data The JSON-LD data array.
		 * @param WP_Post $post        The post object.
		 * @param string  $post_type   The post type.
		 */
		$jsonld_data = apply_filters( 'acf/schema/data', $jsonld_data, $post, $post_type );

		// Only output if we have data after filtering.
		if ( empty( $jsonld_data ) ) {
			return;
		}

		// Output the JSON-LD using the shared helper.
		GEO::render_jsonld_script( $jsonld_data );
	}
}
