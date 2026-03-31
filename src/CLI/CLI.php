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

namespace ACF\CLI;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrapper for ACF WP-CLI commands.
 */
class CLI {

	/**
	 * Registers all free ACF WP-CLI commands.
	 *
	 * @since 6.8
	 */
	public function __construct() {
		\WP_CLI::add_command( 'acf json', JsonCommand::class );
	}
}
