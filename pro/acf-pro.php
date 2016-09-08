<?php 

if( !class_exists('acf_pro') ):

class acf_pro {
	
	/*
	*  __construct
	*
	*  
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// update setting
		acf_update_setting( 'pro', true );
		acf_update_setting( 'name', __('Advanced Custom Fields PRO', 'acf') );
		

		// api
		acf_include('pro/api/api-pro.php');
		acf_include('pro/api/api-options-page.php');
		
		
		// updates
		acf_include('pro/core/updates.php');
			
			
		// admin
		if( is_admin() ) {
			
			// options page
			acf_include('pro/admin/options-page.php');
			
			// settings
			acf_include('pro/admin/settings-updates.php');
			
		}
		
		
		// actions
		add_action('init',										array($this, 'register_assets'));
		add_action('acf/include_field_types',					array($this, 'include_field_types'), 5);
		add_action('acf/input/admin_enqueue_scripts',			array($this, 'input_admin_enqueue_scripts'));
		add_action('acf/field_group/admin_enqueue_scripts',		array($this, 'field_group_admin_enqueue_scripts'));
		add_action('acf/field_group/admin_l10n',				array($this, 'field_group_admin_l10n'));
		
		
		// filters
		add_filter('acf/get_valid_field',						array($this, 'get_valid_field'), 11, 1);
		add_filter('acf/prepare_field_for_export', 				array($this, 'prepare_field_for_export'));
		add_filter('acf/prepare_field_for_import', 				array($this, 'prepare_field_for_import'));
		
	}
	
	
	/*
	*  include_field_types
	*
	*  description
	*
	*  @type	function
	*  @date	21/10/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function include_field_types() {
		
		acf_include('pro/fields/repeater.php');
		acf_include('pro/fields/flexible-content.php');
		acf_include('pro/fields/gallery.php');
		acf_include('pro/fields/clone.php');
		
	}
	
	
	/*
	*  get_valid_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_field( $field ) {
		
		// extract old width
		$width = acf_extract_var( $field, 'column_width' );
		
		
		// if old width, update the new width
		if( $width ) {
			
			$field['wrapper']['width'] = $width;
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  register_assets
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function register_assets() {
		
		// min
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		
		// register scripts
		wp_register_script( 'acf-pro-input', acf_get_dir( "pro/assets/js/acf-pro-input{$min}.js" ), false, acf_get_setting('version') );
		wp_register_script( 'acf-pro-field-group', acf_get_dir( "pro/assets/js/acf-pro-field-group{$min}.js" ), false, acf_get_setting('version') );
		
		
		// register styles
		wp_register_style( 'acf-pro-input', acf_get_dir( 'pro/assets/css/acf-pro-input.css' ), false, acf_get_setting('version') ); 
		wp_register_style( 'acf-pro-field-group', acf_get_dir( 'pro/assets/css/acf-pro-field-group.css' ), false, acf_get_setting('version') ); 
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script('acf-pro-input');
	
	
		// styles
		wp_enqueue_style('acf-pro-input');
		
	}
	
	
	/*
	*  field_group_admin_l10n
	*
	*  description
	*
	*  @type	function
	*  @date	1/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_l10n( $l10n ) {
		
		// append
		$l10n['flexible_content'] = array(
			'layout_warning' => __('Flexible Content requires at least 1 layout','acf')
		);
		
		
		// return
		return $l10n;
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script('acf-pro-field-group');
	
	
		// styles
		wp_enqueue_style('acf-pro-field-group');
		
	}
	
	
	
	/*
	*  prepare_field_for_export
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_export( $field ) {
		
		// sub field (parent_layout)
		acf_extract_var( $field, 'parent_layout');
		
		
		// repeater
		if( $field['type'] == 'repeater' ) {
			
			$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
		
		// flexible content
		} elseif( $field['type'] == 'flexible_content' ) {
			
			foreach( $field['layouts'] as $l => $layout ) {
				
				$field['layouts'][ $l ]['sub_fields'] = acf_prepare_fields_for_export( $layout['sub_fields'] );
			
			}

		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_import
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_import( $field ) {
		
		// var
		$extra = array();
		
		
		// sub fields
		if( $field['type'] == 'repeater' ) {
			
			// extract sub fields
			$sub_fields = acf_extract_var( $field, 'sub_fields');
			
			
			// reset field setting
			$field['sub_fields'] = array();
			
			
			if( !empty($sub_fields) ) {
			
				foreach( array_keys($sub_fields) as $i ) {
					
					// extract sub field
					$sub_field = acf_extract_var( $sub_fields, $i );
							
					
					// attributes
					$sub_field['parent'] = $field['key'];
					
					
					// append to extra
					$extra[] = $sub_field;
					
				}
				
			}
			
		} elseif( $field['type'] == 'flexible_content' ) {
			
			// extract layouts
			$layouts = acf_extract_var( $field, 'layouts');
			
			
			// reset field setting
			$field['layouts'] = array();
			
			
			// validate layouts
			if( !empty($layouts) ) {
				
				// loop over layouts
				foreach( array_keys($layouts) as $i ) {
					
					// extract layout
					$layout = acf_extract_var( $layouts, $i );
					
					
					// get valid layout (fixes ACF4 export code bug undefined index 'key')
					if( empty($layout['key']) ) {
						
						$layout['key'] = uniqid();
						
					}
					
					
					// extract sub fields
					$sub_fields = acf_extract_var( $layout, 'sub_fields');
					
					
					// validate sub fields
					if( !empty($sub_fields) ) {
						
						// loop over sub fields
						foreach( array_keys($sub_fields) as $j ) {
							
							// extract sub field
							$sub_field = acf_extract_var( $sub_fields, $j );
							
							
							// attributes
							$sub_field['parent'] = $field['key'];
							$sub_field['parent_layout'] = $layout['key'];
							
							
							// append to extra
							$extra[] = $sub_field;
							
						}
						
					}
					
					
					// append to layout
					$field['layouts'][] = $layout;
				
				}
				
			}

		}
		
		
		// extra
		if( !empty($extra) ) {
			
			array_unshift($extra, $field);
			
			return $extra;
			
		}
		
		
		// return
		return $field;
		
	}
	 
}


// instantiate
new acf_pro();


// end class
endif;

?>
