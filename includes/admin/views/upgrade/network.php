<?php
/**
 * Network Admin Database Upgrade
 *
 * Shows the databse upgrade process.
 *
 * @package ACF
 */

?>
<style type="text/css">
	
	/* hide steps */
	.show-on-complete {
		display: none;
	}	
	
</style>
<div id="acf-upgrade-wrap" class="wrap">
	
	<h1><?php esc_html_e( 'Upgrade Database', 'acf' ); ?></h1>
	
	<?php // translators: %s The button label name, translated seperately ?>
	<p><?php printf( esc_html__( 'The following sites require a DB upgrade. Check the ones you want to update and then click %s.', 'acf' ), '"' . esc_html__( 'Upgrade Sites', 'acf' ) . '"' ); ?></p>
	<p><input type="submit" name="upgrade" value="<?php esc_attr_e( 'Upgrade Sites', 'acf' ); ?>" class="button" id="upgrade-sites"></p>
	
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<td class="manage-column check-column" scope="col">
					<input type="checkbox" id="sites-select-all">
				</td>
				<th class="manage-column" scope="col" style="width:33%;">
					<label for="sites-select-all"><?php esc_html_e( 'Site', 'acf' ); ?></label>
				</th>
				<th><?php esc_html_e( 'Description', 'acf' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="manage-column check-column" scope="col">
					<input type="checkbox" id="sites-select-all-2">
				</td>
				<th class="manage-column" scope="col">
					<label for="sites-select-all-2"><?php esc_html_e( 'Site', 'acf' ); ?></label>
				</th>
				<th><?php esc_html_e( 'Description', 'acf' ); ?></th>
			</tr>
		</tfoot>
		<tbody id="the-list">
		<?php

		$sites = acf_get_sites();
		if ( $sites ) :
			foreach ( $sites as $i => $site ) :

				// switch blog
				switch_to_blog( $site['blog_id'] );

				?>
			<tr
				<?php
				if ( $i % 2 == 0 ) :
					?>
				class="alternate"<?php endif; ?>>
				<th class="check-column" scope="row">
				<?php if ( acf_has_upgrade() ) : ?>
					<input type="checkbox" value="<?php echo esc_attr( $site['blog_id'] ); ?>" name="checked[]">
				<?php endif; ?>
				</th>
				<td>
					<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong><br /><?php echo esc_url( home_url() ); ?>
				</td>
				<td>
				<?php if ( acf_has_upgrade() ) : ?>
					<?php // translators: %1 current db version, %2 available db version ?>
					<span class="response"><?php echo esc_html( printf( __( 'Site requires database upgrade from %1$s to %2$s', 'acf' ), acf_get_db_version(), ACF_VERSION ) ); ?></span>
				<?php else : ?>
					<?php esc_html_e( 'Site is up to date', 'acf' ); ?>
				<?php endif; ?>
				</td>
			</tr>
				<?php

				// restore
				restore_current_blog();
		endforeach;
		endif;

		?>
		</tbody>
	</table>
	
	<p><input type="submit" name="upgrade" value="<?php esc_attr_e( 'Upgrade Sites', 'acf' ); ?>" class="button" id="upgrade-sites-2"></p>
	<?php // translators: %s admin dashboard url page ?>
	<p class="show-on-complete"><?php echo acf_esc_html( sprintf( __( 'Database Upgrade complete. <a href="%s">Return to network dashboard</a>', 'acf' ), esc_url( network_admin_url() ) ) ); ?></p>
	
	<script type="text/javascript">
	(function($) {
		
		var upgrader = new acf.Model({
			events: {
				'click #upgrade-sites':		'onClick',
				'click #upgrade-sites-2':	'onClick'
			},
			$inputs: function(){
				return $('#the-list input:checked');
			},
			onClick: function( e, $el ){
				
				// prevent default
				e.preventDefault();
				
				// bail early if no selection
				if( !this.$inputs().length ) {
					return alert('<?php esc_attr_e( 'Please select at least one site to upgrade.', 'acf' ); ?>');
				}
				
				// confirm action
				if( !confirm("<?php esc_attr_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'acf' ); ?>") ) {
					return;
				}
				
				// upgrade
				this.upgrade();
			},
			upgrade: function(){
				
				// vars
				var $inputs = this.$inputs();
				
				// bail early if no sites selected
				if( !$inputs.length ) {
					return this.complete();
				}
				
				// disable buttons
				$('.button').prop('disabled', true);
				
				// vars
				var $input = $inputs.first();
				var $row = $input.closest('tr');
				var text = '';
				var success = false;
				
				// show loading
				<?php // translators: %s the version being upgraded to. ?>
				$row.find('.response').html('<i class="acf-loading"></i></span> <?php printf( esc_attr__( 'Upgrading data to version %s', 'acf' ), esc_attr( ACF_VERSION ) ); ?>');
				
				// send ajax request to upgrade DB
				$.ajax({
					url: acf.get('ajaxurl'),
					dataType: 'json',
					type: 'post',
					data: acf.prepareForAjax({
						action: 'acf/ajax/upgrade',
						blog_id: $input.val()
					}),
					success: function( json ){
						success = true;
						$input.remove();
						text = '<?php esc_attr_e( 'Upgrade complete.', 'acf' ); ?>';	
					},
					error: function( jqXHR, textStatus, errorThrown ){
						text = '<?php esc_attr_e( 'Upgrade failed.', 'acf' ); ?>';
						if( error = acf.getXhrError(jqXHR) ) {
							text += ' <code>' + error +  '</code>';
						}
					},
					complete: this.proxy(function(){
						
						// display text
						$row.find('.response').html( text );
						
						// if successful upgrade, proceed to next site. Otherwise, skip to complete.
						if( success ) {
							this.upgrade();
						} else {
							this.complete();
						}
					})
				});
			},
			complete: function(){
				
				// enable buttons
				$('.button').prop('disabled', false);
				
				// show message
				$('.show-on-complete').show();
			}
		});
				
	})(jQuery);	
	</script>
</div>
