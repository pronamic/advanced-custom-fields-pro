<?php

class acf_field {
	
	var $name,
		$title,
		$category,
		$defaults,
		$l10n;
	
	
	/*
	*  __construct
	*
	*  This construcor registeres many actions and filters
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// register field
		add_filter("acf/get_field_types",								array($this, 'get_field_types'), 10, 1);
		add_filter("acf/get_valid_field/type={$this->name}",			array($this, 'get_valid_field'), 10, 1);
		
		
		// value
		$this->add_filter("acf/load_value/type={$this->name}",			array($this, 'load_value'), 10, 3);
		$this->add_filter("acf/update_value/type={$this->name}",		array($this, 'update_value'), 10, 3);
		$this->add_filter("acf/format_value/type={$this->name}",		array($this, 'format_value'), 10, 3);
		$this->add_filter("acf/validate_value/type={$this->name}",		array($this, 'validate_value'), 10, 4);
		$this->add_action("acf/delete_value/type={$this->name}",		array($this, 'delete_value'), 10, 3);
		
		
		// field
		$this->add_filter("acf/load_field/type={$this->name}",				array($this, 'load_field'), 10, 1);
		$this->add_filter("acf/update_field/type={$this->name}",			array($this, 'update_field'), 10, 1);
		$this->add_filter("acf/duplicate_field/type={$this->name}",			array($this, 'duplicate_field'), 10, 1);
		$this->add_action("acf/delete_field/type={$this->name}",			array($this, 'delete_field'), 10, 1);
		$this->add_action("acf/render_field/type={$this->name}",			array($this, 'render_field'), 10, 1);
		$this->add_action("acf/render_field_settings/type={$this->name}",	array($this, 'render_field_settings'), 10, 1);
		$this->add_action("acf/prepare_field/type={$this->name}",			array($this, 'prepare_field'), 10, 1);
		$this->add_action("acf/translate_field/type={$this->name}",			array($this, 'translate_field'), 10, 1);
		
		
		// input actions
		$this->add_action("acf/input/admin_enqueue_scripts",			array($this, 'input_admin_enqueue_scripts'), 10, 0);
		$this->add_action("acf/input/admin_head",						array($this, 'input_admin_head'), 10, 0);
		$this->add_action("acf/input/form_data",						array($this, 'input_form_data'), 10, 1);
		$this->add_filter("acf/input/admin_l10n",						array($this, 'input_admin_l10n'), 10, 1);
		$this->add_action("acf/input/admin_footer",						array($this, 'input_admin_footer'), 10, 1);
		
		
		// field group actions
		$this->add_action("acf/field_group/admin_enqueue_scripts", 		array($this, 'field_group_admin_enqueue_scripts'), 10, 0);
		$this->add_action("acf/field_group/admin_head",					array($this, 'field_group_admin_head'), 10, 0);
		$this->add_action("acf/field_group/admin_footer",				array($this, 'field_group_admin_footer'), 10, 0);
	}
	
	
	/*
	*  add_filter
	*
	*  This function checks if the function is_callable before adding the filter
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		
		if( is_callable($function_to_add) )
		{
			add_filter($tag, $function_to_add, $priority, $accepted_args);
		}
	}
	
	
	/*
	*  add_action
	*
	*  This function checks if the function is_callable before adding the action
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		
		if( is_callable($function_to_add) )
		{
			add_action($tag, $function_to_add, $priority, $accepted_args);
		}
	}
	
	
	/*
	*  get_field_types()
	*
	*  This function will append the current field type to the list of available field types
	*
	*  @type	function
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$fields	(array)
	*  @return	$fields
	*/
	
	function get_field_types( $fields ) {
		
		$l10n = array(
			'basic'			=> __('Basic', 'acf'),
			'content'		=> __('Content', 'acf'),
			'choice'		=> __('Choice', 'acf'),
			'relational'	=> __('Relational', 'acf'),
			'jquery'		=> __('jQuery', 'acf'),
			'layout'		=> __('Layout', 'acf'),
		);
		
		
		// defaults
		if( !$this->category )
		{
			$this->category = 'basic';
		}
		
		
		// cat
		if( isset($l10n[ $this->category ]) )
		{
			$cat = $l10n[ $this->category ];
		}
		else
		{
			$cat = $this->category;
		}
		
		
		// add to array
		$fields[ $cat ][ $this->name ] = $this->label;
		
		
		// return array
		return $fields;
	}
	
	
	/*
	*  get_valid_field
	*
	*  This function will append default settings to a field
	*
	*  @type	filter ("acf/get_valid_field/type={$this->name}")
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array)
	*  @return	$field (array)
	*/
	
	function get_valid_field( $field ) {
		
		if( !empty($this->defaults) )
		{
			foreach( $this->defaults as $k => $v )
			{
				if( !isset($field[ $k ]) )
				{
					$field[ $k ] = $v;
				}
			}
		}
		
		return $field;
	}
	
	
	/*
	*  admin_l10n
	*
	*  This function will append l10n text translations to an array which is later passed to JS
	*
	*  @type	filter ("acf/input/admin_l10n")
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$l10n (array)
	*  @return	$l10n (array)
	*/
	
	function input_admin_l10n( $l10n ) {
		
		if( !empty($this->l10n) )
		{
			$l10n[ $this->name ] = $this->l10n;
		}
		
		return $l10n;
	}
	
	
}

?>
