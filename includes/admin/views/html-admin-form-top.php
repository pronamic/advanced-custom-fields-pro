<?php
global $title, $post_new_file, $post_type_object, $post;
$title_placeholder = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
?>
<div class="acf-headerbar acf-headerbar-field-editor">
	<div class="acf-headerbar-inner">

		<div class="acf-headerbar-content">
			<h1 class="acf-page-title">
			<?php
			echo esc_html( $title );
			?>
			</h1>
			<div class="acf-title-wrap">
				<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $title_placeholder; ?></label>
				<input form="post" type="text" name="post_title" size="30" value="<?php echo esc_attr( $post->post_title ); ?>" id="title" class="acf-headerbar-title-field" spellcheck="true" autocomplete="off" placeholder="<?php esc_attr_e( 'Field Group Title', 'acf' ); ?>" />
			</div>
		</div>

		<div class="acf-headerbar-actions">
			<a href="#" class="acf-btn acf-btn-secondary add-field"><i class="acf-icon acf-icon-plus"></i><?php _e( 'Add Field', 'acf' ); ?></a>
			<button form="post" class="acf-btn acf-publish" type="submit"><?php _e( 'Save Changes', 'acf' ); ?></button>
		</div>

	</div>
</div>
