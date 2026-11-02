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
		wp_deregister_script( 'translation-stats-themes' );
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


	/**
	 * Test the scripts aren't registered on other pages.
	 */
	public function test_register_plugin_scripts_not_allowed_page() {

		$translation_stats = new Translation_Stats();
		$translation_stats->register_plugin_scripts( 'index.php' );

		$this->assertFalse( wp_script_is( 'translation-stats-settings', 'registered' ) );
		$this->assertFalse( wp_script_is( 'translation-stats-plugins', 'registered' ) );
	}


	/**
	 * Test the built themes script is registered with its dependencies and the WordPress themes script, in the footer.
	 */
	public function test_register_plugin_scripts_themes_page() {

		$translation_stats = new Translation_Stats();
		$translation_stats->register_plugin_scripts( 'themes.php' );

		$script = wp_scripts()->registered['translation-stats-themes'];
		$asset  = $this->get_asset_file( 'js/admin-themes' );

		$this->assertSame( TRANSLATION_STATS_DIR_URL . 'assets/build/js/admin-themes.js', $script->src );
		$this->assertSame( array_merge( $asset['dependencies'], array( 'theme' ) ), $script->deps );
		$this->assertSame( $asset['version'], $script->ver );
		$this->assertSame( 1, wp_scripts()->get_data( 'translation-stats-themes', 'group' ) );
		$this->assertTrue( wp_script_is( 'translation-stats-themes', 'enqueued' ) );

		// The themes settings URL, for the Themes screen header.
		$this->assertStringContainsString( 'var tstatsThemes = {"settingsUrl":', (string) wp_scripts()->get_data( 'translation-stats-themes', 'data' ) );
	}


	/**
	 * Test the themes script and the styles aren't loaded on the Network Admin themes list table, which has the same hook.
	 */
	public function test_register_plugin_scripts_network_themes_page() {

		set_current_screen( 'themes-network' );

		$translation_stats = new Translation_Stats();
		$translation_stats->register_plugin_styles( 'themes.php' );
		$translation_stats->register_plugin_scripts( 'themes.php' );

		set_current_screen( 'front' );

		$this->assertFalse( wp_script_is( 'translation-stats-themes', 'registered' ) );
		$this->assertFalse( wp_script_is( 'theme', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'translation-stats', 'registered' ) );
	}
}
