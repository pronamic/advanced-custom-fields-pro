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

namespace ACF\Site_Health;

use WP_Error;
use WP_REST_Request;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * AI Usage
 *
 * Logs information about ACF AI/Abilities usage for the ACF Site Health report.
 * Measures opt-in, potential (AI-ready objects), discovery (browsing), and utility (execution).
 */
class AI_Usage {

	/**
	 * An instance of the ACF Site_Health class.
	 *
	 * @var Site_Health
	 */
	private Site_Health $site_health;

	/**
	 * Constructs the class.
	 *
	 * @since 6.8.0
	 *
	 * @param Site_Health $site_health An instance of Site_Health.
	 * @return void
	 */
	public function __construct( Site_Health $site_health ) {
		$this->site_health = $site_health;

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initializes the class on init if the ACF Abilities API is available.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function init() {
		// Only hook if AI is enabled and Abilities API exists.
		if ( ! $this->is_acf_abilities_api_available() ) {
			return;
		}

		// Execution logging - hook after ability execution.
		add_action( 'wp_after_execute_ability', array( $this, 'log_execution' ), 10, 3 );
	}

	/**
	 * Checks if ACF AI and the Abilities API are enabled.
	 *
	 * @since 6.8.0
	 *
	 * @return boolean
	 */
	private function is_acf_abilities_api_available(): bool {
		return acf_get_setting( 'enable_acf_ai' ) && function_exists( 'wp_register_ability' );
	}

	/**
	 * Log execution events (when agents execute ACF abilities).
	 *
	 * Hooks into wp_after_execute_ability to log successful ability executions.
	 *
	 * @since 6.8.0
	 *
	 * @param string $ability_name The namespaced ability name.
	 * @param mixed  $input        The input data passed to the ability.
	 * @param mixed  $result       The result returned by the ability.
	 * @return void
	 */
	public function log_execution( string $ability_name, $input, $result ) {
		// Only log ACF abilities.
		if ( strpos( $ability_name, 'acf/' ) !== 0 ) {
			return;
		}

		$is_error = $result instanceof WP_Error;

		$this->increment_execution_count( $ability_name, $is_error );
	}

	/**
	 * Increment execution counts.
	 *
	 * @since 6.8.0
	 *
	 * @param string  $ability_name The ability that was executed.
	 * @param boolean $is_error     Whether the execution resulted in an error.
	 * @return boolean Success status.
	 */
	private function increment_execution_count( string $ability_name, bool $is_error ): bool {
		$data = $this->site_health->get_site_health();

		if ( ! isset( $data['ai_usage'] ) ) {
			$data['ai_usage'] = $this->get_default_usage_structure();
		}

		// Increment total executions.
		++$data['ai_usage']['total_executions'];
		$data['ai_usage']['last_execution_at'] = time();

		// Increment per-ability count.
		if ( ! isset( $data['ai_usage']['executions_by_ability'][ $ability_name ] ) ) {
			$data['ai_usage']['executions_by_ability'][ $ability_name ] = 0;
		}
		++$data['ai_usage']['executions_by_ability'][ $ability_name ];

		if ( $is_error ) {
			++$data['ai_usage']['error_count'];
		}

		return $this->site_health->update_site_health( $data );
	}

	/**
	 * Get the default log data structure.
	 *
	 * @since 6.8.0
	 *
	 * @return array
	 */
	private function get_default_usage_structure(): array {
		return array(
			'total_executions'      => 0,
			'error_count'           => 0,
			'last_execution_at'     => null,
			'executions_by_ability' => array(),
		);
	}

	/**
	 * Get AI-ready object counts for Site Health display.
	 *
	 * @since 6.8.0
	 *
	 * @param array $field_groups An array of ACF field groups.
	 * @param array $post_types   An array of ACF post types.
	 * @param array $taxonomies   An array of ACF taxonomies.
	 * @return array Counts of AI-ready objects by type.
	 */
	public function get_ai_ready_counts( $field_groups, $post_types, $taxonomies ): array {
		$counts = array(
			'field_groups' => 0,
			'post_types'   => 0,
			'taxonomies'   => 0,
		);

		// Count AI-ready field groups.
		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['allow_ai_access'] ) && ! empty( $field_group['active'] ) ) {
				++$counts['field_groups'];
			}
		}

		// Count AI-ready post types.
		foreach ( $post_types as $post_type ) {
			if ( ! empty( $post_type['allow_ai_access'] ) && ! empty( $post_type['active'] ) ) {
				++$counts['post_types'];
			}
		}

		// Count AI-ready taxonomies.
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy['allow_ai_access'] ) && ! empty( $taxonomy['active'] ) ) {
				++$counts['taxonomies'];
			}
		}

		return $counts;
	}

	/**
	 * Get the usage metrics for Site Health display.
	 *
	 * @since 6.8.0
	 *
	 * @return array The usage metrics.
	 */
	public function get_usage_metrics(): array {
		$site_health = $this->site_health->get_site_health();

		if ( ! isset( $site_health['ai_usage'] ) ) {
			return array(
				'total_discovery_hits' => 0,
				'total_executions'     => 0,
				'error_count'          => 0,
			);
		}

		$usage = $site_health['ai_usage'];

		return array(
			'total_discovery_hits'  => $usage['total_discovery_hits'] ?? 0,
			'total_executions'      => $usage['total_executions'] ?? 0,
			'error_count'           => $usage['error_count'] ?? 0,
			'executions_by_ability' => $usage['executions_by_ability'] ?? array(),
		);
	}
}
