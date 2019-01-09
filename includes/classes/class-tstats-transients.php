<?php
/**
 * Class file for registering Translation Stats transients.
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Transients' ) ) {

	/**
	 * Class TStats_Transients.
	 */
	class TStats_Transients {

		/**
		 * Retrieve the site transients.
		 *
		 * @param string $search  Transient search term.
		 */
		public function tstats_get_transients( $search ) {
			global $wpdb;

			$tstats_transients = $wpdb->get_results(
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

			return $tstats_transients;
		}


		/**
		 * Delete the site transients.
		 *
		 * @param string $prefix  Transient prefix.
		 */
		public function tstats_delete_transients( $prefix ) {
			$tstats_transients = $this->tstats_get_transients( $prefix );
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
