<?php
/**
 * Remove ElasticPress and Jetpack activation, settings, and scheduled jobs.
 *
 * Posts, attachments, and block markup are intentionally preserved.
 */

return static function () {
	global $wpdb;

	$plugin_files = array(
		'elasticpress/elasticpress.php',
		'jetpack/jetpack.php',
	);

	$delete_prefixes = static function ( string $table, string $column, array $prefixes ) use ( $wpdb ): void {
		foreach ( $prefixes as $prefix ) {
			$result = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table} WHERE {$column} LIKE %s",
					$wpdb->esc_like( $prefix ) . '%'
				)
			);
			if ( false === $result ) {
				throw new RuntimeException( "Failed to delete {$prefix} records from {$table}: {$wpdb->last_error}" );
			}
		}
	};

	$cleanup_blog = static function () use ( $wpdb, $plugin_files, $delete_prefixes ): void {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_array( $active_plugins ) ) {
			$filtered = array_values( array_diff( $active_plugins, $plugin_files ) );
			if ( $filtered !== $active_plugins && ! update_option( 'active_plugins', $filtered ) ) {
				throw new RuntimeException( 'Failed to remove ElasticPress and Jetpack from active_plugins.' );
			}
		}

		$delete_prefixes(
			$wpdb->options,
			'option_name',
			array(
				'ep_',
				'elasticpress_',
				'jetpack_',
				'jp_',
				'_transient_ep_',
				'_transient_timeout_ep_',
				'_transient_jetpack_',
				'_transient_timeout_jetpack_',
				'_transient_jp_',
				'_transient_timeout_jp_',
			)
		);

		foreach ( array( 'stats_options', 'stats_dashboard_widget' ) as $option ) {
			delete_option( $option );
		}

		$cron = _get_cron_array();
		if ( is_array( $cron ) ) {
			$hooks = array();
			foreach ( $cron as $events ) {
				foreach ( array_keys( $events ) as $hook ) {
					if (
						str_starts_with( $hook, 'ep_' )
						|| str_starts_with( $hook, 'jetpack_' )
						|| str_starts_with( $hook, 'jp_' )
						|| str_starts_with( $hook, 'grunion_' )
						|| str_starts_with( $hook, 'wordads_' )
					) {
						$hooks[ $hook ] = true;
					}
				}
			}
			foreach ( array_keys( $hooks ) as $hook ) {
				$result = wp_clear_scheduled_hook( $hook );
				if ( false === $result || is_wp_error( $result ) ) {
					throw new RuntimeException( "Failed to clear the {$hook} cron hook." );
				}
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			$administrator->remove_cap( 'manage_elasticpress' );
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

	$delete_prefixes( $wpdb->usermeta, 'meta_key', array( 'jetpack_', 'jp_' ) );

	if ( is_multisite() ) {
		$delete_prefixes(
			$wpdb->sitemeta,
			'meta_key',
			array(
				'ep_',
				'elasticpress_',
				'jetpack_',
				'jp_',
				'_site_transient_ep_',
				'_site_transient_timeout_ep_',
				'_site_transient_jetpack_',
				'_site_transient_timeout_jetpack_',
				'_site_transient_jp_',
				'_site_transient_timeout_jp_',
			)
		);

		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network_plugins ) ) {
			$filtered = array_diff_key( $network_plugins, array_flip( $plugin_files ) );
			if ( $filtered !== $network_plugins && ! update_site_option( 'active_sitewide_plugins', $filtered ) ) {
				throw new RuntimeException( 'Failed to remove network-active ElasticPress and Jetpack plugins.' );
			}
		}
	}

	wp_cache_flush();
};
