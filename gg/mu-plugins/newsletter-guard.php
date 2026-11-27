<?php
/**
 * Plugin Name: Gratia Gratis newsletter signup guard
 * Description: Stops bots subscribing third-party addresses through the Mailchimp for WordPress form, and turns off XML-RPC.
 */

defined( 'ABSPATH' ) || exit;

/**
 * XML-RPC is blocked at the web server too (.upsun/config.yaml); this keeps it
 * closed if that location is ever loosened.
 */
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	status_header( 403 );
	exit( 'XML-RPC is disabled.' );
}
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );

/**
 * New subscriptions per IPv4 address or IPv6 /64 in the 24 hours starting
 * with the first success. Failed attempts and subscriber updates do not count.
 */
const GRATIA_SIGNUPS_PER_IP_PER_DAY = 3;

/**
 * Cloudflare Turnstile keys, set as Upsun variables `env:TURNSTILE_SITE_KEY`
 * and `env:TURNSTILE_SECRET_KEY` (sensitive). Without both, the challenge is
 * skipped and only the per-IP limit applies.
 *
 * @return array{site: string, secret: string}|null
 */
function gratia_turnstile_keys() {
	$site   = (string) getenv( 'TURNSTILE_SITE_KEY' );
	$secret = (string) getenv( 'TURNSTILE_SECRET_KEY' );

	return ( '' !== $site && '' !== $secret ) ? array( 'site' => $site, 'secret' => $secret ) : null;
}

function gratia_signup_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

/** Canonical IPv4 address or IPv6 /64; never trust forwarded request headers. */
function gratia_signup_bucket( $ip ) {
	$packed = @inet_pton( $ip );
	if ( false === $packed ) {
		return '';
	}
	if ( 16 === strlen( $packed ) ) {
		// Treat IPv4-mapped IPv6 as the corresponding IPv4 address.
		if ( substr( $packed, 0, 12 ) === str_repeat( "\0", 10 ) . "\xff\xff" ) {
			$packed = substr( $packed, 12 );
		} else {
			$packed = substr( $packed, 0, 8 ) . str_repeat( "\0", 8 );
		}
	}
	return inet_ntop( $packed );
}

/**
 * Serialize a bucket from validation through the Mailchimp result. A database
 * advisory lock works across PHP workers, including with Redis transients.
 * Failed requests consume no quota. Release before page rendering; shutdown
 * (or a closed DB connection) is a fallback for interrupted requests.
 */
class Gratia_Signup_Limit {
	private static $pending = null;

	public static function reserve( $ip, $form ) {
		global $wpdb;
		$bucket = gratia_signup_bucket( $ip );
		if ( '' === $bucket ) {
			return false;
		}
		if ( null !== self::$pending ) {
			return self::$pending['form'] === $form;
		}
		$key  = 'gratia_signups_v2_' . md5( $bucket );
		$lock = 'gratia_signup_' . md5( DB_NAME . ':' . $wpdb->prefix . ':' . $bucket );
		// Briefly queue overlapping readers; prolonged contention still rejects.
		$held = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 1)', $lock ) );
		if ( '1' !== (string) $held ) {
			if ( null === $held ) {
				error_log( 'newsletter-guard: Could not acquire the signup database lock.' );
			}
			return false;
		}
		$window = get_transient( $key );
		self::$pending = compact( 'key', 'lock', 'form', 'window' );
		if ( is_array( $window ) && $window['expires'] > time() && $window['count'] >= GRATIA_SIGNUPS_PER_IP_PER_DAY ) {
			self::release();
			return false;
		}
		return true;
	}

	public static function record( $form ) {
		if ( null === self::$pending || self::$pending['form'] !== $form ) {
			return;
		}
		$now    = time();
		$window = self::$pending['window'];
		if ( ! is_array( $window ) || $window['expires'] <= $now ) {
			$window = array( 'count' => 0, 'expires' => $now + DAY_IN_SECONDS );
		}
		++$window['count'];
		set_transient( self::$pending['key'], $window, $window['expires'] - $now );
		self::release();
	}

	public static function release() {
		global $wpdb;
		if ( null !== self::$pending ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', self::$pending['lock'] ) );
			self::$pending = null;
		}
	}
}
register_shutdown_function( array( 'Gratia_Signup_Limit', 'release' ) );
add_action( 'mc4wp_form_added_subscriber', array( 'Gratia_Signup_Limit', 'record' ) );
add_action( 'mc4wp_form_subscribed', array( 'Gratia_Signup_Limit', 'release' ) );
add_action( 'mc4wp_form_api_error', array( 'Gratia_Signup_Limit', 'release' ) );
// Also covers already-subscribed errors and errors from later validators.
add_action( 'mc4wp_form_respond', array( 'Gratia_Signup_Limit', 'release' ), 0 );

/**
 * Render the Turnstile widget after the form fields. "interaction-only" keeps
 * it invisible unless Cloudflare wants a human to click, so the layout holds.
 */
add_filter( 'mc4wp_form_after_fields', function ( $html, $form ) {
	$keys = gratia_turnstile_keys();
	if ( null === $keys || 'subscribe' !== $form->get_action() ) {
		return $html;
	}

	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js',
		array(),
		null,
		array(
			'in_footer' => true,
			'strategy'  => 'async',
		)
	);

	return $html . sprintf(
		'<div class="cf-turnstile" data-sitekey="%s" data-appearance="interaction-only" style="margin-top:1rem"></div>',
		esc_attr( $keys['site'] )
	);
}, 10, 2 );

/**
 * Validate after MC4WP's own honeypot/timestamp checks. Error keys are
 * namespaced under `spam.` so the form shows its generic error message.
 */
add_filter( 'mc4wp_form_errors', function ( array $errors, $form ) {
	if ( ! empty( $errors ) || 'subscribe' !== $form->get_action() ) {
		return $errors;
	}

	$ip = gratia_signup_ip();

	$keys = gratia_turnstile_keys();
	if ( null !== $keys ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- MC4WP forms carry no nonce; Turnstile is the check.
		$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		if ( '' === $token ) {
			return array( 'spam.turnstile' );
		}

		$response = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 5,
				'body'    => array(
					'secret'   => $keys['secret'],
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);

		// Service failures fall back to the IP limit; invalid tokens still fail.
		if ( is_wp_error( $response ) ) {
			error_log( 'newsletter-guard: Turnstile siteverify transport failure.' );
		} else {
			$status = wp_remote_retrieve_response_code( $response );
			$result = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( $status < 200 || $status >= 300 || ! is_array( $result ) || ! isset( $result['success'] ) || ! is_bool( $result['success'] ) ) {
				error_log( 'newsletter-guard: Turnstile siteverify unavailable or malformed response.' );
			} elseif ( false === $result['success'] && array( 'internal-error' ) === ( $result['error-codes'] ?? array() ) ) {
				error_log( 'newsletter-guard: Turnstile siteverify internal error.' );
			} elseif ( true !== $result['success'] ) {
				return array( 'spam.turnstile' );
			}
		}
	}

	if ( ! Gratia_Signup_Limit::reserve( $ip, $form ) ) {
		return array( 'spam.rate_limit' );
	}

	return $errors;
}, 100, 2 );
