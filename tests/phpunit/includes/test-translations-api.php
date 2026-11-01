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


	/**
	 * Test get the plugin metadata from the plugins updates transient, with or without available update, or from the plugin file if not on WordPress.org.
	 */
	public function test_plugin_metadata() {

		$update_plugins = new stdClass();

		$update_plugins->response  = array(
			'akismet/akismet.php' => (object) array(
				'id'          => 'w.org/plugins/akismet',
				'slug'        => 'akismet',
				'new_version' => '99.0',
			),
		);
		$update_plugins->no_update = array(
			'hello.php' => (object) array(
				'id'   => 'w.org/plugins/hello-dolly',
				'slug' => 'hello-dolly',
			),
		);

		set_site_transient( 'update_plugins', $update_plugins );

		// Plugin with available update.
		$this->assertTrue( Translations_API::plugin_on_wporg( 'akismet/akismet.php' ) );
		$this->assertSame( '99.0', Translations_API::plugin_metadata( 'akismet/akismet.php', 'new_version' ) );

		// Plugin without available update.
		$this->assertSame( 'hello-dolly', Translations_API::plugin_metadata( 'hello.php', 'slug' ) );
		$this->assertNull( Translations_API::plugin_metadata( 'hello.php', 'new_version' ) );

		// Plugin not on WordPress.org, with the slug from the plugin file.
		$this->assertFalse( Translations_API::plugin_on_wporg( 'other/other.php' ) );
		$this->assertSame( 'other', Translations_API::plugin_metadata( 'other/other.php', 'slug' ) );
		$this->assertSame( '', Translations_API::plugin_metadata( 'other/other.php', 'new_version' ) );
	}
}
