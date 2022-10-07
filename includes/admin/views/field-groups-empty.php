<script>document.body.classList.add('acf-no-field-groups');</script>
<div class="acf-no-field-groups-wrapper">
	<div class="acf-no-field-groups-inner">
		<img src="<?php echo acf_get_url( 'assets/images/empty-group.svg' ); ?>" />
		<h2><?php _e( 'Add Your First Field Group', 'acf' ); ?></h2>
		<p>
		<?php
		printf(
			/* translators: %s url to creating a field group page */
			__( 'ACF uses <a href="%s" target="_blank">field groups</a> to group custom fields together, and then attach those fields to edit screens.', 'acf' ),
			acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/creating-a-field-group/', 'docs', 'no-field-groups' )
		);
		?>
		</p>
		<a href="<?php echo admin_url( 'post-new.php?post_type=acf-field-group' ); ?>" class="acf-btn"><i class="acf-icon acf-icon-plus"></i> <?php _e( 'Add Field Group', 'acf' ); ?></a>
		<p class="acf-small">
			<?php
			printf(
				/* translators: %s url to getting started guide */
				__( 'New to ACF? Take a look at our <a href="%s" target="_blank">getting started guide</a>.', 'acf' ),
				acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/getting-started-with-acf/', 'docs', 'no-field-groups' )
			);
			?>
		</p>
	</div>
</div>
