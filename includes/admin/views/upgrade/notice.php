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

// calculate add-ons (non pro only)
$plugins = array();

if ( ! acf_get_setting( 'pro' ) ) {
	if ( is_plugin_active( 'acf-repeater/acf-repeater.php' ) ) {
		$plugins[] = __( 'Repeater', 'acf' );
	}
	if ( is_plugin_active( 'acf-flexible-content/acf-flexible-content.php' ) ) {
		$plugins[] = __( 'Flexible Content', 'acf' );
	}
	if ( is_plugin_active( 'acf-gallery/acf-gallery.php' ) ) {
		$plugins[] = __( 'Gallery', 'acf' );
	}
	if ( is_plugin_active( 'acf-options-page/acf-options-page.php' ) ) {
		$plugins[] = __( 'Options Page', 'acf' );
	}
}

?>
<div id="acf-upgrade-notice" class="notice notice-warning">
	<div class="notice-container">
		<div class="col-content">
			<img src="<?php echo esc_url( acf_get_url( 'assets/images/acf-logo.svg' ) ); ?>" />
			<h2><?php esc_html_e( 'Database Upgrade Required', 'acf' ); ?></h2>
			<?php // translators: %1 plugin name, %2 version number ?>
			<p><?php echo acf_esc_html( sprintf( __( 'Thank you for updating to %1$s v%2$s!', 'acf' ), acf_get_setting( 'name' ), acf_get_setting( 'version' ) ) ); ?><br />
			<?php esc_html_e( 'This version contains improvements to your database and requires an upgrade.', 'acf' ); ?></p>
			<?php if ( ! empty( $plugins ) ) : ?>
				<?php // translators: %s a list of plugin ?>
				<p><?php echo acf_esc_html( sprintf( __( 'Please also check all premium add-ons (%s) are updated to the latest version.', 'acf' ), implode( ', ', $plugins ) ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="col-actions">
			<a id="acf-upgrade-button" href="<?php echo esc_url( $button_url ); ?>" class="button-primary"><?php echo esc_html( $button_text ); ?></a>
		</div>
		
	</div>
</div>
<?php if ( $confirm ) : ?>
<script type="text/javascript">
(function($) {
	
	$("#acf-upgrade-button").on("click", function(){
		return confirm("<?php esc_attr_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'acf' ); ?>");
	});
		
})(jQuery);	
</script>
<?php endif; ?>
