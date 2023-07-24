<script>document.body.classList.add('acf-no-post-types');</script>
<div class="acf-no-post-types-wrapper">
	<div class="acf-no-post-types-inner">
		<img src="<?php echo esc_url( acf_get_url( 'assets/images/empty-post-types.svg' ) ); ?>" />
		<h2><?php esc_html_e( 'Add Your First Post Type', 'acf' ); ?></h2>
		<p><?php esc_html_e( 'Expand the functionality of WordPress beyond standard posts and pages with custom post types.', 'acf' ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=acf-post-type' ) ); ?>" class="acf-btn"><i class="acf-icon acf-icon-plus"></i> <?php esc_html_e( 'Add Post Type', 'acf' ); ?></a>
		<p class="acf-small">
			<?php
			printf(
				/* translators: %s url to getting started guide */
				__( 'New to ACF? Take a look at our <a href="%s" target="_blank">getting started guide</a>.', 'acf' ),
				acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/getting-started-with-acf/', 'docs', 'no-post-types' )
			);
			?>
		</p>
	</div>
</div>
