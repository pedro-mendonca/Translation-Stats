<?php
/**
 * Class Translations_API Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Translations_API;


/**
 * Translations_API test case.
 */
class Test_Translations_API extends WP_UnitTestCase {


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
	 * Test get plugin data from translate.WordPress.org API.
	 *
	 * @group wordpress-translate-api
	 */
	public function test_translations_api_get_plugin() {

		// Existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'translation-stats' );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertSame( $response_code, 200 );

		// Non-existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'wrong-slug-translation-stats' );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertSame( $response_code, 404 );

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
	 */
	public function test_translate_url( $project, $api, $expected_result ) {

		$translate_url = Translations_API::translate_url( $project, $api );

		$this->assertSame(
			$translate_url,
			$expected_result
		);

	}

}
