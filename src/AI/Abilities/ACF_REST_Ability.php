<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace ACF\AI\Abilities;

use WP_Ability;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF REST Ability
 *
 * Custom ability class that extends WP_Ability to skip output validation.
 * This is needed because REST API schemas don't always match Abilities API schemas exactly,
 * but we want to proxy directly to REST API endpoints.
 */
class ACF_REST_Ability extends WP_Ability {

	/**
	 * Override validate_output to always return true.
	 *
	 * Since we're proxying to WordPress REST API endpoints that have their own
	 * validation, we trust their output and skip Abilities API output validation.
	 *
	 * @since 6.8.0
	 *
	 * @param mixed $output The output to validate.
	 * @return true Always returns true to skip validation.
	 */
	protected function validate_output( $output ) {
		return true;
	}
}
