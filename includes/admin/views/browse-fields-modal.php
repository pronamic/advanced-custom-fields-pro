<?php
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included template file.

$browse_fields_tabs = array( 'popular' => __( 'Popular', 'acf' ) );
$browse_fields_tabs = $browse_fields_tabs + acf_get_field_categories_i18n();
?>
<div class="acf-browse-fields-modal-wrap">
	<div class="acf-modal acf-browse-fields-modal">
		<div class="acf-field-picker">
			<div class="acf-modal-title">
				<h1><?php esc_html_e( 'Select Field Type', 'acf' ); ?></h1>
				<span class="acf-search-field-types-wrap">
					<input class="acf-search-field-types" type="search" placeholder="<?php esc_attr_e( 'Search fields...', 'acf' ); ?>" />
				</span>
			</div>
			<div class="acf-modal-content">
				<?php
				foreach ( $browse_fields_tabs as $name => $label ) {
					acf_render_field_wrap(
						array(
							'type'  => 'tab',
							'label' => $label,
							'key'   => 'acf_browse_fields_tabs',
						)
					);

					printf(
						'<div class="acf-field-types-tab" data-category="%s"></div>',
						esc_attr( $name )
					);
				}
				?>
				<div class="acf-field-type-search-results"></div>
				<div class="acf-field-type-search-no-results">
					<img src="<?php echo esc_url( acf_get_url( 'assets/images/face-sad.svg' ) ); ?>" />
					<p class="acf-no-results-text">
						<?php
						printf(
							/* translators: %s: The invalid search term */
							acf_esc_html( __( "No search results for '%s'", 'acf' ) ),
							'<span class="acf-invalid-search-term"></span>'
						);
						?>
					</p>
					<p>
						<?php
						$browse_popular_link = '<a href="#" class="acf-browse-popular-fields">' . esc_html( __( 'Popular fields', 'acf' ) ) . '</a>';
						printf(
							/* translators: %s: A link to the popular fields used in ACF */
							acf_esc_html( __( 'Try a different search term or browse %s', 'acf' ) ),
							$browse_popular_link //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
						?>
					</p>
				</div>
			</div>
			<div class="acf-modal-toolbar acf-field-picker-toolbar">
				<div class="acf-field-picker-label">
					<input class="acf-insert-field-label" type="text" placeholder="<?php esc_attr_e( 'Field Label', 'acf' ); ?>" />
				</div>
				<div class="acf-field-picker-actions">
					<button class="button acf-cancel acf-modal-close"><?php esc_html_e( 'Cancel', 'acf' ); ?></button>
					<button class="acf-btn acf-select-field"><?php esc_html_e( 'Select Field', 'acf' ); ?></button>
					<a target="_blank" data-url-base="<?php echo esc_attr( acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'field-type-modal', '' ) ); ?>" class="acf-btn acf-btn-pro">
						<?php esc_html_e( 'Upgrade to PRO', 'acf' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="acf-field-type-preview">
			<div class="field-type-info">
				<h2 class="field-type-name"></h2>
				<a target="_blank" data-url-base="<?php echo esc_attr( acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'field-type-modal', '' ) ); ?>" class="field-type-upgrade-to-unlock">
					<i class="acf-icon acf-icon-lock"></i>
					<?php esc_html_e( 'Available with ACF PRO', 'acf' ); ?>
				</a>
				<p class="field-type-desc"></p>
				<div class="field-type-preview-container">
					<img class="field-type-image" />
				</div>
			</div>
			<ul class="acf-hl field-type-links">
				<li>
					<a class="field-type-tutorial" href="#" target="_blank">
						<i class="acf-icon acf-icon-play"></i>
						<?php esc_html_e( 'Tutorial', 'acf' ); ?>
					</a>
				</li>
				<li>
					<a class="field-type-doc" href="#" target="_blank">
						<i class="acf-icon acf-icon-document"></i>
						<?php esc_html_e( 'Documentation', 'acf' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="acf-modal-backdrop acf-modal-close"></div>
</div>
