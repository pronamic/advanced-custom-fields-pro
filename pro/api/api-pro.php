<?php 

/*
*  acf_pro_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_pro_get_view( $view_name = '', $args = array() ) {
	
	// vars
	$path = acf_get_path("pro/admin/views/{$view_name}.php");
	
	
	if( file_exists($path) ) {
		
		include( $path );
		
	}
	
}


/*
*  acf_pro_get_remote_url
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

function acf_pro_get_remote_url( $action = '', $args = array() ) {
	
	// defaults
	$args['a'] = $action;
	$args['p'] = 'pro';
	
	
	// vars
	$url = "https://connect.advancedcustomfields.com/index.php?" . build_query($args);
	
	
	// return
	return $url;
	
}


/*
*  acf_pro_get_remote_response
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

function acf_pro_get_remote_response( $action = '', $post = array() ) {
	
	// vars
	$url = acf_pro_get_remote_url( $action );
	
	
	// connect
	$request = wp_remote_post( $url, array(
		'body' => $post
	));
	
	
	// return body
    if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
    	
        return $request['body'];
    
    }
    
    
    // return
    return 0;
    
}


/*
*  acf_pro_is_update_available
*
*  This function will return true if an update is available
*
*  @type	function
*  @date	14/05/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(boolean)
*/

function acf_pro_is_update_available() {
	
	// vars
	$info = acf_get_remote_plugin_info();
	$version = acf_get_setting('version');
	 
	
	// return false if no info
	if( empty($info['version']) ) return false;
	
    
    // return false if the external version is '<=' the current version
	if( version_compare($info['version'], $version, '<=') ) {
		
    	return false;
    
    }
    
	
	// return
	return true;
	
}


/*
*  acf_pro_get_remote_info
*
*  This function will return remote plugin data
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(mixed)
*/

function acf_pro_get_remote_info() {
	
	// clear transient if force check is enabled
	if( !empty($_GET['force-check']) ) {
		
		// only allow transient to be deleted once per page load
		if( empty($_GET['acf-ignore-force-check']) ) {
			
			delete_transient( 'acf_pro_get_remote_info' );
			
		}
		
		
		// update $_GET
		$_GET['acf-ignore-force-check'] = true;
		
	}
	
	
	// get transient
	$transient = get_transient( 'acf_pro_get_remote_info' );

	if( $transient !== false ) {
	
		return $transient;
	
	}

	
	// vars
	$info = acf_pro_get_remote_response('get-info');
	$timeout = 12 * HOUR_IN_SECONDS;
	
	
    // decode
    if( !empty($info) ) {
    	
		$info = json_decode($info, true);
		
		// fake info version
        //$info['version'] = '6.0.0';
        
    } else {
	    
	    $info = 0; // allow transient to be returned, but empty to validate
	    $timeout = 2 * HOUR_IN_SECONDS;
	    
    }
        
        
	// update transient
	set_transient('acf_pro_get_remote_info', $info, $timeout );
	
	
	// return
	return $info;
}


function acf_pro_is_license_active() {
	
	// vars
	$data = acf_pro_get_license( true );
	$url = home_url();
	
	if( !empty($data['url']) && !empty($data['key']) && $data['url'] == $url ) {
		
		return true;
		
	}
	
	
	return false;
	
}

function acf_pro_get_license( $all = false ) {
	
	// get option
	$data = get_option('acf_pro_license');
	
	
	// decode
	$data = base64_decode($data);
	
	
	// attempt deserialize
	if( is_serialized( $data ) )
	{
		$data = maybe_unserialize($data);
		
		// $all
		if( !$all )
		{
			$data = $data['key'];
		}
		
		return $data;
	}
	
	
	// return
	return false;
}



function acf_pro_update_license( $license ) {
	
	$save = array(
		'key'	=> $license,
		'url'	=> home_url()
	);
	
	
	$save = maybe_serialize($save);
	$save = base64_encode($save);
	
	
	return update_option('acf_pro_license', $save);
	
}

?>
