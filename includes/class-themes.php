<?php
/**
 * Class file for the Translation Stats themes.
 *
 * @package Translation_Stats
 *
 * @since 1.5.0
 */

namespace Translation_Stats;

use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Themes' ) ) {

	/**
	 * Class Themes.
	 *
	 * Renders the themes translation stats.
	 */
	class Themes {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add the translation stats to the themes data of the Themes screen.
			add_filter( 'wp_prepare_themes_for_js', array( $this, 'add_themes_stats' ) );
		}


		/**
		 * Add the translation stats to the enabled themes data of the Themes screen, for the theme card and details.
		 *
		 * @since 1.5.0
		 *
		 * @param array $prepared_themes   Themes data, by theme slug.
		 *
		 * @return array   Themes data, with the 'translationStats' of the enabled themes.
		 */
		public function add_themes_stats( $prepared_themes ) {

			$locale = Translations_API::locale( Utils::translation_language() );

			// Check if the Locale exists and isn't 'en_US'.
			if ( is_null( $locale ) || 'en_US' === $locale->wp_locale ) {
				return $prepared_themes;
			}

			$options = Utils::get_options();

			foreach ( array_keys( $prepared_themes ) as $theme_slug ) {

				$theme_slug = (string) $theme_slug;

				if ( empty( $options['themes'][ $theme_slug ]['enabled'] ) || ! Translations_API::theme_on_wporg( $theme_slug ) ) {
					continue;
				}

				$prepared_themes[ $theme_slug ]['translationStats'] = $this->theme_stats_data( $theme_slug, $locale );
			}

			return $prepared_themes;
		}


		/**
		 * Get the translation stats data of a theme, for the theme card badge and the widget in the theme details:
		 * the widget title, the cached stats or a placeholder, the status notice, the language and the percent translated.
		 *
		 * @since 1.5.0
		 *
		 * @param string $theme_slug   Theme slug.
		 * @param Locale $locale       Locale object.
		 *
		 * @return array{title: string, stats: string, notice: string, language: string, cached: bool, percent: int|null}   Theme translation stats data.
		 */
		public function theme_stats_data( $theme_slug, $locale ) {

			$project = new Theme_Project( $theme_slug );
			$cached  = $project->stats_cached( $locale );

			ob_start();
			$this->theme_widget_title( $theme_slug, $locale );
			Stats_Widget::render_update_button();
			$title = (string) ob_get_clean();

			ob_start();
			if ( $cached ) {
				$this->theme_widget_content_stats( $theme_slug, $locale, false );
			} else {
				Stats_Widget::render_placeholder( $project->get_subprojects() );
			}
			$stats = (string) ob_get_clean();

			// The status notice is hidden, to show errors. The stats without cache are loaded with the REST API.
			ob_start();
			Stats_Widget::render_status_notice( $cached );
			$notice = (string) ob_get_clean();

			return array(
				'title'    => $title,
				'stats'    => $stats,
				'notice'   => $notice,
				'language' => $locale->native_name,
				'cached'   => $cached,
				'percent'  => $cached ? $this->percent_translated( $theme_slug, $locale ) : null,
			);
		}


		/**
		 * Render the theme widget title, with the link to the theme translation of the Locale on translate.WordPress.org, as the plugins.
		 *
		 * @since 1.5.0
		 *
		 * @param string $theme_slug   Theme slug.
		 * @param Locale $locale       Locale object.
		 *
		 * @return void
		 */
		public function theme_widget_title( $theme_slug, $locale ) {

			$url = 'https://translate.wordpress.org/locale/' . $locale->locale_slug . '/wp-themes/' . $theme_slug;

			printf(
				wp_kses_post(
					/* translators: %s: Language native name. */
					__( 'Translation for %s', 'translation-stats' )
				),
				'<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $locale->native_name ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL and name.
			);
		}


		/**
		 * Get the Translation Stats themes settings URL, if the user can manage the settings.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Themes settings URL, or empty if the user can't manage the settings.
		 */
		public static function settings_url() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return '';
			}

			return add_query_arg( 'page', TRANSLATION_STATS_SETTINGS_PAGE . '#themes', admin_url( 'options-general.php' ) );
		}


		/**
		 * Get the cached percent translated of a theme.
		 *
		 * @since 1.5.0
		 *
		 * @param string $theme_slug   Theme slug.
		 * @param Locale $locale       Locale object.
		 *
		 * @return int|null   Percent translated, 0 if the theme has no translation, or null if the stats aren't cached.
		 */
		public function percent_translated( $theme_slug, $locale ) {

			$project = new Theme_Project( $theme_slug );

			if ( ! $project->stats_cached( $locale ) ) {
				return null;
			}

			$stats = $project->subproject_stats( $locale, '', false );

			return is_object( $stats ) && isset( $stats->percent_translated ) && is_numeric( $stats->percent_translated ) ? (int) $stats->percent_translated : 0;
		}


		/**
		 * Load the theme widget content stats, with the stats bar of the theme project.
		 *
		 * @since 1.5.0
		 *
		 * @param string $theme_slug     Theme slug.
		 * @param Locale $locale         Locale object.
		 * @param bool   $force_update   True: Force get new stats. False: Use transients.
		 *
		 * @return true|WP_Error   True if rendered, or WP_Error if translate.WordPress.org is unavailable, without rendering.
		 */
		public function theme_widget_content_stats( $theme_slug, $locale, $force_update ) {

			$project = new Theme_Project( $theme_slug );

			$project_stats = $project->stats( $locale, $force_update );

			if ( is_wp_error( $project_stats ) ) {
				return $project_stats;
			}

			?>
			<div class="translation-stats-content-stats widget-inside" data-slug="<?php echo esc_attr( $theme_slug ); ?>">
				<?php
				foreach ( $project->get_subprojects() as $subproject ) {
					Stats_Bar::render( $project, $project_stats[ $subproject['slug'] ], $locale, $subproject['name'], $subproject['slug'], 'div.translation-stats-content-stats' );
				}
				?>
			</div>
			<?php

			return true;
		}
	}
}
