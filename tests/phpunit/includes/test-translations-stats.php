<?php
/**
 * Class Translation_Stats Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Translation_Stats;


/**
 * Translation_Stats test case.
 */
class Test_Translation_Stats extends WP_UnitTestCase {


	/**
	 * Test add action links to the settings on the Plugins screen.
	 */
	public function test_plugin_action_links() {

		$links = array(
			'deactivate' => '<a href="#" id="deactivate-akismet" aria-label="Deactivate Akismet">Deactivate</a>',
		);

		$translation_stats = new Translation_Stats;

		$plugin_action_links = $translation_stats->plugin_action_links( $links );

		$this->assertSame(
			$plugin_action_links,
			array(
				'<a href="' . admin_url( 'options-general.php?page=' . TRANSLATION_STATS_SETTINGS_PAGE ) . '">Settings</a>',
				'deactivate' => '<a href="#" id="deactivate-akismet" aria-label="Deactivate Akismet">Deactivate</a>',
			)
		);

	}


	/**
	 * Test set admin pages where to load Translation Stats styles and scripts.
	 */
	public function test_allowed_pages() {

		$translation_stats = new Translation_Stats;

		// Allowed pages.
		$this->assertTrue( $translation_stats->allowed_pages( 'plugins.php' ) );
		$this->assertTrue( $translation_stats->allowed_pages( 'update-core.php' ) );
		$this->assertTrue( $translation_stats->allowed_pages( 'settings_page_' . TRANSLATION_STATS_SETTINGS_PAGE ) );

		// Others.
		$this->assertFalse( $translation_stats->allowed_pages( 'something-else' ) );
		$this->assertFalse( $translation_stats->allowed_pages( '' ) );
		$this->assertFalse( $translation_stats->allowed_pages( 123 ) );
		$this->assertFalse( $translation_stats->allowed_pages( null ) );
		$this->assertFalse( $translation_stats->allowed_pages( true ) );
		$this->assertFalse( $translation_stats->allowed_pages( false ) );

	}

}
