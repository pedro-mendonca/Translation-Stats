<?php
/**
 * Class file for the Translation Stats block REST API controller.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 */

namespace Translation_Stats;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Stats_Block_REST_Controller' ) ) {

	/**
	 * Class Stats_Block_REST_Controller.
	 *
	 * The cached stats of the Translation Stats blocks, to show them when loaded in the background.
	 * Public, as the blocks are on the frontend, and only reads the cache: it never requests translate.wordpress.org.
	 */
	class Stats_Block_REST_Controller extends WP_REST_Controller {


		/**
		 * REST API route of the cached stats, in the Translation Stats namespace.
		 *
		 * @var string
		 */
		const REST_ROUTE = '/block-stats';

		/**
		 * REST API route of the WordPress.org projects search, for the block settings.
		 *
		 * @var string
		 */
		const SEARCH_ROUTE = '/block-projects';

		/**
		 * Number of WordPress.org projects found by the search.
		 *
		 * @var int
		 */
		const SEARCH_RESULTS = 10;


		/**
		 * Get the REST API URL of the cached stats of a block, to show them when loaded in the background.
		 *
		 * @since 2.1.0
		 *
		 * @param Project                                 $project       The plugin or theme project.
		 * @param Locale                                  $locale        The Locale.
		 * @param list<array{slug: string, name: string}> $subprojects   Subprojects.
		 * @param string                                  $block_id      Block ID.
		 *
		 * @return string   REST API URL.
		 */
		public static function refresh_url( $project, $locale, $subprojects, $block_id ) {

			return add_query_arg(
				array(
					'type'        => $project->get_type(),
					'slug'        => $project->get_slug(),
					'language'    => $locale->wp_locale,
					'subprojects' => implode( ',', wp_list_pluck( $subprojects, 'slug' ) ),
					'id'          => $block_id,
				),
				rest_url( Projects_REST_Controller::REST_NAMESPACE . self::REST_ROUTE )
			);
		}

		/**
		 * Register the REST API routes of the cached stats of a block, and of the WordPress.org projects search.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function register_routes() {

			register_rest_route(
				Projects_REST_Controller::REST_NAMESPACE,
				self::REST_ROUTE,
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
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

			register_rest_route(
				Projects_REST_Controller::REST_NAMESPACE,
				self::SEARCH_ROUTE,
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'type'   => array(
							'type'     => 'string',
							'enum'     => array( 'plugins', 'themes' ),
							'required' => true,
						),
						'search' => array(
							'type'      => 'string',
							'minLength' => 2,
							'required'  => true,
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
		public function get_item( $request ) {

			$attributes = array(
				'type'        => $request['type'],
				'slug'        => $request['slug'],
				'language'    => $request['language'],
				'subprojects' => explode( ',', is_string( $request['subprojects'] ) ? $request['subprojects'] : '' ),
			);

			$html    = null;
			$status  = 'queued';
			$project = Stats_Block_Attributes::project( $attributes );
			$locale  = Stats_Block_Attributes::locale( $attributes );

			if ( null !== $project && null !== $locale ) {

				$subprojects = Stats_Block_Attributes::subprojects( $project, $attributes );
				$stats       = Stats_Block_Cache::cached_stats( $project, $locale, $subprojects );

				if ( ! empty( $subprojects ) && is_array( $stats ) ) {
					$html   = Stats_Block::render_stats( $project, $locale, $subprojects, $stats, is_string( $request['id'] ) ? $request['id'] : '' );
					$status = 'cached';
				} else {
					$status = Stats_Block_Cache::loading_status( $project, $locale );
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
		 * Check if the user can search the WordPress.org projects: only in the block editor, for the users who can edit posts.
		 *
		 * @since 2.1.0
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return bool   True if the user can edit posts.
		 */
		public function get_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Parent method parameter.
			return current_user_can( 'edit_posts' );
		}


		/**
		 * Search the plugins or themes on WordPress.org, for the block settings.
		 *
		 * @since 2.1.0
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return WP_REST_Response|WP_Error   Projects found, with their slug and name, or WP_Error if WordPress.org is unavailable.
		 */
		public function get_items( $request ) {

			$search = is_string( $request['search'] ) ? $request['search'] : '';

			if ( 'themes' === $request['type'] ) {
				require_once ABSPATH . 'wp-admin/includes/theme.php';
				$response = themes_api(
					'query_themes',
					array(
						'search'   => $search,
						'per_page' => self::SEARCH_RESULTS,
					)
				);
				$projects = is_object( $response ) && isset( $response->themes ) ? $response->themes : array();
			} else {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				$response = plugins_api(
					'query_plugins',
					array(
						'search'   => $search,
						'per_page' => self::SEARCH_RESULTS,
						'fields'   => array(
							'short_description' => false,
							'icons'             => false,
						),
					)
				);
				$projects = is_object( $response ) && isset( $response->plugins ) ? $response->plugins : array();
			}

			if ( is_wp_error( $response ) ) {
				return new WP_Error(
					'translation_stats_search_unavailable',
					__( 'The projects couldn\'t be searched on WordPress.org. Please try again later.', 'translation-stats' ),
					array( 'status' => 503 )
				);
			}

			return rest_ensure_response( self::search_results( is_array( $projects ) ? $projects : array() ) );
		}


		/**
		 * Get the slug and name of the projects found, with the names decoded as the WordPress.org API encodes them.
		 *
		 * @since 2.1.0
		 *
		 * @param array<mixed> $projects   Projects found on WordPress.org, as arrays or objects.
		 *
		 * @return list<array{value: string, label: string}>   Projects slug and name.
		 */
		private static function search_results( $projects ) {

			$results = array();

			foreach ( $projects as $project ) {

				$project = (array) $project;

				if ( ! isset( $project['slug'], $project['name'] ) || ! is_string( $project['slug'] ) || ! is_string( $project['name'] ) ) {
					continue;
				}

				$results[] = array(
					'value' => $project['slug'],
					'label' => html_entity_decode( $project['name'], ENT_QUOTES, 'UTF-8' ),
				);
			}

			return $results;
		}
	}
}
