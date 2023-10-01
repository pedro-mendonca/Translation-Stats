<?php
/**
 * Class file for registering Translation Stats settings section of transient tools.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Tools_Transients' ) ) {

	/**
	 * Class Settings_Section_Tools_Transients.
	 */
	class Settings_Section_Tools_Transients extends Settings_Section {


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'tools_transients', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'Cache', 'translation-stats' ),
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
					'id'             => 'transients_expiration',
					'type'           => 'select',
					'class'          => '',
					'title'          => __( 'Expiration', 'translation-stats' ),
					'label'          => __( 'Select cache expiration', 'translation-stats' ),
					'description'    => __( 'Set the cache expiration to update the translation stats.', 'translation-stats' ),
					'helper'         => __( 'Need help?', 'translation-stats' ),
					'select_options' => array(
						'3600'   => __( '60 Minutes', 'translation-stats' ),
						'86400'  => __( '24 Hours', 'translation-stats' ),
						'604800' => __( '7 Days', 'translation-stats' ),
					),
					'default'        => '86400',
				),
				array(
					'id'           => 'delete_transients',
					'name'         => 'delete_transients',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Clean Cache', 'translation-stats' ),
					'label'        => __( 'Clean', 'translation-stats' ),
					'description'  => __( 'Click to delete all Translation Stats cache and force update translation stats.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! All Translation Stats plugin cache will be deleted! Click \'Cancel\' to go back, \'OK\' to delete.', 'translation-stats' ),
				),

			);
		}
	}
}
