<?php
/**
 * Base test case for Translation Stats tests.
 *
 * @package Translation_Stats
 */


/**
 * Translation Stats test case, with helpers shared by the test classes.
 */
abstract class Translation_Stats_TestCase extends WP_UnitTestCase {


	/**
	 * URLs requested to the mocked translate.WordPress.org API.
	 *
	 * @var array
	 */
	protected $requested_urls = array();


	/**
	 * Save the Translation Stats settings, merged with the defaults needed by the plugin.
	 *
	 * @param array $settings   Settings to save in the 'settings' key.
	 * @param array $plugins    Plugins settings to save in the 'plugins' key.
	 */
	protected function set_settings( $settings = array(), $plugins = array() ) {

		update_option(
			TRANSLATION_STATS_WP_OPTION,
			array(
				'settings' => array_merge(
					array(
						'transients_expiration' => HOUR_IN_SECONDS,
						'settings_version'      => TRANSLATION_STATS_SETTINGS_VERSION,
					),
					$settings
				),
				'plugins'  => $plugins,
			)
		);
	}


	/**
	 * Mock the translate.WordPress.org API response, without making a real HTTP request.
	 *
	 * @param int|WP_Error $response   HTTP response code to return, or a WP_Error.
	 * @param mixed        $body       Response body, encoded as JSON if not a string.
	 */
	protected function mock_translate_api( $response, $body = '' ) {

		$this->requested_urls = array();

		// Settings needed to set the transients expiration.
		if ( false === get_option( TRANSLATION_STATS_WP_OPTION ) ) {
			$this->set_settings();
		}

		if ( ! is_string( $body ) ) {
			$body = wp_json_encode( $body );
		}

		remove_all_filters( 'pre_http_request' );

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $response, $body ) {

				$this->requested_urls[] = $url;

				if ( is_wp_error( $response ) ) {
					return $response;
				}

				return array(
					'headers'  => array(),
					'body'     => $body,
					'response' => array(
						'code'    => $response,
						'message' => get_status_header_desc( $response ),
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);
	}
}
