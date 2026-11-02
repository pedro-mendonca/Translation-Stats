<?php
/**
 * Class Activation Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Activation;


/**
 * Activation test case.
 */
class Test_Activation extends Translation_Stats_TestCase {


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$activation = new Activation();

		// With the plugin file, whatever the plugin folder name.
		$this->assertSame( 10, has_action( 'activate_' . TRANSLATION_STATS_FILE, array( $activation, 'activate' ) ) );
		$this->assertSame( 10, has_action( 'admin_notices', array( $activation, 'activate_notice' ) ) );
	}


	/**
	 * Test the activation notice is shown once after activation, even without warnings enabled.
	 */
	public function test_activate_notice() {

		$this->set_settings( array( 'show_warnings' => false ) );

		$activation = new Activation();

		// Not activated.
		ob_start();
		$activation->activate_notice();
		$this->assertSame( '', ob_get_clean() );

		$activation->activate();

		$this->assertTrue( get_transient( 'translation_stats_activate' ) );

		ob_start();
		$activation->activate_notice();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'notice-success', $html );
		$this->assertStringContainsString( 'Thank you for installing Translation Stats.', $html );
		$this->assertStringContainsString( 'to select the plugins and themes to show their translation stats.', $html );
		$this->assertStringContainsString( 'options-general.php?page=translation-stats', $html );
		$this->assertFalse( get_transient( 'translation_stats_activate' ) );

		// Only once.
		ob_start();
		$activation->activate_notice();
		$this->assertSame( '', ob_get_clean() );
	}
}
