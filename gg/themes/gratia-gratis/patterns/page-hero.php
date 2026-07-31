<?php
/**
 * Title: Dynamic page hero
 * Slug: gratia-gratis/page-hero
 * Categories: banner, gratia-gratis
 * Block Types: core/post-title
 * Description: Dynamic title for standard pages.
 */
?>
<!-- wp:group {"metadata":{"name":"Page hero"},"align":"full","className":"gg-page-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"parchment","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull gg-page-hero has-parchment-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","verticalAlignment":"bottom","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-bottom"><!-- wp:column {"verticalAlignment":"bottom","width":"32%"} --><div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:32%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">Gratia Gratis</p><!-- /wp:paragraph --></div><!-- /wp:column -->
	<!-- wp:column {"verticalAlignment":"bottom","width":"68%"} --><div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:68%"><!-- wp:post-title {"level":1,"fontSize":"display"} /--></div><!-- /wp:column --></div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
