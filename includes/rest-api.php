<?php

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
