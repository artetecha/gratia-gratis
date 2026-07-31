<?php
/**
 * Title: Books preview
 * Slug: gratia-gratis/books-preview
 * Categories: featured, gratia-gratis
 * Description: Four-book preview populated from Book posts and linking to the complete Books page.
 */
?>
<!-- wp:group {"metadata":{"name":"Books preview"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"parchment","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-parchment-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"},"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-bottom:var(--wp--preset--spacing--60)"><!-- wp:column {"width":"32%"} --><div class="wp-block-column" style="flex-basis:32%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">03 / Publishing</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column {"width":"68%"} --><div class="wp-block-column" style="flex-basis:68%"><!-- wp:heading {"className":"gg-section-title","fontSize":"x-large"} --><h2 class="wp-block-heading gg-section-title has-x-large-font-size">Grace-centred books, in Italian.</h2><!-- /wp:heading --></div><!-- /wp:column --></div>
	<!-- /wp:columns -->

	<!-- wp:query {"queryId":53,"query":{"perPage":4,"pages":0,"offset":0,"postType":"book","order":"asc","orderBy":"menu_order","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide","className":"gg-books-query gg-books-preview-query"} -->
	<div class="wp-block-query alignwide gg-books-query gg-books-preview-query"><!-- wp:post-template {"className":"gg-book-preview-grid","layout":{"type":"grid","columnCount":4}} -->
	<!-- wp:group {"className":"gg-book-card","layout":{"type":"default"}} -->
	<div class="wp-block-group gg-book-card"><!-- wp:post-featured-image {"isLink":true,"sizeSlug":"large","className":"gg-book-cover"} /--><!-- wp:post-title {"isLink":true,"level":3,"className":"gg-book-title","fontSize":"large"} /--><!-- wp:group {"className":"gg-book-info","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} --><div class="wp-block-group gg-book-info"><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"book_author"}}}},"className":"gg-meta"} --><p class="gg-meta"></p><!-- /wp:paragraph --><!-- wp:post-terms {"term":"book_status","className":"gg-meta"} /--></div><!-- /wp:group --></div>
	<!-- /wp:group -->
	<!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>No books have been added yet.</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div>
	<!-- /wp:query -->

	<!-- wp:buttons {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} --><div class="wp-block-buttons alignwide" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/books/">View the full book list →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div>
<!-- /wp:group -->
