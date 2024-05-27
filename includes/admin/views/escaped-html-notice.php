<?php

$acf_plugin_name      = acf_is_pro() ? 'ACF PRO' : 'ACF';
$acf_plugin_name      = '<strong>' . $acf_plugin_name . ' &mdash;</strong>';
$acf_learn_how_to_fix = '<a href="' . acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/escaping-the-field/', 'docs', '6-2-5-security-changes' ) . '" target="_blank">' . __( 'Learn&nbsp;more', 'acf' ) . '</a>';
$acf_class            = 'notice-error';
$acf_user_can_acf     = false;

if ( current_user_can( acf_get_setting( 'capability' ) ) ) {
	$acf_user_can_acf = true;
	$acf_dismiss_url  = add_query_arg( array( 'acf-dismiss-esc-html-notice' => wp_create_nonce( 'acf/dismiss_escaped_html_notice' ) ) );

	// "Show/Hide Details" is a button for accessibility purposes, because it isn't a link. But since the design shows a link, we need to make it look like a link.
	$acf_style_button_as_link = trim(
		'display: inline;
		padding: 0;
		background: none;
		border: none;
		color: #0073aa;
		text-decoration: underline;
		cursor: pointer;'
	);

	$acf_show_details  = '<button style="' . esc_attr( $acf_style_button_as_link ) . '" class="acf-show-more-details">' . __( 'Show&nbsp;details', 'acf' ) . '</button>';
	$acf_show_details .= ' | <a class="acf-dismiss-permanently-button" href="' . esc_url( $acf_dismiss_url ) . '">' . __( 'Dismiss permanently', 'acf' ) . '</a>';
} else {
	$acf_show_details = __( 'Please contact your site administrator or developer for more details.', 'acf' );
}

$acf_error_msg = sprintf(
/* translators: %1$s - name of the ACF plugin. %2$s - Link to documentation. %3$s - Link to show more details about the error */
	__( '%1$s ACF now automatically escapes unsafe HTML when rendered by <code>the_field</code> or the ACF shortcode. We\'ve detected the output of some of your fields has been modified by this change, but this may not be a breaking change. %2$s.', 'acf' ),
	$acf_plugin_name,
	$acf_learn_how_to_fix
);


?>
<div class="acf-admin-notice notice acf-escaped-html-notice <?php echo esc_attr( $acf_class ); ?>">
	<p style="margin-bottom: 0.5em; padding-bottom: 2px;"><?php echo acf_esc_html( $acf_error_msg ); ?></p>
	<p style="margin: 0.5em 0; padding: 2px;"><?php echo acf_esc_html( $acf_show_details ); ?></p>
	<?php if ( $acf_user_can_acf && ! empty( $acf_escaped ) ) : ?>
	<div class="acf-error-details" style="display: none; list-style: disc; margin-left: 14px;">
		<ul class="acf-error-details" style="display: none; list-style: disc; margin-left: 14px;">
			<?php
			foreach ( $acf_escaped as $acf_field_key => $acf_data ) {
				$acf_error = sprintf(
					/* translators: %1$s - The selector used  %2$s The field name  3%$s The parent function name */
					__( '%1$s (%2$s) - rendered via %3$s', 'acf' ),
					$acf_data['selector'],
					$acf_data['field'],
					$acf_data['function']
				);

				echo '<li>' . esc_html( $acf_error ) . '</li>';
			}
			?>
		</ul>
		<p style="margin: 0.5em 0; padding: 2px;">
		<?php
			$acf_clear_logs_url = add_query_arg( array( 'acf-clear-esc-html-log' => wp_create_nonce( 'acf/clear_escaped_html_log' ) ) );
			// translators: %s - The clear log button opening HTML tag. %s - The closing HTML tag.
			echo acf_esc_html( '<i>' . sprintf( __( 'This data is logged as we detect values that have been changed during output. %1$sClear log and dismiss%2$s after escaping the values in your code. The notice will reappear if we detect changed values again.', 'acf' ), '<a class="acf-clear-log-button" href="' . esc_url( $acf_clear_logs_url ) . '">', '</a>' ) . '</i>' );
		?>
		</p>
	</div>
	<?php endif; ?>
</div>
