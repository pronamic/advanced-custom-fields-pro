<?php
/**
 * The template for displaying admin navigation.
 *
 * @date    27/3/20
 * @since   5.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $submenu, $parent_file, $submenu_file, $plugin_page, $pagenow, $acf_page_title;

// Vars.
$parent_slug = 'edit.php?post_type=acf-field-group';

// Generate array of navigation items.
$tabs = array();
if ( isset( $submenu[ $parent_slug ] ) ) {
	foreach ( $submenu[ $parent_slug ] as $i => $sub_item ) {

		// Check user can access page.
		if ( ! current_user_can( $sub_item[1] ) ) {
			continue;
		}

		// Ignore "Add New".
		if ( $i === 1 ) {
			continue;
		}

		// Define tab.
		$tab = array(
			'text' => $sub_item[0],
			'url'  => $sub_item[2],
		);

		// Convert submenu slug "test" to "$parent_slug&page=test".
		if ( ! strpos( $sub_item[2], '.php' ) ) {
			$tab['url']   = add_query_arg( array( 'page' => $sub_item[2] ), $parent_slug );
			$tab['class'] = $sub_item[2];
		} else {
			// Build class from URL.
			$tab['class'] = str_replace( 'edit.php?post_type=', '', $sub_item[2] );
		}

		// Detect active state.
		if ( $submenu_file === $sub_item[2] || $plugin_page === $sub_item[2] ) {
			$tab['is_active'] = true;
		}

		// Special case for "Add New" page.
		if ( $i === 0 && $submenu_file === 'post-new.php?post_type=acf-field-group' ) {
			$tab['is_active'] = true;
		}
		$tabs[] = $tab;
	}
}

/**
 * Filters the admin navigation tabs.
 *
 * @date    27/3/20
 * @since   5.9.0
 *
 * @param   array $tabs The array of navigation tabs.
 */
$tabs = apply_filters( 'acf/admin/toolbar', $tabs );

// Bail early if set to false.
if ( $tabs === false ) {
	return;
}

?>
<div class="acf-admin-toolbar">

	<a href="<?php echo admin_url( 'edit.php?post_type=acf-field-group' ); ?>" class="acf-logo"><img src="<?php echo acf_get_url( 'assets/images/acf-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Advanced Custom Fields logo', 'acf' ); ?>"></a>
	<h2><?php echo acf_get_setting( 'name' ); ?></h2>
	<?php
	foreach ( $tabs as $tab ) {
		$classname = ! empty( $tab['class'] ) ? $tab['class'] : $tab['text'];
		printf(
			'<a class="acf-tab%s %s" href="%s"><i class="acf-icon"></i>%s</a>',
			! empty( $tab['is_active'] ) ? ' is-active' : '',
			'acf-header-tab-' . acf_slugify( $classname ),
			esc_url( $tab['url'] ),
			acf_esc_html( $tab['text'] )
		);
	}
	?>

	<?php if ( ! defined( 'ACF_PRO' ) || ! ACF_PRO ) : ?>
	<a target="_blank" href="<?php echo acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'header' ); ?>" class="btn-upgrade acf-admin-toolbar-upgrade-btn">
		<i class="acf-icon acf-icon-stars"></i>
		<p><?php _e( 'Unlock Extra Features with ACF PRO', 'acf' ); ?></p>
	</a>
	<?php endif; ?>

</div>

<?php

global $plugin_page;
$screen = get_current_screen();
if ( $screen->id !== 'acf-field-group' ) {
	if ( $plugin_page == 'acf-tools' ) {
		$acf_page_title = __( 'Tools', 'acf' );
	} elseif ( $plugin_page == 'acf-settings-updates' ) {
		$acf_page_title = __( 'Updates', 'acf' );
	}
	acf_get_view( 'html-admin-acf-header' );
}
?>
