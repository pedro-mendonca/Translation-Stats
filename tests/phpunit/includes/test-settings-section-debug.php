<?php
/**
 * Class Settings_Section_Debug Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings_Section_Debug;


/**
 * Settings_Section_Debug test case.
 */
class Test_Settings_Section_Debug extends Translation_Stats_TestCase {


	/**
	 * Render a debug info method.
	 *
	 * @param string $method   Debug info method.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render( $method ) {

		$section = new Settings_Section_Debug();

		ob_start();
		$section->$method();
		return (string) ob_get_clean();
	}


	/**
	 * Test the settings debug info, with and without settings.
	 */
	public function test_debug_info__settings() {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello' => array( 'enabled' => 'true' ) ) );

		$html = $this->render( 'debug_info__settings' );

		$this->assertStringContainsString( 'Translation Stats Locale: <code>pt_PT</code>', $html );
		$this->assertStringContainsString( '&quot;hello&quot;', $html );
		$this->assertStringNotContainsString( 'No settings found.', $html );

		delete_option( TRANSLATION_STATS_WP_OPTION );

		$this->assertSame( 2, substr_count( $this->render( 'debug_info__settings' ), 'No settings found.' ) );
	}


	/**
	 * Test the transients debug info lists the Translation Stats transients.
	 */
	public function test_debug_info__transients() {

		$this->assertStringContainsString( 'No transients found.', $this->render( 'debug_info__transients' ) );

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );

		$html = $this->render( 'debug_info__transients' );

		$this->assertStringContainsString( '&quot;' . TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats&quot;', $html );
		$this->assertStringNotContainsString( 'No transients found.', $html );
	}
}
