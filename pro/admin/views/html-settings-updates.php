<?php

// vars
$active   = $license ? true : false;
$nonce    = $active ? 'deactivate_pro_license' : 'activate_pro_license';
$button   = $active ? __( 'Deactivate License', 'acf' ) : __( 'Activate License', 'acf' );
$readonly = $active ? 1 : 0;

?>
<div class="wrap acf-settings-wrap acf-updates">

	<h1><?php _e( 'Updates', 'acf' ); ?></h1>

	<div class="acf-box" id="acf-license-information">
		<div class="title">
			<h3><?php _e( 'License Information', 'acf' ); ?></h3>
		</div>
		<div class="inner">
			<?php if ( $is_defined_license ) { ?>

				<p>
					<?php echo acf_esc_html( apply_filters( 'acf/admin/license_key_constant_message', __( 'Your license key is defined in wp-config.php.', 'acf' ) ) ); ?>
				</p>

				<?php if ( ! $active ) { ?>

					<form action="" method="post">
						<?php acf_nonce_input( 'acf_delete_activation_transient' ); ?>
						<input type="submit" value="<?php echo esc_attr( __( 'Retry Activation', 'acf' ) ); ?>" class="button button-primary">
					</form>

				<?php } ?>
			<?php } else { ?>
				<p><?php printf( __( 'To unlock updates, please enter your license key below. If you don\'t have a licence key, please see <a href="%s" target="_blank">details & pricing</a>.', 'acf' ), acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'license activations' ) ); ?></p>
				<form action="" method="post" class="acf-activation-form">
					<?php acf_nonce_input( $nonce ); ?>
					<label for="acf-field-acf_pro_license"><?php _e( 'License Key', 'acf' ); ?></label>
					<?php

					// render field
					acf_render_field(
						array(
							'type'     => 'text',
							'name'     => 'acf_pro_license',
							'value'    => str_repeat( '*', strlen( $license ) ),
							'readonly' => $readonly,
						)
					);

					?>
					<input type="submit" value="<?php echo esc_attr( $button ); ?>" class="acf-btn">
				</form>
			<?php } ?>

		</div>

	</div>

	<div class="acf-box" id="acf-update-information">
		<div class="title">
			<h3><?php _e( 'Update Information', 'acf' ); ?></h3>
		</div>
		<div class="inner">
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label><?php _e( 'Current Version', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo esc_html( $current_version ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php _e( 'Latest Version', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo esc_html( $remote_version ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php _e( 'Update Available', 'acf' ); ?></label>
						</th>
						<td>
							<?php if ( $update_available ) : ?>

								<span style="margin-right: 5px;"><?php _e( 'Yes', 'acf' ); ?></span>
							<?php else : ?>
								<span style="margin-right: 5px;"><?php _e( 'No', 'acf' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ( $upgrade_notice ) : ?>
					<tr>
						<th>
							<label><?php _e( 'Upgrade Notice', 'acf' ); ?></label>
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
					<a class="button" disabled="disabled" href="#"><?php _e( 'Please upgrade WordPress to update ACF', 'acf' ); ?></a>
				<?php elseif ( $license_error ) : ?>
					<a class="button" disabled="disabled" href="#"><?php _e( 'Please reactivate your license to unlock updates', 'acf' ); ?></a>
				<?php elseif ( $active ) : ?>
					<a class="acf-btn" href="<?php echo esc_attr( admin_url( 'plugins.php?s=Advanced+Custom+Fields+Pro' ) ); ?>"><?php _e( 'Update Plugin', 'acf' ); ?></a>
				<?php else : ?>
					<a class="button" disabled="disabled" href="#"><?php _e( 'Enter your license key to unlock updates', 'acf' ); ?></a>
				<?php endif; ?>

			<?php else : ?>

				<a class="acf-btn acf-btn-secondary" href="<?php echo esc_attr( add_query_arg( 'force-check', 1 ) ); ?>"><?php _e( 'Check For Updates', 'acf' ); ?></a>

			<?php endif; ?>

		</div>
	</div>
</div>
<style type="text/css">
	#acf_pro_license {
		width: 75%;
	}

	#acf-update-information td h4 {
		display: none;
	}
</style>
