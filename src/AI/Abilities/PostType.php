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

namespace ACF\AI\Abilities;

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF Post Type Abilities
 *
 * Handles ACF custom post type related abilities for the WordPress Abilities API.
 */
class PostType extends AbstractAbilityGroup {

	/**
	 * Register post type related abilities.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! $this->is_abilities_api_available() ) {
			return;
		}

		// Register ACF Custom Post Types resource.
		$this->register_ability(
			'acf/custom-post-types',
			array(
				'label'               => __( 'ACF Custom Post Types', 'acf' ),
				'description'         => __( 'Get all ACF registered custom post types', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type' => 'null',
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'custom_post_types' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'object' ),
						),
						'count'             => array( 'type' => 'integer' ),
						'message'           => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'get_custom_post_types' ),
				'permission_callback' => function () {
					return current_user_can( acf_get_setting( 'capability' ) );
				},
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);

		// Register custom post type ability.
		$this->register_ability(
			'acf/register-custom-post-type',
			array(
				'label'               => __( 'Register Custom Post Type', 'acf' ),
				'description'         => __( 'Register a new post type definition in WordPress (e.g., "Book", "Event"). This creates the post type schema itself, not individual posts. Use the create post abilities to add posts to an existing post type.', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'post_type'        => array(
							'type'        => 'string',
							'pattern'     => '^[a-z0-9_-]*$',
							'maxLength'   => 20,
							'description' => 'The post type key (slug)',
							'required'    => true,
						),
						'label'            => array(
							'type'        => 'string',
							'description' => 'The singular label for the post type',
							'required'    => true,
						),
						'plural_label'     => array(
							'type'        => 'string',
							'description' => 'The plural label for the post type',
							'required'    => true,
						),
						'description'      => array(
							'type'        => 'string',
							'description' => 'Description of the post type',
							'required'    => false,
						),
						'public'           => array(
							'type'        => 'boolean',
							'description' => 'Whether the post type is public',
							'required'    => false,
						),
						'hierarchical'     => array(
							'type'        => 'boolean',
							'description' => 'Whether the post type is hierarchical',
							'required'    => false,
						),
						'supports'         => array(
							'type'        => 'array',
							'description' => 'Features the post type supports. Can be array of strings ["title", "editor"] or object {"title": true, "editor": false}. Available: title, editor, author, thumbnail, excerpt, comments, trackbacks, revisions, page-attributes, custom-fields, post-formats',
							'required'    => false,
						),
						'show_in_rest'     => array(
							'type'        => 'boolean',
							'description' => 'Whether to show this post type in the REST API (required for AI abilities)',
							'required'    => false,
						),
						'rest_base'        => array(
							'type'        => 'string',
							'description' => 'Custom REST API base path (defaults to post type key)',
							'required'    => false,
						),
						'allow_ai_access'  => array(
							'type'        => 'boolean',
							'description' => 'Whether to allow AI access to this post type',
							'required'    => false,
						),
						'ai_description'   => array(
							'type'        => 'string',
							'description' => 'Description to help AI understand the purpose of this post type',
							'required'    => false,
						),
						'menu_icon'        => array(
							'type'        => array( 'string', 'object' ),
							'description' => 'Menu icon (dashicon class, URL, or object with type and value)',
							'required'    => false,
						),
						'menu_position'    => array(
							'type'        => 'integer',
							'description' => 'Position in the admin menu (5-100)',
							'required'    => false,
						),
						'has_archive'      => array(
							'type'        => 'boolean',
							'description' => 'Whether the post type has an archive page',
							'required'    => false,
						),
						'has_archive_slug' => array(
							'type'        => 'string',
							'description' => 'Custom slug for the archive page',
							'required'    => false,
						),
						'taxonomies'       => array(
							'type'        => 'array',
							'description' => 'Array of taxonomy names to associate with this post type',
							'items'       => array( 'type' => 'string' ),
							'required'    => false,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'   => array( 'type' => 'boolean' ),
						'post_type' => array( 'type' => 'object' ),
						'message'   => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'create_custom_post_type' ),
				'permission_callback' => function () {
					return current_user_can( acf_get_setting( 'capability' ) );
				},
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					),
					'show_in_rest' => true,
				),
			)
		);

		// Register abilities for each ACF custom post type that has REST API enabled.
		$this->register_acf_post_type_abilities();
	}

	/**
	 * Register abilities for each ACF custom post type that has REST API enabled.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	private function register_acf_post_type_abilities() {
		$acf_post_types = acf_get_acf_post_types();

		foreach ( $acf_post_types as $acf_post_type ) {
			$post_type_name = $acf_post_type['post_type'] ?? '';
			if ( ! $post_type_name ) {
				continue;
			}

			// Check if the post type is active and AI access is enabled.
			if ( empty( $acf_post_type['active'] ) || empty( $acf_post_type['allow_ai_access'] ) ) {
				continue;
			}

			// Sanitize post type name for feature ID (convert underscores to hyphens, ensure lowercase)
			$sanitized_post_type_name = str_replace( '_', '-', strtolower( $post_type_name ) );

			// Skip if we can't retrieve the post type object or if it isn't configured with REST API access.
			$post_type_object = get_post_type_object( $post_type_name );
			if ( ! $post_type_object || empty( $post_type_object->show_in_rest ) ) {
				continue;
			}

			$rest_base              = acf_get_object_type_rest_base( $post_type_object );
			$post_type_label        = $post_type_object->labels->singular_name ?? $post_type_name;
			$post_type_label_plural = $post_type_object->labels->name ?? $post_type_name . 's';

			// Get AI description for enhanced ability descriptions
			$ai_description     = $acf_post_type['ai_description'] ?? '';
			$description_suffix = $ai_description ? ' ' . $ai_description : '';

			// Get ACF fields for this post type.
			$acf_fields = $this->get_acf_fields_for_object( 'post_type', $post_type_name );

			// Get schemas from REST controller.
			$item_schema       = $this->get_rest_item_output_schema( $acf_fields, $post_type_name );
			$collection_schema = $this->get_rest_item_output_schema( $acf_fields, $post_type_name, 'collection' );

			// Register query/list feature for this post type
			$this->register_ability(
				'acf/' . $sanitized_post_type_name . 's',
				array(
					/* translators: %s The plural label for the custom post type. */
					'label'               => sprintf( __( 'Query %s', 'acf' ), $post_type_label_plural ),
					/* translators: %s The plural label for the custom post type. */
					'description'         => sprintf( __( 'Get a list of %s that match the query parameters.', 'acf' ), strtolower( $post_type_label_plural ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'                 => array( 'object', 'null' ),
						'properties'           => array(
							'per_page' => array(
								'type'    => 'integer',
								'default' => 10,
								'minimum' => 1,
								'maximum' => 100,
							),
							'page'     => array(
								'type'    => 'integer',
								'default' => 1,
								'minimum' => 1,
							),
							'search'   => array(
								'type'        => 'string',
								'description' => 'Limit results to those matching a string.',
							),
							'slug'     => array(
								'type'        => 'array',
								'items'       => array(
									'type' => 'string',
								),
								'description' => 'Limit result set to posts with one or more specific slugs.',
							),
							'orderby'  => array(
								'type'        => 'string',
								'enum'        => array( 'date', 'id', 'modified', 'relevance', 'slug', 'title' ),
								'default'     => 'date',
								'description' => 'Sort collection by post attribute.',
							),
							'order'    => array(
								'type'        => 'string',
								'enum'        => array( 'asc', 'desc' ),
								'default'     => 'desc',
								'description' => 'Order sort attribute ascending or descending.',
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => $collection_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						return $this->execute_rest_request( 'GET', $rest_base, $input );
					},
					'permission_callback' => function () use ( $post_type_object ) {
						// For querying, allow if user can read or if they can read private posts of this type.
						return current_user_can( 'read' ) || current_user_can( $post_type_object->cap->read_private_posts );
					},
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
					'ability_class'       => self::REST_ABILITY_CLASS,
				)
			);

			// Register create ability for this post type
			$this->register_ability(
				'acf/create-' . $sanitized_post_type_name,
				array(
					/* translators: %s The singular label for the custom post type. */
					'label'               => sprintf( __( 'Create %s', 'acf' ), $post_type_label ),
					/* translators: %s The singular label for the custom post type. */
					'description'         => sprintf( __( 'Create a new "%s" post item.', 'acf' ), strtolower( $post_type_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => $this->get_rest_item_input_schema( $acf_fields, $post_type_label, 'create', $post_type_name ),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						return $this->execute_rest_request( 'POST', $rest_base, $input );
					},
					'permission_callback' => function () use ( $post_type_object ) {
						return current_user_can( $post_type_object->cap->create_posts );
					},
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => false,
							'idempotent'  => false,
						),
						'show_in_rest' => true,
					),
					'ability_class'       => self::REST_ABILITY_CLASS,
				)
			);

			// Register view single ability for this post type
			$this->register_ability(
				'acf/view-' . $sanitized_post_type_name,
				array(
					/* translators: %s The singular label for the custom post type. */
					'label'               => sprintf( __( 'View a %s', 'acf' ), $post_type_label ),
					/* translators: %s The singular label for the custom post type. */
					'description'         => sprintf( __( 'Get a %s by its ID.', 'acf' ), strtolower( $post_type_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'type'        => 'integer',
								'description' => sprintf( 'The ID of the %s to view.', strtolower( $post_type_label ) ),
								'required'    => true,
							),
						),
					),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						$item_id = $input['id'] ?? null;
						return $this->execute_rest_request( 'GET', $rest_base, $input, $item_id );
					},
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
					'ability_class'       => self::REST_ABILITY_CLASS,
				)
			);

			// Register update ability for this post type
			$this->register_ability(
				'acf/update-' . $sanitized_post_type_name,
				array(
					/* translators: %s The singular label for the custom post type. */
					'label'               => sprintf( __( 'Update a %s', 'acf' ), $post_type_label ),
					/* translators: %s The singular label for the custom post type. */
					'description'         => sprintf( __( 'Update a %s by its ID.', 'acf' ), strtolower( $post_type_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => $this->get_rest_item_input_schema( $acf_fields, $post_type_label, 'update', $post_type_name ),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						$item_id = $input['id'] ?? null;
						return $this->execute_rest_request( 'PUT', $rest_base, $input, $item_id );
					},
					'permission_callback' => function () use ( $post_type_object ) {
						return current_user_can( $post_type_object->cap->edit_posts );
					},
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => false,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
					'ability_class'       => self::REST_ABILITY_CLASS,
				)
			);

			// Register delete ability for this post type.
			$this->register_ability(
				'acf/delete-' . $sanitized_post_type_name,
				array(
					/* translators: %s The singular label for the custom post type. */
					'label'               => sprintf( __( 'Delete a %s', 'acf' ), $post_type_label ),
					/* translators: %s The singular label for the custom post type. */
					'description'         => sprintf( __( 'Delete a %s by its ID.', 'acf' ), strtolower( $post_type_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'type'        => 'integer',
								'description' => sprintf( 'The ID of the %s to delete.', strtolower( $post_type_label ) ),
								'required'    => true,
							),
						),
					),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						$item_id = $input['id'] ?? null;
						return $this->execute_rest_request( 'DELETE', $rest_base, $input, $item_id );
					},
					'permission_callback' => function () use ( $post_type_object ) {
						return current_user_can( $post_type_object->cap->delete_posts );
					},
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => true,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
					'ability_class'       => self::REST_ABILITY_CLASS,
				)
			);
		}
	}

	/**
	 * Builds a basic input schema for creating or updating a post type item.
	 *
	 * We only include a few common properties since the REST API handles the
	 * validation and updates.
	 *
	 * @since 6.8.0
	 *
	 * @param array  $acf_fields      An array of ACF fields present on the post type.
	 * @param string $post_type_label The singular label for the post type.
	 * @param string $action          The action being performed on the item.
	 * @param string $post_type_name  The post type name/key.
	 * @return array
	 */
	private function get_rest_item_input_schema( array $acf_fields, string $post_type_label, string $action = 'create', string $post_type_name = '' ): array {
		$schema = array(
			'type'       => 'object',
			'properties' => array(),
		);

		if ( 'update' === $action ) {
			$schema['properties']['id'] = array(
				'type'        => 'integer',
				'description' => sprintf( 'The ID of the %s to update.', strtolower( $post_type_label ) ),
				'required'    => true,
			);
		}

		$schema['properties']['title'] = array(
			'type'        => 'string',
			'description' => sprintf( 'The title of the %s.', strtolower( $post_type_label ) ),
			'required'    => 'update' !== $action,
		);

		$schema['properties']['content'] = array(
			'type'        => 'string',
			'description' => sprintf( 'The content of the %s.', strtolower( $post_type_label ) ),
			'required'    => false,
		);

		// TODO: Provide enum?
		$schema['properties']['status'] = array(
			'type'        => 'string',
			'description' => 'The status of the post (publish, draft, etc.)',
			'required'    => false,
		);

		// Only add featured_media if the post type supports thumbnails.
		if ( ! empty( $post_type_name ) && post_type_supports( $post_type_name, 'thumbnail' ) ) {
			$schema['properties']['featured_media'] = array(
				'type'        => 'integer',
				'description' => 'The ID of the featured image (attachment) for this post. The attachment must exist and be an image. Set to 0 to remove; invalid or non-image IDs will not set a featured image.',
				'required'    => false,
			);
		}

		// Only add author if the post type supports author.
		if ( ! empty( $post_type_name ) && post_type_supports( $post_type_name, 'author' ) ) {
			$schema['properties']['author'] = array(
				'type'        => 'integer',
				'description' => 'The ID of the user to assign as the author of this post. The user must exist and have permission to be assigned as an author.',
				'required'    => false,
			);
		}

		return $this->add_acf_fields_to_schema( $schema, $acf_fields );
	}

	/**
	 * Gets the REST schema for item(s) in a CPT.
	 *
	 * @since 6.8.0
	 *
	 * @param array  $acf_fields An array of ACF fields present on the post type.
	 * @param string $post_type  The post type to get the schema for.
	 * @param string $type       Schema type: 'item' or 'collection'.
	 * @return array|null Schema array or null if not available.
	 */
	private function get_rest_item_output_schema( array $acf_fields, string $post_type, string $type = 'item' ) {
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object ) {
			return null;
		}

		$controller = $post_type_object->get_rest_controller();
		if ( ! $controller || ! method_exists( $controller, 'get_public_item_schema' ) ) {
			return null;
		}

		$schema = $controller->get_public_item_schema();
		$schema = $this->add_acf_fields_to_schema( $schema, $acf_fields );

		if ( $type === 'collection' ) {
			return array(
				'type'  => 'array',
				'items' => $schema,
			);
		}

		return $schema;
	}

	/**
	 * Callback for the "acf/get-custom-post-types" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input An array of input args.
	 * @return array
	 */
	public function get_custom_post_types( $input ) {
		unset( $input ); // Not used, but required by interface.

		// Get ACF custom post types.
		$acf_post_types    = acf_get_acf_post_types();
		$custom_post_types = array();

		foreach ( $acf_post_types as $acf_post_type ) {
			$post_type_name = $acf_post_type['post_type'] ?? '';
			if ( ! $post_type_name ) {
				continue;
			}

			if ( empty( $acf_post_type['active'] ) || empty( $acf_post_type['allow_ai_access'] ) ) {
				continue;
			}

			$post_type_object = get_post_type_object( $post_type_name );
			if ( $post_type_object ) {
				$post_type_data = array(
					'post_type'    => $post_type_name,
					'label'        => $post_type_object->label,
					'labels'       => (array) $post_type_object->labels,
					'description'  => $post_type_object->description,
					'public'       => $post_type_object->public,
					'hierarchical' => $post_type_object->hierarchical,
					'supports'     => get_all_post_type_supports( $post_type_name ),
					'acf_settings' => $acf_post_type,
				);

				// Add ACF field groups information
				$acf_fields = $this->get_acf_fields_for_object( 'post_type', $post_type_name );
				if ( ! empty( $acf_fields ) ) {
					$post_type_data['acf_field_groups'] = $acf_fields;
				}

				$custom_post_types[] = $post_type_data;
			}
		}

		$count = count( $custom_post_types );

		return array(
			'custom_post_types' => $custom_post_types,
			'count'             => $count,
			'message'           => sprintf(
				/* translators: %d: Number of ACF custom post types */
				_n( 'Found %d ACF custom post type', 'Found %d ACF custom post types', $count, 'acf' ),
				$count
			),
		);
	}

	/**
	 * Callback for the "acf/register-custom-post-type" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input An array of input args.
	 * @return array|WP_Error
	 */
	public function create_custom_post_type( $input ) {
		// Required parameters
		$post_type    = sanitize_key( $input['post_type'] );
		$label        = sanitize_text_field( $input['label'] );
		$plural_label = sanitize_text_field( $input['plural_label'] );

		// Check if post type already exists.
		if ( post_type_exists( $post_type ) ) {
			return new WP_Error(
				'post_type_exists',
				__( 'A post type with this key already exists', 'acf' ),
				array( 'status' => 400 )
			);
		}

		// Basic optional parameters
		$description  = sanitize_text_field( $input['description'] ?? '' );
		$public       = $input['public'] ?? true;
		$hierarchical = $input['hierarchical'] ?? false;
		$supports     = $input['supports'] ?? array( 'title', 'editor' );

		// REST API settings
		$show_in_rest = $input['show_in_rest'] ?? true;
		$rest_base    = $input['rest_base'] ?? '';

		// AI settings
		$allow_ai_access = $input['allow_ai_access'] ?? true;
		$ai_description  = $input['ai_description'] ?? '';

		// Menu settings
		$menu_icon     = $input['menu_icon'] ?? '';
		$menu_position = $input['menu_position'] ?? null;

		// Archive settings
		$has_archive      = $input['has_archive'] ?? false;
		$has_archive_slug = $input['has_archive_slug'] ?? '';

		// Taxonomies
		$taxonomies = $input['taxonomies'] ?? array();

		// Handle supports parameter - convert from associative array to simple array if needed
		if ( is_array( $supports ) && ! empty( $supports ) ) {
			// Check if this is an associative array (like {'title': true, 'editor': false})
			if ( array_keys( $supports ) !== range( 0, count( $supports ) - 1 ) ) {
				// Convert associative array to simple array of enabled features
				$enabled_supports = array();
				foreach ( $supports as $feature => $enabled ) {
					if ( $enabled ) {
						$enabled_supports[] = sanitize_key( $feature );
					}
				}
				$supports = $enabled_supports;
			} else {
				$supports = array_map( 'sanitize_key', $supports );
			}
		}

		// Use ACF's method to create the post type.
		$post_type_data = array(
			'key'             => uniqid( 'post_type_' ),
			'post_type'       => $post_type,
			'title'           => $plural_label,
			'labels'          => wp_parse_args(
				array(
					'name'          => $plural_label,
					'singular_name' => $label,
				),
				acf_get_internal_post_type_instance( 'acf-post-type' )->get_settings_array()['labels']
			),
			'description'     => $description,
			'public'          => $public ? 1 : 0,
			'hierarchical'    => $hierarchical ? 1 : 0,
			'supports'        => $supports,
			'active'          => 1,
			// REST API settings
			'show_in_rest'    => $show_in_rest ? 1 : 0,
			// AI settings
			'allow_ai_access' => $allow_ai_access ? 1 : 0,
		);

		// Add optional settings only if provided
		if ( ! empty( $rest_base ) ) {
			$post_type_data['rest_base'] = sanitize_text_field( $rest_base );
		}

		if ( ! empty( $ai_description ) ) {
			$post_type_data['ai_description'] = sanitize_text_field( $ai_description );
		}

		if ( ! empty( $menu_icon ) ) {
			// Handle menu_icon which can be string or object
			if ( is_string( $menu_icon ) ) {
				$post_type_data['menu_icon'] = array(
					'type'  => strpos( $menu_icon, 'http' ) === 0 ? 'url' : 'dashicons',
					'value' => sanitize_text_field( $menu_icon ),
				);
			} elseif ( is_array( $menu_icon ) && isset( $menu_icon['type'], $menu_icon['value'] ) ) {
				$post_type_data['menu_icon'] = array(
					'type'  => sanitize_text_field( $menu_icon['type'] ),
					'value' => sanitize_text_field( $menu_icon['value'] ),
				);
			}
		}

		if ( ! is_null( $menu_position ) ) {
			$post_type_data['menu_position'] = intval( $menu_position );
		}

		if ( $has_archive ) {
			$post_type_data['has_archive'] = 1;
			if ( ! empty( $has_archive_slug ) ) {
				$post_type_data['has_archive_slug'] = sanitize_title( $has_archive_slug );
			}
		}

		if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
			$post_type_data['taxonomies'] = array_map( 'sanitize_key', $taxonomies );
		}

		$result = acf_import_post_type( $post_type_data );

		if ( empty( $result['ID'] ) || ! is_int( $result['ID'] ) || ! post_type_exists( $result['post_type'] ) ) {
			return new WP_Error(
				'post_type_creation_failed',
				__( 'Failed to create the custom post type', 'acf' )
			);
		}

		return array(
			'success'   => true,
			'post_type' => $result,
			'message'   => __( 'ACF custom post type created successfully', 'acf' ),
		);
	}
}
