<?php
/**
 * Plugin file Test.
 *
 * @package Translation_Stats
 */


/**
 * Plugin file test case.
 */
class Test_Plugin_File extends WP_UnitTestCase {

	/**
	 * Test backup sanity check, in case the plugin is activated, or the versions change after activation.
	 */
	public function test_translation_stats_check_version() {
		$this->assertNull( Translation_Stats\translation_stats_check_version() );
	}

	/**
	 * Test Translation Stats minimum requirements.
	 */
	public function test_translation_stats_compatible_version() {

		$compatible_version = Translation_Stats\translation_stats_compatible_version();

		if ( version_compare( PHP_VERSION, TRANSLATION_STATS_REQUIRED_PHP, '>=' ) ) {
			$this->assertTrue( $compatible_version );
		} else {
			$this->assertFalse( $compatible_version );
		}

 	}

}
