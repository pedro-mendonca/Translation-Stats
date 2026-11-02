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
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Translation_Stats' ) ) {

	/**
	 * Class Translation_Stats.
	 */
	class Translation_Stats {


		/**
		 * Plugins instance, for the plugins page and the REST API.
		 *
		 * @var Plugins
		 */
		protected $plugins;


		/**
		 * Themes instance, for the REST API.
		 *
		 * @var Themes
		 */
		protected $themes;


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
			$this->plugins = new Plugins();

			// Initialize the themes translation stats.
			$this->themes = new Themes();

			// Initialize the Translation Stats block.
			new Stats_Block();

			// Register the REST API routes.
			add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

			// Initialize the plugin debug.
			if ( Utils::is_development_mode() ) {
				new Debug();
			}
		}


		/**
		 * Register the REST API routes.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function register_rest_routes() {

			$projects_controller = new Projects_REST_Controller( $this->plugins, $this->themes );
			$projects_controller->register_routes();
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
		 * @since 2.0.0   Use the style built with @wordpress/scripts, with RTL support.
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function register_plugin_styles( $hook ) {

			if ( ! $this->allowed_pages( $hook ) ) {
				return;
			}

			$asset = Utils::get_build_asset( 'css/admin', 'css' );

			wp_register_style(
				'translation-stats',
				$asset['url'],
				$asset['dependencies'],
				$asset['version']
			);

			// Use the built RTL style for right-to-left languages.
			wp_style_add_data( 'translation-stats', 'rtl', 'replace' );

			wp_enqueue_style( 'translation-stats' );
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_register_plugin_scripts() to register_plugin_scripts().
		 * @since 2.0.0   Use the scripts built with @wordpress/scripts, with their dependencies and version.
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

				// The settings script includes the tablesorter library.
				$asset = Utils::get_build_asset( 'js/admin-settings', 'js' );

				wp_register_script(
					'translation-stats-settings',
					$asset['url'],
					$asset['dependencies'],
					$asset['version'],
					false
				);

				wp_enqueue_script( 'translation-stats-settings' );

				wp_localize_script(
					'translation-stats-settings',
					'tstats',
					$vars
				);

			}

			// Check for plugins page.
			if ( 'plugins.php' === $hook ) {

				$asset = Utils::get_build_asset( 'js/admin-plugins', 'js' );

				wp_register_script(
					'translation-stats-plugins',
					$asset['url'],
					$asset['dependencies'],
					$asset['version'],
					false
				);

				wp_enqueue_script( 'translation-stats-plugins' );

				wp_set_script_translations( 'translation-stats-plugins', 'translation-stats' );

			}

			// Check for themes page of the site. In the Network Admin, the themes list table doesn't have the WordPress themes script.
			if ( 'themes.php' === $hook && ! is_network_admin() ) {

				$asset = Utils::get_build_asset( 'js/admin-themes', 'js' );

				// Load after the WordPress themes script, to extend its views before they are rendered on document ready.
				wp_register_script(
					'translation-stats-themes',
					$asset['url'],
					array_merge( $asset['dependencies'], array( 'theme' ) ),
					$asset['version'],
					true
				);

				wp_enqueue_script( 'translation-stats-themes' );

				wp_set_script_translations( 'translation-stats-themes', 'translation-stats' );

				// The themes settings URL, for the link in the Themes screen header.
				wp_localize_script(
					'translation-stats-themes',
					'tstatsThemes',
					array(
						'settingsUrl' => Themes::settings_url(),
					)
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

			// Check for themes page of the site, not the Network Admin themes list table, which has the same hook.
			if ( 'themes.php' === $hook && ! is_network_admin() ) {
				return true;
			}

			return false;
		}
	}
}
