<?php
/**
 * Primary class file for the Translation Stats plugin.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Translation_Stats' ) ) {

	/**
	 * Class Translation_Stats.
	 */
	class Translation_Stats {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_styles' ) );

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

			// Add Plugin action links.
			add_filter( 'plugin_action_links_' . TRANSLATION_STATS_FILE, array( $this, 'plugin_action_links' ) );

			// Initialize the plugin activation.
			new Activation();

			// Initialize the plugin database update check.
			new DB_Update();

			// Initialize the plugin settings.
			new Settings();

			// Initialize the plugins page metadata view.
			new Plugins();

			// Initialize the plugin debug.
			if ( Utils::is_development_mode() ) {
				new Debug();
			}
		}


		/**
		 * Add action links to the settings on the Plugins screen.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_action_links() to plugin_action_links().
		 *
		 * @param array $links  Array of plugin action links.
		 *
		 * @return array        Array with added Translation Stats action links.
		 */
		public function plugin_action_links( $links ) {
			$translationstats_links = array(
				'<a href="' . admin_url( 'options-general.php?page=' . TRANSLATION_STATS_SETTINGS_PAGE ) . '">' . __( 'Settings', 'translation-stats' ) . '</a>',
			);
			return array_merge( $translationstats_links, $links );
		}


		/**
		 * Register and enqueue style sheet.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_register_plugin_styles() to register_plugin_styles().
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function register_plugin_styles( $hook ) {

			if ( ! $this->allowed_pages( $hook ) ) {
				return;
			}

			wp_register_style(
				'translation-stats',
				Utils::get_asset_url( 'css/admin.css', true ),
				array(),
				TRANSLATION_STATS_VERSION
			);

			wp_enqueue_style( 'translation-stats' );
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_register_plugin_scripts() to register_plugin_scripts().
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function register_plugin_scripts( $hook ) {

			if ( ! $this->allowed_pages( $hook ) ) {
				return;
			}

			// Variables to send to JavaScript.
			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);

			// Check for Translation Stats settings page.
			if ( 'settings_page_' . TRANSLATION_STATS_SETTINGS_PAGE === $hook ) {

				wp_register_script(
					'translation-stats-settings',
					Utils::get_asset_url( 'js/admin-settings.js', true ),
					array(
						'jquery',
					),
					TRANSLATION_STATS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-stats-settings' );

				wp_localize_script(
					'translation-stats-settings',
					'tstats',
					$vars
				);

				wp_register_script(
					'translation-stats-tablesorter',
					Utils::get_asset_url( 'lib/tablesorter/jquery.tablesorter.combined.js', true ),
					array(
						'jquery',
					),
					'2.31.3',
					false
				);

				wp_enqueue_script( 'translation-stats-tablesorter' );

			}

			// Check for plugins page.
			if ( 'plugins.php' === $hook ) {

				wp_register_script(
					'translation-stats-plugins',
					Utils::get_asset_url( 'js/admin-plugins.js', true ),
					array(
						'jquery',
					),
					TRANSLATION_STATS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-stats-plugins' );

				wp_set_script_translations( 'translation-stats-plugins', 'translation-stats' );

				wp_localize_script(
					'translation-stats-plugins',
					'tstats',
					$vars
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
		public function allowed_pages( $hook ) {

			// Check for plugins page, updates page and Translation Stats settings page.
			if ( 'plugins.php' === $hook || 'update-core.php' === $hook || 'settings_page_' . TRANSLATION_STATS_SETTINGS_PAGE === $hook ) {
				return true;
			}

			return false;
		}
	}
}
