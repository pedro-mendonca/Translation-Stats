<?php
/**
 * Class file for registering Translation Stats Debug.
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

if ( ! class_exists( __NAMESPACE__ . '\Debug' ) ) {

	/**
	 * Class Debug.
	 */
	class Debug {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Translation Stats settings debug page tab and sections.
			add_filter( 'translation_stats_settings_pages', array( $this, 'add_settings_debug_page' ) );

			// Add Translation Stats settings field debug info.
			add_action( 'translation_stats_setting_field__after', array( $this, 'setting_field__debug' ), 10, 3 );

			// Add Translation Stats plugin stats widget debug.
			add_action( 'translation_stats_plugins_stats_widget__after', array( $this, 'plugin_stats_widget__debug' ), 10, 3 );
		}


		/**
		 * Add Translation Stats settings debug page tab and sections.
		 *
		 * @since 1.2.0
		 *
		 * @param array $settings_pages   Array of settings pages tabs and content sections.
		 * @return array                  Array of filtered settings pages tabs and content sections.
		 */
		public function add_settings_debug_page( $settings_pages ) {

			$debug_page = array(
				'debug' => array(
					'tab'      => array(
						'title' => esc_html__( 'Debug', 'translation-stats' ),
						'icon'  => 'dashicons dashicons-info',
					),
					'sections' => array(
						'debug',
					),
				),
			);

			return array_merge( $settings_pages, $debug_page );
		}


		/**
		 * Display debug formatted message with plugin setting info.
		 *
		 * @since 0.8.0
		 * @since 1.2.0   Renamed from tstats_debug_setting_field_info() to setting_field__debug().
		 *
		 * @param string $field_id        Setting ID.
		 * @param string $value           Setting Value.
		 * @param string $default_value   Setting Default.
		 *
		 * @return void
		 */
		public function setting_field__debug( $field_id, $value, $default_value ) {

			?>
			<div class="tstats-debug-block notice notice-alt inline notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: Setting ID. */
						esc_html__( 'ID: %s', 'translation-stats' ),
						'<code>' . esc_html( $field_id ) . '</code>'
					);
					?>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: Setting Value. */
						esc_html__( 'Value: %s', 'translation-stats' ),
						'<code>' . esc_html( $value ) . '</code>'
					);
					?>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: Setting Default. */
						esc_html__( 'Default: %s', 'translation-stats' ),
						'<code>' . esc_html( $default_value ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}


		/**
		 * Display debug formatted message with plugin translation project info.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_settings_plugin_widget__debug() to plugin_stats_widget__debug().
		 *
		 * @param string $project_slug                  Plugin Slug..
		 * @param string $plugin_on_wporg               Plugin exist on WP.org: True or false.
		 * @param string $plugin_translation_on_wporg   Plugin translation project exist on WP.org: True or false.
		 *
		 * @return void
		 */
		public function plugin_stats_widget__debug( $project_slug, $plugin_on_wporg, $plugin_translation_on_wporg ) {

			?>
			<div class="tstats-debug-block notice notice-alt inline notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: Plugin slug. */
						esc_html__( 'Slug: %s', 'translation-stats' ),
						'<code>' . esc_html( $project_slug ) . '</code>'
					);
					?>
				</p>
				<p>
					<?php
					if ( $plugin_on_wporg ) {
						esc_html_e( 'Plugin found on WordPress.org', 'translation-stats' );
					} else {
						esc_html_e( 'Plugin not found on WordPress.org', 'translation-stats' );
					}
					?>
				</p>
				<p>
					<?php
					if ( $plugin_translation_on_wporg ) {
						$api_url = Translations_API::translate_url( 'plugins', true ) . $project_slug;
						printf(
							/* translators: 1: Opening tag <a>. 2: Closing tag </a>. */
							esc_html__( 'Translation project found on %1$sWordPress.org%2$s', 'translation-stats' ),
							'<a href="' . esc_url( $api_url ) . '" target="_blank">',
							'</a>'
						);
					} else {
						esc_html_e( 'Translation project not found on WordPress.org', 'translation-stats' );
					}
					?>
				</p>
			</div>
			<?php
		}
	}
}
