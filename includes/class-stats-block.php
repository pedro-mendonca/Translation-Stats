<?php
/**
 * Class file for the Translation Stats block.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 */

namespace Translation_Stats;

use WP_Block_Supports;
use WP_Block_Type_Registry;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Stats_Block' ) ) {

	/**
	 * Class Stats_Block.
	 *
	 * Shows the translation stats of any plugin or theme from translate.wordpress.org on the frontend.
	 * Visitors only get the cached stats, see Stats_Block_Cache.
	 *
	 * @phpstan-import-type BlockAttributes from Stats_Block_Attributes
	 */
	class Stats_Block {


		/**
		 * Block name.
		 *
		 * @var string
		 */
		const NAME = 'translation-stats/stats';

		/**
		 * Minimum WordPress version for the block, with the block.json API version 3.
		 *
		 * @var string
		 */
		const MIN_WP_VERSION = '6.3';


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Register the block.
			add_action( 'init', array( $this, 'register' ) );

			// Add the editor data to the block editor script, only in the block editor.
			add_action( 'enqueue_block_editor_assets', array( $this, 'add_editor_data' ) );
		}


		/**
		 * Register the block, from the built block.json.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function register() {

			if ( ! is_wp_version_compatible( self::MIN_WP_VERSION ) || ! file_exists( TRANSLATION_STATS_DIR_PATH . 'assets/build/blocks/stats/block.json' ) ) {
				return;
			}

			register_block_type( TRANSLATION_STATS_DIR_PATH . 'assets/build/blocks/stats' );
		}

		/**
		 * Add the editor data to the block editor script.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function add_editor_data() {

			$block_type = WP_Block_Type_Registry::get_instance()->get_registered( self::NAME );

			if ( null === $block_type ) {
				return;
			}

			foreach ( $block_type->editor_script_handles as $handle ) {
				wp_add_inline_script(
					$handle,
					'window.tstatsBlock = ' . wp_json_encode( self::editor_data() ) . ';',
					'before'
				);
			}
		}

		/**
		 * Get the editor data: the languages, the site language, and the installed plugins and themes.
		 *
		 * @since 2.1.0
		 * @since 2.1.1 Added the languages native name and translate.wordpress.org slug, and the site language, for the stats placeholder.
		 *
		 * @return array{languages: list<array{value: string, label: string, name: string, slug: string}>, siteLanguage: string, plugins: list<array{value: string, label: string}>, themes: list<array{value: string, label: string}>}
		 */
		public static function editor_data() {

			$languages = array();
			foreach ( Locales::instance()->locales as $locale ) {
				if ( 'en' === $locale->slug || null === $locale->wp_locale ) {
					continue;
				}
				$languages[] = array(
					'value' => $locale->wp_locale,
					'label' => $locale->native_name . ' (' . $locale->wp_locale . ')',
					'name'  => $locale->native_name,
					'slug'  => $locale->locale_slug,
				);
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @codeCoverageIgnore
			}

			$plugins = array();
			foreach ( get_plugins() as $plugin_file => $plugin ) {
				$plugins[] = array(
					'value' => Translations_API::get_plugin_slug( $plugin_file ),
					'label' => $plugin['Name'],
				);
			}

			$themes = array();
			foreach ( wp_get_themes() as $stylesheet => $theme ) {
				$themes[] = array(
					'value' => (string) $stylesheet,
					'label' => (string) $theme->get( 'Name' ),
				);
			}

			return array(
				'languages'    => $languages,
				'siteLanguage' => get_locale(),
				'plugins'      => $plugins,
				'themes'       => $themes,
			);
		}


		/**
		 * Render the block.
		 *
		 * Shows the cached stats. Without cached stats, shows the stats placeholder and loads the stats in the background,
		 * also in the block editor preview, that shows the stats when loaded.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return string   Block HTML, or empty without project, language or subprojects.
		 */
		public static function render( $attributes ) {

			$project = Stats_Block_Attributes::project( $attributes );
			$locale  = Stats_Block_Attributes::locale( $attributes );

			if ( null === $project || null === $locale ) {
				return '';
			}

			$subprojects = Stats_Block_Attributes::subprojects( $project, $attributes );

			if ( empty( $subprojects ) ) {
				return '';
			}

			$stats    = self::stats( $project, $locale, $subprojects, $attributes );
			$block_id = Stats_Block_Attributes::block_id( $project, $locale );

			ob_start();
			?>
			<div <?php echo self::wrapper_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() and esc_attr(). ?>>
				<div <?php echo self::widget_attributes( $project, $locale, $subprojects, $stats, $block_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in widget_attributes(). ?>>
					<?php self::render_header( $project, $locale, $attributes ); ?>
					<div class="translation-stats-content">
						<?php
						if ( is_array( $stats ) ) {
							echo self::render_stats( $project, $locale, $subprojects, $stats, $block_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in Stats_Bar::render().
						} else {
							Stats_Widget::render_placeholder( $subprojects );
						}
						?>
					</div>
				</div>
			</div>
			<?php

			return (string) ob_get_clean();
		}


		/**
		 * Get the block stats: the cached stats, or loaded in the background.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects   Subprojects.
		 * @param BlockAttributes                         $attributes    Block attributes.
		 *
		 * @return array<string, object|false>|null   Stats of each subproject, or null if not cached yet.
		 */
		private static function stats( $project, $locale, $subprojects, $attributes ) {

			$stats = Stats_Block_Cache::cached_stats( $project, $locale, $subprojects );

			if ( null !== $stats ) {
				return $stats;
			}

			/**
			 * Filters whether to load the missing stats right away, instead of in the background.
			 *
			 * @since 2.1.0
			 *
			 * @since 2.1.1 Defaults to false also in the block editor preview, that loads the stats in the background,
			 *              instead of loading each block stats in its preview request.
			 *
			 * @param bool                 $load         True to load the missing stats right away. Defaults to false.
			 * @param array<string, mixed> $attributes   Block attributes.
			 */
			if ( true === apply_filters( 'translation_stats_block_load_stats', false, $attributes ) ) {
				return Stats_Block_Cache::load_stats( $project, $locale, $subprojects );
			}

			Stats_Block_Cache::schedule_refresh( $project, $locale );

			return null;
		}


		/**
		 * Get the block wrapper attributes, or only the block class outside the block render, as when rendered directly.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return string   Block wrapper attributes.
		 */
		private static function wrapper_attributes( $attributes ) {

			$classes = false === ( $attributes['showCounts'] ?? true ) ? 'tstats-hide-counts' : '';

			if ( null === WP_Block_Supports::$block_to_render ) {
				return 'class="' . esc_attr( trim( 'wp-block-translation-stats-stats ' . $classes ) ) . '"';
			}

			return get_block_wrapper_attributes( array( 'class' => $classes ) );
		}


		/**
		 * Get the widget attributes: the moving stripes while loading, and the cached stats REST API URL while not cached.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects   Subprojects.
		 * @param array<string, object|false>|null        $stats         Stats of each subproject, or null if not cached yet.
		 * @param string                                  $block_id      Block ID.
		 *
		 * @return string   Widget attributes.
		 */
		private static function widget_attributes( $project, $locale, $subprojects, $stats, $block_id ) {

			$attributes = 'data-tstats-block="' . esc_attr( $block_id ) . '" data-slug="' . esc_attr( $project->get_slug() ) . '"';

			if ( is_array( $stats ) ) {
				return 'class="translation-stats-widget" ' . $attributes;
			}

			$class = 'loading' === Stats_Block_Cache::loading_status( $project, $locale ) ? 'translation-stats-widget tstats-loading' : 'translation-stats-widget';

			return 'class="' . esc_attr( $class ) . '" ' . $attributes . ' data-tstats-refresh="' . esc_url( Stats_Block_REST_Controller::refresh_url( $project, $locale, $subprojects, $block_id ) ) . '"';
		}


		/**
		 * Render the block header: the project name, and the language linked to the project on translate.wordpress.org.
		 *
		 * @since 2.1.0
		 *
		 * @param Project         $project      The plugin or theme project.
		 * @param Locale          $locale       The Locale.
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return void
		 */
		private static function render_header( $project, $locale, $attributes ) {

			if ( false !== ( $attributes['showName'] ?? true ) ) {
				?>
				<p class="translation-stats-name"><strong><?php echo esc_html( Stats_Block_Attributes::project_name( $project, $attributes ) ); ?></strong></p>
				<?php
			}

			if ( false === ( $attributes['showTitle'] ?? true ) ) {
				return;
			}

			?>
			<div class="translation-stats-title">
				<p>
					<?php
					printf(
						/* translators: %s: Language native name. */
						esc_html__( 'Translation for %s', 'translation-stats' ),
						'<a href="' . esc_url( Stats_Block_Attributes::locale_project_url( $project, $locale ) ) . '" target="_blank">' . esc_html( $locale->native_name ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL and name.
					);
					?>
				</p>
			</div>
			<?php
		}


		/**
		 * Render the stats bars of the subprojects.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects   Subprojects.
		 * @param array<string, object|false>             $stats         Stats of each subproject, false if not found.
		 * @param string                                  $block_id      Block ID, to scope the bars width.
		 *
		 * @return string   Stats bars HTML.
		 */
		public static function render_stats( $project, $locale, $subprojects, $stats, $block_id ) {

			ob_start();
			?>
			<div class="translation-stats-content-stats">
				<?php
				foreach ( $subprojects as $subproject ) {
					// Scoped to the block without an ID selector, to stretch the bars on hover as in the admin widget.
					Stats_Bar::render( $project, $stats[ $subproject['slug'] ] ?? false, $locale, $subproject['name'], $subproject['slug'], 'div[data-tstats-block=' . $block_id . ']' );
				}
				?>
			</div>
			<?php

			return (string) ob_get_clean();
		}

		/**
		 * Check if the block is rendered for the block editor preview, by a user who can edit posts.
		 *
		 * @since 2.1.0
		 *
		 * @return bool   True for the block editor preview.
		 */
		public static function is_editor_preview() {
			return defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' );
		}
	}
}
