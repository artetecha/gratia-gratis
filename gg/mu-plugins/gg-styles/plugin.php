<?php
/**
 * Plugin Name: Gratia Custom Styles
 * Author: Gratia Gratis
 */

add_action( 'wp_enqueue_scripts', function() {
	// Only load if the CSS file actually exists to prevent errors
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'form-style.css' ) ) {

		wp_enqueue_style(
			'gratia-mc4wp-styles', // Handle name
			plugins_url( 'form-style.css', __FILE__ ), // URL to the CSS file
			[],
			'1.0.0'
		);

	}
});
