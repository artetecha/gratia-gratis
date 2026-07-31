<?php
/**
 * Title: Site footer
 * Slug: gratia-gratis/footer
 * Categories: footer, gratia-gratis
 * Block Types: core/template-part/footer
 * Inserter: no
 */

$footer_about_navigation_id = function_exists( 'gratia_gratis_get_navigation_id' )
	? gratia_gratis_get_navigation_id( 'footer-about-navigation' )
	: 0;
$footer_explore_navigation_id = function_exists( 'gratia_gratis_get_navigation_id' )
	? gratia_gratis_get_navigation_id( 'footer-explore-navigation' )
	: 0;
?>
<!-- wp:group {"align":"full","className":"gg-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull gg-footer has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"},"padding":{"bottom":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="padding-bottom:var(--wp--preset--spacing--50)">
		<!-- wp:column {"width":"50%"} -->
		<div class="wp-block-column" style="flex-basis:50%"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group"><!-- wp:site-logo {"width":46} /--><!-- wp:site-title {"level":0} /--></div>
		<!-- /wp:group --><!-- wp:paragraph {"fontSize":"medium"} --><p class="has-medium-font-size">A Free Grace ministry to Italy.</p><!-- /wp:paragraph --></div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"25%"} -->
		<div class="wp-block-column" style="flex-basis:25%"><!-- wp:heading {"level":3,"className":"gg-eyebrow"} --><h3 class="wp-block-heading gg-eyebrow">About</h3><!-- /wp:heading -->
		<?php if ( $footer_about_navigation_id ) : ?>
		<!-- wp:navigation {"ref":<?php echo absint( $footer_about_navigation_id ); ?>,"overlayMenu":"never","ariaLabel":"About","className":"gg-footer-navigation","layout":{"type":"flex","orientation":"vertical"}} /-->
		<?php else : ?>
		<!-- wp:navigation {"overlayMenu":"never","ariaLabel":"About","className":"gg-footer-navigation","layout":{"type":"flex","orientation":"vertical"}} -->
			<!-- wp:navigation-link {"label":"Our team","url":"/about-us/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Contact us","url":"/contact-us/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Join us","url":"/contact-us/","kind":"custom","isTopLevelLink":true} /-->
		<!-- /wp:navigation -->
		<?php endif; ?></div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"25%"} -->
		<div class="wp-block-column" style="flex-basis:25%"><!-- wp:heading {"level":3,"className":"gg-eyebrow"} --><h3 class="wp-block-heading gg-eyebrow">Explore</h3><!-- /wp:heading -->
		<?php if ( $footer_explore_navigation_id ) : ?>
		<!-- wp:navigation {"ref":<?php echo absint( $footer_explore_navigation_id ); ?>,"overlayMenu":"never","ariaLabel":"Explore","className":"gg-footer-navigation","layout":{"type":"flex","orientation":"vertical"}} /-->
		<?php else : ?>
		<!-- wp:navigation {"overlayMenu":"never","ariaLabel":"Explore","className":"gg-footer-navigation","layout":{"type":"flex","orientation":"vertical"}} -->
			<!-- wp:navigation-link {"label":"Journal","url":"/blog/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Books","url":"/books/","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"Give","url":"/#donate","kind":"custom","isTopLevelLink":true} /-->
		<!-- /wp:navigation -->
		<?php endif; ?></div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:group {"align":"wide","style":{"border":{"top":{"color":"#d7d5cf","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|30"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"fontSize":"xs","fontFamily":"sans"} -->
	<div class="wp-block-group alignwide has-sans-font-family has-xs-font-size" style="border-top-color:#d7d5cf;border-top-width:1px;padding-top:var(--wp--preset--spacing--30)"><!-- wp:paragraph --><p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Gratia Gratis</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Grace, freely given</p><!-- /wp:paragraph --></div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
