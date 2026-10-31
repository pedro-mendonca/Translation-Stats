<?php
/**
 * Class Settings_Section_Plugins Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Settings_Section_Plugins;

/**
 * Settings_Section_Plugins test case.
 */
class Test_Settings_Section_Plugins extends Translation_Stats_TestCase {


	/**
	 * Set up the settings, the plugins on WordPress.org and the API mock for each test.
	 */
	public function set_up() {
		parent::set_up();

		// The available translations from WordPress.org aren't needed.
		$this->mock_translate_api( 500 );

		$this->set_settings(
			array( 'translation_language' => 'pt_PT' ),
			array(
				'hello-dolly' => array(
					'enabled' => 'true',
					'stable'  => 'true',
				),
			)
		);

		$this->set_plugin_on_wporg( 'hello-dolly' );
	}


	/**
	 * Set Hello Dolly as a plugin on WordPress.org, with a slug. Akismet isn't on WordPress.org.
	 *
	 * @param string $slug   Plugin slug on WordPress.org.
	 */
	private function set_plugin_on_wporg( $slug ) {

		set_site_transient(
			'update_plugins',
			(object) array(
				'last_checked' => time(),
				'response'     => array(),
				'no_update'    => array(
					'hello.php' => (object) array(
						'id'   => 'w.org/plugins/' . $slug,
						'slug' => $slug,
						'url'  => 'https://wordpress.org/plugins/' . $slug . '/',
					),
				),
			)
		);
	}


	/**
	 * Render the plugins settings table.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render_plugins_list() {

		$section = new Settings_Section_Plugins();

		ob_start();
		$section->plugins_list();
		return (string) ob_get_clean();
	}


	/**
	 * Test the plugins on WordPress.org can be selected, with their enabled subprojects.
	 */
	public function test_plugins_list_plugin_on_wporg() {

		$html = $this->render_plugins_list();

		$this->assertStringContainsString( '<tr class="active" data-plugin="hello-dolly" data-subprojects="1">', $html );
		$this->assertMatchesRegularExpression( '/<input name="tstats_settings\[plugins\]\[hello-dolly\]\[enabled\]"\s+checked=\'checked\'/', $html );
		$this->assertMatchesRegularExpression( '/<input name="tstats_settings\[plugins\]\[hello-dolly\]\[stable\]"\s+checked=\'checked\'/', $html );
		$this->assertMatchesRegularExpression( '/<input name="tstats_settings\[plugins\]\[hello-dolly\]\[dev\]"\s+class="checkbox-plugin-subproject"/', $html );

		// Link to the plugin on the Locale subdomain of WordPress.org.
		$this->assertStringContainsString( '<a href="https://pt.wordpress.org/plugins/hello-dolly/" target="_blank">Hello Dolly</a>', $html );
		$this->assertStringContainsString( '<a href="http://ma.tt/" target="_blank">Matt Mullenweg</a>', $html );

		// The slug matches the text domain.
		$this->assertMatchesRegularExpression( '/data-plugin="hello-dolly".*?dashicons-yes/s', $html );
	}


	/**
	 * Test the plugins not on WordPress.org are listed disabled, without checkboxes.
	 */
	public function test_plugins_list_plugin_not_on_wporg() {

		$html = $this->render_plugins_list();

		$this->assertStringContainsString( '<tr class="disabled" data-plugin="akismet" data-subprojects="0">', $html );
		$this->assertStringNotContainsString( 'name="tstats_settings[plugins][akismet][enabled]"', $html );
		$this->assertStringNotContainsString( 'name="tstats_settings[plugins][akismet][stable]"', $html );
	}


	/**
	 * Test the table header checkboxes are checked and indeterminate according to the enabled subprojects.
	 */
	public function test_plugins_list_header() {

		$html = $this->render_plugins_list();

		// All the plugins on WordPress.org have the stable subproject enabled.
		$this->assertMatchesRegularExpression( '/id="subprojects_stable" type="checkbox" value="true"\s+checked=\'checked\' data-indeterminate="false"/', $html );
		$this->assertMatchesRegularExpression( '/id="subprojects_dev" type="checkbox" value="true"\s+data-indeterminate="false"/', $html );

		// Only some subprojects are enabled.
		$this->assertMatchesRegularExpression( '/id="all_plugins" type="checkbox" value="true"\s+checked=\'checked\' data-indeterminate="true"/', $html );
	}


	/**
	 * Test the slug and text domain column shows the text domain different from the slug.
	 */
	public function test_plugins_list_slug_different_from_text_domain() {

		$this->set_plugin_on_wporg( 'hello' );

		$html = $this->render_plugins_list();

		$this->assertMatchesRegularExpression( '/data-plugin="hello".*?dashicons-no.*?<code class="">hello<\/code><br>\s*<code class="code-error">hello-dolly<\/code>/s', $html );
	}


	/**
	 * Test the table columns can be filtered.
	 */
	public function test_plugins_list_table_args_filter() {

		add_filter(
			'translation_stats_settings_plugins_table_args',
			function ( $table_args ) {
				$table_args['show_author']           = false;
				$table_args['show_slug_text_domain'] = false;
				return $table_args;
			}
		);

		$html = $this->render_plugins_list();

		$this->assertStringNotContainsString( 'column-author', $html );
		$this->assertStringNotContainsString( 'plugin-author', $html );
		$this->assertStringNotContainsString( 'column-slug-text-domain', $html );
		$this->assertStringContainsString( 'data-plugin="hello-dolly"', $html );
	}


	/**
	 * Test the section with the plugins search and the link to the selected plugins.
	 */
	public function test_render_custom_section() {

		$section = new Settings_Section_Plugins();

		ob_start();
		$section->render_custom_section();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'id="plugins-search-input"', $html );
		$this->assertStringContainsString( 'id="plugins-search-reset"', $html );
		$this->assertStringContainsString( 'plugins.php?plugin_status=translation_stats', $html );
		$this->assertStringContainsString( 'id="tstats-table-plugins"', $html );
	}
}
