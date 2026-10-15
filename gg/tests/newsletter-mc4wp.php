<?php
/**
 * Run after composer install. Exercise the locked MC4WP listener's actual
 * success/failure branches and hooks, with no Mailchimp network requests.
 */
require __DIR__ . '/newsletter-guard.php';

$plugin = __DIR__ . '/../wordpress/wp-content/plugins/mailchimp-for-wp';
require $plugin . '/includes/forms/class-form.php';
require $plugin . '/includes/forms/class-form-message.php';
require $plugin . '/includes/forms/class-form-listener.php';

class NewsletterIntegrationForm extends MC4WP_Form {
	public function __construct() {
		$this->ID = 1158;
		$this->config['email_type'] = 'html';
		$this->config['lists'] = array( 'test-list' );
		$this->settings = array( 'double_optin' => false, 'update_existing' => true, 'replace_interests' => false );
		$this->messages = array_fill_keys( array( 'already_subscribed', 'error', 'updated', 'subscribed' ), 'test message' );
	}
	public function get_data() { return array( 'EMAIL' => 'test@example.invalid' ); }
	public function get_subscriber_tags() { return array(); }
}

// Only the API/data-mapping boundary is mocked: production MC4WP chooses the
// event and fires the hook that must reach Gratia_Signup_Limit::record().
class MC4WP_MailChimp {
	public static $result;
	public static $error_code = 500;
	public function list_subscribe( ...$args ) { return self::$result; }
	public function get_error_code() { return self::$error_code; }
	public function get_error_message() { return 'Mock Mailchimp error'; }
}
class MC4WP_MailChimp_Subscriber {
	public $email_address = 'test@example.invalid';
	public $status;
	public $email_type;
	public $ip_signup;
	public $tags;
	public function to_array() { return get_object_vars( $this ); }
}
class MC4WP_List_Data_Mapper {
	public function __construct( ...$args ) {}
	public function map() { return array( 'test-list' => new MC4WP_MailChimp_Subscriber() ); }
}
class NewsletterTestLog {
	public function info( $message ) {}
	public function warning( $message ) {}
	public function error( $message ) {}
}
function mc4wp_get_service( $name ) { return new NewsletterTestLog(); }
function mc4wp_get_request_ip_address() { return $_SERVER['REMOTE_ADDR']; }

$listener = new MC4WP_Form_Listener();
$start_checks = $checks;
foreach ( array(
	'new subscriber' => array( (object) array( 'id' => 'new', 'status' => 'subscribed', 'was_already_on_list' => false ), 500, false, 1 ),
	'double opt-in subscriber' => array( (object) array( 'id' => 'pending', 'status' => 'pending', 'was_already_on_list' => false ), 500, true, 1 ),
	'existing subscriber update' => array( (object) array( 'id' => 'existing', 'status' => 'subscribed', 'was_already_on_list' => true ), 500, false, 0 ),
	'API failure' => array( false, 500, false, 0 ),
	'already subscribed error' => array( false, 214, false, 0 ),
) as $label => list( $result, $error_code, $double_optin, $expected_count ) ) {
	fresh_request();
	$transients = array();
	$form = new NewsletterIntegrationForm();
	$form->settings['double_optin'] = $double_optin;
	MC4WP_MailChimp::$result = $result;
	MC4WP_MailChimp::$error_code = $error_code;
	check( array(), validate( $form ), $label . ': validation passes' );
	$listener->process_subscribe_form( $form );
	$window = reset( $transients );
	check( $expected_count, $window ? $window['count'] : 0, $label . ': real MC4WP hook produces correct quota' );
	// Exercise MC4WP's real response path too, including already-subscribed
	// failures that do not emit mc4wp_form_api_error. Request JSON to avoid redirects.
	$_SERVER['HTTP_ACCEPT'] = 'application/json';
	$listener->respond( $form );
	$lock = $wpdb->last_lock;
	check( '1', (string) $competitor->get_var( $competitor->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ), $label . ': lock released before page rendering' );
	$competitor->get_var( $competitor->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
	Gratia_Signup_Limit::release();
}
echo 'Passed ' . ( $checks - $start_checks ) . " checks using the installed MC4WP form listener.\n";
