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


	/**
	 * Test the disabled notice, with the minimum required PHP version and the link to update PHP.
	 */
	public function test_translation_stats_disabled_notice() {

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		ob_start();
		Translation_Stats\translation_stats_disabled_notice();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'notice-error', $html );
		$this->assertStringContainsString( 'The plugin <code>Translation Stats</code> has been deactivated due to an error:', $html );
		$this->assertStringContainsString( 'Requires PHP version ' . TRANSLATION_STATS_REQUIRED_PHP . ' or higher.', $html );
		$this->assertStringContainsString( '<a href="' . esc_url( wp_get_update_php_url() ) . '">Learn more about updating PHP</a>.', $html );
	}


	/**
	 * Test the class autoload ignores the classes of other namespaces, and the project classes without file.
	 */
	public function test_class_autoload() {

		Translation_Stats\class_autoload( 'Other_Namespace\\Settings' );
		Translation_Stats\class_autoload( 'Translation_Stats\\Class_Without_File' );

		$this->assertFalse( class_exists( 'Other_Namespace\\Settings', false ) );
		$this->assertFalse( class_exists( 'Translation_Stats\\Class_Without_File', false ) );

		// A project class.
		Translation_Stats\class_autoload( 'Translation_Stats\\Stats_Widget' );

		$this->assertTrue( class_exists( 'Translation_Stats\\Stats_Widget', false ) );
	}
}
