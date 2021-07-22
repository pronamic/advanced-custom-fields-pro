<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register store.
acf_register_store( 'block-types' );
acf_register_store( 'block-cache' );
		
/**
 * acf_register_block_type
 *
 * Registers a block type.
 *
 * @date	18/2/19
 * @since	5.8.0
 *
 * @param	array $block The block settings.
 * @return	(array|false)
 */
function acf_register_block_type( $block ) {
	
	// Validate block type settings.
	$block = acf_validate_block_type( $block );
	
	/**
	 * Filters the arguments for registering a block type.
	 *
	 * @since	5.8.9
	 *
	 * @param	array $block The array of arguments for registering a block type.
	 */
    $block = apply_filters( 'acf/register_block_type_args', $block );
    
    // Require name.
    if( !$block['name'] ) {
	    $message = __( 'Block type name is required.', 'acf' );
	    _doing_it_wrong( __FUNCTION__, $message, '5.8.0' );
		return false;
    }
    
	// Bail early if already exists.
	if( acf_has_block_type($block['name']) ) {
		$message = sprintf( __( 'Block type "%s" is already registered.' ), $block['name'] );
		_doing_it_wrong( __FUNCTION__, $message, '5.8.0' );
		return false;
	}
	
	// Add to storage.
	acf_get_store( 'block-types' )->set( $block['name'], $block );
	
	// Register block type in WP.
	if( function_exists('register_block_type') ) {
		register_block_type($block['name'], array(
			'attributes'		=> acf_get_block_type_default_attributes( $block ),
			'render_callback'	=> 'acf_render_block_callback',
		));
	}
	
	// Register action.
	add_action( 'enqueue_block_editor_assets', 'acf_enqueue_block_assets' );
	
	// Return block.
	return $block;
}

/**
 * acf_register_block
 *
 * See acf_register_block_type().
 *
 * @date	18/2/19
 * @since	5.7.12
 *
 * @param	array $block The block settings.
 * @return	(array|false)
 */
function acf_register_block( $block ) {
	return acf_register_block_type( $block );
}

/**
 * acf_has_block_type
 *
 * Returns true if a block type exists for the given name.
 *
 * @date	18/2/19
 * @since	5.7.12
 *
 * @param	string $name The block type name.
 * @return	bool
 */
function acf_has_block_type( $name ) {
	return acf_get_store( 'block-types' )->has( $name );
}

/**
 * acf_get_block_types
 *
 * Returns an array of all registered block types.
 *
 * @date	18/2/19
 * @since	5.7.12
 *
 * @param	void
 * @return	array
 */
function acf_get_block_types() {
	return acf_get_store( 'block-types' )->get();
}

/**
 * acf_get_block_types
 *
 * Returns a block type for the given name.
 *
 * @date	18/2/19
 * @since	5.7.12
 *
 * @param	string $name The block type name.
 * @return	(array|null)
 */
function acf_get_block_type( $name ) {
	return acf_get_store( 'block-types' )->get( $name );
}

/**
 * acf_remove_block_type
 *
 * Removes a block type for the given name.
 *
 * @date	18/2/19
 * @since	5.7.12
 *
 * @param	string $name The block type name.
 * @return	void
 */
function acf_remove_block_type( $name ) {
	acf_get_store( 'block-types' )->remove( $name );
}

/**
 * acf_get_block_type_default_attributes
 *
 * Returns an array of default attribute settings for a block type.
 *
 * @date	19/11/18
 * @since	5.8.0
 *
 * @param	void
 * @return	array
 */
function acf_get_block_type_default_attributes( $block_type ) {
	$attributes = array(
		'id'		=> array(
			'type'		=> 'string',
			'default'	=> '',
		),
		'name'		=> array(
			'type'		=> 'string',
			'default'	=> '',
		),
		'data'		=> array(
			'type'		=> 'object',
			'default'	=> array(),
		),
		'align'		=> array(
			'type'		=> 'string',
			'default'	=> '',
		),
		'mode'		=> array(
			'type'		=> 'string',
			'default'	=> '',
		)
	);
	if( !empty( $block_type['supports']['align_text'] ) ) {
		$attributes['align_text'] = array(
			'type'		=> 'string',
			'default'	=> '',
		);
	}
	if( !empty( $block_type['supports']['align_content'] ) ) {
		$attributes['align_content'] = array(
			'type'		=> 'string',
			'default'	=> '',
		);
	}
	return $attributes;
}

/**
 * acf_validate_block_type
 *
 * Validates a block type ensuring all settings exist.
 *
 * @date	10/4/18
 * @since	5.8.0
 *
 * @param	array $block The block settings.
 * @return	array
 */
function acf_validate_block_type( $block ) {
	
	// Add default settings.
	$block = wp_parse_args($block, array(
		'name'				=> '',
		'title'				=> '',
		'description'		=> '',
		'category'			=> 'common',
		'icon'				=> '',
		'mode'				=> 'preview',
		'align'				=> '',
		'keywords'			=> array(),
		'supports'			=> array(),
		'post_types'		=> array(),
		'render_template'	=> false,
		'render_callback'	=> false,
		'enqueue_style'		=> false,
		'enqueue_script'	=> false,
		'enqueue_assets'	=> false,
	));
	
	// Restrict keywords to 3 max to avoid JS error in older versions.
	if( acf_version_compare('wp', '<', '5.2') ) {
		$block['keywords'] = array_slice($block['keywords'], 0, 3);
	}
	
	// Generate name with prefix.
	if( $block['name'] ) {
		$block['name'] = 'acf/' . acf_slugify($block['name']);
	}
	
	// Add default 'supports' settings.
	$block['supports'] = wp_parse_args($block['supports'], array(
		'align'		=> true,
		'html'		=> false,
		'mode'		=> true,
	));

	// Correct "Experimental" flags.
	if( isset($block['supports']['__experimental_jsx']) ) {
		$block['supports']['jsx'] = $block['supports']['__experimental_jsx'];
	}
	
	// Return block.
	return $block;
}

/**
 * acf_prepare_block
 *
 * Prepares a block for use in render_callback by merging in all settings and attributes.
 *
 * @date	19/11/18
 * @since	5.8.0
 *
 * @param	array $block The block props.
 * @return	array
 */
function acf_prepare_block( $block ) {
	
	// Bail early if no name.
	if( !isset($block['name']) ) {
		return false;
	}
	
	// Get block type and return false if doesn't exist.
	$block_type = acf_get_block_type( $block['name'] );
	if( !$block_type ) {
		return false;
	}
	
	// Generate default attributes.
	$attributes = array();
	foreach( acf_get_block_type_default_attributes($block_type) as $k => $v ) {
		$attributes[ $k ] = $v['default'];
	}
	
	// Merge together arrays in order of least to most specific.
	$block = array_merge($block_type, $attributes, $block);
	
	// Return block.
	return $block;
}

/**
 * The render callback for all ACF blocks.
 *
 * @date	28/10/20
 * @since	5.9.2
 *
 * @param	array  $attributes The block attributes.
 * @param	string $content The block content.
 * @param	WP_Block $wp_block The block instance (since WP 5.5).
 * @return	string The block HTML.
 */
function acf_render_block_callback( $attributes, $content = '', $wp_block = null ) {
	$is_preview = false;
	$post_id = get_the_ID();
	
	// Set preview flag to true when rendering for the block editor.
	if( is_admin() && acf_is_block_editor() ) {
		$is_preview = true;
	}
	
	// Return rendered block HTML.
	return acf_rendered_block( $attributes, $content, $is_preview, $post_id, $wp_block );
}

/**
 * Returns the rendered block HTML.
 *
 * @date	28/2/19
 * @since	5.7.13
 *
 * @param	array  $attributes The block attributes.
 * @param	string $content The block content.
 * @param	bool $is_preview Whether or not the block is being rendered for editing preview.
 * @param	int $post_id The current post being edited or viewed.
 * @param	WP_Block $wp_block The block instance (since WP 5.5).
 * @return	string The block HTML.
 */
function acf_rendered_block( $attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null ) {
	
	// Capture block render output.
	ob_start();
	acf_render_block( $attributes, $content, $is_preview, $post_id, $wp_block );
	$html = ob_get_clean();

	// Replace <InnerBlocks /> placeholder on front-end.
	if( !$is_preview ) {
		// Escape "$" character to avoid "capture group" interpretation.
		$content = str_replace( '$', '\$', $content );
		$html = preg_replace( '/<InnerBlocks([\S\s]*?)\/>/', $content, $html );
	}
	
	// Store in cache for preloading.
	acf_get_store( 'block-cache' )->set( $attributes['id'], '<div class="acf-block-preview">' . $html . '</div>' );
	return $html;
}

/**
 * Renders the block HTML.
 *
 * @date	19/2/19
 * @since	5.7.12
 *
 * @param	array  $attributes The block attributes.
 * @param	string $content The block content.
 * @param	bool $is_preview Whether or not the block is being rendered for editing preview.
 * @param	int $post_id The current post being edited or viewed.
 * @param	WP_Block $wp_block The block instance (since WP 5.5).
 * @return	void
 */
function acf_render_block( $attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null ) {
	
	// Prepare block ensuring all settings and attributes exist.
	$block = acf_prepare_block( $attributes );
	if( !$block ) {
		return '';
	}
	
	// Find post_id if not defined.
	if( !$post_id ) {
		$post_id = get_the_ID();
	}
	
	// Enqueue block type assets.
	acf_enqueue_block_type_assets( $block );
	
	// Setup postdata allowing get_field() to work.
	acf_setup_meta( $block['data'], $block['id'], true );
	
	// Call render_callback.
	if( is_callable( $block['render_callback'] ) ) {
		call_user_func( $block['render_callback'], $block, $content, $is_preview, $post_id, $wp_block );
	
	// Or include template.
	} elseif( $block['render_template'] ) {
		
		// Locate template.
		if( file_exists($block['render_template']) ) {
			$path = $block['render_template'];
	    } else {
		    $path = locate_template( $block['render_template'] );
	    }
	    
	    // Include template.
	    if( file_exists($path) ) {
		    include( $path );
	    }
	}
	
	// Reset postdata.
	acf_reset_meta( $block['id'] );
}

/**
 * acf_get_block_fields
 *
 * Returns an array of all fields for the given block.
 *
 * @date	24/10/18
 * @since	5.8.0
 *
 * @param	array $block The block props.
 * @return	array
 */
function acf_get_block_fields( $block ) {
	
	// Vars.
	$fields = array();
	
	// Get field groups for this block.
	$field_groups = acf_get_field_groups( array(
		'block'	=> $block['name']
	));
			
	// Loop over results and append fields.
	if( $field_groups ) {
	foreach( $field_groups as $field_group ) {
		$fields = array_merge( $fields, acf_get_fields( $field_group ) );
	}}
	
	// Return fields.
	return $fields;
}

/**
 * acf_enqueue_block_assets
 *
 * Enqueues and localizes block scripts and styles.
 *
 * @date	28/2/19
 * @since	5.7.13
 *
 * @param	void
 * @return	void
 */
function acf_enqueue_block_assets() {
	
	// Localize text.
	acf_localize_text(array(
		'Switch to Edit'		=> __('Switch to Edit', 'acf'),
		'Switch to Preview'		=> __('Switch to Preview', 'acf'),
		'Change content alignment'	=> __('Change content alignment', 'acf'),

		/* translators: %s: Block type title */
		'%s settings'			=> __('%s settings', 'acf'),
	));
	
	// Get block types.
	$block_types = acf_get_block_types();
	
	// Localize data.
	acf_localize_data(
		array(
			'blockTypes' => array_values( $block_types ),
			'postType'   => get_post_type(),
		)
	);
	
	// Enqueue script.
	$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	wp_enqueue_script( 'acf-blocks', acf_get_url("assets/build/js/pro/acf-pro-blocks{$min}.js"), array('acf-input', 'wp-blocks'), ACF_VERSION, true );
	
	// Enqueue block assets.
	array_map( 'acf_enqueue_block_type_assets', $block_types );
	
	// During the edit screen loading, WordPress renders all blocks in its own attempt to preload data.
	// Retrieve any cached block HTML and include this in the localized data.
	if( defined('ACF_EXPERIMENTAL_PRELOAD_BLOCKS') && ACF_EXPERIMENTAL_PRELOAD_BLOCKS ) {
		$preloaded_blocks = acf_get_store( 'block-cache' )->get_data();
		acf_localize_data(array(
			'preloadedBlocks' => $preloaded_blocks
		));
	}
}

/**
 * acf_enqueue_block_type_assets
 *
 * Enqueues scripts and styles for a specific block type.
 *
 * @date	28/2/19
 * @since	5.7.13
 *
 * @param	array $block_type The block type settings.
 * @return	void
 */
function acf_enqueue_block_type_assets( $block_type ) {
	
	// Generate handle from name.
	$handle = 'block-' . acf_slugify($block_type['name']);
	
	// Enqueue style.
	if( $block_type['enqueue_style'] ) {
		wp_enqueue_style( $handle, $block_type['enqueue_style'], array(), false, 'all' );
	}
	
	// Enqueue script.
	if( $block_type['enqueue_script'] ) {
		wp_enqueue_script( $handle, $block_type['enqueue_script'], array(), false, true );
	}
	
	// Enqueue assets callback.
	if( $block_type['enqueue_assets'] && is_callable($block_type['enqueue_assets']) ) {
		call_user_func( $block_type['enqueue_assets'], $block_type );
	}
}

/**
 * acf_ajax_fetch_block
 *
 * Handles the ajax request for block data.
 *
 * @date	28/2/19
 * @since	5.7.13
 *
 * @param	void
 * @return	void
 */
function acf_ajax_fetch_block() {
	
	// Validate ajax request.
	if( !acf_verify_ajax() ) {
		 wp_send_json_error();
	}
	
	// Get request args.
	extract(acf_request_args(array(
		'block'		=> false,
		'post_id'	=> 0,
		'query'		=> array(),
	)));
	
	// Bail ealry if no block.
	if( !$block ) {
		wp_send_json_error();
	}
	
	// Unslash and decode $_POST data.
	$block = wp_unslash($block);
	$block = json_decode($block, true);
	
	// Prepare block ensuring all settings and attributes exist.
	if( !$block = acf_prepare_block( $block ) ) {
		wp_send_json_error();
	}
	
	// Load field defaults when first previewing a block.
	if( !empty($query['preview']) && !$block['data'] ) {
		$fields = acf_get_block_fields( $block );
		foreach( $fields as $field ) {
		   	$block['data'][ "_{$field['name']}" ] = $field['key'];
   		}
	}
	
	// Setup postdata allowing form to load meta.
	acf_setup_meta( $block['data'], $block['id'], true );
   	
	// Vars.
	$response = array();
	
	// Query form.
	if( !empty($query['form']) ) {
		
		// Load fields for form.
		$fields = acf_get_block_fields( $block );
		
		// Prefix field inputs to avoid multiple blocks using the same name/id attributes.
		acf_prefix_fields( $fields, "acf-{$block['id']}" );
		
		// Start Capture.
		ob_start();
		
		// Render.
		echo '<div class="acf-block-fields acf-fields">';
   			acf_render_fields( $fields, $block['id'], 'div', 'field' );
		echo '</div>';
		
		// Store Capture.
		$response['form'] = ob_get_contents();
		ob_end_clean();
	}
	
	// Query preview.
	if( !empty($query['preview']) ) {
		
		// Render_callback vars.
   		$content = '';
   		$is_preview = true;
   		
		// Render.
		$html = '';
		$html .= '<div class="acf-block-preview">';
   		$html .= 	acf_rendered_block( $block, $content, $is_preview, $post_id );
		$html .= '</div>';
		
		// Store HTML.
		$response['preview'] = $html;
	}
	
	// Send repsonse.
	wp_send_json_success( $response );
}

// Register ajax action.
acf_register_ajax( 'fetch-block', 'acf_ajax_fetch_block' );

/**
 * acf_parse_save_blocks
 *
 * Parse content that may contain HTML block comments and saves ACF block meta.
 *
 * @date	27/2/19
 * @since	5.7.13
 *
 * @param	string $text Content that may contain HTML block comments.
 * @return	string
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
 * acf_parse_save_blocks_callback
 *
 * Callback used in preg_replace to modify ACF Block comment.
 *
 * @date	1/3/19
 * @since	5.7.13
 *
 * @param	array $matches The preg matches.
 * @return	string
 */
function acf_parse_save_blocks_callback( $matches ) {
	
	// Defaults
	$name = isset($matches['name']) ? $matches['name'] : '';
	$attrs = isset($matches['attrs']) ? json_decode( $matches['attrs'], true) : '';
	$void = isset($matches['void']) ? $matches['void'] : '';
	
	// Bail early if missing data or not an ACF Block.
	if( !$name || !$attrs || !acf_has_block_type($name) ) {
		return $matches[0];
	}
	
	// Convert "data" to "meta".
	// No need to check if already in meta format. Local Meta will do this for us.
	if( isset($attrs['data']) ) {
		$attrs['data'] = acf_setup_meta( $attrs['data'], $attrs['id'] );
	}
	
	// Prevent wp_targeted_link_rel from corrupting JSON.
	remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
	remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
	remove_filter( 'content_save_pre', 'balanceTags', 50 );
	
	/**
	 * Filteres the block attributes before saving.
	 *
	 * @date	18/3/19
	 * @since	5.7.14
	 *
	 * @param	array $attrs The block attributes.
	 */
	$attrs = apply_filters( 'acf/pre_save_block', $attrs );
	
	// Return new comment
	return '<!-- wp:' . $name . ' ' . acf_json_encode($attrs) . ' ' . $void . '-->';
}
