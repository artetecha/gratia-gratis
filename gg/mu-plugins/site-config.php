<?php
/**
 * Plugin Name: Gratia Gratis Upsun site configuration
 * Description: Project-owned tuning for the upsun-wp MU plugin.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mirror the route cache configuration from .upsun/config.yaml so
 * `wp upsun cache-check` reports the effective cookie allowlist.
 */
add_filter( 'upsun_cache_check_route_cache', function ( array $config ) {
	return array(
		'enabled'     => true,
		'default_ttl' => 0,
		'cookies'     => array(
			'/^wordpress_logged_in_/',
			'/^wordpress_sec_/',
			'wordpress_test_cookie',
			'/^wp-settings-/',
			'/^wp-postpass-/',
			'PHPSESSID',
		),
		'known'       => true,
	);
} );

/**
 * Polylang is configured to determine language from the URL, with browser
 * detection and language redirects disabled. Its pll_language cookie would
 * otherwise add Set-Cookie to every anonymous response and prevent the Upsun
 * router from caching HTML.
 */
add_filter( 'upsun_page_cache_strip_cookies', function ( array $prefixes ) {
	$prefixes[] = 'pll_language';

	return array_values( array_unique( $prefixes ) );
} );

/**
 * Remove Polylang's cookie before upsun-wp makes its cacheability decision at
 * template_redirect priority 99. The module's header callback remains the
 * fallback for cookies added later in the response lifecycle.
 */
add_action( 'template_redirect', function (): void {
	if (
		'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? '' )
		|| is_admin()
		|| is_user_logged_in()
	) {
		return;
	}

	$retained_cookies = array();
	$removed          = false;

	foreach ( headers_list() as $header ) {
		if ( 0 !== stripos( $header, 'Set-Cookie:' ) ) {
			continue;
		}

		$cookie_name = trim( strtok( substr( $header, strlen( 'Set-Cookie:' ) ), '=' ) );
		if ( 'pll_language' === $cookie_name ) {
			$removed = true;
			continue;
		}

		$retained_cookies[] = $header;
	}

	if ( ! $removed ) {
		return;
	}

	header_remove( 'Set-Cookie' );
	foreach ( $retained_cookies as $header ) {
		header( $header, false );
	}
}, 98 );
