<?php
/**
 * Title: Site header
 * Slug: gratia-gratis/header
 * Categories: header, gratia-gratis
 * Block Types: core/template-part/header
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"gg-header","style":{"spacing":{"padding":{"top":"1.4rem","bottom":"1.4rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull gg-header has-paper-background-color has-background" style="padding-top:1.4rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:1.4rem;padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">
			<!-- wp:site-logo {"width":46,"shouldSyncIcon":true} /-->
			<!-- wp:site-title {"level":0} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:navigation {"overlayMenu":"mobile","icon":"menu","layout":{"type":"flex","justifyContent":"center"}} -->
			<!-- wp:navigation-link {"label":"Content","url":"/blog/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Books","url":"/books/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"About us","url":"/about-us/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Contact","url":"/contact-us/","kind":"custom","isTopLevelLink":true} /-->
		<!-- /wp:navigation -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline gg-header-subscribe"} -->
		<div class="wp-block-button is-style-outline gg-header-subscribe"><a class="wp-block-button__link wp-element-button" href="#subscribe">Subscribe</a></div>
		<!-- /wp:button --></div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
