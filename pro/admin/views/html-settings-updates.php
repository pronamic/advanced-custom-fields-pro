<?php
/**
 * Renders the "License Information" and "Update Information" metaboxes.
 *
 * @package ACF
 */

$nonce                   = $active ? 'deactivate_pro_license' : 'activate_pro_license';
$activate_deactivate_btn = $active ? __( 'Deactivate License', 'acf' ) : __( 'Activate License', 'acf' );

/**
 * Renders the license status table.
 *
 * @since 6.2.3
 *
 * @param array $status The current license status array.
 * @return void
 */
function acf_pro_render_license_status_table( $status ) {
	// Bail early if we don't have a status from the server.
	if ( acf_pro_get_license_key() && empty( $status['status'] ) ) {
		return;
	}

	$status['status'] = ! empty( $status['status'] ) ? $status['status'] : 'inactive';
	$status_text      = _x( 'Inactive', 'license status', 'acf' );
	$is_lifetime      = ! empty( $status['lifetime'] );
	$is_wpe           = ! empty( $status['wpe'] );

	if ( 'active' === $status['status'] ) {
		$status_text = _x( 'Active', 'license status', 'acf' );
	} elseif ( 'expired' === $status['status'] ) {
		$status_text = _x( 'Expired', 'license status', 'acf' );
	} elseif ( 'cancelled' === $status['status'] ) {
		$status_text = _x( 'Cancelled', 'license status', 'acf' );
	}

	$indicator = '<span class="acf-license-status ' . esc_attr( $status['status'] ) . '">' . esc_html( $status_text ) . '</span>';
	?>

	<table class="acf-license-status-table">
		<tr>
			<th>
				<?php
				if ( $is_lifetime || 'inactive' === $status['status'] ) {
					esc_html_e( 'License Status', 'acf' );
				} else {
					esc_html_e( 'Subscription Status', 'acf' );
				}
				?>
			</th>
			<td><?php echo acf_esc_html( $indicator ); ?></td>
		</tr>
		<?php if ( ! empty( $status['name'] ) ) : ?>
		<tr>
			<th>
				<?php
				if ( $is_lifetime ) {
					esc_html_e( 'License Type', 'acf' );
				} else {
					esc_html_e( 'Subscription Type', 'acf' );
				}
				?>
			</th>
			<td>
				<?php
				if ( $is_lifetime && ! $is_wpe ) {
					esc_html_e( 'Lifetime - ', 'acf' );
				}
				echo esc_html( $status['name'] );
				?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! $is_lifetime && ! empty( $status['expiry'] ) && is_numeric( $status['expiry'] ) ) : ?>
		<tr>
			<th>
				<?php
				if ( acf_pro_is_license_expired( $status ) ) {
					esc_html_e( 'Subscription Expired', 'acf' );
				} else {
					esc_html_e( 'Subscription Expires', 'acf' );
				}
				?>
			</th>
			<td>
				<?php
				$date_format = get_option( 'date_format', 'F j, Y' );
				$expiry_date = date_i18n( $date_format, $status['expiry'] );
				echo esc_html( $expiry_date );
				?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
}

/**
 * Renders the "Manage License"/"Renew Subscription" button.
 *
 * @since 6.2.3
 *
 * @param array $status The current license status.
 * @return void
 */
function acf_pro_render_manage_license_button( $status ) {
	// Lifetime licenses don't have anything to manage.
	if ( ! empty( $status['lifetime'] ) ) {
		return;
	}

	$url   = acf_pro_get_manage_license_url( $status );
	$url   = acf_add_url_utm_tags( $url, 'updates page', 'manage license button' );
	$text  = __( 'Manage License', 'acf' );
	$class = '';

	if ( acf_pro_is_license_expired( $status ) || acf_pro_was_license_refunded( $status ) ) {
		$text  = __( 'Renew Subscription', 'acf' );
		$class = ' acf-btn acf-renew-subscription';
	}

	printf(
		'<a href="%1$s" target="_blank" class="acf-manage-license-btn%2$s">%3$s<i class="acf-icon acf-icon-arrow-up-right"></i></a>',
		esc_url( $url ),
		esc_attr( $class ),
		esc_html( $text )
	);
}
?>
<div class="wrap acf-settings-wrap acf-updates">

	<h1><?php esc_html_e( 'Updates', 'acf' ); ?></h1>

	<div class="acf-box" id="acf-license-information">
		<div class="title">
			<h3><?php esc_html_e( 'License Information', 'acf' ); ?></h3>
		</div>
		<div class="inner">
			<?php if ( $is_defined_license ) : ?>

				<p class="acf-license-defined">
					<?php echo acf_esc_html( apply_filters( 'acf/admin/license_key_constant_message', __( 'Your license key is defined in wp-config.php.', 'acf' ) ) ); ?>
				</p>

				<?php if ( ! acf_pro_is_license_active() ) : ?>
					<div class="acf-retry-activation">
						<?php
						$acf_recheck_class = ' acf-btn acf-btn-secondary';

						if ( acf_pro_is_license_expired( $license_status ) || acf_pro_was_license_refunded( $license_status ) ) {
							acf_pro_render_manage_license_button( $license_status );
							$acf_recheck_class = '';
						}

						$acf_recheck_nonce = wp_create_nonce( 'acf_retry_activation' );
						$acf_recheck_url   = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates&acf_retry_nonce=' . $acf_recheck_nonce );
						$acf_recheck_text  = __( 'Recheck License', 'acf' );
						printf(
							'<a class="acf-recheck-license%1$s" href="%2$s"><i class="acf-icon acf-icon-regenerate"></i>%3$s</a>',
							esc_attr( $acf_recheck_class ),
							esc_url( $acf_recheck_url ),
							esc_html( $acf_recheck_text )
						);
						?>
					</div>
				<?php endif; ?>
			<?php else : // License is not defined. ?>
				<?php if ( empty( $license_status['wpe'] ) ) { // Don't show the license key and deactivate button for WPE issued licenses. ?>
					<form action="" method="post" class="acf-activation-form">
						<?php acf_nonce_input( $nonce ); ?>
						<label for="acf-field-acf_pro_license"><?php esc_html_e( 'License Key', 'acf' ); ?></label>
						<?php
						acf_render_field(
							array(
								'type'     => 'text',
								'name'     => 'acf_pro_license',
								'value'    => str_repeat( '*', strlen( $license ) ),
								'readonly' => $active ? 1 : 0,
							)
						);

						$activate_deactivate_btn_id    = $active ? 'id="deactivate-license" ' : '';
						$activate_deactivate_btn_class = $active ? ' acf-btn-tertiary' : '';
						?>
						<input <?php echo $activate_deactivate_btn_id; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- manually defined safe HTML. ?>type="submit" value="<?php echo esc_attr( $activate_deactivate_btn ); ?>" class="acf-btn<?php echo esc_attr( $activate_deactivate_btn_class ); ?>">

						<?php
						acf_pro_render_manage_license_button( $license_status );

						if ( acf_pro_is_license_expired( $license_status ) || acf_pro_was_license_refunded( $license_status ) ) {
							$acf_recheck_nonce = wp_create_nonce( 'acf_recheck_status' );
							$acf_recheck_url   = admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates&acf_retry_nonce=' . $acf_recheck_nonce );
							$acf_recheck_text  = __( 'Recheck License', 'acf' );
							printf(
								'<a class="acf-recheck-license" href="%1$s"><i class="acf-icon acf-icon-regenerate"></i>%2$s</a>',
								esc_url( $acf_recheck_url ),
								esc_html( $acf_recheck_text )
							);
						}
						?>

					</form>
				<?php } ?>
			<?php endif; // End of license_defined check. ?>
			<div class="acf-license-status-wrap">
				<?php
				acf_pro_render_license_status_table( $license_status );

				if ( ! $active && ! defined( 'ACF_PRO_LICENSE' ) ) :
					?>
					<div class="acf-no-license-view-pricing">
						<span>
							<?php
							$acf_view_pricing_text = esc_html__( 'View pricing & purchase', 'acf' );
							$acf_view_pricing_link = sprintf(
								'<a href=%s target="_blank">%s <i class="acf-icon acf-icon-arrow-up-right"></i></a>',
								acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'license activations' ),
								$acf_view_pricing_text
							);
							echo acf_esc_html(
								sprintf(
									/* translators: %s - link to ACF website */
									__( 'Don\'t have an ACF PRO license? %s', 'acf' ),
									$acf_view_pricing_link
								)
							);
							?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

	</div>

	<div class="acf-box" id="acf-update-information">
		<div class="title">
			<h3><?php esc_html_e( 'Update Information', 'acf' ); ?></h3>
		</div>
		<div class="inner">
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Current Version', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo esc_html( $current_version ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Latest Version', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo esc_html( $remote_version ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Update Available', 'acf' ); ?></label>
						</th>
						<td>
							<?php if ( $update_available ) : ?>

								<span style="margin-right: 5px;"><?php esc_html_e( 'Yes', 'acf' ); ?></span>
							<?php else : ?>
								<span style="margin-right: 5px;"><?php esc_html_e( 'No', 'acf' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ( $upgrade_notice ) : ?>
					<tr>
						<th>
							<label><?php esc_html_e( 'Upgrade Notice', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo acf_esc_html( $upgrade_notice ); ?>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $changelog ) : ?>
			<div class="acf-update-changelog">
				<?php echo acf_esc_html( $changelog ); ?>
			</div>
			<?php endif; ?>

			<?php if ( $update_available ) : ?>
				<?php if ( $wp_not_compatible ) : ?>
					<a class="button" disabled="disabled" href="#"><?php esc_html_e( 'Please upgrade WordPress to update ACF', 'acf' ); ?></a>
				<?php elseif ( $license_error ) : ?>
					<a class="button" disabled="disabled" href="#"><?php esc_html_e( 'Please reactivate your license to unlock updates', 'acf' ); ?></a>
				<?php elseif ( $active && is_multisite() ) : ?>
					<a class="button" disabled="disabled" href="#"><?php esc_html_e( 'Update ACF in Network Admin', 'acf' ); ?></a>
				<?php elseif ( $active && ! is_plugin_active( ACF_BASENAME ) ) : ?>
					<a class="button" disabled="disabled" href="#"><?php esc_html_e( 'Updates must be manually installed in this configuration', 'acf' ); ?></a>
				<?php elseif ( $active ) : ?>
					<a class="acf-btn" href="<?php echo esc_url( admin_url( 'plugins.php?s=Advanced+Custom+Fields+Pro' ) ); ?>"><?php esc_html_e( 'Update Plugin', 'acf' ); ?></a>
				<?php else : ?>
					<a class="button" disabled="disabled" href="#"><?php esc_html_e( 'Enter your license key to unlock updates', 'acf' ); ?></a>
				<?php endif; ?>
			<?php else : ?>
				<a class="acf-btn acf-btn-secondary" href="<?php echo esc_url( add_query_arg( 'force-check', 1 ) ); ?>"><?php esc_html_e( 'Check For Updates', 'acf' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
