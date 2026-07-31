<?php
/**
 * Title: Complete books catalog
 * Slug: gratia-gratis/books-catalog
 * Categories: featured, gratia-gratis
 * Description: Dynamic published and forthcoming Book queries with a translation call to action.
 */

$published_term = get_term_by( 'slug', 'published', 'book_status' );
$coming_term    = get_term_by( 'slug', 'coming-soon', 'book_status' );
$published_id   = $published_term instanceof WP_Term ? $published_term->term_id : 0;
$coming_id      = $coming_term instanceof WP_Term ? $coming_term->term_id : 0;
?>
<!-- wp:group {"metadata":{"name":"Complete books catalog"},"align":"full","className":"gg-books-catalog","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"parchment","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull gg-books-catalog has-parchment-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:group {"align":"wide","className":"gg-books-group","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide gg-books-group">
		<!-- wp:group {"className":"gg-books-group-heading","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
		<div class="wp-block-group gg-books-group-heading"><!-- wp:heading {"fontSize":"x-large"} --><h2 class="wp-block-heading has-x-large-font-size">Published</h2><!-- /wp:heading --><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">Available now</p><!-- /wp:paragraph --></div>
		<!-- /wp:group -->

		<!-- wp:query {"queryId":51,"query":{"perPage":100,"pages":0,"offset":0,"postType":"book","order":"asc","orderBy":"menu_order","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"include":{"book_status":[<?php echo absint( $published_id ); ?>]}}},"className":"gg-books-query gg-books-query--published"} -->
		<div class="wp-block-query gg-books-query gg-books-query--published"><!-- wp:post-template {"className":"gg-book-grid","layout":{"type":"grid","columnCount":3}} -->
		<!-- wp:group {"className":"gg-book-card","layout":{"type":"default"}} -->
		<div class="wp-block-group gg-book-card"><!-- wp:post-featured-image {"isLink":true,"sizeSlug":"large","className":"gg-book-cover"} /--><!-- wp:post-title {"isLink":true,"level":3,"className":"gg-book-title","fontSize":"large"} /--><!-- wp:group {"className":"gg-book-info","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} --><div class="wp-block-group gg-book-info"><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"book_author"}}}},"className":"gg-meta"} --><p class="gg-meta"></p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"metadata":{"bindings":{"url":{"source":"core/post-meta","args":{"key":"book_url"}},"text":{"source":"core/post-meta","args":{"key":"book_link_label"}}}},"className":"gg-book-action"} --><div class="wp-block-button gg-book-action"><a class="wp-block-button__link wp-element-button">Learn more ↗</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group --></div>
		<!-- /wp:group -->
		<!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>No published books have been added yet.</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div>
		<!-- /wp:query -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"gg-books-group","style":{"spacing":{"margin":{"top":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide gg-books-group" style="margin-top:var(--wp--preset--spacing--70)">
		<!-- wp:group {"className":"gg-books-group-heading","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
		<div class="wp-block-group gg-books-group-heading"><!-- wp:heading {"fontSize":"x-large"} --><h2 class="wp-block-heading has-x-large-font-size">Coming soon</h2><!-- /wp:heading --><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">In translation</p><!-- /wp:paragraph --></div>
		<!-- /wp:group -->

		<!-- wp:query {"queryId":52,"query":{"perPage":100,"pages":0,"offset":0,"postType":"book","order":"asc","orderBy":"menu_order","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"include":{"book_status":[<?php echo absint( $coming_id ); ?>]}}},"className":"gg-books-query gg-books-query--coming-soon"} -->
		<div class="wp-block-query gg-books-query gg-books-query--coming-soon"><!-- wp:post-template {"className":"gg-book-grid","layout":{"type":"grid","columnCount":3}} -->
		<!-- wp:group {"className":"gg-book-card","layout":{"type":"default"}} -->
		<div class="wp-block-group gg-book-card"><!-- wp:post-featured-image {"isLink":true,"sizeSlug":"large","className":"gg-book-cover"} /--><!-- wp:post-title {"isLink":true,"level":3,"className":"gg-book-title","fontSize":"large"} /--><!-- wp:group {"className":"gg-book-info","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} --><div class="wp-block-group gg-book-info"><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"book_author"}}}},"className":"gg-meta"} --><p class="gg-meta"></p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"metadata":{"bindings":{"url":{"source":"core/post-meta","args":{"key":"book_url"}},"text":{"source":"core/post-meta","args":{"key":"book_link_label"}}}},"className":"gg-book-action"} --><div class="wp-block-button gg-book-action"><a class="wp-block-button__link wp-element-button">Learn more ↗</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group --></div>
		<!-- /wp:group -->
		<!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>No forthcoming books have been added yet.</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div>
		<!-- /wp:query -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"gold","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-gold-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} --><div class="wp-block-columns alignwide"><!-- wp:column {"width":"28%"} --><div class="wp-block-column" style="flex-basis:28%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">Suggest a title</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column {"width":"50%"} --><div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading {"fontSize":"x-large"} --><h2 class="wp-block-heading has-x-large-font-size">What should we translate next?</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Our pipeline is never empty, but we welcome suggestions for sound, grace-centred books that are not yet available in Italian.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/contact-us/">Send a suggestion →</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:column --><!-- wp:column {"width":"22%"} --><div class="wp-block-column" style="flex-basis:22%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">Join the team</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Translators, proofreaders, designers, and publishing partners are always welcome.</p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns --></div>
<!-- /wp:group -->
