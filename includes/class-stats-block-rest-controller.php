<?php
/**
 * Class file for the Translation Stats block REST API controller.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 */

namespace Translation_Stats;

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
		 * Register the REST API route of the cached stats of a block.
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
	}
}
