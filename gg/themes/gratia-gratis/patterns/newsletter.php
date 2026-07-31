<?php
/**
 * Title: Newsletter signup
 * Slug: gratia-gratis/newsletter
 * Categories: call-to-action, gratia-gratis
 * Block Types: core/template-part/newsletter
 * Description: Full newsletter signup powered by the existing MC4WP form.
 */
?>
<!-- wp:group {"metadata":{"name":"Newsletter signup"},"anchor":"subscribe","align":"full","className":"gg-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40"}}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained"}} -->
<div id="subscribe" class="wp-block-group alignfull gg-newsletter has-paper-color has-ink-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","verticalAlignment":"bottom","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-bottom"><!-- wp:column {"verticalAlignment":"bottom","width":"42%"} --><div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:42%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">The occasional letter</p><!-- /wp:paragraph --><!-- wp:heading {"fontSize":"x-large"} --><h2 class="wp-block-heading has-x-large-font-size">Stay in the loop.</h2><!-- /wp:heading --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">Ministry updates, new writing, and published translations—sent thoughtfully, never noisily.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
	<!-- wp:column {"verticalAlignment":"bottom","width":"58%"} --><div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:58%"><!-- wp:shortcode -->[gratia_newsletter_form variant="full"]<!-- /wp:shortcode --></div><!-- /wp:column --></div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
