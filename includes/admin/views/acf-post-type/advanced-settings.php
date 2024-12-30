<?php

global $acf_post_type;

foreach ( acf_get_combined_post_type_settings_tabs() as $tab_key => $tab_label ) {
	acf_render_field_wrap(
		array(
			'type'  => 'tab',
			'label' => $tab_label,
			'key'   => 'acf_post_type_tabs',
		)
	);

	$wrapper_class = str_replace( '_', '-', $tab_key );

	echo '<div class="acf-post-type-advanced-settings acf-post-type-' . esc_attr( $wrapper_class ) . '-settings">';

	switch ( $tab_key ) {
		case 'general':
			$acf_available_supports = array(
				'title'           => __( 'Title', 'acf' ),
				'author'          => __( 'Author', 'acf' ),
				'comments'        => __( 'Comments', 'acf' ),
				'trackbacks'      => __( 'Trackbacks', 'acf' ),
				'editor'          => __( 'Editor', 'acf' ),
				'excerpt'         => __( 'Excerpt', 'acf' ),
				'revisions'       => __( 'Revisions', 'acf' ),
				'page-attributes' => __( 'Page Attributes', 'acf' ),
				'thumbnail'       => __( 'Featured Image', 'acf' ),
				'custom-fields'   => __( 'Custom Fields', 'acf' ),
				'post-formats'    => __( 'Post Formats', 'acf' ),
			);
			$acf_available_supports = apply_filters( 'acf/post_type/available_supports', $acf_available_supports, $acf_post_type );
			$acf_selected_supports  = is_array( $acf_post_type['supports'] ) ? $acf_post_type['supports'] : array();

			acf_render_field_wrap(
				array(
					'type'                      => 'checkbox',
					'name'                      => 'supports',
					'key'                       => 'supports',
					'label'                     => __( 'Supports', 'acf' ),
					'instructions'              => __( 'Enable various features in the content editor.', 'acf' ),
					'prefix'                    => 'acf_post_type',
					'value'                     => array_unique( array_filter( $acf_selected_supports ) ),
					'choices'                   => $acf_available_supports,
					'allow_custom'              => true,
					'class'                     => 'acf_post_type_supports',
					'custom_choice_button_text' => __( 'Add Custom', 'acf' ),
				),
				'div'
			);

			acf_render_field_wrap( array( 'type' => 'seperator' ) );

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'description',
					'key'          => 'description',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['description'],
					'label'        => __( 'Description', 'acf' ),
					'instructions' => __( 'A descriptive summary of the post type.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap( array( 'type' => 'seperator' ) );

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'active',
					'key'          => 'active',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['active'],
					'label'        => __( 'Active', 'acf' ),
					'instructions' => __( 'Active post types are enabled and registered with WordPress.', 'acf' ),
					'ui'           => true,
					'default'      => 1,
				)
			);

			break;
		case 'labels':
			echo '<div class="acf-field acf-regenerate-labels-bar">';
			echo '<span class="acf-btn acf-btn-sm acf-btn-clear acf-regenerate-labels"><i class="acf-icon acf-icon-regenerate"></i>' . esc_html__( 'Regenerate', 'acf' ) . '</span>';
			echo '<span class="acf-btn acf-btn-sm acf-btn-clear acf-clear-labels"><i class="acf-icon acf-icon-trash"></i>' . esc_html__( 'Clear', 'acf' ) . '</span>';
			echo '<span class="acf-tip acf-labels-tip"><i class="acf-icon acf-icon-help acf-js-tooltip" title="' . esc_attr__( 'Regenerate all labels using the Singular and Plural labels', 'acf' ) . '"></i></span>';
			echo '</div>';

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'menu_name',
					'key'          => 'menu_name',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['menu_name'],
					'data'         => array(
						'label'   => '%s',
						'replace' => 'plural',
					),
					'label'        => __( 'Menu Name', 'acf' ),
					'instructions' => __( 'Admin menu name for the post type.', 'acf' ),
					'placeholder'  => __( 'Posts', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'all_items',
					'key'          => 'all_items',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['all_items'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'   => __( 'All %s', 'acf' ),
						'replace' => 'plural',
					),
					'label'        => __( 'All Items', 'acf' ),
					'instructions' => __( 'In the post type submenu in the admin dashboard.', 'acf' ),
					'placeholder'  => __( 'All Posts', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'edit_item',
					'key'          => 'edit_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['edit_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'Edit %s', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Edit Item', 'acf' ),
					'instructions' => __( 'At the top of the editor screen when editing an item.', 'acf' ),
					'placeholder'  => __( 'Edit Post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'view_item',
					'key'          => 'view_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['view_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'View %s', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'View Item', 'acf' ),
					'instructions' => __( 'In the admin bar to view item when editing it.', 'acf' ),
					'placeholder'  => __( 'View Post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'view_items',
					'key'          => 'view_items',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['view_items'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'   => __( 'View %s', 'acf' ),
						'replace' => 'plural',
					),
					'label'        => __( 'View Items', 'acf' ),
					'instructions' => __( 'Appears in the admin bar in the \'All Posts\' view, provided the post type supports archives and the home page is not an archive of that post type.', 'acf' ),
					'placeholder'  => __( 'View Posts', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'add_new_item',
					'key'          => 'add_new_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['add_new_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'Add New %s', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Add New Item', 'acf' ),
					'instructions' => __( 'At the top of the editor screen when adding a new item.', 'acf' ),
					'placeholder'  => __( 'Add New Post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'add_new',
					'key'          => 'add_new',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['add_new'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'Add New %s', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Add New', 'acf' ),
					'instructions' => __( 'In the post type submenu in the admin dashboard.', 'acf' ),
					'placeholder'  => __( 'Add New Post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'new_item',
					'key'          => 'new_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['new_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'New %s', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'New Item', 'acf' ),
					'instructions' => __( 'In the post type submenu in the admin dashboard.', 'acf' ),
					'placeholder'  => __( 'New Post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'parent_item_colon',
					'key'          => 'parent_item_colon',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['parent_item_colon'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'Parent %s:', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Parent Item Prefix', 'acf' ),
					'instructions' => __( 'For hierarchical types in the post type list screen.', 'acf' ),
					'placeholder'  => __( 'Parent Page:', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'search_items',
					'key'          => 'search_items',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['search_items'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( 'Search %s', 'acf' ),
						'replace' => 'plural',
					),
					'label'        => __( 'Search Items', 'acf' ),
					'instructions' => __( 'At the top of the items screen when searching for an item.', 'acf' ),
					'placeholder'  => __( 'Search Posts', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'not_found',
					'key'          => 'not_found',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['not_found'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'     => __( 'No %s found', 'acf' ),
						'replace'   => 'plural',
						'transform' => 'lower',
					),
					'label'        => __( 'No Items Found', 'acf' ),
					'instructions' => __( 'At the top of the post type list screen when there are no posts to display.', 'acf' ),
					'placeholder'  => __( 'No posts found', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'not_found_in_trash',
					'key'          => 'not_found_in_trash',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['not_found_in_trash'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'     => __( 'No %s found in Trash', 'acf' ),
						'replace'   => 'plural',
						'transform' => 'lower',
					),
					'label'        => __( 'No Items Found in Trash', 'acf' ),
					'instructions' => __( 'At the top of the post type list screen when there are no posts in the trash.', 'acf' ),
					'placeholder'  => __( 'No posts found in Trash', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'archives',
					'key'          => 'archives',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['archives'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s Archives', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Archives Nav Menu', 'acf' ),
					'instructions' => __( "Adds 'Post Type Archive' items with this label to the list of posts shown when adding items to an existing menu in a CPT with archives enabled. Only appears when editing menus in 'Live Preview' mode and a custom archive slug has been provided.", 'acf' ),
					'placeholder'  => __( 'Post Archives', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'attributes',
					'key'          => 'attributes',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['attributes'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s Attributes', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Attributes Meta Box', 'acf' ),
					'instructions' => __( 'In the editor used for the title of the post attributes meta box.', 'acf' ),
					'placeholder'  => __( 'Post Attributes', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'featured_image',
					'key'          => 'featured_image',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['featured_image'],
					'label'        => __( 'Featured Image Meta Box', 'acf' ),
					'instructions' => __( 'In the editor used for the title of the featured image meta box.', 'acf' ),
					'placeholder'  => __( 'Featured image', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'set_featured_image',
					'key'          => 'set_featured_image',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['set_featured_image'],
					'label'        => __( 'Set Featured Image', 'acf' ),
					'instructions' => __( 'As the button label when setting the featured image.', 'acf' ),
					'placeholder'  => __( 'Set featured image', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'remove_featured_image',
					'key'          => 'remove_featured_image',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['remove_featured_image'],
					'label'        => __( 'Remove Featured Image', 'acf' ),
					'instructions' => __( 'As the button label when removing the featured image.', 'acf' ),
					'placeholder'  => __( 'Remove featured image', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'use_featured_image',
					'key'          => 'use_featured_image',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['use_featured_image'],
					'label'        => __( 'Use Featured Image', 'acf' ),
					'instructions' => __( 'As the button label for selecting to use an image as the featured image.', 'acf' ),
					'placeholder'  => __( 'Use as featured image', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'insert_into_item',
					'key'          => 'insert_into_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['insert_into_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'     => __( 'Insert into %s', 'acf' ),
						'replace'   => 'singular',
						'transform' => 'lower',
					),
					'label'        => __( 'Insert Into Media Button', 'acf' ),
					'instructions' => __( 'As the button label when adding media to content.', 'acf' ),
					'placeholder'  => __( 'Insert into post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'uploaded_to_this_item',
					'key'          => 'uploaded_to_this_item',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['uploaded_to_this_item'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'     => __( 'Uploaded to this %s', 'acf' ),
						'replace'   => 'singular',
						'transform' => 'lower',
					),
					'label'        => __( 'Uploaded To This Item', 'acf' ),
					'instructions' => __( 'In the media modal showing all media uploaded to this item.', 'acf' ),
					'placeholder'  => __( 'Uploaded to this post', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'filter_items_list',
					'key'          => 'filter_items_list',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['filter_items_list'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'     => __( 'Filter %s list', 'acf' ),
						'replace'   => 'plural',
						'transform' => 'lower',
					),
					'label'        => __( 'Filter Items List', 'acf' ),
					'instructions' => __( 'Used by screen readers for the filter links heading on the post type list screen.', 'acf' ),
					'placeholder'  => __( 'Filter posts list', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'filter_by_date',
					'key'          => 'filter_by_date',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['filter_by_date'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'     => __( 'Filter %s by date', 'acf' ),
						'replace'   => 'plural',
						'transform' => 'lower',
					),
					'label'        => __( 'Filter Items By Date', 'acf' ),
					'instructions' => __( 'Used by screen readers for the filter by date heading on the post type list screen.', 'acf' ),
					'placeholder'  => __( 'Filter posts by date', 'acf' ),
				),
				'div',
				'field'
			);


			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'items_list_navigation',
					'key'          => 'items_list_navigation',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['items_list_navigation'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'   => __( '%s list navigation', 'acf' ),
						'replace' => 'plural',
					),
					'label'        => __( 'Items List Navigation', 'acf' ),
					'instructions' => __( 'Used by screen readers for the filter list pagination on the post type list screen.', 'acf' ),
					'placeholder'  => __( 'Posts list navigation', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'items_list',
					'key'          => 'items_list',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['items_list'],
					'data'         => array(
						/* translators: %s Plural form of post type name */
						'label'   => __( '%s list', 'acf' ),
						'replace' => 'plural',
					),
					'label'        => __( 'Items List', 'acf' ),
					'instructions' => __( 'Used by screen readers for the items list on the post type list screen.', 'acf' ),
					'placeholder'  => __( 'Posts list', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_published',
					'key'          => 'item_published',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_published'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s published.', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Published', 'acf' ),
					'instructions' => __( 'In the editor notice after publishing an item.', 'acf' ),
					'placeholder'  => __( 'Post published.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_published_privately',
					'key'          => 'item_published_privately',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_published_privately'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s published privately.', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Published Privately', 'acf' ),
					'instructions' => __( 'In the editor notice after publishing a private item.', 'acf' ),
					'placeholder'  => __( 'Post published privately.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_reverted_to_draft',
					'key'          => 'item_reverted_to_draft',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_reverted_to_draft'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s reverted to draft.', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Reverted To Draft', 'acf' ),
					'instructions' => __( 'In the editor notice after reverting an item to draft.', 'acf' ),
					'placeholder'  => __( 'Post reverted to draft.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_scheduled',
					'key'          => 'item_scheduled',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_scheduled'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s scheduled.', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Scheduled', 'acf' ),
					'instructions' => __( 'In the editor notice after scheduling an item.', 'acf' ),
					'placeholder'  => __( 'Post scheduled.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_updated',
					'key'          => 'item_updated',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_updated'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s updated.', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Updated', 'acf' ),
					'instructions' => __( 'In the editor notice after an item is updated.', 'acf' ),
					'placeholder'  => __( 'Post updated.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_link',
					'key'          => 'item_link',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_link'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'   => __( '%s Link', 'acf' ),
						'replace' => 'singular',
					),
					'label'        => __( 'Item Link', 'acf' ),
					'instructions' => __( 'Title for a navigation link block variation.', 'acf' ),
					'placeholder'  => __( 'Post Link', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'item_link_description',
					'key'          => 'item_link_description',
					'prefix'       => 'acf_post_type[labels]',
					'value'        => $acf_post_type['labels']['item_link_description'],
					'data'         => array(
						/* translators: %s Singular form of post type name */
						'label'     => __( 'A link to a %s.', 'acf' ),
						'replace'   => 'singular',
						'transform' => 'lower',
					),
					'label'        => __( 'Item Link Description', 'acf' ),
					'instructions' => __( 'Description for a navigation link block variation.', 'acf' ),
					'placeholder'  => __( 'A link to a post.', 'acf' ),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'enter_title_here',
					'key'          => 'enter_title_here',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['enter_title_here'],
					'label'        => __( 'Title Placeholder', 'acf' ),
					'instructions' => __( 'In the editor used as the placeholder of the title.', 'acf' ),
					'placeholder'  => __( 'Add title', 'acf' ),
				),
				'div',
				'field'
			);

			break;
		case 'visibility':
			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'show_ui',
					'key'          => 'show_ui',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['show_ui'],
					'label'        => __( 'Show In UI', 'acf' ),
					'instructions' => __( 'Items can be edited and managed in the admin dashboard.', 'acf' ),
					'ui'           => true,
					'default'      => 1,
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'show_in_menu',
					'key'          => 'show_in_menu',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['show_in_menu'],
					'label'        => __( 'Show In Admin Menu', 'acf' ),
					'instructions' => __( 'Admin editor navigation in the sidebar menu.', 'acf' ),
					'ui'           => true,
					'default'      => 1,
					'conditions'   => array(
						'field'    => 'show_ui',
						'operator' => '==',
						'value'    => 1,
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'admin_menu_parent',
					'key'          => 'admin_menu_parent',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['admin_menu_parent'],
					'placeholder'  => 'edit.php?post_type={parent_page}',
					'label'        => __( 'Admin Menu Parent', 'acf' ),
					'instructions' => __( 'By default the post type will get a new top level item in the admin menu. If an existing top level item is supplied here, the post type will be added as a submenu item under it.', 'acf' ),
					'conditions'   => array(
						'field'    => 'show_in_menu',
						'operator' => '==',
						'value'    => 1,
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'number',
					'name'         => 'menu_position',
					'key'          => 'menu_position',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['menu_position'],
					'label'        => __( 'Menu Position', 'acf' ),
					'instructions' => __( 'The position in the sidebar menu in the admin dashboard.', 'acf' ),
					'conditions'   => array(
						'field'    => 'show_in_menu',
						'operator' => '==',
						'value'    => 1,
					),
				),
				'div',
				'field'
			);

			// Set the default value for the icon field.
			$acf_default_icon_value = array(
				'type'  => 'dashicons',
				'value' => 'dashicons-admin-post',
			);

			if ( empty( $acf_post_type['menu_icon'] ) ) {
				$acf_post_type['menu_icon'] = $acf_default_icon_value;
			}

			// Backwards compatibility for before the icon picker was introduced.
			if ( is_string( $acf_post_type['menu_icon'] ) ) {
				// If the old value was a string that starts with dashicons-, assume it's a dashicon.
				if ( false !== strpos( $acf_post_type['menu_icon'], 'dashicons-' ) ) {
					$acf_post_type['menu_icon'] = array(
						'type'  => 'dashicons',
						'value' => $acf_post_type['menu_icon'],
					);
				} else {
					$acf_post_type['menu_icon'] = array(
						'type'  => 'url',
						'value' => $acf_post_type['menu_icon'],
					);
				}
			}

			acf_render_field_wrap(
				array(
					'type'        => 'icon_picker',
					'name'        => 'menu_icon',
					'key'         => 'menu_icon',
					'prefix'      => 'acf_post_type',
					'value'       => $acf_post_type['menu_icon'],
					'label'       => __( 'Menu Icon', 'acf' ),
					'placeholder' => 'dashicons-admin-post',
					'conditions'  => array(
						array(
							'field'    => 'show_in_menu',
							'operator' => '==',
							'value'    => '1',
						),
						array(
							'field'    => 'admin_menu_parent',
							'operator' => '==',
							'value'    => '',
						),
					),
				),
				'div',
				'field'
			);

			$acf_enable_meta_box_cb_edit  = acf_get_setting( 'enable_meta_box_cb_edit' );
			$acf_meta_box_cb_instructions = __( 'A PHP function name to be called when setting up the meta boxes for the edit screen. For security, this callback will be executed in a special context without access to any superglobals like $_POST or $_GET.', 'acf' );

			// Only show if user is allowed to update, or if it already has a value.
			if ( $acf_enable_meta_box_cb_edit || ! empty( $acf_post_type['register_meta_box_cb'] ) ) {
				if ( ! $acf_enable_meta_box_cb_edit ) {
					if ( is_multisite() ) {
						$acf_meta_box_cb_instructions .= ' ' . __( 'By default only super admin users can edit this setting.', 'acf' );
					} else {
						$acf_meta_box_cb_instructions .= ' ' . __( 'By default only admin users can edit this setting.', 'acf' );
					}
				}

				acf_render_field_wrap(
					array(
						'type'         => 'text',
						'name'         => 'register_meta_box_cb',
						'key'          => 'register_meta_box_cb',
						'prefix'       => 'acf_post_type',
						'value'        => $acf_post_type['register_meta_box_cb'],
						'label'        => __( 'Custom Meta Box Callback', 'acf' ),
						'instructions' => $acf_meta_box_cb_instructions,
						'readonly'     => ! $acf_enable_meta_box_cb_edit,
						'conditions'   => array(
							'field'    => 'show_ui',
							'operator' => '==',
							'value'    => '1',
						),
					),
					'div',
					'field'
				);
			}

			acf_render_field_wrap(
				array(
					'type'       => 'seperator',
					'conditions' => array(
						'field'    => 'show_ui',
						'operator' => '==',
						'value'    => 1,
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'show_in_admin_bar',
					'key'          => 'show_in_admin_bar',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['show_in_admin_bar'],
					'label'        => __( 'Show In Admin Bar', 'acf' ),
					'instructions' => __( "Appears as an item in the 'New' menu in the admin bar.", 'acf' ),
					'ui'           => true,
					'default'      => 1,
					'conditions'   => array(
						'field'    => 'show_ui',
						'operator' => '==',
						'value'    => 1,
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'show_in_nav_menus',
					'key'          => 'show_in_nav_menus',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['show_in_nav_menus'],
					'label'        => __( 'Appearance Menus Support', 'acf' ),
					'instructions' => __( "Allow items to be added to menus in the 'Appearance' > 'Menus' screen. Must be turned on in 'Screen options'.", 'acf' ),
					'ui'           => true,
					'default'      => 1,
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'exclude_from_search',
					'key'          => 'exclude_from_search',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['exclude_from_search'],
					'label'        => __( 'Exclude From Search', 'acf' ),
					'instructions' => __( 'Sets whether posts should be excluded from search results and taxonomy archive pages.', 'acf' ),
					'ui'           => true,
				)
			);

			break;
		case 'permissions':
			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'rename_capabilities',
					'key'          => 'rename_capabilities',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['rename_capabilities'],
					'label'        => __( 'Rename Capabilities', 'acf' ),
					'instructions' => __( "By default the capabilities of the post type will inherit the 'Post' capability names, eg. edit_post, delete_posts. Enable to use post type specific capabilities, eg. edit_{singular}, delete_{plural}.", 'acf' ),
					'default'      => false,
					'ui'           => true,
				),
				'div'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'singular_capability_name',
					'key'          => 'singular_capability_name',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['singular_capability_name'],
					'label'        => __( 'Singular Capability Name', 'acf' ),
					'instructions' => __( 'Choose another post type to base the capabilities for this post type.', 'acf' ),
					'conditions'   => array(
						'field'    => 'rename_capabilities',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'plural_capability_name',
					'key'          => 'plural_capability_name',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['plural_capability_name'],
					'label'        => __( 'Plural Capability Name', 'acf' ),
					'instructions' => __( 'Optionally provide a plural to be used in capabilities.', 'acf' ),
					'conditions'   => array(
						'field'    => 'rename_capabilities',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'       => 'seperator',
					'conditions' => array(
						'field'    => 'rename_capabilities',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'can_export',
					'key'          => 'can_export',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['can_export'],
					'label'        => __( 'Can Export', 'acf' ),
					'instructions' => __( "Allow the post type to be exported from 'Tools' > 'Export'.", 'acf' ),
					'default'      => 1,
					'ui'           => 1,
				),
				'div'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'delete_with_user',
					'key'          => 'delete_with_user',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['delete_with_user'],
					'label'        => __( 'Delete With User', 'acf' ),
					'instructions' => __( 'Delete items by a user when that user is deleted.', 'acf' ),
					'ui'           => 1,
				),
				'div'
			);
			break;
		case 'urls':
			acf_render_field_wrap(
				array(
					'type'         => 'select',
					'name'         => 'permalink_rewrite',
					'key'          => 'permalink_rewrite',
					'prefix'       => 'acf_post_type[rewrite]',
					'value'        => isset( $acf_post_type['rewrite']['permalink_rewrite'] ) ? $acf_post_type['rewrite']['permalink_rewrite'] : 'post_type_key',
					'label'        => __( 'Permalink Rewrite', 'acf' ),
					/* translators: this string will be appended with the new permalink structure. */
					'instructions' => __( 'Rewrite the URL using the post type key as the slug. Your permalink structure will be', 'acf' ) . ' {slug}.',
					'choices'      => array(
						'post_type_key'    => __( 'Post Type Key', 'acf' ),
						'custom_permalink' => __( 'Custom Permalink', 'acf' ),
						'no_permalink'     => __( 'No Permalink (prevent URL rewriting)', 'acf' ),
					),
					'default'      => 'post_type_key',
					'hide_search'  => true,
					'data'         => array(
						/* translators: this string will be appended with the new permalink structure. */
						'post_type_key_instructions'    => __( 'Rewrite the URL using the post type key as the slug. Your permalink structure will be', 'acf' ) . ' {slug}.',
						/* translators: this string will be appended with the new permalink structure. */
						'custom_permalink_instructions' => __( 'Rewrite the URL using a custom slug defined in the input below. Your permalink structure will be', 'acf' ) . ' {slug}.',
						'no_permalink_instructions'     => __( 'Permalinks for this post type are disabled.', 'acf' ),
						'site_url'                      => get_site_url(),
					),
					'class'        => 'permalink_rewrite',
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'slug',
					'key'          => 'slug',
					'prefix'       => 'acf_post_type[rewrite]',
					'value'        => isset( $acf_post_type['rewrite']['slug'] ) ? $acf_post_type['rewrite']['slug'] : '',
					'label'        => __( 'URL Slug', 'acf' ),
					'instructions' => __( 'Customize the slug used in the URL.', 'acf' ),
					'conditions'   => array(
						'field'    => 'permalink_rewrite',
						'operator' => '==',
						'value'    => 'custom_permalink',
					),
					'class'        => 'rewrite_slug_field',
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'with_front',
					'key'          => 'with_front',
					'prefix'       => 'acf_post_type[rewrite]',
					'value'        => isset( $acf_post_type['rewrite']['with_front'] ) ? $acf_post_type['rewrite']['with_front'] : true,
					'label'        => __( 'Front URL Prefix', 'acf' ),
					'instructions' => __( 'Alters the permalink structure to add the `WP_Rewrite::$front` prefix to URLs.', 'acf' ),
					'ui'           => true,
					'default'      => 1,
					'conditions'   => array(
						'field'    => 'permalink_rewrite',
						'operator' => '!=',
						'value'    => 'no_permalink',
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'feeds',
					'key'          => 'feeds',
					'prefix'       => 'acf_post_type[rewrite]',
					'value'        => isset( $acf_post_type['rewrite']['feeds'] ) ? $acf_post_type['rewrite']['feeds'] : $acf_post_type['has_archive'],
					'label'        => __( 'Feed URL', 'acf' ),
					'instructions' => __( 'RSS feed URL for the post type items.', 'acf' ),
					'ui'           => true,
					'conditions'   => array(
						'field'    => 'permalink_rewrite',
						'operator' => '!=',
						'value'    => 'no_permalink',
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'pages',
					'key'          => 'pages',
					'prefix'       => 'acf_post_type[rewrite]',
					'value'        => isset( $acf_post_type['rewrite']['pages'] ) ? $acf_post_type['rewrite']['pages'] : true,
					'label'        => __( 'Pagination', 'acf' ),
					'instructions' => __( 'Pagination support for the items URLs such as the archives.', 'acf' ),
					'ui'           => true,
					'default'      => 1,
					'conditions'   => array(
						'field'    => 'permalink_rewrite',
						'operator' => '!=',
						'value'    => 'no_permalink',
					),
				)
			);

			acf_render_field_wrap( array( 'type' => 'seperator' ) );

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'has_archive',
					'key'          => 'has_archive',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['has_archive'],
					'label'        => __( 'Archive', 'acf' ),
					'instructions' => __( 'Has an item archive that can be customized with an archive template file in your theme.', 'acf' ),
					'ui'           => true,
				),
				'div'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'has_archive_slug',
					'key'          => 'has_archive_slug',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['has_archive_slug'],
					'label'        => __( 'Archive Slug', 'acf' ),
					'instructions' => __( 'Custom slug for the Archive URL.', 'acf' ),
					'ui'           => true,
					'conditions'   => array(
						'field'    => 'has_archive',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap( array( 'type' => 'seperator' ) );

			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'publicly_queryable',
					'key'          => 'publicly_queryable',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['publicly_queryable'],
					'label'        => __( 'Publicly Queryable', 'acf' ),
					'instructions' => __( 'URLs for an item and items can be accessed with a query string.', 'acf' ),
					'default'      => 1,
					'ui'           => true,
				),
				'div'
			);

			acf_render_field_wrap(
				array(
					'type'       => 'seperator',
					'conditions' => array(
						'field'    => 'publicly_queryable',
						'operator' => '==',
						'value'    => 1,
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'select',
					'name'         => 'query_var',
					'key'          => 'query_var',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['query_var'],
					'label'        => __( 'Query Variable Support', 'acf' ),
					'instructions' => __( 'Items can be accessed using the non-pretty permalink, eg. {post_type}={post_slug}.', 'acf' ),
					'choices'      => array(
						'post_type_key'    => __( 'Post Type Key', 'acf' ),
						'custom_query_var' => __( 'Custom Query Variable', 'acf' ),
						'none'             => __( 'No Query Variable Support', 'acf' ),
					),
					'default'      => 1,
					'hide_search'  => true,
					'class'        => 'query_var',
					'conditions'   => array(
						'field'    => 'publicly_queryable',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'query_var_name',
					'key'          => 'query_var_name',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['query_var_name'],
					'label'        => __( 'Query Variable', 'acf' ),
					'instructions' => __( 'Customize the query variable name.', 'acf' ),
					'ui'           => true,
					'conditions'   => array(
						'field'    => 'query_var',
						'operator' => '==',
						'value'    => 'custom_query_var',
					),
				),
				'div',
				'field'
			);

			break;
		case 'rest_api':
			acf_render_field_wrap(
				array(
					'type'         => 'true_false',
					'name'         => 'show_in_rest',
					'key'          => 'show_in_rest',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['show_in_rest'],
					'label'        => __( 'Show In REST API', 'acf' ),
					'instructions' => __( 'Exposes this post type in the REST API. Required to use the block editor.', 'acf' ),
					'default'      => 1,
					'ui'           => true,
				),
				'div'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'rest_base',
					'key'          => 'rest_base',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['rest_base'],
					'label'        => __( 'Base URL', 'acf' ),
					'instructions' => __( 'The base URL for the post type REST API URLs.', 'acf' ),
					'conditions'   => array(
						'field'    => 'show_in_rest',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'rest_namespace',
					'key'          => 'rest_namespace',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['rest_namespace'],
					'label'        => __( 'Namespace Route', 'acf' ),
					'instructions' => __( 'The namespace part of the REST API URL.', 'acf' ),
					'conditions'   => array(
						'field'    => 'show_in_rest',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'rest_controller_class',
					'key'          => 'rest_controller_class',
					'prefix'       => 'acf_post_type',
					'value'        => $acf_post_type['rest_controller_class'],
					'label'        => __( 'Controller Class', 'acf' ),
					'instructions' => __( 'Optional custom controller to use instead of `WP_REST_Posts_Controller`.', 'acf' ),
					'default'      => 'WP_REST_Posts_Controller',
					'conditions'   => array(
						'field'    => 'show_in_rest',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'div',
				'field'
			);
			break;
	}

	do_action( "acf/post_type/render_settings_tab/{$tab_key}", $acf_post_type );

	echo '</div>';
}
