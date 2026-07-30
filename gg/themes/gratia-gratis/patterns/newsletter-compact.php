<?php
/**
 * Title: Compact newsletter signup
 * Slug: gratia-gratis/newsletter-compact
 * Categories: call-to-action, gratia-gratis
 * Block Types: core/template-part/newsletter
 * Description: Email-only newsletter treatment for interior templates.
 */
?>
<!-- wp:group {"metadata":{"name":"Compact newsletter signup"},"anchor":"subscribe","align":"full","className":"gg-newsletter gg-newsletter--compact","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40"}}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained"}} -->
<div id="subscribe" class="wp-block-group alignfull gg-newsletter gg-newsletter--compact has-paper-color has-ink-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"42%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:paragraph {"className":"gg-eyebrow"} --><p class="gg-eyebrow">The occasional letter</p><!-- /wp:paragraph --><!-- wp:heading {"fontSize":"x-large"} --><h2 class="wp-block-heading has-x-large-font-size">Stay in the loop.</h2><!-- /wp:heading --></div><!-- /wp:column -->
	<!-- wp:column {"verticalAlignment":"center","width":"58%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:shortcode -->[gratia_newsletter_form variant="compact"]<!-- /wp:shortcode --></div><!-- /wp:column --></div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
