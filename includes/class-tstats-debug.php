<?php
/**
 * Class file for registering Translation Stats Debug.
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

if ( ! class_exists( 'TStats_Debug' ) ) {

	/**
	 * Class TStats_Debug.
	 */
	class TStats_Debug {


		/**
		 * Transients.
		 *
		 * @var object
		 */
		protected $transients;


		/**
		 * Globals.
		 *
		 * @var object
		 */
		protected $tstats_globals;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Transients.
			$this->transients = new TStats_Transients();

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

			// Add Translation Stats settings field debug info.
			add_action( 'tstats_debug_setting_field_info', array( $this, 'tstats_debug_setting_field_info' ), 10, 3 );

			// Add Translation Stats settings debug section.
			add_action( 'tstats_settings_section__after', array( $this, 'tstats_settings_section__debug' ) );

			// Add Translation Stats settings debug tab.
			add_action( 'tstats_settings_tab__after', array( $this, 'tstats_settings_tab__debug' ) );

			// Add Translation Stats settings debug content.
			add_action( 'tstats_settings_content__after', array( $this, 'tstats_settings_content__debug' ) );

			// Add Translation Stats plugin widget debug.
			add_action( 'tstats_stats_plugin_widget_debug', array( $this, 'tstats_settings_plugin_widget__debug' ), 10, 3 );

		}


		/**
		 * Add Translation Stats settings debug tab.
		 *
		 * @since 0.9.0
		 *
		 * @return void
		 */
		public function tstats_settings_tab__debug() {
			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {
				?>
				<a class="nav-tab" href="#debug"><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Debug', 'translation-stats' ); ?></a>
				<?php
			}
		}


		/**
		 * Add Translation Stats settings debug content.
		 *
		 * @since 0.9.0
		 *
		 * @return void
		 */
		public function tstats_settings_content__debug() {
			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {
				?>
				<div id="tab-debug" class="tab-content hidden">
					<?php
					$section = 'tstats_settings_advanced_debug';
					do_settings_sections( $section );
					?>
				</div>
				<?php
			}
		}


		/**
		 * Register Settings Page sections Debug.
		 *
		 * @since 0.9.0
		 *
		 * @return void
		 */
		public function tstats_settings_section__debug() {

			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {

				add_settings_section(
					'tstats_settings_advanced_debug',                          // String for use in the 'id' attribute of tags.
					__( 'Debug', 'translation-stats' ),                        // Title of the section.
					array( $this, 'tstats_settings_advanced_debug_callback' ), // Function that fills the section with the desired content.
					'tstats_settings_advanced_debug'                           // The menu page on which to display this section. Should match $menu_slug.
				);

			}

		}


		/**
		 * Callback function for section "Debug".
		 *
		 * @since 0.8.0
		 *
		 * @return void
		 */
		public function tstats_settings_advanced_debug_callback() {

			?>
			<p class="description">
				<?php
				esc_html_e( 'List of settings and transients of Translation Stats.', 'translation-stats' );
				?>
			</p>
			<?php

			// Display plugin options and transients debug information.
			$this->tstats_debug_info();

		}


		/**
		 * Display debug formated message with plugin options.
		 *
		 * @since 0.8.0
		 *
		 * @return void
		 */
		public function tstats_debug_info() {
			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {
				?>
				<br>
				<div class="tstats-debug-block notice notice-alt inline notice-info">
					<?php
					// Show server info.
					$this->tstats_debug_info__server();
					// Show the site settings debug info.
					$this->tstats_debug_info__site();
					// Show the Translation Stats settings debug info.
					$this->tstats_debug_info__settings();
					// Show the site transients debug info.
					$this->tstats_debug_info__transients();
					?>
					<br>
				</div>
				<?php
			}
		}


		/**
		 * Show the server debug info.
		 *
		 * @since 0.8.0
		 *
		 * @return void
		 */
		public function tstats_debug_info__server() {
			?>
			<h3>
				<?php esc_html_e( 'Server', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: Version Number. */
					esc_html__( 'PHP Version: %s', 'translation-stats' ),
					'<code>' . esc_html( phpversion() ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'PHP Functions Check:', 'translation-stats' ); ?>
			</p>
			<code class="tstats-code-block">
				<?php
				// Test important functions.
				$test_functions = array(
					'array_column',
					'array_map',
				);
				if ( ! empty( $test_functions ) ) {
					foreach ( $test_functions as $test_function ) {
						$dashicon = function_exists( $test_function ) ? 'dashicons-yes' : 'dashicons-no';
						?>
						<span class="dashicons <?php echo esc_attr( $dashicon ); ?>"></span><?php echo esc_html( $test_function . '()' ); ?><br>
						<?php
					}
				} else {
					esc_html_e( 'No functions to test.', 'translation-stats' );
				}
				?>
			</code>
			<?php
		}


		/**
		 * Show the site settings debug info.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function tstats_debug_info__site() {
			?>
			<h3>
				<?php esc_html_e( 'Site', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: WordPress Locale code. */
					esc_html__( 'Site Locale: %s', 'translation-stats' ),
					'<code>' . esc_html( get_locale() ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: User Locale code. */
					esc_html__( 'User Locale: %s', 'translation-stats' ),
					'<code>' . esc_html( get_user_locale() ) . '</code>'
				);
				?>
			</p>
			<?php
		}


		/**
		 * Show the Translation Stats settings debug info.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function tstats_debug_info__settings() {
			// Get plugin settings.
			$tstats_options = get_option( TSTATS_WP_OPTION );
			?>
			<h3>
				<?php esc_html_e( 'Translation Stats', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: User Locale code. */
					esc_html__( 'Version: %s', 'translation-stats' ),
					'<code>' . esc_html( TSTATS_VERSION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: User Locale code. */
					esc_html__( 'Settings database version: %s', 'translation-stats' ),
					'<code>' . esc_html( TSTATS_SETTINGS_VERSION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: WordPress Locale code. */
					esc_html__( 'Translation Stats Locale: %s', 'translation-stats' ),
					'<code>' . esc_html( $tstats_options['settings']['translation_language'] ) . '</code>'
				);
				$tstats_locale = Translations_API::locale( $this->tstats_globals->tstats_translation_language() );
				?>
			</p>
			<div>
				<pre><code class="tstats-code-block"><?php echo esc_html( var_export( $tstats_locale, true ) ); // phpcs:ignore ?></code></pre>
			</div>
			<p>
				<?php
				printf(
					/* translators: %s: Page Name. */
					esc_html__( 'Settings Page: %s', 'translation-stats' ),
					'<code>' . esc_html( TSTATS_SETTINGS_PAGE ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: Option Name. */
					esc_html__( 'WordPress Option: %s', 'translation-stats' ),
					'<code>' . esc_html( TSTATS_WP_OPTION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Translation Stats settings:', 'translation-stats' ); ?>
			</p>
			<div>
				<?php
				if ( isset( $tstats_options['settings'] ) ) {
					?>
					<pre><code class="tstats-code-block"><?php echo esc_html( var_export( $tstats_options['settings'], true ) ); // phpcs:ignore ?></code></pre>
					<?php
				} else {
					?>
					<code><?php esc_html_e( 'No settings found.', 'translation-stats' ); ?></code>
					<?php
				}
				?>
			</div>
			<p>
				<?php esc_html_e( 'Installed plugins settings:', 'translation-stats' ); ?>
			</p>
			<div>
				<?php
				if ( isset( $tstats_options['plugins'] ) ) {
					?>
					<pre><code class="tstats-code-block"><?php echo esc_html( var_export( $tstats_options['plugins'], true ) ); // phpcs:ignore ?></code></pre>
					<?php
				} else {
					?>
					<code><?php esc_html_e( 'No settings found.', 'translation-stats' ); ?></code>
					<?php
				}
				?>
			</div>
			<?php
		}


		/**
		 * Show the site transients debug info.
		 *
		 * @since 0.8.0
		 *
		 * @return void
		 */
		public function tstats_debug_info__transients() {
			?>
			<h3>
				<?php esc_html_e( 'Transients', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: Prefix Name. */
					esc_html__( 'Transients Prefix: %s', 'translation-stats' ),
					'<code>' . esc_html( TSTATS_TRANSIENTS_PREFIX ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Transients List:', 'translation-stats' ); ?>
			</p>
			<div>
				<code class="tstats-code-block">
					<?php
					$transients = $this->transients->tstats_get_transients( TSTATS_TRANSIENTS_PREFIX );
					if ( ! empty( $transients ) ) {
						foreach ( $transients as $transient ) {
							echo esc_html( substr( $transient, strlen( '_transient_' ) ) );
							?>
							<br>
							<?php
						}
					} else {
						esc_html_e( 'No transients found.', 'translation-stats' );
					}
					?>
				</code>
			</div>
			<?php
		}


		/**
		 * Display debug formated message with plugin setting info.
		 *
		 * @since 0.8.0
		 *
		 * @param string $field_id  Setting ID.
		 * @param string $value     Setting Value.
		 * @param string $default   Setting Default.
		 *
		 * @return void
		 */
		public function tstats_debug_setting_field_info( $field_id, $value, $default ) {
			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {
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
							'<code>' . esc_html( $default ) . '</code>'
						);
						?>
					</p>
				</div>
				<?php
			}
		}


		/**
		 * Display debug formated message with plugin translation project info.
		 *
		 * @since 0.9.4
		 *
		 * @param string $project_slug                  Plugin Slug..
		 * @param string $plugin_on_wporg               Plugin exist on WP.org: True or false.
		 * @param string $plugin_translation_on_wporg   Plugin translation project exist on WP.org: True or false.
		 *
		 * @return void
		 */
		public function tstats_settings_plugin_widget__debug( $project_slug, $plugin_on_wporg, $plugin_translation_on_wporg ) {
			if ( defined( 'TSTATS_DEBUG' ) && TSTATS_DEBUG ) {
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
							$api_url = Translations_API::translations_api_url( 'plugins' ) . $project_slug;
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

}
