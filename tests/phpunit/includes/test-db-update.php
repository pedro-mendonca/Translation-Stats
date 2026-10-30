<?php
/**
 * Class DB_Update Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\DB_Update;


/**
 * DB_Update test case.
 */
class Test_DB_Update extends Translation_Stats_TestCase {


	/**
	 * Get the settings in the version 0.0 format, without the 'settings' and 'plugins' keys.
	 *
	 * @return array  Settings v0.0.
	 */
	private function get_settings_v0() {
		return array(
			'show_warnings'            => 'true',
			'translation_language'     => 'pt_PT',
			'delete_data_on_uninstall' => 'true',
			'transients_expiration'    => DAY_IN_SECONDS,
			'all_plugins'              => 'true',
			'translation-stats'        => array(
				'enabled' => 'true',
				'stable'  => 'true',
			),
		);
	}


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$db_update = new DB_Update();

		$this->assertSame( 10, has_action( 'plugins_loaded', array( $db_update, 'settings_db_check' ) ) );
	}


	/**
	 * Test get the installed settings version.
	 */
	public function test_installed_settings_version() {

		$db_update = new DB_Update();

		// No settings.
		delete_option( TRANSLATION_STATS_WP_OPTION );

		$this->assertFalse( $db_update->installed_settings_version() );

		// Settings without version.
		update_option( TRANSLATION_STATS_WP_OPTION, $this->get_settings_v0() );

		$this->assertSame( '0.0', $db_update->installed_settings_version() );

		// Settings with current version.
		$this->set_settings();

		$this->assertSame( TRANSLATION_STATS_SETTINGS_VERSION, $db_update->installed_settings_version() );
	}


	/**
	 * Test update the settings from version 0.0 to version 1.0.
	 */
	public function test_settings_update_v0_to_v1() {

		update_option( TRANSLATION_STATS_WP_OPTION, $this->get_settings_v0() );

		$db_update = new DB_Update();

		ob_start();
		$db_update->settings_update_v0_to_v1();
		ob_end_clean();

		$this->assertSame(
			array(
				'settings' => array(
					'show_warnings'            => 'true',
					'translation_language'     => 'pt_PT',
					'delete_data_on_uninstall' => 'true',
					'transients_expiration'    => DAY_IN_SECONDS,
					'settings_version'         => '1.0',
				),
				'plugins'  => array(
					'translation-stats' => array(
						'enabled' => 'true',
						'stable'  => 'true',
					),
				),
			),
			get_option( TRANSLATION_STATS_WP_OPTION )
		);
	}


	/**
	 * Test the settings database check updates outdated settings and shows a notice.
	 */
	public function test_settings_db_check() {

		update_option( TRANSLATION_STATS_WP_OPTION, $this->get_settings_v0() );

		$db_update = new DB_Update();

		ob_start();
		$db_update->settings_db_check();
		$output = ob_get_clean();

		$this->assertSame( TRANSLATION_STATS_SETTINGS_VERSION, $db_update->installed_settings_version() );
		$this->assertStringContainsString( 'successfully updated from version <code>0.0</code> to version <code>1.0</code>', $output );
	}


	/**
	 * Test the settings database check does nothing without settings or with current settings.
	 */
	public function test_settings_db_check_without_update() {

		$db_update = new DB_Update();

		// No settings.
		delete_option( TRANSLATION_STATS_WP_OPTION );

		ob_start();
		$db_update->settings_db_check();
		$output = ob_get_clean();

		$this->assertFalse( get_option( TRANSLATION_STATS_WP_OPTION ) );
		$this->assertSame( '', $output );

		// Settings with current version.
		$this->set_settings( array( 'translation_language' => 'pt_PT' ) );

		$settings = get_option( TRANSLATION_STATS_WP_OPTION );

		ob_start();
		$db_update->settings_db_check();
		$output = ob_get_clean();

		$this->assertSame( $settings, get_option( TRANSLATION_STATS_WP_OPTION ) );
		$this->assertSame( '', $output );
	}
}
