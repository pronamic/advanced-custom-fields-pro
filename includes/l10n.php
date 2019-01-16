<?php 

/**
 * Determine the current locale desired for the request.
 *
 * @since 5.0.0
 *
 * @global string $pagenow
 *
 * @return string The determined locale.
 */
if( !function_exists('determine_locale') ):
function determine_locale() {
	/**
	 * Filters the locale for the current request prior to the default determination process.
	 *
	 * Using this filter allows to override the default logic, effectively short-circuiting the function.
	 *
	 * @since 5.0.0
	 *
	 * @param string|null The locale to return and short-circuit, or null as default.
	 */
	$determined_locale = apply_filters( 'pre_determine_locale', null );
	if ( ! empty( $determined_locale ) && is_string( $determined_locale ) ) {
		return $determined_locale;
	}

	$determined_locale = get_locale();

	if ( is_admin() ) {
		$determined_locale = get_user_locale();
	}

	if ( isset( $_GET['_locale'] ) && 'user' === $_GET['_locale'] && wp_is_json_request() ) {
		$determined_locale = get_user_locale();
	}

	if ( ! empty( $_GET['wp_lang'] ) && ! empty( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		$determined_locale = sanitize_text_field( $_GET['wp_lang'] );
	}

	/**
	 * Filters the locale for the current request.
	 *
	 * @since 5.0.0
	 *
	 * @param string $locale The locale.
	 */
	return apply_filters( 'determine_locale', $determined_locale );
}
endif;

/*
 * acf_get_locale
 *
 * Returns the current locale.
 *
 * @date	16/12/16
 * @since	5.5.0
 *
 * @param	void
 * @return	string
 */
function acf_get_locale() {
	
	// Determine local.
	$locale = determine_locale();
	
	// Fallback to parent language for regions without translation.
	// https://wpastra.com/docs/complete-list-wordpress-locale-codes/
	$langs = array(
		'az_TR'	=> 'az',		// Azerbaijani (Turkey)
		'zh_HK'	=> 'zh_CN',		// Chinese (Hong Kong)
		'zh_TW'	=> 'zh_CN',		// Chinese (Taiwan)
		'nl_BE'	=> 'nl_NL',		// Dutch (Belgium)
		'fr_BE'	=> 'fr_FR',		// French (Belgium)
		'nn_NO'	=> 'nb_NO',		// Norwegian (Nynorsk)
		'fa_AF'	=> 'fa_IR',		// Persian (Afghanistan)
		'ru_UA'	=> 'ru_RU',		// Russian (Ukraine)
	);
	if( isset($langs[ $locale ]) ) {
		$locale = $langs[ $locale ];
	}
	
	/**
	 * Filters the determined local.
	 *
	 * @date	8/1/19
	 * @since	5.7.10
	 *
	 * @param	string $locale The local.
	 */
	return apply_filters( 'acf/get_locale', $locale );
}

/**
 * acf_load_textdomain
 *
 * Loads the plugin's translated strings similar to load_plugin_textdomain().
 *
 * @date	8/1/19
 * @since	5.7.10
 *
 * @param	string $locale The plugin's current locale.
 * @return	void
 */
function acf_load_textdomain( $domain = 'acf' ) {
	
	/**
	 * Filters a plugin's locale.
	 *
	 * @date	8/1/19
	 * @since	5.7.10
	 *
	 * @param 	string $locale The plugin's current locale.
	 * @param 	string $domain Text domain. Unique identifier for retrieving translated strings.
	 */
	$locale = apply_filters( 'plugin_locale', acf_get_locale(), $domain );
	$mofile = $domain . '-' . $locale . '.mo';
	
	// Try to load from the languages directory first.
	if( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
		return true;
	}
	
	// Load from plugin lang folder.
	return load_textdomain( $domain, acf_get_path( 'lang/' . $mofile ) );
}
