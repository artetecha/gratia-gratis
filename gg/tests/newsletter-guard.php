<?php
/**
 * Standalone behavior tests: php tests/newsletter-guard.php
 * CI sets NEWSLETTER_TEST_DB_HOST/PASSWORD to exercise real MariaDB locks.
 * WordPress HTTP, transient storage and hooks are isolated from live services.
 */
define( 'ABSPATH', __DIR__ );
define( 'DB_NAME', 'newsletter_test' );
define( 'DAY_IN_SECONDS', 86400 );

$hooks = $transients = $writes = array();
$http_calls = 0;
$checks = 0;

function add_filter( $name, $callback, $priority = 10, $accepted = 1 ) {
	$GLOBALS['hooks'][ $name ][ $priority ][] = array( $callback, $accepted );
}
function add_action( ...$args ) { add_filter( ...$args ); }
function apply_filters( $name, $value, ...$args ) {
	$priorities = $GLOBALS['hooks'][ $name ] ?? array();
	ksort( $priorities );
	foreach ( $priorities as $callbacks ) {
		foreach ( $callbacks as list( $callback, $accepted ) ) {
			$value = $callback( ...array_slice( array_merge( array( $value ), $args ), 0, $accepted ) );
		}
	}
	return $value;
}
function do_action( $name, ...$args ) {
	$priorities = $GLOBALS['hooks'][ $name ] ?? array();
	ksort( $priorities );
	foreach ( $priorities as $callbacks ) {
		foreach ( $callbacks as list( $callback, $accepted ) ) {
			$callback( ...array_slice( $args, 0, $accepted ) );
		}
	}
}
function get_transient( $key ) { return $GLOBALS['transients'][ $key ] ?? false; }
function set_transient( $key, $value, $ttl ) {
	$GLOBALS['transients'][ $key ] = $value;
	$GLOBALS['writes'][] = array( $key, $value, $ttl );
	return true;
}
function wp_remote_post( $url, $args ) {
	++$GLOBALS['http_calls'];
	return $GLOBALS['response'];
}
function wp_remote_retrieve_response_code( $response ) { return $response['status']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function is_wp_error( $response ) { return $response instanceof WP_Error; }
function sanitize_text_field( $value ) { return is_scalar( $value ) ? trim( $value ) : ''; }
function wp_unslash( $value ) { return $value; }
function wp_enqueue_script( ...$args ) {}
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function __return_false() { return false; }
function __return_empty_array() { return array(); }
function status_header( $code ) { echo "status:$code\n"; }
class WP_Error {}
class TestForm {
	public function __construct( private string $action = 'subscribe' ) {}
	public function get_action() { return $this->action; }
}

/** The same adapter uses real, independent MariaDB sessions in CI. */
class TestDatabase {
	public $prefix = 'wp_';
	public $last_lock;
	public $unavailable = false;
	private $connection;
	private static $locks = array();
	public function __construct() {
		if ( getenv( 'NEWSLETTER_TEST_DB_HOST' ) ) {
			$this->connection = new mysqli( getenv( 'NEWSLETTER_TEST_DB_HOST' ), 'root', getenv( 'NEWSLETTER_TEST_DB_PASSWORD' ), DB_NAME );
		}
	}
	public function prepare( $query, $key ) { return str_replace( '%s', "'" . addslashes( $key ) . "'", $query ); }
	public function get_var( $query ) {
		if ( $this->unavailable ) {
			return null;
		}
		preg_match( "/(GET_LOCK|RELEASE_LOCK)\('([^']+)'/", $query, $match );
		$this->last_lock = $match[2];
		if ( $this->connection ) {
			return $this->connection->query( $query )->fetch_row()[0];
		}
		$owner = self::$locks[ $match[2] ] ?? null;
		if ( 'GET_LOCK' === $match[1] ) {
			if ( null !== $owner && $owner !== $this ) {
				return '0';
			}
			self::$locks[ $match[2] ] = $this;
			return '1';
		}
		if ( $owner === $this ) {
			unset( self::$locks[ $match[2] ] );
			return '1';
		}
		return '0';
	}
}

if ( in_array( '--xmlrpc', $argv, true ) ) {
	define( 'XMLRPC_REQUEST', true );
	require __DIR__ . '/../mu-plugins/newsletter-guard.php';
	echo 'ERROR: XML-RPC execution continued';
	exit( 1 );
}

$wpdb = new TestDatabase();
$competitor = new TestDatabase();
require __DIR__ . '/../mu-plugins/newsletter-guard.php';

function check( $expected, $actual, $label ) {
	++$GLOBALS['checks'];
	if ( $expected !== $actual ) {
		throw new RuntimeException( $label . ': expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
	}
}
function fresh_request() {
	Gratia_Signup_Limit::release();
	$_SERVER['REMOTE_ADDR'] = '192.0.2.10';
	$_POST = array();
	putenv( 'TURNSTILE_SITE_KEY' );
	putenv( 'TURNSTILE_SECRET_KEY' );
	return new TestForm();
}
function validate( $form, $errors = array() ) { return apply_filters( 'mc4wp_form_errors', $errors, $form ); }

$form = fresh_request();
check( array( 'spam.honeypot' ), validate( $form, array( 'spam.honeypot' ) ), 'Preserve MC4WP errors' );
check( array(), $transients, 'Existing errors consume no quota' );
check( 0, $http_calls, 'Existing errors make no HTTP call' );
check( '<input>', apply_filters( 'mc4wp_form_after_fields', '<input>', $form ), 'No keys means no widget' );

// Failed API calls and subsequent validation errors must not consume quota.
for ( $i = 0; $i < 5; ++$i ) {
	$form = fresh_request();
	check( array(), validate( $form ), 'A retry after Mailchimp failure is allowed' );
	do_action( 'mc4wp_form_api_error', $form, 'mock API failure' );
}
check( array(), $transients, 'API failures consume no quota' );
add_filter( 'mc4wp_form_errors', fn( $errors ) => array_merge( $errors, array( 'late_error' ) ), 101 );
check( array( 'late_error' ), validate( fresh_request() ), 'Later validation error is preserved' );
Gratia_Signup_Limit::release();
unset( $hooks['mc4wp_form_errors'][101] );
check( array(), $transients, 'Later errors consume no quota' );
$form = fresh_request();
validate( $form );
do_action( 'mc4wp_form_subscribed', $form );
do_action( 'mc4wp_form_updated_subscriber', $form );
check( array(), $transients, 'Updating an existing subscriber consumes no quota' );

// Count successful new subscribers, keep the first expiry, and reject the fourth.
for ( $i = 1; $i <= 3; ++$i ) {
	$form = fresh_request();
	check( array(), validate( $form ), 'Signup below quota is allowed' );
	do_action( 'mc4wp_form_added_subscriber', $form );
	do_action( 'mc4wp_form_added_subscriber', $form );
	$key = array_key_first( $transients );
	check( $i, $transients[ $key ]['count'], 'Success counted exactly once' );
	if ( 1 === $i ) {
		check( true, abs( $transients[ $key ]['expires'] - time() - DAY_IN_SECONDS ) <= 1, 'Window starts at first success' );
		$expiry = time() + 1000;
		$transients[ $key ]['expires'] = $expiry;
	} else {
		check( $expiry, $transients[ $key ]['expires'], 'Expiry is not extended' );
		check( true, end( $writes )[2] <= 1000, 'Transient TTL keeps remaining window' );
	}
}
check( array( 'spam.rate_limit' ), validate( fresh_request() ), 'Fourth success is blocked before Mailchimp' );
check( $expiry, $transients[ $key ]['expires'], 'Rejected attempt does not extend expiry' );
$transients[ $key ]['expires'] = time() - 1;
$form = fresh_request();
check( array(), validate( $form ), 'Expired quota permits a signup' );
do_action( 'mc4wp_form_added_subscriber', $form );
check( 1, $transients[ $key ]['count'], 'Expired window restarts at one' );

// A second DB connection cannot enter the bucket before the first request ends.
$form = fresh_request();
validate( $form );
$lock = $wpdb->last_lock;
check( '0', (string) $competitor->get_var( $competitor->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ), 'Concurrent worker cannot bypass validation lock' );
Gratia_Signup_Limit::release();
check( '1', (string) $competitor->get_var( $competitor->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ), 'Request completion frees the lock' );
check( array( 'spam.rate_limit' ), validate( $form ), 'Busy bucket rejects concurrent submission' );
$competitor->get_var( $competitor->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
check( array(), validate( $form ), 'Submission can retry after competing request finishes' );
Gratia_Signup_Limit::release();
$wpdb->unavailable = true;
check( array( 'spam.rate_limit' ), validate( $form ), 'Database lock failure cannot bypass the limit' );
$wpdb->unavailable = false;

// IPv6 rotation within a /64 shares quota; another /64 remains independent.
$transients = array();
for ( $i = 1; $i <= 3; ++$i ) {
	$form = fresh_request();
	$_SERVER['REMOTE_ADDR'] = '2001:db8:1:2::' . $i;
	check( array(), validate( $form ), 'IPv6 signup below shared quota passes' );
	do_action( 'mc4wp_form_added_subscriber', $form );
}
$form = fresh_request();
$_SERVER['REMOTE_ADDR'] = '2001:0db8:0001:0002:ffff:ffff:ffff:ffff';
check( array( 'spam.rate_limit' ), validate( $form ), 'Rotating IPv6 address cannot reset quota' );
$_SERVER['REMOTE_ADDR'] = '2001:db8:1:3::1';
check( array(), validate( $form ), 'Different IPv6 /64 has its own quota' );
check( gratia_signup_bucket( '192.0.2.10' ), gratia_signup_bucket( '::ffff:192.0.2.10' ), 'IPv4-mapped address shares IPv4 quota' );
$form = fresh_request();
$_SERVER['REMOTE_ADDR'] = '';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.2.10';
check( array( 'spam.rate_limit' ), validate( $form ), 'Missing trusted IP cannot bypass the limit' );

$transients = array();
foreach ( array(
	'network error' => new WP_Error(),
	'HTTP 503' => array( 'status' => 503, 'body' => '<html>unavailable</html>' ),
	'HTTP 429' => array( 'status' => 429, 'body' => '{"success":false}' ),
	'invalid JSON' => array( 'status' => 200, 'body' => 'invalid' ),
	'missing success' => array( 'status' => 200, 'body' => '{}' ),
	'invalid success type' => array( 'status' => 200, 'body' => '{"success":"false"}' ),
	'internal error' => array( 'status' => 200, 'body' => '{"success":false,"error-codes":["internal-error"]}' ),
	'valid token' => array( 'status' => 200, 'body' => '{"success":true}' ),
) as $label => $response ) {
	$form = fresh_request();
	putenv( 'TURNSTILE_SITE_KEY=test-site' );
	putenv( 'TURNSTILE_SECRET_KEY=test-secret' );
	$_POST['cf-turnstile-response'] = 'test-token';
	check( array(), validate( $form ), $label . ' still permits a signup under quota' );
	Gratia_Signup_Limit::release();
	$key = 'gratia_signups_v2_' . md5( '192.0.2.10' );
	$transients[ $key ] = array( 'count' => 3, 'expires' => time() + 1000 );
	check( array( 'spam.rate_limit' ), validate( $form ), $label . ' cannot bypass IP quota' );
	$transients = array();
}
$response = array( 'status' => 200, 'body' => '{"success":false,"error-codes":["invalid-input-response"]}' );
check( array( 'spam.turnstile' ), validate( $form ), 'Rejected token fails closed' );
$_POST = array();
check( array( 'spam.turnstile' ), validate( $form ), 'Missing token fails closed' );
$unsubscribe = new TestForm( 'unsubscribe' );
$calls_before = $http_calls;
check( array(), validate( $unsubscribe ), 'Unsubscribe is exempt even without a token' );
check( $calls_before, $http_calls, 'Unsubscribe does not call Turnstile' );
check( '<input>', apply_filters( 'mc4wp_form_after_fields', '<input>', $unsubscribe ), 'Unsubscribe has no widget' );
check( true, str_contains( apply_filters( 'mc4wp_form_after_fields', '', $form ), 'cf-turnstile' ), 'Configured subscribe form has a widget' );

check( false, apply_filters( 'xmlrpc_enabled', true ), 'Authenticated XML-RPC methods disabled' );
check( array(), apply_filters( 'xmlrpc_methods', array( 'pingback.ping' => 'callback' ) ), 'WordPress XML-RPC methods removed' );
$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' --xmlrpc';
exec( $command, $output, $exit_code );
check( 0, $exit_code, 'XML-RPC exits cleanly' );
check( "status:403\nXML-RPC is disabled.", implode( "\n", $output ), 'XML-RPC is refused before reaching any method' );

echo "Passed $checks newsletter guard checks (" . ( getenv( 'NEWSLETTER_TEST_DB_HOST' ) ? 'real MariaDB locks' : 'simulated database; CI verifies MariaDB' ) . ").\n";
