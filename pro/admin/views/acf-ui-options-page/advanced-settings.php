<?php

global $acf_ui_options_page;

foreach ( acf_get_combined_options_page_settings_tabs() as $tab_key => $tab_label ) {
	acf_render_field_wrap(
		array(
			'type'  => 'tab',
			'label' => $tab_label,
			'key'   => 'acf_ui_options_page_tabs',
		)
	);

	$wrapper_class = str_replace( '_', '-', $tab_key );

	echo '<div class="acf-ui-options-page-advanced-settings acf-ui-options-page-' . esc_attr( $wrapper_class ) . '-settings">';

	switch ( $tab_key ) {
		case 'visibility':
			$acf_dashicon_class_name = __( 'Dashicon class name', 'acf' );
			$acf_dashicon_link       = '<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">' . $acf_dashicon_class_name . '</a>';

			$acf_menu_icon_instructions = sprintf(
			/* translators: %s = "dashicon class name", link to the WordPress dashicon documentation. */
				__( 'The icon used for the options page menu item in the admin dashboard. Can be a URL or %s to use for the icon.', 'acf' ),
				$acf_dashicon_link
			);

			acf_render_field_wrap(
				array(
					'label'        => __( 'Menu Icon', 'acf' ),
					'type'         => 'text',
					'name'         => 'icon_url',
					'key'          => 'icon_url',
					'class'        => 'acf-options-page-menu_icon',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['icon_url'],
					'instructions' => $acf_menu_icon_instructions,
					'placeholder'  => 'dashicons-admin-generic',
					'conditions'   => array(
						'field'    => 'parent_slug',
						'operator' => '==',
						'value'    => 'none',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'label'  => __( 'Menu Title', 'acf' ),
					'type'   => 'text',
					'name'   => 'menu_title',
					'key'    => 'menu_title',
					'class'  => 'acf-options-page-menu_title',
					'prefix' => 'acf_ui_options_page',
					'value'  => $acf_ui_options_page['menu_title'],
				),
				'div',
				'field'
			);

			$acf_menu_position_link = sprintf(
				'<a href="https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure" target="_blank">%s</a>',
				__( 'Learn more about menu positions.', 'acf' )
			);
			$acf_menu_position_desc = sprintf(
				/* translators: %s - link to WordPress docs to learn more about menu positions. */
				__( 'The position in the menu where this page should appear. %s', 'acf' ),
				$acf_menu_position_link
			);

			$acf_menu_position_desc_parent = sprintf(
				/* translators: %s - link to WordPress docs to learn more about menu positions. */
				__( 'The position in the menu where this page should appear. %s', 'acf' ),
				$acf_menu_position_link
			);

			$acf_menu_position_desc_child = __( 'The position in the menu where this child page should appear. The first child page is 0, the next is 1, etc.', 'acf' );

			$acf_menu_position_desc  = '<span class="acf-menu-position-desc-parent">' . $acf_menu_position_desc_parent . '</span>';
			$acf_menu_position_desc .= '<span class="acf-menu-position-desc-child">' . $acf_menu_position_desc_child . '</span>';

			acf_render_field_wrap(
				array(
					'label'        => __( 'Menu Position', 'acf' ),
					'type'         => 'text',
					'name'         => 'position',
					'key'          => 'position',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['position'],
					'instructions' => $acf_menu_position_desc,
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'label'        => __( 'Redirect to Child Page', 'acf' ),
					'instructions' => __( 'When child pages exist for this parent page, this page will redirect to the first child page.', 'acf' ),
					'type'         => 'true_false',
					'name'         => 'redirect',
					'key'          => 'redirect',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['redirect'],
					'ui'           => 1,
					'default'      => 1,
					'conditions'   => array(
						'field'    => 'parent_slug',
						'operator' => '==',
						'value'    => 'none',
					),
				)
			);

			acf_render_field_wrap(
				array(
					'type'         => 'text',
					'name'         => 'description',
					'key'          => 'description',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['description'],
					'label'        => __( 'Description', 'acf' ),
					'instructions' => __( 'A descriptive summary of the options page.', 'acf' ),
				),
				'div',
				'field'
			);
			break;
		case 'labels':
			acf_render_field_wrap(
				array(
					'label'        => __( 'Update Button Label', 'acf' ),
					'instructions' => __( 'The label used for the submit button which updates the fields on the options page.', 'acf' ),
					'placeholder'  => __( 'Update', 'acf' ),
					'type'         => 'text',
					'name'         => 'update_button',
					'key'          => 'update_button',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['update_button'],
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'label'        => __( 'Updated Message', 'acf' ),
					'instructions' => __( 'The message that is displayed after successfully updating the options page.', 'acf' ),
					'placeholder'  => __( 'Updated Options', 'acf' ),
					'type'         => 'text',
					'name'         => 'updated_message',
					'key'          => 'updated_message',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['updated_message'],
				),
				'div',
				'field'
			);

			break;
		case 'permissions':
			$acf_all_caps = array();

			foreach ( wp_roles()->roles as $acf_role ) {
				$acf_all_caps = array_merge( $acf_all_caps, $acf_role['capabilities'] );
			}

			// Get rid of duplicates and set the keys equal to the values.
			$acf_all_caps = array_unique( array_keys( $acf_all_caps ) );
			$acf_all_caps = array_combine( $acf_all_caps, $acf_all_caps );

			// Move the "edit_posts" to the first select option.
			if ( in_array( 'edit_posts', $acf_all_caps, true ) ) {
				$acf_all_caps = array_diff( $acf_all_caps, array( 'edit_posts' ) );
				$acf_all_caps = array_merge( array( 'edit_posts' => 'edit_posts' ), $acf_all_caps );
			}

			// TODO: Should we AJAX load this? Seems to require UI = true, which breaks our custom template.
			acf_render_field_wrap(
				array(
					'type'         => 'select',
					'name'         => 'capability',
					'key'          => 'capability',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['capability'],
					'label'        => __( 'Capability', 'acf' ),
					'instructions' => __( 'The capability required for this menu to be displayed to the user.', 'acf' ),
					'choices'      => $acf_all_caps,
					'default'      => 'edit_posts',
					'class'        => 'acf-options-page-capability',
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'type'         => 'select',
					'name'         => 'data_storage',
					'key'          => 'data_storage',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['data_storage'],
					'label'        => __( 'Data Storage', 'acf' ),
					'instructions' => __( 'By default, the option page stores field data in the options table. You can make the page load field data from a post, user, or term.', 'acf' ),
					'choices'      => array(
						'options' => __( 'Options', 'acf' ),
						'post_id' => __( 'Custom Storage', 'acf' ),
					),
					'default'      => 'options',
					'hide_search'  => true,
					'class'        => 'acf-options-page-data_storage',
				),
				'div',
				'field'
			);

			$acf_custom_storage_url = acf_add_url_utm_tags(
				'https://www.advancedcustomfields.com/resources/get_field/',
				'docs',
				'options_page_ui',
				'get-a-value-from-different-objects'
			);

			$acf_custom_storage_link = sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				$acf_custom_storage_url,
				__( 'Learn more about available settings.', 'acf' )
			);

			$acf_custom_storage_desc = sprintf(
				/* translators: %s = link to learn more about storage locations. */
				__( 'Set a custom storage location. Can be a numeric post ID (123), or a string (`user_2`). %s', 'acf' ),
				$acf_custom_storage_link
			);

			acf_render_field_wrap(
				array(
					'label'        => __( 'Custom Storage', 'acf' ),
					'instructions' => $acf_custom_storage_desc,
					'type'         => 'text',
					'name'         => 'post_id',
					'key'          => 'post_id',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['post_id'],
					'conditions'   => array(
						'field'    => 'data_storage',
						'operator' => '==',
						'value'    => 'post_id',
					),
				),
				'div',
				'field'
			);

			acf_render_field_wrap(
				array(
					'label'        => __( 'Autoload Options', 'acf' ),
					'instructions' => __( 'Improve performance by loading the fields in the option records automatically when WordPress loads.', 'acf' ),
					'type'         => 'true_false',
					'name'         => 'autoload',
					'key'          => 'autoload',
					'prefix'       => 'acf_ui_options_page',
					'value'        => $acf_ui_options_page['autoload'],
					'ui'           => 1,
					'default'      => 0,
				)
			);
			break;
		default:
	}

	do_action( "acf/ui_options_page/render_settings_tab/{$tab_key}", $acf_ui_options_page );

	echo '</div>';
}
