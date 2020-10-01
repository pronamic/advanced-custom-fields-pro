<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Admin_Upgrade') ) :

class ACF_Admin_Upgrade {
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {
		
		// actions
		add_action( 'admin_menu', 			array($this,'admin_menu'), 20 );
		if( is_multisite() ) {
			add_action( 'network_admin_menu',	array($this,'network_admin_menu'), 20 );
		}
	}
	
	/**
	*  admin_menu
	*
	*  Setus up logic if DB Upgrade is needed on a single site.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function admin_menu() {
		
		// check if upgrade is avaialble
		if( acf_has_upgrade() ) {
			
			// add notice
			add_action('admin_notices', array($this, 'admin_notices'));
			
			// add page
			$page = add_submenu_page('index.php', __('Upgrade Database','acf'), __('Upgrade Database','acf'), acf_get_setting('capability'), 'acf-upgrade', array($this,'admin_html') );
			
			// actions
			add_action('load-' . $page, array($this,'admin_load'));
		}
	}
	
	/**
	 * network_admin_menu
	 *
	 * Sets up admin logic if DB Upgrade is required on a multi site.
	 *
	 * @date	24/8/18
	 * @since	5.7.4
	 *
	 * @param	void
	 * @return	void
	 */
	function network_admin_menu() {
		
		// Vars.
		$upgrade = false;
		
		// Loop over sites and check for upgrades.
		$sites = get_sites( array( 'number' => 0 ) );
		if( $sites ) {
			
			// Unhook action to avoid memory issue (as seen in wp-includes/ms-site.php).
			remove_action( 'switch_blog', 'wp_switch_roles_and_user', 1 );
			foreach( $sites as $site ) {
				
				// Switch site.
				switch_to_blog( $site->blog_id );
				
				// Check for upgrade.
				$site_upgrade = acf_has_upgrade();
				
				// Restore site.
				// Ideally, we would switch back to the original site at after looping, however,
				// the restore_current_blog() is needed to modify global vars.
				restore_current_blog();
				
				// Check if upgrade was found.
				if( $site_upgrade ) {
					$upgrade = true;
					break;
				}
		    }
		    add_action( 'switch_blog', 'wp_switch_roles_and_user', 1, 2 );
		}
		
		// Bail early if no upgrade is needed.
		if( !$upgrade ) {
			return;
		}
		
		// Add notice.
		add_action('network_admin_notices', array($this, 'network_admin_notices'));
		
		// Add page.
		$page = add_submenu_page(
			'index.php', 
			__('Upgrade Database','acf'), 
			__('Upgrade Database','acf'), 
			acf_get_setting('capability'), 
			'acf-upgrade-network', 
			array( $this,'network_admin_html' )
		);
		add_action( "load-$page", array( $this, 'network_admin_load' ) );
	}
	
	/**
	*  admin_load
	*
	*  Runs during the loading of the admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	function admin_load() {
		
		// remove prompt 
		remove_action('admin_notices', array($this, 'admin_notices'));
		
		// Enqueue core script.
		acf_enqueue_script( 'acf' );
	}
	
	/**
	*  network_admin_load
	*
	*  Runs during the loading of the network admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	function network_admin_load() {
		
		// remove prompt 
		remove_action('network_admin_notices', array($this, 'network_admin_notices'));
		
		// Enqueue core script.
		acf_enqueue_script( 'acf' );
	}
	
	/**
	*  admin_notices
	*
	*  Displays the DB Upgrade prompt.
	*
	*  @date	23/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	function admin_notices() {
		
		// vars
		$view = array(
			'button_text'	=> __("Upgrade Database", 'acf'),
			'button_url'	=> admin_url('index.php?page=acf-upgrade'),
			'confirm'		=> true
		);
		
		// view
		acf_get_view('html-notice-upgrade', $view);
	}
	
	/**
	*  network_admin_notices
	*
	*  Displays the DB Upgrade prompt on a multi site.
	*
	*  @date	23/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	function network_admin_notices() {
		
		// vars
		$view = array(
			'button_text'	=> __("Review sites & upgrade", 'acf'),
			'button_url'	=> network_admin_url('index.php?page=acf-upgrade-network'),
			'confirm'		=> false
		);
		
		// view
		acf_get_view('html-notice-upgrade', $view);
	}
	
	/**
	*  admin_html
	*
	*  Displays the HTML for the admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function admin_html() {
		acf_get_view('html-admin-page-upgrade');
	}
	
	/**
	*  network_admin_html
	*
	*  Displays the HTML for the network upgrade admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function network_admin_html() {
		acf_get_view('html-admin-page-upgrade-network');
	}
}

// instantiate
acf_new_instance('ACF_Admin_Upgrade');

endif; // class_exists check

?>