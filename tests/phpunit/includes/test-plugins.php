<?php
/**
 * Class Plugins Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\GP_Locale;
use Translation_Stats\Locale;
use Translation_Stats\Plugins;


/**
 * Plugins test case.
 */
class Test_Plugins extends Translation_Stats_TestCase {


	/**
	 * Get a Locale object.
	 *
	 * @param string      $wp_locale   WordPress Locale ( e.g. 'pt_PT' ).
	 * @param string      $slug        Locale slug ( e.g. 'pt' or 'pt/ao90' ).
	 * @param string|null $root_slug   Root Locale slug, for variants ( e.g. 'pt' ).
	 *
	 * @return Locale  Locale object.
	 */
	private function get_locale( $wp_locale, $slug, $root_slug = null ) {

		$gp_locale = new GP_Locale();

		$gp_locale->wp_locale = $wp_locale;
		$gp_locale->slug      = $slug;

		if ( null !== $root_slug ) {
			$gp_locale->root_slug = $root_slug;
		}

		return new Locale( $gp_locale );
	}


	/**
	 * Get a translate.WordPress.org API subproject response body.
	 *
	 * @return array  Response body with translation sets.
	 */
	private function get_subproject_body() {
		return array(
			'translation_sets' => array(
				array(
					'locale'             => 'pt',
					'slug'               => 'default',
					'percent_translated' => 95,
				),
				array(
					'locale'             => 'pt',
					'slug'               => 'ao90',
					'percent_translated' => 80,
				),
			),
		);
	}


	/**
	 * Test the hooks added on construct.
	 */
	public function test_construct() {

		$plugins = new Plugins();

		$this->assertSame( 10, has_filter( 'manage_plugins_columns', array( $plugins, 'add_translation_stats_column' ) ) );
		$this->assertSame( 10, has_action( 'manage_plugins_custom_column', array( $plugins, 'render_plugin_stats_column' ) ) );
		$this->assertSame( 10, has_action( 'pre_current_active_plugins', array( $plugins, 'plugins_filter_by_translation_stats' ) ) );
		$this->assertSame( 10, has_filter( is_multisite() ? 'views_plugins-network' : 'views_plugins', array( $plugins, 'plugins_status_link' ) ) );
	}


	/**
	 * Test the Translation Stats column is added only if the translation language isn't 'en_US'.
	 */
	public function test_add_translation_stats_column() {

		$plugins = new Plugins();

		// Default site language 'en_US'.
		$this->set_settings();

		$this->assertSame( array( 'name' => 'Plugin' ), $plugins->add_translation_stats_column( array( 'name' => 'Plugin' ) ) );

		// Translation language 'pt_PT'.
		$this->set_settings( array( 'translation_language' => 'pt_PT' ) );

		$columns = $plugins->add_translation_stats_column( array( 'name' => 'Plugin' ) );

		$this->assertArrayHasKey( 'translation-stats', $columns );
		$this->assertStringContainsString( 'page=translation-stats#plugins', $columns['translation-stats'] );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_plugin_subproject_stats() {
		return array(
			'root locale'          => array(
				'wp_locale'       => 'pt_PT',
				'slug'            => 'pt',
				'root_slug'       => null,
				'expected_result' => 95,
			),
			'variant locale'       => array(
				'wp_locale'       => 'pt_PT_ao90',
				'slug'            => 'pt/ao90',
				'root_slug'       => 'pt',
				'expected_result' => 80,
			),
			'locale without stats' => array(
				'wp_locale'       => 'pt_BR',
				'slug'            => 'pt-br',
				'root_slug'       => null,
				'expected_result' => false,
			),
		);
	}


	/**
	 * Test get the subproject stats for the Locale translation set.
	 *
	 * @dataProvider provide_test_plugin_subproject_stats
	 *
	 * @param string      $wp_locale         WordPress Locale.
	 * @param string      $slug              Locale slug.
	 * @param string|null $root_slug         Root Locale slug, for variants.
	 * @param int|false   $expected_result   Expected translated percentage, or false if no stats.
	 */
	public function test_plugin_subproject_stats( $wp_locale, $slug, $root_slug, $expected_result ) {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$plugins = new Plugins();

		$stats = $plugins->plugin_subproject_stats( $this->get_locale( $wp_locale, $slug, $root_slug ), 'translation-stats', 'stable', false );

		$this->assertSame( array( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable' ), $this->requested_urls );

		if ( false === $expected_result ) {
			$this->assertFalse( $stats );
		} else {
			$this->assertSame( $expected_result, $stats->percent_translated );
		}
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_plugin_subproject_stats_not_found() {
		return array(
			'no translation sets' => array(
				'response' => 200,
				'body'     => array( 'translation_sets' => array() ),
			),
			'not found'           => array(
				'response' => 404,
				'body'     => '',
			),
		);
	}


	/**
	 * Test get the subproject stats when there are no stats for the subproject.
	 *
	 * @dataProvider provide_test_plugin_subproject_stats_not_found
	 *
	 * @param int|WP_Error $response   The mocked API response.
	 * @param mixed        $body       The mocked API response body.
	 */
	public function test_plugin_subproject_stats_not_found( $response, $body ) {

		$this->mock_translate_api( $response, $body );

		$plugins = new Plugins();

		$this->assertFalse( $plugins->plugin_subproject_stats( $this->get_locale( 'pt_PT', 'pt' ), 'translation-stats', 'stable', false ) );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_plugin_subproject_stats_api_unavailable() {
		return array(
			'rate limited' => array(
				'response' => 429,
			),
			'server error' => array(
				'response' => 503,
			),
			'error'        => array(
				'response' => new WP_Error( 'http_request_failed', 'Connection timed out.' ),
			),
		);
	}


	/**
	 * Test get the subproject stats when translate.WordPress.org is unavailable returns an error, which isn't cached.
	 *
	 * @dataProvider provide_test_plugin_subproject_stats_api_unavailable
	 *
	 * @param int|WP_Error $response   The mocked API response.
	 */
	public function test_plugin_subproject_stats_api_unavailable( $response ) {

		$this->mock_translate_api( $response );

		$plugins = new Plugins();

		$stats = $plugins->plugin_subproject_stats( $this->get_locale( 'pt_PT', 'pt' ), 'translation-stats', 'stable', false );

		$this->assertWPError( $stats );
		$this->assertSame( 503, $stats->get_error_data()['status'] );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
	}


	/**
	 * Test the subproject stats are cached in a transient, unless forcing the update.
	 */
	public function test_plugin_subproject_stats_cache() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$plugins = new Plugins();
		$locale  = $this->get_locale( 'pt_PT', 'pt' );

		$plugins->plugin_subproject_stats( $locale, 'translation-stats', 'stable', false );

		$this->assertSame( 95, get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' )->percent_translated );

		// The second request uses the transient.
		$stats = $plugins->plugin_subproject_stats( $locale, 'translation-stats', 'stable', false );

		$this->assertSame( 95, $stats->percent_translated );
		$this->assertCount( 1, $this->requested_urls );

		// Forcing the update ignores the transient.
		$plugins->plugin_subproject_stats( $locale, 'translation-stats', 'stable', true );

		$this->assertCount( 2, $this->requested_urls );
	}


	/**
	 * Test the status link for the plugins enabled in Translation Stats.
	 */
	public function test_plugins_status_link() {

		$plugins = new Plugins();

		$status_links = array(
			'all' => '<a href="plugins.php?plugin_status=all" class="current" aria-current="page">All</a>',
		);

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello' => array( 'enabled' => 'true' ) ) );

		// User without permission to update plugins.
		$this->assertSame( $status_links, $plugins->plugins_status_link( $status_links ) );

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		$links = $plugins->plugins_status_link( $status_links );

		$this->assertArrayHasKey( 'translation_stats', $links );
		$this->assertStringContainsString( '<span class="count">(1)</span>', $links['translation_stats'] );
		$this->assertStringNotContainsString( 'class="current"', $links['translation_stats'] );
		$this->assertSame( $status_links['all'], $links['all'] );

		// Current status is 'translation_stats'.
		$_REQUEST['plugin_status'] = 'translation_stats';

		$links = $plugins->plugins_status_link( $status_links );

		unset( $_REQUEST['plugin_status'] );

		$this->assertStringContainsString( 'class="current"', $links['translation_stats'] );
		$this->assertStringNotContainsString( 'class="current"', $links['all'] );
	}


	/**
	 * Test the status link isn't added without enabled plugins or with 'en_US' translation language.
	 */
	public function test_plugins_status_link_not_added() {

		$plugins = new Plugins();

		$status_links = array(
			'all' => '<a href="plugins.php?plugin_status=all">All</a>',
		);

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );

		// No enabled plugins.
		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello' => array( 'enabled' => '' ) ) );

		$this->assertSame( $status_links, $plugins->plugins_status_link( $status_links ) );

		// Translation language 'en_US'.
		$this->set_settings( array(), array( 'hello' => array( 'enabled' => 'true' ) ) );

		$this->assertSame( $status_links, $plugins->plugins_status_link( $status_links ) );
	}
}
