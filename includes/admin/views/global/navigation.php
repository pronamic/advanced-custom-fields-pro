<?php
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included template file.
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
$more_items = array();
$core_tabs  = array();
if ( isset( $submenu[ $parent_slug ] ) ) {
	foreach ( $submenu[ $parent_slug ] as $i => $sub_item ) {

		// Check user can access page.
		if ( ! current_user_can( $sub_item[1] ) ) {
			continue;
		}

		// Define tab.
		$menu_item = array(
			'text' => $sub_item[0],
			'url'  => $sub_item[2],
		);

		// Convert submenu slug "test" to "$parent_slug&page=test".
		if ( ! strpos( $sub_item[2], '.php' ) ) {
			$menu_item['url']   = add_query_arg( array( 'page' => $sub_item[2] ), $parent_slug );
			$menu_item['class'] = $sub_item[2];
		} else {
			// Build class from URL.
			$menu_item['class'] = str_replace( 'edit.php?post_type=', '', $sub_item[2] );
		}

		// Detect active state.
		if ( $submenu_file === $sub_item[2] || $plugin_page === $sub_item[2] ) {
			$menu_item['is_active'] = true;
		}

		// Handle "Add New" versions of edit page.
		if ( str_replace( 'edit', 'post-new', $sub_item[2] ) === $submenu_file ) {
			$menu_item['is_active'] = true;
		}

		// Flag core tabs.
		$core_tabs_classes = array_merge( acf_get_internal_post_types(), array( 'acf-tools', 'acf-settings-updates' ) );
		if ( in_array( $menu_item['class'], $core_tabs_classes, true ) ) {
			$core_tabs[] = $menu_item;
		} else {
			$more_items[] = $menu_item;
		}
	}
}

/**
 * Filters the admin navigation more items.
 *
 * @since   5.9.0
 *
 * @param   array $more_items The array of navigation tabs.
 */
$more_items = apply_filters( 'acf/admin/toolbar', $more_items );

// Bail early if set to false.
if ( $core_tabs === false ) {
	return;
}

$acf_is_free            = ! defined( 'ACF_PRO' ) || ! ACF_PRO;
$acf_wpengine_logo_link = acf_add_url_utm_tags(
	'https://wpengine.com/',
	'bx_prod_referral',
	$acf_is_free ? 'acf_free_plugin_topbar_logo' : 'acf_pro_plugin_topbar_logo',
	false,
	'acf_plugin',
	'referral'
);

?>
<div class="acf-admin-toolbar">
	<div class="acf-admin-toolbar-inner">
		<div class="acf-nav-wrap">
			<a href="<?php echo admin_url( 'edit.php?post_type=acf-field-group' ); ?>" class="acf-logo">
				<img src="<?php echo acf_get_url( 'assets/images/acf-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Advanced Custom Fields logo', 'acf' ); ?>">
				<?php if ( defined( 'ACF_PRO' ) && ACF_PRO ) { ?>
					<div class="acf-pro-label">PRO</div>
				<?php } ?>
			</a>

			<h2><?php echo acf_get_setting( 'name' ); ?></h2>
			<?php
			foreach ( $core_tabs as $menu_item ) {
				$classname = ! empty( $menu_item['class'] ) ? $menu_item['class'] : $menu_item['text'];
				printf(
					'<a class="acf-tab%s %s" href="%s"><i class="acf-icon"></i>%s</a>',
					! empty( $menu_item['is_active'] ) ? ' is-active' : '',
					'acf-header-tab-' . acf_slugify( $classname ),
					esc_url( $menu_item['url'] ),
					acf_esc_html( $menu_item['text'] )
				);
			}
			?>
			<?php if ( $more_items ) { ?>
				<div class="acf-more acf-header-tab-acf-more" tabindex="0">
					<span class="acf-tab acf-more-tab"><i class="acf-icon acf-icon-more"></i><?php esc_html_e( 'More', 'acf' ); ?> <i class="acf-icon acf-icon-dropdown"></i></span>
					<ul>
						<?php
						foreach ( $more_items as $more_item ) {
							$classname = ! empty( $more_item['class'] ) ? $more_item['class'] : $more_item['text'];
							printf(
								'<li><a class="acf-tab%s %s" href="%s"><i class="acf-icon"></i>%s</a></li>',
								! empty( $more_item['is_active'] ) ? ' is-active' : '',
								'acf-header-tab-' . acf_slugify( $classname ),
								esc_url( $more_item['url'] ),
								acf_esc_html( $more_item['text'] )
							);
						}
						?>
					</ul>
				</div>
			<?php } ?>
		</div>
		<div class="acf-nav-upgrade-wrap">
			<?php if ( $acf_is_free ) : ?>
				<a target="_blank" href="<?php echo acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'header' ); ?>" class="btn-upgrade acf-admin-toolbar-upgrade-btn">
					<i class="acf-icon acf-icon-stars"></i>
					<p><?php esc_html_e( 'Unlock Extra Features with ACF PRO', 'acf' ); ?></p>
				</a>
			<?php endif; ?>
			<a href="<?php echo $acf_wpengine_logo_link; ?>" target="_blank" class="acf-nav-wpengine-logo">
				<img src="<?php echo esc_url( acf_get_url( 'assets/images/wp-engine-horizontal-white.svg' ) ); ?>" alt="<?php esc_html_e( 'WP Engine logo', 'acf' ); ?>" />
			</a>
		</div>
	</div>
</div>

<?php

global $plugin_page;
$screen = get_current_screen();

if ( ! in_array( $screen->id, acf_get_internal_post_types(), true ) ) {
	if ( $plugin_page == 'acf-tools' ) {
		$acf_page_title = __( 'Tools', 'acf' );
	} elseif ( $plugin_page == 'acf-settings-updates' ) {
		$acf_page_title = __( 'Updates', 'acf' );
	}
	acf_get_view( 'global/header' );
}
?>
