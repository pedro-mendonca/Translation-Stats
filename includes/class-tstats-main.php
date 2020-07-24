<?php
/**
 * Primary class file for the Translation Stats plugin.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

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

			// Initialize the plugin activation.
			new TStats_Activation();

			// Initialize the plugin database update check.
			new TStats_DB_Update();

			// Initialize the plugin settings.
			new TStats_Settings();

			// Initialize the plugins page metadata view.
			new TStats_Plugins();

			// Initialize the plugin debug.
			new TStats_Debug();

		}


		/**
		 * Add action links to the settings on the Plugins screen.
		 *
		 * @since 0.8.0
		 *
		 * @param array $links  Array of plugin action links.
		 *
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
		 * @since 0.8.0
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function tstats_register_plugin_styles( $hook ) {

			if ( ! $this->tstats_allowed_pages( $hook ) ) {
				return;
			}

			wp_register_style(
				'translation-stats',
				TSTATS_DIR_URL . 'css/admin.css',
				array(),
				TSTATS_VERSION
			);

			wp_enqueue_style( 'translation-stats' );

			// Add Dark Mode style sheet.
			// https://github.com/danieltj27/Dark-Mode/wiki/Help:-Plugin-Compatibility-Guide.
			add_action( 'doing_dark_mode', array( $this, 'tstats_register_plugin_styles_dark_mode' ) );
		}


		/**
		 * Register and enqueue Dark Mode style sheet.
		 *
		 * @since 0.8.0
		 *
		 * @return void
		 */
		public function tstats_register_plugin_styles_dark_mode() {

			wp_register_style(
				'translation-stats-dark-mode',
				TSTATS_DIR_URL . 'css/admin-dark-mode.css',
				array(),
				TSTATS_VERSION
			);

			wp_enqueue_style( 'translation-stats-dark-mode' );
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 0.8.0
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function tstats_register_plugin_scripts( $hook ) {

			if ( ! $this->tstats_allowed_pages( $hook ) ) {
				return;
			}

			$tstats_vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);

			// Check for Translation Stats settings page.
			if ( 'settings_page_' . TSTATS_SETTINGS_PAGE === $hook ) {

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-stats-settings',
					TSTATS_DIR_URL . 'js/tstats-settings' . $suffix . '.js',
					array(
						'jquery',
					),
					TSTATS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-stats-settings' );

				wp_localize_script(
					'translation-stats-settings',
					'tstats',
					$tstats_vars
				);

				wp_register_script(
					'tablesorter-combined',
					TSTATS_DIR_URL . 'js/vendor/jquery.tablesorter.combined' . $suffix . '.js',
					array(
						'jquery',
					),
					'2.31.3',
					false
				);

				wp_enqueue_script( 'tablesorter-combined' );

			}

			// Check for updates page.
			if ( 'update-core.php' === $hook ) {

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-stats-update-core',
					TSTATS_DIR_URL . 'js/tstats-update-core' . $suffix . '.js',
					array(
						'jquery',
					),
					TSTATS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-stats-update-core' );

				wp_localize_script(
					'translation-stats-update-core',
					'tstats',
					$tstats_vars
				);

			}

			// Check for plugins page.
			if ( 'plugins.php' === $hook ) {

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-stats-plugins',
					TSTATS_DIR_URL . 'js/tstats-plugins' . $suffix . '.js',
					array(
						'jquery',
					),
					TSTATS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-stats-plugins' );

				wp_localize_script(
					'translation-stats-plugins',
					'tstats',
					$tstats_vars
				);

			}

		}


		/**
		 * Set admin pages where to load Translation Stats styles and scripts.
		 *
		 * @since 0.9.3
		 *
		 * @param string $hook  Hook.
		 *
		 * @return bool  Return true if current page is allowed, false if isn't allowed.
		 */
		public function tstats_allowed_pages( $hook ) {

			// Check for plugins page, updates page and Translation Stats settings page.
			if ( 'plugins.php' === $hook || 'update-core.php' === $hook || 'settings_page_' . TSTATS_SETTINGS_PAGE === $hook ) {
				return true;
			}

			return false;

		}

	}

}
