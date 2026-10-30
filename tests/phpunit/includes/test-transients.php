<?php
/**
 * Class Transients Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Transients;


/**
 * Transients test case.
 */
class Test_Transients extends Translation_Stats_TestCase {


	/**
	 * Test get the transients names by search term.
	 */
	public function test_get_transients() {

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );
		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT', 'stats' );
		set_transient( 'other_plugin_transient', true );

		$transients = new Transients();

		$names = $transients->get_transients( TRANSLATION_STATS_TRANSIENTS_PREFIX );

		sort( $names );

		$this->assertSame(
			array(
				'_transient_' . TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats',
				'_transient_' . TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT',
			),
			$names
		);
	}


	/**
	 * Test delete the transients by prefix, keeping other transients.
	 */
	public function test_delete_transients() {

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );
		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT', 'stats' );
		set_transient( 'other_plugin_transient', true );

		$transients = new Transients();

		$transients->delete_transients( TRANSLATION_STATS_TRANSIENTS_PREFIX );

		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats' ) );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
		$this->assertSame( array(), $transients->get_transients( TRANSLATION_STATS_TRANSIENTS_PREFIX ) );
		$this->assertTrue( get_transient( 'other_plugin_transient' ) );
	}
}
