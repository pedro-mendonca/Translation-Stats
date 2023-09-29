<?php
/**
 * Class file for registering Translation Stats general settings section.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_General' ) ) {

	/**
	 * Class Settings_Section_General.
	 */
	class Settings_Section_General extends Settings_Section {


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'general', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'General Settings', 'translation-stats' ),
				'description' => null,
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'settings',
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
					'id'          => 'show_warnings',
					'type'        => 'checkbox',
					'class'       => '',
					'title'       => __( 'Warnings', 'translation-stats' ),
					'label'       => __( 'Show translation project warnings', 'translation-stats' ),
					'description' => __( 'Check this to show translation project error messages for selected plugins.', 'translation-stats' ),
					'helper'      => __( 'Need help?', 'translation-stats' ),
					'default'     => true,
				),
				array(
					'id'             => 'translation_language',
					'type'           => 'select_language',
					'class'          => '',
					'title'          => __( 'Translation Language', 'translation-stats' ),
					'label'          => __( 'Select translation language', 'translation-stats' ),
					'description'    => __( 'Select the language for which you want to show the translation stats.', 'translation-stats' ),
					'helper'         => __( 'Need help?', 'translation-stats' ),
					'select_options' => '',
					'default'        => 'site-default',
				),

			);
		}
	}
}
