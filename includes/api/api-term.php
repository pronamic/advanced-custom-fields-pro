<?php

/*
*  acf_get_taxonomies
*
*  Returns an array of taxonomy names.
*
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	array $args An array of args used in the get_taxonomies() function.
*  @return	array An array of taxonomy names.
*/

function acf_get_taxonomies( $args = array() ) {

	// vars
	$taxonomies = array();
	
	// get taxonomy objects
	$objects = get_taxonomies( $args, 'objects' );
	
	// loop
	foreach( $objects as $i => $object ) {
		
		// bail early if is builtin (WP) private post type
		// - nav_menu_item, revision, customize_changeset, etc
		if( $object->_builtin && !$object->public ) continue;
		
		// append
		$taxonomies[] = $i;
	}
	
	// filter
	$taxonomies = apply_filters('acf/get_taxonomies', $taxonomies, $args);
	
	// return
	return $taxonomies;
}

/*
*  acf_get_taxonomy_labels
*
*  Returns an array of taxonomies in the format "name => label" for use in a select field.
*
*  @date	3/8/18
*  @since	5.7.2
*
*  @param	array $taxonomies Optional. An array of specific taxonomies to return.
*  @return	array
*/

function acf_get_taxonomy_labels( $taxonomies = array() ) {
	
	// default
	if( empty($taxonomies) ) {
		$taxonomies = acf_get_taxonomies();
	}
	
	// vars
	$ref = array();
	$data = array();
	
	// loop
	foreach( $taxonomies as $taxonomy ) {
		
		// vars
		$object = get_taxonomy( $taxonomy );
		$label = $object->labels->singular_name;
		
		// append
		$data[ $taxonomy ] = $label;
		
		// increase counter
		if( !isset($ref[ $label ]) ) {
			$ref[ $label ] = 0;
		}
		$ref[ $label ]++;
	}
	
	// show taxonomy name next to label for shared labels
	foreach( $data as $taxonomy => $label ) {
		if( $ref[$label] > 1 ) {
			$data[ $taxonomy ] .= ' (' . $taxonomy . ')';
		}
	}
	
	// return
	return $data;
}

/**
*  acf_get_grouped_terms
*
*  Returns an array of terms for the given query $args and groups by taxonomy name.
*
*  @date	2/8/18
*  @since	5.7.2
*
*  @param	array $args An array of args used in the get_terms() function.
*  @return	array
*/

function acf_get_grouped_terms( $args ) {
	
	// vars
	$data = array();
	
	// defaults
	$args = wp_parse_args($args, array(
		'taxonomy'					=> 'category',
		'hide_empty'				=> false,
		'update_term_meta_cache'	=> false,
	));
	
	// vars
	$taxonomies = acf_get_taxonomy_labels( acf_get_array($args['taxonomy']) );
	$is_single = (count($taxonomies) == 1);
	
	// add filter to group results by taxonomy
	if( !$is_single ) {
		add_filter('terms_clauses', '_acf_terms_clauses', 10, 3);
	}
	
	// get terms
	$terms = get_terms( $args );
	
	// remove this filter (only once)
	if( !$is_single ) {
		remove_filter('terms_clauses', '_acf_terms_clauses', 10, 3);
	}
	
	// loop
	foreach( $taxonomies as $taxonomy => $label ) {
		
		// vars
		$this_terms = array();
		
		// populate $this_terms
		foreach( $terms as $term ) {
			if( $term->taxonomy == $taxonomy ) {
				$this_terms[] = $term;
			}
		}
		
		// bail early if no $items
		if( empty($this_terms) ) continue;
		
		// sort into hierachial order
		// this will fail if a search has taken place because parents wont exist
		if( is_taxonomy_hierarchical($taxonomy) && empty($args['s'])) {
			
			// get all terms from this taxonomy
			$all_terms = get_terms(array_merge($args, array(
				'number'		=> 0,
				'offset'		=> 0,
				'taxonomy'		=> $taxonomy
			)));
			
			// vars
			$length = count($this_terms);
			$offset = 0;
			
			// find starting point (offset)
			foreach( $all_terms as $i => $term ) {
				if( $term->term_id == $this_terms[0]->term_id ) {
					$offset = $i;
					break;
				}
			}
			
			// order terms
			$parent = acf_maybe_get( $args, 'parent', 0 );
			$parent = acf_maybe_get( $args, 'child_of', $parent );
			$ordered_terms = _get_term_children( $parent, $all_terms, $taxonomy );
			
			// compare aray lengths
			// if $ordered_posts is smaller than $all_posts, WP has lost posts during the get_page_children() function
			// this is possible when get_post( $args ) filter out parents (via taxonomy, meta and other search parameters)
			if( count($ordered_terms) == count($all_terms) ) {
				$this_terms = array_slice($ordered_terms, $offset, $length);
			}
		}
		
		// populate group
		$data[ $label ] = array();
		foreach( $this_terms as $term ) {
			$data[ $label ][ $term->term_id ] = $term;
		}	
	}
	
	// return
	return $data;
}

/**
*  _acf_terms_clauses
*
*  Used in the 'terms_clauses' filter to order terms by taxonomy name.
*
*  @date	2/8/18
*  @since	5.7.2
*
*  @param	array $pieces     Terms query SQL clauses.
*  @param	array $taxonomies An array of taxonomies.
*  @param	array $args       An array of terms query arguments.
*  @return	array $pieces
*/

function _acf_terms_clauses( $pieces, $taxonomies, $args ) {
	
	// prepend taxonomy to 'orderby' SQL
	if( is_array($taxonomies) ) {
		$sql = "FIELD(tt.taxonomy,'" . implode("', '", array_map('esc_sql', $taxonomies)) . "')";
		$pieces['orderby'] = str_replace("ORDER BY", "ORDER BY $sql,", $pieces['orderby']);
	}
	
	// return	
	return $pieces;
}

/**
*  acf_get_pretty_taxonomies
*
*  Deprecated in favor of acf_get_taxonomy_labels() function.
*
*  @date		7/10/13
*  @since		5.0.0
*  @deprecated	5.7.2
*/

function acf_get_pretty_taxonomies( $taxonomies = array() ) {
	return acf_get_taxonomy_labels( $taxonomies );
}

?>