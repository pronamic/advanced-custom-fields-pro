<?php
$acf_learn_more_link = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'no-options-pages' );
$acf_upgrade_button  = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'no-options-pages-pricing', 'pricing-table' );

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
<div class="wrap acf_options_preview_wrap">
	<table class="wp-list-table widefat fixed striped">
		<tbody id="the-list">
			<tr class="no-items">
				<td class="colspanchange" colspan="6">
					<div class="acf-no-field-groups-wrapper">
						<div class="acf-no-field-groups-inner acf-field-group-pro-features-content">
							<img src="<?php echo acf_get_url( 'assets/images/empty-post-types.svg' ); ?>" />
								<h2><?php echo acf_esc_html( 'Upgrade to ACF PRO to create options pages in just a few clicks', 'acf' ); ?></h2>
								<p><?php echo acf_esc_html( $acf_options_pages_desc ); ?></p>
								<div class="acf-field-group-pro-features-actions">
									<a target="_blank" href="<?php echo $acf_learn_more_link; ?>" class="acf-btn acf-btn-muted"><?php esc_html_e( 'Learn More', 'acf' ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i></a>
									<a target="_blank" href="<?php echo $acf_upgrade_button; ?>" class="acf-btn acf-options-pages-preview-upgrade-button"> <?php esc_html_e( 'Upgrade to ACF PRO', 'acf' ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i></a>
								</div>
								<p class="acf-small"><?php echo acf_esc_html( $acf_getting_started ); ?></p>
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
