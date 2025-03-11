<?php
/**
 * Adds support for saving/retrieving values from term meta.
 *
 * @package    AdvancedCustomFields
 * @subpackage Meta
 * @author     WP Engine
 */

namespace ACF\Meta;

/**
 * A class to add support for saving to term meta.
 */
class Term extends MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = 'term';
}
