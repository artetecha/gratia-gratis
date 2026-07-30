<?php
/**
 * Theme setup and presentation helpers.
 *
 * @package GratiaGratis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure editor and front-end theme support.
 */
function gratia_gratis_setup() {
	load_theme_textdomain( 'gratia-gratis', get_template_directory() . '/languages' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/theme.css' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'gratia_gratis_setup' );

/**
 * Load the small CSS layer that complements theme.json.
 */
function gratia_gratis_enqueue_styles() {
	$stylesheet_path = get_theme_file_path( 'assets/css/theme.css' );
	$version         = file_exists( $stylesheet_path ) ? (string) filemtime( $stylesheet_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'gratia-gratis',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'gratia_gratis_enqueue_styles' );

/**
 * Register a dedicated inserter category for the theme patterns.
 */
function gratia_gratis_register_pattern_categories() {
	register_block_pattern_category(
		'gratia-gratis',
		array(
			'label'       => __( 'Gratia Gratis', 'gratia-gratis' ),
			'description' => __( 'Editorial sections and ministry layouts for Gratia Gratis.', 'gratia-gratis' ),
		)
	);
}
add_action( 'init', 'gratia_gratis_register_pattern_categories' );

/**
 * Expose a few reusable block treatments in the editor.
 */
function gratia_gratis_register_block_styles() {
	register_block_style(
		'core/list',
		array(
			'name'         => 'plain',
			'label'        => __( 'Plain', 'gratia-gratis' ),
			'inline_style' => '.wp-block-list.is-style-plain{list-style:none;padding-left:0;}',
		)
	);

	register_block_style(
		'core/image',
		array(
			'name'         => 'rounded',
			'label'        => __( 'Rounded', 'gratia-gratis' ),
			'inline_style' => '.wp-block-image.is-style-rounded img{border-radius:24px;}',
		)
	);
}
add_action( 'init', 'gratia_gratis_register_block_styles' );

/**
 * Render the configured Mailchimp form, with an honest visual fallback when
 * the plugin is installed but inactive (as can happen on a fresh preview).
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function gratia_gratis_newsletter_form( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'variant' => 'compact',
		),
		$attributes,
		'gratia_newsletter_form'
	);

	if ( shortcode_exists( 'mc4wp_form' ) ) {
		return do_shortcode( '[mc4wp_form id="1158"]' );
	}

	$is_full = 'full' === sanitize_key( $attributes['variant'] );

	ob_start();
	?>
	<form class="mc4wp-form gg-newsletter-fallback" aria-label="<?php esc_attr_e( 'Newsletter signup', 'gratia-gratis' ); ?>">
		<div class="mc4wp-form-fields">
			<?php if ( $is_full ) : ?>
				<p><label><?php esc_html_e( 'First name', 'gratia-gratis' ); ?><input type="text" autocomplete="given-name" disabled></label></p>
				<p><label><?php esc_html_e( 'Last name', 'gratia-gratis' ); ?><input type="text" autocomplete="family-name" disabled></label></p>
			<?php endif; ?>
			<p><label><?php esc_html_e( 'Email address', 'gratia-gratis' ); ?><input type="email" autocomplete="email" disabled></label></p>
			<?php if ( $is_full ) : ?>
				<p class="gg-newsletter-options"><label><input type="checkbox" checked disabled> <?php esc_html_e( 'Ministry updates', 'gratia-gratis' ); ?></label><label><input type="checkbox" checked disabled> <?php esc_html_e( 'New writing', 'gratia-gratis' ); ?></label></p>
			<?php endif; ?>
			<p><input type="submit" value="<?php esc_attr_e( 'Subscribe →', 'gratia-gratis' ); ?>" disabled></p>
		</div>
		<p class="gg-newsletter-status"><?php esc_html_e( 'Newsletter signup is temporarily unavailable on this preview.', 'gratia-gratis' ); ?></p>
	</form>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'gratia_newsletter_form', 'gratia_gratis_newsletter_form' );
