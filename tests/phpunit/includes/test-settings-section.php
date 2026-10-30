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
					'translation_language' => 'pt_PT',
					'show_warnings'        => 'true',
				),
				'plugins'  => array(
					'translation-stats' => array(
						'enabled' => 'true',
						'stable'  => 'true',
					),
				),
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
						'invalid'           => 'true',
					),
				)
			)
		);
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
			),
			$section->sanitize_settings( array() )
		);
	}
}
