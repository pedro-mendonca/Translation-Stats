<?php
/**
 * Class file for registering Translation Stats settings debug section.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Debug' ) ) {

	/**
	 * Class Settings_Section_Debug.
	 */
	class Settings_Section_Debug extends Settings_Section {


		/**
		 * Transients.
		 *
		 * @var object
		 */
		protected $transients;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Load parent construct.
			parent::__construct();

			// Instantiate Translation Stats Transients.
			$this->transients = new Transients();
		}


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'debug', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'Debug', 'translation-stats' ),
				'description' => __( 'List of settings and transients of Translation Stats.', 'translation-stats' ),
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'debug',
			);
		}


		/**
		 * Display debug formatted message with plugin options.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function render_custom_section() {

			?>
			<br>
			<div class="tstats-debug-block notice notice-alt inline notice-info">
				<?php
				// Show server info.
				$this->debug_info__server();
				// Show the site settings debug info.
				$this->debug_info__site();
				// Show the Translation Stats settings debug info.
				$this->debug_info__settings();
				// Show the site transients debug info.
				$this->debug_info__transients();
				?>
				<br>
			</div>
			<?php
		}


		/**
		 * Show the server debug info.
		 *
		 * @since 0.8.0
		 * @since 1.2.0   Renamed from tstats_debug_info__server() to debug_info__server().
		 *
		 * @return void
		 */
		public function debug_info__server() {

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
			<?php
		}


		/**
		 * Show the site settings debug info.
		 *
		 * @since 1.0.0
		 * @since 1.2.0   Renamed from tstats_debug_info__site() to debug_info__site().
		 *
		 * @return void
		 */
		public function debug_info__site() {

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
		 * @since 1.2.0   Renamed from tstats_debug_info__settings() to debug_info__settings().
		 *
		 * @return void
		 */
		public function debug_info__settings() {

			// Get plugin settings.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );
			?>
			<h3>
				<?php esc_html_e( 'Translation Stats', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: Version of the plugin. */
					esc_html__( 'Version: %s', 'translation-stats' ),
					'<code>' . esc_html( TRANSLATION_STATS_VERSION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: Version of settings database. */
					esc_html__( 'Settings database version: %s', 'translation-stats' ),
					'<code>' . esc_html( TRANSLATION_STATS_SETTINGS_VERSION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: Page Name. */
					esc_html__( 'Settings Page: %s', 'translation-stats' ),
					'<code>' . esc_html( TRANSLATION_STATS_SETTINGS_PAGE ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: Option Name. */
					esc_html__( 'WordPress Option: %s', 'translation-stats' ),
					'<code>' . esc_html( TRANSLATION_STATS_WP_OPTION ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Translation Stats settings:', 'translation-stats' ); ?>
			</p>
			<div>
				<?php
				if ( isset( $options['settings'] ) ) {
					?>
					<pre><code class="tstats-code-block"><?php echo esc_html( wp_json_encode( $options['settings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></code></pre>
					<p>
						<?php
						printf(
							/* translators: %s: WordPress Locale code. */
							esc_html__( 'Translation Stats Locale: %s', 'translation-stats' ),
							'<code>' . esc_html( $options['settings']['translation_language'] ) . '</code>'
						);
						$translationstats_locale = Translations_API::locale( Utils::translation_language() );
						?>
					</p>
					<div>
						<pre><code class="tstats-code-block"><?php echo esc_html( wp_json_encode( $translationstats_locale, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></code></pre>
					</div>
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
				if ( isset( $options['plugins'] ) ) {
					?>
					<pre><code class="tstats-code-block"><?php echo esc_html( wp_json_encode( $options['plugins'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></code></pre>
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
		 * @since 1.2.0   Renamed from tstats_debug_info__transients() to debug_info__transients().
		 *
		 * @return void
		 */
		public function debug_info__transients() {

			$transients = $this->transients->get_transients( TRANSLATION_STATS_TRANSIENTS_PREFIX );

			?>
			<h3>
				<?php esc_html_e( 'Transients', 'translation-stats' ); ?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %s: Prefix Name. */
					esc_html__( 'Transients Prefix: %s', 'translation-stats' ),
					'<code>' . esc_html( TRANSLATION_STATS_TRANSIENTS_PREFIX ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Transients List:', 'translation-stats' ); ?>
			</p>
			<div>
				<?php

				if ( ! empty( $transients ) ) {
					$response = array();
					foreach ( $transients as $transient ) {
						$response[] = esc_html( substr( $transient, strlen( '_transient_' ) ) );
					}
					?>
					<pre><code class="tstats-code-block"><?php echo esc_html( wp_json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></code></pre>
					<?php
				} else {
					?>
					<code class="tstats-code-block"><?php esc_html_e( 'No transients found.', 'translation-stats' ); ?></code>
					<?php
				}

				?>
			</div>
			<?php
		}
	}
}
