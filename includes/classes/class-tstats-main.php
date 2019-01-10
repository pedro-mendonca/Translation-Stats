<?php
/**
 * Primary class file for the Translation Stats plugin.
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Main' ) ) {

	/**
	 * Class TStats_Main.
	 */
	class TStats_Main {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'tstats_register_plugin_styles' ) );

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'tstats_register_plugin_scripts' ) );

			// Add Plugin action links.
			add_filter( 'plugin_action_links_' . TSTATS_FILE, array( $this, 'tstats_action_links' ) );

		}


		/**
		 * Add action links to the settings on the Plugins screen.
		 *
		 * @param array $links  Array of plugin action links.
		 * @return array        Array with added Translation Stats action links.
		 */
		public function tstats_action_links( $links ) {
			$tstats_links = array(
				'<a href="' . admin_url( 'options-general.php?page=' . TSTATS_SETTINGS_PAGE ) . '">' . __( 'Settings', 'translation-stats' ) . '</a>',
			);
			return array_merge( $tstats_links, $links );
		}


		/**
		 * Register and enqueue style sheet.
		 *
		 * @param string $hook  Hook.
		 */
		public function tstats_register_plugin_styles( $hook ) {

			// Loads plugin style sheets only in the plugins page.
			if ( 'plugins.php' !== $hook && 'settings_page_' . TSTATS_SETTINGS_PAGE !== $hook ) {
				return;
			};

			wp_register_style(
				'translation-stats',
				TSTATS_PATH . 'css/admin.css',
				false,
				TSTATS_VERSION
			);

			wp_enqueue_style( 'translation-stats' );

			// Add Dark Mode style sheet.
			// https://github.com/danieltj27/Dark-Mode/wiki/Help:-Plugin-Compatibility-Guide.
			add_action( 'doing_dark_mode', array( $this, 'tstats_register_plugin_styles_dark_mode' ) );
		}


		/**
		 * Register and enqueue Dark Mode style sheet.
		 */
		public function tstats_register_plugin_styles_dark_mode() {

			wp_register_style(
				'translation-stats-dark-mode',
				TSTATS_PATH . 'css/admin-dark-mode.css',
				false,
				TSTATS_VERSION
			);

			wp_enqueue_style( 'translation-stats-dark-mode' );
		}


		/**
		 * Register and enqueue scripts.
		 */
		public function tstats_register_plugin_scripts() {

			wp_register_script(
				'translation-stats',
				TSTATS_PATH . 'js/admin.js',
				false,
				TSTATS_VERSION,
				false
			);

			wp_enqueue_script( 'translation-stats' );
		}

	}

}

new TStats_Main();
