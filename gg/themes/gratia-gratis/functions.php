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
