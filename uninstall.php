<?php
/**
 * Translation Stats uninstall file to clean all settings and transient data from the database.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}


// Check if it is a multisite uninstall - if so, run the uninstall function for each blog id.
if ( is_multisite() ) {
	foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $tstats_blog ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		switch_to_blog( $tstats_blog );
		tstats_uninstall();
	}
	restore_current_blog();
} else {
	tstats_uninstall();
}


/**
 * Removes ALL plugin data if set in the settings.
 *
 * @since 0.8.0
 *
 * @param string $search  Transient search term.
 */
function tstats_uninstall() {
	$option = get_option( 'tstats_settings' );
	// Check if Delete Data on Uninstall is set.
	if ( empty( $option['delete_data_on_uninstall'] ) ) {
		return;
	} else {
		if ( is_multisite() ) {
			// Delete option in Multisite.
			delete_site_option( 'tstats_settings' );
		} else {
			// Delete option.
			delete_option( 'tstats_settings' );
		}
		// Delete transients.
		tstats_uninstall_delete_transients( 'translation_stats_plugin_' );
	}
}


/**
 * Removes ALL transiantes on uninstall.
 *
 * @since 0.8.0
 */
function tstats_uninstall_delete_transients( $search ) {
	global $wpdb;

	$tstats_transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT option_name AS name FROM $wpdb->options WHERE option_name LIKE %s",
			'%_transient_' . $search . '%'
		)
	);
	$tstats_transients = array_map(
		function( $o ) {
			return $o->name;
		},
		$tstats_transients
	);
	if ( is_array( $tstats_transients ) ) {
		foreach ( $tstats_transients as $tstats_transient ) {
			if ( is_multisite() ) {
				// Delete transients in Multisite.
				delete_site_transient( substr( $tstats_transient, strlen( '_transient_' ) ) );
			} else {
				// Delete transients.
				delete_transient( substr( $tstats_transient, strlen( '_transient_' ) ) );
			}
		}
	}
}
