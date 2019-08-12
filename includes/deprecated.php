<?php 

// Register deprecated filters ( $deprecated, $version, $replacement ).
acf_add_deprecated_filter( 'acf/settings/export_textdomain',	'5.3.3', 'acf/settings/l10n_textdomain' );
acf_add_deprecated_filter( 'acf/settings/export_translate',		'5.3.3', 'acf/settings/l10n_field' );
acf_add_deprecated_filter( 'acf/settings/export_translate',		'5.3.3', 'acf/settings/l10n_field_group' );
acf_add_deprecated_filter( 'acf/settings/dir',					'5.6.8', 'acf/settings/url' );
acf_add_deprecated_filter( 'acf/get_valid_field',				'5.5.6', 'acf/validate_field' );
acf_add_deprecated_filter( 'acf/get_valid_field_group',			'5.5.6', 'acf/validate_field_group' );
acf_add_deprecated_filter( 'acf/get_valid_post_id',				'5.5.6', 'acf/validate_post_id' );
acf_add_deprecated_filter( 'acf/get_field_reference',			'5.6.5', 'acf/load_reference' );
acf_add_deprecated_filter( 'acf/get_field_group',				'5.7.11', 'acf/load_field_group' );
acf_add_deprecated_filter( 'acf/get_field_groups',				'5.7.11', 'acf/load_field_groups' );
acf_add_deprecated_filter( 'acf/get_fields',					'5.7.11', 'acf/load_fields' );

// Register variations for deprecated filters.
acf_add_filter_variations( 'acf/get_valid_field', array('type'), 0 );

/**
 * acf_render_field_wrap_label
 *
 * Renders the field's label.
 *
 * @date	19/9/17
 * @since	5.6.3
 * @deprecated 5.6.5
 *
 * @param	array $field The field array.
 * @return	void
 */
function acf_render_field_wrap_label( $field ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.7.11', 'acf_render_field_label()' );
	
	// Render.
	acf_render_field_label( $field );
}

/**
 * acf_render_field_wrap_description
 *
 * Renders the field's instructions.
 *
 * @date	19/9/17
 * @since	5.6.3
 * @deprecated 5.6.5
 *
 * @param	array $field The field array.
 * @return	void
 */
function acf_render_field_wrap_description( $field ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.7.11', 'acf_render_field_instructions()' );
	
	// Render.
	acf_render_field_instructions( $field );
}

/*
 * acf_get_fields_by_id
 *
 * Returns and array of fields for the given $parent_id.
 *
 * @date	27/02/2014
 * @since	5.0.0.
 * @deprecated	5.7.11
 *
 * @param	int $parent_id The parent ID.
 * @return	array
 */
function acf_get_fields_by_id( $parent_id = 0 ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.7.11', 'acf_get_fields()' );
	
	// Return fields.
	return acf_get_fields(array( 'ID' => $parent_id, 'key' => "group_$parent_id" ));
}

/**
 * acf_update_option
 *
 * A wrapper for the WP update_option but provides logic for a 'no' autoload
 *
 * @date	4/01/2014
 * @since	5.0.0
 * @deprecated	5.7.11
 *
 * @param	string $option The option name.
 * @param	string $value The option value.
 * @param	string $autoload An optional autoload value.
 * @return	bool
 */
function acf_update_option( $option = '', $value = '', $autoload = null ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.7.11', 'update_option()' );
	
	// Update.
	if( $autoload === null ) {
		$autoload = (bool) acf_get_setting('autoload');
	}
	return update_option( $option, $value, $autoload );
}

/**
 * acf_get_field_reference
 *
 * Finds the field key for a given field name and post_id.
 *
 * @date	26/1/18
 * @since	5.6.5
 * @deprecated	5.6.8
 *
 * @param	string	$field_name	The name of the field. eg 'sub_heading'
 * @param	mixed	$post_id	The post_id of which the value is saved against
 * @return	string	$reference	The field key
 */
function acf_get_field_reference( $field_name, $post_id ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.6.8', 'acf_get_reference()' );
	
	// Return reference.
	return acf_get_reference( $field_name, $post_id );
}

/**
 * acf_get_dir
 *
 * Returns the plugin url to a specified file.
 *
 * @date	28/09/13
 * @since	5.0.0
 * @deprecated	5.6.8
 *
 * @param	string $filename The specified file.
 * @return	string
 */
function acf_get_dir( $filename = '' ) {
	
	// Warning.
	_deprecated_function( __FUNCTION__, '5.6.8', 'acf_get_url()' );
	
	// Return.
	return acf_get_url( $filename );
}
