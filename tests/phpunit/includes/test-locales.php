<?php
/**
 * Class Locales Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Locales;
use Translation_Stats\GP_Locales;


/**
 * Locales test case.
 */
class Test_Locales extends WP_UnitTestCase {

	/**
	 * Test developmente mode.
	 */
	public function test_instance() {

		$this->assertFalse( isset( $GLOBALS['translation_stats_locales'] ) );

		// Get wordpress.org Locales.
		Locales::locales();

		$this->assertTrue( isset( $GLOBALS['translation_stats_locales'] ) );
	}
}
