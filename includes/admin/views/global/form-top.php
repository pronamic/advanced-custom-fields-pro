<?php
global $title, $post_new_file, $post_type_object, $post;
$acf_title_placeholder = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
$acf_title             = $post->post_title;
$acf_post_type         = is_object( $post_type_object ) ? $post_type_object->name : '';
$acf_publish_btn_name  = 'save';

if ( 'publish' !== $post->post_status ) {
	$acf_publish_btn_name = 'publish';
}

if ( 'acf-field-group' === $acf_post_type ) {
	$acf_use_post_type = acf_get_post_type_from_request_args( 'add-fields' );
	$acf_use_taxonomy  = acf_get_taxonomy_from_request_args( 'add-fields' );

	/* translators: %s - singular label of post type/taxonomy, i.e. "Movie"/"Genre" */
	$acf_prefilled_title = __( '%s fields', 'acf' );

	/**
	 * Sets a default title to be prefilled (e.g. "Movies Fields") for a post type or taxonomy.
	 *
	 * @since 6.1.5
	 *
	 * @param string $acf_prefilled_title A string to define the prefilled title for a post type or taxonomy.
	 */
	$acf_prefilled_title = (string) apply_filters( 'acf/field_group/prefill_title', $acf_prefilled_title );

	if ( $acf_use_post_type && ! empty( $acf_use_post_type['labels']['singular_name'] ) ) {
		$acf_prefilled_title = sprintf( $acf_prefilled_title, $acf_use_post_type['labels']['singular_name'] );
	} elseif ( $acf_use_taxonomy && ! empty( $acf_use_taxonomy['labels']['singular_name'] ) ) {
		$acf_prefilled_title = sprintf( $acf_prefilled_title, $acf_use_taxonomy['labels']['singular_name'] );
	} else {
		$acf_prefilled_title = false;
	}

	if ( empty( $acf_title ) && $acf_prefilled_title ) {
		$acf_title = $acf_prefilled_title;
	}
}
?>
<div class="acf-headerbar acf-headerbar-field-editor">
	<div class="acf-headerbar-inner">

		<div class="acf-headerbar-content">
			<h1 class="acf-page-title">
			<?php
			echo esc_html( $title );
			?>
			</h1>
			<?php if ( 'acf-field-group' === $acf_post_type ) : ?>
			<div class="acf-title-wrap">
				<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo esc_html( $acf_title_placeholder ); ?></label>
				<input form="post" type="text" name="post_title" size="30" value="<?php echo esc_attr( $acf_title ); ?>" id="title" class="acf-headerbar-title-field" spellcheck="true" autocomplete="off" placeholder="<?php esc_attr_e( 'Field Group Title', 'acf' ); ?>" />
			</div>
			<?php endif; ?>
		</div>

		<div class="acf-headerbar-actions" id="submitpost">
			<?php if ( 'acf-field-group' === $acf_post_type ) : ?>
				<a href="#" class="acf-btn acf-btn-secondary add-field">
					<i class="acf-icon acf-icon-plus"></i>
					<?php esc_html_e( 'Add Field', 'acf' ); ?>
				</a>
			<?php endif; ?>
			<button form="post" class="acf-btn acf-publish" name="<?php echo esc_attr( $acf_publish_btn_name ); ?>" type="submit">
				<?php esc_html_e( 'Save Changes', 'acf' ); ?>
			</button>
		</div>

	</div>
</div>
