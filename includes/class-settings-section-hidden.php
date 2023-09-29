<?php
/**
 * Class file for registering Translation Stats hidden settings section.
 *
 * @package Translation_Stats
 *
 * @since 1.2.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Hidden' ) ) {

	/**
	 * Class Settings_Section_Hidden.
	 */
	class Settings_Section_Hidden extends Settings_Section {


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'hidden', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => null,
				'description' => null,
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'hidden',
			);
		}


		/**
		 * Fields for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section fields.
		 */
		public function fields() {

			return array(
				array(
					'id'      => 'settings_version',
					'type'    => 'hidden',
					'title'   => null,
					'default' => TRANSLATION_STATS_SETTINGS_VERSION,
				),

			);
		}
	}
}
