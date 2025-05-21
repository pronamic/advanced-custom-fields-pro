<?php
/**
 * View to output admin tools for both archive and single
 *
 * @package ACF
 */

$class = $active ? 'single' : 'grid';
$tool  = $active ? ' tool-' . $active : '';
?>
<div id="acf-admin-tools" class="wrap<?php echo esc_attr( $tool ); ?>">

	<h1>
		<?php esc_html_e( 'Tools', 'acf' ); ?>
		<?php if ( $active ) { ?>
			<a class="page-title-action" href="<?php echo esc_url( acf_get_admin_tools_url() ); ?>"><?php esc_html_e( 'Back to all tools', 'acf' ); ?></a>
		<?php } ?>
	</h1>

	<div class="acf-meta-box-wrap -<?php echo esc_attr( $class ); ?>">
		<?php do_meta_boxes( $screen_id, 'normal', '' ); ?>
	</div>

	<?php
	if ( ! acf_is_pro() ) {
		acf_get_view( 'acf-field-group/pro-features' );
	}
	?>
</div>
