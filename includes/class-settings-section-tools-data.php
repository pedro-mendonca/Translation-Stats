<?php
/**
 * Class file for registering Translation Stats settings section of data tools.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Tools_Data' ) ) {

	/**
	 * Class Settings_Section_Tools_Data.
	 */
	class Settings_Section_Tools_Data extends Settings_Section {


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'tools_data', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'Plugin Data', 'translation-stats' ),
				'description' => null,
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'tools',
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
					'id'          => 'delete_data_on_uninstall',
					'type'        => 'checkbox',
					'class'       => '',
					'title'       => __( 'Uninstall', 'translation-stats' ),
					'label'       => __( 'Delete all plugin data on uninstall', 'translation-stats' ),
					'description' => __( 'Check to delete all Translation Stats plugin settings and cache on uninstall.', 'translation-stats' ),
					'helper'      => __( 'Need help?', 'translation-stats' ),
					'default'     => true,
				),
				array(
					'id'           => 'reset_settings',
					'name'         => 'reset_settings',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Reset Settings', 'translation-stats' ),
					'label'        => __( 'Reset', 'translation-stats' ),
					'description'  => __( 'Click to restore the default Translation Stats plugin settings.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! Translation Stats plugin settings will be reset to default! Click \'Cancel\' to go back, \'OK\' to reset.', 'translation-stats' ),
				),

			);
		}
	}
}
