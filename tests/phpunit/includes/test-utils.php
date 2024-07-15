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
	 * Test get_asset_url().
	 *
	 * @dataProvider provide_test_get_asset_url
	 *
	 * @param string $test_asset                 The test asset.
	 * @param string $expected_result            The expected result for the test.
	 * @param string $expected_result_minified   The expected minified result for the test.
	 */
	public function test_get_asset_url( $test_asset, $expected_result, $expected_result_minified ) {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			// Load main asset files.
			$asset_url = Utils::get_asset_url( $test_asset, false );

			$this->assertEquals(
				$asset_url,
				$expected_result // Load normal version.
			);

			// Load minified asset files.
			$asset_url = Utils::get_asset_url( $test_asset, true );

			$this->assertEquals(
				$asset_url,
				$expected_result // Load normal version.
			);
		} else {
			// Load main asset files.
			$asset_url = Utils::get_asset_url( $test_asset, false );

			$this->assertEquals(
				$asset_url,
				$expected_result // Load normal version.
			);

			// Load minified asset files.
			$asset_url = Utils::get_asset_url( $test_asset, true );

			$this->assertEquals(
				$asset_url,
				$expected_result_minified // Load minified version.
			);
		}
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_get_asset_url() {
		return array(
			// No extension.
			array(
				'test_asset'               => 'filename',
				'expected_result'          => false,
				'expected_result_minified' => false,
			),
			array(
				'test_asset'               => 'path/filename',
				'expected_result'          => false,
				'expected_result_minified' => false,
			),
			// Unsupported extension.
			array(
				'test_asset'               => 'path/filename.txt',
				'expected_result'          => false,
				'expected_result_minified' => false,
			),
			array(
				'test_asset'               => 'path/filename.xls',
				'expected_result'          => false,
				'expected_result_minified' => false,
			),
			array(
				'test_asset'               => 'path/filename.doc',
				'expected_result'          => false,
				'expected_result_minified' => false,
			),
			array(
				'test_asset'               => 'path/filename.css',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/path/filename.css',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/path/filename.min.css',
			),
			array(
				'test_asset'               => 'path/filename.js',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/path/filename.js',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/path/filename.min.js',
			),
			array(
				'test_asset'               => 'css/admin.css',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/css/admin.css',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/css/admin.min.css',
			),
			array(
				'test_asset'               => 'js/admin-plugins.js',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/js/admin-plugins.js',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/js/admin-plugins.min.js',
			),
			array(
				'test_asset'               => 'js/admin-settings.js',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/js/admin-settings.js',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/js/admin-settings.min.js',
			),
			array(
				'test_asset'               => 'lib/tablesorter/jquery.tablesorter.combined.js',
				'expected_result'          => TRANSLATION_STATS_DIR_URL . 'assets/lib/tablesorter/jquery.tablesorter.combined.js',
				'expected_result_minified' => TRANSLATION_STATS_DIR_URL . 'assets/lib/tablesorter/jquery.tablesorter.combined.min.js',
			),
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
