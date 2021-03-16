<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Local_JSON') ) :

class ACF_Local_JSON {
	
	/**
	 * The found JSON field group files.
	 *
	 * @since 5.9.0
	 * @var array
	 */
	private $files = array();
	
	/**
	 * Constructor.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	void
	 * @return	void
	 */
	public function __construct() {
		
		// Update settings.
		acf_update_setting( 'save_json', get_stylesheet_directory() . '/acf-json' );
		acf_append_setting( 'load_json', get_stylesheet_directory() . '/acf-json' );
		
		// Add listeners.
		add_action( 'acf/update_field_group',	array( $this, 'update_field_group' ) );
		add_action( 'acf/untrash_field_group',	array( $this, 'update_field_group' ) );
		add_action( 'acf/trash_field_group',	array( $this, 'delete_field_group' ) );
		add_action( 'acf/delete_field_group',	array( $this, 'delete_field_group' ) );
		
		// Include fields.
		add_action( 'acf/include_fields', 		array( $this, 'include_fields' ) );
	}
	
	/**
	 * Returns true if this component is enabled.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	void
	 * @return	bool.
	 */
	public function is_enabled() {
		return (bool) acf_get_setting( 'json' );
	}
	
	/**
	 * Writes field group data to JSON file.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	array $field_group The field group.
	 * @return	void
	 */
	public function update_field_group( $field_group ) {
		
		// Bail early if disabled.
		if( !$this->is_enabled() ) {
			return false;
		}
		
		// Append fields.
		$field_group['fields'] = acf_get_fields( $field_group );
		
		// Save to file.
		$this->save_file( $field_group['key'], $field_group );
	}
	
	/**
	 * Deletes a field group JSON file.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	array $field_group The field group.
	 * @return	void
	 */
	public function delete_field_group( $field_group ) {
		
		// Bail early if disabled.
		if( !$this->is_enabled() ) {
			return false;
		}
		
		// WP appends '__trashed' to end of 'key' (post_name).
		$key = str_replace( '__trashed', '', $field_group['key'] );
		
		// Delete file.
		$this->delete_file( $key );
	}
	
	/**
	 * Includes all local JSON fields.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	void
	 * @return	void
	 */
	public function include_fields() {
		
		// Bail early if disabled.
		if( !$this->is_enabled() ) {
			return false;
		}
		
		// Get load paths.
		$files = $this->scan_field_groups();
		foreach( $files as $key => $file ) {
			$json = json_decode( file_get_contents( $file ), true );
	    	$json['local'] = 'json';
	    	$json['local_file'] = $file;
	    	acf_add_local_field_group( $json );
		}
	}
	
	/**
	 * Scans for JSON field groups.
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	void
	 * @return	array
	 */
	function scan_field_groups() {
		$json_files = array();
		
		// Loop over "local_json" paths and parse JSON files.
		$paths = (array) acf_get_setting( 'load_json' );
		foreach( $paths as $path ) {
			if( is_dir( $path ) ) {
				$files = scandir( $path );
				if( $files ) {
					foreach( $files as $filename ) {
						
						// Ignore hidden files.
						if( $filename[0] === '.' ) {
							continue;
						}
						
						// Ignore sub directories.
						$file = untrailingslashit( $path ) . '/' . $filename;
						if( is_dir($file) ) {
							continue;
						}
						
						// Ignore non JSON files.
						$ext = pathinfo( $filename, PATHINFO_EXTENSION );
						if( $ext !== 'json' ) {
							continue;
						}
						
						// Read JSON data.
				    	$json = json_decode( file_get_contents( $file ), true );
				    	if( !is_array($json) || !isset($json['key']) ) {
					    	continue;
				    	}
				    	
				    	// Append data.
				    	$json_files[ $json['key'] ] = $file;
					}
				}
			}
		}
		
		// Store data and return.
		$this->files = $json_files;
		return $json_files;
	}
	
	/**
	 * Returns an array of found JSON field group files. 
	 *
	 * @date	14/4/20
	 * @since	5.9.0
	 *
	 * @param	void
	 * @return	array
	 */
	public function get_files() {
		return $this->files;
	}
	
	/**
	 * Saves a field group JSON file.
	 *
	 * @date	17/4/20
	 * @since	5.9.0
	 *
	 * @param	string $key The field group key.
	 * @param	array $field_group The field group.
	 * @return	bool
	 */
	public function save_file( $key, $field_group ) {
		$path = acf_get_setting( 'save_json' );
		$file = untrailingslashit( $path ) . '/' . $key . '.json';
		if( !is_writable($path) ) {
			return false;
		}
		
		// Append modified time.
		if( $field_group['ID'] ) {
			$field_group['modified'] = get_post_modified_time( 'U', true, $field_group['ID'] );
		} else {
			$field_group['modified'] = strtotime( 'now' );
		}
		
		// Prepare for export.
		$field_group = acf_prepare_field_group_for_export( $field_group );
		
		// Save and return true if bytes were written.
		$result = file_put_contents( $file, acf_json_encode( $field_group ) );
		return is_int( $result );
	}
	
	/**
	 * Deletes a field group JSON file.
	 *
	 * @date	17/4/20
	 * @since	5.9.0
	 *
	 * @param	string $key The field group key.
	 * @return	bool True on success.
	 */
	public function delete_file( $key ) {
		$path = acf_get_setting( 'save_json' );
		$file = untrailingslashit( $path ) . '/' . $key . '.json';
		if( is_readable($file) ) {
			unlink( $file );
			return true;
		}
		return false;
	}

	/**
	 * Includes all local JSON files.
	 *
	 * @date	10/03/2014
	 * @since	5.0.0
	 * @deprecated 5.9.0
	 *
	 * @param	void
	 * @return	void
	 */
	public function include_json_folders() {
		_deprecated_function( __METHOD__, '5.9.0', 'ACF_Local_JSON::include_fields()' );
		$this->include_fields();
	}

	/**
	 * Includes local JSON files within a specific folder.
	 *
	 * @date	01/05/2017
	 * @since	5.5.13
	 * @deprecated 5.9.0
	 *
	 * @param	string $path The path to a specific JSON folder.
	 * @return	void
	 */
	public function include_json_folder( $path = '' ) {
		_deprecated_function( __METHOD__, '5.9.0' );
		// Do nothing.
	}
}

// Initialize.
acf_new_instance('ACF_Local_JSON');

endif; // class_exists check

/**
 * Returns an array of found JSON field group files.
 *
 * @date	14/4/20
 * @since	5.9.0
 *
 * @param	type $var Description. Default.
 * @return	type Description.
 */
function acf_get_local_json_files() {
	return acf_get_instance( 'ACF_Local_JSON' )->get_files();
}

/**
 * Saves a field group JSON file.
 *
 * @date	5/12/2014
 * @since	5.1.5
 *
 * @param	array $field_group The field group.
 * @return	bool
 */
function acf_write_json_field_group( $field_group ) {
	return acf_get_instance( 'ACF_Local_JSON' )->save_file( $field_group['key'], $field_group );	
}

/**
 * Deletes a field group JSON file.
 *
 * @date	5/12/2014
 * @since	5.1.5
 *
 * @param	string $key The field group key.
 * @return	bool True on success.
 */
function acf_delete_json_field_group( $key ) {
	return acf_get_instance( 'ACF_Local_JSON' )->delete_file( $key );	
}
