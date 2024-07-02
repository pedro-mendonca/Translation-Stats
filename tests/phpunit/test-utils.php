<?php
/**
 * Class Utils Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Utils;


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
	function provide_test_get_asset_url() {
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
	 * Test default translation language without any settings.
	 */
	public function test_translation_language_default() {

		// Get the Translation Stats configured language.
		$translationstats_language = Utils::translation_language();

		$this->assertEquals(
			$translationstats_language,
			'en_US'
		);

	}


	/**
	 * Test translation language configured in the settings.
	 */
	public function test_translation_language() {

		// Configure translation language.
		$settings = array(
			'settings' => array(
				'translation_language' => 'pt_PT',
			)
		);

		// Add plugin setting to the database.
		add_option( TRANSLATION_STATS_WP_OPTION, $settings );

		// Get the Translation Stats configured language.
		$translationstats_language = Utils::translation_language();

		$this->assertEquals(
			$translationstats_language,
			'pt_PT'
		);

	}

}
