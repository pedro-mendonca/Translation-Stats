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
	 * Render the theme widget content stats.
	 *
	 * @param bool $force_update   True: Force get new stats. False: Use transients.
	 *
	 * @return array{0: true|WP_Error, 1: string}  The result and the rendered HTML.
	 */
	private function render_theme_widget_content_stats( $force_update = false ) {

		$themes = new Themes();

		ob_start();
		$result = $themes->theme_widget_content_stats( 'twentytwentyfive', $this->get_locale( 'pt_PT', 'pt' ), $force_update );
		return array( $result, (string) ob_get_clean() );
	}


	/**
	 * Test the theme widget content stats render the stats bar of the theme project, cached.
	 */
	public function test_theme_widget_content_stats() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		list( $result, $html ) = $this->render_theme_widget_content_stats();

		$this->assertTrue( $result );
		$this->assertStringContainsString( '<div class="translation-stats-content-stats widget-inside" data-slug="twentytwentyfive">', $html );
		$this->assertSame( 1, substr_count( $html, 'content__subproject' ) );
		$this->assertStringContainsString( '<div class="content__subproject project">', $html );
		$this->assertStringContainsString( 'href="https://translate.wordpress.org/projects/wp-themes/twentytwentyfive/pt/default"', $html );
		$this->assertStringContainsString( 'div.translation-stats-content-stats[data-slug="twentytwentyfive"] div.subproject.project {', $html );
		$this->assertStringContainsString( '<span class="subproject-bar__percentage">95%</span>', $html );
		$this->assertStringContainsString( '<span class="subproject-bar__name">Theme</span>', $html );

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
		$this->assertStringContainsString( 'Theme: <strong>Not found</strong>', $html );
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
}
