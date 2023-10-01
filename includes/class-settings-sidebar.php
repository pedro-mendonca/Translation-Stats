<?php
/**
 * Class file for registering Translation Stats settings sidebar.
 *
 * @package Translation_Stats
 *
 * @since 0.9.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Sidebar' ) ) {

	/**
	 * Class Settings_Sidebar.
	 */
	class Settings_Sidebar {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Sidebar before Translation Stats settings.
			add_action( 'translation_stats_settings__before', array( $this, 'settings__sidebar' ) );
		}


		/**
		 * Show Translation Stats settings sidebar.
		 *
		 * @since 0.9.0
		 * @since 1.2.0   Renamed from tstats_settings__sidebar() to settings__sidebar().
		 *
		 * @return void
		 */
		public function settings__sidebar() {
			?>

			<div class="tstats-settings__sidebar">

				<?php
				// Add content to Translation Stats settings sidebar.
				do_action( 'translation_stats_settings_sidebar__content' );
				?>

			</div>
			<?php
		}
	}
}
