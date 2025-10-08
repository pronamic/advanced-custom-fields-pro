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
