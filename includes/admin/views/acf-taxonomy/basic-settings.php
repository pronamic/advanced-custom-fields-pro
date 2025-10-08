<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

global $acf_taxonomy;

$acf_duplicate_taxonomy = acf_get_taxonomy_from_request_args( 'acfduplicate' );

if ( acf_is_taxonomy( $acf_duplicate_taxonomy ) ) {
	// Reset vars that likely have to be changed.
	$acf_duplicate_taxonomy['key']             = uniqid( 'taxonomy_' );
	$acf_duplicate_taxonomy['title']           = '';
	$acf_duplicate_taxonomy['labels']          = array_map( '__return_empty_string', $acf_duplicate_taxonomy['labels'] );
	$acf_duplicate_taxonomy['taxonomy']        = '';
	$acf_duplicate_taxonomy['rewrite']['slug'] = '';
	$acf_duplicate_taxonomy['query_var_name']  = '';
	$acf_duplicate_taxonomy['rest_base']       = '';

	// Rest of the vars can be reused.
	$acf_taxonomy = $acf_duplicate_taxonomy;
}

acf_render_field_wrap(
	array(
		'label'       => __( 'Plural Label', 'acf' ),
		/* translators: example taxonomy */
		'placeholder' => __( 'Genres', 'acf' ),
		'type'        => 'text',
		'key'         => 'name',
		'name'        => 'name',
		'class'       => 'acf_plural_label',
		'prefix'      => 'acf_taxonomy[labels]',
		'value'       => $acf_taxonomy['labels']['name'],
		'required'    => 1,
	),
	'div',
	'field'
);

acf_render_field_wrap(
	array(
		'label'       => __( 'Singular Label', 'acf' ),
		/* translators: example taxonomy */
		'placeholder' => __( 'Genre', 'acf' ),
		'type'        => 'text',
		'key'         => 'singular_name',
		'name'        => 'singular_name',
		'class'       => 'acf_slugify_to_key acf_singular_label',
		'prefix'      => 'acf_taxonomy[labels]',
		'value'       => $acf_taxonomy['labels']['singular_name'],
		'required'    => 1,
	),
	'div',
	'field'
);

acf_render_field_wrap(
	array(
		'label'        => __( 'Taxonomy Key', 'acf' ),
		'instructions' => __( 'Lower case letters, underscores and dashes only, Max 32 characters.', 'acf' ),
		/* translators: example taxonomy */
		'placeholder'  => __( 'genre', 'acf' ),
		'type'         => 'text',
		'key'          => 'taxonomy',
		'name'         => 'taxonomy',
		'maxlength'    => 32,
		'class'        => 'acf_slugified_key',
		'prefix'       => 'acf_taxonomy',
		'value'        => $acf_taxonomy['taxonomy'],
		'required'     => 1,
	),
	'div',
	'field'
);

// Allow preselecting the linked post types based on previously created post type.
$acf_use_post_type = acf_get_post_type_from_request_args( 'create-taxonomy' );
if ( $acf_use_post_type && ! empty( $acf_use_post_type['post_type'] ) ) {
	$acf_taxonomy['object_type'] = array( $acf_use_post_type['post_type'] );
}

acf_render_field_wrap(
	array(
		'label'        => __( 'Post Types', 'acf' ),
		'type'         => 'select',
		'name'         => 'object_type',
		'prefix'       => 'acf_taxonomy',
		'value'        => $acf_taxonomy['object_type'],
		'choices'      => acf_get_pretty_post_types(),
		'multiple'     => 1,
		'ui'           => 1,
		'allow_null'   => 1,
		'instructions' => __( 'One or many post types that can be classified with this taxonomy.', 'acf' ),
	),
	'div',
	'field'
);

acf_render_field_wrap( array( 'type' => 'seperator' ) );

acf_render_field_wrap(
	array(
		'type'         => 'true_false',
		'key'          => 'public',
		'name'         => 'public',
		'prefix'       => 'acf_taxonomy',
		'value'        => $acf_taxonomy['public'],
		'label'        => __( 'Public', 'acf' ),
		'instructions' => __( 'Makes a taxonomy visible on the frontend and in the admin dashboard.', 'acf' ),
		'ui'           => true,
		'default'      => 1,
	)
);

acf_render_field_wrap(
	array(
		'type'         => 'true_false',
		'key'          => 'hierarchical',
		'name'         => 'hierarchical',
		'class'        => 'acf_hierarchical_switch',
		'prefix'       => 'acf_taxonomy',
		'value'        => $acf_taxonomy['hierarchical'],
		'label'        => __( 'Hierarchical', 'acf' ),
		'instructions' => __( 'Hierarchical taxonomies can have descendants (like categories).', 'acf' ),
		'ui'           => true,
	),
	'div'
);

do_action( 'acf/taxonomy/basic_settings', $acf_taxonomy );

acf_render_field_wrap( array( 'type' => 'seperator' ) );

acf_render_field_wrap(
	array(
		'label'        => __( 'Advanced Configuration', 'acf' ),
		'instructions' => __( 'I know what I\'m doing, show me all the options.', 'acf' ),
		'type'         => 'true_false',
		'key'          => 'advanced_configuration',
		'name'         => 'advanced_configuration',
		'prefix'       => 'acf_taxonomy',
		'value'        => $acf_taxonomy['advanced_configuration'],
		'ui'           => 1,
		'class'        => 'acf-advanced-settings-toggle',
	)
);

?>
	<div class="acf-hidden">
		<input type="hidden" name="acf_taxonomy[key]" value="<?php echo esc_attr( $acf_taxonomy['key'] ); ?>" />
		<input type="hidden" name="acf_taxonomy[import_source]" value="<?php echo esc_attr( $acf_taxonomy['import_source'] ); ?>" />
		<input type="hidden" name="acf_taxonomy[import_date]" value="<?php echo esc_attr( $acf_taxonomy['import_date'] ); ?>" />
	</div>
<?php
