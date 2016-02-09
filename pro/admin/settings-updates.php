<?php 

class acf_settings_updates {
	
	var $view;
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
		// actions
		add_action('admin_menu', array($this, 'admin_menu'), 20 );
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the ACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// vars
		$basename = acf_get_setting('basename');
		
		
		// bail early if no show_admin
		if( !acf_get_setting('show_admin') ) {
			
			return;
			
		}
		
		
		// bail early if no show_updates
		if( !acf_get_setting('show_updates') ) {
			
			return;
			
		}
		
		
		// bail early if not a plugin (included in theme)
		if( !is_plugin_active($basename) ) {
			
			return;
			
		}
				
		
		// add page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Updates','acf'), __('Updates','acf'), acf_get_setting('capability'),'acf-settings-updates', array($this,'html') );
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load() {
		
		// $_POST
		if( acf_verify_nonce('activate_pro_licence') ) {
		
			$this->activate_pro_licence();
			
		} elseif( acf_verify_nonce('deactivate_pro_licence') ) {
		
			$this->deactivate_pro_licence();
			
		}
		
		
		// view
		$this->view = array(
			'license'			=> '',
			'active'			=> 0,
			'current_version'	=> acf_get_setting('version'),
			'remote_version'	=> '',
			'update_available'	=> false,
			'changelog'			=> '',
			'upgrade_notice'	=> ''
		);
		
		
		// license
		if( acf_pro_is_license_active() ) {
		
			$this->view['license'] = acf_pro_get_license();
			$this->view['active'] = 1;
			
		}
		
		
		// vars
		$info = acf_pro_get_remote_info();
		
		
		// validate
        if( empty($info) ) {
        
        	acf_add_admin_notice( __('<b>Error</b>. Could not connect to update server', 'acf'), 'error');
        	return;
        	
        }
        
        
        // add info to view
        $this->view['remote_version'] = $info['version'];
        
        
        // add changelog if the remote version is '>' than the current version
		if( acf_pro_is_update_available() )
        {
        	$this->view['update_available'] = true;
        	 
        	 
        	// changelog
        	$changelogs = explode('<h4>', $info['changelog']);
        	
        	foreach( $changelogs as $changelog )
        	{
        		// validate (first segment is always empty due to explode)
	        	if( empty($changelog) )
	        	{
		        	continue;
	        	}
	        	
	        	
        	 	// explode
	        	$changelog = explode('</h4>', $changelog);
	        	$changelog_version = trim($changelog[0]);
	        	$changelog_text = trim($changelog[1]);
	        	$changelog_text = str_replace('<ul>', '<ul class="ul-disc">', $changelog_text);
	        	
	        	if( version_compare($this->view['remote_version'], $changelog_version, '==') )
	        	{
		        	$this->view['changelog'] = $changelog_text;
		        	break;
	        	}
	        	
        	}
        	 
        	 
        	// upgrade_notice
        	$upgrade_notices = explode('<h4>', $info['upgrade_notice']);
        	
        	foreach( $upgrade_notices as $upgrade_notice )
        	{
        		// validate (first segment is always empty due to explode)
	        	if( empty($upgrade_notice) )
	        	{
		        	continue;
	        	}
	        	
	        	
        	 	// explode
	        	$upgrade_notice = explode('</h4>', $upgrade_notice);
	        	$upgrade_version = trim($upgrade_notice[0]);
	        	$upgrade_text = trim($upgrade_notice[1]);
	        	$upgrade_text = str_replace('<ul>', '<ul class="ul-disc">', $upgrade_text);
	        	
	        	if( version_compare($this->view['current_version'], $upgrade_version, '<') )
	        	{
		        	$this->view['upgrade_notice'] = $upgrade_text;
		        	break;
	        	}
	        	
        	 }
        }
		
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// load view
		acf_pro_get_view('settings-updates', $this->view);
		
	}
	
	
	/*
	*  activate_pro_licence
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
	
	function activate_pro_licence() {
		
		// connect
		$args = array(
			'_nonce'		=> wp_create_nonce('activate_pro_licence'),
			'acf_license'	=> acf_extract_var($_POST, 'acf_pro_licence'),
			'acf_version'	=> acf_get_setting('version'),
			'wp_name'		=> get_bloginfo('name'),
			'wp_url'		=> home_url(),
			'wp_version'	=> get_bloginfo('version'),
			'wp_language'	=> get_bloginfo('language'),
			'wp_timezone'	=> get_option('timezone_string'),
		);
		
		
		// connect
		$response = acf_pro_get_remote_response( 'activate-license', $args );
		
		
		// validate
		if( empty($response) ) {
			
			acf_add_admin_notice( __('<b>Connection Error</b>. Sorry, please try again', 'acf'), 'error');
			return;
			
		}
		
		
		// vars
		$response = json_decode($response, true);
		$class = '';
		
		
		// action
		if( $response['status'] == 1 ) {
			
			acf_pro_update_license($response['license']);
			
		} else {
			
			$class = 'error';
			
		}
		
		
		// show message
		if( $response['message'] ) {
			
			acf_add_admin_notice($response['message'], $class);
			
		}
		
	}
	
	
	/*
	*  deactivate_pro_licence
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
	
	function deactivate_pro_licence() {
		
		// validate
		if( !acf_pro_is_license_active() ) {
			
			return;
			
		}
		
		
		// connect
		$args = array(
			'_nonce'		=> wp_create_nonce('deactivate_pro_licence'),
			'acf_license'	=> acf_pro_get_license(),
			'wp_url'		=> home_url(),
		);
		
		
		// connect
		$response = acf_pro_get_remote_response( 'deactivate-license', $args );
		
		
		// validate
		if( empty($response) ) {
		
			acf_add_admin_notice(__('<b>Connection Error</b>. Sorry, please try again', 'acf'), 'error');
			return;
			
		}
		
		
		// vars
		$response = json_decode($response, true);
		$class = '';
		
		
		// allways clear DB
		acf_pro_update_license('');
		
		
		// action
		if( $response['status'] == 1 ) {
		
			
			
		} else {
			
			$class = 'error';
			
		}
		
		
		// show message
		if( $response['message'] ) {
		
			acf_add_admin_notice($response['message'], $class);
			
		}
		
	}
	
}


// initialize
new acf_settings_updates();

?>
