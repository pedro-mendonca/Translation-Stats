<?php
/**
 * Class Settings_Section_Themes Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings_Section_Themes;


/**
 * Settings_Section_Themes test case.
 */
class Test_Settings_Section_Themes extends Translation_Stats_TestCase {


	/**
	 * Add the test themes: one on WordPress.org, in the themes updates transient, and other not.
	 */
	public function set_up() {
		parent::set_up();

		$this->add_test_themes();

		set_site_transient(
			'update_themes',
			(object) array(
				'no_update' => array(
					'tstats-theme-wporg' => array(
						'theme' => 'tstats-theme-wporg',
						'url'   => 'https://wordpress.org/themes/tstats-theme-wporg/',
					),
				),
			)
		);
	}


	/**
	 * Restore the theme directories.
	 */
	public function tear_down() {
		$this->remove_test_themes();

		parent::tear_down();
	}


	/**
	 * Render the themes section.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render() {

		$section = new Settings_Section_Themes();

		ob_start();
		$section->render_custom_section();
		return (string) ob_get_clean();
	}


	/**
	 * Get the HTML of a theme row.
	 *
	 * @param string $html         The rendered HTML.
	 * @param string $theme_slug   Theme slug.
	 *
	 * @return string  The row HTML.
	 */
	private function get_row( $html, $theme_slug ) {

		$this->assertSame( 1, preg_match( '#<tr class="[a-z]+" data-theme="' . $theme_slug . '">.*?</tr>#s', $html, $matches ), "Row of '{$theme_slug}' not found." );

		return $matches[0];
	}


	/**
	 * Test the themes section data, in the themes settings page.
	 */
	public function test_section() {

		$section = new Settings_Section_Themes();

		$this->assertSame( 'themes', $section->section()['id'] );
		$this->assertSame( TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'themes', $section->section()['page'] );
	}


	/**
	 * Test the themes table, with only the themes on WordPress.org selectable.
	 */
	public function test_render_custom_section() {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ) );

		$html = $this->render();

		$this->assertStringContainsString( 'id="themes-search-input"', $html );
		$this->assertStringContainsString( 'from the list of installed themes. <a href="' . admin_url( 'themes.php' ) . '" aria-label="View installed themes">View installed themes</a>', $html );
		$this->assertStringContainsString( '<table id="tstats-table-themes" class="tstats-theme-list-table widefat plugins tablesorter">', $html );

		// Theme on WordPress.org, not selected, with the link to the Locale WordPress.org themes directory.
		$row = $this->get_row( $html, 'tstats-theme-wporg' );

		$this->assertStringContainsString( 'class="inactive"', $row );
		$this->assertStringContainsString( '<input name="tstats_settings[themes][tstats-theme-wporg][enabled]"  id="themes_tstats-theme-wporg" class="checkbox-theme" type="checkbox" value="true" />', $row );
		$this->assertStringContainsString( '<a href="https://pt.wordpress.org/themes/tstats-theme-wporg/" target="_blank">Translation Stats Theme on WordPress.org</a>', $row );
		$this->assertStringContainsString( '<a href="https://translationstats.com" target="_blank">Translation Stats</a>', $row );
		$this->assertStringContainsString( 'dashicons-yes', $row );

		// Theme not on WordPress.org, without checkbox, and with a different Text Domain.
		$row = $this->get_row( $html, 'tstats-theme-other' );

		$this->assertStringContainsString( 'class="disabled"', $row );
		$this->assertStringNotContainsString( '<input', $row );
		$this->assertStringNotContainsString( '<a href', $row );
		$this->assertStringContainsString( 'dashicons-no', $row );
		$this->assertStringContainsString( '<code class="code-error">other-text-domain</code>', $row );
	}


	/**
	 * Test the selected themes are checked, and the select all checkbox.
	 */
	public function test_render_custom_section_selected() {

		// No theme selected.
		$this->assertStringContainsString( '<input class="all_themes" id="all_themes" type="checkbox" value="true"  data-indeterminate="false" />', $this->render() );

		// Theme selected.
		update_option(
			TRANSLATION_STATS_WP_OPTION,
			array(
				'settings' => array( 'translation_language' => 'en_US' ),
				'themes'   => array( 'tstats-theme-wporg' => array( 'enabled' => true ) ),
			)
		);

		$html = $this->render();
		$row  = $this->get_row( $html, 'tstats-theme-wporg' );

		$this->assertStringContainsString( 'class="active"', $row );
		$this->assertStringContainsString( "checked='checked'", $row );
		$this->assertMatchesRegularExpression( '/id="all_themes" type="checkbox" value="true"\s+checked=\'checked\' data-indeterminate="false"/', $html );

		// The link to WordPress.org themes directory, without Locale subdomain for 'en_US'.
		$this->assertStringContainsString( 'href="https://wordpress.org/themes/tstats-theme-wporg/"', $row );
	}
}
