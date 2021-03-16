<?php
/*
Plugin Name: Advanced Custom Fields PRO
Plugin URI: https://www.advancedcustomfields.com
Description: Customize WordPress with powerful, professional and intuitive fields.
Version: 5.9.5
Author: Elliot Condon
Author URI: https://www.advancedcustomfields.com
Text Domain: acf
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF') ) :

class ACF {
	
	/** @var string The plugin version number. */
	var $version = '5.9.5';
	
	/** @var array The plugin settings array. */
	var $settings = array();
	
	/** @var array The plugin data array. */
	var $data = array();
	
	/** @var array Storage for class instances. */
	var $instances = array();
	
	/**
	 * __construct
	 *
	 * A dummy constructor to ensure ACF is only setup once.
	 *
	 * @date	23/06/12
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */	
	function __construct() {
		// Do nothing.
	}
	
	/**
	 * initialize
	 *
	 * Sets up the ACF plugin.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
	function initialize() {
		
		// Define constants.
		$this->define( 'ACF', true );
		$this->define( 'ACF_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ACF_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'ACF_VERSION', $this->version );
		$this->define( 'ACF_MAJOR_VERSION', 5 );
		
		// Define settings.
		$this->settings = array(
			'name'						=> __('Advanced Custom Fields', 'acf'),
			'slug'						=> dirname( ACF_BASENAME ),
			'version'					=> ACF_VERSION,
			'basename'					=> ACF_BASENAME,
			'path'						=> ACF_PATH,
			'file'						=> __FILE__,
			'url'						=> plugin_dir_url( __FILE__ ),
			'show_admin'				=> true,
			'show_updates'				=> true,
			'stripslashes'				=> false,
			'local'						=> true,
			'json'						=> true,
			'save_json'					=> '',
			'load_json'					=> array(),
			'default_language'			=> '',
			'current_language'			=> '',
			'capability'				=> 'manage_options',
			'uploader'					=> 'wp',
			'autoload'					=> false,
			'l10n'						=> true,
			'l10n_textdomain'			=> '',
			'google_api_key'			=> '',
			'google_api_client'			=> '',
			'enqueue_google_maps'		=> true,
			'enqueue_select2'			=> true,
			'enqueue_datepicker'		=> true,
			'enqueue_datetimepicker'	=> true,
			'select2_version'			=> 4,
			'row_index_offset'			=> 1,
			'remove_wp_meta_box'		=> true
		);
		
		// Include utility functions.
		include_once( ACF_PATH . 'includes/acf-utility-functions.php');
		
		// Include previous API functions.
		acf_include('includes/api/api-helpers.php');
		acf_include('includes/api/api-template.php');
		acf_include('includes/api/api-term.php');
		
		// Include classes.
		acf_include('includes/class-acf-data.php');
		acf_include('includes/fields/class-acf-field.php');
		acf_include('includes/locations/abstract-acf-legacy-location.php');
		acf_include('includes/locations/abstract-acf-location.php');
		
		// Include functions.
		acf_include('includes/acf-helper-functions.php');
		acf_include('includes/acf-hook-functions.php');
		acf_include('includes/acf-field-functions.php');
		acf_include('includes/acf-field-group-functions.php');
		acf_include('includes/acf-form-functions.php');
		acf_include('includes/acf-meta-functions.php');
		acf_include('includes/acf-post-functions.php');
		acf_include('includes/acf-user-functions.php');
		acf_include('includes/acf-value-functions.php');
		acf_include('includes/acf-input-functions.php');
		acf_include('includes/acf-wp-functions.php');
		
		// Include core.
		acf_include('includes/fields.php');
		acf_include('includes/locations.php');
		acf_include('includes/assets.php');
		acf_include('includes/compatibility.php');
		acf_include('includes/deprecated.php');
		acf_include('includes/l10n.php');
		acf_include('includes/local-fields.php');
		acf_include('includes/local-meta.php');
		acf_include('includes/local-json.php');
		acf_include('includes/loop.php');
		acf_include('includes/media.php');
		acf_include('includes/revisions.php');
		acf_include('includes/updates.php');
		acf_include('includes/upgrades.php');
		acf_include('includes/validation.php');
		
		// Include ajax.
		acf_include('includes/ajax/class-acf-ajax.php');
		acf_include('includes/ajax/class-acf-ajax-check-screen.php');
		acf_include('includes/ajax/class-acf-ajax-user-setting.php');
		acf_include('includes/ajax/class-acf-ajax-upgrade.php');
		acf_include('includes/ajax/class-acf-ajax-query.php');
		acf_include('includes/ajax/class-acf-ajax-query-users.php');
		acf_include('includes/ajax/class-acf-ajax-local-json-diff.php');
		
		// Include forms.
		acf_include('includes/forms/form-attachment.php');
		acf_include('includes/forms/form-comment.php');
		acf_include('includes/forms/form-customizer.php');
		acf_include('includes/forms/form-front.php');
		acf_include('includes/forms/form-nav-menu.php');
		acf_include('includes/forms/form-post.php');
		acf_include('includes/forms/form-gutenberg.php');
		acf_include('includes/forms/form-taxonomy.php');
		acf_include('includes/forms/form-user.php');
		acf_include('includes/forms/form-widget.php');
		
		// Include admin.
		if( is_admin() ) {
			acf_include('includes/admin/admin.php');
			acf_include('includes/admin/admin-field-group.php');
			acf_include('includes/admin/admin-field-groups.php');
			acf_include('includes/admin/admin-notices.php');
			acf_include('includes/admin/admin-tools.php');
			acf_include('includes/admin/admin-upgrade.php');
		}
		
		// Include legacy.
		acf_include('includes/legacy/legacy-locations.php');
		
		// Include PRO.
		acf_include('pro/acf-pro.php');
		
		// Include tests.
		if( defined('ACF_DEV') && ACF_DEV ) {
			acf_include('tests/tests.php');
		}
		
		// Add actions.
		add_action( 'init', array($this, 'init'), 5 );
		add_action( 'init', array($this, 'register_post_types'), 5 );
		add_action( 'init', array($this, 'register_post_status'), 5 );
		
		// Add filters.
		add_filter( 'posts_where', array($this, 'posts_where'), 10, 2 );
	}
	
	/**
	 * init
	 *
	 * Completes the setup process on "init" of earlier.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
	function init() {
		
		// Bail early if called directly from functions.php or plugin file.
		if( !did_action('plugins_loaded') ) {
			return;
		}
		
		// This function may be called directly from template functions. Bail early if already did this.
		if( acf_did('init') ) {
			return;
		}
		
		// Update url setting. Allows other plugins to modify the URL (force SSL).
		acf_update_setting( 'url', plugin_dir_url( __FILE__ ) );
		
		// Load textdomain file.
		acf_load_textdomain();
		
		// Include 3rd party compatiblity.
		acf_include('includes/third-party.php');
		
		// Include wpml support.
		if( defined('ICL_SITEPRESS_VERSION') ) {
			acf_include('includes/wpml.php');
		}
		
		// Include fields.
		acf_include('includes/fields/class-acf-field-text.php');
		acf_include('includes/fields/class-acf-field-textarea.php');
		acf_include('includes/fields/class-acf-field-number.php');
		acf_include('includes/fields/class-acf-field-range.php');
		acf_include('includes/fields/class-acf-field-email.php');
		acf_include('includes/fields/class-acf-field-url.php');
		acf_include('includes/fields/class-acf-field-password.php');
		acf_include('includes/fields/class-acf-field-image.php');
		acf_include('includes/fields/class-acf-field-file.php');
		acf_include('includes/fields/class-acf-field-wysiwyg.php');
		acf_include('includes/fields/class-acf-field-oembed.php');
		acf_include('includes/fields/class-acf-field-select.php');
		acf_include('includes/fields/class-acf-field-checkbox.php');
		acf_include('includes/fields/class-acf-field-radio.php');
		acf_include('includes/fields/class-acf-field-button-group.php');
		acf_include('includes/fields/class-acf-field-true_false.php');
		acf_include('includes/fields/class-acf-field-link.php');
		acf_include('includes/fields/class-acf-field-post_object.php');
		acf_include('includes/fields/class-acf-field-page_link.php');
		acf_include('includes/fields/class-acf-field-relationship.php');
		acf_include('includes/fields/class-acf-field-taxonomy.php');
		acf_include('includes/fields/class-acf-field-user.php');
		acf_include('includes/fields/class-acf-field-google-map.php');
		acf_include('includes/fields/class-acf-field-date_picker.php');
		acf_include('includes/fields/class-acf-field-date_time_picker.php');
		acf_include('includes/fields/class-acf-field-time_picker.php');
		acf_include('includes/fields/class-acf-field-color_picker.php');
		acf_include('includes/fields/class-acf-field-message.php');
		acf_include('includes/fields/class-acf-field-accordion.php');
		acf_include('includes/fields/class-acf-field-tab.php');
		acf_include('includes/fields/class-acf-field-group.php');
		
		/**
		 * Fires after field types have been included.
		 *
		 * @date	28/09/13
		 * @since	5.0.0
		 *
		 * @param	int $major_version The major version of ACF.
		 */
		do_action( 'acf/include_field_types', ACF_MAJOR_VERSION );
		
		// Include locations.
		acf_include('includes/locations/class-acf-location-post-type.php');
		acf_include('includes/locations/class-acf-location-post-template.php');
		acf_include('includes/locations/class-acf-location-post-status.php');
		acf_include('includes/locations/class-acf-location-post-format.php');
		acf_include('includes/locations/class-acf-location-post-category.php');
		acf_include('includes/locations/class-acf-location-post-taxonomy.php');
		acf_include('includes/locations/class-acf-location-post.php');
		acf_include('includes/locations/class-acf-location-page-template.php');
		acf_include('includes/locations/class-acf-location-page-type.php');
		acf_include('includes/locations/class-acf-location-page-parent.php');
		acf_include('includes/locations/class-acf-location-page.php');
		acf_include('includes/locations/class-acf-location-current-user.php');
		acf_include('includes/locations/class-acf-location-current-user-role.php');
		acf_include('includes/locations/class-acf-location-user-form.php');
		acf_include('includes/locations/class-acf-location-user-role.php');
		acf_include('includes/locations/class-acf-location-taxonomy.php');
		acf_include('includes/locations/class-acf-location-attachment.php');
		acf_include('includes/locations/class-acf-location-comment.php');
		acf_include('includes/locations/class-acf-location-widget.php');
		acf_include('includes/locations/class-acf-location-nav-menu.php');
		acf_include('includes/locations/class-acf-location-nav-menu-item.php');
		
		/**
		 * Fires after location types have been included.
		 *
		 * @date	28/09/13
		 * @since	5.0.0
		 *
		 * @param	int $major_version The major version of ACF.
		 */
		do_action( 'acf/include_location_rules', ACF_MAJOR_VERSION );
		
		/**
		 * Fires during initialization. Used to add local fields.
		 *
		 * @date	28/09/13
		 * @since	5.0.0
		 *
		 * @param	int $major_version The major version of ACF.
		 */
		do_action( 'acf/include_fields', ACF_MAJOR_VERSION );
		
		/**
		 * Fires after ACF is completely "initialized".
		 *
		 * @date	28/09/13
		 * @since	5.0.0
		 *
		 * @param	int $major_version The major version of ACF.
		 */
		do_action( 'acf/init', ACF_MAJOR_VERSION );
	}
	
	/**
	 * register_post_types
	 *
	 * Registers the ACF post types.
	 *
	 * @date	22/10/2015
	 * @since	5.3.2
	 *
	 * @param	void
	 * @return	void
	 */	
	function register_post_types() {
		
		// Vars.
		$cap = acf_get_setting('capability');
		
		// Register the Field Group post type.
		register_post_type('acf-field-group', array(
			'labels'			=> array(
			    'name'					=> __( 'Field Groups', 'acf' ),
				'singular_name'			=> __( 'Field Group', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field Group' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field Group' , 'acf' ),
			    'new_item'				=> __( 'New Field Group' , 'acf' ),
			    'view_item'				=> __( 'View Field Group', 'acf' ),
			    'search_items'			=> __( 'Search Field Groups', 'acf' ),
			    'not_found'				=> __( 'No Field Groups found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Field Groups found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'hierarchical'		=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'supports' 			=> array('title'),
			'rewrite'			=> false,
			'query_var'			=> false,
		));
		
		
		// Register the Field post type.
		register_post_type('acf-field', array(
			'labels'			=> array(
			    'name'					=> __( 'Fields', 'acf' ),
				'singular_name'			=> __( 'Field', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field' , 'acf' ),
			    'new_item'				=> __( 'New Field' , 'acf' ),
			    'view_item'				=> __( 'View Field', 'acf' ),
			    'search_items'			=> __( 'Search Fields', 'acf' ),
			    'not_found'				=> __( 'No Fields found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Fields found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'hierarchical'		=> true,
			'show_ui'			=> false,
			'show_in_menu'		=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'supports' 			=> array('title'),
			'rewrite'			=> false,
			'query_var'			=> false,
		));
	}
	
	/**
	 * register_post_status
	 *
	 * Registers the ACF post statuses.
	 *
	 * @date	22/10/2015
	 * @since	5.3.2
	 *
	 * @param	void
	 * @return	void
	 */
	function register_post_status() {
		
		// Register the Disabled post status.
		register_post_status('acf-disabled', array(
			'label'                     => _x( 'Disabled', 'post status', 'acf' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>', 'acf' ),
		));
	}
	
	/**
	 * posts_where
	 *
	 * Filters the $where clause allowing for custom WP_Query args.
	 *
	 * @date	31/8/19
	 * @since	5.8.1
	 *
	 * @param	string $where The WHERE clause.
	 * @return	WP_Query $wp_query The query object.
	 */
	function posts_where( $where, $wp_query ) {
		global $wpdb;
		
		// Add custom "acf_field_key" arg.
		if( $field_key = $wp_query->get('acf_field_key') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $field_key );
	    }
	    
	    // Add custom "acf_field_name" arg.
	    if( $field_name = $wp_query->get('acf_field_name') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $field_name );
	    }
	    
	    // Add custom "acf_group_key" arg.
		if( $group_key = $wp_query->get('acf_group_key') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $group_key );
	    }
	    
	    // Return.
	    return $where;
	}
	
	/**
	 * define
	 *
	 * Defines a constant if doesnt already exist.
	 *
	 * @date	3/5/17
	 * @since	5.5.13
	 *
	 * @param	string $name The constant name.
	 * @param	mixed $value The constant value.
	 * @return	void
	 */
	function define( $name, $value = true ) {
		if( !defined($name) ) {
			define( $name, $value );
		}
	}
	
	/**
	 * has_setting
	 *
	 * Returns true if a setting exists for this name.
	 *
	 * @date	2/2/18
	 * @since	5.6.5
	 *
	 * @param	string $name The setting name.
	 * @return	boolean
	 */
	function has_setting( $name ) {
		return isset($this->settings[ $name ]);
	}
	
	/**
	 * get_setting
	 *
	 * Returns a setting or null if doesn't exist.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	string $name The setting name.
	 * @return	mixed
	 */
	function get_setting( $name ) {
		return isset($this->settings[ $name ]) ? $this->settings[ $name ] : null;
	}
	
	/**
	 * update_setting
	 *
	 * Updates a setting for the given name and value.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	string $name The setting name.
	 * @param	mixed $value The setting value.
	 * @return	true
	 */
	function update_setting( $name, $value ) {
		$this->settings[ $name ] = $value;
		return true;
	}
	
	/**
	 * get_data
	 *
	 * Returns data or null if doesn't exist.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	string $name The data name.
	 * @return	mixed
	 */
	function get_data( $name ) {
		return isset($this->data[ $name ]) ? $this->data[ $name ] : null;
	}
	
	/**
	 * set_data
	 *
	 * Sets data for the given name and value.
	 *
	 * @date	28/09/13
	 * @since	5.0.0
	 *
	 * @param	string $name The data name.
	 * @param	mixed $value The data value.
	 * @return	void
	 */
	function set_data( $name, $value ) {
		$this->data[ $name ] = $value;
	}
	
	/**
	 * get_instance
	 *
	 * Returns an instance or null if doesn't exist.
	 *
	 * @date	13/2/18
	 * @since	5.6.9
	 *
	 * @param	string $class The instance class name.
	 * @return	object
	 */
	function get_instance( $class ) {
		$name = strtolower($class);
		return isset($this->instances[ $name ]) ? $this->instances[ $name ] : null;
	}
	
	/**
	 * new_instance
	 *
	 * Creates and stores an instance of the given class.
	 *
	 * @date	13/2/18
	 * @since	5.6.9
	 *
	 * @param	string $class The instance class name.
	 * @return	object
	 */
	function new_instance( $class ) {
		$instance = new $class();
		$name = strtolower($class);
		$this->instances[ $name ] = $instance;
		return $instance;
	}
	
	/**
	 * Magic __isset method for backwards compatibility.
	 *
	 * @date	24/4/20
	 * @since	5.9.0
	 *
	 * @param	string $key Key name.
	 * @return	bool
	 */
	public function __isset( $key ) {
		return in_array( $key, array( 'locations', 'json' ) );
	}
	
	/**
	 * Magic __get method for backwards compatibility.
	 *
	 * @date	24/4/20
	 * @since	5.9.0
	 *
	 * @param	string $key Key name.
	 * @return	mixed
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'locations':
				return acf_get_instance( 'ACF_Legacy_Locations' );
			case 'json':
				return acf_get_instance( 'ACF_Local_JSON' );
		}
		return null;
	}
}

/*
 * acf
 *
 * The main function responsible for returning the one true acf Instance to functions everywhere.
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php $acf = acf(); ?>
 *
 * @date	4/09/13
 * @since	4.3.0
 *
 * @param	void
 * @return	ACF
 */
function acf() {
	global $acf;
	
	// Instantiate only once.
	if( !isset($acf) ) {
		$acf = new ACF();
		$acf->initialize();
	}
	return $acf;
}

// Instantiate.
acf();

endif; // class_exists check
