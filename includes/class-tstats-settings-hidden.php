<?php
/**
 * Class file for registering Translation Stats Hidden Settings.
 *
 * @since 1.0.0
 *
 * @package Translation Stats
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Hidden' ) ) {

	/**
	 * Class TStats_Settings_Hidden.
	 */
	class TStats_Settings_Hidden {


		/**
		 * Settings API.
		 *
		 * @var object
		 */
		protected $settings_api;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Settings API.
			$this->settings_api = new TStats_Settings_API();

		}


		/**
		 * Registers Settings General page section.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_section() {

			add_settings_section(
				'tstats_settings__hidden',                    // String for use in the 'id' attribute of tags.
				'',                                           // Title of the section.
				array( $this, 'settings__hidden__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__hidden'                     // The menu page on which to display this section. Should match $menu_slug.
			);

		}


		/**
		 * Callback function for section "Settings".
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings__hidden__callback() {

			// Add 'settings_version' hidden field.
			$this->settings_api->tstats_render_input_hidden(
				array(
					'id'      => 'settings_version',
					'path'    => 'settings',
					'type'    => 'hidden',
					'default' => TSTATS_SETTINGS_VERSION,
				)
			);

		}

	}

}
