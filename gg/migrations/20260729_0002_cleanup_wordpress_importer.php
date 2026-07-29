<?php
/**
 * Remove the one-time WordPress Importer from activation state.
 *
 * Imported posts, comments, terms, users, media, and metadata are standard
 * WordPress content and are intentionally preserved.
 */

return static function () {
	$plugin_file = 'wordpress-importer/wordpress-importer.php';

	$cleanup_blog = static function () use ( $plugin_file ): void {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( ! is_array( $active_plugins ) ) {
			return;
		}

		$filtered = array_values( array_diff( $active_plugins, array( $plugin_file ) ) );
		if ( $filtered !== $active_plugins && ! update_option( 'active_plugins', $filtered ) ) {
			throw new RuntimeException( 'Failed to remove WordPress Importer from active_plugins.' );
		}
	};

	$original_blog_id = get_current_blog_id();
	$site_ids         = is_multisite()
		? get_sites( array( 'fields' => 'ids', 'number' => 0 ) )
		: array( $original_blog_id );

	foreach ( $site_ids as $site_id ) {
		$switched = is_multisite() && (int) $site_id !== get_current_blog_id();
		if ( $switched ) {
			switch_to_blog( (int) $site_id );
		}

		try {
			$cleanup_blog();
		} finally {
			if ( $switched ) {
				restore_current_blog();
			}
		}
	}

	if ( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network_plugins ) && isset( $network_plugins[ $plugin_file ] ) ) {
			unset( $network_plugins[ $plugin_file ] );
			if ( ! update_site_option( 'active_sitewide_plugins', $network_plugins ) ) {
				throw new RuntimeException( 'Failed to remove the network-active WordPress Importer.' );
			}
		}
	}

	wp_cache_flush();
};
