<?php
/**
 * Class file for registering Translation Stats Settings Sidebar.
 *
 * @since 0.8.6
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Sidebar' ) ) {

	/**
	 * Class TStats_Settings_Sidebar.
	 */
	class TStats_Settings_Sidebar {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Translation Stats settings debug content.
			add_action( 'tstats_settings__before', array( $this, 'tstats_settings__sidebar' ) );

		}


		/**
		 * Show plugin info sidebar.
		 *
		 * @since 0.8.6
		 *
		 * @param string $show  True or false.
		 */
		public function tstats_settings__sidebar() {

		}

	}

}

new TStats_Settings_Sidebar();
