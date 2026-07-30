<?php
/**
 * Title: Latest journal entries
 * Slug: gratia-gratis/journal-query
 * Categories: posts, query, gratia-gratis
 * Block Types: core/query
 * Description: Typography-led list of the three most recent posts.
 */
?>
<!-- wp:group {"metadata":{"name":"Latest journal entries"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"olive","textColor":"paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-paper-color has-olive-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide"><!-- wp:column {"width":"35%"} --><div class="wp-block-column" style="flex-basis:35%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">04 / Journal</p><!-- /wp:paragraph --><!-- wp:heading {"className":"gg-section-title","fontSize":"x-large"} --><h2 class="wp-block-heading gg-section-title has-x-large-font-size">Watch, read, listen.</h2><!-- /wp:heading --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">Long-form Bible study, reflections, and teaching for those who want to look again—and more closely.</p><!-- /wp:paragraph --><!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} --><div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/blog/">View all content →</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:column -->

	<!-- wp:column {"width":"65%"} --><div class="wp-block-column" style="flex-basis:65%"><!-- wp:query {"queryId":1,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"enhancedPagination":true} -->
	<div class="wp-block-query"><!-- wp:post-template {"className":"gg-query-list","layout":{"type":"default"}} -->
	<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:post-title {"isLink":true,"fontSize":"large"} /--><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"fontSize":"xs","fontFamily":"sans"} --><div class="wp-block-group has-sans-font-family has-xs-font-size"><!-- wp:post-date {"isLink":true} /--><!-- wp:post-author-name {"isLink":true} /--><!-- wp:post-terms {"term":"category"} /--></div><!-- /wp:group --></div><!-- /wp:group -->
	<!-- /wp:post-template --></div>
	<!-- /wp:query --></div><!-- /wp:column --></div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

