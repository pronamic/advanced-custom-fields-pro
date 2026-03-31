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
 * ACF Taxonomy Abilities
 *
 * Handles ACF custom taxonomy related abilities for the WordPress Abilities API.
 */
class Taxonomy extends AbstractAbilityGroup {

	/**
	 * Register taxonomy related abilities.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! $this->is_abilities_api_available() ) {
			return;
		}

		// Register ACF Custom Taxonomies resource.
		$this->register_ability(
			'acf/custom-taxonomies',
			array(
				'label'               => __( 'ACF Custom Taxonomies', 'acf' ),
				'description'         => __( 'Get all ACF registered custom taxonomies', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type' => 'null',
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'custom_taxonomies' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'object' ),
						),
						'count'             => array( 'type' => 'integer' ),
						'message'           => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'get_custom_taxonomies' ),
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

		// Register custom taxonomy ability.
		$this->register_ability(
			'acf/register-custom-taxonomy',
			array(
				'label'               => __( 'Register Custom Taxonomy', 'acf' ),
				'description'         => __( 'Register a new taxonomy definition in WordPress (e.g., "Genre", "Color"). This creates the taxonomy schema itself, not individual terms within it. Use the create term abilities to add terms to an existing taxonomy.', 'acf' ),
				'category'            => 'acf-field-management',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'taxonomy'          => array(
							'type'        => 'string',
							'pattern'     => '^[a-z0-9_-]*$',
							'maxLength'   => 32,
							'description' => 'The taxonomy key (slug)',
							'required'    => true,
						),
						'label'             => array(
							'type'        => 'string',
							'description' => 'The singular label for the taxonomy',
							'required'    => true,
						),
						'plural_label'      => array(
							'type'        => 'string',
							'description' => 'The plural label for the taxonomy',
							'required'    => true,
						),
						'description'       => array(
							'type'        => 'string',
							'description' => 'Description of the taxonomy',
							'required'    => false,
						),
						'public'            => array(
							'type'        => 'boolean',
							'description' => 'Whether the taxonomy is public',
							'required'    => false,
						),
						'hierarchical'      => array(
							'type'        => 'boolean',
							'description' => 'Whether the taxonomy is hierarchical (like categories) or flat (like tags)',
							'required'    => false,
						),
						'post_types'        => array(
							'type'        => 'array',
							'description' => 'Array of post types this taxonomy applies to',
							'required'    => false,
							'items'       => array(
								'type' => 'string',
							),
						),
						'show_in_rest'      => array(
							'type'        => 'boolean',
							'description' => 'Whether to show this taxonomy in the REST API (required for AI abilities)',
							'required'    => false,
						),
						'rest_base'         => array(
							'type'        => 'string',
							'description' => 'Custom REST API base path (defaults to taxonomy key)',
							'required'    => false,
						),
						'allow_ai_access'   => array(
							'type'        => 'boolean',
							'description' => 'Whether to allow AI access to this taxonomy',
							'required'    => false,
						),
						'ai_description'    => array(
							'type'        => 'string',
							'description' => 'Description to help AI understand the purpose of this taxonomy',
							'required'    => false,
						),
						'show_ui'           => array(
							'type'        => 'boolean',
							'description' => 'Whether to generate a default UI for managing this taxonomy in the admin',
							'required'    => false,
						),
						'show_admin_column' => array(
							'type'        => 'boolean',
							'description' => 'Whether to display a column for the taxonomy on its post type listing screens',
							'required'    => false,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'  => array( 'type' => 'boolean' ),
						'taxonomy' => array( 'type' => 'object' ),
						'message'  => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'create_custom_taxonomy' ),
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

		// Register abilities for each ACF custom taxonomy that has REST API enabled.
		$this->register_acf_taxonomy_term_abilities();
	}

	/**
	 * Register CRUD abilities for taxonomy terms.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	private function register_acf_taxonomy_term_abilities() {
		$acf_taxonomies = acf_get_acf_taxonomies();

		foreach ( $acf_taxonomies as $acf_taxonomy ) {
			$taxonomy_name = $acf_taxonomy['taxonomy'] ?? '';
			if ( ! $taxonomy_name ) {
				continue;
			}

			// Check if AI access is enabled for this taxonomy.
			if ( empty( $acf_taxonomy['allow_ai_access'] ) || empty( $acf_taxonomy['active'] ) ) {
				continue;
			}

			// Sanitize taxonomy name for feature ID (convert underscores to hyphens, ensure lowercase)
			$sanitized_taxonomy_name = str_replace( '_', '-', strtolower( $taxonomy_name ) );

			// Skip if we can't retrieve the taxonomy object or if it isn't configured with REST API access.
			$taxonomy_object = get_taxonomy( $taxonomy_name );
			if ( ! $taxonomy_object || empty( $taxonomy_object->show_in_rest ) ) {
				continue;
			}

			$rest_base             = acf_get_object_type_rest_base( $taxonomy_object );
			$taxonomy_label        = $taxonomy_object->labels->singular_name ?? $taxonomy_name;
			$taxonomy_label_plural = $taxonomy_object->labels->name ?? $taxonomy_name . 's';

			// Get AI description for enhanced ability descriptions
			$ai_description     = $acf_taxonomy['ai_description'] ?? '';
			$description_suffix = $ai_description ? ' ' . $ai_description : '';

			// Get ACF fields for this taxonomy.
			$acf_fields = $this->get_acf_fields_for_object( 'taxonomy', $taxonomy_name );

			// Get schemas from REST controller.
			$item_schema       = $this->get_rest_item_output_schema( $acf_fields, $taxonomy_name );
			$collection_schema = $this->get_rest_item_output_schema( $acf_fields, $taxonomy_name, 'collection' );

			// Register query/list feature for this taxonomy
			$this->register_ability(
				'acf/' . $sanitized_taxonomy_name . 's',
				array(
					/* translators: %s The plural label for the custom taxonomy. */
					'label'               => sprintf( __( 'Query %s', 'acf' ), $taxonomy_label_plural ),
					/* translators: %s The plural label for the custom taxonomy. */
					'description'         => sprintf( __( 'Get a list of %s terms that match the query parameters.', 'acf' ), strtolower( $taxonomy_label_plural ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'                 => array( 'object', 'null' ),
						'properties'           => array(
							'per_page'   => array(
								'type'    => 'integer',
								'default' => 10,
								'minimum' => 1,
								'maximum' => 100,
							),
							'page'       => array(
								'type'    => 'integer',
								'default' => 1,
								'minimum' => 1,
							),
							'search'     => array(
								'type'        => 'string',
								'default'     => '',
								'description' => 'Search terms by name.',
							),
							'post'       => array(
								'type'        => 'integer',
								'default'     => null,
								'description' => 'Search terms assigned to a specific post.',
							),
							'slug'       => array(
								'type'        => 'array',
								'items'       => array(
									'type' => 'string',
								),
								'description' => 'Search terms with specific slugs.',
							),
							'parent'     => array(
								'type'        => 'integer',
								'description' => 'Filter by parent term ID for hierarchical taxonomies. Use 0 for top-level terms only.',
								'required'    => false,
							),
							'orderby'    => array(
								'type'        => 'string',
								'enum'        => array( 'id', 'name', 'slug', 'description', 'count' ),
								'default'     => 'name',
								'description' => 'Sort collection by term attribute.',
							),
							'order'      => array(
								'type'        => 'string',
								'enum'        => array( 'asc', 'desc' ),
								'default'     => 'asc',
								'description' => 'Order sort attribute ascending or descending.',
							),
							'hide_empty' => array(
								'type'        => 'boolean',
								'default'     => false,
								'description' => 'Whether to hide terms not assigned to any posts.',
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => $collection_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						return $this->execute_rest_request( 'GET', $rest_base, $input );
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

			// Register create ability for this taxonomy
			$this->register_ability(
				'acf/create-' . $sanitized_taxonomy_name,
				array(
					/* translators: %s The singular label for the custom taxonomy. */
					'label'               => sprintf( __( 'Create %s Term', 'acf' ), $taxonomy_label ),
					/* translators: %s The singular label for the custom taxonomy. */
					'description'         => sprintf( __( 'Create a new "%s" term.', 'acf' ), strtolower( $taxonomy_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => $this->get_rest_item_input_schema( $acf_fields, $taxonomy_label, $taxonomy_object->hierarchical ),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						return $this->execute_rest_request( 'POST', $rest_base, $input );
					},
					'permission_callback' => function () use ( $taxonomy_object ) {
						return current_user_can( $taxonomy_object->cap->manage_terms );
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

			// Register view single ability for this taxonomy
			$this->register_ability(
				'acf/view-' . $sanitized_taxonomy_name,
				array(
					/* translators: %s The singular label for the custom taxonomy. */
					'label'               => sprintf( __( 'View a %s Term', 'acf' ), $taxonomy_label ),
					/* translators: %s The singular label for the custom taxonomy. */
					'description'         => sprintf( __( 'Get a %s term by its ID.', 'acf' ), strtolower( $taxonomy_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'type'        => 'integer',
								'description' => sprintf( 'The ID of the %s term to view.', strtolower( $taxonomy_label ) ),
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

			// Register update ability for this taxonomy
			$this->register_ability(
				'acf/update-' . $sanitized_taxonomy_name,
				array(
					/* translators: %s The singular label for the custom taxonomy. */
					'label'               => sprintf( __( 'Update a %s Term', 'acf' ), $taxonomy_label ),
					/* translators: %s The singular label for the custom taxonomy. */
					'description'         => sprintf( __( 'Update a %s term by its ID.', 'acf' ), strtolower( $taxonomy_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => $this->get_rest_item_input_schema( $acf_fields, $taxonomy_label, $taxonomy_object->hierarchical, 'update' ),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						$item_id = $input['id'] ?? null;
						return $this->execute_rest_request( 'PUT', $rest_base, $input, $item_id );
					},
					'permission_callback' => function () use ( $taxonomy_object ) {
						return current_user_can( $taxonomy_object->cap->edit_terms );
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

			// Register delete ability for this taxonomy.
			$this->register_ability(
				'acf/delete-' . $sanitized_taxonomy_name,
				array(
					/* translators: %s The singular label for the custom taxonomy. */
					'label'               => sprintf( __( 'Delete a %s Term', 'acf' ), $taxonomy_label ),
					/* translators: %s The singular label for the custom taxonomy. */
					'description'         => sprintf( __( 'Delete a %s term by its ID.', 'acf' ), strtolower( $taxonomy_label ) ) . $description_suffix,
					'category'            => 'wordpress-content-discovery',
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'type'        => 'integer',
								'description' => sprintf( 'The ID of the %s term to delete.', strtolower( $taxonomy_label ) ),
								'required'    => true,
							),
							'force' => array(
								'type'        => 'boolean',
								'description' => 'Whether to permanently delete the term (required, as terms cannot be trashed)',
								'required'    => false,
								'default'     => false,
							),
						),
					),
					'output_schema'       => $item_schema,
					'execute_callback'    => function ( $input = array() ) use ( $rest_base ) {
						$item_id = $input['id'] ?? null;
						return $this->execute_rest_request( 'DELETE', $rest_base, $input, $item_id );
					},
					'permission_callback' => function () use ( $taxonomy_object ) {
						return current_user_can( $taxonomy_object->cap->delete_terms );
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
	 * Get REST input schema for taxonomy terms.
	 *
	 * @since 6.8.0
	 *
	 * @param array   $acf_fields     ACF fields for this taxonomy.
	 * @param string  $taxonomy_label Taxonomy label for descriptions.
	 * @param boolean $hierarchical   Whether taxonomy is hierarchical.
	 * @param string  $action         Action type ('create' or 'update').
	 * @return array
	 */
	private function get_rest_item_input_schema( array $acf_fields, string $taxonomy_label, bool $hierarchical = false, string $action = 'create' ): array {
		$schema = array(
			'type'       => 'object',
			'properties' => array(),
		);

		if ( 'update' === $action ) {
			$schema['properties']['id'] = array(
				'type'        => 'integer',
				'description' => sprintf( 'The ID of the %s term to update.', strtolower( $taxonomy_label ) ),
				'required'    => true,
			);
		}

		$schema['properties']['name'] = array(
			'type'        => 'string',
			'description' => sprintf( 'The name of the %s term.', strtolower( $taxonomy_label ) ),
			'required'    => 'update' !== $action,
		);

		$schema['properties']['description'] = array(
			'type'        => 'string',
			'description' => sprintf( 'The description of the %s term.', strtolower( $taxonomy_label ) ),
			'required'    => false,
		);

		$schema['properties']['slug'] = array(
			'type'        => 'string',
			'description' => sprintf( 'The slug of the %s term (auto-generated from name if not provided).', strtolower( $taxonomy_label ) ),
			'required'    => false,
		);

		if ( $hierarchical ) {
			$schema['properties']['parent'] = array(
				'type'        => 'integer',
				'description' => 'Parent term ID for hierarchical taxonomies. Use 0 or omit for top-level terms. For child terms, provide the ID of the parent term.',
				'required'    => false,
				'default'     => 0,
			);
		}

		return $this->add_acf_fields_to_schema( $schema, $acf_fields );
	}

	/**
	 * Get REST output schema for taxonomy terms.
	 *
	 * @since 6.8.0
	 *
	 * @param array  $acf_fields ACF fields for this taxonomy.
	 * @param string $taxonomy   Taxonomy name.
	 * @param string $type       Schema type ('item' or 'collection').
	 * @return array|null
	 */
	private function get_rest_item_output_schema( array $acf_fields, string $taxonomy, string $type = 'item' ) {
		$taxonomy_object = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_object ) {
			return null;
		}

		$controller = $taxonomy_object->get_rest_controller();
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
	 * Callback for the "acf/get-custom-taxonomies" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input Input args (unused).
	 * @return array
	 */
	public function get_custom_taxonomies( $input ) {
		unset( $input ); // Not used, but required by interface.

		$custom_taxonomies = array();

		// Get ACF custom taxonomies.
		$acf_taxonomies = acf_get_acf_taxonomies();

		foreach ( $acf_taxonomies as $acf_taxonomy ) {
			$taxonomy_name = $acf_taxonomy['taxonomy'] ?? '';
			if ( ! $taxonomy_name ) {
				continue;
			}

			if ( empty( $acf_taxonomy['active'] ) || empty( $acf_taxonomy['allow_ai_access'] ) ) {
				continue;
			}

			$taxonomy_object = get_taxonomy( $taxonomy_name );
			if ( $taxonomy_object ) {
				$taxonomy_data = array(
					'taxonomy'     => $taxonomy_name,
					'label'        => $taxonomy_object->label,
					'labels'       => (array) $taxonomy_object->labels,
					'description'  => $taxonomy_object->description,
					'public'       => $taxonomy_object->public,
					'hierarchical' => $taxonomy_object->hierarchical,
					'object_type'  => $taxonomy_object->object_type,
					'acf_settings' => $acf_taxonomy,
				);

				// Add ACF field groups information
				$acf_fields = $this->get_acf_fields_for_object( 'taxonomy', $taxonomy_name );
				if ( ! empty( $acf_fields ) ) {
					$taxonomy_data['acf_field_groups'] = $acf_fields;
				}

				$custom_taxonomies[] = $taxonomy_data;
			}
		}

		$count = count( $custom_taxonomies );

		return array(
			'custom_taxonomies' => $custom_taxonomies,
			'count'             => $count,
			'message'           => sprintf(
				/* translators: %d: Number of ACF custom taxonomies */
				_n( 'Found %d ACF custom taxonomy', 'Found %d ACF custom taxonomies', $count, 'acf' ),
				$count
			),
		);
	}

	/**
	 * Callback for the "acf/register-custom-taxonomy" ability.
	 *
	 * @since 6.8.0
	 *
	 * @param array $input Input args.
	 * @return array|WP_Error
	 */
	public function create_custom_taxonomy( $input ) {
		// Required parameters
		$taxonomy     = sanitize_key( $input['taxonomy'] );
		$label        = sanitize_text_field( $input['label'] );
		$plural_label = sanitize_text_field( $input['plural_label'] );

		// Basic optional parameters
		$description  = sanitize_text_field( $input['description'] ?? '' );
		$public       = $input['public'] ?? true;
		$hierarchical = $input['hierarchical'] ?? false;
		$post_types   = array_map( 'sanitize_key', $input['post_types'] ?? array( 'post' ) );

		// REST API settings
		$show_in_rest = $input['show_in_rest'] ?? true;
		$rest_base    = $input['rest_base'] ?? '';

		// AI settings
		$allow_ai_access = $input['allow_ai_access'] ?? true;
		$ai_description  = $input['ai_description'] ?? '';

		// UI settings
		$show_ui           = $input['show_ui'] ?? true;
		$show_admin_column = $input['show_admin_column'] ?? false;

		// Check if taxonomy already exists.
		if ( taxonomy_exists( $taxonomy ) ) {
			return new WP_Error(
				'taxonomy_exists',
				__( 'A taxonomy with this key already exists', 'acf' ),
				array( 'status' => 400 )
			);
		}

		// Use ACF's method to create the taxonomy.
		$taxonomy_data = array(
			'key'               => uniqid( 'taxonomy_' ),
			'taxonomy'          => $taxonomy,
			'title'             => $plural_label,
			'labels'            => wp_parse_args(
				array(
					'name'          => $plural_label,
					'singular_name' => $label,
				),
				acf_get_internal_post_type_instance( 'acf-taxonomy' )->get_settings_array()['labels']
			),
			'description'       => $description,
			'public'            => $public ? 1 : 0,
			'hierarchical'      => $hierarchical ? 1 : 0,
			'object_type'       => $post_types,
			'active'            => 1,
			// REST API settings
			'show_in_rest'      => $show_in_rest ? 1 : 0,
			// AI settings
			'allow_ai_access'   => $allow_ai_access ? 1 : 0,
			// UI settings
			'show_ui'           => $show_ui ? 1 : 0,
			'show_admin_column' => $show_admin_column ? 1 : 0,
		);

		// Add optional settings only if provided
		if ( ! empty( $rest_base ) ) {
			$taxonomy_data['rest_base'] = sanitize_text_field( $rest_base );
		}

		if ( ! empty( $ai_description ) ) {
			$taxonomy_data['ai_description'] = sanitize_text_field( $ai_description );
		}

		$result = acf_import_taxonomy( $taxonomy_data );

		if ( empty( $result['ID'] ) || ! is_int( $result['ID'] ) || ! taxonomy_exists( $result['taxonomy'] ) ) {
			return new WP_Error(
				'taxonomy_creation_failed',
				__( 'Failed to create the custom taxonomy', 'acf' )
			);
		}

		return array(
			'success'  => true,
			'taxonomy' => $result,
			'message'  => __( 'ACF custom taxonomy created successfully', 'acf' ),
		);
	}
}
