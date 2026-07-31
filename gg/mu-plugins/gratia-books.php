<?php
/**
 * Plugin Name: Gratia Gratis Books
 * Description: Provides the Book content type, publishing-status taxonomy, and book details.
 * Author: Gratia Gratis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the editorial data model used by the Books page and homepage.
 */
function gratia_books_register_content_type(): void {
	register_post_type(
		'book',
		array(
			'labels'       => array(
				'name'                  => __( 'Books', 'gratia-gratis' ),
				'singular_name'         => __( 'Book', 'gratia-gratis' ),
				'add_new_item'          => __( 'Add New Book', 'gratia-gratis' ),
				'edit_item'             => __( 'Edit Book', 'gratia-gratis' ),
				'new_item'              => __( 'New Book', 'gratia-gratis' ),
				'view_item'             => __( 'View Book', 'gratia-gratis' ),
				'search_items'          => __( 'Search Books', 'gratia-gratis' ),
				'not_found'             => __( 'No books found.', 'gratia-gratis' ),
				'featured_image'        => __( 'Book cover', 'gratia-gratis' ),
				'set_featured_image'    => __( 'Set book cover', 'gratia-gratis' ),
				'remove_featured_image' => __( 'Remove book cover', 'gratia-gratis' ),
				'use_featured_image'    => __( 'Use as book cover', 'gratia-gratis' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-book-alt',
			'rewrite'      => array(
				'slug'       => 'book',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'taxonomies'   => array( 'book_status' ),
		)
	);

	register_taxonomy(
		'book_status',
		array( 'book' ),
		array(
			'labels'            => array(
				'name'          => __( 'Book Statuses', 'gratia-gratis' ),
				'singular_name' => __( 'Book Status', 'gratia-gratis' ),
				'all_items'     => __( 'All Book Statuses', 'gratia-gratis' ),
				'edit_item'     => __( 'Edit Book Status', 'gratia-gratis' ),
				'add_new_item'  => __( 'Add New Book Status', 'gratia-gratis' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'book-status',
				'with_front' => false,
			),
			'default_term'      => array(
				'name' => __( 'Published', 'gratia-gratis' ),
				'slug' => 'published',
			),
		)
	);

	$meta_fields = array(
		'book_author'     => 'sanitize_text_field',
		'book_url'        => 'esc_url_raw',
		'book_link_label' => 'sanitize_text_field',
	);

	foreach ( $meta_fields as $meta_key => $sanitize_callback ) {
		register_post_meta(
			'book',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => static function (): bool {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'gratia_books_register_content_type', 0 );

/**
 * Ensure the initial workflow terms exist before theme patterns are rendered.
 */
function gratia_books_register_default_statuses(): void {
	$statuses = array(
		'published'   => __( 'Published', 'gratia-gratis' ),
		'coming-soon' => __( 'Coming Soon', 'gratia-gratis' ),
	);

	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'book_status' ) ) {
			wp_insert_term( $name, 'book_status', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'gratia_books_register_default_statuses', 1 );

/**
 * Add the small set of book-specific fields to the editor.
 */
function gratia_books_add_details_meta_box(): void {
	add_meta_box(
		'gratia-book-details',
		__( 'Book details', 'gratia-gratis' ),
		'gratia_books_render_details_meta_box',
		'book',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_book', 'gratia_books_add_details_meta_box' );

/**
 * Render the Book details editor fields.
 *
 * @param WP_Post $post Current Book post.
 */
function gratia_books_render_details_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'gratia_books_save_details', 'gratia_books_details_nonce' );

	$author     = (string) get_post_meta( $post->ID, 'book_author', true );
	$url        = (string) get_post_meta( $post->ID, 'book_url', true );
	$link_label = (string) get_post_meta( $post->ID, 'book_link_label', true );
	?>
	<p>
		<label for="gratia-book-author"><strong><?php esc_html_e( 'Book author', 'gratia-gratis' ); ?></strong></label>
		<input class="widefat" id="gratia-book-author" name="book_author" type="text" value="<?php echo esc_attr( $author ); ?>">
	</p>
	<p>
		<label for="gratia-book-url"><strong><?php esc_html_e( 'Purchase or preview URL', 'gratia-gratis' ); ?></strong></label>
		<input class="widefat" id="gratia-book-url" name="book_url" type="url" value="<?php echo esc_attr( $url ); ?>">
	</p>
	<p>
		<label for="gratia-book-link-label"><strong><?php esc_html_e( 'Link label', 'gratia-gratis' ); ?></strong></label>
		<input class="widefat" id="gratia-book-link-label" name="book_link_label" type="text" value="<?php echo esc_attr( $link_label ); ?>" placeholder="<?php esc_attr_e( 'Learn more ↗', 'gratia-gratis' ); ?>">
	</p>
	<p class="description"><?php esc_html_e( 'Use the featured image panel for the cover and Book Status for Published or Coming Soon.', 'gratia-gratis' ); ?></p>
	<?php
}

/**
 * Persist Book details from the editor.
 *
 * @param int $post_id Current Book post ID.
 */
function gratia_books_save_details( int $post_id ): void {
	if (
		! isset( $_POST['gratia_books_details_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gratia_books_details_nonce'] ) ), 'gratia_books_save_details' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$fields = array(
		'book_author'     => 'sanitize_text_field',
		'book_url'        => 'esc_url_raw',
		'book_link_label' => 'sanitize_text_field',
	);

	foreach ( $fields as $meta_key => $sanitize_callback ) {
		$value = isset( $_POST[ $meta_key ] ) ? call_user_func( $sanitize_callback, wp_unslash( $_POST[ $meta_key ] ) ) : '';

		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
add_action( 'save_post_book', 'gratia_books_save_details' );

/**
 * Locate an existing media-library cover by its stable filename stem.
 *
 * @param string $filename_stem Partial original filename.
 * @return int Attachment ID, or zero when unavailable.
 */
function gratia_books_find_cover_attachment( string $filename_stem ): int {
	global $wpdb;

	$like = '%' . $wpdb->esc_like( $filename_stem ) . '%';
	$id   = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);

	return absint( $id );
}

/**
 * One-time migration of the five covers from the legacy Books page into Book posts.
 * The catalog templates query these posts and contain no book-specific content.
 */
function gratia_books_migrate_legacy_catalog(): void {
	if ( '1' === get_option( 'gratia_books_migration_version' ) || wp_installing() ) {
		return;
	}

	$existing_books = get_posts(
		array(
			'post_type'        => 'book',
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);

	if ( $existing_books ) {
		update_option( 'gratia_books_migration_version', '1', false );
		return;
	}

	$legacy_books = array(
		array(
			'title'      => 'Un Solenne Avvertimento',
			'author'     => 'Thomas Taylor',
			'status'     => 'published',
			'url'        => 'https://www.amazon.it/dp/B0BLT3JZCD',
			'link_label' => 'Amazon ↗',
			'cover'      => '71sWk7J32pL',
		),
		array(
			'title'      => 'Dio è Eterno',
			'author'     => 'William Lane Craig',
			'status'     => 'published',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-eterno-dr-william-lane-craig/',
			'link_label' => 'Publisher ↗',
			'cover'      => 'cop-Dio-e-eterno',
		),
		array(
			'title'      => 'L’Eredità degli Affamati',
			'author'     => 'Zane C. Hodges',
			'status'     => 'published',
			'url'        => 'https://www.amazon.it/dp/B0CX97WCC8',
			'link_label' => 'Amazon ↗',
			'cover'      => '61G5eqbv14L',
		),
		array(
			'title'      => 'Dio è Spirito',
			'author'     => 'William Lane Craig',
			'status'     => 'coming-soon',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-spirito-dr-william-craig/',
			'link_label' => 'Preview ↗',
			'cover'      => 'cop-Dio-e-Spirito',
		),
		array(
			'title'      => 'Dio è Ovunque',
			'author'     => 'William Lane Craig',
			'status'     => 'coming-soon',
			'url'        => 'https://solideogloriaedizioni.com/prodotto/dio-e-ovunque-dr-william-lane-craig/',
			'link_label' => 'Preview ↗',
			'cover'      => 'Libro-Dio-e-Ovunque',
		),
	);

	foreach ( $legacy_books as $index => $book ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'book',
				'post_status' => 'publish',
				'post_title'  => $book['title'],
				'menu_order'  => ( $index + 1 ) * 10,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, 'book_author', $book['author'] );
		update_post_meta( $post_id, 'book_url', $book['url'] );
		update_post_meta( $post_id, 'book_link_label', $book['link_label'] );
		wp_set_object_terms( $post_id, $book['status'], 'book_status' );

		$cover_id = gratia_books_find_cover_attachment( $book['cover'] );
		if ( $cover_id ) {
			set_post_thumbnail( $post_id, $cover_id );
		}
	}

	update_option( 'gratia_books_migration_version', '1', false );
}
add_action( 'init', 'gratia_books_migrate_legacy_catalog', 30 );

/**
 * Refresh rewrite rules once when the content type is introduced.
 */
function gratia_books_maybe_flush_rewrite_rules(): void {
	if ( '1' !== get_option( 'gratia_books_rewrite_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'gratia_books_rewrite_version', '1', false );
	}
}
add_action( 'init', 'gratia_books_maybe_flush_rewrite_rules', 99 );
