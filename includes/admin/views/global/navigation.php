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

global $submenu, $submenu_file, $plugin_page, $acf_page_title;

// Setup default vars and generate array of navigation items.
$parent_slug         = 'edit.php?post_type=acf-field-group';
$core_tabs           = array();
$acf_more_items      = array();
$more_items          = array();
$wpengine_more_items = array();

// Hardcoded since future ACF post types will likely live in the "More" menu.
$core_tabs_classes      = array( 'acf-field-group', 'acf-post-type', 'acf-taxonomy' );
$acf_more_items_classes = array( 'acf-ui-options-page', 'acf-tools', 'acf-settings-updates' );

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

		// Organize the menu items.
		if ( in_array( $menu_item['class'], $core_tabs_classes, true ) ) {
			// Main ACF tabs.
			$core_tabs[] = $menu_item;

			// Add post types & taxonomies to the more menu as well so we can show them there on smaller screens.
			if ( in_array( $menu_item['class'], array( 'acf-post-type', 'acf-taxonomy' ), true ) ) {
				$acf_more_items[] = $menu_item;
			}
		} elseif ( in_array( $menu_item['class'], $acf_more_items_classes, true ) ) {
			// ACF tabs moved to the "More" menu.
			$acf_more_items[] = $menu_item;
		} else {
			// Third party tabs placed into the "More" menu.
			if ( 'acf_options_preview' === $menu_item['class'] ) {
				continue;
			}

			$more_items[] = $menu_item;
		}
	}
}

if ( ! acf_get_setting( 'pro' ) ) {
	$acf_more_items[] = array(
		'url'    => 'edit.php?post_type=acf-field-group&page=acf_options_preview',
		'text'   => __( 'Options Pages', 'acf' ) . '<span class="acf-requires-pro">' . __( 'PRO', 'acf' ) . '</span>',
		'target' => '_self',
	);
}

if ( ! defined( 'PWP_NAME' ) ) {
	$acf_wpengine_logo     = acf_get_url( 'assets/images/wp-engine-horizontal-black.svg' );
	$acf_wpengine_logo     = sprintf( '<span><img class="acf-wp-engine-pro" src="%s" alt="WP Engine" /></span>', $acf_wpengine_logo );
	$utm_content           = acf_is_pro() ? 'acf_pro_plugin_topbar_dropdown_cta' : 'acf_free_plugin_topbar_dropdown_cta';
	$wpengine_more_items[] = array(
		'url'        => acf_add_url_utm_tags( 'https://wpengine.com/plans/?coupon=freedomtocreate', 'bx_prod_referral', $utm_content, false, 'acf_plugin', 'referral' ),
		'text'       => $acf_wpengine_logo . '<span class="acf-wp-engine-upsell-pill">' . __( '4 Months Free', 'acf' ) . '</span>',
		'target'     => '_blank',
		'li_class'   => 'acf-wp-engine',
		'aria-label' => __( 'Get 4 months free on any WP Engine plan', 'acf' ),
	);
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

$acf_wpengine_logo_link = acf_add_url_utm_tags(
	'https://wpengine.com/',
	'bx_prod_referral',
	acf_is_pro() ? 'acf_pro_plugin_topbar_logo' : 'acf_free_plugin_topbar_logo',
	false,
	'acf_plugin',
	'referral'
);

/**
 * Helper function for looping over the provided menu items
 * and echoing out the necessary markup.
 *
 * @since 6.2
 *
 * @param array  $menu_items An array of menu items to print.
 * @param string $section    The section being printed.
 * @return void
 */
function acf_print_menu_section( $menu_items, $section = '' ) {
	// Bail if no menu items.
	if ( ! is_array( $menu_items ) || empty( $menu_items ) ) {
		return;
	}

	$section_html = '';

	foreach ( $menu_items as $menu_item ) {
		$class      = ! empty( $menu_item['class'] ) ? $menu_item['class'] : $menu_item['text'];
		$target     = ! empty( $menu_item['target'] ) ? ' target="' . esc_attr( $menu_item['target'] ) . '"' : '';
		$aria_label = ! empty( $menu_item['aria-label'] ) ? ' aria-label="' . esc_attr( $menu_item['aria-label'] ) . '"' : '';
		$li_class   = ! empty( $menu_item['li_class'] ) ? esc_attr( $menu_item['li_class'] ) : '';

		$html = sprintf(
			'<a class="acf-tab%s %s" href="%s"%s%s><i class="acf-icon"></i>%s</a>',
			! empty( $menu_item['is_active'] ) ? ' is-active' : '',
			'acf-header-tab-' . esc_attr( acf_slugify( $class ) ),
			esc_url( $menu_item['url'] ),
			$target,
			$aria_label,
			acf_esc_html( $menu_item['text'] )
		);

		if ( 'core' !== $section ) {
			if ( $li_class === '' ) {
				$html = '<li>' . $html . '</li>';
			} else {
				$html = sprintf( '<li class="%s">', $li_class ) . $html . '</li>';
			}
		}

		$section_html .= $html;
	}

	echo $section_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safely built and escaped HTML above.
}
?>
<div class="acf-admin-toolbar">
	<div class="acf-admin-toolbar-inner">
		<div class="acf-nav-wrap">
			<?php if ( acf_is_pro() && acf_pro_is_license_active() ) { ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acf-field-group' ) ); ?>" class="acf-logo pro" aria-label="<?php esc_attr_e( 'Edit ACF Field Groups', 'acf' ); ?>">
					<img src="<?php echo esc_url( acf_get_url( 'assets/images/acf-pro-logo.svg' ) ); ?>" alt="<?php esc_attr_e( 'Advanced Custom Fields logo', 'acf' ); ?>">
				</a>
			<?php } else { ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acf-field-group' ) ); ?>" class="acf-logo" aria-label="<?php esc_attr_e( 'Edit ACF Field Groups', 'acf' ); ?>">
					<img src="<?php echo esc_url( acf_get_url( 'assets/images/acf-logo.svg' ) ); ?>" alt="<?php esc_attr_e( 'Advanced Custom Fields logo', 'acf' ); ?>">
				</a>
			<?php } ?>

			<h2><?php echo esc_html( acf_get_setting( 'name' ) ); ?></h2>
			<?php acf_print_menu_section( $core_tabs, 'core' ); ?>
			<?php if ( $acf_more_items || $more_items ) { ?>
				<div class="acf-more acf-header-tab-acf-more" tabindex="0">
					<span class="acf-tab acf-more-tab"><i class="acf-icon acf-icon-more"></i><?php esc_html_e( 'More', 'acf' ); ?> <i class="acf-icon acf-icon-dropdown"></i></span>
					<ul>
						<?php
						if ( $acf_more_items ) {
							if ( $more_items ) {
								echo '<li class="acf-more-section-header"><span class="acf-tab acf-tab-header">ACF</span></li>';
							}
							acf_print_menu_section( $acf_more_items, 'acf' );
						}
						if ( $more_items ) {
							echo '<li class="acf-more-section-header"><span class="acf-tab acf-tab-header">' . esc_html__( 'Other', 'acf' ) . ' </span></li>';
							acf_print_menu_section( $more_items );
						}
						if ( $wpengine_more_items ) {
							acf_print_menu_section( $wpengine_more_items );
						}
						?>
					</ul>
				</div>
			<?php } ?>
		</div>
		<div class="acf-nav-upgrade-wrap">
			<?php
			if ( ! acf_is_pro() || ! acf_pro_is_license_active() ) {
				$unlock_url    = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'header' );
				$unlock_target = '_blank';
				$unlock_text   = __( 'Unlock Extra Features with ACF PRO', 'acf' );

				if ( acf_is_pro() ) {
					if ( acf_pro_is_updates_page_visible() ) {
						$unlock_url    = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates#acf_pro_license' );
						$unlock_target = '';
					}

					if ( acf_pro_is_license_expired() ) {
						$unlock_url    = acf_add_url_utm_tags( acf_pro_get_manage_license_url(), 'ACF renewal', 'header' );
						$unlock_target = '_blank';
						$unlock_text   = __( 'Renew ACF PRO License', 'acf' );
					}
				}
				?>
				<a target="<?php echo esc_attr( $unlock_target ); ?>" href="<?php echo esc_url( $unlock_url ); ?>" class="btn-upgrade acf-admin-toolbar-upgrade-btn">
					<i class="acf-icon acf-icon-stars"></i>
					<p><?php echo esc_html( $unlock_text ); ?></p>
				</a>
				<?php
			}
			?>
			<a href="<?php echo $acf_wpengine_logo_link; ?>" target="_blank" class="acf-nav-wpengine-logo" aria-label="<?php esc_attr_e( 'WP Engine', 'acf' ); ?>"><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
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
	} elseif ( $plugin_page == 'acf_options_preview' && ! acf_is_pro() ) {
		$acf_page_title = __( 'Options Pages', 'acf' );
	}
	acf_get_view( 'global/header' );
}
?>
