<?php
global $acf_ui_options_page;

acf_render_field_wrap(
	array(
		'label'       => __( 'Page Title', 'acf' ),
		/* translators: example options page name */
		'placeholder' => __( 'Site Settings', 'acf' ),
		'type'        => 'text',
		'name'        => 'page_title',
		'key'         => 'page_title',
		'class'       => 'acf_options_page_title acf_slugify_to_key',
		'prefix'      => 'acf_ui_options_page',
		'value'       => $acf_ui_options_page['page_title'],
		'required'    => true,
	),
	'div',
	'field'
);

acf_render_field_wrap(
	array(
		'label'    => __( 'Menu Slug', 'acf' ),
		'type'     => 'text',
		'name'     => 'menu_slug',
		'key'      => 'menu_slug',
		'class'    => 'acf-options-page-menu_slug acf_slugified_key',
		'prefix'   => 'acf_ui_options_page',
		'value'    => $acf_ui_options_page['menu_slug'],
		'required' => true,
	),
	'div',
	'field'
);

$acf_all_options_pages   = acf_get_options_pages();
$acf_parent_page_choices = array( 'none' => __( 'No Parent', 'acf' ) );

if ( is_array( $acf_all_options_pages ) ) {
	foreach ( $acf_all_options_pages as $options_page ) {
		// Can't assign to child pages.
		if ( ! empty( $options_page['parent_slug'] ) ) {
			continue;
		}

		$acf_parent_menu_slug = ! empty( $options_page['menu_slug'] ) ? $options_page['menu_slug'] : '';

		// ACF overrides the `menu_slug` of parent pages with one child so they redirect to the child.
		if ( ! empty( $options_page['_menu_slug'] ) ) {
			$acf_parent_menu_slug = $options_page['_menu_slug'];
		}

		// Can't be a child of itself...
		if ( $acf_parent_menu_slug === $acf_ui_options_page['menu_slug'] ) {
			continue;
		}

		$acf_parent_page_choices[ $acf_parent_menu_slug ] = ! empty( $options_page['page_title'] ) ? $options_page['page_title'] : $options_page['menu_slug'];
	}
}

acf_render_field_wrap(
	array(
		'label'    => __( 'Parent Page', 'acf' ),
		'type'     => 'select',
		'name'     => 'parent_slug',
		'key'      => 'parent_slug',
		'class'    => 'acf-options-page-parent_slug',
		'prefix'   => 'acf_ui_options_page',
		'value'    => $acf_ui_options_page['parent_slug'],
		'choices'  => $acf_parent_page_choices,
		'required' => true,
	),
	'div',
	'field'
);

do_action( 'acf/post_type/basic_settings', $acf_ui_options_page );

acf_render_field_wrap( array( 'type' => 'seperator' ) );

acf_render_field_wrap(
	array(
		'label'        => __( 'Advanced Configuration', 'acf' ),
		'instructions' => __( 'I know what I\'m doing, show me all the options.', 'acf' ),
		'type'         => 'true_false',
		'name'         => 'advanced_configuration',
		'key'          => 'advanced_configuration',
		'prefix'       => 'acf_ui_options_page',
		'value'        => $acf_ui_options_page['advanced_configuration'],
		'ui'           => 1,
		'class'        => 'acf-advanced-settings-toggle',
	)
);

?>
	<div class="acf-hidden">
		<input type="hidden" name="acf_ui_options_page[key]" value="<?php echo esc_attr( $acf_ui_options_page['key'] ); ?>" />
	</div>
<?php
