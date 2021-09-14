<?php

// vars
$active   = $license ? true : false;
$nonce    = $active ? 'deactivate_pro_licence' : 'activate_pro_licence';
$button   = $active ? __( 'Deactivate License', 'acf' ) : __( 'Activate License', 'acf' );
$readonly = $active ? 1 : 0;

?>
<div class="wrap acf-settings-wrap">
	
	<h1><?php _e( 'Updates', 'acf' ); ?></h1>
	
	<div class="acf-box" id="acf-license-information">
		<div class="title">
			<h3><?php _e( 'License Information', 'acf' ); ?></h3>
		</div>
		<div class="inner">
			<p><?php printf( __( 'To unlock updates, please enter your license key below. If you don\'t have a licence key, please see <a href="%s" target="_blank">details & pricing</a>.', 'acf' ), esc_url( 'https://www.advancedcustomfields.com/pro/?utm_source=ACF%2Bpro%2Bplugin&utm_medium=insideplugin&utm_campaign=ACF%2Bupgrade&utm_content=license%2Bactivations' ) ); ?></p>
			<form action="" method="post">
				<?php acf_nonce_input( $nonce ); ?>
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="acf-field-acf_pro_licence"><?php _e( 'License Key', 'acf' ); ?></label>
							</th>
							<td>
								<?php

								// render field
								acf_render_field(
									array(
										'type'     => 'text',
										'name'     => 'acf_pro_licence',
										'value'    => str_repeat( '*', strlen( $license ) ),
										'readonly' => $readonly,
									)
								);

								?>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" value="<?php echo esc_attr( $button ); ?>" class="button button-primary">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			
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
								
								<?php if ( $active ) : ?>
									<a class="button button-primary" href="<?php echo esc_attr( admin_url( 'plugins.php?s=Advanced+Custom+Fields+Pro' ) ); ?>"><?php _e( 'Update Plugin', 'acf' ); ?></a>
								<?php else : ?>
									<a class="button" disabled="disabled" href="#"><?php _e( 'Please enter your license key above to unlock updates', 'acf' ); ?></a>
								<?php endif; ?>
								
							<?php else : ?>
								
								<span style="margin-right: 5px;"><?php _e( 'No', 'acf' ); ?></span>
								<a class="button" href="<?php echo esc_attr( add_query_arg( 'force-check', 1 ) ); ?>"><?php _e( 'Check Again', 'acf' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ( $changelog ) : ?>
					<tr>
						<th>
							<label><?php _e( 'Changelog', 'acf' ); ?></label>
						</th>
						<td>
							<?php echo acf_esc_html( $changelog ); ?>
						</td>
					</tr>
					<?php endif; ?>
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
		</div>
	</div>
</div>
<style type="text/css">
	#acf_pro_licence {
		width: 75%;
	}
	
	#acf-update-information td h4 {
		display: none;
	}
</style>
