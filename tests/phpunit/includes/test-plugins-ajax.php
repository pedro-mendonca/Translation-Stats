<?php
/**
 * Class Plugins Ajax Test.
 *
 * @package Translation_Stats
 */

/**
 * Plugins Ajax test case.
 */
class Test_Plugins_Ajax extends WP_Ajax_UnitTestCase {

	use Translation_Stats_Test_Helpers;


	/**
	 * The plugin widget content Ajax action.
	 *
	 * @var string
	 */
	const ACTION = 'translation_stats_plugin_widget_content_load';


	/**
	 * Set up the settings and the API mock for each test.
	 */
	public function set_up() {
		parent::set_up();

		$this->set_settings(
			array( 'translation_language' => 'pt_PT' ),
			array(
				'translation-stats' => array(
					'enabled' => 'true',
					'stable'  => 'true',
				),
			)
		);

		$this->mock_translate_api(
			200,
			array(
				'translation_sets' => array(
					array(
						'locale'             => 'pt',
						'slug'               => 'default',
						'percent_translated' => 95,
						'current_count'      => 95,
						'waiting_count'      => 0,
						'fuzzy_count'        => 0,
						'untranslated_count' => 5,
					),
				),
			)
		);
	}


	/**
	 * Clean up the request data after each test.
	 */
	public function tear_down() {
		unset( $_POST['tstatsPlugin'], $_POST['forceUpdate'], $_POST['_ajax_nonce'] );
		parent::tear_down();
	}


	/**
	 * Log in a user with a role, as super admin on multisite for administrators.
	 *
	 * @param string $role   User role.
	 */
	private function login( $role ) {

		$user_id = self::factory()->user->create( array( 'role' => $role ) );

		if ( is_multisite() && 'administrator' === $role ) {
			grant_super_admin( $user_id );
		}

		wp_set_current_user( $user_id );
	}


	/**
	 * Set the request data for the plugin widget content.
	 *
	 * @param string|null $nonce   The request nonce, or null to not send it.
	 */
	private function set_request( $nonce ) {

		$_POST['tstatsPlugin'] = 'translation-stats';
		$_POST['forceUpdate']  = 'true';

		if ( null !== $nonce ) {
			$_POST['_ajax_nonce'] = $nonce;
		}
	}


	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_request_denied() {
		return array(
			'no nonce'                    => array(
				'role'  => 'administrator',
				'nonce' => null,
			),
			'invalid nonce'               => array(
				'role'  => 'administrator',
				'nonce' => 'invalid',
			),
			'user without plugins access' => array(
				'role'  => 'subscriber',
				'nonce' => 'valid',
			),
		);
	}


	/**
	 * Test the request is denied without a valid nonce or permission, before requesting the API.
	 *
	 * @dataProvider provide_test_request_denied
	 *
	 * @param string      $role    User role.
	 * @param string|null $nonce   The request nonce, 'valid' to create a valid nonce, or null to not send it.
	 */
	public function test_request_denied( $role, $nonce ) {

		$this->login( $role );

		$this->set_request( 'valid' === $nonce ? wp_create_nonce( self::ACTION ) : $nonce );

		try {
			$this->_handleAjax( self::ACTION );
			$this->fail( 'Expected the Ajax request to be denied.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}

		$this->assertSame( array(), $this->requested_urls );
		$this->assertFalse( get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . 'translation-stats_stable_pt_PT' ) );
	}


	/**
	 * Test the plugin widget content is loaded with a valid nonce and permission.
	 */
	public function test_request_allowed() {

		$this->login( 'administrator' );

		$this->set_request( wp_create_nonce( self::ACTION ) );

		try {
			$this->_handleAjax( self::ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertContains( 'https://translate.wordpress.org/api/projects/wp-plugins/translation-stats/stable', $this->requested_urls );
		$this->assertStringContainsString( 'translation-stats-content-stats', $this->_last_response );
		$this->assertStringContainsString( '95%', $this->_last_response );
	}
}
