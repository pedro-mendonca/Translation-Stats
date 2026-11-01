<?php
/**
 * Class Project Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Plugin_Project;
use Translation_Stats\Theme_Project;
use Translation_Stats\Translations_API;


/**
 * Project test case, for plugins and themes translation projects.
 */
class Test_Project extends Translation_Stats_TestCase {


	/**
	 * Skip the test if translate.WordPress.org API is unavailable, rate limited or failing.
	 *
	 * @param array|WP_Error $response   The API response.
	 */
	private function skip_if_api_unavailable( $response ) {

		if ( is_wp_error( $response ) ) {
			$this->markTestSkipped( 'Translate WordPress API request failed: ' . $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 429 === $response_code || $response_code >= 500 ) {
			$this->markTestSkipped( 'Translate WordPress API unavailable: HTTP ' . $response_code );
		}
	}


	/**
	 * Test the plugin project type, subprojects and paths.
	 */
	public function test_plugin_project() {

		$project = new Plugin_Project( 'translation-stats' );
		$locale  = $this->get_locale( 'pt_PT_ao90', 'pt/ao90', 'pt' );

		$this->assertSame( 'plugins', $project->get_type() );
		$this->assertSame( 'translation-stats', $project->get_slug() );
		$this->assertSame( Translations_API::plugin_subprojects(), $project->get_subprojects() );
		$this->assertSame( 'translation-stats', $project->get_path() );
		$this->assertSame( 'translation-stats/stable', $project->get_path( 'stable' ) );
		$this->assertSame( 'https://translate.wordpress.org/projects/wp-plugins/translation-stats/stable/pt/ao90', $project->translate_url( 'stable', $locale ) );
		$this->assertSame( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT_ao90', $project->subproject_stats_transient( $locale, 'stable' ) );
	}


	/**
	 * Test the theme project type, without subprojects, and its transients don't conflict with a plugin with the same slug.
	 */
	public function test_theme_project() {

		$project = new Theme_Project( 'twentytwentyfive' );
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$this->assertSame( 'themes', $project->get_type() );
		$this->assertSame( array( '' ), wp_list_pluck( $project->get_subprojects(), 'slug' ) );
		$this->assertSame( 'twentytwentyfive', $project->get_path( '' ) );
		$this->assertSame( 'https://translate.wordpress.org/projects/wp-themes/twentytwentyfive/pt/default', $project->translate_url( '', $locale ) );
		$this->assertSame( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'theme_twentytwentyfive_pt_PT', $project->subproject_stats_transient( $locale, '' ) );
		$this->assertNotSame( ( new Plugin_Project( 'twentytwentyfive' ) )->subproject_stats_transient( $locale, '' ), $project->subproject_stats_transient( $locale, '' ) );
	}


	/**
	 * Test get the project from translate.WordPress.org API.
	 *
	 * @group wordpress-translate-api
	 */
	public function test_api_get() {

		// Existent plugin slug in Translating WordPress.
		$response = ( new Plugin_Project( 'translation-stats' ) )->api_get();

		$this->skip_if_api_unavailable( $response );

		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );

		// Non-existent plugin slug in Translating WordPress.
		$response = ( new Plugin_Project( 'wrong-slug-translation-stats' ) )->api_get();

		$this->skip_if_api_unavailable( $response );

		$this->assertSame( 404, wp_remote_retrieve_response_code( $response ) );

		// Existent theme slug in Translating WordPress.
		$response = ( new Theme_Project( 'twentytwentyfive' ) )->api_get();

		$this->skip_if_api_unavailable( $response );

		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
	}


	/**
	 * Test get the project and subprojects from a mocked translate.WordPress.org API.
	 */
	public function test_api_get_mocked() {

		$this->mock_translate_api( 200 );

		( new Plugin_Project( 'translation-stats' ) )->api_get();
		( new Plugin_Project( 'translation-stats' ) )->api_get( 'stable' );
		( new Theme_Project( 'twentytwentyfive' ) )->api_get();

		$this->assertSame(
			array(
				'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats',
				'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable',
				'https://translate.wordpress.org/api/projects/wp-themes/twentytwentyfive',
			),
			$this->requested_urls
		);
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_on_translate_wporg() {
		return array(
			'found'        => array(
				'response'          => 200,
				'expected_result'   => true,
				'expected_requests' => 1,
			),
			'not found'    => array(
				'response'          => 404,
				'expected_result'   => false,
				'expected_requests' => 1,
			),
			'rate limited' => array(
				'response'          => 429,
				'expected_result'   => true,
				'expected_requests' => 2,
			),
			'server error' => array(
				'response'          => 503,
				'expected_result'   => true,
				'expected_requests' => 2,
			),
			'error'        => array(
				'response'          => new WP_Error( 'http_request_failed', 'Connection timed out.' ),
				'expected_result'   => true,
				'expected_requests' => 2,
			),
		);
	}


	/**
	 * Test if the translation project exists on translate.WordPress.org, and is cached in a transient.
	 * If translate.WordPress.org is unavailable, the project is assumed to exist and the result isn't cached.
	 *
	 * @dataProvider provide_test_on_translate_wporg
	 *
	 * @param int|WP_Error $response            The mocked API response.
	 * @param bool         $expected_result     The expected result for the test.
	 * @param int          $expected_requests   The expected requests after checking twice.
	 */
	public function test_on_translate_wporg( $response, $expected_result, $expected_requests ) {

		$this->mock_translate_api( $response );

		$project = new Plugin_Project( 'translation-stats' );

		$this->assertSame( $expected_result, $project->on_translate_wporg() );
		$this->assertSame( array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats' ), $this->requested_urls );

		// The second check uses the transient, without a new request, unless the API was unavailable.
		$project->on_translate_wporg();

		$this->assertCount( $expected_requests, $this->requested_urls );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_subproject_stats() {
		return array(
			'root locale'          => array(
				'wp_locale'       => 'pt_PT',
				'slug'            => 'pt',
				'root_slug'       => null,
				'expected_result' => 95,
			),
			'variant locale'       => array(
				'wp_locale'       => 'pt_PT_ao90',
				'slug'            => 'pt/ao90',
				'root_slug'       => 'pt',
				'expected_result' => 80,
			),
			'locale without stats' => array(
				'wp_locale'       => 'pt_BR',
				'slug'            => 'pt-br',
				'root_slug'       => null,
				'expected_result' => false,
			),
		);
	}


	/**
	 * Test get the subproject stats for the Locale translation set.
	 *
	 * @dataProvider provide_test_subproject_stats
	 *
	 * @param string      $wp_locale         WordPress Locale.
	 * @param string      $slug              Locale slug.
	 * @param string|null $root_slug         Root Locale slug, for variants.
	 * @param int|false   $expected_result   Expected translated percentage, or false if no stats.
	 */
	public function test_subproject_stats( $wp_locale, $slug, $root_slug, $expected_result ) {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Plugin_Project( 'translation-stats' );

		$stats = $project->subproject_stats( $this->get_locale( $wp_locale, $slug, $root_slug ), 'stable', false );

		$this->assertSame( array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable' ), $this->requested_urls );

		if ( false === $expected_result ) {
			$this->assertFalse( $stats );
		} else {
			$this->assertSame( $expected_result, $stats->percent_translated );
		}
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_subproject_stats_not_found() {
		return array(
			'no translation sets' => array(
				'response' => 200,
				'body'     => array( 'translation_sets' => array() ),
			),
			'not found'           => array(
				'response' => 404,
				'body'     => '',
			),
		);
	}


	/**
	 * Test get the subproject stats when there are no stats for the subproject, also when cached.
	 *
	 * @dataProvider provide_test_subproject_stats_not_found
	 *
	 * @param int|WP_Error $response   The mocked API response.
	 * @param mixed        $body       The mocked API response body.
	 */
	public function test_subproject_stats_not_found( $response, $body ) {

		$this->mock_translate_api( $response, $body );

		$project = new Plugin_Project( 'translation-stats' );
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$this->assertFalse( $project->subproject_stats( $locale, 'stable', false ) );

		// Cached.
		$this->assertFalse( $project->subproject_stats( $locale, 'stable', false ) );
		$this->assertCount( 1, $this->requested_urls );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_subproject_stats_api_unavailable() {
		return array(
			'rate limited' => array(
				'response' => 429,
			),
			'server error' => array(
				'response' => 503,
			),
			'error'        => array(
				'response' => new WP_Error( 'http_request_failed', 'Connection timed out.' ),
			),
		);
	}


	/**
	 * Test get the subproject stats when translate.WordPress.org is unavailable returns an error, which isn't cached.
	 *
	 * @dataProvider provide_test_subproject_stats_api_unavailable
	 *
	 * @param int|WP_Error $response   The mocked API response.
	 */
	public function test_subproject_stats_api_unavailable( $response ) {

		$this->mock_translate_api( $response );

		$project = new Plugin_Project( 'translation-stats' );

		$stats = $project->subproject_stats( $this->get_locale( 'pt_PT', 'pt' ), 'stable', false );

		$this->assertWPError( $stats );
		$this->assertSame( 503, $stats->get_error_data()['status'] );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
	}


	/**
	 * Test the subproject stats are cached in a transient, unless forcing the update.
	 */
	public function test_subproject_stats_cache() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Plugin_Project( 'translation-stats' );
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$project->subproject_stats( $locale, 'stable', false );

		$this->assertSame( 95, get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' )->percent_translated );

		// The second request uses the transient.
		$stats = $project->subproject_stats( $locale, 'stable', false );

		$this->assertSame( 95, $stats->percent_translated );
		$this->assertCount( 1, $this->requested_urls );

		// Forcing the update ignores the transient.
		$project->subproject_stats( $locale, 'stable', true );

		$this->assertCount( 2, $this->requested_urls );
	}


	/**
	 * Test get the stats of all the plugin subprojects, cached.
	 */
	public function test_stats() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Plugin_Project( 'translation-stats' );
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$this->assertFalse( $project->stats_cached( $locale ) );

		$stats = $project->stats( $locale, false );

		$this->assertSame( array( 'dev', 'dev-readme', 'stable', 'stable-readme' ), array_keys( $stats ) );
		$this->assertSame( 95, $stats['dev-readme']->percent_translated );
		$this->assertCount( 4, $this->requested_urls );
		$this->assertTrue( $project->stats_cached( $locale ) );
	}


	/**
	 * Test get the stats of the theme project, from the project itself.
	 */
	public function test_stats_theme() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Theme_Project( 'twentytwentyfive' );
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$stats = $project->stats( $locale, false );

		$this->assertSame( array( '' ), array_keys( $stats ) );
		$this->assertSame( 95, $stats['']->percent_translated );
		$this->assertSame( array( 'https://translate.wordpress.org/api/projects/wp-themes/twentytwentyfive' ), $this->requested_urls );
		$this->assertTrue( $project->stats_cached( $locale ) );
	}


	/**
	 * Test get the stats returns the error if translate.WordPress.org is unavailable, without the other subprojects stats.
	 */
	public function test_stats_api_unavailable() {

		$this->mock_translate_api( 503 );

		$project = new Plugin_Project( 'translation-stats' );

		$this->assertWPError( $project->stats( $this->get_locale( 'pt_PT', 'pt' ), false ) );
		$this->assertCount( 1, $this->requested_urls );
	}
}
