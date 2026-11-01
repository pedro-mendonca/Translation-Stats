<?php
/**
 * Class file for the Translation Stats Plugins REST API controller.
 *
 * @package Translation_Stats
 *
 * @since 1.5.0
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

if ( ! class_exists( __NAMESPACE__ . '\Plugins_REST_Controller' ) ) {

	/**
	 * Class Plugins_REST_Controller.
	 *
	 * REST API controller to load the plugins translation stats widget content.
	 */
	class Plugins_REST_Controller extends WP_REST_Controller {


		/**
		 * REST API namespace.
		 *
		 * @var string
		 */
		const REST_NAMESPACE = 'translation-stats/v1';

		/**
		 * REST API base route.
		 *
		 * @var string
		 */
		const REST_BASE = 'plugins';

		/**
		 * Plugins instance, to render the plugins translation stats.
		 *
		 * @var Plugins
		 */
		protected $plugins;


		/**
		 * Constructor.
		 *
		 * @param Plugins $plugins   Plugins instance, to render the plugins translation stats.
		 */
		public function __construct( $plugins ) {

			$this->plugins   = $plugins;
			$this->namespace = self::REST_NAMESPACE;
			$this->rest_base = self::REST_BASE;
		}


		/**
		 * Register the route to load the plugin translation stats.
		 *
		 * @since 1.4.0   Registered by Plugins::register_rest_routes().
		 * @since 1.5.0   Moved to the REST API controller.
		 *
		 * @return void
		 */
		public function register_routes() {

			register_rest_route(
				self::REST_NAMESPACE,
				'/' . self::REST_BASE . '/(?P<slug>[a-z0-9_-]+)/stats',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'slug'  => array(
								'description' => __( 'Plugin slug.', 'translation-stats' ),
								'type'        => 'string',
							),
							'force' => array(
								'description' => __( 'Force getting new stats instead of the cached ones.', 'translation-stats' ),
								'type'        => 'boolean',
								'default'     => false,
							),
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}


		/**
		 * Check if the user can access the plugins, where the plugin translation stats are shown.
		 *
		 * @since 1.4.0   Plugins::rest_permission_check().
		 * @since 1.5.0   Moved to the REST API controller.
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return true|WP_Error   True if the user can access the plugins, otherwise WP_Error.
		 */
		public function get_item_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Required by the WP_REST_Controller method signature.

			if ( ! current_user_can( 'activate_plugins' ) ) {
				return new WP_Error(
					'rest_forbidden',
					__( 'Sorry, you are not allowed to load the plugins translation stats.', 'translation-stats' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

			return true;
		}


		/**
		 * Load the plugin translation stats widget content.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_stats_plugin_widget_content_load() to plugin_widget_content_load().
		 * @since 1.4.0   Use the REST API instead of Ajax, renamed to rest_plugin_widget_content().
		 * @since 1.5.0   Moved to the REST API controller, renamed to get_item().
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return WP_REST_Response|WP_Error   Response with the plugin widget content HTML, or WP_Error if translate.WordPress.org is unavailable.
		 */
		public function get_item( $request ) {

			$html = '';

			$locale = Translations_API::locale( Utils::translation_language() );

			if ( ! is_null( $locale ) ) {

				ob_start();
				$result = $this->plugins->plugin_widget_content_stats( $request['slug'], $locale, $request['force'] );
				$html   = (string) ob_get_clean();

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}

			return rest_ensure_response( array( 'html' => $html ) );
		}


		/**
		 * Get the plugin translation stats response schema.
		 *
		 * @since 1.5.0
		 *
		 * @return array<string, mixed>   Response schema.
		 */
		public function get_item_schema() {

			if ( $this->schema ) {
				return $this->add_additional_fields_schema( $this->schema );
			}

			$this->schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'translation-stats-plugin-stats',
				'type'       => 'object',
				'properties' => array(
					'html' => array(
						'description' => __( 'Plugin translation stats widget content HTML.', 'translation-stats' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
				),
			);

			return $this->add_additional_fields_schema( $this->schema );
		}
	}
}
