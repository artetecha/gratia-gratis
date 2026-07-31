<?php
/**
 * Move the legacy Books-page catalog into the Book content type.
 *
 * Existing Book records are updated in place so this migration safely follows
 * the earlier runtime bootstrap used on preview environments.
 */

return static function (): void {
	global $wpdb;

	if ( ! function_exists( 'gratia_books_register_content_type' ) ) {
		throw new RuntimeException( 'The Gratia Gratis Books MU plugin is unavailable.' );
	}

	if ( ! post_type_exists( 'book' ) || ! taxonomy_exists( 'book_status' ) ) {
		gratia_books_register_content_type();
	}

	if ( function_exists( 'gratia_books_register_default_statuses' ) ) {
		gratia_books_register_default_statuses();
	}

	$books = array(
		array(
			'title'      => 'Un Solenne Avvertimento',
			'slug'       => 'un-solenne-avvertimento',
			'author'     => 'Thomas Taylor',
			'status'     => 'published',
			'url'        => 'https://www.amazon.it/dp/B0BLT3JZCD',
			'link_label' => 'Amazon ↗',
			'cover'      => '71sWk7J32pL',
		),
		array(
			'title'      => 'Dio è Eterno',
			'slug'       => 'dio-e-eterno',
			'author'     => 'William Lane Craig',
			'status'     => 'published',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-eterno-dr-william-lane-craig/',
			'link_label' => 'Publisher ↗',
			'cover'      => 'cop-Dio-e-eterno',
		),
		array(
			'title'      => 'L’Eredità degli Affamati',
			'slug'       => 'leredita-degli-affamati',
			'author'     => 'Zane C. Hodges',
			'status'     => 'published',
			'url'        => 'https://www.amazon.it/dp/B0CX97WCC8',
			'link_label' => 'Amazon ↗',
			'cover'      => '61G5eqbv14L',
		),
		array(
			'title'      => 'Dio è Spirito',
			'slug'       => 'dio-e-spirito',
			'author'     => 'William Lane Craig',
			'status'     => 'coming-soon',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-spirito-dr-william-craig/',
			'link_label' => 'Preview ↗',
			'cover'      => 'cop-Dio-e-Spirito',
		),
		array(
			'title'      => 'Dio è Ovunque',
			'slug'       => 'dio-e-ovunque',
			'author'     => 'William Lane Craig',
			'status'     => 'coming-soon',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-ovunque-dr-william-lane-craig/',
			'link_label' => 'Preview ↗',
			'cover'      => 'Libro-Dio-e-Ovunque',
		),
	);

	$find_post_id = static function ( string $slug, string $title ) use ( $wpdb ): int {
		$post_id = absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'book' AND post_name = %s ORDER BY ID ASC LIMIT 1",
					$slug
				)
			)
		);

		if ( $post_id ) {
			return $post_id;
		}

		return absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'book' AND post_title = %s ORDER BY ID ASC LIMIT 1",
					$title
				)
			)
		);
	};

	$find_cover_id = static function ( string $filename_stem ) use ( $wpdb ): int {
		return absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT posts.ID FROM {$wpdb->posts} posts INNER JOIN {$wpdb->postmeta} metadata ON metadata.post_id = posts.ID WHERE posts.post_type = 'attachment' AND metadata.meta_key = '_wp_attached_file' AND metadata.meta_value LIKE %s ORDER BY posts.ID DESC LIMIT 1",
					'%' . $wpdb->esc_like( $filename_stem ) . '%'
				)
			)
		);
	};

	foreach ( $books as $index => $book ) {
		$post_id  = $find_post_id( $book['slug'], $book['title'] );
		$post_data = array(
			'post_type'   => 'book',
			'post_status' => 'publish',
			'post_title'  => $book['title'],
			'post_name'   => $book['slug'],
			'menu_order'  => ( $index + 1 ) * 10,
		);

		if ( $post_id ) {
			$post_data['ID'] = $post_id;
			$result          = wp_update_post( $post_data, true );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $result ) ) {
			throw new RuntimeException( sprintf( 'Could not migrate “%s”: %s', $book['title'], $result->get_error_message() ) );
		}

		$post_id = absint( $result );
		update_post_meta( $post_id, 'book_author', $book['author'] );
		update_post_meta( $post_id, 'book_url', $book['url'] );
		update_post_meta( $post_id, 'book_link_label', $book['link_label'] );

		$status_result = wp_set_object_terms( $post_id, $book['status'], 'book_status' );
		if ( is_wp_error( $status_result ) ) {
			throw new RuntimeException( sprintf( 'Could not assign the status for “%s”: %s', $book['title'], $status_result->get_error_message() ) );
		}

		$cover_id = $find_cover_id( $book['cover'] );
		if ( ! $cover_id ) {
			throw new RuntimeException( sprintf( 'Could not locate the cover attachment for “%s”.', $book['title'] ) );
		}

		set_post_thumbnail( $post_id, $cover_id );
	}

	delete_option( 'gratia_books_migration_version' );
	flush_rewrite_rules( false );
};
