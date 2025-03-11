<?php
/**
 * Adds support for saving/retrieving values from post meta.
 *
 * @package    AdvancedCustomFields
 * @subpackage Meta
 * @author     WP Engine
 */

namespace ACF\Meta;

/**
 * A class to add support for saving to standard post meta.
 */
class Post extends MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = 'post';
}
