<?php 
/**
 * The template for displaying admin navigation. 
 *
 * @date	27/3/20
 * @since	5.9.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

global $submenu, $parent_file, $submenu_file, $plugin_page, $pagenow;

// Vars.
$parent_slug = 'edit.php?post_type=acf-field-group';

// Generate array of navigation items.
$tabs = array();
if( isset($submenu[ $parent_slug ]) ) {
	foreach( $submenu[ $parent_slug ] as $i => $sub_item ) {
		
		// Check user can access page.
		if ( !current_user_can( $sub_item[1] ) ) {
			continue;
		}
		
		// Ignore "Add New".
		if( $i === 1 ) {
			continue;
		}
		
		// Define tab.
		$tab = array(
			'text'	=> $sub_item[0],
			'url' => $sub_item[2]
		);
		
		// Convert submenu slug "test" to "$parent_slug&page=test".
		if( !strpos($sub_item[2], '.php') ) {
			$tab['url'] = add_query_arg( array( 'page' => $sub_item[2] ), $parent_slug );
		}
		
		// Detect active state.
		if( $submenu_file === $sub_item[2] || $plugin_page === $sub_item[2] ) {
			$tab['is_active'] = true;
		}
		
		// Special case for "Add New" page.
		if( $i === 0 && $submenu_file === 'post-new.php?post_type=acf-field-group' ) {
			$tab['is_active'] = true;
		}
		$tabs[] = $tab;
	}
}

/**
 * Filters the admin navigation tabs.
 *
 * @date	27/3/20
 * @since	5.9.0
 *
 * @param	array $tabs The array of navigation tabs.
 */
$tabs = apply_filters( 'acf/admin/toolbar', $tabs );

// Bail early if set to false.
if( $tabs === false ) {
	return;
}

?>
<div class="acf-admin-toolbar">
	<h2><i class="acf-tab-icon dashicons dashicons-welcome-widgets-menus"></i> <?php echo acf_get_setting('name'); ?></h2>
	<?php foreach( $tabs as $tab ) {
		printf(
			'<a class="acf-tab%s" href="%s">%s</a>',
			!empty( $tab['is_active'] ) ? ' is-active' : '',
			esc_url( $tab['url'] ),
			acf_esc_html( $tab['text'] )
		);
	} ?>

    <?php if ( ! defined( 'ACF_PRO' ) || ! ACF_PRO ) : ?>
        <a href="https://www.advancedcustomfields.com/pro/?utm_source=ACF%2BFree&utm_medium=insideplugin&utm_campaign=ACF%2Bupgrade" class="btn-upgrade">
            <img src="<?php echo acf_get_url('assets/images/icon-upgrade-pro.svg' ); ?>" />
            <p><?php _e('Upgrade to Pro', 'acf'); ?></p>
        </a>
    <?php endif; ?>

</div>