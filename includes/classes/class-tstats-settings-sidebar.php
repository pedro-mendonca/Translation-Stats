<?php
/**
 * Class file for registering Translation Stats settings sidebar.
 *
 * @since 0.9.0
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

			// Add Sidebar before Translation Stats settings.
			add_action( 'tstats_settings__before', array( $this, 'tstats_settings__sidebar' ) );

		}


		/**
		 * Show Translation Stats settings sidebar.
		 *
		 * @since 0.9.0
		 */
		public function tstats_settings__sidebar() {
			?>

			<div class="tstats-settings__sidebar">

				<h2 class="tstats-settings__sidebar__title">
					<?php
					printf(
						/* translators: Plugin Name and version - Do not translate! */
						esc_html__( 'Translation Stats %s', 'translation-stats' ),
						'<small>v.' . esc_html( TSTATS_VERSION ) . '</small>'
					);
					?>
				</h2>

				<?php
				// Add content to Translation Stats settings sidebar.
				do_action( 'tstats_settings__sidebar__content' );
				?>

			</div>
			<?php
		}

	}

}

new TStats_Settings_Sidebar();
