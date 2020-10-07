<?php
/**
 * Class file for registering Translation Stats transients.
 *
 * @package Translation Stats
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

			$tstats_transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT option_name AS name FROM $wpdb->options WHERE option_name LIKE %s",
					'%_transient_' . $search . '%'
				)
			);
			$tstats_transients = array_map(
				function( $object ) {
					return $object->name;
				},
				$tstats_transients
			);

			return $tstats_transients;
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
			$tstats_transients = $this->get_transients( $prefix );
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

	}
}
