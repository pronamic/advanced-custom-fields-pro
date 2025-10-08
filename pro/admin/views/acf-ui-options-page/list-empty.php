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

$acf_options_pages_desc = sprintf(
	/* translators: %s URL to ACF options pages documentation */
	__( 'ACF <a href="%s" target="_blank">options pages</a> are custom admin pages for managing global settings via fields. You can create multiple pages and sub-pages.', 'acf' ),
	acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/options-page/', 'docs', 'no-options-pages' )
);

$acf_getting_started = sprintf(
	/* translators: %s url to getting started guide */
	__( 'New to ACF? Take a look at our <a href="%s" target="_blank">getting started guide</a>.', 'acf' ),
	acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/getting-started-with-acf/', 'docs', 'no-options-pages' )
);

$acf_learn_more_link = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'no-options-pages' );
$acf_upgrade_button  = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'no-options-pages-pricing', 'pricing-table' );
?>
<script>document.body.classList.add('acf-no-options-pages');</script>
<div class="acf-no-options-pages-wrapper">
	<div class="acf-no-options-pages-inner">
		<img src="<?php echo esc_url( acf_get_url( 'assets/images/empty-post-types.svg' ) ); ?>" />

		<?php
		if ( acf_pro_is_license_active() ) {
			$acf_options_pages_title = __( 'Add Your First Options Page', 'acf' );
		} else {
			$acf_options_pages_title = __( 'Upgrade to ACF PRO to create options pages in just a few clicks', 'acf' );
		}
		?>

		<h2><?php echo esc_html( $acf_options_pages_title ); ?></h2>
		<p><?php echo acf_esc_html( $acf_options_pages_desc ); ?></p>

		<?php if ( acf_pro_is_license_active() ) : ?>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=acf-ui-options-page' ) ); ?>" class="acf-btn"><i class="acf-icon acf-icon-plus"></i> <?php esc_html_e( 'Add Options Page', 'acf' ); ?></a>
		<?php else : ?>
			<div class="acf-ui-options-page-pro-features-actions">
				<a target="_blank" href="<?php echo $acf_learn_more_link; ?>" class="acf-btn acf-btn-muted"><?php esc_html_e( 'Learn More', 'acf' ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i></a><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
				<a target="_blank" href="<?php echo $acf_upgrade_button; ?>" class="acf-btn acf-options-pages-preview-upgrade-button"> <?php esc_html_e( 'Upgrade to ACF PRO', 'acf' ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i></a><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
			</div>
		<?php endif; ?>

		<p class="acf-small"><?php echo acf_esc_html( $acf_getting_started ); ?></p>
	</div>
</div>
