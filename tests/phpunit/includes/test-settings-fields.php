<?php
/**
 * Class Settings Fields Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings_Field;
use Translation_Stats\Settings_Field_Button;
use Translation_Stats\Settings_Field_Checkbox;
use Translation_Stats\Settings_Field_Hidden;
use Translation_Stats\Settings_Field_Select;
use Translation_Stats\Settings_Field_Select_Language;

/**
 * Settings fields test case.
 */
class Test_Settings_Fields extends Translation_Stats_TestCase {


	/**
	 * Render a settings field.
	 *
	 * @param Settings_Field $field   Settings field.
	 * @param array          $args    Settings field arguments.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render( $field, $args ) {

		ob_start();
		$field->callback(
			array_merge(
				array(
					'path'        => 'settings',
					'label'       => 'Field label',
					'description' => 'Field description <script>',
					'class'       => 'field-class',
				),
				$args
			)
		);
		return (string) ob_get_clean();
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_is_supported() {
		return array(
			'button'          => array( 'button', true ),
			'checkbox'        => array( 'checkbox', true ),
			'hidden'          => array( 'hidden', true ),
			'select'          => array( 'select', true ),
			'select language' => array( 'select_language', true ),
			'text'            => array( 'text', false ),
		);
	}


	/**
	 * Test the supported field types.
	 *
	 * @dataProvider provide_test_is_supported
	 *
	 * @param string $type              Field type.
	 * @param bool   $expected_result   Whether the field type is supported.
	 */
	public function test_is_supported( $type, $expected_result ) {

		$this->assertSame( $expected_result, Settings_Field::is_supported( $type ) );
	}


	/**
	 * Test the supported field types can be filtered.
	 */
	public function test_is_supported_filter() {

		add_filter(
			'translation_stats_settings_field_types',
			function ( $types ) {
				$types[] = 'text';
				return $types;
			}
		);

		$this->assertTrue( Settings_Field::is_supported( 'text' ) );
	}


	/**
	 * Test the field is registered in its settings page section.
	 */
	public function test_add_field() {
		global $wp_settings_fields;

		$field = array(
			'id'      => 'show_warnings',
			'title'   => 'Show warnings',
			'page'    => 'translation_stats_settings',
			'section' => 'general',
		);

		$checkbox = new Settings_Field_Checkbox( $field );

		$this->assertSame( array( $checkbox, 'callback' ), $wp_settings_fields['translation_stats_settings']['general']['show_warnings']['callback'] );
		$this->assertSame( $field, $wp_settings_fields['translation_stats_settings']['general']['show_warnings']['args'] );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_checkbox() {
		return array(
			'default without settings, checked'   => array(
				'settings'        => null,
				'default_value'   => true,
				'expected_result' => true,
			),
			'default without settings, unchecked' => array(
				'settings'        => null,
				'default_value'   => false,
				'expected_result' => false,
			),
			'saved checked'                       => array(
				'settings'        => array( 'show_warnings' => 'true' ),
				'default_value'   => false,
				'expected_result' => true,
			),
			'saved unchecked'                     => array(
				'settings'        => array( 'show_warnings' => '' ),
				'default_value'   => true,
				'expected_result' => false,
			),
		);
	}


	/**
	 * Test the checkbox field uses the saved setting, or the default without settings.
	 *
	 * @dataProvider provide_test_checkbox
	 *
	 * @param array|null $settings          Saved settings, or null without settings.
	 * @param bool       $default_value     Field default.
	 * @param bool       $expected_result   Whether the checkbox is checked.
	 */
	public function test_checkbox( $settings, $default_value, $expected_result ) {

		if ( null === $settings ) {
			delete_option( TRANSLATION_STATS_WP_OPTION );
		} else {
			$this->set_settings( $settings );
		}

		$html = $this->render(
			new Settings_Field_Checkbox(),
			array(
				'id'      => 'show_warnings',
				'default' => $default_value,
			)
		);

		$this->assertStringContainsString( 'name="tstats_settings[settings][show_warnings]"', $html );
		$this->assertStringContainsString( 'class="field-class" type="checkbox" value="true"', $html );
		$this->assertSame( $expected_result, false !== strpos( $html, "checked='checked'" ) );
		$this->assertStringContainsString( 'Field description &lt;script&gt;', $html );
	}


	/**
	 * Test the select field selects the saved setting, or the default without settings.
	 */
	public function test_select() {

		$args = array(
			'id'             => 'transients_expiration',
			'default'        => DAY_IN_SECONDS,
			'select_options' => array(
				HOUR_IN_SECONDS => '1 hour',
				DAY_IN_SECONDS  => '1 day',
			),
		);

		// Default without settings.
		delete_option( TRANSLATION_STATS_WP_OPTION );

		$html = $this->render( new Settings_Field_Select(), $args );

		$this->assertStringContainsString( '<select name="tstats_settings[settings][transients_expiration]"', $html );
		$this->assertStringContainsString( '<option value="' . DAY_IN_SECONDS . '" selected=\'selected\'>1 day</option>', $html );
		$this->assertStringContainsString( '<option value="' . HOUR_IN_SECONDS . '">1 hour</option>', $html );

		// Saved setting.
		$this->set_settings( array( 'transients_expiration' => HOUR_IN_SECONDS ) );

		$html = $this->render( new Settings_Field_Select(), $args );

		$this->assertStringContainsString( '<option value="' . HOUR_IN_SECONDS . '" selected=\'selected\'>1 hour</option>', $html );
		$this->assertStringContainsString( 'Field description &lt;script&gt;', $html );
	}


	/**
	 * Test the language select field with the site default option, selecting the saved setting.
	 */
	public function test_select_language() {

		// The available translations from WordPress.org aren't needed.
		$this->mock_translate_api( 500 );

		$this->set_settings( array( 'translation_language' => 'site-default' ) );

		$html = $this->render(
			new Settings_Field_Select_Language(),
			array(
				'id'      => 'translation_language',
				'default' => 'site-default',
			)
		);

		$this->assertStringContainsString( 'name="tstats_settings[settings][translation_language]"', $html );
		$this->assertMatchesRegularExpression( '/<option value="site-default"[^>]*selected/', $html );
		$this->assertStringContainsString( 'Field description &lt;script&gt;', $html );
	}


	/**
	 * Test the hidden field uses the saved setting, or the default without settings.
	 */
	public function test_hidden() {

		$args = array(
			'id'      => 'settings_version',
			'default' => TRANSLATION_STATS_SETTINGS_VERSION,
		);

		delete_option( TRANSLATION_STATS_WP_OPTION );

		$this->assertStringContainsString(
			'name="tstats_settings[settings][settings_version]" id="tstats_settings[settings][settings_version]" type="hidden" value="' . TRANSLATION_STATS_SETTINGS_VERSION . '"',
			$this->render( new Settings_Field_Hidden(), $args )
		);

		$this->set_settings( array( 'settings_version' => '0.9' ) );

		$this->assertStringContainsString( 'type="hidden" value="0.9"', $this->render( new Settings_Field_Hidden(), $args ) );
	}


	/**
	 * Test the button field, with a confirmation escaped for JavaScript.
	 */
	public function test_button() {

		$html = $this->render(
			new Settings_Field_Button(),
			array(
				'id'           => 'reset_settings',
				'name'         => 'reset_settings',
				'label'        => 'Reset settings',
				'class'        => 'secondary',
				'wrap'         => false,
				'formaction'   => 'options.php',
				'confirmation' => "Are you sure? It's permanent.",
			)
		);

		$this->assertStringContainsString( 'name="reset_settings"', $html );
		$this->assertStringContainsString( 'id="tstats_settings[settings][reset_settings]"', $html );
		$this->assertStringContainsString( 'formaction="options.php"', $html );
		$this->assertStringContainsString( 'value="Reset settings"', $html );
		$this->assertStringContainsString( 'onclick="return confirm( &#039;Are you sure? It\&#039;s permanent.&#039; )"', $html );
		$this->assertStringContainsString( 'Field description &lt;script&gt;', $html );
	}


	/**
	 * Test the button field without confirmation.
	 */
	public function test_button_without_confirmation() {

		$html = $this->render(
			new Settings_Field_Button(),
			array(
				'id'           => 'delete_transients',
				'name'         => 'delete_transients',
				'class'        => 'secondary',
				'wrap'         => false,
				'formaction'   => 'options.php',
				'confirmation' => '',
			)
		);

		$this->assertStringContainsString( 'name="delete_transients"', $html );
		$this->assertStringNotContainsString( 'confirm(', $html );
	}
}
