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

namespace ACF\AI;

use ACF\AI\Abilities\Abilities;
use ACF\AI\GEO\GEO;

/**
 * Initializes the ACF AI functionality if enabled.
 */
class AI {

	/**
	 * Constructs the AI class.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'initialize' ) );
	}

	/**
	 * Initializes the AI functionality.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function initialize() {
		if ( $this->is_geo_enabled() ) {
			new GEO();
		}

		if ( $this->is_ai_enabled() ) {
			new Abilities();
			$this->add_admin_ui_hooks();
		}
	}

	/**
	 * Checks if AI functionality is enabled.
	 *
	 * @since 6.8.0
	 *
	 * @return boolean
	 */
	public function is_ai_enabled(): bool {
		return (bool) acf_get_setting( 'enable_acf_ai' );
	}

	/**
	 * Checks if GEO functionality is enabled.
	 *
	 * @since 6.8.0
	 *
	 * @return boolean
	 */
	public function is_geo_enabled(): bool {
		return (bool) acf_get_setting( 'enable_schema' );
	}

	/**
	 * Adds admin UI hooks.
	 *
	 * @since 6.8.0
	 *
	 * @return void
	 */
	public function add_admin_ui_hooks() {
		// Add ACF AI tab to field groups.
		add_filter( 'acf/field_group/additional_group_settings_tabs', array( $this, 'add_acf_ai_tab' ) );
		add_action( 'acf/field_group/render_group_settings_tab/acf-ai', array( $this, 'render_acf_ai_tab' ) );

		// Add ACF AI tab to post types.
		add_filter( 'acf/post_type/additional_settings_tabs', array( $this, 'add_acf_ai_tab' ) );
		add_action( 'acf/post_type/render_settings_tab/acf-ai', array( $this, 'render_acf_ai_tab' ) );

		// Add ACF AI tab to taxonomies.
		add_filter( 'acf/taxonomy/additional_settings_tabs', array( $this, 'add_acf_ai_tab' ) );
		add_action( 'acf/taxonomy/render_settings_tab/acf-ai', array( $this, 'render_acf_ai_tab' ) );
	}

	/**
	 * Registers the AI tab in various contexts.
	 *
	 * @since 6.8.0
	 *
	 * @param array $tabs The existing tabs array.
	 * @return array
	 */
	public function add_acf_ai_tab( array $tabs ): array {
		$tabs['acf-ai'] = __( 'ACF AI', 'acf' );
		return $tabs;
	}

	/**
	 * Renders the ACF AI tab in various contexts.
	 *
	 * @since 6.8.0
	 *
	 * @param array $item The field group, post type, taxonomy, etc. being edited.
	 * @return void
	 */
	public function render_acf_ai_tab( array $item ) {
		if ( empty( $item['key'] ) ) {
			return;
		}

		$item_type = acf_determine_internal_post_type( $item['key'] );
		if ( ! $item_type ) {
			return;
		}

		$item_type           = str_replace( '-', '_', $item_type );
		$post_id             = (int) acf_request_arg( 'post', 0 );
		$allow_ai_access_val = ! empty( $item['allow_ai_access'] ) ? 1 : 0;

		// If this is a new item, default to allowing AI access.
		if ( ! $post_id ) {
			$allow_ai_access_val = 1;
		}

		$allow_access_label   = __( 'Allow AI Access', 'acf' );
		$allow_access_desc    = __( 'Allow AI systems to access and modify this content through the WordPress Abilities API.', 'acf' );
		$ai_description_label = __( 'AI Description', 'acf' );
		$ai_description_desc  = __( 'Provide a description that will help AI systems understand the purpose and how to use this effectively.', 'acf' );

		if ( 'acf_post_type' === $item_type ) {
			$allow_access_label   = __( 'Allow AI Access to Post Content', 'acf' );
			$allow_access_desc    = __( 'When enabled, AI models can access and interact with the content of posts in this post type using supported integrations. This feature uses the WordPress Abilities API.', 'acf' );
			$ai_description_label = __( 'AI Guidance for Post Type', 'acf' );
			$ai_description_desc  = __( "Add a short explanation of what this post type is for. The clearer your description, the better the AI's output will be.", 'acf' );
		}

		if ( 'acf_taxonomy' === $item_type ) {
			$allow_access_label   = __( 'Allow AI Access to Taxonomy Terms', 'acf' );
			$allow_access_desc    = __( 'When enabled, AI models can access and interact with the terms in this taxonomy using supported integrations. This feature uses the WordPress Abilities API.', 'acf' );
			$ai_description_label = __( 'AI Guidance for Taxonomy', 'acf' );
			$ai_description_desc  = __( "Add a short explanation of what this taxonomy is for. The clearer your description, the better the AI's output will be.", 'acf' );
		}

		if ( 'acf_field_group' === $item_type ) {
			$allow_access_label   = __( 'Allow AI Access to Field Data', 'acf' );
			$allow_access_desc    = __( 'When enabled, AI models can access and interact with the fields in this group using supported integrations. This feature uses the WordPress Abilities API.', 'acf' );
			$ai_description_label = __( 'AI Context Description', 'acf' );
			$ai_description_desc  = __( "Add a short explanation of what this field group is for. The clearer your description, the better the AI's output will be.", 'acf' );
		}

		acf_render_field_wrap(
			array(
				'type'         => 'true_false',
				'name'         => 'allow_ai_access',
				'key'          => 'allow_ai_access',
				'prefix'       => $item_type,
				'value'        => $allow_ai_access_val,
				'label'        => $allow_access_label,
				'instructions' => $allow_access_desc,
				'ui'           => true,
				'default'      => 1,
			)
		);

		acf_render_field_wrap(
			array(
				'type'         => 'textarea',
				'name'         => 'ai_description',
				'key'          => 'ai_description',
				'prefix'       => $item_type,
				'value'        => $item['ai_description'] ?? '',
				'label'        => $ai_description_label,
				'instructions' => $ai_description_desc,
				'rows'         => 4,
				'conditions'   => array(
					array(
						'field'    => 'allow_ai_access',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'div',
			'field'
		);
	}
}
