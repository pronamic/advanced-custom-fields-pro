<?php
$acf_field_group_pro_features_title = __( 'Unlock Advanced Features and Build Even More with ACF PRO', 'acf' );
$acf_learn_more_text                = __( 'Learn More', 'acf' );
$acf_learn_more_link                = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'metabox' );
$acf_learn_more_target              = '_blank';
$acf_pricing_text                   = __( 'View Pricing & Upgrade', 'acf' );
$acf_pricing_link                   = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'metabox_pricing', 'pricing-table' );
$acf_more_tools_link                = acf_add_url_utm_tags( 'https://wpengine.com/developer/', 'bx_prod_referral', 'acf_free_plugin_cta_panel_logo', false, 'acf_plugin', 'referral' );
$acf_wpengine_logo_link             = acf_add_url_utm_tags( 'https://wpengine.com/', 'bx_prod_referral', 'acf_free_plugin_cta_panel_logo', false, 'acf_plugin', 'referral' );

if ( acf_is_pro() ) {
	if ( ! acf_pro_get_license_key() && acf_pro_is_updates_page_visible() ) {
		$acf_learn_more_target = '';
		$acf_learn_more_text   = __( 'Manage License', 'acf' );
		$acf_learn_more_link   = esc_url( admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates#acf_pro_license' ) );
	} elseif ( acf_pro_is_license_expired() ) {
		$acf_pricing_text = __( 'Renew License', 'acf' );
		$acf_pricing_link = acf_add_url_utm_tags( acf_pro_get_manage_license_url(), 'ACF renewal', 'metabox' );
	}
}

?>
<div id="tmpl-acf-field-group-pro-features">
	<div class="acf-field-group-pro-features-wrapper">
		<h1 class="acf-field-group-pro-features-title-sm"><?php echo esc_html( $acf_field_group_pro_features_title ); ?> <div class="acf-pro-label"><img src="<?php echo esc_url( acf_get_url( 'assets/images/pro-chip.svg' ) ); ?>" alt="<?php esc_attr_e( 'ACF PRO logo', 'acf' ); ?>"></div></h1>
		<div class="acf-field-group-pro-features-content">
			<h1 class="acf-field-group-pro-features-title"><?php echo esc_html( $acf_field_group_pro_features_title ); ?> <div class="acf-pro-label"><img src="<?php echo esc_url( acf_get_url( 'assets/images/pro-chip.svg' ) ); ?>" alt="<?php esc_attr_e( 'ACF PRO logo', 'acf' ); ?>"></div></h1>
			<p class="acf-field-group-pro-features-desc"><?php esc_html_e( 'Speed up your workflow and develop better websites with features like ACF Blocks and Options Pages, and sophisticated field types like Repeater, Flexible Content, Clone, and Gallery.', 'acf' ); ?></p>
			<div class="acf-field-group-pro-features-actions">
				<a target="<?php echo esc_attr( $acf_learn_more_target ); ?>" href="<?php echo $acf_learn_more_link; ?>" class="acf-btn acf-btn-muted acf-pro-features-learn-more"><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
					<?php echo esc_html( $acf_learn_more_text ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i>
				</a>
				<a target="_blank" href="<?php echo $acf_pricing_link; ?>" class="acf-btn acf-pro-features-upgrade"><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
					<?php echo esc_html( $acf_pricing_text ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i>
				</a>
			</div>
		</div>

		<div class="acf-field-group-pro-features-grid">
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon field-type-icon-flexible-content"></i>
				<span class="field-type-label"><?php esc_html_e( 'Flexible Content Field', 'acf' ); ?></span>
			</div>
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon field-type-icon-repeater"></i>
				<span class="field-type-label"><?php esc_html_e( 'Repeater Field', 'acf' ); ?></span>
			</div>
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon field-type-icon-clone"></i>
				<span class="field-type-label"><?php esc_html_e( 'Clone Field', 'acf' ); ?></span>
			</div>
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon pro-feature-blocks"></i>
				<span class="field-type-label"><?php esc_html_e( 'ACF Blocks', 'acf' ); ?></span>
			</div>
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon pro-feature-options-pages"></i>
				<span class="field-type-label"><?php esc_html_e( 'Options Pages', 'acf' ); ?></span>
			</div>
			<div class="acf-field-group-pro-feature">
				<i class="field-type-icon field-type-icon-gallery"></i>
				<span class="field-type-label"><?php esc_html_e( 'Gallery Field', 'acf' ); ?></span>
			</div>
		</div>
	</div>
	<div class="acf-field-group-pro-features-footer-wrap">
		<div class="acf-field-group-pro-features-footer">
			<div class="acf-for-the-builders">
				<?php
				$acf_wpengine_logo = acf_get_url( 'assets/images/wp-engine-horizontal-white.svg' );
				$acf_wpengine_logo = sprintf( '<a href="%s" target="_blank"><img class="acf-field-group-pro-features-wpengine-logo" src="%s" alt="WP Engine" /></a>', $acf_wpengine_logo_link, $acf_wpengine_logo );
				/* translators: %s - WP Engine logo */
				$acf_made_for_text = sprintf( __( 'Built for those that build with WordPress, by the team at %s', 'acf' ), $acf_wpengine_logo );
				echo acf_esc_html( $acf_made_for_text );
				?>
			</div>
			<div class="acf-more-tools-from-wpengine">
				<a href="<?php echo $acf_more_tools_link; ?>" target="_blank"><?php esc_html_e( 'More Tools from WP Engine', 'acf' ); ?> <i class="acf-icon acf-icon-arrow-up-right"></i></a><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped on generation. ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	( function ( $, undefined ) {
		$( document ).ready( function() {
			if ( 'field_group' === acf.get( 'screen' ) ) {
				$( '#acf-field-group-options' ).after( $( '#tmpl-acf-field-group-pro-features' ).css( 'display', 'block' ) );
			} else {
				$( '#tmpl-acf-field-group-pro-features' ).appendTo( '#wpbody-content .wrap' ).css( 'display', 'block' );
			}
		} );
	} )( jQuery );
</script>
