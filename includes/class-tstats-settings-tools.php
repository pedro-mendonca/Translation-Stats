<?php
/**
 * Class file for registering Translation Stats Tools Settings.
 *
 * @since 0.9.9
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Tools' ) ) {

	/**
	 * Class TStats_Settings_Tools.
	 */
	class TStats_Settings_Tools {


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
		 * Registers Settings Tools page section.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Moved from class TStats_Settings() to TStats_Settings_Tools().
		 *                Renamed from tstats_settings_section__tools() to settings_section().
		 *
		 * @return void
		 */
		public function settings_section() {

			add_settings_section(
				'tstats_settings__tools__settings',                    // String for use in the 'id' attribute of tags.
				__( 'Settings', 'translation-stats' ),                 // Title of the section.
				array( $this, 'settings__tools__settings__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__tools__settings'                     // The menu page on which to display this section. Should match $menu_slug.
			);

			add_settings_section(
				'tstats_settings__tools__transients',                    // String for use in the 'id' attribute of tags.
				__( 'Cache', 'translation-stats' ),                      // Title of the section.
				array( $this, 'settings__tools__transients__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__tools__transients'                     // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings__tools__settings', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION                    // The WordPress option to store Translation Stats settings.
			);

			register_setting(
				'tstats_settings__tools__transients', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION                      // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Callback function for section "Tools > Settings".
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Moved from class TStats_Settings() to TStats_Settings_Tools().
		 *                Renamed from tstats_settings__tools__settings__callback() to settings__tools__settings__callback().
		 *
		 * @return void
		 */
		public function settings__tools__settings__callback() {

			$section = 'tstats_settings__tools__settings';

			/*
			Section description.
			esc_html_e( 'Advanced settings for Translation Stats', 'translation-stats' );
			*/

			$this->settings_api->tstats_add_settings_field(
				array(
					'section'     => $section,
					'path'        => 'settings',
					'id'          => 'delete_data_on_uninstall',
					'type'        => 'checkbox',
					'class'       => '',
					'title'       => __( 'Uninstall', 'translation-stats' ),
					'label'       => __( 'Delete all plugin data on uninstall', 'translation-stats' ),
					'description' => __( 'Check to delete all Translation Stats plugin settings and cache on uninstall.', 'translation-stats' ),
					'helper'      => __( 'Need help?', 'translation-stats' ),
					'callback'    => 'tstats_render_input_checkbox',
					'default'     => true,
				)
			);

			$this->settings_api->tstats_add_settings_field(
				array(
					'section'      => $section,
					'path'         => 'settings',
					'id'           => 'reset_settings',
					'name'         => 'reset_settings',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Reset Settings', 'translation-stats' ),
					'label'        => __( 'Reset', 'translation-stats' ),
					'description'  => __( 'Click to restore the default Translation Stats plugin settings.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'callback'     => 'tstats_render_input_button',
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! Translation Stats plugin settings will be reset to default! Click \'Cancel\' to go back, \'OK\' to reset.', 'translation-stats' ),
				)
			);

		}


		/**
		 * Callback function for section "Tools > Cache".
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Moved from class TStats_Settings() to TStats_Settings_Tools().
		 *                Renamed from tstats_settings__tools__settings__callback() to settings__tools__transients__callback().
		 *
		 * @return void
		 */
		public function settings__tools__transients__callback() {

			$section = 'tstats_settings__tools__transients';

			/*
			Section description.
			esc_html_e( 'Transient settings for Translation Stats', 'translation-stats' );
			*/

			$this->settings_api->tstats_add_settings_field(
				array(
					'section'        => $section,
					'path'           => 'settings',
					'id'             => 'transients_expiration',
					'type'           => 'select',
					'class'          => '',
					'title'          => __( 'Expiration', 'translation-stats' ),
					'label'          => __( 'Select cache expiration', 'translation-stats' ),
					'description'    => __( 'Set the cache expiration to update the translation stats.', 'translation-stats' ),
					'helper'         => __( 'Need help?', 'translation-stats' ),
					'callback'       => 'tstats_render_input_select',
					'select_options' => array(
						'3600'   => __( '60 Minutes', 'translation-stats' ),
						'86400'  => __( '24 Hours', 'translation-stats' ),
						'604800' => __( '7 Days', 'translation-stats' ),
					),
					'default'        => '86400',
				)
			);

			$this->settings_api->tstats_add_settings_field(
				array(
					'section'      => $section,
					'path'         => 'settings',
					'id'           => 'delete_transients',
					'name'         => 'delete_transients',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Clean Cache', 'translation-stats' ),
					'label'        => __( 'Clean', 'translation-stats' ),
					'description'  => __( 'Click to delete all Translation Stats cache and force update translation stats.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'callback'     => 'tstats_render_input_button',
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! All Translation Stats plugin cache will be deleted! Click \'Cancel\' to go back, \'OK\' to delete.', 'translation-stats' ),
				)
			);

		}

	}

}
