<?php
/**
 * Class Themes Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Themes;


/**
 * Themes test case.
 */
class Test_Themes extends Translation_Stats_TestCase {


	/**
	 * Add the test themes.
	 */
	public function set_up() {
		parent::set_up();
		$this->add_test_themes();
	}


	/**
	 * Remove the test themes.
	 */
	public function tear_down() {
		$this->remove_test_themes();
		parent::tear_down();
	}


	/**
	 * Render the theme widget content stats.
	 *
	 * @param bool $force_update   True: Force get new stats. False: Use transients.
	 *
	 * @return array{0: true|WP_Error, 1: string}  The result and the rendered HTML.
	 */
	private function render_theme_widget_content_stats( $force_update = false ) {

		$themes = new Themes();

		ob_start();
		$result = $themes->theme_widget_content_stats( 'tstats-theme-wporg', $this->get_locale( 'pt_PT', 'pt' ), $force_update );
		return array( $result, (string) ob_get_clean() );
	}


	/**
	 * Test the theme widget content stats render the stats bar of the theme project, cached.
	 */
	public function test_theme_widget_content_stats() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		list( $result, $html ) = $this->render_theme_widget_content_stats();

		$this->assertTrue( $result );
		$this->assertStringContainsString( '<div class="translation-stats-content-stats widget-inside" data-slug="tstats-theme-wporg">', $html );
		$this->assertSame( 1, substr_count( $html, 'content__subproject' ) );
		$this->assertStringContainsString( '<div class="content__subproject project">', $html );
		$this->assertStringContainsString( 'href="https://translate.wordpress.org/projects/wp-themes/tstats-theme-wporg/pt/default"', $html );
		$this->assertStringContainsString( 'div.translation-stats-content-stats[data-slug="tstats-theme-wporg"] div.subproject.project {', $html );
		$this->assertStringContainsString( '<span class="subproject-bar__percentage">95%</span>', $html );
		$this->assertStringContainsString( '<span class="subproject-bar__name">Translation Stats Theme on WordPress.org</span>', $html );

		// Cached.
		$this->render_theme_widget_content_stats();

		$this->assertCount( 1, $this->requested_urls );

		// Forced update.
		$this->render_theme_widget_content_stats( true );

		$this->assertCount( 2, $this->requested_urls );
	}


	/**
	 * Test the theme widget content stats of a theme without translation project.
	 */
	public function test_theme_widget_content_stats_not_found() {

		$this->mock_translate_api( 404 );

		list( $result, $html ) = $this->render_theme_widget_content_stats();

		$this->assertTrue( $result );
		$this->assertStringContainsString( 'Translation Stats Theme on WordPress.org: <strong>Not found</strong>', $html );
	}


	/**
	 * Test the theme widget content stats return the error if translate.WordPress.org is unavailable, without rendering.
	 */
	public function test_theme_widget_content_stats_api_unavailable() {

		$this->mock_translate_api( 503 );

		list( $result, $html ) = $this->render_theme_widget_content_stats();

		$this->assertWPError( $result );
		$this->assertSame( '', $html );
	}


	/**
	 * Enable the test theme on WordPress.org in the settings, with the 'pt_PT' translation language.
	 *
	 * @param bool $enabled   Whether the theme is enabled.
	 */
	private function set_theme_settings( $enabled = true ) {

		update_option(
			TRANSLATION_STATS_WP_OPTION,
			array(
				'settings' => array(
					'translation_language'  => 'pt_PT',
					'transients_expiration' => HOUR_IN_SECONDS,
				),
				'themes'   => $enabled ? array( 'tstats-theme-wporg' => array( 'enabled' => true ) ) : array(),
			)
		);

		set_site_transient( 'update_themes', (object) array( 'no_update' => array( 'tstats-theme-wporg' => array() ) ) );
	}


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$themes = new Themes();

		$this->assertSame( 10, has_filter( 'wp_prepare_themes_for_js', array( $themes, 'add_themes_stats' ) ) );
	}


	/**
	 * Test the stats are added only to the enabled themes on WordPress.org, with the stats placeholder if not cached.
	 */
	public function test_add_themes_stats() {

		$this->set_theme_settings();
		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$themes = new Themes();

		$prepared = $themes->add_themes_stats(
			array(
				'tstats-theme-wporg' => array( 'id' => 'tstats-theme-wporg' ),
				'tstats-theme-other' => array( 'id' => 'tstats-theme-other' ),
			)
		);

		$this->assertArrayNotHasKey( 'translationStats', $prepared['tstats-theme-other'] );

		$stats = $prepared['tstats-theme-wporg']['translationStats'];

		$this->assertFalse( $stats['cached'] );
		$this->assertNull( $stats['percent'] );
		$this->assertSame( 'Português', $stats['language'] );
		$this->assertStringContainsString( 'Translation for <a href="https://translate.wordpress.org/locale/pt/default/wp-themes/tstats-theme-wporg" target="_blank">Português</a>', $stats['title'] );
		$this->assertStringContainsString( 'tstats-update-button', $stats['title'] );
		$this->assertStringContainsString( 'translation-stats-placeholder', $stats['stats'] );
		$this->assertStringContainsString( 'Translation Stats Theme on WordPress.org', $stats['stats'] );
		$this->assertMatchesRegularExpression( '/class="[^"]*translation-stats-loading[^"]*hidden/', $stats['notice'] );

		// Without requesting the stats, only the core available translations.
		$this->assertSame( array(), preg_grep( '#^https://translate\.wordpress\.org/#', $this->requested_urls ) );
	}


	/**
	 * Test the stats added to the themes are the cached stats, with the percent translated.
	 */
	public function test_add_themes_stats_cached() {

		$this->set_theme_settings();
		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$themes = new Themes();
		$locale = $this->get_locale( 'pt_PT', 'pt' );

		// Not cached.
		$this->assertNull( $themes->percent_translated( 'tstats-theme-wporg', $locale ) );

		$this->render_theme_widget_content_stats();

		$stats = $themes->add_themes_stats( array( 'tstats-theme-wporg' => array() ) )['tstats-theme-wporg']['translationStats'];

		$this->assertTrue( $stats['cached'] );
		$this->assertSame( 95, $stats['percent'] );
		$this->assertStringContainsString( '<span class="subproject-bar__percentage">95%</span>', $stats['stats'] );
		$this->assertStringContainsString( 'translation-stats-cached', $stats['notice'] );
	}


	/**
	 * Test the percent translated of a theme without translation for the Locale is 0.
	 */
	public function test_percent_translated_without_translation() {

		$this->mock_translate_api( 404 );

		$this->render_theme_widget_content_stats();

		$this->assertSame( 0, ( new Themes() )->percent_translated( 'tstats-theme-wporg', $this->get_locale( 'pt_PT', 'pt' ) ) );
	}


	/**
	 * Test no stats are added without enabled themes, or with the 'en_US' translation language.
	 */
	public function test_add_themes_stats_not_added() {

		$themes   = new Themes();
		$prepared = array( 'tstats-theme-wporg' => array( 'id' => 'tstats-theme-wporg' ) );

		// Theme not enabled.
		$this->set_theme_settings( false );

		$this->assertSame( $prepared, $themes->add_themes_stats( $prepared ) );

		// Translation language 'en_US'.
		$this->set_theme_settings();
		$this->set_settings( array( 'translation_language' => 'en_US' ) );

		$this->assertSame( $prepared, $themes->add_themes_stats( $prepared ) );
	}


	/**
	 * Test the themes settings URL, only for the users who can manage the settings.
	 */
	public function test_settings_url() {

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$this->assertSame( '', Themes::settings_url() );

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		$this->assertSame( admin_url( 'options-general.php?page=translation-stats#themes' ), Themes::settings_url() );
	}
}
