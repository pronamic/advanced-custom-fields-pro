<?php
/**
 * The ACF Blocks PHP code.
 *
 * @package ACF
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register store.
acf_register_store( 'block-types' );
acf_register_store( 'block-cache' );

// Register block.json support handlers.
add_filter( 'block_type_metadata', 'acf_add_block_namespace' );
add_filter( 'block_type_metadata_settings', 'acf_handle_json_block_registration', 99, 2 );
add_action( 'acf_block_render_template', 'acf_block_render_template', 10, 6 );

/**
 * Prefix block names for ACF blocks registered through block.json
 *
 * @since 6.0.0
 *
 * @param array $metadata The block metadata array.
 * @return array The original array with a prefixed block name if it's an ACF block.
 */
function acf_add_block_namespace( $metadata ) {
	if ( acf_is_acf_block_json( $metadata ) ) {
		// If the block doesn't already have a namespace, append ACF's.
		if ( strpos( $metadata['name'], '/' ) === false ) {
			$metadata['name'] = 'acf/' . acf_slugify( $metadata['name'] );
		}
	}
	return $metadata;
}

/**
 * Handle an ACF block registered through block.json
 *
 * @since 6.0.0
 *
 * @param array $settings The compiled block settings.
 * @param array $metadata The raw json metadata.
 *
 * @return array Block registration settings with ACF required additions.
 */
function acf_handle_json_block_registration( $settings, $metadata ) {
	if ( ! acf_is_acf_block_json( $metadata ) ) {
		return $settings;
	}

	// Setup ACF defaults.
	$settings = wp_parse_args(
		$settings,
		array(
			'render_template'   => false,
			'render_callback'   => false,
			'enqueue_style'     => false,
			'enqueue_script'    => false,
			'enqueue_assets'    => false,
			'post_types'        => array(),
			'uses_context'      => array(),
			'supports'          => array(),
			'attributes'        => array(),
			'acf_block_version' => 2,
			'api_version'       => 2,
		)
	);

	// Add user provided attributes to ACF's required defaults.
	$settings['attributes'] = wp_parse_args(
		acf_get_block_type_default_attributes( $metadata ),
		$settings['attributes']
	);

	// Add default ACF 'supports' settings.
	$settings['supports'] = wp_parse_args(
		$settings['supports'],
		array(
			'align' => true,
			'html'  => false,
			'mode'  => true,
			'jsx'   => true,
		)
	);

	// Add default ACF 'uses_context' settings.
	$settings['uses_context'] = array_values(
		array_unique(
			array_merge(
				$settings['uses_context'],
				array(
					'postId',
					'postType',
				)
			)
		)
	);

	// Map custom ACF properties from the ACF key, with localization.
	$property_mappings = array(
		'renderCallback' => 'render_callback',
		'renderTemplate' => 'render_template',
		'mode'           => 'mode',
		'blockVersion'   => 'acf_block_version',
		'postTypes'      => 'post_types',
	);
	$textdomain        = ! empty( $metadata['textdomain'] ) ? $metadata['textdomain'] : 'acf';
	$i18n_schema       = get_block_metadata_i18n_schema();

	foreach ( $property_mappings as $key => $mapped_key ) {
		if ( isset( $metadata['acf'][ $key ] ) ) {
			unset( $settings[ $key ] );
			$settings[ $mapped_key ] = $metadata['acf'][ $key ];
			if ( $textdomain && isset( $i18n_schema->$key ) ) {
				$settings[ $mapped_key ] = translate_settings_using_i18n_schema( $i18n_schema->$key, $settings[ $key ], $textdomain );
			}
		}
	}

	// Add the block name and registration path to settings.
	$settings['name'] = $metadata['name'];
	$settings['path'] = dirname( $metadata['file'] );

	acf_get_store( 'block-types' )->set( $metadata['name'], $settings );
	add_action( 'enqueue_block_editor_assets', 'acf_enqueue_block_assets' );

	// Ensure our render callback is used.
	$settings['render_callback'] = 'acf_render_block_callback';

	return $settings;
}

/**
 * Check if a block.json block is an ACF block.
 *
 * @since 6.0.0
 *
 * @param array $metadata The raw block metadata array.
 * @return bool
 */
function acf_is_acf_block_json( $metadata ) {
	return ( isset( $metadata['acf'] ) && $metadata['acf'] );
}


/**
 * Registers a block type.
 *
 * @date    18/2/19
 * @since   5.8.0
 *
 * @param   array $block The block settings.
 * @return  (array|false)
 */
function acf_register_block_type( $block ) {
	// Validate block type settings.
	$block = acf_validate_block_type( $block );

	/**
	 * Filters the arguments for registering a block type.
	 *
	 * @since   5.8.9
	 *
	 * @param   array $block The array of arguments for registering a block type.
	 */
	$block = apply_filters( 'acf/register_block_type_args', $block );

	// Require name.
	if ( ! $block['name'] ) {
		$message = __( 'Block type name is required.', 'acf' );
		_doing_it_wrong( __FUNCTION__, $message, '5.8.0' ); //phpcs:ignore -- escape not required.
		return false;
	}

	// Bail early if already exists.
	if ( acf_has_block_type( $block['name'] ) ) {
		/* translators: The name of the block type */
		$message = sprintf( __( 'Block type "%s" is already registered.', 'acf' ), $block['name'] );
		_doing_it_wrong( __FUNCTION__, $message, '5.8.0' ); //phpcs:ignore -- escape not required.
		return false;
	}

	// Set ACF required attributes.
	$block['attributes'] = acf_get_block_type_default_attributes( $block );
	if ( ! isset( $block['api_version'] ) ) {
		$block['api_version'] = 2;
	}
	if ( ! isset( $block['acf_block_version'] ) ) {
		$block['acf_block_version'] = 1;
	}

	// Add to storage.
	acf_get_store( 'block-types' )->set( $block['name'], $block );

	// Overwrite callback for WordPress registration.
	$block['render_callback'] = 'acf_render_block_callback';

	// Register block type in WP.
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(
			$block['name'],
			$block
		);
	}

	// Register action.
	add_action( 'enqueue_block_editor_assets', 'acf_enqueue_block_assets' );

	// Return block.
	return $block;
}

/**
 * See acf_register_block_type().
 *
 * @date    18/2/19
 * @since   5.7.12
 *
 * @param   array $block The block settings.
 * @return  (array|false)
 */
function acf_register_block( $block ) {
	return acf_register_block_type( $block );
}

/**
 * Returns true if a block type exists for the given name.
 *
 * @since   5.7.12
 *
 * @param   string $name The block type name.
 * @return  bool
 */
function acf_has_block_type( $name ) {
	return acf_get_store( 'block-types' )->has( $name );
}

/**
 * Returns an array of all registered block types.
 *
 * @since   5.7.12
 *
 * @return  array
 */
function acf_get_block_types() {
	return acf_get_store( 'block-types' )->get();
}

/**
 * Returns a block type for the given name.
 *
 * @since   5.7.12
 *
 * @param   string $name The block type name.
 * @return  (array|null)
 */
function acf_get_block_type( $name ) {
	return acf_get_store( 'block-types' )->get( $name );
}

/**
 * Removes a block type for the given name.
 *
 * @since   5.7.12
 *
 * @param   string $name The block type name.
 * @return  void
 */
function acf_remove_block_type( $name ) {
	acf_get_store( 'block-types' )->remove( $name );
}

/**
 * Returns an array of default attribute settings for a block type.
 *
 * @date    19/11/18
 * @since   5.8.0
 *
 * @param array $block_type A block configuration array.
 * @return array
 */
function acf_get_block_type_default_attributes( $block_type ) {
	$attributes = array(
		'name'  => array(
			'type'    => 'string',
			'default' => '',
		),
		'data'  => array(
			'type'    => 'object',
			'default' => array(),
		),
		'align' => array(
			'type'    => 'string',
			'default' => '',
		),
		'mode'  => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	foreach ( acf_get_block_back_compat_attribute_key_array() as $new => $old ) {
		if ( isset( $block_type['supports'][ $old ] ) ) {
			$block_type['supports'][ $new ] = $block_type['supports'][ $old ];
			unset( $block_type['supports'][ $old ] );
		}
	}

	if ( ! empty( $block_type['supports']['alignText'] ) ) {
		$attributes['alignText'] = array(
			'type'    => 'string',
			'default' => '',
		);
	}
	if ( ! empty( $block_type['supports']['alignContent'] ) ) {
		$attributes['alignContent'] = array(
			'type'    => 'string',
			'default' => '',
		);
	}
	if ( ! empty( $block_type['supports']['fullHeight'] ) ) {
		$attributes['fullHeight'] = array(
			'type'    => 'boolean',
			'default' => '',
		);
	}

	// For each of ACF's block attributes, check if the user's block attributes contains a default value we should use.
	if ( isset( $block_type['attributes'] ) && is_array( $block_type['attributes'] ) ) {
		foreach ( array_keys( $attributes ) as $key ) {
			if ( isset( $block_type['attributes'][ $key ] ) && is_array( $block_type['attributes'][ $key ] ) && isset( $block_type['attributes'][ $key ]['default'] ) ) {
				$attributes[ $key ]['default'] = $block_type['attributes'][ $key ]['default'];
			}
		}
	}

	return $attributes;
}

/**
 * Validates a block type ensuring all settings exist.
 *
 * @since   5.8.0
 *
 * @param   array $block The block settings.
 * @return  array
 */
function acf_validate_block_type( $block ) {

	// Add default settings.
	$block = wp_parse_args(
		$block,
		array(
			'name'            => '',
			'title'           => '',
			'description'     => '',
			'category'        => 'common',
			'icon'            => '',
			'mode'            => 'preview',
			'keywords'        => array(),
			'supports'        => array(),
			'post_types'      => array(),
			'uses_context'    => array(),
			'render_template' => false,
			'render_callback' => false,
			'enqueue_style'   => false,
			'enqueue_script'  => false,
			'enqueue_assets'  => false,
		)
	);

	// Generate name with prefix.
	if ( $block['name'] ) {
		$block['name'] = 'acf/' . acf_slugify( $block['name'] );
	}

	// Add default 'supports' settings.
	$block['supports'] = wp_parse_args(
		$block['supports'],
		array(
			'align' => true,
			'html'  => false,
			'mode'  => true,
		)
	);

	// Add default 'uses_context' settings.
	$block['uses_context'] = wp_parse_args(
		$block['uses_context'],
		array(
			'postId',
			'postType',
		)
	);

	// Correct "Experimental" flags.
	if ( isset( $block['supports']['__experimental_jsx'] ) ) {
		$block['supports']['jsx'] = $block['supports']['__experimental_jsx'];
	}

	// Return block.
	return $block;
}

/**
 * Prepares a block for use in render_callback by merging in all settings and attributes.
 *
 * @since   5.8.0
 *
 * @param   array $block The block props.
 * @return  array
 */
function acf_prepare_block( $block ) {

	// Bail early if no name.
	if ( ! isset( $block['name'] ) ) {
		return false;
	}

	// Get block type and return false if doesn't exist.
	$block_type = acf_get_block_type( $block['name'] );
	if ( ! $block_type ) {
		return false;
	}

	// Generate default attributes.
	$attributes = array();
	foreach ( acf_get_block_type_default_attributes( $block_type ) as $k => $v ) {
		$attributes[ $k ] = $v['default'];
	}

	// Merge together arrays in order of least to most specific.
	$block = array_merge( $block_type, $attributes, $block );

	// Add backward compatibility attributes.
	$block = acf_add_back_compat_attributes( $block );

	// Return block.
	return $block;
}

/**
 * Add backwards compatible attribute values.
 *
 * @since 6.0.0
 *
 * @param array $block The original block.
 * @return array Modified block array with backwards compatibility attributes.
 */
function acf_add_back_compat_attributes( $block ) {
	foreach ( acf_get_block_back_compat_attribute_key_array() as $new => $old ) {
		if ( ! empty( $block[ $new ] ) || ( isset( $block[ $new ] ) && ! isset( $block[ $old ] ) ) ) {
			$block[ $old ] = $block[ $new ];
		}
	}

	return $block;
}

/**
 * Get back compat new values and old values.
 *
 * @since 6.0.0
 *
 * @return array back compat key array.
 */
function acf_get_block_back_compat_attribute_key_array() {
	return array(
		'fullHeight'   => 'full_height',
		'alignText'    => 'align_text',
		'alignContent' => 'align_content',
	);
}


/**
 * The render callback for all ACF blocks.
 *
 * @date    28/10/20
 * @since   5.9.2
 *
 * @param   array    $attributes The block attributes.
 * @param   string   $content The block content.
 * @param   WP_Block $wp_block The block instance (since WP 5.5).
 * @return  string The block HTML.
 */
function acf_render_block_callback( $attributes, $content = '', $wp_block = null ) {
	$is_preview = false;
	$post_id    = get_the_ID();

	// Set preview flag to true when rendering for the block editor.
	if ( is_admin() && acf_is_block_editor() ) {
		$is_preview = true;
	}

	// If ACF's block save method hasn't been called yet, try to initialize a default block.
	if ( empty( $attributes['name'] ) && ! empty( $wp_block->name ) ) {
		$attributes['name'] = $wp_block->name;
	}

	// Return rendered block HTML.
	return acf_rendered_block( $attributes, $content, $is_preview, $post_id, $wp_block );
}

/**
 * Returns the rendered block HTML.
 *
 * @date    28/2/19
 * @since   5.7.13
 *
 * @param   array    $attributes The block attributes.
 * @param   string   $content The block content.
 * @param   bool     $is_preview Whether or not the block is being rendered for editing preview.
 * @param   int      $post_id The current post being edited or viewed.
 * @param   WP_Block $wp_block The block instance (since WP 5.5).
 * @param   array    $context The block context array.
 * @return  string   The block HTML.
 */
function acf_rendered_block( $attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null, $context = false ) {
	$mode = isset( $attributes['mode'] ) ? $attributes['mode'] : 'auto';
	$form = ( 'edit' === $mode && $is_preview );

	// If context is available from the WP_Block class object and we have no context of our own, use that.
	if ( empty( $context ) && ! empty( $wp_block->context ) ) {
		$context = $wp_block->context;
	}

	// Check if we need to generate a block ID.
	$attributes['id'] = acf_get_block_id( $attributes, $context );

	// Check if we've already got a cache of this block ID and return it to save rendering if we're in the backend.
	if ( $is_preview ) {
		$cached_block = acf_get_store( 'block-cache' )->get( $attributes['id'] );
		if ( $cached_block ) {
			if ( $form ) {
				if ( $cached_block['form'] ) {
					return $cached_block['html'];
				}
			} else {
				if ( ! $cached_block['form'] ) {
					return $cached_block['html'];
				}
			}
		}
	}

	ob_start();

	if ( $form ) {
		// Load the block form since we're in edit mode.

		// Set flag for post REST cleanup of media enqueue count during preloads.
		acf_set_data( 'acf_did_render_block_form', true );

		$block = acf_prepare_block( $attributes );
		acf_setup_meta( $block['data'], $block['id'], true );
		$fields = acf_get_block_fields( $block );
		if ( $fields ) {
			acf_prefix_fields( $fields, "acf-{$block['id']}" );

			echo '<div class="acf-block-fields acf-fields">';
			acf_render_fields( $fields, $block['id'], 'div', 'field' );
			echo '</div>';
		} else {
			echo acf_get_empty_block_form_html( $attributes['name'] ); //phpcs:ignore -- escaped in function.
		}
	} else {
		// Capture block render output.
		acf_render_block( $attributes, $content, $is_preview, $post_id, $wp_block, $context );
	}

	$html = ob_get_clean();
	$html = is_string( $html ) ? $html : '';

	// Replace <InnerBlocks /> placeholder on front-end, or if we're rendering an ACF block inside another ACF block template.
	if ( ! $is_preview || doing_action( 'acf_block_render_template' ) ) {
		// Escape "$" character to avoid "capture group" interpretation.
		$content = str_replace( '$', '\$', $content );

		// Wrap content in our acf-inner-container wrapper if necessary.
		if ( $wp_block && $wp_block->block_type->acf_block_version > 1 && apply_filters( 'acf/blocks/wrap_frontend_innerblocks', true, $attributes['name'] ) ) {
			// Check for a class (or className) provided in the template to become the InnerBlocks wrapper class.
			$matches = array();
			if ( preg_match( '/<InnerBlocks(?:[^<]+?)(?:class|className)=(?:["\']\W+\s*(?:\w+)\()?["\']([^\'"]+)[\'"]/', $html, $matches ) ) {
				$class = isset( $matches[1] ) ? $matches[1] : 'acf-innerblocks-container';
			} else {
				$class = 'acf-innerblocks-container';
			}
			$content = '<div class="' . $class . '">' . $content . '</div>';
		}
		$html = preg_replace( '/<InnerBlocks([\S\s]*?)\/>/', $content, $html );
	}

	// Store in cache for preloading if we're in the backend.
	acf_get_store( 'block-cache' )->set(
		$attributes['id'],
		array(
			'form' => $form,
			'html' => $html,
		)
	);

	// Prevent edit forms being output to rest endpoints.
	if ( $form && acf_get_data( 'acf_inside_rest_call' ) && apply_filters( 'acf/blocks/prevent_edit_forms_on_rest_endpoints', true ) ) {
		return '';
	}

	return $html;
}

/**
 * Renders the block HTML.
 *
 * @since   5.7.12
 *
 * @param   array    $attributes The block attributes.
 * @param   string   $content The block content.
 * @param   bool     $is_preview Whether or not the block is being rendered for editing preview.
 * @param   int      $post_id The current post being edited or viewed.
 * @param   WP_Block $wp_block The block instance (since WP 5.5).
 * @param   array    $context The block context array.
 * @return  void|string
 */
function acf_render_block( $attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null, $context = false ) {

	// Prepare block ensuring all settings and attributes exist.
	$block = acf_prepare_block( $attributes );
	if ( ! $block ) {
		return '';
	}

	// Find post_id if not defined.
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Enqueue block type assets.
	acf_enqueue_block_type_assets( $block );

	// Ensure block ID is prefixed for render.
	$block['id'] = acf_ensure_block_id_prefix( $block['id'] );

	// Setup postdata allowing get_field() to work.
	acf_setup_meta( $block['data'], $block['id'], true );

	// Call render_callback.
	if ( is_callable( $block['render_callback'] ) ) {
		call_user_func( $block['render_callback'], $block, $content, $is_preview, $post_id, $wp_block, $context );

		// Or include template.
	} elseif ( $block['render_template'] ) {
		do_action( 'acf_block_render_template', $block, $content, $is_preview, $post_id, $wp_block, $context );
	}

	// Reset postdata.
	acf_reset_meta( $block['id'] );
}

/**
 * Locate and include an ACF block's template.
 *
 * @since   6.0.4
 *
 * @param   array $block The block props.
 */
function acf_block_render_template( $block, $content, $is_preview, $post_id, $wp_block, $context ) {
	// Locate template.
	if ( isset( $block['path'] ) && file_exists( $block['path'] . '/' . $block['render_template'] ) ) {
		$path = $block['path'] . '/' . $block['render_template'];
	} elseif ( file_exists( $block['render_template'] ) ) {
		$path = $block['render_template'];
	} else {
		$path = locate_template( $block['render_template'] );
	}

	// Include template.
	if ( file_exists( $path ) ) {
		include $path;
	}
}

/**
 * Returns an array of all fields for the given block.
 *
 * @date    24/10/18
 * @since   5.8.0
 *
 * @param   array $block The block props.
 * @return  array
 */
function acf_get_block_fields( $block ) {

	// Vars.
	$fields = array();

	// Get field groups for this block.
	$field_groups = acf_get_field_groups(
		array(
			'block' => $block['name'],
		)
	);

	// Loop over results and append fields.
	if ( $field_groups ) {
		foreach ( $field_groups as $field_group ) {
			$fields = array_merge( $fields, acf_get_fields( $field_group ) );
		}
	}

	// Return fields.
	return $fields;
}

/**
 * Enqueues and localizes block scripts and styles.
 *
 * @since   5.7.13
 *
 * @return  void
 */
function acf_enqueue_block_assets() {

	// Localize text.
	acf_localize_text(
		array(
			'Switch to Edit'           => __( 'Switch to Edit', 'acf' ),
			'Switch to Preview'        => __( 'Switch to Preview', 'acf' ),
			'Change content alignment' => __( 'Change content alignment', 'acf' ),

			/* translators: %s: Block type title */
			'%s settings'              => __( '%s settings', 'acf' ),
		)
	);

	// Get block types.
	$block_types = array_map(
		function( $block ) {
			// Render Callback may contain a incompatible class for JSON encoding. Turn it into a boolean for the frontend.
			$block['render_callback'] = ! empty( $block['render_callback'] );
			return $block;
		},
		acf_get_block_types()
	);

	// Localize data.
	acf_localize_data(
		array(
			'blockTypes' => array_values( $block_types ),
			'postType'   => get_post_type(),
		)
	);

	// Enqueue script.
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$blocks_js_path = acf_get_url( "assets/build/js/pro/acf-pro-blocks{$min}.js" );

	wp_enqueue_script( 'acf-blocks', $blocks_js_path, array( 'acf-input', 'wp-blocks' ), ACF_VERSION, true );

	// Enqueue block assets.
	array_map( 'acf_enqueue_block_type_assets', $block_types );

	// During the edit screen loading, WordPress renders all blocks in its own attempt to preload data.
	// Retrieve any cached block HTML and include this in the localized data.
	if ( acf_get_setting( 'preload_blocks' ) ) {
		$preloaded_blocks = acf_get_store( 'block-cache' )->get_data();
		acf_localize_data(
			array(
				'preloadedBlocks' => $preloaded_blocks,
			)
		);
	}
}

/**
 * Enqueues scripts and styles for a specific block type.
 *
 * @since   5.7.13
 *
 * @param   array $block_type The block type settings.
 * @return  void
 */
function acf_enqueue_block_type_assets( $block_type ) {

	// Generate handle from name.
	$handle = 'block-' . acf_slugify( $block_type['name'] );

	// Enqueue style.
	if ( $block_type['enqueue_style'] ) {
		wp_enqueue_style( $handle, $block_type['enqueue_style'], array(), ACF_VERSION, 'all' );
	}

	// Enqueue script.
	if ( $block_type['enqueue_script'] ) {
		wp_enqueue_script( $handle, $block_type['enqueue_script'], array(), ACF_VERSION, true );
	}

	// Enqueue assets callback.
	if ( $block_type['enqueue_assets'] && is_callable( $block_type['enqueue_assets'] ) ) {
		call_user_func( $block_type['enqueue_assets'], $block_type );
	}
}

/**
 * Handles the ajax request for block data.
 *
 * @since   5.7.13
 *
 * @return  void
 */
function acf_ajax_fetch_block() {
	// Validate ajax request.
	if ( ! acf_verify_ajax() ) {
		wp_send_json_error();
	}

	// Get request args.
	$args = acf_request_args(
		array(
			'post_id'  => 0,
			'clientId' => null,
			'query'    => array(),
		)
	);

	$args['block']   = isset( $_REQUEST['block'] ) ? $_REQUEST['block'] : false; //phpcs:ignore -- requires auth; designed to contain unescaped html.
	$args['context'] = isset( $_REQUEST['context'] ) ? $_REQUEST['context'] : array(); //phpcs:ignore -- requires auth; designed to contain unescaped html.

	$block       = $args['block'];
	$query       = $args['query'];
	$client_id   = $args['clientId'];
	$raw_context = $args['context'];
	$post_id     = $args['post_id'];

	// Bail early if no block.
	if ( ! $block ) {
		wp_send_json_error();
	}

	// Unslash and decode $_POST data for block and context.
	$block = wp_unslash( $block );
	$block = json_decode( $block, true );

	$context = false;
	if ( ! empty( $raw_context ) ) {
		$raw_context = wp_unslash( $raw_context );
		$raw_context = json_decode( $raw_context, true );
		if ( is_array( $raw_context ) ) {
			$context = $raw_context;
			// Check if a postId is set in the context, otherwise try and use it the default post_id.
			$post_id = isset( $context['postId'] ) ? intval( $context['postId'] ) : intval( $args['post_id'] );
		}
	}

	// Check if clientId should become $block['id'].
	if ( empty( $block['id'] ) && ! empty( $client_id ) ) {
		$block['id'] = $client_id;
	}

	// Prepare block ensuring all settings and attributes exist.
	$block = acf_prepare_block( $block );
	if ( ! $block ) {
		wp_send_json_error();
	}

	// Load field defaults when first previewing a block.
	if ( ! empty( $query['preview'] ) && ! $block['data'] ) {
		$fields = acf_get_block_fields( $block );
		foreach ( $fields as $field ) {
			$block['data'][ "_{$field['name']}" ] = $field['key'];
		}
	}

	// Setup postdata allowing form to load meta.
	acf_setup_meta( $block['data'], acf_ensure_block_id_prefix( $block['id'] ), true );

	// Setup main postdata for post_id.
	global $post;
	//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- required for block template rendering.
	$post = get_post( $post_id );
	setup_postdata( $post );

	// Vars.
	$response = array( 'clientId' => $client_id );

	// Query form.
	if ( ! empty( $query['form'] ) ) {

		// Load fields for form.
		$fields = acf_get_block_fields( $block );

		// Prefix field inputs to avoid multiple blocks using the same name/id attributes.
		acf_prefix_fields( $fields, "acf-{$block['id']}" );

		if ( $fields ) {
			// Start Capture.
			ob_start();

			// Render.
			echo '<div class="acf-block-fields acf-fields">';
				acf_render_fields( $fields, acf_ensure_block_id_prefix( $block['id'] ), 'div', 'field' );
			echo '</div>';

			// Store Capture.
			$response['form'] = ob_get_clean();
		} else {
			// There are no fields on this block.
			$response['form'] = acf_get_empty_block_form_html( $block['name'] ); //phpcs:ignore -- escaped in function.
		}
	}

	// Query preview.
	if ( ! empty( $query['preview'] ) ) {
		// Render_callback vars.
		$content    = '';
		$is_preview = true;

		// Render and store HTML.
		$response['preview'] = acf_rendered_block( $block, $content, $is_preview, $post_id, null, $context );
	}

	// Send response.
	wp_send_json_success( $response );
}

// Register ajax action.
acf_register_ajax( 'fetch-block', 'acf_ajax_fetch_block' );

/**
 * Render the empty block form for when a block has no fields assigned.
 *
 * @since   6.0.0
 *
 * @param   string $block_name The block name current being rendered.
 * @return  string The html that makes up a block form with no fields.
 */
function acf_get_empty_block_form_html( $block_name ) {

	$message = __( 'This block contains no editable fields.', 'acf' );

	if ( acf_current_user_can_admin() ) {
		$message .= ' ';
		$message .= sprintf(
			/* translators: %s: an admin URL to the field group edit screen */
			__( 'Assign a <a href="%s" target="_blank">field group</a> to add fields to this block.', 'acf' ),
			admin_url( 'edit.php?post_type=acf-field-group' )
		);
	}

	$message = apply_filters( 'acf/blocks/no_fields_assigned_message', $message, $block_name );

	return empty( $message ) ? '' : acf_esc_html( '<div class="acf-block-fields acf-fields acf-empty-block-fields">' . $message . '</div>' );
}

/**
 * Parse content that may contain HTML block comments and saves ACF block meta.
 *
 * @since   5.7.13
 *
 * @param   string $text Content that may contain HTML block comments.
 * @return  string
 */
function acf_parse_save_blocks( $text = '' ) {

	// Search text for dynamic blocks and modify attrs.
	return addslashes(
		preg_replace_callback(
			'/<!--\s+wp:(?P<name>[\S]+)\s+(?P<attrs>{[\S\s]+?})\s+(?P<void>\/)?-->/',
			'acf_parse_save_blocks_callback',
			stripslashes( $text )
		)
	);

}

// Hook into saving process.
add_filter( 'content_save_pre', 'acf_parse_save_blocks', 5, 1 );

/**
 * Callback used in preg_replace to modify ACF Block comment.
 *
 * @since   5.7.13
 *
 * @param   array $matches The preg matches.
 * @return  string
 */
function acf_parse_save_blocks_callback( $matches ) {

	// Defaults.
	$name  = isset( $matches['name'] ) ? $matches['name'] : '';
	$attrs = isset( $matches['attrs'] ) ? json_decode( $matches['attrs'], true ) : '';
	$void  = isset( $matches['void'] ) ? $matches['void'] : '';

	// Bail early if missing data or not an ACF Block.
	if ( ! $name || ! $attrs || ! acf_has_block_type( $name ) ) {
		return $matches[0];
	}

	// Check if we need to generate a block ID.
	$block_id = acf_get_block_id( $attrs );

	// Convert "data" to "meta".
	// No need to check if already in meta format. Local Meta will do this for us.
	if ( isset( $attrs['data'] ) ) {
		$attrs['data'] = acf_setup_meta( $attrs['data'], acf_ensure_block_id_prefix( $block_id ) );
	}

	/**
	 * Filters the block attributes before saving.
	 *
	 * @date    18/3/19
	 * @since   5.7.14
	 *
	 * @param   array $attrs The block attributes.
	 */
	$attrs = apply_filters( 'acf/pre_save_block', $attrs );

	// Gutenberg expects a specific encoding format.
	$attrs = acf_serialize_block_attributes( $attrs );

	return '<!-- wp:' . $name . ' ' . $attrs . ' ' . $void . '-->';
}

/**
 * Return or generate a block ID.
 *
 * @since 6.0.0
 *
 * @param array $attributes A block attributes array.
 * @param array $context The block context array, defaults to an empty array.
 * @return string A block ID.
 */
function acf_get_block_id( $attributes, $context = array() ) {
	$attributes['_acf_context'] = $context;
	if ( empty( $attributes['id'] ) ) {
		unset( $attributes['id'] );

		// Remove all empty string values as they're not present in JS hash building.
		foreach ( $attributes as $key => $value ) {
			if ( '' === $value ) {
				unset( $attributes[ $key ] );
			}
		}

		// Check if data is empty and remove it if so to match JS hash building.
		if ( isset( $attributes['data'] ) && empty( $attributes['data'] ) ) {
			unset( $attributes['data'] );
		}

		ksort( $attributes );
		return md5( wp_json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}
	return $attributes['id'];
}

/**
 * Ensure a block ID always has a block_ prefix for post meta internals.
 *
 * @since 6.0.0
 *
 * @param string $block_id A possibly non-prefixed block ID.
 * @return string A prefixed block ID.
 */
function acf_ensure_block_id_prefix( $block_id ) {
	if ( substr( $block_id, 0, 6 ) === 'block_' ) {
		return $block_id;
	}
	return 'block_' . $block_id;
}

/**
 * This directly copied from the WordPress core `serialize_block_attributes()` function.
 *
 * We need this in order to make sure that block attributes are stored in a way that is
 * consistent with how Gutenberg sends them over from JS, and so that things like wp_kses()
 * work as expected. Copied from core to get around a bug that was fixed in 5.8.1 or on the off chance
 * that folks are still using WP 5.3 or below.
 *
 * TODO: Remove this when we refactor `acf_parse_save_blocks_callback()` to use `serialize_block()`,
 * or when we're confident that folks aren't using WP versions prior to 5.8.
 *
 * @since 5.12
 *
 * @param array $block_attributes Attributes object.
 * @return string Serialized attributes.
 */
function acf_serialize_block_attributes( $block_attributes ) {
	$encoded_attributes = wp_json_encode( $block_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	$encoded_attributes = preg_replace( '/--/', '\\u002d\\u002d', $encoded_attributes );
	$encoded_attributes = preg_replace( '/</', '\\u003c', $encoded_attributes );
	$encoded_attributes = preg_replace( '/>/', '\\u003e', $encoded_attributes );
	$encoded_attributes = preg_replace( '/&/', '\\u0026', $encoded_attributes );
	// Regex: /\\"/.
	$encoded_attributes = preg_replace( '/\\\\"/', '\\u0022', $encoded_attributes );

	return $encoded_attributes;
}

/**
 * Set ACF data before a rest call if media scripts have not been enqueued yet for after REST reset.
 *
 * @date    07/06/22
 * @since   6.0
 *
 * @param   WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response The WordPress response object.
 * @return  mixed
 */
function acf_set_after_rest_media_enqueue_reset_flag( $response ) {
	global $wp_actions;

	acf_set_data( 'acf_inside_rest_call', true );
	acf_set_data( 'acf_should_reset_media_enqueue', empty( $wp_actions['wp_enqueue_media'] ) );
	acf_set_data( 'acf_did_render_block_form', false );

	return $response;
}
add_filter( 'rest_request_before_callbacks', 'acf_set_after_rest_media_enqueue_reset_flag' );

/**
 * Reset wp_enqueue_media action count after REST call so it can happen inside the main execution if required.
 *
 * @date    07/06/22
 * @since   6.0
 *
 * @param   WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response The WordPress response object.
 * @return  mixed
 */
function acf_reset_media_enqueue_after_rest( $response ) {
	acf_set_data( 'acf_inside_rest_call', false );
	if ( acf_get_data( 'acf_should_reset_media_enqueue' ) && acf_get_data( 'acf_did_render_block_form' ) ) {
		global $wp_actions;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- no other option here as this works around a breaking WordPress change with REST preload scopes.
		$wp_actions['wp_enqueue_media'] = 0;
	}

	return $response;
}
add_filter( 'rest_request_after_callbacks', 'acf_reset_media_enqueue_after_rest' );
