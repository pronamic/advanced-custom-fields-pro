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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_include( 'includes/rest-api/acf-rest-api-functions.php' );
acf_include( 'includes/rest-api/class-acf-rest-api.php' );
acf_include( 'includes/rest-api/class-acf-rest-embed-links.php' );
acf_include( 'includes/rest-api/class-acf-rest-request.php' );

// Initialize.
acf_new_instance( 'ACF_Rest_Api' );
