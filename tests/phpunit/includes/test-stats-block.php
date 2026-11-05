<?php
/**
 * Class Test_Stats_Block
 *
 * @package Translation_Stats
 */

use Translation_Stats\Plugin_Project;
use Translation_Stats\Stats_Block;
use Translation_Stats\Stats_Block_Attributes;
use Translation_Stats\Stats_Block_Cache;
use Translation_Stats\Theme_Project;

/**
 * Test the Translation Stats block.
 */
class Test_Stats_Block extends Translation_Stats_TestCase {


	/**
	 * Remove the scheduled stats loading and the filters after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		wp_unschedule_hook( Stats_Block_Cache::REFRESH_HOOK );
		remove_all_filters( 'translation_stats_block_load_stats' );
		parent::tear_down();
	}


	/**
	 * Cache the stats of plugin subprojects in pt_PT.
	 *
	 * @param string                $slug    Plugin slug.
	 * @param array<string, object> $stats   Stats of each subproject slug.
	 *
	 * @return void
	 */
	private function cache_plugin_stats( $slug, $stats ) {

		$project = new Plugin_Project( $slug );
		$locale  = Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) );

		foreach ( $stats as $subproject_slug => $subproject_stats ) {
			set_transient( $project->subproject_stats_transient( $locale, $subproject_slug ), $subproject_stats, HOUR_IN_SECONDS );
		}
	}


	/**
	 * Get subproject stats.
	 *
	 * @param int $percent   Percent translated.
	 *
	 * @return object   Subproject stats.
	 */
	private function stats( $percent ) {
		return (object) array(
			'percent_translated' => $percent,
			'current_count'      => $percent,
			'all_count'          => 100,
		);
	}


	/**
	 * Test the block project, from the type and slug.
	 */
	public function test_project() {

		$this->assertNull( Stats_Block_Attributes::project( array() ) );
		$this->assertNull( Stats_Block_Attributes::project( array( 'slug' => '' ) ) );
		$this->assertNull( Stats_Block_Attributes::project( array( 'slug' => array( 'hello' ) ) ) );

		$plugin = Stats_Block_Attributes::project( array( 'slug' => 'Hello Dolly' ) );
		$this->assertInstanceOf( Plugin_Project::class, $plugin );
		$this->assertSame( 'hello-dolly', $plugin->get_slug() );

		$theme = Stats_Block_Attributes::project(
			array(
				'type' => 'themes',
				'slug' => 'twentytwentyfive',
			)
		);
		$this->assertInstanceOf( Theme_Project::class, $theme );
	}


	/**
	 * Test the project name: the block name, the installed plugin or theme name, or the slug.
	 */
	public function test_project_name() {

		$plugin = new Plugin_Project( 'hello' );
		$this->assertSame( 'Hello Dolly', Stats_Block_Attributes::project_name( $plugin, array() ) );
		$this->assertSame( 'Custom name', Stats_Block_Attributes::project_name( $plugin, array( 'name' => ' Custom name ' ) ) );
		$this->assertSame( 'Hello Dolly', Stats_Block_Attributes::project_name( $plugin, array( 'name' => ' ' ) ) );
		$this->assertSame( 'not-installed', Stats_Block_Attributes::project_name( new Plugin_Project( 'not-installed' ), array() ) );

		$theme = wp_get_theme();
		$this->assertSame( $theme->get( 'Name' ), Stats_Block_Attributes::project_name( new Theme_Project( $theme->get_stylesheet() ), array() ) );
		$this->assertSame( 'not-installed', Stats_Block_Attributes::project_name( new Theme_Project( 'not-installed' ), array() ) );
	}


	/**
	 * Test the block ID is the same for the same project and language, also in different requests as in the block editor.
	 */
	public function test_block_id() {

		$plugin = new Plugin_Project( 'akismet' );
		$pt     = Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) );
		$ao90   = Stats_Block_Attributes::locale( array( 'language' => 'pt_PT_ao90' ) );

		$this->assertMatchesRegularExpression( '/^translation-stats-block-[0-9a-f]{12}$/', Stats_Block_Attributes::block_id( $plugin, $pt ) );
		$this->assertSame( Stats_Block_Attributes::block_id( $plugin, $pt ), Stats_Block_Attributes::block_id( new Plugin_Project( 'akismet' ), $pt ) );
		$this->assertNotSame( Stats_Block_Attributes::block_id( $plugin, $pt ), Stats_Block_Attributes::block_id( $plugin, $ao90 ) );
		$this->assertNotSame( Stats_Block_Attributes::block_id( $plugin, $pt ), Stats_Block_Attributes::block_id( new Theme_Project( 'akismet' ), $pt ) );
	}


	/**
	 * Test the translate.wordpress.org URL of the project in the Locale.
	 */
	public function test_locale_project_url() {

		$locale = Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) );

		$this->assertSame( 'https://translate.wordpress.org/locale/pt/default/wp-plugins/hello-dolly', Stats_Block_Attributes::locale_project_url( new Plugin_Project( 'hello-dolly' ), $locale ) );
		$this->assertSame( 'https://translate.wordpress.org/locale/pt/default/wp-themes/twentytwentyfive', Stats_Block_Attributes::locale_project_url( new Theme_Project( 'twentytwentyfive' ), $locale ) );
	}


	/**
	 * Test the block Locale, from the language or the site language.
	 */
	public function test_locale() {

		$this->assertSame( 'pt_PT', Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) )->wp_locale );
		$this->assertSame( 'pt/default', Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) )->locale_slug );
		$this->assertNull( Stats_Block_Attributes::locale( array( 'language' => 'xx_XX' ) ) );

		// Without language, the site language. English has no translations.
		$this->assertNull( Stats_Block_Attributes::locale( array() ) );

		add_filter(
			'locale',
			function () {
				return 'pt_PT';
			}
		);
		$this->assertSame( 'pt_PT', Stats_Block_Attributes::locale( array( 'language' => '' ) )->wp_locale );
		remove_all_filters( 'locale' );
	}


	/**
	 * Test the selected plugin subprojects, and the theme project.
	 */
	public function test_subprojects() {

		$plugin = new Plugin_Project( 'hello-dolly' );

		$this->assertSame( array( 'stable', 'stable-readme' ), wp_list_pluck( Stats_Block_Attributes::subprojects( $plugin, array() ), 'slug' ) );
		$this->assertSame( array( 'dev', 'stable' ), wp_list_pluck( Stats_Block_Attributes::subprojects( $plugin, array( 'subprojects' => array( 'stable', 'dev', 'unknown' ) ) ), 'slug' ) );
		$this->assertSame( array(), Stats_Block_Attributes::subprojects( $plugin, array( 'subprojects' => array() ) ) );

		// Themes have no subprojects, their stats are in the project, with the block project name.
		$theme = new Theme_Project( 'not-installed-theme' );
		$this->assertSame(
			array(
				array(
					'slug' => '',
					'name' => 'not-installed-theme',
				),
			),
			Stats_Block_Attributes::subprojects( $theme, array( 'subprojects' => array( 'stable' ) ) )
		);
		$this->assertSame( 'GeneratePress', Stats_Block_Attributes::subprojects( $theme, array( 'name' => 'GeneratePress' ) )[0]['name'] );
	}


	/**
	 * Test the block renders nothing without project, language or subprojects.
	 */
	public function test_render_without_project_language_or_subprojects() {

		$this->assertSame( '', Stats_Block::render( array( 'language' => 'pt_PT' ) ) );
		$this->assertSame( '', Stats_Block::render( array( 'slug' => 'hello-dolly' ) ) );
		$this->assertSame(
			'',
			Stats_Block::render(
				array(
					'slug'        => 'hello-dolly',
					'language'    => 'pt_PT',
					'subprojects' => array(),
				)
			)
		);
	}


	/**
	 * Test the block renders the cached stats, without requests to translate.wordpress.org.
	 */
	public function test_render_cached_stats() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );
		$this->cache_plugin_stats(
			'hello-dolly',
			array(
				'stable'        => $this->stats( 95 ),
				'stable-readme' => '',
			)
		);

		$html = Stats_Block::render(
			array(
				'slug'     => 'hello-dolly',
				'language' => 'pt_PT',
			)
		);

		$this->assertStringContainsString( 'wp-block-translation-stats-stats', $html );
		$this->assertStringContainsString( 'data-slug="hello-dolly"', $html );
		$this->assertStringContainsString( '<strong>hello-dolly</strong>', $html );
		$this->assertStringContainsString( 'Translation for <a href="https://translate.wordpress.org/locale/pt/default/wp-plugins/hello-dolly" target="_blank">Português</a>', $html );
		$this->assertStringContainsString( '95%', $html );
		$this->assertStringContainsString( '(95/100)', $html );
		$this->assertStringContainsString( 'https://translate.wordpress.org/projects/wp-plugins/hello-dolly/stable/pt/default', $html );
		$this->assertMatchesRegularExpression( '/div\[data-tstats-block=translation-stats-block-[0-9a-f]{12}\]\[data-slug="hello-dolly"\] div\.subproject\.stable \{\s*width: 95%;/', $html );

		// The readme without stats is not found.
		$this->assertStringContainsString( 'Stable Readme: <strong>Not found</strong>', $html );
		$this->assertStringNotContainsString( 'translation-stats-placeholder', $html );
		$this->assertStringNotContainsString( 'tstats-loading', $html );
		$this->assertStringNotContainsString( 'data-tstats-refresh', $html );
		$this->assertStringNotContainsString( 'tstats-hide-counts', $html );

		// Only the cached stats, without requests to translate.wordpress.org nor background loading.
		$this->assertSame( array(), $this->requested_urls );
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test the block without the language title and the strings count.
	 */
	public function test_render_without_title_and_counts() {

		$this->cache_plugin_stats(
			'hello-dolly',
			array(
				'stable'        => $this->stats( 50 ),
				'stable-readme' => $this->stats( 10 ),
			)
		);

		$html = Stats_Block::render(
			array(
				'slug'       => 'hello-dolly',
				'language'   => 'pt_PT',
				'showName'   => false,
				'showTitle'  => false,
				'showCounts' => false,
			)
		);

		$this->assertStringNotContainsString( 'translation-stats-name', $html );
		$this->assertStringNotContainsString( 'Translation for', $html );
		$this->assertStringContainsString( 'tstats-hide-counts', $html );
		$this->assertStringContainsString( '50%', $html );
	}


	/**
	 * Test the block renders the placeholder without cached stats, and loads them in the background once.
	 */
	public function test_render_not_cached_loads_in_background() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$attributes = array(
			'slug'     => 'hello-dolly',
			'language' => 'pt_PT',
		);

		$html = Stats_Block::render( $attributes );

		$this->assertStringContainsString( 'translation-stats-placeholder', $html );

		// The REST API URL of the cached stats, to show them when loaded in the background.
		$this->assertSame( 1, preg_match( '/data-tstats-refresh="([^"]+)"/', $html, $matches ) );
		$url = html_entity_decode( $matches[1] );
		$this->assertStringContainsString( 'translation-stats/v1/block-stats', rawurldecode( $url ) );
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$this->assertSame( 'plugins', $query['type'] );
		$this->assertSame( 'hello-dolly', $query['slug'] );
		$this->assertSame( 'pt_PT', $query['language'] );
		$this->assertSame( 'stable,stable-readme', $query['subprojects'] );
		$this->assertSame( Stats_Block_Attributes::block_id( new Plugin_Project( 'hello-dolly' ), Stats_Block_Attributes::locale( $attributes ) ), $query['id'] );
		$this->assertStringContainsString( 'Stable Readme', $html );
		$this->assertSame( array(), $this->requested_urls );

		$args = array( 'plugins', 'hello-dolly', 'pt_PT' );
		$this->assertIsInt( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, $args ) );

		// The next in the queue, without other project loading, with the moving stripes as the admin widgets.
		$project = new Plugin_Project( 'hello-dolly' );
		$locale  = Stats_Block_Attributes::locale( $attributes );
		$this->assertSame( 'loading', Stats_Block_Cache::loading_status( $project, $locale ) );
		$this->assertStringContainsString( 'translation-stats-widget tstats-loading', $html );

		// Queued, after the first in the queue.
		$second = Stats_Block::render(
			array(
				'slug'     => 'akismet',
				'language' => 'pt_PT',
			)
		);
		$this->assertStringContainsString( 'translation-stats-placeholder', $second );
		$this->assertStringNotContainsString( 'tstats-loading', $second );
		$this->assertSame( 'queued', Stats_Block_Cache::loading_status( new Plugin_Project( 'akismet' ), $locale ) );

		// Queued, while other project is loading.
		set_transient( Stats_Block_Cache::LOADING_TRANSIENT, array( 'plugins', 'akismet', 'pt_PT' ) );
		$this->assertSame( 'queued', Stats_Block_Cache::loading_status( $project, $locale ) );
		$this->assertSame( 'loading', Stats_Block_Cache::loading_status( new Plugin_Project( 'akismet' ), $locale ) );
		delete_transient( Stats_Block_Cache::LOADING_TRANSIENT );

		// Scheduled once.
		Stats_Block::render( $attributes );
		$events = 0;
		foreach ( _get_cron_array() as $hooks ) {
			$events += count( $hooks[ Stats_Block_Cache::REFRESH_HOOK ] ?? array() );
		}
		$this->assertSame( 2, $events );

		// Not queued while loading.
		wp_unschedule_hook( Stats_Block_Cache::REFRESH_HOOK );
		set_transient( Stats_Block_Cache::LOADING_TRANSIENT, array( 'plugins', 'hello-dolly', 'pt_PT' ) );
		Stats_Block::render( $attributes );
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, $args ) );
		delete_transient( Stats_Block_Cache::LOADING_TRANSIENT );
	}


	/**
	 * Test the block loads the missing stats right away, if filtered.
	 */
	public function test_render_loads_stats_right_away_if_filtered() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );
		add_filter( 'translation_stats_block_load_stats', '__return_true' );

		$html = Stats_Block::render(
			array(
				'slug'     => 'hello-dolly',
				'language' => 'pt_PT',
			)
		);

		$this->assertStringContainsString( '95%', $html );
		$this->assertStringNotContainsString( 'translation-stats-placeholder', $html );
		$this->assertCount( 2, $this->requested_urls );
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test the block editor preview shows the placeholder and loads the stats in the background, as the frontend,
	 * instead of loading the stats of each block in its preview request.
	 *
	 * In a separate process, to define REST_REQUEST as in the block editor preview requests.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_editor_preview_loads_stats_in_background() {

		if ( ! is_wp_version_compatible( Stats_Block::MIN_WP_VERSION ) ) {
			$this->markTestSkipped( 'The block requires WordPress ' . Stats_Block::MIN_WP_VERSION . '.' );
		}

		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$this->mock_translate_api( 200, $this->get_subproject_body() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/block-renderer/' . Stats_Block::NAME );
		$request->set_query_params(
			array(
				'context'    => 'edit',
				'attributes' => array(
					'slug'     => 'hello-dolly',
					'language' => 'pt_PT',
				),
			)
		);
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertStringContainsString( 'translation-stats-placeholder', $data['rendered'] );
		$this->assertStringContainsString( 'data-tstats-refresh=', $data['rendered'] );
		$this->assertCount( 0, $this->requested_urls );
		$this->assertNotFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test the block renders the placeholder if translate.wordpress.org is unavailable.
	 */
	public function test_render_loads_stats_unavailable() {

		$this->mock_translate_api( 503 );
		add_filter( 'translation_stats_block_load_stats', '__return_true' );

		$html = Stats_Block::render(
			array(
				'slug'     => 'hello-dolly',
				'language' => 'pt_PT',
			)
		);

		$this->assertStringContainsString( 'translation-stats-placeholder', $html );
	}


	/**
	 * Get the block cached stats from the REST API.
	 *
	 * @param array<string, string> $params   Request parameters.
	 *
	 * @return WP_REST_Response   Response.
	 */
	private function request_block_stats( $params ) {

		$request = new WP_REST_Request( 'GET', '/translation-stats/v1/block-stats' );
		$request->set_query_params( $params );

		return rest_get_server()->dispatch( $request );
	}


	/**
	 * Test the REST API route returns the cached stats, publicly, without requests to translate.wordpress.org.
	 */
	public function test_rest_cached_stats() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$params = array(
			'type'        => 'plugins',
			'slug'        => 'hello-dolly',
			'language'    => 'pt_PT',
			'subprojects' => 'stable,stable-readme',
			'id'          => 'translation-stats-block-7',
		);

		// Not cached yet, without loading nor scheduling it.
		$response = $this->request_block_stats( $params );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			array(
				'html'   => null,
				'status' => 'queued',
			),
			$response->get_data()
		);

		// Loading in the background.
		set_transient( Stats_Block_Cache::LOADING_TRANSIENT, array( 'plugins', 'hello-dolly', 'pt_PT' ) );
		$this->assertSame( 'loading', $this->request_block_stats( $params )->get_data()['status'] );
		delete_transient( Stats_Block_Cache::LOADING_TRANSIENT );
		$this->assertSame( array(), $this->requested_urls );
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );

		$this->cache_plugin_stats(
			'hello-dolly',
			array(
				'stable'        => $this->stats( 95 ),
				'stable-readme' => $this->stats( 50 ),
			)
		);

		$data = $this->request_block_stats( $params )->get_data();
		$this->assertStringContainsString( 'translation-stats-content-stats', $data['html'] );
		$this->assertStringContainsString( 'div[data-tstats-block=translation-stats-block-7][data-slug="hello-dolly"] div.subproject.stable-readme', $data['html'] );
		$this->assertStringContainsString( '50%', $data['html'] );
		$this->assertSame( 'cached', $data['status'] );
		$this->assertSame( array(), $this->requested_urls );

		// Unknown language, or without subprojects.
		$this->assertNull( $this->request_block_stats( array_merge( $params, array( 'language' => 'xx_XX' ) ) )->get_data()['html'] );
		$this->assertNull( $this->request_block_stats( array_merge( $params, array( 'subprojects' => '' ) ) )->get_data()['html'] );

		// Invalid block ID.
		$this->assertSame( 400, $this->request_block_stats( array_merge( $params, array( 'id' => '"><script>' ) ) )->get_status() );
	}


	/**
	 * Search the WordPress.org projects with the REST API, with a mocked WordPress.org API response.
	 *
	 * @param array<string, string>       $params     Request parameters.
	 * @param int                         $code       WordPress.org API response code.
	 * @param array<string, mixed>|string $body WordPress.org API response body.
	 *
	 * @return WP_REST_Response   Response.
	 */
	private function search_projects( $params, $code = 200, $body = array() ) {

		$this->mock_translate_api( $code, $body );

		$request = new WP_REST_Request( 'GET', '/translation-stats/v1/block-projects' );
		$request->set_query_params( $params );

		return rest_get_server()->dispatch( $request );
	}


	/**
	 * Test the WordPress.org projects search, for the users who can edit posts.
	 */
	public function test_rest_search_projects() {

		$params = array(
			'type'   => 'plugins',
			'search' => 'woo',
		);
		$body   = array(
			'info'    => array( 'results' => 2 ),
			'plugins' => array(
				array(
					'slug' => 'woocommerce',
					'name' => 'WooCommerce',
				),
				array(
					'slug' => 'essential-addons',
					'name' => 'Essential Addons &#8211; Templates &amp; Widgets',
				),
				array( 'slug' => 'without-name' ),
			),
		);

		// Not public.
		$this->assertSame( 401, $this->search_projects( $params, 200, $body )->get_status() );
		$this->assertSame( array(), $this->requested_urls );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertSame( 403, $this->search_projects( $params, 200, $body )->get_status() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
		$response = $this->search_projects( $params, 200, $body );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			array(
				array(
					'value' => 'woocommerce',
					'label' => 'WooCommerce',
				),
				array(
					'value' => 'essential-addons',
					'label' => 'Essential Addons – Templates & Widgets',
				),
			),
			$response->get_data()
		);
		$this->assertStringContainsString( 'api.wordpress.org/plugins/info/', $this->requested_urls[0] );
		$this->assertStringContainsString( 'request[search]=woo', rawurldecode( $this->requested_urls[0] ) );

		// Themes.
		$response = $this->search_projects(
			array(
				'type'   => 'themes',
				'search' => 'twenty',
			),
			200,
			array(
				'info'   => array( 'results' => 1 ),
				'themes' => array(
					array(
						'slug' => 'twentytwentyfive',
						'name' => 'Twenty Twenty-Five',
					),
				),
			)
		);
		$this->assertSame(
			array(
				array(
					'value' => 'twentytwentyfive',
					'label' => 'Twenty Twenty-Five',
				),
			),
			$response->get_data()
		);
		$this->assertStringContainsString( 'api.wordpress.org/themes/info/', $this->requested_urls[0] );

		// WordPress.org unavailable, without a JSON response.
		$response = $this->search_projects( $params, 503, '' );
		$this->assertSame( 503, $response->get_status() );
		$this->assertSame( 'translation_stats_search_unavailable', $response->get_data()['code'] );

		// Too short search, without requests.
		$response = $this->search_projects( array_merge( $params, array( 'search' => 'w' ) ), 200, $body );
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( array(), $this->requested_urls );
	}


	/**
	 * Test updating the block stats from translate.wordpress.org, for the users who can edit posts.
	 */
	public function test_rest_update_stats() {

		$this->cache_plugin_stats(
			'hello-dolly',
			array(
				'stable'        => $this->stats( 10 ),
				'stable-readme' => $this->stats( 10 ),
			)
		);

		$update = function ( $params ) {
			$request = new WP_REST_Request( 'POST', '/translation-stats/v1/block-stats' );
			$request->set_body_params( $params );
			return rest_get_server()->dispatch( $request );
		};

		$params = array(
			'type'        => 'plugins',
			'slug'        => 'hello-dolly',
			'language'    => 'pt_PT',
			'subprojects' => 'stable,stable-readme',
		);

		// Not public.
		$this->mock_translate_api( 200, $this->get_subproject_body() );
		$this->assertSame( 401, $update( $params )->get_status() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertSame( 403, $update( $params )->get_status() );
		$this->assertSame( array(), $this->requested_urls );

		// Updated, also with cached stats.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
		$response = $update( $params );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( 'status' => 'updated' ), $response->get_data() );
		$this->assertCount( 2, $this->requested_urls );
		$project = new Plugin_Project( 'hello-dolly' );
		$locale  = Stats_Block_Attributes::locale( $params );
		$this->assertSame( 95, get_transient( $project->subproject_stats_transient( $locale, 'stable' ) )->percent_translated );

		// translate.wordpress.org unavailable.
		$this->mock_translate_api( 503 );
		$response = $update( $params );
		$this->assertSame( 503, $response->get_status() );
		$this->assertSame( 'translation_stats_api_unavailable', $response->get_data()['code'] );

		// Unknown language.
		$response = $update( array_merge( $params, array( 'language' => 'xx_XX' ) ) );
		$this->assertSame( 404, $response->get_status() );
		$this->assertSame( 'translation_stats_block_not_found', $response->get_data()['code'] );
	}


	/**
	 * Test the block editor preview check, outside REST API requests.
	 */
	public function test_is_editor_preview() {

		$this->assertFalse( Stats_Block::is_editor_preview() );
	}


	/**
	 * Test the background loading of all the project subprojects stats.
	 */
	public function test_refresh() {

		$this->mock_translate_api( 200, $this->get_subproject_body() );

		$project = new Plugin_Project( 'hello-dolly' );
		$locale  = Stats_Block_Attributes::locale( array( 'language' => 'pt_PT' ) );

		// Loading while getting the stats.
		$statuses = array();
		add_filter(
			'pre_http_request',
			function ( $preempt ) use ( $project, $locale, &$statuses ) {
				$statuses[] = Stats_Block_Cache::loading_status( $project, $locale );
				return $preempt;
			},
			5
		);

		( new Stats_Block_Cache() )->refresh( 'plugins', 'hello-dolly', 'pt_PT' );

		// All the plugin subprojects.
		$this->assertCount( 4, $this->requested_urls );
		$this->assertSame( array( 'loading', 'loading', 'loading', 'loading' ), $statuses );
		$this->assertSame( 'queued', Stats_Block_Cache::loading_status( $project, $locale ) );

		$this->assertSame( 95, get_transient( $project->subproject_stats_transient( $locale, 'stable' ) )->percent_translated );

		// Unknown language, without requests.
		$this->mock_translate_api( 200, $this->get_subproject_body() );
		( new Stats_Block_Cache() )->refresh( 'plugins', 'hello-dolly', 'xx_XX' );
		$this->assertSame( array(), $this->requested_urls );
	}



	/**
	 * Test saving a post schedules the loading of the blocks stats not cached.
	 */
	public function test_save_post() {

		$this->cache_plugin_stats(
			'cached-plugin',
			array(
				'stable'        => $this->stats( 50 ),
				'stable-readme' => $this->stats( 50 ),
			)
		);

		$post_id = self::factory()->post->create(
			array(
				'post_content' => '<!-- wp:group --><div class="wp-block-group">' .
					'<!-- wp:translation-stats/stats {"slug":"hello-dolly","language":"pt_PT"} /-->' .
					'<!-- wp:translation-stats/stats {"slug":"cached-plugin","language":"pt_PT"} /-->' .
					'<!-- wp:translation-stats/stats {"slug":"no-language"} /-->' .
					'<!-- wp:translation-stats/stats /-->' .
					'</div><!-- /wp:group -->',
			)
		);

		$this->assertIsInt( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'cached-plugin', 'pt_PT' ) ) );

		// Posts without the block.
		wp_unschedule_hook( Stats_Block_Cache::REFRESH_HOOK );
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => '<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph -->',
			)
		);
		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test saving a revision doesn't schedule the stats loading.
	 */
	public function test_save_post_revision() {

		$post_id = self::factory()->post->create();

		$revision = wp_save_post_revision( $post_id );
		wp_unschedule_hook( Stats_Block_Cache::REFRESH_HOOK );

		( new Stats_Block_Cache() )->save_post(
			(int) $revision,
			new WP_Post(
				(object) array(
					'ID'           => $revision,
					'post_type'    => 'revision',
					'post_content' => '<!-- wp:translation-stats/stats {"slug":"hello-dolly","language":"pt_PT"} /-->',
				)
			)
		);

		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test the deactivation removes the scheduled stats loading.
	 */
	public function test_deactivate() {

		wp_schedule_single_event( time(), Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) );

		Stats_Block_Cache::deactivate();

		$this->assertFalse( wp_next_scheduled( Stats_Block_Cache::REFRESH_HOOK, array( 'plugins', 'hello-dolly', 'pt_PT' ) ) );
	}


	/**
	 * Test the block editor data: the languages, the site language, and the installed plugins and themes.
	 */
	public function test_editor_data() {

		$data = Stats_Block::editor_data();

		$this->assertContains(
			array(
				'value' => 'pt_PT',
				'label' => 'Português (pt_PT)',
				'name'  => 'Português',
				'slug'  => 'pt/default',
			),
			$data['languages']
		);
		$this->assertSame( get_locale(), $data['siteLanguage'] );
		$this->assertNotContains( 'en_US', wp_list_pluck( $data['languages'], 'value' ) );
		$this->assertContains( 'hello', wp_list_pluck( $data['plugins'], 'value' ) );
		$this->assertContains( 'Hello Dolly', wp_list_pluck( $data['plugins'], 'label' ) );
		$this->assertNotEmpty( $data['themes'] );
	}


	/**
	 * Test the block registration, the editor data and the render file.
	 */
	public function test_register() {

		if ( ! is_wp_version_compatible( Stats_Block::MIN_WP_VERSION ) ) {
			( new Stats_Block() )->register();
			( new Stats_Block() )->add_editor_data();
			$this->assertFalse( WP_Block_Type_Registry::get_instance()->is_registered( Stats_Block::NAME ) );
			return;
		}

		$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( Stats_Block::NAME ) );

		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( Stats_Block::NAME );
		$this->assertSame( 'plugins', $block_type->attributes['type']['default'] );

		// The editor data is added only in the block editor.
		foreach ( $block_type->editor_script_handles as $handle ) {
			$this->assertEmpty( wp_scripts()->get_data( $handle, 'before' ) );
		}
		( new Stats_Block() )->add_editor_data();
		foreach ( $block_type->editor_script_handles as $handle ) {
			$this->assertStringContainsString( 'window.tstatsBlock = ', implode( '', (array) wp_scripts()->get_data( $handle, 'before' ) ) );
		}

		// The block renders with the render file.
		$this->cache_plugin_stats(
			'hello-dolly',
			array(
				'stable'        => $this->stats( 95 ),
				'stable-readme' => $this->stats( 95 ),
			)
		);
		$html = render_block(
			array(
				'blockName'    => Stats_Block::NAME,
				'attrs'        => array(
					'slug'     => 'hello-dolly',
					'language' => 'pt_PT',
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$this->assertStringContainsString( '95%', $html );
	}
}
