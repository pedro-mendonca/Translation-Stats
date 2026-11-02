<?php
/**
 * Class file for the Translation Stats block cache and background loading.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 */

namespace Translation_Stats;

use WP_Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Stats_Block_Cache' ) ) {

	/**
	 * Class Stats_Block_Cache.
	 *
	 * Visitors only get the cached stats of the Translation Stats blocks: the missing stats are loaded in the background, with WP-Cron,
	 * one project at a time, as in the admin widgets queue.
	 *
	 * @phpstan-import-type BlockAttributes from Stats_Block_Attributes
	 */
	class Stats_Block_Cache {


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
		 * Constructor.
		 */
		public function __construct() {

			// Load the project stats in the background.
			add_action( self::REFRESH_HOOK, array( $this, 'refresh' ), 10, 3 );

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

			$project = Stats_Block_Attributes::project( $attributes );
			$locale  = Stats_Block_Attributes::locale( $attributes );

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
		private static function next_refresh() {

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

			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! has_block( Stats_Block::NAME, $post ) ) {
				return;
			}

			foreach ( self::find_blocks( parse_blocks( $post->post_content ) ) as $attributes ) {

				$project = Stats_Block_Attributes::project( $attributes );
				$locale  = Stats_Block_Attributes::locale( $attributes );

				if ( null !== $project && null !== $locale && null === self::cached_stats( $project, $locale, Stats_Block_Attributes::subprojects( $project, $attributes ) ) ) {
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
		private static function find_blocks( $blocks ) {

			$found = array();

			foreach ( $blocks as $block ) {

				if ( ! is_array( $block ) ) {
					continue;
				}

				if ( Stats_Block::NAME === ( $block['blockName'] ?? null ) ) {
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
