<?php
/**
 * Class Settings_Section Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings;
use Translation_Stats\Settings_Section_General;


/**
 * Settings_Section test case.
 */
class Test_Settings_Section extends Translation_Stats_TestCase {


	/**
	 * Test sanitize the settings and plugins form input.
	 */
	public function test_sanitize_settings() {

		$section = new Settings_Section_General();

		$this->assertSame(
			array(
				'settings' => array(
					'translation_language'     => 'pt_PT',
					'show_warnings'            => true,
					'delete_data_on_uninstall' => false,
				),
				'plugins'  => array(
					'translation-stats' => array(
						'enabled' => 'true',
						'stable'  => 'true',
					),
					'akismet'           => array(
						'enabled' => '',
					),
				),
				'themes'   => array(),
			),
			$section->sanitize_settings(
				array(
					'settings' => array(
						'translation_language' => ' pt_PT ',
						'show_warnings'        => 'true',
					),
					'plugins'  => array(
						'Translation-Stats' => array(
							'enabled' => 'true',
							'stable'  => '<b>true</b>',
						),
						'akismet'           => array(
							'enabled' => array( 'true' ),
						),
						'invalid'           => 'true',
					),
				)
			)
		);
	}


	/**
	 * Test sanitize the settings saves the checkboxes as booleans, also when saving the default settings.
	 */
	public function test_sanitize_settings_booleans() {

		$section  = new Settings_Section_General();
		$settings = new Settings();

		$sanitized = $section->sanitize_settings( $settings->settings_defaults() );

		$this->assertTrue( $sanitized['settings']['show_warnings'] );
		$this->assertTrue( $sanitized['settings']['delete_data_on_uninstall'] );

		$sanitized = $section->sanitize_settings(
			array(
				'settings' => array(
					'show_warnings'            => '',
					'delete_data_on_uninstall' => '<b>true</b>',
				),
			)
		);

		$this->assertFalse( $sanitized['settings']['show_warnings'] );
		$this->assertTrue( $sanitized['settings']['delete_data_on_uninstall'] );
	}


	/**
	 * Test sanitize the form input without settings uses the default settings.
	 */
	public function test_sanitize_settings_defaults() {

		$section  = new Settings_Section_General();
		$settings = new Settings();

		$this->assertSame(
			array(
				'settings' => $settings->settings_defaults()['settings'],
				'plugins'  => array(),
				'themes'   => array(),
			),
			$section->sanitize_settings( array() )
		);
	}


	/**
	 * Test sanitize the themes saves only the enabled themes, as booleans.
	 */
	public function test_sanitize_settings_themes() {

		$section = new Settings_Section_General();

		$sanitized = $section->sanitize_settings(
			array(
				'themes' => array(
					'TwentyTwentyFive' => array( 'enabled' => 'true' ),
					'twentytwentyfour' => array( 'enabled' => '' ),
					'invalid'          => 'true',
				),
			)
		);

		$this->assertSame( array( 'twentytwentyfive' => array( 'enabled' => true ) ), $sanitized['themes'] );
	}
}
