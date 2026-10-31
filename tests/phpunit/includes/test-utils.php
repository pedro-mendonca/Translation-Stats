<?php
/**
 * Class Utils Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Utils;


/**
 * Utils test case.
 */
class Test_Utils extends WP_UnitTestCase {

	/**
	 * Test developmente mode.
	 */
	public function test_is_development_mode() {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			$this->assertTrue( Utils::is_development_mode() );
		} else {
			$this->assertFalse( Utils::is_development_mode() );
		}
	}

	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_get_build_asset() {
		return array(
			'plugins script'  => array(
				'name'                  => 'js/admin-plugins',
				'extension'             => 'js',
				'expected_url'          => TRANSLATION_STATS_DIR_URL . 'assets/build/js/admin-plugins.js',
				'expected_dependencies' => array( 'jquery', 'wp-api-fetch', 'wp-i18n' ),
			),
			'settings script' => array(
				'name'                  => 'js/admin-settings',
				'extension'             => 'js',
				'expected_url'          => TRANSLATION_STATS_DIR_URL . 'assets/build/js/admin-settings.js',
				'expected_dependencies' => array( 'jquery' ),
			),
			'style'           => array(
				'name'                  => 'css/admin',
				'extension'             => 'css',
				'expected_url'          => TRANSLATION_STATS_DIR_URL . 'assets/build/css/admin.css',
				'expected_dependencies' => array(),
			),
		);
	}


	/**
	 * Test get the built asset URL, with the dependencies and the version from its asset file.
	 *
	 * @dataProvider provide_test_get_build_asset
	 *
	 * @param string $name                   The built asset name.
	 * @param string $extension              The built asset extension.
	 * @param string $expected_url           The expected URL.
	 * @param array  $expected_dependencies   The expected dependencies.
	 */
	public function test_get_build_asset( $name, $extension, $expected_url, $expected_dependencies ) {

		$asset = Utils::get_build_asset( $name, $extension );

		$asset_file = require TRANSLATION_STATS_DIR_PATH . 'assets/build/' . $name . '.asset.php';

		$this->assertSame( $expected_url, $asset['url'] );
		$this->assertSame( $expected_dependencies, $asset['dependencies'] );
		$this->assertSame( $asset_file['version'], $asset['version'] );
	}


	/**
	 * Test get a built asset without asset file uses no dependencies and the plugin version.
	 */
	public function test_get_build_asset_without_asset_file() {

		$this->assertSame(
			array(
				'url'          => TRANSLATION_STATS_DIR_URL . 'assets/build/js/missing.js',
				'dependencies' => array(),
				'version'      => TRANSLATION_STATS_VERSION,
			),
			Utils::get_build_asset( 'js/missing', 'js' )
		);
	}


	/**
	 * Test translation language.
	 */
	public function test_translation_language() {

		/**
		 * Test default translation language without any settings.
		 */

		// Get the Translation Stats configured language.
		$translationstats_language = Utils::translation_language();

		$this->assertEquals(
			$translationstats_language,
			'en_US'
		);

		/**
		 * Test translation language set as 'site-default'.
		 */

		// Configure translation language.
		$settings = array(
			'settings' => array(
				'translation_language' => 'site-default',
			),
		);

		// Add plugin setting to the database.
		add_option( TRANSLATION_STATS_WP_OPTION, $settings );

		// Get the Translation Stats configured language.
		$translationstats_language = Utils::translation_language();

		$this->assertEquals(
			$translationstats_language,
			'en_US'
		);

		/**
		 * Test translation language configured in the settings.
		 */

		// Configure translation language.
		$settings = array(
			'settings' => array(
				'translation_language' => 'pt_PT',
			),
		);

		// Update plugin setting to the database.
		update_option( TRANSLATION_STATS_WP_OPTION, $settings );

		// Get the Translation Stats configured language.
		$translationstats_language = Utils::translation_language();

		$this->assertEquals(
			$translationstats_language,
			'pt_PT'
		);
	}


	/**
	 * Test campaign information to URL.
	 *
	 * @dataProvider provide_test_campaign_link
	 *
	 * @param string $link              Link to customize.
	 * @param string $source            Set utm_source, default is 'plugin'.
	 * @param string $medium            Set utm_medium, default is 'link'.
	 * @param string $campaign          Set utm_campaign, default is 'plugin_link'.
	 * @param string $expected_result   The expected result for the test.
	 */
	public function test_campaign_link( $link, $source, $medium, $campaign, $expected_result ) {

		$campaign_link = Utils::campaign_link( $link, $source, $medium, $campaign );

		$this->assertEquals(
			$campaign_link,
			$expected_result
		);
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_campaign_link() {
		return array(
			// All parameteres provided.
			array(
				'link'            => 'https://example.com/',
				'source'          => 'example_source',
				'medium'          => 'example_medium',
				'campaign'        => 'example_campaign',
				'expected_result' => 'https://example.com/?utm_source=example_source&amp;utm_medium=example_medium&amp;utm_campaign=example_campaign',
			),
			// Fallback to defaults.
			array(
				'link'            => 'https://example.com/',
				'source'          => null,
				'medium'          => null,
				'campaign'        => null,
				'expected_result' => 'https://example.com/?utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=plugin_link',
			),
			// Some values set.
			array(
				'link'            => 'https://example.com/',
				'source'          => 'example_source',
				'medium'          => null,
				'campaign'        => null,
				'expected_result' => 'https://example.com/?utm_source=example_source&amp;utm_medium=link&amp;utm_campaign=plugin_link',
			),
			array(
				'link'            => 'https://example.com/',
				'source'          => null,
				'medium'          => 'example_medium',
				'campaign'        => null,
				'expected_result' => 'https://example.com/?utm_source=plugin&amp;utm_medium=example_medium&amp;utm_campaign=plugin_link',
			),
			array(
				'link'            => 'https://example.com/',
				'source'          => null,
				'medium'          => null,
				'campaign'        => 'example_campaign',
				'expected_result' => 'https://example.com/?utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=example_campaign',
			),
		);
	}


	/**
	 * Test array of allowed HTML elements for use in wp_kses().
	 */
	public function test_allowed_html() {

		// Expected array of allowed HTML elements.
		$expected = array(
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'class'  => array(),
				'data'   => array(),
				'rel'    => array(),
				'target' => array(),
			),
			'br'     => array(),
			'button' => array(
				'aria-expanded' => array(),
				'class'         => array(),
				'id'            => array(),
				'type'          => array(),
			),
			'div'    => array(
				'class' => array(),
				'data'  => array(),
				'style' => array(),
			),
			'em'     => array(),
			'form'   => array(
				'action' => array(),
				'class'  => array(),
				'method' => array(),
				'name'   => array(),
			),
			'img'    => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'input'  => array(
				'class' => array(),
				'name'  => array(),
				'type'  => array(),
				'value' => array(),
			),
			'li'     => array(
				'class' => array(),
			),
			'ol'     => array(
				'class' => array(),
			),
			'option' => array(
				'value'    => array(),
				'selected' => array(),
			),
			'p'      => array(
				'class' => array(),
			),
			'script' => array(),
			'select' => array(
				'id'    => array(),
				'class' => array(),
				'name'  => array(),
			),
			'span'   => array(
				'class' => array(),
				'style' => array(),
			),
			'strong' => array(),
			'style'  => array(),

			'ul'     => array(
				'class' => array(),
			),
		);

		// Call the method being tested.
		$result = Utils::allowed_html();

		// Assert that the result matches the expected array.
		$this->assertEquals( $expected, $result );
	}
}
