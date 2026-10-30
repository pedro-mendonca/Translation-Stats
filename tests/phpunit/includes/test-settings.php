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

		$_POST[ $action ]                       = 'true';
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
	 * Test the settings sections of all the settings pages are registered, with the sanitized option.
	 */
	public function test_settings_sections() {
		global $wp_settings_sections;

		$settings = new Settings();
		$settings->settings_sections();

		$settings_pages = Settings::get_settings_pages();

		// The debug page is added in development mode, as WP_DEBUG is enabled in the tests.
		$this->assertArrayHasKey( 'debug', $settings_pages );

		foreach ( $settings_pages as $page_id => $settings_page ) {

			$page = TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . $page_id;

			foreach ( $settings_page['sections'] as $section_id ) {
				$this->assertArrayHasKey( $section_id, $wp_settings_sections[ $page ], "Section '{$section_id}' not registered in page '{$page}'." );
			}
		}

		// The plugin option, sanitized.
		$registered_settings = get_registered_settings();

		$this->assertArrayHasKey( TRANSLATION_STATS_WP_OPTION, $registered_settings );
		$this->assertIsCallable( $registered_settings[ TRANSLATION_STATS_WP_OPTION ]['sanitize_callback'] );
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


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_invalid_nonce() {
		return array(
			'reset settings'    => array(
				'action'   => 'reset_settings',
				'callback' => 'settings_reset_callback',
			),
			'delete transients' => array(
				'action'   => 'delete_transients',
				'callback' => 'transients_delete_callback',
			),
		);
	}


	/**
	 * Test the actions stop without changing data if the nonce is invalid.
	 *
	 * @dataProvider provide_test_invalid_nonce
	 *
	 * @param string $action     The submitted action.
	 * @param string $callback   The settings callback to run.
	 */
	public function test_invalid_nonce( $action, $callback ) {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ) );

		set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats', true );

		$this->submit_action( $action );

		$_POST['translation_stats_nonce_check'] = 'invalid';

		$settings = new Settings();

		try {
			$settings->$callback();
			$this->fail( 'Expected wp_die() on invalid nonce.' );
		} catch ( WPDieException $e ) {
			$this->assertSame( 'Sorry, your nonce did not verify.', $e->getMessage() );
		}

		$this->assertSame( 'pt_PT', get_option( TRANSLATION_STATS_WP_OPTION )['settings']['translation_language'] );
		$this->assertTrue( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats' ) );
	}
}
