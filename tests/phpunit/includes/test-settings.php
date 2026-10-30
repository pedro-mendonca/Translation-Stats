<?php
/**
 * Class Settings Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings;
use Translation_Stats\Settings_Section_General;


/**
 * Settings test case.
 */
class Test_Settings extends Translation_Stats_TestCase {


	/**
	 * Clean up the request data after each test.
	 */
	public function tear_down() {
		unset( $_POST['reset_settings'], $_POST['delete_transients'], $_POST['translation_stats_nonce_check'] );
		parent::tear_down();
	}


	/**
	 * Submit a Translation Stats settings action with a valid nonce.
	 *
	 * @param string $action   The action ( 'reset_settings' or 'delete_transients' ).
	 */
	private function submit_action( $action ) {

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$_POST[ $action ]                        = 'true';
		$_POST['translation_stats_nonce_check'] = wp_create_nonce( 'translation_stats_action' );
	}


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$settings = new Settings();

		$this->assertSame( 10, has_action( 'admin_menu', array( $settings, 'admin_menu' ) ) );
		$this->assertSame( 10, has_action( 'admin_init', array( $settings, 'settings_sections' ) ) );
	}


	/**
	 * Test the default settings.
	 */
	public function test_settings_defaults() {

		$settings = new Settings();

		$this->assertSame(
			array(
				'settings' => array(
					'show_warnings'            => true,
					'translation_language'     => 'site-default',
					'delete_data_on_uninstall' => true,
					'transients_expiration'    => TRANSLATION_STATS_TRANSIENTS_TRANSLATIONS_EXPIRATION,
					'settings_version'         => TRANSLATION_STATS_SETTINGS_VERSION,
				),
			),
			$settings->settings_defaults()
		);
	}


	/**
	 * Test reset the settings to the defaults.
	 */
	public function test_settings_reset_callback() {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello' => array( 'enabled' => 'true' ) ) );

		$settings = new Settings();

		// Without the reset action, the settings are kept.
		$settings->settings_reset_callback();

		$this->assertSame( 'pt_PT', get_option( TRANSLATION_STATS_WP_OPTION )['settings']['translation_language'] );

		$this->submit_action( 'reset_settings' );

		ob_start();
		$settings->settings_reset_callback();
		$output = ob_get_clean();

		$this->assertSame( $settings->settings_defaults(), get_option( TRANSLATION_STATS_WP_OPTION ) );
		$this->assertStringContainsString( 'Settings restored successfully.', $output );
	}


	/**
	 * Test delete the Translation Stats transients.
	 */
	public function test_transients_delete_callback() {

		$this->set_settings();

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );

		$settings = new Settings();

		// Without the delete action, the transients are kept.
		$settings->transients_delete_callback();

		$this->assertTrue( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats' ) );

		$this->submit_action( 'delete_transients' );

		ob_start();
		$settings->transients_delete_callback();
		$output = ob_get_clean();

		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats' ) );
		$this->assertStringContainsString( 'Cache cleaned successfully.', $output );
	}
}
