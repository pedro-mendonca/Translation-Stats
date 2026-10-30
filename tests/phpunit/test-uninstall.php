<?php
/**
 * Class Uninstall Test.
 *
 * @package Translation_Stats
 */

/**
 * Uninstall test case.
 */
class Test_Uninstall extends Translation_Stats_TestCase {


	/**
	 * Run the plugin uninstall, for each site if multisite.
	 */
	private function uninstall() {

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'translation-stats/translation-stats.php' );
		}

		// The uninstall file declares functions, so it can only be loaded once.
		if ( ! function_exists( 'translation_stats_uninstall' ) ) {
			require dirname( dirname( __DIR__ ) ) . '/uninstall.php';
			return;
		}

		if ( is_multisite() ) {
			foreach ( get_sites( array( 'fields' => 'ids' ) ) as $site_id ) {
				switch_to_blog( $site_id );
				translation_stats_uninstall();
			}
			restore_current_blog();
		} else {
			translation_stats_uninstall();
		}
	}


	/**
	 * Add the plugin data to the current site.
	 *
	 * @param mixed $delete_data_on_uninstall   The 'delete_data_on_uninstall' setting value.
	 */
	private function add_plugin_data( $delete_data_on_uninstall ) {

		$this->set_settings( array( 'delete_data_on_uninstall' => $delete_data_on_uninstall ) );

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );
	}


	/**
	 * Assert if the plugin data exists in the current site.
	 *
	 * @param bool $exists   Whether the plugin data should exist.
	 */
	private function assert_plugin_data( $exists ) {

		$this->assertSame( $exists, false !== get_option( TRANSLATION_STATS_WP_OPTION ) );
		$this->assertSame( $exists, false !== get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats' ) );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_uninstall_deletes_data() {
		return array(
			'delete data, saved from settings form' => array(
				'delete_data_on_uninstall' => 'true',
				'expected_data'            => false,
			),
			'delete data, default settings'         => array(
				'delete_data_on_uninstall' => true,
				'expected_data'            => false,
			),
			'keep data'                             => array(
				'delete_data_on_uninstall' => '',
				'expected_data'            => true,
			),
		);
	}


	/**
	 * Test the plugin data is deleted on uninstall, if set in the settings.
	 *
	 * @dataProvider provide_test_uninstall_deletes_data
	 *
	 * @param mixed $delete_data_on_uninstall   The 'delete_data_on_uninstall' setting value.
	 * @param bool  $expected_data              Whether the plugin data should exist after uninstall.
	 */
	public function test_uninstall_deletes_data( $delete_data_on_uninstall, $expected_data ) {

		$this->add_plugin_data( $delete_data_on_uninstall );

		$this->uninstall();

		$this->assert_plugin_data( $expected_data );
	}


	/**
	 * Test the plugin data is deleted in all the sites, with an additional site on multisite.
	 */
	public function test_uninstall_all_sites() {

		$this->add_plugin_data( 'true' );

		$site_ids = array();
		if ( is_multisite() ) {
			$site_ids[] = self::factory()->blog->create();
		}

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			$this->add_plugin_data( 'true' );
			restore_current_blog();
		}

		$this->uninstall();

		$this->assert_plugin_data( false );

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			$this->assert_plugin_data( false );
			restore_current_blog();
		}
	}
}
