<?php
/**
 * Translation Stats uninstall file to clean all settings and transient data from the database.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}


// Check if it is a multisite uninstall - if so, run the uninstall function for each blog id.
if ( is_multisite() ) {
	global $wpdb;
	foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $translation_stats_blog ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		switch_to_blog( $translation_stats_blog );
		translation_stats_uninstall();
	}
	restore_current_blog();
} else {
	translation_stats_uninstall();
}


/**
 * Removes ALL plugin data if set in the settings.
 *
 * @since 0.8.0
 *
 * @return void
 */
function translation_stats_uninstall() {
	$option = get_option( 'tstats_settings' );
	// Check if Delete Data on Uninstall is set, as string 'true' from the settings form or boolean true from the default settings.
	// TODO: turn setting to boolean instead of string 'true'.
	if ( ! empty( $option['settings']['delete_data_on_uninstall'] ) ) {
		// Delete option, set per site also in Multisite.
		delete_option( 'tstats_settings' );
		// Delete transients.
		translation_stats_uninstall_delete_transients( 'translation_stats_plugin_' );
	}
}


/**
 * Removes ALL transiantes on uninstall.
 *
 * @since 0.8.0
 *
 * @param string $search  Transient search term.
 *
 * @return void
 */
function translation_stats_uninstall_delete_transients( $search ) {
	global $wpdb;

	$transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT option_name AS name FROM $wpdb->options WHERE option_name LIKE %s",
			'%_transient_' . $search . '%'
		)
	);
	$transients = array_map(
		function ( $transient_object ) {
			return $transient_object->name;
		},
		$transients
	);
	if ( ! empty( $transients ) ) {
		foreach ( $transients as $transient ) {
			// Delete transients, set per site also in Multisite.
			delete_transient( substr( $transient, strlen( '_transient_' ) ) );
		}
	}
}
