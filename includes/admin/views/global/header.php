<?php
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included template file.

global $post_type, $post_type_object, $acf_page_title;
$post_new_file = sprintf(
	'post-new.php?post_type=%s',
	is_string( $post_type ) ? $post_type : 'acf-field-group'
);

$acf_is_options_page_preview = acf_request_arg( 'page' ) === 'acf_options_preview';

$page_title = false;
if ( isset( $acf_page_title ) ) {
	$page_title = $acf_page_title;
} elseif ( is_object( $post_type_object ) ) {
	$page_title = $post_type_object->labels->name;
}
if ( $page_title ) {
	?>
<div class="acf-headerbar">

	<h1 class="acf-page-title">
	<?php
	echo esc_html( $page_title );
	?>
	<?php if ( $acf_is_options_page_preview ) { ?>
			<div class="acf-pro-label">PRO</div>
		<?php
	}
	?>
	</h1>
	<?php if ( $acf_is_options_page_preview ) { ?>
			<a href="#" class="acf-btn acf-btn-sm disabled">
				<i class="acf-icon acf-icon-plus"></i>
				<?php esc_html_e( 'Add Options Page', 'acf' ); ?>
			</a>
	<?php } ?>
	<?php
	if ( ! empty( $post_type_object ) && current_user_can( $post_type_object->cap->create_posts ) ) {
		echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="acf-btn acf-btn-sm"><i class="acf-icon acf-icon-plus"></i>' . esc_html( $post_type_object->labels->add_new ) . '</a>';
	}
	?>

</div>
<?php } ?>
