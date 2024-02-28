<?php

$acf_plugin_name      = acf_is_pro() ? 'ACF PRO' : 'ACF';
$acf_plugin_name      = '<strong>' . $acf_plugin_name . ' &mdash;</strong>';
$acf_learn_how_to_fix = '<a href="' . acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/escaping-the-field/', 'docs', '6-2-5-security-changes' ) . '" target="_blank">' . __( 'Learn&nbsp;more', 'acf' ) . '</a>';
$acf_class            = 'notice-error';
$acf_user_can_acf     = false;

if ( current_user_can( acf_get_setting( 'capability' ) ) ) {
	$acf_user_can_acf = true;
	$acf_show_details = ' <a class="acf-show-more-details" href="#">' . __( 'Show&nbsp;details', 'acf' ) . '</a>';
	$acf_class       .= ' is-dismissible';
} else {
	$acf_show_details = __( 'Please contact your site administrator or developer for more details.', 'acf' );
}

$acf_error_msg = sprintf(
/* translators: %1$s - name of the ACF plugin. %2$s - Link to documentation. %3$s - Link to show more details about the error */
	__( '%1$s ACF now automatically escapes unsafe HTML when rendered by <code>the_field</code> or the ACF shortcode. We\'ve detected the output of some of your fields has been modified by this change, but this may not be a breaking change. %2$s. %3$s.', 'acf' ),
	$acf_plugin_name,
	$acf_learn_how_to_fix,
	$acf_show_details
);

?>
<div class="acf-admin-notice notice acf-escaped-html-notice <?php echo esc_attr( $acf_class ); ?>">
	<p><?php echo acf_esc_html( $acf_error_msg ); ?></p>
	<?php if ( $acf_user_can_acf && ! empty( $acf_escaped ) ) : ?>
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
	<?php endif; ?>
</div>
