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
 * Use the editable purchase or preview destination for Book card permalinks.
 * Books without an external destination retain their local detail page.
 *
 * @param string  $permalink Generated Book permalink.
 * @param WP_Post $post      Current Book post.
 * @return string
 */
function gratia_books_destination_permalink( string $permalink, WP_Post $post ): string {
	if ( 'book' !== $post->post_type ) {
		return $permalink;
	}

	$destination = esc_url_raw( (string) get_post_meta( $post->ID, 'book_url', true ) );

	return $destination ?: $permalink;
}
add_filter( 'post_type_link', 'gratia_books_destination_permalink', 10, 2 );

/**
 * Refresh rewrite rules once when the content type is introduced.
 */
function gratia_books_maybe_flush_rewrite_rules(): void {
	if ( '2' !== get_option( 'gratia_books_rewrite_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'gratia_books_rewrite_version', '2', false );
	}
}
add_action( 'init', 'gratia_books_maybe_flush_rewrite_rules', 99 );
