<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_pro_updates') ) :

class acf_pro_updates {
	

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
		
		// append plugin information
		// Note: is_admin() was used previously, however this prevents jetpack manage & ManageWP from working
	    add_filter('plugins_api', array($this, 'inject_info'), 20, 3);
	    
	    
		// append update information
		add_filter('pre_set_site_transient_update_plugins', array($this, 'inject_update'));
		
		
		// add custom message when PRO not activated but update available
		add_action('in_plugin_update_message-' . acf_get_setting('basename'), array($this, 'in_plugin_update_message'), 10, 2 );
			
	}
	
	
	/*
	*  inject_info
	*
	*  This function will populate the plugin data visible in the 'View details' popup
	*
	*  @type	function
	*  @date	17/01/2014
	*  @since	5.0.0
	*
	*  @param	$result (bool|object)
	*  @param	$action (string)
	*  @param	$args (object)
	*  @return	$result
	*/
	
	function inject_info( $result, $action = null, $args = null ) {
		
		// vars
		$slug = acf_get_setting('slug');
        
        
		// validate
    	if( isset($args->slug) && $args->slug == $slug ) {
	    	
	    	$info = acf_pro_get_remote_info();
	    	$sections = acf_extract_vars($info, array(
	    		'description',
	    		'installation',
	    		'changelog',
	    		'upgrade_notice',
	    	));
	    	
	    	$obj = new stdClass();
		
		    foreach( $info as $k => $v ) {
			    
		        $obj->$k = $v;
		        
		    }
		    
		    $obj->sections = $sections;

		    return $obj;
		    
    	}
    	
    	
    	// return        
        return $result;
        
	}
	
	
	/*
	*  inject_update
	*
	*  This function will connect to the ACF website and find release details
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$transient (object)
	*  @return	$transient
	*/
	
	function inject_update( $transient ) {
		
		// vars
		$basename = acf_get_setting('basename');
		
		
		// bail early if no show_updates
		if( !acf_get_setting('show_updates') ) {
			
			return $transient;
			
		}
		
		
		// bail early if not a plugin (included in theme)
		if( !is_plugin_active($basename) ) {
			
			return $transient;
			
		}
		
				
		// bail early if no update available
		if( !acf_pro_is_update_available() ) {
			
			return $transient;
			
		}
		
		 
        // vars
		$info = acf_pro_get_remote_info();
		$basename = acf_get_setting('basename');
		$slug = acf_get_setting('slug');
		
		
        // create new object for update
        $obj = new stdClass();
        $obj->slug = $slug;
        $obj->plugin = $basename;
        $obj->new_version = $info['version'];
        $obj->url = $info['homepage'];
        $obj->package = '';
        
        
        // license
		if( acf_pro_is_license_active() ) {
			
			$obj->package = acf_pro_get_remote_url('download', array(
				'k'				=> acf_pro_get_license(),
				'wp_url'		=> home_url(),
				'acf_version'	=> acf_get_setting('version'),
				'wp_version'	=> get_bloginfo('version'),
			));
		
		}
		
        
        // add to transient
        $transient->response[ $basename ] = $obj;
        
		
		// return 
        return $transient;
        
	}
	
	
	/*
	*  in_plugin_update_message
	*
	*  Displays an update message for plugin list screens.
	*  Shows only the version updates from the current until the newest version
	*
	*  @type	function
	*  @date	5/06/13
	*
	*  @param	{array}		$plugin_data
	*  @param	{object}	$r
	*/

	function in_plugin_update_message( $plugin_data, $r ) {
		
		// validate
		if( acf_pro_is_license_active() ) {
			
			return;
			
		}
		
		
		// vars
		$m = __('To enable updates, please enter your license key on the <a href="%s">Updates</a> page. If you don\'t have a licence key, please see <a href="%s">details & pricing</a>', 'acf');
		
		
		// show message
		echo '<br />' . sprintf( $m, admin_url('edit.php?post_type=acf-field-group&page=acf-settings-updates'), 'http://www.advancedcustomfields.com/pro');
	
	}
	
}


// initialize
new acf_pro_updates();

endif; // class_exists check

?>
