<?php
/**
 * Class Translation_Stats Assets Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Translation_Stats;

/**
 * Translation_Stats assets test case.
 */
class Test_Translation_Stats_Assets extends Translation_Stats_TestCase {


	/**
	 * Remove the registered assets after each test.
	 */
	public function tear_down() {
		wp_deregister_style( 'translation-stats' );
		wp_deregister_script( 'translation-stats-settings' );
		wp_deregister_script( 'translation-stats-plugins' );
		parent::tear_down();
	}


	/**
	 * Get the asset file data of a built asset.
	 *
	 * @param string $name   Built asset name.
	 *
	 * @return array  Asset file data.
	 */
	private function get_asset_file( $name ) {
		return require TRANSLATION_STATS_DIR_PATH . 'assets/build/' . $name . '.asset.php';
	}


	/**
	 * Test the built style is registered with its version and RTL version on the allowed pages.
	 */
	public function test_register_plugin_styles() {

		$translation_stats = new Translation_Stats();

		// Not allowed page.
		$translation_stats->register_plugin_styles( 'index.php' );

		$this->assertFalse( wp_style_is( 'translation-stats', 'registered' ) );

		// Plugins page.
		$translation_stats->register_plugin_styles( 'plugins.php' );

		$style = wp_styles()->registered['translation-stats'];

		$this->assertSame( TRANSLATION_STATS_DIR_URL . 'assets/build/css/admin.css', $style->src );
		$this->assertSame( $this->get_asset_file( 'css/admin' )['version'], $style->ver );
		$this->assertSame( 'replace', wp_styles()->get_data( 'translation-stats', 'rtl' ) );
		$this->assertTrue( wp_style_is( 'translation-stats', 'enqueued' ) );
	}


	/**
	 * Test the built settings script is registered with its dependencies and version, with the bundled tablesorter.
	 */
	public function test_register_plugin_scripts_settings_page() {

		$translation_stats = new Translation_Stats();
		$translation_stats->register_plugin_scripts( 'settings_page_' . TRANSLATION_STATS_SETTINGS_PAGE );

		$script = wp_scripts()->registered['translation-stats-settings'];
		$asset  = $this->get_asset_file( 'js/admin-settings' );

		$this->assertSame( TRANSLATION_STATS_DIR_URL . 'assets/build/js/admin-settings.js', $script->src );
		$this->assertSame( $asset['dependencies'], $script->deps );
		$this->assertSame( $asset['version'], $script->ver );
		$this->assertTrue( wp_script_is( 'translation-stats-settings', 'enqueued' ) );

		// The tablesorter library is bundled in the settings script.
		$this->assertFalse( wp_script_is( 'translation-stats-tablesorter', 'registered' ) );
	}


	/**
	 * Test the built plugins script is registered with its dependencies and version.
	 */
	public function test_register_plugin_scripts_plugins_page() {

		$translation_stats = new Translation_Stats();
		$translation_stats->register_plugin_scripts( 'plugins.php' );

		$script = wp_scripts()->registered['translation-stats-plugins'];
		$asset  = $this->get_asset_file( 'js/admin-plugins' );

		$this->assertSame( TRANSLATION_STATS_DIR_URL . 'assets/build/js/admin-plugins.js', $script->src );
		$this->assertSame( $asset['dependencies'], $script->deps );
		$this->assertSame( $asset['version'], $script->ver );
		$this->assertTrue( wp_script_is( 'translation-stats-plugins', 'enqueued' ) );
	}
}
