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

use WP_REST_Request;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The ACF Abilities API integration.
 *
 * Extends the WordPress Abilities API to expose field groups, post types,
 * taxonomies, and options pages when the "Allow AI Access" setting is enabled.
 */
class Abilities {

	/**
	 * Array of registered ability group instances
	 *
	 * @var array
	 */
	private array $ability_groups = array();

	/**
	 * Constructs the class.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the Abilities API integration.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function init() {
		// Register ability group classes.
		$this->register_ability_group( 'field_group', FieldGroup::class );
		$this->register_ability_group( 'post_type', PostType::class );
		$this->register_ability_group( 'taxonomy', Taxonomy::class );

		// Register categories (v0.3.0+ requirement).
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );

		// Register abilities.
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );

		// Fix for WordPress 6.9 Abilities API bug: parse JSON from query parameters.
		add_filter( 'rest_request_before_callbacks', array( $this, 'parse_abilities_json_input' ), 10, 3 );
	}

	/**
	 * Register an ability group class.
	 *
	 * @since 6.8.0
	 *
	 * @param string $key        Unique key for this ability group.
	 * @param string $class_name Fully qualified class name.
	 * @param array  $args       Optional constructor arguments.
	 * @return void
	 */
	private function register_ability_group( $key, $class_name, $args = array() ) {
		if ( ! class_exists( $class_name ) ) {
			return;
		}

		// Instantiate the class with any provided arguments.
		if ( ! empty( $args ) ) {
			$this->ability_groups[ $key ] = new $class_name( ...$args );
		} else {
			$this->ability_groups[ $key ] = new $class_name();
		}
	}

	/**
	 * Get an ability group instance by key
	 *
	 * @since 6.8.0
	 *
	 * @param string $key The ability group key.
	 * @return object|null The ability group instance or null if not found.
	 */
	private function get_ability_group( $key ) {
		return $this->ability_groups[ $key ] ?? null;
	}

	/**
	 * Register Ability Categories
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function register_categories() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		// ACF Field Management category.
		wp_register_ability_category(
			'acf-field-management',
			array(
				'label'       => __( 'ACF Field Management', 'acf' ),
				'description' => __( 'Abilities for managing Advanced Custom Fields field groups and field data.', 'acf' ),
			)
		);

		// WordPress Content Discovery category.
		wp_register_ability_category(
			'wordpress-content-discovery',
			array(
				'label'       => __( 'WordPress Content Discovery', 'acf' ),
				'description' => __( 'Abilities for discovering WordPress content types, taxonomies, and structure.', 'acf' ),
			)
		);
	}

	/**
	 * Register Abilities for ACF
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		// Register abilities from all registered ability groups.
		foreach ( $this->ability_groups as $ability_group ) {
			if ( method_exists( $ability_group, 'register_abilities' ) ) {
				$ability_group->register_abilities();
			}
		}
	}

	/**
	 * Parse JSON input from query parameters for Abilities API
	 *
	 * WordPress 6.9's Abilities API REST controller doesn't parse JSON strings
	 * from query parameters in GET requests. This filter fixes that by detecting
	 * JSON strings in the 'input' parameter and parsing them into objects/arrays.
	 *
	 * @since 6.8.0
	 *
	 * @param mixed           $response Response object.
	 * @param array           $handler  Route handler info.
	 * @param WP_REST_Request $request  Request object.
	 * @return mixed
	 */
	public function parse_abilities_json_input( $response, $handler, $request ) {
		// Only process ACF abilities.
		$route = $request->get_route();
		if ( strpos( $route, '/wp-abilities/v1/abilities/acf/' ) !== 0 ) {
			return $response;
		}

		// Only process GET and DELETE requests (POST uses JSON body which is already parsed).
		if ( ! in_array( $request->get_method(), array( 'GET', 'DELETE' ), true ) ) {
			return $response;
		}

		// Get the input query parameter.
		$input = $request->get_param( 'input' );

		// If input is a string that looks like JSON, try to parse it.
		if ( is_string( $input ) && ! empty( $input ) ) {
			$first_char = substr( trim( $input ), 0, 1 );
			// Check if it starts with { or [ (JSON object or array).
			if ( in_array( $first_char, array( '{', '[' ), true ) ) {
				$parsed = json_decode( $input, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					// Successfully parsed JSON - update the request parameter.
					$request->set_param( 'input', $parsed );
				}
			}
		}

		return $response;
	}
}
