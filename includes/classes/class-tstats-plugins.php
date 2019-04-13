<?php
/**
 * Class file for the Translation Stats Plugins.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Plugins' ) ) {

	/**
	 * Class TStats_Plugins.
	 */
	class TStats_Plugins {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

			// Instantiate Translation Stats Notices.
			$this->tstats_notices = new TStats_Notices();

			// Instantiate Translation Stats Translations API.
			$this->tstats_translations_api = new TStats_Translations_API();

			// Add plugin translation stats column.
			add_filter( 'manage_plugins_columns', array( $this, 'tstats_add_translation_stats_column' ) );

			// Show plugin translation stats content in column.
			add_action( 'manage_plugins_custom_column', array( $this, 'tstats_render_plugin_stats_column' ), 10, 3 );

		}


		/**
		 * Set the plugin translation stats column if user locale is not 'en_US'.
		 *
		 * @since 0.8.0
		 *
		 * @param array $columns   Columns array.
		 * @return array $columns  Columns array with added 'translation-stats'.
		 */
		public function tstats_add_translation_stats_column( $columns ) {
			$tstats_language = $this->tstats_globals->tstats_translation_language();
			// Check if user locale is not 'en_US'.
			if ( 'en_US' !== $tstats_language ) {
				$columns['translation-stats'] = _x( 'Translation Stats', 'Column label', 'translation-stats' );
			}
			return $columns;
		}


		/**
		 * Show Plugin Translation Stats Content if the plugin is in WP.org and if Locale isn´t 'en_US'.
		 *
		 * @since 0.8.0
		 *
		 * @param string $column_name  Column Slug ( e.g. 'translation-stats' ).
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 */
		public function tstats_render_plugin_stats_column( $column_name, $plugin_file ) {

			// Add Translation Stats if plugin is on wordpress.org and if user Locale isn't 'en_US'.
			// Check if is in column 'translation-stats'.
			if ( 'translation-stats' === $column_name ) {

				$tstats_language = $this->tstats_globals->tstats_translation_language();
				// Check if user locale is not 'en_US'.
				if ( 'en_US' !== $tstats_language ) {

					$project_slug = $this->tstats_translations_api->tstats_plugin_metadata( $plugin_file, 'slug' );
					$options      = get_option( TSTATS_WP_OPTION );

					// Show Stats only if plugin is enabled in plugin settings.
					if ( empty( $options[ $project_slug ]['enabled'] ) ) {
						return;
					}

					// Check if plugin is on WordPress.org.
					if ( ! $this->tstats_translations_api->tstats_plugin_on_wporg( $plugin_file ) ) {
						$this->tstats_notices->tstats_notice_message( esc_html__( 'Plugin not found on WordPress.org', 'translation-stats' ), 'error' ); // Todo: Add alternative GlotPress API.
					} else {
						// Check if translation project is on WordPress.org.
						if ( ! $this->tstats_translations_api->tstats_plugin_project_on_translate_wporg( $project_slug ) ) {
							$this->tstats_notices->tstats_notice_message( esc_html__( 'Translation project not found on WordPress.org', 'translation-stats' ), 'error' );
						} else {
							$this->tstats_render_plugin_stats( $project_slug );
						}
					}
				}
			}
		}


		/**
		 * Render Plugin Translation Stats for current locale.
		 *
		 * @since 0.8.0
		 *
		 * @param string $project_slug   Plugin Slug.
		 */
		public function tstats_render_plugin_stats( $project_slug ) {

			$variant         = 'default'; // Todo: Add support for non-default variant.
			$tstats_language = $this->tstats_globals->tstats_translation_language();
			$locale          = $this->tstats_translations_api->tstats_locale( $tstats_language );
			ob_start();
			?>
			<div class="translation-stats-title">
				<?php
				$url         = 'https://translate.wordpress.org/locale/' . $locale['slug'] . '/' . $variant . '/wp-plugins/' . $project_slug;
				$locale_link = '<a href="' . esc_url( $url ) . '" target="_blank">' . $locale['native_name'] . '</a>';
				/* translators: %s Language native name. */
				echo sprintf( wp_kses_post( __( 'Translation for %s', 'translation-stats' ) ), wp_kses_post( $locale_link ) );
				?>
			</div>
			<div class="translation-stats-content notice-warning notice-alt">
				<?php

				$subprojects = $this->tstats_translations_api->tstats_plugin_subprojects();
				$i18n_errors = 0;
				foreach ( $subprojects as $subproject ) {
					$subproject = $this->tstats_render_stats_bar( $locale, $project_slug, $subproject['name'], $subproject['slug'] );
					echo wp_kses( $subproject['stats'], $this->tstats_allowed_html() );
					$i18n_errors = $i18n_errors + $subproject['error'];
				}
				?>
			</div>
			<?php

			if ( ! empty( $i18n_errors ) ) {
				$this->tstats_notices->tstats_notice_message(
					sprintf(
						/* translators: %1$s Opening link tag <a href="[link]">. %2$s Closing link tag </a>. */
						wp_kses_post( __( 'This plugin is not %1$sproperly prepared for localization%2$s.', 'translation-stats' ) ),
						'<a href="https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/" target="_blank">',
						'</a>'
					),
					'warning'
				);
				$this->tstats_notices->tstats_notice_message(
					sprintf(
						( '%1$s%2$s%3$s' ),
						'<a href="https://make.wordpress.org/meta/handbook/documentation/translations/#this-plugin-is-not-properly-prepared-for-localization-%e2%80%93-help" target="_blank">',
						esc_html__( 'View detailed logs on Slack', 'translation-stats' ),
						'</a>'
					),
					'warning'
				);
				$this->tstats_notices->tstats_notice_message(
					sprintf(
						/* translators: %1$s Opening link tag <a href="[link]">. %2$s Closing link tag </a>. */
						wp_kses_post( __( 'If you would like to translate this plugin, %1$splease contact the author%2$s.', 'translation-stats' ) ),
						'<a href="https://wordpress.org/support/plugin/' . esc_attr( $project_slug ) . '" target="_blank">',
						'</a>'
					),
					'warning'
				);
			}

			$plugin_stats = ob_get_clean();
			echo wp_kses( $plugin_stats, $this->tstats_allowed_html() );
		}


		/**
		 * Render plugin subproject stat bar.
		 *
		 * @since 0.8.0
		 *
		 * @param string $locale           Locale (wp_locale), e.g. 'pt_PT' or $this->tstats_globals->tstats_translation_language().
		 * @param string $project_slug     Plugin Slug.
		 * @param string $subproject       Translation subproject (' Dev', 'Dev Readme', 'Stable', 'Stable Readme' ).
		 * @param string $subproject_slug  Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 * @return string $stats_bar       Subproject stats bar.
		 */
		public function tstats_render_stats_bar( $locale, $project_slug, $subproject, $subproject_slug ) {

			$options = get_option( TSTATS_WP_OPTION );
			// Show bar only if subproject is enabled in plugin settings.
			if ( empty( $options[ $project_slug ][ $subproject_slug ] ) ) {
				return;
			}

			$variant = 'default'; // Todo: Add support for non-default variant.
			$url     = 'https://translate.wordpress.org/projects/wp-plugins/' . $project_slug . '/' . $subproject_slug . '/' . $locale['slug'] . '/' . $variant;

			// Get plugin subproject translation stats.
			$translation_stats = $this->tstats_plugin_subproject_stats( $locale['slug'], $variant, $project_slug, $subproject_slug );

			// If translation stats are not an object, project not found.
			if ( ! is_object( $translation_stats ) ) {
				$i18n_error = true;
			} else {
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

				// If translation stats are an object, get the percent translated property.
				$percent_translated = $translation_stats->percent_translated;
				$i18n_error         = false;
			}

			$class = ! $i18n_error ? 'enabled' : 'disabled';
			$href  = ! $i18n_error ? 'href="' . esc_url( $url ) . '"' : '';
			ob_start();
			?>
			<div class="content__subproject <?php echo esc_attr( $subproject_slug ); ?>">
				<a class="<?php echo esc_attr( $class ); ?>" target="_blank" <?php echo wp_kses_post( $href ); ?>>
				<?php
				if ( ! $i18n_error ) {
					?>
					<style>
						tr[data-slug="<?php echo esc_attr( $project_slug ); ?>"] div.subproject.<?php echo esc_attr( $subproject_slug ); ?> {
							width: <?php echo esc_attr( $percent_translated ); ?>%;
						}
					</style>
					<div class="subproject <?php echo esc_attr( 'percent' . 10 * floor( $percent_translated / 10 ) . ' ' . $subproject_slug ); ?>">
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
		 * @since 0.8.0
		 *
		 * @param string $locale              Locale (wp_locale), e.g. 'pt_PT' or $this->tstats_globals->tstats_translation_language().
		 * @param string $variant             Variant ( e.g. 'default', 'formal' ).
		 * @param string $project_slug        Plugin Slug.
		 * @param string $subproject_slug     Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 * @return string $translation_stats  Plugin stats.
		 */
		public function tstats_plugin_subproject_stats( $locale, $variant, $project_slug, $subproject_slug ) {

			// Check subproject transients.
			$translation_stats = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug . '_' . $locale );

			if ( false === $translation_stats ) {

				$json = $this->tstats_translations_api->tstats_translations_api_get_plugin( $project_slug . '/' . $subproject_slug );
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

				set_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug . '_' . $locale, $translation_stats, get_option( TSTATS_WP_OPTION )['transients_expiration'] );
			}

			return $translation_stats;
		}


		/**
		 * Returns array of allowed HTML elements for use in wp_kses().
		 *
		 * @since 0.8.5
		 *
		 * @return array  Array of allowed HTML elements.
		 */
		public function tstats_allowed_html() {
			$allowed_html = array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'class'  => array(),
					'data'   => array(),
					'rel'    => array(),
					'target' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'ul'     => array(
					'class' => array(),
				),
				'ol'     => array(
					'class' => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'strong' => array(),
				'div'    => array(
					'class' => array(),
					'data'  => array(),
					'style' => array(),
				),
				'span'   => array(
					'class' => array(),
					'style' => array(),
				),
				'img'    => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'select' => array(
					'id'    => array(),
					'class' => array(),
					'name'  => array(),
				),
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
				'style'  => array(),
			);

			return $allowed_html;
		}

	}

}

new TStats_Plugins();
