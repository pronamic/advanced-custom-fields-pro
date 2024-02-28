<?php
/**
 * The empty list state for an ACF Options Page
 *
 * @package ACF
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
?>
<script>document.body.classList.add('acf-no-options-pages');</script>
<div class="acf-no-options-pages-wrapper">
	<div class="acf-no-options-pages-inner">
		<img src="<?php echo esc_url( acf_get_url( 'assets/images/empty-post-types.svg' ) ); ?>" />
		<h2><?php esc_html_e( 'Add Your First Options Page', 'acf' ); ?></h2>
		<p><?php echo acf_esc_html( $acf_options_pages_desc ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=acf-ui-options-page' ) ); ?>" class="acf-btn"><i class="acf-icon acf-icon-plus"></i> <?php esc_html_e( 'Add Options Page', 'acf' ); ?></a>
		<p class="acf-small"><?php echo acf_esc_html( $acf_getting_started ); ?></p>
	</div>
</div>
