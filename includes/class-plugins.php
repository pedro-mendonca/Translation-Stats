<?php
/**
 * Class file for the Translation Stats Plugins.
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

if ( ! class_exists( __NAMESPACE__ . '\Plugins' ) ) {

	/**
	 * Class Plugins.
	 */
	class Plugins {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add plugin translation stats column.
			add_filter( 'manage_plugins_columns', array( $this, 'add_translation_stats_column' ) );

			// Show plugin translation stats content in column.
			add_action( 'manage_plugins_custom_column', array( $this, 'render_plugin_stats_column' ), 10, 2 );

			// Add Translation Stats plugin widget title.
			add_action( 'translation_stats_plugin_widget_title', array( $this, 'plugin_widget_title' ), 10, 2 );

			// Add Translation Stats plugin widget title actions.
			add_action( 'translation_stats_plugin_widget_title__actions', array( $this, 'plugin_update_button' ) );

			// Add Translation Stats plugin widget content.
			add_action( 'translation_stats_plugin_widget_content', array( $this, 'plugin_widget_content' ) );

			// Load plugin subprojects stats.
			add_action( 'wp_ajax_translation_stats_plugin_widget_content_load', array( $this, 'plugin_widget_content_load' ) );

			// Filter plugins list to show only Translation Stats enabled plugins.
			add_action( 'pre_current_active_plugins', array( $this, 'plugins_filter_by_translation_stats' ) );

			// Add status link to Translation Stats enabled plugins View.
			add_filter( is_multisite() ? 'views_plugins-network' : 'views_plugins', array( $this, 'plugins_status_link' ) );
		}


		/**
		 * Set the plugin translation stats column if user locale is not 'en_US'.
		 *
		 * @since 0.8.0
		 * @since 1.2.0   Renamed from tstats_add_translation_stats_column() to add_translation_stats_column().
		 *
		 * @param array $columns   Columns array.
		 *
		 * @return array $columns  Columns array with added 'translation-stats'.
		 */
		public function add_translation_stats_column( $columns ) {

			// Get the Translation Stats configured language.
			$translationstats_language = Utils::translation_language();

			$settings_link = sprintf(
				'<a href="%s" class="tstats-edit-settings-button" aria-label="%s"><span class="dashicons dashicons-edit"></span></a>',
				esc_url( add_query_arg( 'page', 'translation-stats#plugins', admin_url( 'options-general.php' ) ) ),
				esc_html__( 'Edit plugins settings', 'translation-stats' )
			);

			// Check if user locale is not 'en_US'.
			if ( 'en_US' !== $translationstats_language ) {
				$columns['translation-stats'] = _x( 'Translation Stats', 'Column label', 'translation-stats' ) . ' ' . $settings_link;
			}

			return $columns;
		}


		/**
		 * Show Plugin Translation Stats Content if the plugin is in WP.org and if Locale isn´t 'en_US'.
		 *
		 * @since 0.8.0
		 * @since 1.2.0   Renamed from tstats_render_plugin_stats_column() to render_plugin_stats_column().
		 *
		 * @param string $column_name  Column Slug ( e.g. 'translation-stats' ).
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 *
		 * @return void
		 */
		public function render_plugin_stats_column( $column_name, $plugin_file ) {

			// Add Translation Stats if plugin is on wordpress.org and if user Locale isn't 'en_US'.
			// Check if is in column 'translation-stats'.
			if ( 'translation-stats' === $column_name ) {

				// Get the Translation Stats configured language.
				$translationstats_language = Utils::translation_language();

				// Check if user locale is not 'en_US'.
				if ( 'en_US' !== $translationstats_language ) {

					$project_slug = Translations_API::plugin_metadata( $plugin_file, 'slug' );
					$options      = get_option( TRANSLATION_STATS_WP_OPTION );

					// Show Stats only if plugin is enabled in plugin settings.
					if ( empty( $options['plugins'][ $project_slug ]['enabled'] ) ) {
						return;
					}

					$plugin_on_wporg             = Translations_API::plugin_on_wporg( $plugin_file );
					$plugin_translation_on_wporg = Translations_API::plugin_project_on_translate_wporg( $project_slug );

					// Check if plugin is on WordPress.org.
					if ( ! $plugin_on_wporg ) {

						$admin_notice = array(
							'type'       => 'error',
							'notice-alt' => true,
							'message'    => esc_html__( 'Plugin not found on WordPress.org', 'translation-stats' ),
						);
						Admin_Notice::message( $admin_notice ); // TODO: Add alternative GlotPress API.

					} elseif ( ! $plugin_translation_on_wporg ) { // Check if translation project is on WordPress.org.

						$admin_notice = array(
							'type'       => 'error',
							'notice-alt' => true,
							'message'    => esc_html__( 'Translation project not found on WordPress.org', 'translation-stats' ),
						);
						Admin_Notice::message( $admin_notice );

					} else {

						$this->render_plugin_stats( $project_slug );

					}

					// Add Stats widget action for debugging.
					do_action( 'translation_stats_plugins_stats_widget__after', $project_slug, $plugin_on_wporg, $plugin_translation_on_wporg );
				}
			}
		}


		/**
		 * Render Plugin Translation Stats for current locale.
		 *
		 * @since 0.8.0
		 * @since 1.2.0   Renamed from tstats_render_plugin_stats() to render_plugin_stats().
		 *
		 * @param string $project_slug   Plugin Slug.
		 *
		 * @return void
		 */
		public function render_plugin_stats( $project_slug ) {

			// Get Translation Stats Locale data.
			$locale = Translations_API::locale( Utils::translation_language() );

			// Add before Translation Stats plugin widget title.
			do_action( 'translation_stats_plugin_widget_title__before', $project_slug, $locale );
			?>

			<div class="translation-stats-title">
				<p>

				<?php
				// Add Translation Stats plugin widget title.
				do_action( 'translation_stats_plugin_widget_title', $project_slug, $locale );

				// Add Translation Stats plugin widget title actions.
				do_action( 'translation_stats_plugin_widget_title__actions', $project_slug, $locale );
				?>

				</p>
			</div>

			<?php
			// Add after Translation Stats plugin widget title.
			do_action( 'translation_stats_plugin_widget_title__after', $project_slug, $locale );

			// Add before Translation Stats plugin widget content.
			do_action( 'translation_stats_plugin_widget_content__before', $project_slug, $locale );
			?>

			<div class="translation-stats-content">

				<?php
				// Add Translation Stats plugin widget content.
				do_action( 'translation_stats_plugin_widget_content' );
				?>

			</div>

			<?php
			// Add after Translation Stats plugin widget content.
			do_action( 'translation_stats_plugin_widget_content__after', $project_slug, $locale );
		}


		/**
		 * Load plugin widget title.
		 *
		 * @since 0.9.4
		 * @since 1.1.0  Use Locale object.
		 * @since 1.2.0  Renamed from tstats_stats_plugin_widget_title() to plugin_widget_title().
		 *
		 * @param string $project_slug  Plugin Slug.
		 * @param object $locale        Locale object.
		 *
		 * @return void
		 */
		public function plugin_widget_title( $project_slug, $locale ) {

			$locale_plugin_url  = 'https://translate.wordpress.org/locale/' . $locale->locale_slug . '/wp-plugins/' . $project_slug;
			$locale_plugin_link = '<a href="' . esc_url( $locale_plugin_url ) . '" target="_blank">' . $locale->native_name . '</a>';

			printf(
				wp_kses_post(
					/* translators: %s: Language native name. */
					__( 'Translation for %s', 'translation-stats' )
				),
				wp_kses_post( $locale_plugin_link )
			);
		}


		/**
		 * Load plugin widget title update button.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_plugin_update_button() to plugin_update_button().
		 *
		 * @return void
		 */
		public function plugin_update_button() {
			?>

			<span class="tstats-update-link">
				<button class="handlediv button-link tstats-update-button" type="button" aria-expanded="true">
					<span class="dashicons dashicons-update"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Update', 'translation-stats' ); ?></span>
				</button>
			</span>

			<?php
		}


		/**
		 * Load plugin widget loading placeholder.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_stats_plugin_widget_content() to plugin_widget_content().
		 *
		 * @return void
		 */
		public function plugin_widget_content() {

			$admin_notice_waiting = array(
				'type'        => 'warning',
				'notice-alt'  => true,
				'css-class'   => 'translation-stats-loading',
				'update-icon' => true,
				'force_show'  => true,
				'message'     => esc_html__( 'Waiting...', 'translation-stats' ),
			);
			Admin_Notice::message( $admin_notice_waiting );
			?>
			<div class="content"></div>
			<?php
		}


		/**
		 * Load plugin widget content.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_stats_plugin_widget_content_load() to plugin_widget_content_load().
		 *
		 * @return void
		 */
		public function plugin_widget_content_load() {

			// Initialize variable.
			$force_update = '';

			if ( isset( $_POST['forceUpdate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$force_update = 'true' === sanitize_key( $_POST['forceUpdate'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			$locale = Translations_API::locale( Utils::translation_language() );

			if ( isset( $_POST['tstatsPlugin'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$project_slug = sanitize_key( $_POST['tstatsPlugin'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$this->plugin_widget_content_stats( $project_slug, $locale, $force_update );

			}

			wp_die();
		}


		/**
		 * Load plugin widget content stats.
		 *
		 * @since 0.9.4
		 * @since 1.1.0   Use Locale object.
		 * @since 1.2.0   Renamed from tstats_stats_plugin_widget_content_stats() to plugin_widget_content_stats().
		 *
		 * @param string $project_slug   Plugin Slug.
		 * @param object $locale         Locale object.
		 * @param bool   $force_update   True: Force get new stats. False: Use transients.
		 *
		 * @return void
		 */
		public function plugin_widget_content_stats( $project_slug, $locale, $force_update ) {

			// Get options.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );

			// Get standard WP.org subprojects.
			$subprojects = Translations_API::plugin_subprojects();

			// Define project translation stats.
			$project_stats = array();

			?>
			<div class="translation-stats-content-stats widget-inside">
				<?php

				foreach ( $subprojects as $subproject ) {

					// Get plugin subproject translation stats.
					$project_stats[ $subproject['slug'] ] = $this->plugin_subproject_stats( $locale, $project_slug, $subproject['slug'], $force_update );

					// Show bar only if subproject is enabled on its settings.
					if ( isset( $options['plugins'][ $project_slug ][ $subproject['slug'] ] ) ) {

						// Render subproject stats bar.
						$this->render_stats_bar( $project_stats[ $subproject['slug'] ], $locale, $project_slug, $subproject['name'], $subproject['slug'] );

					}
				}

				?>
			</div>
			<?php

			// Show admin notices with additional information for each plugin, for development mode only.
			$this->render_notices( $project_stats, $project_slug, $locale );
		}


		/**
		 * Render plugin subproject stat bar.
		 *
		 * @since 0.8.0
		 * @since 1.1.0   Use Locale object.
		 * @since 1.2.0   Renamed from tstats_render_stats_bar() to render_stats_bar().
		 * @since 1.2.1   Removed unused parameter $force_update.
		 *
		 * @param object|string $subproject_stats   Subproject stats. Can be either an object or an empty string.
		 * @param object        $locale             Locale object.
		 * @param string        $project_slug       Plugin Slug.
		 * @param string        $subproject         Translation subproject ( 'Dev', 'Dev Readme', 'Stable', 'Stable Readme' ).
		 * @param string        $subproject_slug    Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 *
		 * @return void
		 */
		public function render_stats_bar( $subproject_stats, $locale, $project_slug, $subproject, $subproject_slug ) {
			/*
			 * Check if subproject is an object.
			 *
			 * Example of the object properties:
			 * [id] => 416518
			 * [name] => Portuguese (Portugal)
			 * [slug] => default | ao90 | informal
			 * [project_id] => 3333
			 * [locale] => pt
			 * [current_count] => 136
			 * [untranslated_count] => 0
			 * [waiting_count] => 0
			 * [fuzzy_count] => 0
			 * [all_count] => 136
			 * [warnings_count] => 0
			 * [percent_translated] => 100
			 * [wp_locale] => pt_PT
			 * [last_modified] => 2018-10-11 10:05:30
			 */
			$subproject_exist = is_object( $subproject_stats ) ? true : false;

			$class = $subproject_exist ? 'enabled' : 'disabled';
			$href  = $subproject_exist ? 'href="' . esc_url( Translations_API::translate_url( 'plugins', false ) . $project_slug . '/' . $subproject_slug . '/' . $locale->locale_slug ) . '"' : '';

			// Percent translated.
			$percent_translated = $subproject_exist && isset( $subproject_stats->percent_translated ) ? $subproject_stats->percent_translated : 0;

			// Current count.
			$current_count = $subproject_exist && isset( $subproject_stats->current_count ) ? $subproject_stats->current_count : 0;

			// All count.
			$all_count = $subproject_exist && isset( $subproject_stats->all_count ) ? $subproject_stats->all_count : 0;

			?>
			<div class="content__subproject <?php echo esc_attr( $subproject_slug ); ?>">
				<a class="<?php echo esc_attr( $class ); ?>" target="_blank" <?php echo wp_kses_post( $href ); ?>>
				<?php
				if ( $subproject_exist ) {

					?>
					<style>
						tr[data-slug="<?php echo esc_attr( $project_slug ); ?>"] div.subproject.<?php echo esc_attr( $subproject_slug ); ?> {
							width: <?php echo esc_attr( $percent_translated ); ?>%;
						}
					</style>
					<div class="subproject <?php echo esc_attr( 'percent' . 10 * floor( $percent_translated / 10 ) . ' ' . $subproject_slug ); ?>">
						<div class="subproject-bar">
							<span class="subproject-bar__percentage"><?php echo esc_html( $percent_translated ); ?>%</span>
							<span class="subproject-bar__name"><?php echo esc_html( $subproject ); ?></span>
							<span class="subproject-bar__count">
								<?php
								printf(
									// translators: 1: Current count. 2: All count.
									esc_html__( '(%1$d/%2$d)', 'translation-stats' ),
									esc_html( $current_count ),
									esc_html( $all_count )
								);
								?>
							</span>
						</div>
					</div>
					<?php

				} else {

					?>
					<div class="subproject">
						<div class="subproject-bar">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: 1: Name of subproject. 2: Error message. */
									__( '%1$s: %2$s', 'translation-stats' ),
									$subproject,
									'<strong>' . __( 'Not found', 'translation-stats' ) . '</strong>'
								)
							);
							?>
						</div>
					</div>
					<?php

				}
				?>
				</a>
			</div>
			<?php
		}


		/**
		 * Show admin notices with additional information for each plugin.
		 *
		 * 1) Show 'Stable' or 'Development' language packs info if available.
		 * 2) Check if at least one of the above are enabled to allow check if it's prepared for localization.
		 * 3) If none, inform that the project isn't ready for localization.
		 *
		 * A few projects that by the time this was coded have missing translation sub-projects:
		 *   - https://translate.wordpress.org/locale/pt/default/wp-plugins/media-library-enable-infinite-scrolling/
		 *   - https://translate.wordpress.org/locale/pt/default/wp-plugins/wp-seo-acf-content-analysis/
		 *   - https://translate.wordpress.org/locale/pt/default/wp-plugins/woo-fly-cart/
		 *   - https://translate.wordpress.org/locale/pt/default/wp-plugins/testerwp-ecommerce-companion/
		 *
		 * @since 1.2.0.
		 *
		 * @param array  $project_stats   Array of the sub-projects translation stats objects.
		 * @param string $project_slug    Plugin slug.
		 * @param object $locale          Locale object.
		 *
		 * @return void
		 */
		public function render_notices( $project_stats, $project_slug, $locale ) {

			// Current threshold for plugins Language Packs.
			$language_packs_threshold = 90;

			// Initialize admin notice common settings.
			$admin_notice = array(
				'message'    => null,
				'notice-alt' => true,
				'wrap'       => false,
			);

			if ( isset( $project_stats['stable'] ) && is_object( $project_stats['stable'] ) ) { // First check id 'Stable' stats are enabled and if the subproject exists on WP.org.

				// Only show initial language pack information if percent translated is below minimum threshold.
				if ( isset( $project_stats['stable']->percent_translated ) && $project_stats['stable']->percent_translated < $language_packs_threshold ) {

					// Loads from Stable (Latest release).
					$admin_notice['type']    = 'info';
					$admin_notice['message'] = sprintf(
						'<p>%s</p>',
						sprintf(
							wp_kses_post(
								/* translators: 1: Threshold value. 2: Translation sub-project name. 3: Current value of percent translated. */
								esc_html__( 'The initial language pack for the plugin will be generated when %1$d%% of the %2$s sub-project strings have been translated (currently %3$d%%).', 'translation-stats' )
							),
							$language_packs_threshold,
							sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								esc_url( Translations_API::translate_url( 'plugins', false ) . $project_slug . '/stable/' . $locale->locale_slug ),
								esc_html_x( 'Stable (latest release)', 'Subproject name', 'translation-stats' )
							),
							$project_stats['stable']->percent_translated
						)
					);

				}
			} elseif ( isset( $project_stats['dev'] ) && is_object( $project_stats['dev'] ) ) { // Second check id 'Development' stats are enabled and if the subproject exists on WP.org.

				// Only show initial language pack information if percent translated is below minimum threshold.
				if ( isset( $project_stats['dev']->percent_translated ) && $project_stats['dev']->percent_translated < $language_packs_threshold ) {

					// Loads from Development (trunk).
					$admin_notice['type']    = 'info';
					$admin_notice['message'] = sprintf(
						'<p>%s</p>',
						sprintf(
							wp_kses_post(
								/* translators: 1: Threshold value. 2: Translation sub-project name. 3: Current value of percent translated. */
								esc_html__( 'The initial language pack for the plugin will be generated when %1$d%% of the %2$s sub-project strings have been translated (currently %3$d%%).', 'translation-stats' )
							),
							$language_packs_threshold,
							sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								esc_url( Translations_API::translate_url( 'plugins', false ) . $project_slug . '/dev/' . $locale->locale_slug ),
								esc_html_x( 'Development (trunk)', 'Subproject name', 'translation-stats' )
							),
							$project_stats['dev']->percent_translated
						)
					);

				}
			} else { // Third check if both 'Stable' and 'Development' stats are enabled.

				// The project is not correctly prepared for localization.
				$admin_notice['type']    = 'error';
				$admin_notice['message'] = sprintf(
					'<p>%1$s %2$s</p><p>%3$s %4$s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							__( 'This plugin is not %1$sproperly prepared for localization%2$s.', 'translation-stats' )
						),
						'<a href="https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/" target="_blank">',
						'</a>'
					),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							__( 'If you would like to translate this plugin, %1$splease contact the author%2$s.', 'translation-stats' )
						),
						'<a href="https://wordpress.org/support/plugin/' . esc_attr( $project_slug ) . '" target="_blank">',
						'</a>'
					),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							__( 'Import results are logged on Slack in the %1$s#meta-language-packs%2$s channel.', 'translation-stats' )
						),
						'<a href="https://wordpress.slack.com/archives/C0E7F4RND" target="_blank">',
						'</a>'
					),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							__( 'Please see the %1$shandbook for more information about Slack and possible errors%2$s.', 'translation-stats' )
						),
						'<a href="https://make.wordpress.org/meta/handbook/documentation/translations/#how-to-handle-this-plugin-is-not-properly-prepared-for-localization-warning" target="_blank">',
						'</a>'
					)
				);

			}

			// Check if there is any actual message to show.
			if ( null === $admin_notice['message'] ) {
				return;
			}

			// Check if the notice is not an error and is not in development mode.
			if ( 'error' !== $admin_notice['type'] && ! Utils::is_development_mode() ) {
				return;
			}

			// Show notice if is an error or if development mode is enabled.
			?>
			<div class="translation-stats-content-notices">
				<?php

				Admin_Notice::message( $admin_notice );

				?>
			</div>
			<?php
		}


		/**
		 * Render plugin subproject stats bar.
		 *
		 * @since 0.8.0
		 * @since 1.1.0   Use Locale object.
		 * @since 1.2.0   Renamed from tstats_plugin_subproject_stats() to plugin_subproject_stats().
		 *
		 * @param object $locale            Locale object.
		 * @param string $project_slug      Plugin Slug.
		 * @param string $subproject_slug   Translation subproject Slug ( 'dev', 'dev-readme', 'stable', 'stable-readme' ).
		 * @param bool   $force_update      True: Force get new stats. False: Use transients.
		 *
		 * @return object|false              Project stats if exist, otherwise returns 'false'.
		 */
		public function plugin_subproject_stats( $locale, $project_slug, $subproject_slug, $force_update ) {

			// Check for force update setting.
			if ( true === $force_update ) {
				$stats = false;
			} else {
				// Get subproject transients.
				$stats = get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug . '_' . $locale->wp_locale );
			}

			if ( false === $stats ) {

				$json = Translations_API::translations_api_get_plugin( $project_slug . '/' . $subproject_slug );

				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {

					// Subproject not found (Error 404) - Plugin is not properly prepared for localization.
					$stats = false;

				} else {

					$body = json_decode( $json['body'] );
					if ( empty( $body->translation_sets ) ) {

						// No translation sets found.
						$stats = false;

					} else {

						foreach ( $body->translation_sets as $translation_set ) {

							// Check for exact match of locale/variant (translation set variant/slug).
							if ( $translation_set->locale . '/' . $translation_set->slug === $locale->locale_slug ) {
								// Set transient value.
								$stats = $translation_set;
								continue;
							}
						}
					}
				}

				set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug . '_' . $locale->wp_locale, $stats, get_option( TRANSLATION_STATS_WP_OPTION )['settings']['transients_expiration'] );
			}

			return $stats;
		}


		/**
		 * Filter plugins list to show only Translation Stats enabled plugins.
		 *
		 * @since 0.9.9
		 * @since 1.2.0   Renamed from tstats_plugins_filter_by_translation_stats() to plugins_filter_by_translation_stats().
		 *
		 * @param array $plugins   Array of arrays containing information on all installed plugins.
		 *
		 * @return void
		 */
		public function plugins_filter_by_translation_stats( $plugins ) {
			// Get WP_Plugins_List_Table and page number.
			global $wp_list_table, $page;

			// Get the Translation Stats configured language.
			$translationstats_language = Utils::translation_language();

			// Check if user locale is not 'en_US'.
			if ( 'en_US' === $translationstats_language ) {
				return;
			}

			// Set the status type.
			$status = 'translation_stats';

			if ( ! ( isset( $_REQUEST['plugin_status'] ) && $status === $_REQUEST['plugin_status'] ) ) { // phpcs:ignore
				// If current status is not 'translation_stats', do nothing.
				return;
			}

			$options = get_site_option( TRANSLATION_STATS_WP_OPTION );

			$translationstats_plugins = array();

			foreach ( $plugins as $plugin_file => $plugin_data ) {

				// Check if the plugin is enabled in the Translation Stats settings.
				$project_slug = Translations_API::plugin_metadata( $plugin_file, 'slug' );
				if ( empty( $options['plugins'][ $project_slug ]['enabled'] ) ) {
					// Skip to next loop iteration.
					continue;
				}

				// Add plugin to list.
				$translationstats_plugins[ $plugin_file ] = $plugin_data;
			}

			// Set the table list items array to just the Translation Stats enabled plugins.
			$wp_list_table->items = $translationstats_plugins;

			// Count Translation Stats enabled plugins.
			$count = count( $translationstats_plugins );

			// Get plugins_per_page setting.
			$plugins_per_page = $wp_list_table->get_items_per_page( str_replace( '-', '_', $wp_list_table->screen->id . '_per_page' ), 999 );

			// Slice plugin list array to show only current page items.
			$start = ( $page - 1 ) * $plugins_per_page;
			if ( $count > $plugins_per_page ) {
				$wp_list_table->items = array_slice( $wp_list_table->items, $start, $plugins_per_page );
			}

			// Set pagination arguments.
			$wp_list_table->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $plugins_per_page,
				)
			);
		}


		/**
		 * Add status link to Translation Stats enabled plugins View.
		 *
		 * @since 0.9.9
		 * @since 1.2.0   Renamed from tstats_plugins_status_link() to plugins_status_link().
		 *
		 * @param array $status_links   Array of status links.
		 *
		 * @return array                Array of status links.
		 */
		public function plugins_status_link( $status_links ) {

			// Get the Translation Stats configured language.
			$translationstats_language = Utils::translation_language();

			// Check if user locale is not 'en_US'.
			if ( 'en_US' === $translationstats_language ) {
				return $status_links;
			}

			if ( ! current_user_can( 'update_plugins' ) ) {
				return $status_links;
			}

			$options = get_site_option( TRANSLATION_STATS_WP_OPTION );

			// Check if Translation Stats settings exist.
			if ( empty( $options ) ) {
				return $status_links;
			}

			// Check if Translation Stats settings plugins array is set.
			if ( ! isset( $options['plugins'] ) ) {
				return $status_links;
			}

			$translationstats_plugins = array();

			$plugins = get_plugins();

			foreach ( $plugins as $plugin_file => $plugin_data ) {

				// Check if the plugin is enabled in the Translation Stats settings.
				$project_slug = Translations_API::plugin_metadata( $plugin_file, 'slug' );
				if ( empty( $options['plugins'][ $project_slug ]['enabled'] ) ) {
					// Skip to next loop iteration.
					continue;
				}

				// Add plugin to list.
				$translationstats_plugins[] = $plugin_file;
			}

			$count = count( $translationstats_plugins );

			// Set the status type.
			$status = 'translation_stats';

			$current_status = isset( $_REQUEST[ 'plugin_status' ] ) ? $_REQUEST[ 'plugin_status' ] : 'all'; // phpcs:ignore

			// Don't show link if count is 0.
			if ( 0 === $count ) {
				return $status_links;
			}

			$text = sprintf(
				/* translators: %s: Number of plugins. */
				'%s <span class="count">(%s)</span>',
				_x( 'Translation Stats', 'Plugin status filter', 'translation-stats' ),
				$count
			);

			// Set the link HTML.
			$status_links[ $status ] = sprintf(
				"<a href='%s'%s>%s</a>",
				add_query_arg( 'plugin_status', $status, 'plugins.php' ),
				( $status === $current_status ) ? ' class="current" aria-current="page"' : '',
				$text
			);

			// Make the 'all' status link not current if current status is "translation_stats".
			if ( $status === $current_status ) {
				$status_links['all'] = str_replace( ' class="current" aria-current="page"', '', $status_links['all'] );
			}

			return $status_links;
		}
	}
}
