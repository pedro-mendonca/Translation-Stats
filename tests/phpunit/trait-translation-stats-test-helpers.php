<?php
/**
 * Helpers shared by the Translation Stats test cases.
 *
 * @package Translation_Stats
 */

/**
 * Translation Stats test helpers, for test cases extending different WordPress test cases.
 */
trait Translation_Stats_Test_Helpers {


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
						'translation_language'  => 'site-default',
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


	/**
	 * Get a Locale object.
	 *
	 * @param string      $wp_locale   WordPress Locale ( e.g. 'pt_PT' ).
	 * @param string      $slug        Locale slug ( e.g. 'pt' or 'pt/ao90' ).
	 * @param string|null $root_slug   Root Locale slug, for variants ( e.g. 'pt' ).
	 *
	 * @return Translation_Stats\Locale  Locale object.
	 */
	protected function get_locale( $wp_locale, $slug, $root_slug = null ) {

		$gp_locale = new Translation_Stats\GP_Locale();

		$gp_locale->wp_locale = $wp_locale;
		$gp_locale->slug      = $slug;

		if ( null !== $root_slug ) {
			$gp_locale->root_slug = $root_slug;
		}

		return new Translation_Stats\Locale( $gp_locale );
	}


	/**
	 * Get a translate.WordPress.org API project response body.
	 *
	 * @return array  Response body with translation sets.
	 */
	protected function get_subproject_body() {
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
	 * Original theme directories, before adding the test themes.
	 *
	 * @var array|null
	 */
	private $original_theme_directories;


	/**
	 * Add the test themes, from 'tests/phpunit/data/themes'.
	 */
	protected function add_test_themes() {
		global $wp_theme_directories;

		$this->original_theme_directories = $wp_theme_directories;

		register_theme_directory( __DIR__ . '/data/themes' );
		wp_clean_themes_cache();
	}


	/**
	 * Remove the test themes, restoring the theme directories.
	 */
	protected function remove_test_themes() {
		global $wp_theme_directories;

		$wp_theme_directories = $this->original_theme_directories; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_clean_themes_cache();
	}
}
