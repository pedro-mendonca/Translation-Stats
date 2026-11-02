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
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Stats_Block' ) ) {

	/**
	 * Class Stats_Block.
	 *
	 * Shows the translation stats of any plugin or theme from translate.wordpress.org on the frontend.
	 * Visitors only get the cached stats: the missing stats are loaded in the background, with WP-Cron.
	 *
	 * @phpstan-type BlockAttributes array{type?: mixed, slug?: mixed, name?: mixed, language?: mixed, subprojects?: mixed, showName?: mixed, showTitle?: mixed, showCounts?: mixed}
	 */
	class Stats_Block {


		/**
		 * Block name.
		 *
		 * @var string
		 */
		const NAME = 'translation-stats/stats';

		/**
		 * WP-Cron hook to load the project stats in the background.
		 *
		 * @var string
		 */
		const REFRESH_HOOK = 'translation_stats_block_refresh';

		/**
		 * Transient of the project stats loading in the background: type, slug and WordPress Locale.
		 *
		 * @var string
		 */
		const LOADING_TRANSIENT = 'translation_stats_block_loading';

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

			// Load the project stats in the background.
			add_action( self::REFRESH_HOOK, array( $this, 'refresh' ), 10, 3 );

			// Register the REST API route of the cached stats, to show them when loaded in the background.
			add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

			// Load the stats of the blocks when the post is saved, to show them to the first visitors.
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

			// Remove the scheduled stats loading on deactivation.
			register_deactivation_hook( TRANSLATION_STATS_FILE, array( __CLASS__, 'deactivate' ) );
		}


		/**
		 * Remove the scheduled stats loading.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public static function deactivate() {
			wp_unschedule_hook( self::REFRESH_HOOK );
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
		 * Get the editor data: the languages and the installed plugins and themes.
		 *
		 * @since 2.1.0
		 *
		 * @return array{languages: list<array{value: string, label: string}>, plugins: list<array{value: string, label: string}>, themes: list<array{value: string, label: string}>}
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
				'languages' => $languages,
				'plugins'   => $plugins,
				'themes'    => $themes,
			);
		}


		/**
		 * Get the project of the block.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return Project|null   The plugin or theme project, or null without slug.
		 */
		public static function project( $attributes ) {

			$slug = isset( $attributes['slug'] ) && is_string( $attributes['slug'] ) ? sanitize_title( $attributes['slug'] ) : '';

			if ( '' === $slug ) {
				return null;
			}

			$type = $attributes['type'] ?? 'plugins';

			return 'themes' === $type ? new Theme_Project( $slug ) : new Plugin_Project( $slug );
		}


		/**
		 * Get the project name: the block name, the installed plugin or theme name, or the slug.
		 *
		 * @since 2.1.0
		 *
		 * @param Project         $project      The plugin or theme project.
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return string   Project name.
		 */
		public static function project_name( $project, $attributes ) {

			if ( isset( $attributes['name'] ) && is_string( $attributes['name'] ) && '' !== trim( $attributes['name'] ) ) {
				return trim( $attributes['name'] );
			}

			if ( 'themes' === $project->get_type() ) {
				$theme = wp_get_theme( $project->get_slug() );
				return $theme->exists() ? (string) $theme->get( 'Name' ) : $project->get_slug();
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @codeCoverageIgnore
			}

			foreach ( get_plugins() as $plugin_file => $plugin ) {
				if ( Translations_API::get_plugin_slug( $plugin_file ) === $project->get_slug() ) {
					return $plugin['Name'];
				}
			}

			return $project->get_slug();
		}


		/**
		 * Get the translate.wordpress.org URL of the project in the Locale.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return string   Project URL in the Locale, as the language link of the plugins and themes widgets.
		 */
		public static function locale_project_url( $project, $locale ) {
			return 'https://translate.wordpress.org/locale/' . $locale->locale_slug . '/wp-' . $project->get_type() . '/' . $project->get_slug();
		}


		/**
		 * Get the WordPress.org Locale of the block language, without loading the available translations from WordPress.org.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return Locale|null   The Locale, or null if not found.
		 */
		public static function locale( $attributes ) {

			$wp_locale = isset( $attributes['language'] ) && is_string( $attributes['language'] ) && '' !== $attributes['language'] ? $attributes['language'] : get_locale();

			foreach ( Locales::instance()->locales as $locale ) {
				if ( 'en' !== $locale->slug && $locale->wp_locale === $wp_locale ) {
					return $locale;
				}
			}

			return null;
		}


		/**
		 * Get the subprojects to show: the selected plugin subprojects, or the theme project.
		 *
		 * @since 2.1.0
		 *
		 * @param Project         $project      The plugin or theme project.
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return list<array{slug: string, name: string}>   Subprojects.
		 */
		public static function subprojects( $project, $attributes ) {

			if ( 'themes' === $project->get_type() ) {
				return array_values( $project->get_subprojects() );
			}

			$selected = isset( $attributes['subprojects'] ) && is_array( $attributes['subprojects'] ) ? $attributes['subprojects'] : array( 'stable', 'stable-readme' );

			return array_values(
				array_filter(
					$project->get_subprojects(),
					function ( $subproject ) use ( $selected ) {
						return in_array( $subproject['slug'], $selected, true );
					}
				)
			);
		}


		/**
		 * Render the block.
		 *
		 * Shows the cached stats. Without cached stats, shows the stats placeholder and loads the stats in the background.
		 * The block editor preview loads the stats right away.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return string   Block HTML, or empty without project, language or subprojects.
		 */
		public static function render( $attributes ) {

			$project = self::project( $attributes );
			$locale  = self::locale( $attributes );

			if ( null === $project || null === $locale ) {
				return '';
			}

			$subprojects = self::subprojects( $project, $attributes );

			if ( empty( $subprojects ) ) {
				return '';
			}

			$stats = self::cached_stats( $project, $locale, $subprojects );

			/**
			 * Filters whether to load the missing stats right away, instead of in the background.
			 *
			 * @since 2.1.0
			 *
			 * @param bool                 $load         True to load the missing stats right away. Defaults to true only in the block editor preview.
			 * @param array<string, mixed> $attributes   Block attributes.
			 */
			$load = apply_filters( 'translation_stats_block_load_stats', self::is_editor_preview(), $attributes );

			// Load the missing stats right away in the block editor preview, or in the background for the visitors.
			if ( null === $stats && true === $load ) {
				$stats = self::load_stats( $project, $locale, $subprojects );
			} elseif ( null === $stats ) {
				self::schedule_refresh( $project, $locale );
			}

			$id = self::block_id( $project, $locale );

			$classes = false === ( $attributes['showCounts'] ?? true ) ? 'tstats-hide-counts' : '';

			// The block wrapper attributes, or only the block class outside the block render, as when rendered directly.
			$wrapper_attributes = null === WP_Block_Supports::$block_to_render
				? 'class="' . esc_attr( trim( 'wp-block-translation-stats-stats ' . $classes ) ) . '"'
				: get_block_wrapper_attributes( array( 'class' => $classes ) );

			ob_start();
			?>
			<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() and esc_attr(). ?>>
				<div class="<?php echo esc_attr( ! is_array( $stats ) && 'loading' === self::loading_status( $project, $locale ) ? 'translation-stats-widget tstats-loading' : 'translation-stats-widget' ); ?>" data-tstats-block="<?php echo esc_attr( $id ); ?>" data-slug="<?php echo esc_attr( $project->get_slug() ); ?>"<?php echo is_array( $stats ) ? '' : ' data-tstats-refresh="' . esc_url( self::refresh_url( $project, $locale, $subprojects, $id ) ) . '"'; ?>>
					<?php
					if ( false !== ( $attributes['showName'] ?? true ) ) {
						?>
						<p class="translation-stats-name"><strong><?php echo esc_html( self::project_name( $project, $attributes ) ); ?></strong></p>
						<?php
					}

					if ( false !== ( $attributes['showTitle'] ?? true ) ) {
						?>
						<div class="translation-stats-title">
							<p>
								<?php
								printf(
									/* translators: %s: Language native name. */
									esc_html__( 'Translation for %s', 'translation-stats' ),
									'<a href="' . esc_url( self::locale_project_url( $project, $locale ) ) . '" target="_blank">' . esc_html( $locale->native_name ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL and name.
								);
								?>
							</p>
						</div>
						<?php
					}
					?>
					<div class="translation-stats-content">
						<?php
						if ( is_array( $stats ) ) {
							echo self::render_stats( $project, $locale, $subprojects, $stats, $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in Stats_Bar::render().
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
		 * Get the block ID, to scope the stats bars width, from the project and language.
		 * Unique also in the block editor, that renders each block in its own request.
		 * Blocks with the same project and language have the same bars width.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return string   Block ID.
		 */
		public static function block_id( $project, $locale ) {
			return 'translation-stats-block-' . substr( md5( implode( '/', self::refresh_args( $project, $locale ) ) ), 0, 12 );
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
		 * @param string                                  $id            Block ID, to scope the bars width.
		 *
		 * @return string   Stats bars HTML.
		 */
		public static function render_stats( $project, $locale, $subprojects, $stats, $id ) {

			ob_start();
			?>
			<div class="translation-stats-content-stats">
				<?php
				foreach ( $subprojects as $subproject ) {
					// Scoped to the block without an ID selector, to stretch the bars on hover as in the admin widget.
					Stats_Bar::render( $project, $stats[ $subproject['slug'] ] ?? false, $locale, $subproject['name'], $subproject['slug'], 'div[data-tstats-block=' . $id . ']' );
				}
				?>
			</div>
			<?php

			return (string) ob_get_clean();
		}


		/**
		 * Get the REST API URL of the cached stats of a block, to show them when loaded in the background.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects   Subprojects.
		 * @param string                                  $id            Block ID.
		 *
		 * @return string   REST API URL.
		 */
		public static function refresh_url( $project, $locale, $subprojects, $id ) {

			return add_query_arg(
				array(
					'type'        => $project->get_type(),
					'slug'        => $project->get_slug(),
					'language'    => $locale->wp_locale,
					'subprojects' => implode( ',', wp_list_pluck( $subprojects, 'slug' ) ),
					'id'          => $id,
				),
				rest_url( 'translation-stats/v1/block-stats' )
			);
		}


		/**
		 * Register the REST API route of the cached stats of a block.
		 *
		 * Public, as the blocks are on the frontend, and only reads the cache: it never requests translate.wordpress.org.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function register_rest_route() {

			register_rest_route(
				'translation-stats/v1',
				'/block-stats',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rest_cached_stats' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'type'        => array(
							'type'     => 'string',
							'enum'     => array( 'plugins', 'themes' ),
							'required' => true,
						),
						'slug'        => array(
							'type'     => 'string',
							'required' => true,
						),
						'language'    => array(
							'type'     => 'string',
							'required' => true,
						),
						'subprojects' => array(
							'type'    => 'string',
							'default' => '',
						),
						'id'          => array(
							'type'     => 'string',
							'pattern'  => '^translation-stats-block-[0-9a-f]+$',
							'required' => true,
						),
					),
				)
			);
		}


		/**
		 * Get the cached stats bars of a block, or null if not cached yet.
		 *
		 * @since 2.1.0
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return WP_REST_Response   Response with the stats bars HTML, or null if not cached, and the status: 'cached', 'loading' or 'queued'.
		 */
		public function rest_cached_stats( $request ) {

			$attributes = array(
				'type'        => $request['type'],
				'slug'        => $request['slug'],
				'language'    => $request['language'],
				'subprojects' => explode( ',', is_string( $request['subprojects'] ) ? $request['subprojects'] : '' ),
			);

			$html    = null;
			$status  = 'queued';
			$project = self::project( $attributes );
			$locale  = self::locale( $attributes );

			if ( null !== $project && null !== $locale ) {

				$subprojects = self::subprojects( $project, $attributes );
				$stats       = self::cached_stats( $project, $locale, $subprojects );

				if ( ! empty( $subprojects ) && is_array( $stats ) ) {
					$html   = self::render_stats( $project, $locale, $subprojects, $stats, is_string( $request['id'] ) ? $request['id'] : '' );
					$status = 'cached';
				} else {
					$status = self::loading_status( $project, $locale );
				}
			}

			return rest_ensure_response(
				array(
					'html'   => $html,
					'status' => $status,
				)
			);
		}


		/**
		 * Get the cached stats of the subprojects.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects  Subprojects.
		 *
		 * @return array<string, object|false>|null   Stats of each subproject, false if not found, or null if any isn't cached.
		 */
		public static function cached_stats( $project, $locale, $subprojects ) {

			$stats = array();

			foreach ( $subprojects as $subproject ) {

				$cached = get_transient( $project->subproject_stats_transient( $locale, $subproject['slug'] ) );

				if ( false === $cached ) {
					return null;
				}

				// A cached subproject without stats is an empty string.
				$stats[ $subproject['slug'] ] = is_object( $cached ) ? $cached : false;
			}

			return $stats;
		}


		/**
		 * Load the stats of the subprojects from translate.wordpress.org, if not cached.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects  Subprojects.
		 *
		 * @return array<string, object|false>|null   Stats of each subproject, false if not found, or null if translate.wordpress.org is unavailable.
		 */
		public static function load_stats( $project, $locale, $subprojects ) {

			$stats = array();

			foreach ( $subprojects as $subproject ) {

				$subproject_stats = $project->subproject_stats( $locale, $subproject['slug'], false );

				if ( is_wp_error( $subproject_stats ) ) {
					return null;
				}

				$stats[ $subproject['slug'] ] = $subproject_stats;
			}

			return $stats;
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


		/**
		 * Schedule the project stats loading in the background, once for each project and language.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return void
		 */
		public static function schedule_refresh( $project, $locale ) {

			$args = self::refresh_args( $project, $locale );

			// Not queued twice, nor while loading.
			if ( false === wp_next_scheduled( self::REFRESH_HOOK, $args ) && get_transient( self::LOADING_TRANSIENT ) !== $args ) {
				wp_schedule_single_event( time(), self::REFRESH_HOOK, $args );
			}
		}


		/**
		 * Load the missing stats of all the subprojects of a project, in the background.
		 *
		 * @since 2.1.0
		 *
		 * @param string $type        Project type, 'plugins' or 'themes'.
		 * @param string $slug        Project slug.
		 * @param string $wp_locale   WordPress Locale.
		 *
		 * @return void
		 */
		public function refresh( $type, $slug, $wp_locale ) {

			$attributes = array(
				'type'     => $type,
				'slug'     => $slug,
				'language' => $wp_locale,
			);

			$project = self::project( $attributes );
			$locale  = self::locale( $attributes );

			if ( null === $project || null === $locale ) {
				return;
			}

			// The project loading, shown with the moving stripes, as the queued projects have the static placeholder.
			set_transient( self::LOADING_TRANSIENT, self::refresh_args( $project, $locale ), 5 * MINUTE_IN_SECONDS );

			$project->stats( $locale, false );

			delete_transient( self::LOADING_TRANSIENT );
		}


		/**
		 * Get the background loading arguments of a project: type, slug and WordPress Locale.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return array{string, string, string}   Background loading arguments.
		 */
		public static function refresh_args( $project, $locale ) {
			return array( $project->get_type(), $project->get_slug(), (string) $locale->wp_locale );
		}


		/**
		 * Get the background loading arguments of the next project in the queue, the first scheduled to load.
		 *
		 * @since 2.1.0
		 *
		 * @return array<mixed>|null   Background loading arguments, or null without projects queued.
		 */
		public static function next_refresh() {

			foreach ( _get_cron_array() as $hooks ) {
				foreach ( $hooks[ self::REFRESH_HOOK ] ?? array() as $event ) {
					return $event['args'];
				}
			}

			return null;
		}


		/**
		 * Get the status of the stats of a block not cached, as in the admin widgets queue:
		 * loading for the project loading in the background, or the next in the queue if none is loading, otherwise queued.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return string   'loading' or 'queued'.
		 */
		public static function loading_status( $project, $locale ) {

			$loading = get_transient( self::LOADING_TRANSIENT );

			if ( ! is_array( $loading ) ) {
				$loading = self::next_refresh();
			}

			return self::refresh_args( $project, $locale ) === $loading ? 'loading' : 'queued';
		}


		/**
		 * Load the stats of the Translation Stats blocks of a saved post, in the background.
		 *
		 * @since 2.1.0
		 *
		 * @param int     $post_id   Post ID.
		 * @param WP_Post $post      Post object.
		 *
		 * @return void
		 */
		public function save_post( $post_id, $post ) {

			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! has_block( self::NAME, $post ) ) {
				return;
			}

			foreach ( self::find_blocks( parse_blocks( $post->post_content ) ) as $attributes ) {

				$project = self::project( $attributes );
				$locale  = self::locale( $attributes );

				if ( null !== $project && null !== $locale && null === self::cached_stats( $project, $locale, self::subprojects( $project, $attributes ) ) ) {
					self::schedule_refresh( $project, $locale );
				}
			}
		}


		/**
		 * Find the attributes of the Translation Stats blocks, also in the inner blocks.
		 *
		 * @since 2.1.0
		 *
		 * @param array<mixed> $blocks   Parsed blocks.
		 *
		 * @return list<BlockAttributes>   Attributes of each Translation Stats block.
		 */
		public static function find_blocks( $blocks ) {

			$found = array();

			foreach ( $blocks as $block ) {

				if ( ! is_array( $block ) ) {
					continue;
				}

				if ( self::NAME === ( $block['blockName'] ?? null ) ) {
					$found[] = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$found = array_merge( $found, self::find_blocks( $block['innerBlocks'] ) );
				}
			}

			return $found;
		}
	}
}
