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
	 * Get the settings in the version 1.0 format, with the checkboxes saved as string 'true' or missing if unchecked.
	 *
	 * @return array  Settings v1.0.
	 */
	private function get_settings_v1() {
		return array(
			'settings' => array(
				'show_warnings'         => 'true',
				'translation_language'  => 'pt_PT',
				'transients_expiration' => DAY_IN_SECONDS,
				'settings_version'      => '1.0',
			),
			'plugins'  => array(
				'translation-stats' => array(
					'enabled' => 'true',
					'stable'  => 'true',
				),
			),
		);
	}


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$db_update = new DB_Update();

		$this->assertSame( 10, has_action( 'plugins_loaded', array( $db_update, 'settings_db_check' ) ) );
		$this->assertSame( 10, has_action( 'admin_notices', array( $db_update, 'settings_update_notice' ) ) );
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
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_settings_update_v1_to_v2() {
		return array(
			'checked'   => array(
				'value'           => 'true',
				'expected_result' => true,
			),
			'unchecked' => array(
				'value'           => '',
				'expected_result' => false,
			),
			'boolean'   => array(
				'value'           => true,
				'expected_result' => true,
			),
		);
	}


	/**
	 * Test update the settings from version 1.0 to version 2.0, with the checkboxes saved as booleans.
	 *
	 * @dataProvider provide_test_settings_update_v1_to_v2
	 *
	 * @param mixed $value             The 'delete_data_on_uninstall' setting value in version 1.0.
	 * @param bool  $expected_result   The 'delete_data_on_uninstall' setting value in version 2.0.
	 */
	public function test_settings_update_v1_to_v2( $value, $expected_result ) {

		$settings_v1 = $this->get_settings_v1();

		$settings_v1['settings']['delete_data_on_uninstall'] = $value;

		update_option( TRANSLATION_STATS_WP_OPTION, $settings_v1 );

		$db_update = new DB_Update();
		$db_update->settings_update_v1_to_v2();

		$this->assertSame(
			array(
				'settings' => array(
					'show_warnings'            => true,
					'translation_language'     => 'pt_PT',
					'transients_expiration'    => DAY_IN_SECONDS,
					'settings_version'         => '2.0',
					'delete_data_on_uninstall' => $expected_result,
				),
				'plugins'  => $settings_v1['plugins'],
			),
			get_option( TRANSLATION_STATS_WP_OPTION )
		);
	}


	/**
	 * Test update the settings from version 1.0 to version 2.0, with the unchecked checkboxes missing.
	 */
	public function test_settings_update_v1_to_v2_unchecked_missing() {

		$settings_v1 = $this->get_settings_v1();

		unset( $settings_v1['settings']['show_warnings'] );

		update_option( TRANSLATION_STATS_WP_OPTION, $settings_v1 );

		$db_update = new DB_Update();
		$db_update->settings_update_v1_to_v2();

		$settings = get_option( TRANSLATION_STATS_WP_OPTION )['settings'];

		$this->assertFalse( $settings['show_warnings'] );
		$this->assertFalse( $settings['delete_data_on_uninstall'] );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_settings_db_check() {
		return array(
			'from version 0.0' => array(
				'settings'          => $this->get_settings_v0(),
				'installed_version' => '0.0',
			),
			'from version 1.0' => array(
				'settings'          => $this->get_settings_v1(),
				'installed_version' => '1.0',
			),
		);
	}


	/**
	 * Test the settings database check runs all the updates since the installed version, without output, to show the notice later.
	 *
	 * @dataProvider provide_test_settings_db_check
	 *
	 * @param array  $settings            Installed settings.
	 * @param string $installed_version   Installed settings version.
	 */
	public function test_settings_db_check( $settings, $installed_version ) {

		update_option( TRANSLATION_STATS_WP_OPTION, $settings );

		$db_update = new DB_Update();

		ob_start();
		$db_update->settings_db_check();
		$output = ob_get_clean();

		$updated_settings = get_option( TRANSLATION_STATS_WP_OPTION );

		// Ignore the settings order.
		ksort( $updated_settings['settings'] );

		$this->assertSame(
			array(
				'settings' => array(
					'delete_data_on_uninstall' => '0.0' === $installed_version,
					'settings_version'         => '2.0',
					'show_warnings'            => true,
					'transients_expiration'    => DAY_IN_SECONDS,
					'translation_language'     => 'pt_PT',
				),
				'plugins'  => array(
					'translation-stats' => array(
						'enabled' => 'true',
						'stable'  => 'true',
					),
				),
			),
			$updated_settings
		);

		// No output in any request, the notice is shown later in the admin notices.
		$this->assertSame( '', $output );
		$this->assertSame( $installed_version, get_transient( 'translation_stats_settings_updated' ) );
	}


	/**
	 * Test the settings update notice is shown once, only to the users who can manage the settings.
	 */
	public function test_settings_update_notice() {

		add_filter( 'translation_stats_development_mode', '__return_false' );

		$db_update = new DB_Update();

		// Without settings update.
		ob_start();
		$db_update->settings_update_notice();
		$this->assertSame( '', ob_get_clean() );

		set_transient( 'translation_stats_settings_updated', '1.0' );

		// User without permission to manage the settings, the notice waits.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		ob_start();
		$db_update->settings_update_notice();
		$this->assertSame( '', ob_get_clean() );
		$this->assertSame( '1.0', get_transient( 'translation_stats_settings_updated' ) );

		// Administrator.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		ob_start();
		$db_update->settings_update_notice();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'notice-success', $output );
		$this->assertStringContainsString( 'successfully updated from version <code>1.0</code> to version <code>2.0</code>', $output );
		$this->assertStringNotContainsString( '<h3>Settings</h3>', $output );
		$this->assertFalse( get_transient( 'translation_stats_settings_updated' ) );

		// Only once.
		ob_start();
		$db_update->settings_update_notice();
		$this->assertSame( '', ob_get_clean() );
	}


	/**
	 * Test the settings update notice shows the updated settings in development mode.
	 */
	public function test_settings_update_notice_development_mode() {

		add_filter( 'translation_stats_development_mode', '__return_true' );

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'translation-stats' => array( 'enabled' => 'true' ) ) );

		set_transient( 'translation_stats_settings_updated', '0.0' );

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		ob_start();
		( new DB_Update() )->settings_update_notice();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'successfully updated from version <code>0.0</code>', $output );
		$this->assertStringContainsString( '<h3>Settings</h3>', $output );
		$this->assertStringContainsString( '<h3>Plugins</h3>', $output );

		// The quotes are encoded or not, depending on the WordPress version.
		$this->assertStringContainsString( '"translation_language": "pt_PT"', html_entity_decode( $output ) );
		$this->assertStringContainsString( '"translation-stats": {', html_entity_decode( $output ) );
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

		// Settings with a newer version, after a plugin downgrade.
		$this->set_settings( array( 'settings_version' => '99.0' ) );

		$settings = get_option( TRANSLATION_STATS_WP_OPTION );

		ob_start();
		$db_update->settings_db_check();
		$output = ob_get_clean();

		$this->assertSame( $settings, get_option( TRANSLATION_STATS_WP_OPTION ) );
		$this->assertSame( '', $output );
	}


	/**
	 * Test the settings updates do nothing with invalid settings.
	 */
	public function test_settings_update_invalid_settings() {

		$db_update = new DB_Update();

		update_option( TRANSLATION_STATS_WP_OPTION, 'invalid' );

		$db_update->settings_update_v0_to_v1();

		$this->assertSame( 'invalid', get_option( TRANSLATION_STATS_WP_OPTION ) );

		$db_update->settings_update_v1_to_v2();

		$this->assertSame( 'invalid', get_option( TRANSLATION_STATS_WP_OPTION ) );
	}
}
