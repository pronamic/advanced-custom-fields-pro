<?php
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template include file
$field_groups     = acf_get_field_groups();
$num_field_groups = 0;
if ( is_array( $field_groups ) ) {
	$num_field_groups = count( $field_groups );
}
$is_subfield   = ! empty( $is_subfield );
$wrapper_class = '';
if ( $is_subfield ) {
	$wrapper_class = ' acf-is-subfields';
	if ( ! $fields ) {
		$wrapper_class .= ' -empty';
	}
} elseif ( ! $fields && ! $parent ) {
	/**
	 * Filter for determining if a new field group should render with a text field automatically
	 *
	 * @since   6.2
	 *
	 * @param bool $bool If an empty field group should render with a new field auto appended.
	 */
	if ( apply_filters( 'acf/field_group/auto_add_first_field', true ) ) {
		$wrapper_class = ' acf-auto-add-field';
	} else {
		$wrapper_class = ' -empty';
	}
}
?>
<?php if ( $parent || $is_subfield ) { ?>
<div class="acf-sub-field-list-header">
	<h3 class="acf-sub-field-list-title"><?php esc_html_e( 'Fields', 'acf' ); ?></h3>
	<a href="#" class="acf-btn acf-btn-secondary add-field"><i class="acf-icon acf-icon-plus"></i><?php esc_html_e( 'Add Field', 'acf' ); ?></a>
</div>
<?php } ?>
<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed string output ?>
<div class="acf-field-list-wrap<?php echo $wrapper_class; ?>">

	<ul class="acf-hl acf-thead">
		<li class="li-field-order">
			<?php
			/* translators: A symbol (or text, if not available in your locale) meaning "Order Number", in terms of positional placement. */
			esc_html_e( '#', 'acf' );
			?>
			<span class="acf-hidden">
				<?php
				/* translators: Hidden accessibility text for the positional order number of the field. */
				esc_html_e( 'Order', 'acf' );
				?>
			</span>
		</li>
		<li class="li-field-label"><?php esc_html_e( 'Label', 'acf' ); ?></li>
		<li class="li-field-name"><?php esc_html_e( 'Name', 'acf' ); ?></li>
		<li class="li-field-key"><?php esc_html_e( 'Key', 'acf' ); ?></li>
		<li class="li-field-type"><?php esc_html_e( 'Type', 'acf' ); ?></li>
	</ul>

	<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed string output ?>
	<div class="acf-field-list<?php echo $wrapper_class; ?>">

		<div class="no-fields-message">
			<div class="no-fields-message-inner">
				<img src="<?php echo esc_url( acf_get_url( 'assets/images/empty-group.svg' ) ); ?>" />
				<h2><?php esc_html_e( 'Add Your First Field', 'acf' ); ?></h2>
				<p><?php esc_html_e( 'Get started creating new custom fields for your posts, pages, custom post types and other WordPress content.', 'acf' ); ?></p>
				<a href="#" class="acf-btn acf-btn-primary add-field add-first-field
"><i class="acf-icon acf-icon-plus"></i> <?php esc_html_e( 'Add Field', 'acf' ); ?></a>
				<p class="acf-small">
				<?php
					echo acf_esc_html(
						sprintf(
							/* translators: %s url to field types list */
							__( 'Choose from over 30 field types. <a href="%s" target="_blank">Learn more</a>.', 'acf' ),
							acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/', 'docs', 'empty-field-group', 'field-types' )
						)
					);
					?>
				</p>
			</div>
		</div>

		<?php
		if ( $fields ) :
			foreach ( $fields as $i => $field ) :
				acf_get_view(
					'acf-field-group/field',
					array(
						'field'            => $field,
						'i'                => $i,
						'num_field_groups' => $num_field_groups,
					)
				);
			endforeach;
		endif;
		?>

	</div>

	<ul class="acf-hl acf-tfoot">
		<li class="acf-fr">
			<a href="#" class="acf-btn acf-btn-secondary add-field"><i class="acf-icon acf-icon-plus"></i><?php esc_html_e( 'Add Field', 'acf' ); ?></a>
		</li>
	</ul>

<?php
if ( ! $parent ) :

	// get clone
	$clone = acf_get_valid_field(
		array(
			'ID'    => 'acfcloneindex',
			'key'   => 'acfcloneindex',
			'label' => __( 'New Field', 'acf' ),
			'name'  => 'new_field',
			'type'  => 'text',
		)
	);

	?>
	<script type="text/html" id="tmpl-acf-field">
	<?php
	acf_get_view(
		'acf-field-group/field',
		array(
			'field'            => $clone,
			'i'                => 0,
			'num_field_groups' => $num_field_groups,
		)
	);
	?>
	</script>
	<script type="text/html" id="tmpl-acf-browse-fields-modal">
		<?php acf_get_view( 'browse-fields-modal' ); ?>
	</script>
<?php endif; ?>

</div>
