<?php
/**
 * Class file for registering Translation Stats transients.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Transients' ) ) {

	/**
	 * Class Transients.
	 */
	class Transients {


		/**
		 * Retrieve the site transients.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_get_transients() to get_transients().
		 *
		 * @param string $search  Transient search term.
		 *
		 * @return array  Search result of transients.
		 */
		public function get_transients( $search ) {
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

			return $transients;
		}


		/**
		 * Delete the site transients.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_delete_transients() to delete_transients().
		 *
		 * @param string $prefix  Transient prefix.
		 *
		 * @return void
		 */
		public function delete_transients( $prefix ) {
			$transients = $this->get_transients( $prefix );
			if ( is_array( $transients ) ) {
				foreach ( $transients as $transient ) {
					if ( is_multisite() ) {
						// Delete transients in Multisite.
						delete_site_transient( substr( $transient, strlen( '_transient_' ) ) );
					} else {
						// Delete transients.
						delete_transient( substr( $transient, strlen( '_transient_' ) ) );
					}
				}
			}
		}
	}
}
