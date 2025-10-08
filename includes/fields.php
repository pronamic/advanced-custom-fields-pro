<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'acf_fields' ) ) :
	class acf_fields {

		/**
		 * Contains an array of field type instances.
		 * @var array
		 */
		public $types = array();

		/**
		 * This function will setup the class functionality
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.4.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function __construct() {
			/* do nothing */
		}

		/**
		 * This function will register a field type instance based on a class name or instance.
		 * It will return the instance for further use.
		 *
		 * @since 5.4.0
		 *
		 * @param   mixed $field_class Either a class name (string) or instance of acf_field.
		 * @return  acf_field The instance of acf_field.
		 */
		public function register_field_type( $field_class ) {
			// Allow registering an instance.
			if ( $field_class instanceof acf_field ) {
				$this->types[ $field_class->name ] = $field_class;
				return $field_class;
			}

			// Allow registering a loaded class name.
			$instance                       = new $field_class();
			$this->types[ $instance->name ] = $instance;
			return $instance;
		}

		/**
		 * This function will return a field type instance
		 *
		 * @type    function
		 * @date    6/07/2016
		 * @since   5.4.0
		 *
		 * @param   $name (string)
		 * @return  (mixed)
		 */
		function get_field_type( $name ) {
			return isset( $this->types[ $name ] ) ? $this->types[ $name ] : null;
		}


		/**
		 * This function will return true if a field type exists
		 *
		 * @type    function
		 * @date    6/07/2016
		 * @since   5.4.0
		 *
		 * @param   $name (string)
		 * @return  (mixed)
		 */
		function is_field_type( $name ) {
			return isset( $this->types[ $name ] );
		}


		/**
		 * This function will store a basic array of info about the field type
		 * to later be overriden by the above register_field_type function
		 *
		 * @type    function
		 * @date    29/5/17
		 * @since   5.6.0
		 *
		 * @param   $info (array)
		 * @return  n/a
		 */
		function register_field_type_info( $info ) {

			// convert to object
			$instance                       = (object) $info;
			$this->types[ $instance->name ] = $instance;
		}


		/**
		 * This function will return an array of all field types
		 *
		 * @type    function
		 * @date    6/07/2016
		 * @since   5.4.0
		 *
		 * @param   $name (string)
		 * @return  (mixed)
		 */
		function get_field_types() {
			return $this->types;
		}
	}


	// initialize
	acf()->fields = new acf_fields();
endif; // class_exists check


/**
 * alias of acf()->fields->register_field_type()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_register_field_type( $class ) {
	return acf()->fields->register_field_type( $class );
}


/**
 * alias of acf()->fields->register_field_type_info()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_register_field_type_info( $info ) {
	return acf()->fields->register_field_type_info( $info );
}


/**
 * alias of acf()->fields->get_field_type()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_get_field_type( $name ) {
	return acf()->fields->get_field_type( $name );
}


/**
 * alias of acf()->fields->get_field_types()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_get_field_types( $args = array() ) {

	// default
	$args = wp_parse_args(
		$args,
		array(
			'public' => true,    // true, false
		)
	);

	// get field types
	$field_types = acf()->fields->get_field_types();

	// filter
	return wp_filter_object_list( $field_types, $args );
}


/**
 * acf_get_field_types_info
 *
 * Returns an array containing information about each field type
 *
 * @date    18/6/18
 * @since   5.6.9
 *
 * @param   type $var Description. Default.
 * @return  type Description.
 */
function acf_get_field_types_info( $args = array() ) {

	// vars
	$data        = array();
	$field_types = acf_get_field_types();

	// loop
	foreach ( $field_types as $type ) {
		$data[ $type->name ] = array_filter(
			array(
				'label'         => $type->label,
				'name'          => $type->name,
				'description'   => $type->description,
				'category'      => $type->category,
				'public'        => $type->public,
				'doc_url'       => $type->doc_url,
				'tutorial_url'  => $type->tutorial_url,
				'preview_image' => $type->preview_image,
				'pro'           => $type->pro,
			)
		);
	}

	// return
	return $data;
}


/**
 * alias of acf()->fields->is_field_type()
 *
 * @type    function
 * @date    31/5/17
 * @since   5.6.0
 *
 * @param   n/a
 * @return  n/a
 */
function acf_is_field_type( $name = '' ) {
	return acf()->fields->is_field_type( $name );
}


/**
 * This function will return a field type's property
 *
 * @type    function
 * @date    1/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  (array)
 */
function acf_get_field_type_prop( $name = '', $prop = '' ) {
	$type = acf_get_field_type( $name );
	return ( $type && isset( $type->$prop ) ) ? $type->$prop : null;
}


/**
 * This function will return the label of a field type
 *
 * @type    function
 * @date    1/10/13
 * @since   5.0.0
 *
 * @param   n/a
 * @return  (array)
 */
function acf_get_field_type_label( $name = '' ) {
	$label = acf_get_field_type_prop( $name, 'label' );
	return $label ? $label : '<span class="acf-js-tooltip" title="' . __( 'Field type does not exist', 'acf' ) . '">' . __( 'Unknown', 'acf' ) . '</span>';
}

/**
 * Returns the value of a field type "supports" property.
 *
 * @since 6.2.5
 *
 * @param string $name    The name of the field type.
 * @param string $prop    The name of the supports property.
 * @param mixed  $default The default value if the property is not set.
 *
 * @return mixed The value of the supports property which may be false, or $default on failure.
 */
function acf_field_type_supports( $name = '', $prop = '', $default = false ) {
	$supports = acf_get_field_type_prop( $name, 'supports' );
	if ( ! is_array( $supports ) ) {
		return $default;
	}
	return isset( $supports[ $prop ] ) ? $supports[ $prop ] : $default;
}


/**
 *
 * @deprecated
 * @see acf_is_field_type()
 *
 * @type    function
 * @date    1/10/13
 * @since   5.0.0
 *
 * @param   $type (string)
 * @return  (boolean)
 */
function acf_field_type_exists( $type = '' ) {
	return acf_is_field_type( $type );
}

/**
 * Returns an array of localised field categories.
 *
 * @since 6.1
 *
 * @return array
 */
function acf_get_field_categories_i18n() {

	$categories_i18n = array(
		'basic'      => __( 'Basic', 'acf' ),
		'content'    => __( 'Content', 'acf' ),
		'choice'     => __( 'Choice', 'acf' ),
		'relational' => __( 'Relational', 'acf' ),
		'advanced'   => __( 'Advanced', 'acf' ),
		'layout'     => __( 'Layout', 'acf' ),
		'pro'        => __( 'PRO', 'acf' ),
	);

	return apply_filters( 'acf/localized_field_categories', $categories_i18n );
}


/**
 * Returns an multi-dimentional array of field types "name => label" grouped by category
 *
 * @since   5.0.0
 *
 * @return  array
 */
function acf_get_grouped_field_types() {

	// vars
	$types  = acf_get_field_types();
	$groups = array();
	$l10n   = acf_get_field_categories_i18n();

	// loop
	foreach ( $types as $type ) {

		// translate
		$cat = $type->category;
		$cat = isset( $l10n[ $cat ] ) ? $l10n[ $cat ] : $cat;

		// append
		$groups[ $cat ][ $type->name ] = $type->label;
	}

	// filter
	$groups = apply_filters( 'acf/get_field_types', $groups );

	// return
	return $groups;
}

/**
 * Returns an array of tabs for a field type.
 * We combine a list of default tabs with filtered tabs.
 * I.E. Default tabs should be static and should not be changed by the
 * filtered tabs.
 *
 * @since   6.1
 *
 * @return array Key/value array of the default settings tabs for field type settings.
 */
function acf_get_combined_field_type_settings_tabs() {
	$default_field_type_settings_tabs = array(
		'general'           => __( 'General', 'acf' ),
		'validation'        => __( 'Validation', 'acf' ),
		'presentation'      => __( 'Presentation', 'acf' ),
		'conditional_logic' => __( 'Conditional Logic', 'acf' ),
		'advanced'          => __( 'Advanced', 'acf' ),
	);

	$field_type_settings_tabs = (array) apply_filters( 'acf/field_group/additional_field_settings_tabs', array() );

	// remove any default tab values from filter tabs.
	foreach ( $field_type_settings_tabs as $key => $tab ) {
		if ( isset( $default_field_type_settings_tabs[ $key ] ) ) {
			unset( $field_type_settings_tabs[ $key ] );
		}
	}

	$combined_field_type_settings_tabs = array_merge( $default_field_type_settings_tabs, $field_type_settings_tabs );

	return $combined_field_type_settings_tabs;
}



/**
 * Get the PRO only fields and their core metadata.
 *
 * @since 6.1
 *
 * @return array An array of all the pro field types and their field type selection required meta data.
 */
function acf_get_pro_field_types() {
	return array(
		'clone'            => array(
			'name'          => 'clone',
			'label'         => _x( 'Clone', 'noun', 'acf' ),
			'doc_url'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/clone/', 'docs', 'field-type-selection' ),
			'preview_image' => acf_get_url() . '/assets/images/field-type-previews/field-preview-clone.png',
			'description'   => __( 'This allows you to select and display existing fields. It does not duplicate any fields in the database, but loads and displays the selected fields at run-time. The Clone field can either replace itself with the selected fields or display the selected fields as a group of subfields.', 'acf' ),
			'tutorial_url'  => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/how-to-use-the-clone-field/', 'docs', 'field-type-selection' ),
			'category'      => 'layout',
			'pro'           => true,
		),
		'flexible_content' => array(
			'name'          => 'flexible_content',
			'label'         => __( 'Flexible Content', 'acf' ),
			'doc_url'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/flexible-content/', 'docs', 'field-type-selection' ),
			'preview_image' => acf_get_url() . '/assets/images/field-type-previews/field-preview-flexible-content.png',
			'description'   => __( 'This provides a simple, structured, layout-based editor. The Flexible Content field allows you to define, create and manage content with total control by using layouts and subfields to design the available blocks.', 'acf' ),
			'tutorial_url'  => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/building-layouts-with-the-flexible-content-field-in-a-theme/', 'docs', 'field-type-selection' ),
			'category'      => 'layout',
			'pro'           => true,
		),
		'gallery'          => array(
			'name'          => 'gallery',
			'label'         => __( 'Gallery', 'acf' ),
			'doc_url'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/gallery/', 'docs', 'field-type-selection' ),
			'preview_image' => acf_get_url() . '/assets/images/field-type-previews/field-preview-gallery.png',
			'description'   => __( 'This provides an interactive interface for managing a collection of attachments. Most settings are similar to the Image field type. Additional settings allow you to specify where new attachments are added in the gallery and the minimum/maximum number of attachments allowed.', 'acf' ),
			'tutorial_url'  => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/how-to-use-the-gallery-field/', 'docs', 'field-type-selection' ),
			'category'      => 'content',
			'pro'           => true,
		),
		'repeater'         => array(
			'name'          => 'repeater',
			'label'         => __( 'Repeater', 'acf' ),
			'doc_url'       => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/repeater/', 'docs', 'field-type-selection' ),
			'preview_image' => acf_get_url() . '/assets/images/field-type-previews/field-preview-repeater.png',
			'description'   => __( 'This provides a solution for repeating content such as slides, team members, and call-to-action tiles, by acting as a parent to a set of subfields which can be repeated again and again.', 'acf' ),
			'tutorial_url'  => acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/repeater/how-to-use-the-repeater-field/', 'docs', 'field-type-selection' ),
			'category'      => 'layout',
			'pro'           => true,
		),
	);
}
