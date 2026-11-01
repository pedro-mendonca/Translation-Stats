<?php
/**
 * Class file for the Translation Stats projects REST API controller.
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

if ( ! class_exists( __NAMESPACE__ . '\Projects_REST_Controller' ) ) {

	/**
	 * Class Projects_REST_Controller.
	 *
	 * REST API controller to load the plugins and themes translation stats widget content.
	 */
	class Projects_REST_Controller extends WP_REST_Controller {


		/**
		 * REST API namespace.
		 *
		 * @var string
		 */
		const REST_NAMESPACE = 'translation-stats/v1';

		/**
		 * Capability required to load the translation stats of each project type, to access the screen where they are shown.
		 *
		 * @var array<string, string>
		 */
		const CAPABILITIES = array(
			'plugins' => 'activate_plugins',
			'themes'  => 'switch_themes',
		);

		/**
		 * Plugins instance, to render the plugins translation stats.
		 *
		 * @var Plugins
		 */
		protected $plugins;

		/**
		 * Themes instance, to render the themes translation stats.
		 *
		 * @var Themes
		 */
		protected $themes;


		/**
		 * Constructor.
		 *
		 * @param Plugins $plugins   Plugins instance, to render the plugins translation stats.
		 * @param Themes  $themes    Themes instance, to render the themes translation stats.
		 */
		public function __construct( $plugins, $themes ) {

			$this->plugins   = $plugins;
			$this->themes    = $themes;
			$this->namespace = self::REST_NAMESPACE;
			$this->rest_base = '(?P<type>' . implode( '|', array_keys( self::CAPABILITIES ) ) . ')';
		}


		/**
		 * Register the route to load the project translation stats.
		 *
		 * @since 1.4.0   Registered by Plugins::register_rest_routes().
		 * @since 1.5.0   Moved to the REST API controller, for plugins and themes.
		 *
		 * @return void
		 */
		public function register_routes() {

			register_rest_route(
				self::REST_NAMESPACE,
				'/' . $this->rest_base . '/(?P<slug>[a-z0-9_-]+)/stats',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'type'  => array(
								'description' => __( 'Project type.', 'translation-stats' ),
								'type'        => 'string',
								'enum'        => array_keys( self::CAPABILITIES ),
							),
							'slug'  => array(
								'description' => __( 'Project slug.', 'translation-stats' ),
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
		 * Check if the user can access the screen where the project translation stats are shown.
		 *
		 * @since 1.4.0   Plugins::rest_permission_check().
		 * @since 1.5.0   Moved to the REST API controller, with the capability of each project type.
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return true|WP_Error   True if the user can access the projects, otherwise WP_Error.
		 */
		public function get_item_permissions_check( $request ) {

			$type = $request['type'];

			if ( ! is_string( $type ) || ! isset( self::CAPABILITIES[ $type ] ) || ! current_user_can( self::CAPABILITIES[ $type ] ) ) {
				return new WP_Error(
					'rest_forbidden',
					__( 'Sorry, you are not allowed to load these translation stats.', 'translation-stats' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

			return true;
		}


		/**
		 * Load the project translation stats widget content.
		 *
		 * @since 0.9.4
		 * @since 1.2.0   Renamed from tstats_stats_plugin_widget_content_load() to plugin_widget_content_load().
		 * @since 1.4.0   Use the REST API instead of Ajax, renamed to rest_plugin_widget_content().
		 * @since 1.5.0   Moved to the REST API controller, renamed to get_item(), for plugins and themes.
		 *
		 * @param WP_REST_Request $request   The REST API request.
		 *
		 * @return WP_REST_Response|WP_Error   Response with the widget content HTML, or WP_Error if translate.WordPress.org is unavailable.
		 */
		public function get_item( $request ) {

			$html = '';

			$locale = Translations_API::locale( Utils::translation_language() );

			if ( ! is_null( $locale ) ) {

				$slug  = is_string( $request['slug'] ) ? $request['slug'] : '';
				$force = (bool) $request['force'];

				ob_start();
				if ( 'themes' === $request['type'] ) {
					$result = $this->themes->theme_widget_content_stats( $slug, $locale, $force );
				} else {
					$result = $this->plugins->plugin_widget_content_stats( $slug, $locale, $force );
				}
				$html = (string) ob_get_clean();

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}

			return rest_ensure_response( array( 'html' => $html ) );
		}


		/**
		 * Get the project translation stats response schema.
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
				'title'      => 'translation-stats-project-stats',
				'type'       => 'object',
				'properties' => array(
					'html' => array(
						'description' => __( 'Translation stats widget content HTML.', 'translation-stats' ),
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
