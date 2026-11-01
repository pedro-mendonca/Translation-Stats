<?php
/**
 * Class Plugins Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Plugin_Project;
use Translation_Stats\Plugins;


/**
 * Plugins test case.
 */
class Test_Plugins extends Translation_Stats_TestCase {


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
	 * Test the update button has the update status in the widget title, announced to screen readers.
	 */
	public function test_plugin_update_button() {

		$plugins = new Plugins();

		ob_start();
		$plugins->plugin_update_button();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( '<span class="tstats-update-status" aria-live="polite"></span>', $html );
		$this->assertStringContainsString( 'tstats-update-button', $html );
	}


	/**
	 * Render the plugin widget content.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render_plugin_widget_content() {

		$plugins = new Plugins();

		ob_start();
		$plugins->plugin_widget_content( 'translation-stats', $this->get_locale( 'pt_PT', 'pt' ) );
		return (string) ob_get_clean();
	}


	/**
	 * Cache the stats of the plugin subprojects.
	 *
	 * @param array $subprojects   Slugs of the subprojects to cache.
	 */
	private function cache_subprojects_stats( $subprojects ) {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Plugin_Project( 'translation-stats' );

		foreach ( $subprojects as $subproject ) {
			$project->subproject_stats( $this->get_locale( 'pt_PT', 'pt' ), $subproject, false );
		}

		// Reset the requests, to check the requests made while rendering.
		$this->mock_translate_api( 200, $this->get_subproject_body() );
	}


	/**
	 * Test the plugin widget content renders the cached stats, without requesting the API or loading them with the REST API.
	 */
	public function test_plugin_widget_content_cached() {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'translation-stats' => array( 'stable' => 'true' ) ) );

		$this->cache_subprojects_stats( array( 'dev', 'dev-readme', 'stable', 'stable-readme' ) );

		$html = $this->render_plugin_widget_content();

		$this->assertStringContainsString( 'translation-stats-cached', $html );
		$this->assertStringContainsString( 'translation-stats-content-stats', $html );
		$this->assertStringContainsString( '95%', $html );
		$this->assertStringContainsString( 'tr[data-slug="translation-stats"] div.subproject.stable {', $html );
		$this->assertStringContainsString( '<div class="content__subproject stable">', $html );
		$this->assertSame( array(), $this->requested_urls );

		// The status notice is below the stats, to not move them when shown.
		$this->assertGreaterThan( strpos( $html, 'translation-stats-content-stats' ), strpos( $html, 'translation-stats-loading' ) );
	}


	/**
	 * Test the plugin stats widget passes the project slug and Locale to the content, rendering the cached stats.
	 */
	public function test_render_plugin_stats() {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'translation-stats' => array( 'stable' => 'true' ) ) );

		$this->cache_subprojects_stats( array( 'dev', 'dev-readme', 'stable', 'stable-readme' ) );

		$received = array();
		add_action(
			'translation_stats_plugin_widget_content',
			function ( $project_slug, $locale ) use ( &$received ) {
				$received = array( $project_slug, $locale->wp_locale );
			},
			10,
			2
		);

		$plugins = new Plugins();

		ob_start();
		$plugins->render_plugin_stats( 'translation-stats' );
		$html = (string) ob_get_clean();

		$this->assertSame( array( 'translation-stats', 'pt_PT' ), $received );
		$this->assertStringContainsString( 'translation-stats-title', $html );
		$this->assertStringContainsString( 'translation-stats-cached', $html );
		$this->assertStringContainsString( '95%', $html );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_plugin_widget_content_not_cached() {
		return array(
			'no cached stats'      => array(
				'subprojects' => array(),
			),
			'partial cached stats' => array(
				'subprojects' => array( 'dev', 'dev-readme', 'stable' ),
			),
		);
	}


	/**
	 * Test the plugin widget content without all the stats cached renders an empty stats placeholder, to load them with the REST API.
	 *
	 * @dataProvider provide_test_plugin_widget_content_not_cached
	 *
	 * @param array $subprojects   Slugs of the cached subprojects.
	 */
	public function test_plugin_widget_content_not_cached( $subprojects ) {

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'translation-stats' => array( 'stable' => 'true' ) ) );

		$this->cache_subprojects_stats( $subprojects );

		$html = $this->render_plugin_widget_content();

		// Placeholder with an empty bar for each enabled subproject.
		$this->assertStringContainsString( 'translation-stats-placeholder', $html );
		$this->assertSame( 1, substr_count( $html, 'content__subproject' ) );
		$this->assertStringContainsString( 'Stable', $html );
		$this->assertStringNotContainsString( 'subproject-bar__percentage', $html );

		// The status notice is hidden, only shown on errors, and loads the stats.
		$this->assertMatchesRegularExpression( '/class="[^"]*translation-stats-loading[^"]*hidden[^"]*"/', $html );
		$this->assertStringNotContainsString( 'translation-stats-cached', $html );
		$this->assertStringNotContainsString( 'Waiting', $html );

		$this->assertSame( array(), $this->requested_urls );
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

		// No settings, with the site language 'pt_PT'.
		add_filter(
			'locale',
			function () {
				return 'pt_PT';
			}
		);

		delete_option( TRANSLATION_STATS_WP_OPTION );

		$this->assertSame( $status_links, $plugins->plugins_status_link( $status_links ) );

		// Settings without plugins.
		update_option( TRANSLATION_STATS_WP_OPTION, array( 'settings' => array( 'translation_language' => 'pt_PT' ) ) );

		$this->assertSame( $status_links, $plugins->plugins_status_link( $status_links ) );
	}


	/**
	 * Set Hello Dolly as a plugin on WordPress.org, in the plugins updates transient.
	 */
	private function set_plugin_on_wporg() {

		$update_plugins = new stdClass();

		$update_plugins->no_update = array(
			'hello.php' => (object) array(
				'id'   => 'w.org/plugins/hello-dolly',
				'slug' => 'hello-dolly',
			),
		);

		set_site_transient( 'update_plugins', $update_plugins );
	}


	/**
	 * Render the Translation Stats column of a plugin.
	 *
	 * @param string $column_name   Column slug.
	 * @param string $plugin_file   Plugin file.
	 *
	 * @return string  The rendered HTML.
	 */
	private function render_plugin_stats_column( $column_name = 'translation-stats', $plugin_file = 'hello.php' ) {

		$plugins = new Plugins();

		ob_start();
		$plugins->render_plugin_stats_column( $column_name, $plugin_file );
		return (string) ob_get_clean();
	}


	/**
	 * Test the Translation Stats column renders nothing for other columns, the 'en_US' translation language, or plugins not enabled.
	 */
	public function test_render_plugin_stats_column_empty() {

		$this->set_plugin_on_wporg();

		// Other column.
		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello-dolly' => array( 'enabled' => 'true' ) ) );

		$this->assertSame( '', $this->render_plugin_stats_column( 'name' ) );

		// Translation language 'en_US'.
		$this->set_settings( array(), array( 'hello-dolly' => array( 'enabled' => 'true' ) ) );

		$this->assertSame( '', $this->render_plugin_stats_column() );

		// Plugin not enabled.
		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello-dolly' => array( 'enabled' => '' ) ) );

		$this->assertSame( '', $this->render_plugin_stats_column() );

		// Plugin on WordPress.org without slug.
		set_site_transient( 'update_plugins', (object) array( 'no_update' => array( 'hello.php' => (object) array( 'id' => 'w.org/plugins/hello-dolly' ) ) ) );

		$this->assertSame( '', $this->render_plugin_stats_column() );
	}


	/**
	 * Test the Translation Stats column shows an error for plugins not on WordPress.org.
	 */
	public function test_render_plugin_stats_column_plugin_not_on_wporg() {

		// Without WordPress.org data, the slug is from the plugin file.
		$this->set_settings(
			array(
				'translation_language' => 'pt_PT',
				'show_warnings'        => true,
			),
			array( 'hello' => array( 'enabled' => 'true' ) )
		);

		$this->mock_translate_api( 404 );

		$html = $this->render_plugin_stats_column();

		$this->assertMatchesRegularExpression( '/notice-error[^>]*>\s*<p>Plugin not found on WordPress.org/', $html );
		$this->assertStringNotContainsString( 'translation-stats-title', $html );
	}


	/**
	 * Test the Translation Stats column shows an error for plugins without translation project on WordPress.org.
	 */
	public function test_render_plugin_stats_column_project_not_on_wporg() {

		$this->set_plugin_on_wporg();

		$this->set_settings(
			array(
				'translation_language' => 'pt_PT',
				'show_warnings'        => true,
			),
			array( 'hello-dolly' => array( 'enabled' => 'true' ) )
		);

		$this->mock_translate_api( 404 );

		$html = $this->render_plugin_stats_column();

		$this->assertMatchesRegularExpression( '/notice-error[^>]*>\s*<p>Translation project not found on WordPress.org/', $html );
		$this->assertStringNotContainsString( 'translation-stats-title', $html );
	}


	/**
	 * Test the Translation Stats column renders the stats widget, with the debug info action.
	 */
	public function test_render_plugin_stats_column() {

		$this->set_plugin_on_wporg();

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello-dolly' => array( 'enabled' => 'true' ) ) );

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$received = array();
		add_action(
			'translation_stats_plugins_stats_widget__after',
			function ( $project_slug, $plugin_on_wporg, $plugin_translation_on_wporg ) use ( &$received ) {
				$received = array( $project_slug, $plugin_on_wporg, $plugin_translation_on_wporg );
			},
			10,
			3
		);

		$html = $this->render_plugin_stats_column();

		$this->assertStringContainsString( 'translation-stats-title', $html );
		$this->assertStringNotContainsString( 'notice-error', $html );
		$this->assertSame( array( 'hello-dolly', true, true ), $received );
	}


	/**
	 * Test the stats bar of a subproject not found on WordPress.org.
	 */
	public function test_render_stats_bar_not_found() {

		$plugins = new Plugins();

		ob_start();
		$plugins->render_stats_bar( false, $this->get_locale( 'pt_PT', 'pt' ), 'translation-stats', 'Stable', 'stable' );
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( '<a class="disabled"', $html );
		$this->assertStringContainsString( 'Stable: <strong>Not found</strong>', $html );
		$this->assertStringNotContainsString( 'href=', $html );
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_render_notices() {
		return array(
			'stable below the language pack threshold' => array(
				'project_stats'    => array(
					'stable' => (object) array( 'percent_translated' => 80 ),
					'dev'    => (object) array( 'percent_translated' => 50 ),
				),
				'development_mode' => true,
				'expected_result'  => array( 'notice-info', 'translation-stats/stable/pt/default', 'Stable (latest release)', '(currently 80%)' ),
			),
			'dev below the language pack threshold'    => array(
				'project_stats'    => array(
					'stable' => false,
					'dev'    => (object) array( 'percent_translated' => 50 ),
				),
				'development_mode' => true,
				'expected_result'  => array( 'notice-info', 'translation-stats/dev/pt/default', 'Development (trunk)', '(currently 50%)' ),
			),
			'not in development mode'                  => array(
				'project_stats'    => array(
					'stable' => (object) array( 'percent_translated' => 80 ),
				),
				'development_mode' => false,
				'expected_result'  => array(),
			),
			'stable above the language pack threshold' => array(
				'project_stats'    => array(
					'stable' => (object) array( 'percent_translated' => 95 ),
				),
				'development_mode' => true,
				'expected_result'  => array(),
			),
			'dev above the language pack threshold'    => array(
				'project_stats'    => array(
					'dev' => (object) array( 'percent_translated' => 95 ),
				),
				'development_mode' => true,
				'expected_result'  => array(),
			),
			'not prepared for localization'            => array(
				'project_stats'    => array(
					'stable' => false,
					'dev'    => false,
				),
				'development_mode' => false,
				'expected_result'  => array( 'notice-error', 'not <a href="https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/" target="_blank">properly prepared for localization</a>', 'https://wordpress.org/support/plugin/translation-stats' ),
			),
		);
	}


	/**
	 * Test the notices about the plugin language packs: info notices only in development mode, error notices always.
	 *
	 * @dataProvider provide_test_render_notices
	 *
	 * @param array $project_stats      Subprojects stats.
	 * @param bool  $development_mode   Whether the development mode is enabled.
	 * @param array $expected_result    Expected strings in the notice, or empty if no notice.
	 */
	public function test_render_notices( $project_stats, $development_mode, $expected_result ) {

		$this->set_settings( array( 'show_warnings' => true ) );

		add_filter( 'translation_stats_development_mode', $development_mode ? '__return_true' : '__return_false' );

		$plugins = new Plugins();

		ob_start();
		$plugins->render_notices( $project_stats, 'translation-stats', $this->get_locale( 'pt_PT', 'pt' ) );
		$html = (string) ob_get_clean();

		if ( empty( $expected_result ) ) {
			$this->assertSame( '', $html );
			return;
		}

		$this->assertStringContainsString( 'translation-stats-content-notices', $html );

		foreach ( $expected_result as $expected ) {
			$this->assertStringContainsString( $expected, $html );
		}
	}


	/**
	 * Set the plugins list table, with the installed plugins.
	 *
	 * @param int $per_page   Plugins per page.
	 * @param int $page       Current page.
	 */
	private function set_plugins_list_table( $per_page = 999, $page = 1 ) {
		global $wp_list_table;

		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-plugins-list-table.php';

		set_current_screen( 'plugins' );

		$wp_list_table = new WP_Plugins_List_Table( array( 'screen' => 'plugins' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$wp_list_table->items = array(
			'akismet/akismet.php' => array( 'Name' => 'Akismet' ),
			'hello.php'           => array( 'Name' => 'Hello Dolly' ),
			'other/other.php'     => array( 'Name' => 'Other' ),
		);

		$GLOBALS['page'] = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		add_filter(
			'plugins_per_page',
			function () use ( $per_page ) {
				return $per_page;
			}
		);
	}


	/**
	 * Filter the plugins list table.
	 *
	 * @param string $plugin_status   Current plugins status.
	 *
	 * @return array  Plugins files in the list table.
	 */
	private function filter_plugins_list_table( $plugin_status ) {
		global $wp_list_table;

		$_REQUEST['plugin_status'] = $plugin_status;

		$plugins = new Plugins();
		$plugins->plugins_filter_by_translation_stats( $wp_list_table->items );

		unset( $_REQUEST['plugin_status'] );

		return array_keys( $wp_list_table->items );
	}


	/**
	 * Test the plugins list is filtered to the plugins enabled in Translation Stats, with pagination.
	 */
	public function test_plugins_filter_by_translation_stats() {
		global $wp_list_table;

		$this->set_settings(
			array( 'translation_language' => 'pt_PT' ),
			array(
				'hello' => array( 'enabled' => 'true' ),
				'other' => array( 'enabled' => 'true' ),
			)
		);

		$this->set_plugins_list_table();

		$this->assertSame( array( 'hello.php', 'other/other.php' ), $this->filter_plugins_list_table( 'translation_stats' ) );
		$this->assertSame( 2, $wp_list_table->get_pagination_arg( 'total_items' ) );

		// Second page, with one plugin per page.
		$this->set_plugins_list_table( 1, 2 );

		$this->assertSame( array( 'other/other.php' ), $this->filter_plugins_list_table( 'translation_stats' ) );
		$this->assertSame( 2, $wp_list_table->get_pagination_arg( 'total_items' ) );
		$this->assertSame( 1, $wp_list_table->get_pagination_arg( 'per_page' ) );
	}


	/**
	 * Test the plugins list isn't filtered for other plugins status, or with the 'en_US' translation language.
	 */
	public function test_plugins_filter_by_translation_stats_not_filtered() {

		$all_plugins = array( 'akismet/akismet.php', 'hello.php', 'other/other.php' );

		$this->set_settings( array( 'translation_language' => 'pt_PT' ), array( 'hello' => array( 'enabled' => 'true' ) ) );

		$this->set_plugins_list_table();

		$this->assertSame( $all_plugins, $this->filter_plugins_list_table( 'all' ) );

		// Translation language 'en_US'.
		$this->set_settings( array(), array( 'hello' => array( 'enabled' => 'true' ) ) );

		$this->assertSame( $all_plugins, $this->filter_plugins_list_table( 'translation_stats' ) );
	}
}
