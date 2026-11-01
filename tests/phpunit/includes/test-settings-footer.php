<?php
/**
 * Class Settings_Footer Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings_Footer;


/**
 * Settings_Footer test case.
 */
class Test_Settings_Footer extends Translation_Stats_TestCase {


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$footer = new Settings_Footer();

		$this->assertSame( 1, has_filter( 'admin_footer_text', array( $footer, 'admin_footer_text' ) ) );
	}


	/**
	 * Test the admin footer text is replaced only on the Translation Stats settings page.
	 */
	public function test_admin_footer_text() {

		$footer = new Settings_Footer();

		set_current_screen( 'dashboard' );

		$this->assertSame( 'Footer', $footer->admin_footer_text( 'Footer' ) );

		set_current_screen( 'settings_page_translation-stats' );

		$text = $footer->admin_footer_text( 'Footer' );

		$this->assertStringContainsString( 'Thank you for translating with <a href="https://translationstats.com', $text );
		$this->assertStringContainsString( 'version ' . TRANSLATION_STATS_VERSION, $text );
		$this->assertStringNotContainsString( 'Footer', $text );
	}
}
