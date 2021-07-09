<?php 

/**
 * acf_filter_attrs
 *
 * Filters out empty attrs from the provided array.
 *
 * @date	11/6/19
 * @since	5.8.1
 *
 * @param	array $attrs The array of attrs.
 * @return	array
 */
function acf_filter_attrs( $attrs ) {
	
	// Filter out empty attrs but allow "0" values.
	$filtered = array_filter( $attrs, 'acf_not_empty' );
	
	// Correct specific attributes (required="required").
	foreach( array('required', 'readonly', 'disabled', 'multiple') as $key ) {
		unset($filtered[ $key ]);
		if( !empty($attrs[ $key ]) ) {
			$filtered[ $key ] = $key;
		}
	}
	return $filtered;
}

/**
 * acf_esc_attrs
 *
 * Generated valid HTML from an array of attrs.
 *
 * @date	11/6/19
 * @since	5.8.1
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_esc_attrs( $attrs ) {
	$html = '';
	
	// Loop over attrs and validate data types.
	foreach( $attrs as $k => $v ) {
		
		// String (but don't trim value).
		if( is_string($v) && ($k !== 'value') ) {
			$v = trim($v);
			
		// Boolean	
		} elseif( is_bool($v) ) {
			$v = $v ? 1 : 0;
			
		// Object
		} elseif( is_array($v) || is_object($v) ) {
			$v = json_encode($v);
		}
		
		// Generate HTML.
		$html .= sprintf( ' %s="%s"', esc_attr($k), esc_attr($v) );
	}
	
	// Return trimmed.
	return trim( $html );
}

if ( defined('ACF_EXPERIMENTAL_ESC_HTML') && ACF_EXPERIMENTAL_ESC_HTML ) {

	/**
	 * Sanitizes text content and strips out disallowed HTML.
	 *
	 * This function emulates `wp_kses_post()` with a context of "acf" for extensibility.
	 *
	 * @date	16/4/21
	 * @since	5.9.6
	 *
	 * @param	string $string
	 * @return	string
	 */
	function acf_esc_html( $string = '' ) {
		return wp_kses( (string) $string, 'acf' );
	}
	
	/**
	 * Private callback for the "wp_kses_allowed_html" filter used to return allowed HTML for "acf" context.
	 *
	 * @date	16/4/21
	 * @since	5.9.6
	 *
	 * @param	array $tags An array of allowed tags.
	 * @param	string $context The context name.
	 * @return	array.
	 */
	
	function _acf_kses_allowed_html( $tags, $context ) {
		global $allowedposttags;
		
		if( $context === 'acf' ) {
			return $allowedposttags;
		}
		return $tags;
	}
	
	add_filter( 'wp_kses_allowed_html', '_acf_kses_allowed_html', 0, 2 );

} else {
	
	/**
	 * acf_esc_html
	 *
	 * Encodes <script> tags for safe HTML output.
	 *
	 * @date	12/6/19
	 * @since	5.8.1
	 *
	 * @param	string $string
	 * @return	string
	 */
	function acf_esc_html( $string = '' ) {
		$string = strval($string);
		
		// Encode "<script" tags to invalidate DOM elements.
		if( strpos($string, '<script') !== false ) {
			$string = str_replace('<script', htmlspecialchars('<script'), $string);
			$string = str_replace('</script', htmlspecialchars('</script'), $string);
		}
		return $string;
	}
}

/**
 * acf_html_input
 *
 * Returns the HTML of an input.
 *
 * @date	13/6/19
 * @since	5.8.1
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
//function acf_html_input( $attrs = array() ) {
//	return sprintf( '<input %s/>', acf_esc_attrs($attrs) );
//}

/**
 * acf_hidden_input
 *
 * Renders the HTML of a hidden input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_hidden_input( $attrs = array() ) {
	echo acf_get_hidden_input( $attrs );
}

/**
 * acf_get_hidden_input
 *
 * Returns the HTML of a hidden input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_hidden_input( $attrs = array() ) {
	return sprintf( '<input type="hidden" %s/>', acf_esc_attrs($attrs) );
}

/**
 * acf_text_input
 *
 * Renders the HTML of a text input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_text_input( $attrs = array() ) {
	echo acf_get_text_input( $attrs );
}

/**
 * acf_get_text_input
 *
 * Returns the HTML of a text input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_text_input( $attrs = array() ) {
	$attrs = wp_parse_args($attrs, array(
		'type' => 'text'
	));
	if ( isset( $attrs['value'] ) && is_string( $attrs['value'] ) ) {
		$attrs['value'] = htmlspecialchars( $attrs['value'] );
	}
	return sprintf( '<input %s/>', acf_esc_attrs( $attrs ) );
}

/**
 * acf_file_input
 *
 * Renders the HTML of a file input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_file_input( $attrs = array() ) {
	echo acf_get_file_input( $attrs );
}

/**
 * acf_get_file_input
 *
 * Returns the HTML of a file input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_file_input( $attrs = array() ) {
	return sprintf( '<input type="file" %s/>', acf_esc_attrs($attrs) );
}

/**
 * acf_textarea_input
 *
 * Renders the HTML of a textarea input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_textarea_input( $attrs = array() ) {
	echo acf_get_textarea_input( $attrs );
}

/**
 * acf_get_textarea_input
 *
 * Returns the HTML of a textarea input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_textarea_input( $attrs = array() ) {
	$value = '';
	if( isset($attrs['value']) ) {
		$value = $attrs['value'];
		unset( $attrs['value'] );
	}
	return sprintf( '<textarea %s>%s</textarea>', acf_esc_attrs($attrs), esc_textarea($value) );
}

/**
 * acf_checkbox_input
 *
 * Renders the HTML of a checkbox input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_checkbox_input( $attrs = array() ) {
	echo acf_get_checkbox_input( $attrs );
}

/**
 * acf_get_checkbox_input
 *
 * Returns the HTML of a checkbox input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_checkbox_input( $attrs = array() ) {
	
	// Allow radio or checkbox type.
	$attrs = wp_parse_args($attrs, array(
		'type' => 'checkbox'
	));
	
	// Get label.
	$label = '';
	if( isset($attrs['label']) ) {
		$label= $attrs['label'];
		unset( $attrs['label'] );
	}
	
	// Render.
	$checked = isset($attrs['checked']);
	return '<label' . ($checked ? ' class="selected"' : '') . '><input ' . acf_esc_attr($attrs) . '/> ' . acf_esc_html($label) . '</label>';
}

/**
 * acf_radio_input
 *
 * Renders the HTML of a radio input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_radio_input( $attrs = array() ) {
	echo acf_get_radio_input( $attrs );
}

/**
 * acf_get_radio_input
 *
 * Returns the HTML of a radio input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_radio_input( $attrs = array() ) {
	$attrs['type'] = 'radio';
	return acf_get_checkbox_input( $attrs );
}

/**
 * acf_select_input
 *
 * Renders the HTML of a select input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_select_input( $attrs = array() ) {
	echo acf_get_select_input( $attrs );
}

/**
 * acf_select_input
 *
 * Returns the HTML of a select input.
 *
 * @date	3/02/2014
 * @since	5.0.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_get_select_input( $attrs = array() ) {
	$value = (array) acf_extract_var( $attrs, 'value' );
	$choices = (array) acf_extract_var( $attrs, 'choices' );
	return sprintf(
		'<select %s>%s</select>',
		acf_esc_attrs($attrs),
		acf_walk_select_input($choices, $value)
	);
}

/**
 * acf_walk_select_input
 *
 * Returns the HTML of a select input's choices.
 *
 * @date	27/6/17
 * @since	5.6.0
 *
 * @param	array $choices The choices to walk through.
 * @param	array $values The selected choices.
 * @param	array $depth The current walk depth.
 * @return	string
 */
function acf_walk_select_input( $choices = array(), $values = array(), $depth = 0 ) {
	$html = '';
	
	// Sanitize values for 'selected' matching (only once).
	if( $depth == 0 ) {
		$values = array_map('esc_attr', $values);
	}
	
	// Loop over choices and append to html.
	if( $choices ) {
		foreach( $choices as $value => $label ) {
			
			// Multiple (optgroup)
			if( is_array($label) ){
				$html .= sprintf(
					'<optgroup label="%s">%s</optgroup>',
					esc_attr($value),
					acf_walk_select_input( $label, $values, $depth+1 )
				);
			
			// single (option)	
			} else {
				$attrs = array(
					'value' => $value
				);
				
				// If is selected.
				$pos = array_search( esc_attr($value), $values );
				if( $pos !== false ) {
					$attrs['selected'] = 'selected';
					$attrs['data-i'] = $pos;
				}
				$html .= sprintf( '<option %s>%s</option>', acf_esc_attr($attrs), esc_html($label) );
			}
		}
	}
	return $html;
}

/**
 * acf_clean_atts
 *
 * See acf_filter_attrs().
 *
 * @date	3/10/17
 * @since	5.6.3
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_clean_atts( $attrs ) {
	return acf_filter_attrs( $attrs );
}

/**
 * acf_esc_atts
 *
 * See acf_esc_attrs().
 *
 * @date	27/6/17
 * @since	5.6.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_esc_atts( $attrs ) {
	return acf_esc_attrs( $attrs );
}

/**
 * acf_esc_attr
 *
 * See acf_esc_attrs().
 *
 * @date	13/6/19
 * @since	5.8.1
 * @deprecated	5.6.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_esc_attr( $attrs ) {
	return acf_esc_attrs( $attrs );
}

/**
 * acf_esc_attr_e
 *
 * See acf_esc_attrs().
 *
 * @date	13/6/19
 * @since	5.8.1
 * @deprecated	5.6.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_esc_attr_e( $attrs ) {
	echo acf_esc_attrs( $attrs );
}

/**
 * acf_esc_atts_e
 *
 * See acf_esc_attrs().
 *
 * @date	13/6/19
 * @since	5.8.1
 * @deprecated	5.6.0
 *
 * @param	array $attrs The array of attrs.
 * @return	string
 */
function acf_esc_atts_e( $attrs ) {
	echo acf_esc_attrs( $attrs );
}
