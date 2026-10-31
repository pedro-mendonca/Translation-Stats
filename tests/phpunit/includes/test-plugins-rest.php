<?php
/**
 * Class Plugins REST API Test.
 *
 * @package Translation_Stats
 */

/**
 * Plugins REST API test case.
 */
class Test_Plugins_REST extends Translation_Stats_TestCase {


	/**
	 * The plugin translation stats route.
	 *
	 * @var string
	 */
	const ROUTE = '/translation-stats/v1/plugins/translation-stats/stats';


	/**
	 * Set up the REST server, the settings and the API mock for each test.
	 */
	public function set_up() {
		parent::set_up();

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );

		$this->set_settings(
			array( 'translation_language' => 'pt_PT' ),
			array(
				'translation-stats' => array(
					'enabled' => 'true',
					'stable'  => 'true',
				),
			)
		);

		$this->mock_translate_api(
			200,
			array(
				'translation_sets' => array(
					array(
						'locale'             => 'pt',
						'slug'               => 'default',
						'percent_translated' => 95,
						'current_count'      => 95,
						'waiting_count'      => 0,
						'fuzzy_count'        => 0,
						'untranslated_count' => 5,
					),
				),
			)
		);
	}


	/**
	 * Reset the REST server after each test.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}


	/**
	 * Log in a user with a role, as super admin on multisite for administrators.
	 *
	 * @param string $role   User role.
	 */
	private function login( $role ) {

		$user_id = self::factory()->user->create( array( 'role' => $role ) );

		if ( is_multisite() && 'administrator' === $role ) {
			grant_super_admin( $user_id );
		}

		wp_set_current_user( $user_id );
	}


	/**
	 * Request the plugin translation stats.
	 *
	 * @param string $route   The route to request.
	 * @param array  $params  The request query parameters.
	 *
	 * @return WP_REST_Response  The response.
	 */
	private function request( $route = self::ROUTE, $params = array() ) {

		$request = new WP_REST_Request( 'GET', $route );
		$request->set_query_params( $params );

		return rest_do_request( $request );
	}


	/**
	 * Test the route is registered.
	 */
	public function test_register_route() {

		$this->assertArrayHasKey( '/translation-stats/v1/plugins/(?P<slug>[a-z0-9_-]+)/stats', rest_get_server()->get_routes() );
	}


	/**
	 * Test the route is registered by the REST controller, and the plugins don't register it.
	 */
	public function test_register_route_controller() {

		$translation_stats = new Translation_Stats\Translation_Stats();

		$this->assertSame( 10, has_action( 'rest_api_init', array( $translation_stats, 'register_rest_routes' ) ) );
		$this->assertFalse( has_action( 'rest_api_init', array( new Translation_Stats\Plugins(), 'register_rest_routes' ) ) );
		$this->assertInstanceOf( WP_REST_Controller::class, new Translation_Stats\Plugins_REST_Controller( new Translation_Stats\Plugins() ) );
	}


	/**
	 * Test the route schema describes the response.
	 */
	public function test_route_schema() {

		$response = rest_do_request( new WP_REST_Request( 'OPTIONS', self::ROUTE ) );

		$schema = $response->get_data()['schema'];

		$this->assertSame( 'translation-stats-plugin-stats', $schema['title'] );
		$this->assertSame( 'string', $schema['properties']['html']['type'] );
		$this->assertTrue( $schema['properties']['html']['readonly'] );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_request_denied() {
		return array(
			'logged out'                  => array(
				'role'   => null,
				'status' => 401,
			),
			'user without plugins access' => array(
				'role'   => 'subscriber',
				'status' => 403,
			),
		);
	}


	/**
	 * Test the request is denied without permission, before requesting the API.
	 *
	 * @dataProvider provide_test_request_denied
	 *
	 * @param string|null $role     User role, or null if logged out.
	 * @param int         $status   Expected response status.
	 */
	public function test_request_denied( $role, $status ) {

		if ( null !== $role ) {
			$this->login( $role );
		}

		$response = $this->request();

		$this->assertSame( $status, $response->get_status() );
		$this->assertSame( array(), $this->requested_urls );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
	}


	/**
	 * Test the request with an invalid plugin slug doesn't match the route.
	 */
	public function test_request_invalid_slug() {

		$this->login( 'administrator' );

		$response = $this->request( '/translation-stats/v1/plugins/Translation%20Stats/stats' );

		$this->assertSame( 404, $response->get_status() );
		$this->assertSame( array(), $this->requested_urls );
	}


	/**
	 * Test the plugin translation stats are returned with permission.
	 */
	public function test_request_allowed() {

		$this->login( 'administrator' );

		$response = $this->request();

		$this->assertSame( 200, $response->get_status() );
		$this->assertContains( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable', $this->requested_urls );
		$this->assertStringContainsString( 'translation-stats-content-stats', $response->get_data()['html'] );
		$this->assertStringContainsString( '95%', $response->get_data()['html'] );
	}


	/**
	 * Test the request returns an error when translate.WordPress.org is unavailable, without caching it.
	 */
	public function test_request_api_unavailable() {

		$this->login( 'administrator' );

		$this->mock_translate_api( 429 );

		$response = $this->request();

		$this->assertSame( 503, $response->get_status() );
		$this->assertSame( 'translation_stats_api_unavailable', $response->get_data()['code'] );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
	}


	/**
	 * Test the stats are cached, unless forcing the update.
	 */
	public function test_request_force_update() {

		$this->login( 'administrator' );

		// Count only the stats requests, not the core available translations requests.
		$count_stats_requests = function () {
			return count( preg_grep( '#^https://translate\.wordpress\.org/#', $this->requested_urls ) );
		};

		$this->request();

		$requests = $count_stats_requests();

		$this->assertGreaterThan( 0, $requests );

		// Cached stats.
		$this->request( self::ROUTE, array( 'force' => 'false' ) );

		$this->assertSame( $requests, $count_stats_requests() );

		// Forced update.
		$this->request( self::ROUTE, array( 'force' => 'true' ) );

		$this->assertSame( 2 * $requests, $count_stats_requests() );
	}


	/**
	 * Test the plugins page script uses the REST API, without the Ajax nonce.
	 */
	public function test_plugins_page_script() {

		$translation_stats = new Translation_Stats\Translation_Stats();
		$translation_stats->register_plugin_scripts( 'plugins.php' );

		$this->assertContains( 'wp-api-fetch', wp_scripts()->registered['translation-stats-plugins']->deps );
		$this->assertStringNotContainsString( 'nonce', (string) wp_scripts()->get_data( 'translation-stats-plugins', 'data' ) );
	}
}
