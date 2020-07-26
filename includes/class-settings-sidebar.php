<?php
/**
 * Class file for registering Translation Stats settings sidebar.
 *
 * @since 0.9.0
 *
 * @package Translation Stats
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
			add_action( 'tstats_settings__before', array( $this, 'tstats_settings__sidebar' ) );

		}


		/**
		 * Show Translation Stats settings sidebar.
		 *
		 * @since 0.9.0
		 *
		 * @return void
		 */
		public function tstats_settings__sidebar() {
			?>

			<div class="tstats-settings__sidebar">

				<?php
				// Add content to Translation Stats settings sidebar.
				do_action( 'tstats_settings__sidebar__content' );
				?>

			</div>
			<?php
		}

	}

}
