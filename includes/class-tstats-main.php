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

			// Load GlotPress locales data.
			include 'glotpress/locales.php';

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'tstats_register_plugin_styles' ) );

			// Add plugin translation stats column.
			add_filter( 'manage_plugins_columns', 'tstats_add_translation_stats_column' );

			/**
			 * Set the plugin translation stats column if user locale is not 'en_US'.
			 *
			 * @param array $columns   Columns array.
			 * @return array $columns  Columns array with added 'translation-stats'.
			 */
			function tstats_add_translation_stats_column( $columns ) {
				// Check if user locale is not 'en_US'.
				if ( get_user_locale() !== 'en_US' ) {
					$columns['translation-stats'] = _x( 'Translation Stats', 'Column label', 'translation-stats' );
				}
				return $columns;
			}

			// Show plugin translation stats content in column.
			add_action( 'manage_plugins_custom_column', array( $this, 'tstats_render_plugin_stats_column' ), 10, 3 );

		}


		/**
		 * Register and enqueue style sheet.
		 *
		 * @param string $hook  Hook.
		 */
		public function tstats_register_plugin_styles( $hook ) {
			// Loads plugin style sheets only in the plugins page.
			if ( 'plugins.php' !== $hook ) {
				return;
			};
			wp_register_style( 'translation-stats', plugins_url( '../css/admin.css', __FILE__ ), false, '0.6.4' );
			wp_enqueue_style( 'translation-stats' );
			// Add Dark Mode style sheet.
			// https://github.com/danieltj27/Dark-Mode/wiki/Help:-Plugin-Compatibility-Guide.
			add_action( 'doing_dark_mode', array( $this, 'tstats_register_plugin_styles_dark_mode' ) );
		}


		/**
		 * Register and enqueue Dark Mode style sheet.
		 */
		public function tstats_register_plugin_styles_dark_mode() {
			wp_register_style( 'translation-stats-dark-mode', plugins_url( '../css/admin-dark-mode.css', __FILE__ ), false, '0.6.4' );
			wp_enqueue_style( 'translation-stats-dark-mode' );
		}


		/**
		 * Check if plugin is on WordPress.org by checking if ID (from Plugin wp.org info) exists in 'response' or 'no_update' in 'update_plugins' transient.
		 *
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 * @return string              Returns 'true' if the plugin exists on WordPress.org.
		 */
		public function tstats_plugin_on_wporg( $plugin_file ) {
			$plugin_state = get_site_transient( 'update_plugins' );
			if ( isset( $plugin_state->response[ $plugin_file ]->id ) || isset( $plugin_state->no_update[ $plugin_file ]->id ) ) {
				return true;
			}
		}


		/**
		 * Get plugin metadata, if the plugin exists on WordPress.org.
		 *
		 * Example:
		 * $plugin_metadata = $this->plugin_data( $plugin_file, 'metadata' ) (e.g. 'slug').
		 *
		 * @param string $plugin_file       Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 * @param string $metadata          Metadata field ( e.g. 'slug' ).
		 * @return string $plugin_metadata  Returns metadata value from plugin.
		 */
		public function tstats_plugin_metadata( $plugin_file, $metadata ) {
			$plugin_state = get_site_transient( 'update_plugins' );
			// Check if plugin is on WordPress.org.
			if ( $this->tstats_plugin_on_wporg( $plugin_file ) ) {
				if ( isset( $plugin_state->response[ $plugin_file ]->$metadata ) ) {
					$plugin_metadata = $plugin_state->response[ $plugin_file ]->$metadata;
				}
				if ( isset( $plugin_state->no_update[ $plugin_file ]->$metadata ) ) {
					$plugin_metadata = $plugin_state->no_update[ $plugin_file ]->$metadata;
				}
				return $plugin_metadata;
			}
		}


		/**
		 * Get data from translate.WordPress.org API.
		 *
		 * @param string $url       URL to get the data from.
		 * @return string $api_get  Returns the response from translate.WordPress.org API URL.
		 */
		public function tstats_translate_api_get( $url ) {
			$api_get = wp_remote_get( 'https://translate.wordpress.org/api/projects/wp-plugins/' . $url );
			return $api_get;
		}


		/**
		 * Display formated notice message.
		 *
		 * Usage of notice types:
		 * notice-error – error message displayed with a red border.
		 * notice-warning – warning message displayed with a yellow border.
		 * notice-success – success message displayed with a green border.
		 * notice-info - info message displayed with a blue border.
		 *
		 * @param string $notice_message   Message to display.
		 * @param string $notice_type      WordPress core notice types ( 'error', 'warning', 'success' and 'info' ).
		 */
		public function tstats_notice_message( $notice_message, $notice_type ) {
			ob_start(); ?>
			<div class="notice notice-alt inline notice-<?php echo esc_attr( $notice_type ); ?>">
				<p class="aria-label"><?php echo wp_kses_post( $notice_message ); ?></p>
			</div>
			<?php
			$display_notice = ob_get_clean();
			echo wp_kses_post( $display_notice );
		}


		/**
		 * Show Plugin Translation Stats Content if the plugin is in WP.org and if Locale isn´t 'en_US'.
		 *
		 * @param string $column_name  Column Slug ( e.g. 'translation-stats' ).
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 */
		public function tstats_render_plugin_stats_column( $column_name, $plugin_file ) {

			// Add Translation Stats if plugin is on wordpress.org and if user Locale isn't 'en_US'.
			// Check if is in column 'translation-stats'.
			if ( 'translation-stats' === $column_name ) {

				// Check if user locale is not 'en_US'.
				if ( get_user_locale() !== 'en_US' ) {

					$project_slug = $this->tstats_plugin_metadata( $plugin_file, 'slug' );

					// Check if plugin is on WordPress.org.
					if ( ! $this->tstats_plugin_on_wporg( $plugin_file ) ) {
						$this->tstats_notice_message( esc_html__( 'Plugin not found on WordPress.org', 'translation-stats' ), 'error' ); // Todo: Add alternative GlotPress API.
					} else {
						// Check if translation project is on WordPress.org.
						if ( ! $this->tstats_plugin_project_on_translate_wporg( $project_slug ) ) {
							$this->tstats_notice_message( esc_html__( 'Translation project not found on WordPress.org', 'translation-stats' ), 'error' );
						} else {
							$this->tstats_render_plugin_stats( $project_slug );
						}
					}
				}
			}
		}


		/**
		 * Check if translation project exist without /subproject slug (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis).
		 *
		 * @param string $project_slug  Plugin Slug (e.g. 'plugin-slug').
		 * @return string               Returns 'true' if the translation project exist on WordPress.org.
		 */
		public function tstats_plugin_project_on_translate_wporg( $project_slug ) {
			// Check project transients.
			$on_wporg = get_transient( 'translation_stats_plugin_' . $project_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translate_api_get( $project_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( 'translation_stats_plugin_' . $project_slug, $on_wporg, MONTH_IN_SECONDS );
			}
			return $on_wporg;
		}


		/**
		 * Check if translation project exist without /subproject slug (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis).
		 *
		 * @param string $project_slug  Plugin Slug (e.g. 'plugin-slug').
		 * @return string               Returns 'true' if the translation project exist on WordPress.org.
		 */
		public function tstats_check_plugin_project_on_translate_wporg( $project_slug ) {
			// Check project transients.
			$on_wporg = get_transient( 'translation_stats_plugin_' . $project_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translate_api_get( $project_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( 'translation_stats_plugin_' . $project_slug, $on_wporg, MONTH_IN_SECONDS );
			}
			return $on_wporg;
		}


		/**
		 * Check if translation subproject exist (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis/stable).
		 *
		 * @param string $project_slug     Plugin Slug (e.g. 'plugin-slug').
		 * @param string $subproject_slug  Plugin Subproject Slug (e.g. 'dev', 'dev-readme', 'stable', 'stable-readme').
		 * @return string                  Returns 'true' if the translation subproject exist on WordPress.org.
		 */
		public function tstats_plugin_subproject_on_translate_wporg( $project_slug, $subproject_slug ) {
			// Check subproject transients.
			$on_wporg = get_transient( 'translation_stats_plugin_' . $project_slug . '_' . $subproject_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translate_api_get( $project_slug . '/' . $subproject_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( 'translation_stats_plugin_' . $project_slug . '_' . $subproject_slug, $on_wporg, MONTH_IN_SECONDS );
			}
			return $on_wporg;
		}



		/**
		 * Render Plugin Translation Stats for current locale.
		 *
		 * @param string $project_slug   Plugin Slug.
		 */
		public function tstats_render_plugin_stats( $project_slug ) {

			$locale  = get_user_locale();
			$variant = 'default'; // Todo: Add support for non-default variant.
			$locale  = GP_Locales::by_field( 'wp_locale', $locale ); // Depends of GlotPress library.
			ob_start();
			?>
			<div class="translation-stats-title">
				<?php
				$url         = 'https://translate.wordpress.org/locale/' . $locale->slug . '/' . $variant . '/wp-plugins/' . $project_slug;
				$locale_link = '<a href="' . esc_url( $url ) . '" _target="blank">' . $locale->native_name . '</a>';
				/* translators: %s Language native name. */
				echo sprintf( wp_kses_post( __( 'Translation for %s', 'translation-stats' ) ), wp_kses_post( $locale_link ) );
				?>
			</div>
			<div class="translation-stats-content notice-warning notice-alt">
				<?php
				$dev           = $this->tstats_render_stats_bar( $locale, $project_slug, /* translators: translate.wp.org subproject name, please don't translate! */ esc_html_x( 'Development', 'Subproject name', 'translation-stats' ), 'dev' );
				$dev_readme    = $this->tstats_render_stats_bar( $locale, $project_slug, /* translators: translate.wp.org subproject name, please don't translate! */ esc_html_x( 'Development Readme', 'Subproject name', 'translation-stats' ), 'dev-readme' );
				$stable        = $this->tstats_render_stats_bar( $locale, $project_slug, /* translators: translate.wp.org subproject name, please don't translate! */ esc_html_x( 'Stable', 'Subproject name', 'translation-stats' ), 'stable' );
				$stable_readme = $this->tstats_render_stats_bar( $locale, $project_slug, /* translators: translate.wp.org subproject name, please don't translate! */ esc_html_x( 'Stable Readme', 'Subproject name', 'translation-stats' ), 'stable-readme' );

				echo wp_kses_post( $dev['stats'] );
				echo wp_kses_post( $dev_readme['stats'] );
				echo wp_kses_post( $stable['stats'] );
				echo wp_kses_post( $stable_readme['stats'] );
				?>
			</div>
			<?php

			$i18n_errors = $dev['error'] + $dev_readme['error'] + $stable['error'] + $stable_readme['error'];
			if ( ! empty( $i18n_errors ) ) {
				$this->tstats_notice_message(
					sprintf(
						/* translators: %1$s Opening link tag <a href="[link]">. %2$s Closing link tag </a>. */
						wp_kses_post( __( 'This plugin is not %1$sproperly prepared for localization%2$s.', 'translation-stats' ) ),
						'<a href="https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/" target="_blank">',
						'</a>'
					),
					'warning'
				);
				$this->tstats_notice_message(
					sprintf(
						( '%1$s%2$s%3$s' ),
						'<a href="https://make.wordpress.org/meta/handbook/documentation/translations/#this-plugin-is-not-properly-prepared-for-localization-%e2%80%93-help" target="_blank">',
						esc_html__( 'View detailed logs on Slack', 'translation-stats' ),
						'</a>'
					),
					'warning'
				);
				$this->tstats_notice_message(
					sprintf(
						/* translators: %1$s Opening link tag <a href="[link]">. %2$s Closing link tag </a>. */
						wp_kses_post( __( 'If you would like to translate this plugin, %1$splease contact the author%2$s.', 'translation-stats' ) ),
						'<a href="https://wordpress.org/support/plugin/' . esc_html( $project_slug ) . '" target="_blank">',
						'</a>'
					),
					'warning'
				);
			}

			$plugin_stats = ob_get_clean();
			echo wp_kses_post( $plugin_stats );
		}


		/**
		 * Render plugin subproject stat bar.
		 *
		 * @param string $locale           Locale (wp_locale), e.g. 'pt_PT' or get_user_locale().
		 * @param string $project_slug     Plugin Slug.
		 * @param string $subproject       Translation subproject (' Dev', 'Dev Readme', 'Stable', 'Stable Readme' ).
		 * @param string $subproject_slug  Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 * @return string $stats_bar       Subproject stats bar.
		 */
		public function tstats_render_stats_bar( $locale, $project_slug, $subproject, $subproject_slug ) {

			$variant = 'default'; // Todo: Add support for non-default variant.
			$url     = 'https://translate.wordpress.org/projects/wp-plugins/' . $project_slug . '/' . $subproject_slug . '/' . $locale->slug . '/' . $variant;

			// Get plugin subproject translation stats.
			$translation_stats = $this->tstats_plugin_subproject_stats( $locale->slug, $variant, $project_slug, $subproject_slug );

			// If translation stats are not an object, project not found.
			if ( ! is_object( $translation_stats ) ) {

				$i18n_error = true;

			} else { // If translation stats are an object, get the percent translated property.

				/*
				 * Get the 'percent_translated' property from subproject translation stats.
				 *
				 * Example of allowed properties:
				 * [id] => 416518
				 * [name] => Portuguese (Portugal)
				 * [slug] => default | ao90 | informal
				 * [project_id] => 3333
				 * [locale] => pt
				 * [current_count] => 136
				 * [untranslated_count] => 0
				 * [waiting_count] => 0
				 * [fuzzy_count] => 0
				 * [percent_translated] => 100
				 * [wp_locale] => pt_PT
				 * [last_modified] => 2018-10-11 10:05:30
				 */
				$percent_translated = $translation_stats->percent_translated;
				$i18n_error         = false;
			}

			ob_start();
			?>

			<div class="content__subproject <?php echo esc_html( $subproject_slug ); ?>">
				<a class="
				<?php
				if ( ! $i18n_error ) {
					echo 'enabled';
				} else {
					echo 'disabled';
				};
				?>
				" target="_blank"
				<?php
				if ( ! $i18n_error ) {
					echo 'href="' . esc_url( $url ) . '"';
				};
				?>
				>
				<?php
				if ( ! $i18n_error ) {
					?>
					<div class="<?php echo esc_html( 'percent' . 10 * floor( $percent_translated / 10 ) . ' ' . $subproject_slug ); ?>" style="width: <?php echo esc_html( $percent_translated ); ?>%;">
						<div class="subproject-bar">
							<span class="subproject-bar__percentage"><?php echo esc_html( $percent_translated ); ?>%</span><span class="subproject-bar__name"><?php echo esc_html( $subproject ); ?></span>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="subproject-bar"><?php echo wp_kses_post( sprintf( /* translators: %1$s Name of subproject. %2$s Error message. */ __( '%1$s: %2$s', 'translation-stats' ), $subproject, '<strong>' . __( 'Not found', 'translation-stats' ) . '</strong>' ) ); ?></div>
					<?php
				}
				?>
				</a>
			</div>

			<?php
			$stats_bar = ob_get_clean();
			$stats_bar = array(
				'stats' => $stats_bar,
				'error' => $i18n_error,
			);
			return $stats_bar;
		}


		/**
		 * Render plugin subproject stat bar.
		 *
		 * @param string $locale              Locale (wp_locale), e.g. 'pt_PT' or get_user_locale().
		 * @param string $variant             Variant ( e.g. 'default', 'formal' ).
		 * @param string $project_slug        Plugin Slug.
		 * @param string $subproject_slug     Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 * @return string $translation_stats  Plugin stats.
		 */
		public function tstats_plugin_subproject_stats( $locale, $variant, $project_slug, $subproject_slug ) {

			// Check subproject transients.
			$translation_stats = get_transient( 'translation_stats_plugin_' . $project_slug . '_' . $subproject_slug . '_' . $locale );

			if ( false === $translation_stats ) {

				$json = $this->tstats_translate_api_get( $project_slug . '/' . $subproject_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {

					// Subproject not found (Error 404) - Plugin is not properly prepared for localization.
					$translation_stats = false;

				} else {

					$body = json_decode( $json['body'] );
					if ( empty( $body->translation_sets ) ) {

						// No translation sets found.
						$translation_stats = false;

					} else {

						foreach ( $body->translation_sets as $translation_set ) {

							if ( $translation_set->locale === $locale && $translation_set->slug === $variant ) {
								// Set transient value.
								$translation_stats = $translation_set;
								continue;
							}
						}
					}
				}

				set_transient( 'translation_stats_plugin_' . $project_slug . '_' . $subproject_slug . '_' . $locale, $translation_stats, DAY_IN_SECONDS );
			}

			return $translation_stats;
		}

	}

}
