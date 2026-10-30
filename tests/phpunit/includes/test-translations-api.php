<?php
/**
 * Class Translations_API Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Translations_API;


/**
 * Translations_API test case.
 */
class Test_Translations_API extends Translation_Stats_TestCase {


	/**
	 * Test get plugin slug from its file path.
	 */
	public function test_get_plugin_slug() {

		// Given a complete file path with folder and file.
		$plugin_slug = Translations_API::get_plugin_slug( 'translation-stats/translation-stats.php' );

		$this->assertSame( $plugin_slug, 'translation-stats' );

		// Given just the file name and extension.
		$plugin_slug = Translations_API::get_plugin_slug( 'translation-stats.php' );

		$this->assertSame( $plugin_slug, 'translation-stats' );

		// Given just a slug.
		$plugin_slug = Translations_API::get_plugin_slug( 'Translation Stats' );

		$this->assertSame( $plugin_slug, 'translation-stats' );

		$plugin_slug = Translations_API::get_plugin_slug( 'translation-stats' );

		$this->assertSame( $plugin_slug, 'translation-stats' );
	}


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
	 * Test get plugin data from translate.WordPress.org API.
	 *
	 * @group wordpress-translate-api
	 */
	public function test_translations_api_get_plugin() {

		// Existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'translation-stats' );

		$this->skip_if_api_unavailable( $response );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertSame( $response_code, 200 );

		// Non-existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'wrong-slug-translation-stats' );

		$this->skip_if_api_unavailable( $response );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertSame( $response_code, 404 );
	}


	/**
	 * Test get plugin data from a mocked translate.WordPress.org API.
	 */
	public function test_translations_api_get_plugin_mocked() {

		// Existent plugin slug in Translating WordPress.
		$this->mock_translate_api( 200 );

		$response = Translations_API::translations_api_get_plugin( 'translation-stats' );

		$this->assertSame( wp_remote_retrieve_response_code( $response ), 200 );
		$this->assertSame( $this->requested_urls, array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats' ) );

		// Non-existent plugin slug in Translating WordPress.
		$this->mock_translate_api( 404 );

		$response = Translations_API::translations_api_get_plugin( 'wrong-slug-translation-stats' );

		$this->assertSame( wp_remote_retrieve_response_code( $response ), 404 );
		$this->assertSame( $this->requested_urls, array( 'https://translate.wordpress.org/api/projects/wp-plugins/wrong-slug-translation-stats' ) );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_translate_api_responses() {
		return array(
			'found'     => array(
				'response'        => 200,
				'expected_result' => true,
			),
			'not found' => array(
				'response'        => 404,
				'expected_result' => false,
			),
			'error'     => array(
				'response'        => new WP_Error( 'http_request_failed', 'Connection timed out.' ),
				'expected_result' => false,
			),
		);
	}


	/**
	 * Test if the translation project exists on translate.WordPress.org, and is cached in a transient.
	 *
	 * @dataProvider provide_test_translate_api_responses
	 *
	 * @param int|WP_Error $response          The mocked API response.
	 * @param bool         $expected_result   The expected result for the test.
	 */
	public function test_plugin_project_on_translate_wporg( $response, $expected_result ) {

		$this->mock_translate_api( $response );

		$on_wporg = Translations_API::plugin_project_on_translate_wporg( 'translation-stats' );

		$this->assertSame( $on_wporg, $expected_result );
		$this->assertSame( $this->requested_urls, array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats' ) );

		// The second check uses the transient, without a new request.
		Translations_API::plugin_project_on_translate_wporg( 'translation-stats' );

		$this->assertCount( 1, $this->requested_urls );
	}


	/**
	 * Test if the translation subproject exists on translate.WordPress.org, and is cached in a transient.
	 *
	 * @dataProvider provide_test_translate_api_responses
	 *
	 * @param int|WP_Error $response          The mocked API response.
	 * @param bool         $expected_result   The expected result for the test.
	 */
	public function test_plugin_subproject_on_translate_wporg( $response, $expected_result ) {

		$this->mock_translate_api( $response );

		$translations_api = new Translations_API();

		$on_wporg = $translations_api->plugin_subproject_on_translate_wporg( 'translation-stats', 'stable' );

		$this->assertSame( $on_wporg, $expected_result );
		$this->assertSame( $this->requested_urls, array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable' ) );

		// The second check uses the transient, without a new request.
		$translations_api->plugin_subproject_on_translate_wporg( 'translation-stats', 'stable' );

		$this->assertCount( 1, $this->requested_urls );
	}


	/**
	 * Test the translate.wordpress.org plugins subprojects structure with 'slug' and 'name'.
	 */
	public function test_plugin_subprojects() {

		$plugin_subprojects = Translations_API::plugin_subprojects();

		$expected = array(
			array(
				'slug' => 'dev',
				/* translators: Subproject name in translate.wordpress.org, do not translate! */
				'name' => _x( 'Development', 'Subproject name', 'translation-stats' ),
			),
			array(
				'slug' => 'dev-readme',
				/* translators: Subproject name in translate.wordpress.org, do not translate! */
				'name' => _x( 'Development Readme', 'Subproject name', 'translation-stats' ),
			),
			array(
				'slug' => 'stable',
				/* translators: Subproject name in translate.wordpress.org, do not translate! */
				'name' => _x( 'Stable', 'Subproject name', 'translation-stats' ),
			),
			array(
				'slug' => 'stable-readme',
				/* translators: Subproject name in translate.wordpress.org, do not translate! */
				'name' => _x( 'Stable Readme', 'Subproject name', 'translation-stats' ),
			),
		);

		$this->assertSame(
			$plugin_subprojects,
			$expected
		);
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_translate_url() {
		return array(
			// No API.
			array(
				'project'         => 'languages',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/languages/',
			),
			array(
				'project'         => 'wp',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/projects/wp/',
			),
			array(
				'project'         => 'plugins',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/projects/wp-plugins/',
			),
			array(
				'project'         => 'themes',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/projects/wp-themes/',
			),
			array(
				'project'         => 'other',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/',
			),
			array(
				'project'         => '',
				'api'             => false,
				'expected_result' => 'https://translate.wordpress.org/',
			),
			// No API.
			array(
				'project'         => 'languages',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/languages/',
			),
			array(
				'project'         => 'wp',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/projects/wp/',
			),
			array(
				'project'         => 'plugins',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/projects/wp-plugins/',
			),
			array(
				'project'         => 'themes',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/projects/wp-themes/',
			),
			array(
				'project'         => 'other',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/',
			),
			array(
				'project'         => '',
				'api'             => true,
				'expected_result' => 'https://translate.wordpress.org/api/',
			),
		);
	}


	/**
	 * Test the translate site URL.
	 *
	 * @dataProvider provide_test_translate_url
	 *
	 * @param string $project           The project.
	 * @param bool   $api               Wether to get an API URL.
	 * @param string $expected_result   The expected result for the test.
	 */
	public function test_translate_url( $project, $api, $expected_result ) {

		$translate_url = Translations_API::translate_url( $project, $api );

		$this->assertSame(
			$translate_url,
			$expected_result
		);
	}
}
