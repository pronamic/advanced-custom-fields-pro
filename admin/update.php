<?php

/*
*  ACF Admin Update Class
*
*  All the logic for updates
*
*  @class 		acf_admin_update
*  @package		ACF
*  @subpackage	Admin
*/

if( ! class_exists('acf_admin_update') ) :

class acf_admin_update {

	/*
	*  __construct
	*
	*  A good place to add actions / filters
	*
	*  @type	function
	*  @date	11/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// actions
		add_action('admin_menu', 						array($this,'admin_menu'), 20);
		add_action('network_admin_menu', 				array($this,'network_admin_menu'), 20);
		
		
		// ajax
		add_action('wp_ajax_acf/admin/data_upgrade',	array($this, 'ajax_upgrade'));
		
	}
	
	
	/*
	*  network_admin_menu
	*
	*  This function will chck for available updates and add actions if needed
	*
	*  @type	function
	*  @date	2/04/2015
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_admin_menu() {
		
		// bail early if no show_admin
		if( !acf_get_setting('show_admin') ) {
			
			return;
		
		}
		
		
		// vars
		$prompt = false;
		
		
		// loop through sites and find updates
		$sites = wp_get_sites();
		
		if( $sites ) {
			
			foreach( $sites as $site ) {
				
				// switch blog
				switch_to_blog( $site['blog_id'] );
				
				
				// get site updates
				$updates = acf_get_updates();
				
				
				// restore
				restore_current_blog();
				
				
				if( $updates ) {
				
					$prompt = true;
					break;
					
				}
				
			}
			
		}
		
		
		// bail if no prompt
		if( !$prompt ) {
			
			return;
			
		}
		
		
		// actions
		add_action('network_admin_notices', array($this, 'network_admin_notices'), 1);
		
		
		// add page
		add_submenu_page('update-core.php', __('Upgrade ACF','acf'), __('Upgrade ACF','acf'), acf_get_setting('capability'),'acf-upgrade', array($this,'network_html'));
		
	}
	
	
	/*
	*  network_admin_notices
	*
	*  This function will render the update notice
	*
	*  @type	function
	*  @date	2/04/2015
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_admin_notices() {
		
		// bail ealry if already on update page
		if( acf_is_screen('admin_page_acf-upgrade-network') ) {
			
			return;
			
		}
		
				
		// view
		$view = array(
			'button_text'	=> __("Review sites & upgrade", 'acf'),
			'button_url'	=> network_admin_url('update-core.php?page=acf-upgrade'),
			'confirm'		=> false
		);
		
		
		// load view
		acf_get_view('update-notice', $view);
		
	}
	
	
	/*
	*  network_html
	*
	*  This function will render the HTML for the network upgrade page
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_html() {
		
		// vars
		$plugin_version = acf_get_setting('version');
		
		
		// loop through sites and find updates
		$sites = wp_get_sites();
		
		if( $sites ) {
			
			foreach( $sites as $i => $site ) {
				
				// switch blog
				switch_to_blog( $site['blog_id'] );
				
				
				// extra info
				$site['name'] = get_bloginfo('name');
				$site['url'] = home_url();
				
				
				// get site updates
				$site['updates'] = acf_get_updates();
				
				
				// get site version
				$site['acf_version'] = get_option('acf_version');
				
				
				// no value equals new instal
				if( !$site['acf_version'] ) {
					
					$site['acf_version'] = $plugin_version;
					
				}
				
				
				// update
				$sites[ $i ] = $site;
				
				
				// restore
				restore_current_blog();
				
			}
			
		}
		
		
		// view
		$view = array(
			'sites' => $sites,
			'plugin_version'	=> $plugin_version
		);
		
		
		// enqueue
		acf_enqueue_scripts();
		
		
		// load view
		acf_get_view('update-network', $view);
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will chck for available updates and add actions if needed
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// vars
		$plugin_version = acf_get_setting('version');
		$acf_version = get_option('acf_version');

		
		// bail early if a new install
		if( !$acf_version ) {
		
			update_option('acf_version', $plugin_version );
			return;
			
		}
		
		
		// bail early if $acf_version is >= $plugin_version
		if( version_compare( $acf_version, $plugin_version, '>=') ) {
		
			return;
			
		}
		
		
		// vars
		$updates = acf_get_updates();
		
		
		// bail early if no updates available
		if( empty($updates) ) {
			
			update_option('acf_version', $plugin_version );
			return;
			
		}
		
		
		// bail early if no show_admin
		if( !acf_get_setting('show_admin') ) {
			
			return;
		
		}
		
		
		// actions
		add_action('admin_notices', array($this, 'admin_notices'), 1);
		
		
		// add page
		add_submenu_page('edit.php?post_type=acf-field-group', __('Upgrade','acf'), __('Upgrade','acf'), acf_get_setting('capability'),'acf-upgrade', array($this,'html') );
		
	}
	
	
	/*
	*  admin_notices
	*
	*  This function will render any admin notices
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_notices() {
		
		// bail ealry if already on update page
		if( acf_is_screen('custom-fields_page_acf-upgrade') ) {
			
			return;
			
		}
		
				
		// view
		$view = array(
			'button_text'	=> __("Upgrade Database", 'acf'),
			'button_url'	=> admin_url('edit.php?post_type=acf-field-group&page=acf-upgrade')
		);
		
		
		// load view
		acf_get_view('update-notice', $view);
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// view
		$view = array(
			'updates'			=> acf_get_updates(),
			'plugin_version'	=> acf_get_setting('version')
		);
		
		
		// enqueue
		acf_enqueue_scripts();
		
		
		// load view
		acf_get_view('update', $view);
		
	}
	
	
	/*
	*  ajax_upgrade
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_upgrade() {
		
   		// options
   		$options = wp_parse_args( $_POST, array(
			'nonce'		=> '',
			'blog_id'	=> '',
		));
		
		
		// validate
		if( !wp_verify_nonce($options['nonce'], 'acf_upgrade') ) {
		
			wp_send_json_error();
			
		}
		
		
		// switch blog
		if( $options['blog_id'] ) { 
			
			switch_to_blog( $options['blog_id'] );
			
		}
		
		
		// vars
		$updates = acf_get_updates();
		$message = '';
		
		
		// bail early if no updates
		if( empty($updates) ) {
			
			wp_send_json_error(array(
				'message' => 'No updates available'
			));	
			
		}
		
		
		// install updates
		foreach( $updates as $version ) {
			
			// get path
			$path = acf_get_path("admin/updates/{$version}.php");
			
			
			// load version
			if( !file_exists($path) ) {
			
				wp_send_json_error(array(
					'message' => 'Error loading update'
				));	
				
			}
			
			
			// load any errors / feedback from update
			ob_start();
			
			
			// action for 3rd party
			do_action('acf/upgrade_start/' . $version );
			
			
			// include
			include( $path );
			
			
			// action for 3rd party
			do_action('acf/upgrade_finish/' . $version );
			
			
			// get feedback
			$message .= ob_get_clean();
			
			
			// update successful
			update_option('acf_version', $version );
		
		}
		
		
		// updates complete
		update_option('acf_version', acf_get_setting('version'));
		
		
		// return
		wp_send_json_success(array(
			'message' => $message
		));
		
	}
	
	
	/*
	*  inject_downgrade
	*
	*  description
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
/*
	function inject_downgrade( $transient ) {
		
		// bail early if no plugins are being checked
	    if( empty($transient->checked) )  {
	    
            return $transient;
            
        }
		
		
		// bail early if no nonce
		if( empty($_GET['_acfrollback']) ) {
			
			return $transient;
			
		}
		
		
		// vars
		$rollback = get_option('acf_version');
		
		
		// bail early if nonce is not correct
		if( !wp_verify_nonce( $_GET['_acfrollback'], 'rollback-acf_' . $rollback ) ) {
			
			return $transient;
			
		}
		
		
		// create new object for update
        $obj = new stdClass();
        $obj->slug = $_GET['plugin'];
        $obj->new_version = $rollback;
        $obj->url = 'https://wordpress.org/plugins/advanced-custom-fields';
        $obj->package = 'http://downloads.wordpress.org/plugin/advanced-custom-fields.' . $rollback . '.zip';;
        
        
        // add to transient
        $transient->response[ $_GET['plugin'] ] = $obj;
        
		
		// return 
        return $transient;
	}
*/
			
}

// initialize
new acf_admin_update();

endif;

?>
