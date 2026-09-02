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
 * Pages are editorial documents and never expose discussion controls.
 */
function gratia_gratis_disable_page_discussion_support() {
	remove_post_type_support( 'page', 'comments' );
	remove_post_type_support( 'page', 'trackbacks' );
}
add_action( 'init', 'gratia_gratis_disable_page_discussion_support', 20 );

/**
 * Keep comments and pingbacks closed for pages, including existing content.
 *
 * @param bool $open    Whether the discussion type is open.
 * @param int  $post_id Post ID.
 * @return bool
 */
function gratia_gratis_close_page_discussion( $open, $post_id ) {
	return 'page' === get_post_type( $post_id ) ? false : $open;
}
add_filter( 'comments_open', 'gratia_gratis_close_page_discussion', 10, 2 );
add_filter( 'pings_open', 'gratia_gratis_close_page_discussion', 10, 2 );

/**
 * Load the small CSS layer that complements theme.json.
 */
function gratia_gratis_enqueue_styles() {
	$stylesheet_path = get_theme_file_path( 'assets/css/theme.css' );
	$stylesheet_hash = file_exists( $stylesheet_path ) ? md5_file( $stylesheet_path ) : false;
	$version         = $stylesheet_hash ? substr( $stylesheet_hash, 0, 12 ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'gratia-gratis',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array(),
		$version
	);

	if ( is_singular( array( 'post', 'page' ) ) ) {
		$print_stylesheet_path = get_theme_file_path( 'assets/css/print.css' );
		$print_stylesheet_hash = file_exists( $print_stylesheet_path ) ? md5_file( $print_stylesheet_path ) : false;
		$print_version         = $print_stylesheet_hash ? substr( $print_stylesheet_hash, 0, 12 ) : wp_get_theme()->get( 'Version' );

		wp_enqueue_style(
			'gratia-gratis-print',
			get_theme_file_uri( 'assets/css/print.css' ),
			array( 'gratia-gratis' ),
			$print_version,
			'print'
		);
	}
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
 * Resolve a shared Navigation entity by its stable slug.
 *
 * Pattern files use this helper instead of hardcoding database-specific post
 * IDs. The migration creates the entities, while the patterns retain embedded
 * fallback links for fresh databases where migrations have not run yet.
 *
 * @param string $slug Navigation post slug.
 * @return int Published Navigation post ID, or zero when it is unavailable.
 */
function gratia_gratis_get_navigation_id( $slug ) {
	$navigation = get_page_by_path( sanitize_title( $slug ), OBJECT, 'wp_navigation' );

	if ( ! $navigation instanceof WP_Post || 'publish' !== $navigation->post_status ) {
		return 0;
	}

	return (int) $navigation->ID;
}

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
 * Replace an expanded legacy Books preview saved in a Site Editor template
 * with the current dynamic pattern. This leaves every other customized block
 * on the front page untouched.
 *
 * @param string $block_content Rendered Group block markup.
 * @param array  $block         Parsed block data.
 * @return string
 */
function gratia_gratis_render_dynamic_books_preview( $block_content, $block ) {
	if ( str_contains( $block_content, 'gg-books-preview-query' ) ) {
		return $block_content;
	}

	$metadata_name = $block['attrs']['metadata']['name'] ?? '';
	$is_named_copy = 'Books preview' === $metadata_name;
	$is_legacy_copy =
		'full' === ( $block['attrs']['align'] ?? '' )
		&& 'parchment' === ( $block['attrs']['backgroundColor'] ?? '' )
		&& str_contains( $block_content, 'gg-book-preview-grid' )
		&& str_contains( $block_content, 'View the full book list' );

	if ( ! $is_named_copy && ! $is_legacy_copy ) {
		return $block_content;
	}

	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'gratia-gratis/books-preview' );
	if ( empty( $pattern['content'] ) ) {
		return $block_content;
	}

	return do_blocks( $pattern['content'] );
}
add_filter( 'render_block_core/group', 'gratia_gratis_render_dynamic_books_preview', 10, 2 );

/**
 * Render the search heading used by the Search template.
 *
 * @return string
 */
function gratia_gratis_search_heading() {
	$search_query = trim( get_search_query() );

	if ( '' === $search_query ) {
		return '<h1 class="wp-block-heading has-display-font-size">' . esc_html__( 'Search', 'gratia-gratis' ) . '</h1>';
	}

	return '<h1 class="wp-block-heading has-display-font-size">' . esc_html__( 'Search results for:', 'gratia-gratis' ) . '</h1>';
}
add_shortcode( 'gratia_search_heading', 'gratia_gratis_search_heading' );

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
