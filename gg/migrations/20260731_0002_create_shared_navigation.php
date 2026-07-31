<?php
/**
 * Create the shared Navigation entities used by the block theme.
 *
 * Existing entities with the stable slugs are preserved so rerunning this
 * callable never overwrites menu edits made in the Site Editor.
 */

return static function (): void {
	if ( ! post_type_exists( 'wp_navigation' ) ) {
		throw new RuntimeException( 'The WordPress Navigation post type is unavailable.' );
	}

	$menus = array(
		'primary-navigation'       => array(
			'title' => 'Primary navigation',
			'links' => array(
				array( 'Content', '/blog/' ),
				array( 'Books', '/books/' ),
				array( 'About us', '/about-us/' ),
				array( 'Contact', '/contact-us/' ),
			),
		),
		'footer-about-navigation'  => array(
			'title' => 'Footer: About',
			'links' => array(
				array( 'Our team', '/about-us/' ),
				array( 'Contact us', '/contact-us/' ),
				array( 'Join us', '/contact-us/' ),
			),
		),
		'footer-explore-navigation' => array(
			'title' => 'Footer: Explore',
			'links' => array(
				array( 'Journal', '/blog/' ),
				array( 'Books', '/books/' ),
				array( 'Give', '/#donate' ),
			),
		),
	);

	foreach ( $menus as $slug => $menu ) {
		$existing = get_page_by_path( $slug, OBJECT, 'wp_navigation' );
		if ( $existing instanceof WP_Post ) {
			continue;
		}

		$blocks = array();
		foreach ( $menu['links'] as $link ) {
			$blocks[] = sprintf(
				'<!-- wp:navigation-link %s /-->',
				wp_json_encode(
					array(
						'label'          => $link[0],
						'url'            => $link[1],
						'kind'           => 'custom',
						'isTopLevelLink' => true,
					),
					JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
				)
			);
		}

		$result = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => $menu['title'],
				'post_name'    => $slug,
				'post_content' => implode( "\n", $blocks ),
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			throw new RuntimeException(
				sprintf( 'Could not create “%s”: %s', $menu['title'], $result->get_error_message() )
			);
		}
	}
};
