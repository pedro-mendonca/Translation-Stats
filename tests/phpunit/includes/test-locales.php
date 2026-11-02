<?php
/**
 * Class Locales Test.
 *
 * @package Translation_Stats
 */

use Translation_Stats\Locales;
use Translation_Stats\GP_Locales;
use Translation_Stats\Plugin_Project;


/**
 * Locales test case.
 */
class Test_Locales extends WP_UnitTestCase {

	/**
	 * Test developmente mode.
	 */
	public function test_instance() {

		$this->assertFalse( isset( $GLOBALS['translation_stats_locales'] ) );

		// Get wordpress.org Locales.
		Locales::locales();

		$this->assertTrue( isset( $GLOBALS['translation_stats_locales'] ) );
	}


	/**
	 * Get a Locale of the bundled WordPress.org Locales, without loading the available translations.
	 *
	 * @param string $wp_locale   WordPress Locale.
	 *
	 * @return Translation_Stats\Locale|null   Locale, or null if not found.
	 */
	private function bundled_locale( $wp_locale ) {
		foreach ( Locales::instance()->locales as $locale ) {
			if ( $locale->wp_locale === $wp_locale ) {
				return $locale;
			}
		}
		return null;
	}


	/**
	 * The Locale variants on translate.wordpress.org: WordPress Locale, translation set locale and slug, and WordPress.org subdomain.
	 *
	 * @return array<string, array{string, string, string}>
	 */
	public function provide_variants() {
		return array(
			'ca_valencia'    => array( 'ca_valencia', 'ca/valencia', 'ca' ),
			'de_DE_formal'   => array( 'de_DE_formal', 'de/formal', 'de' ),
			'de_CH_informal' => array( 'de_CH_informal', 'de-ch/informal', 'de-ch' ),
			'nl_NL_formal'   => array( 'nl_NL_formal', 'nl/formal', 'nl' ),
			'pt_PT_ao90'     => array( 'pt_PT_ao90', 'pt/ao90', 'pt' ),
		);
	}


	/**
	 * Test the Locale variants match their translation sets on translate.wordpress.org, as variants of their root Locale.
	 *
	 * @dataProvider provide_variants
	 *
	 * @param string $wp_locale         WordPress Locale.
	 * @param string $locale_slug       Translation set locale and slug.
	 * @param string $wporg_subdomain   WordPress.org subdomain of the root Locale.
	 */
	public function test_variants( $wp_locale, $locale_slug, $wporg_subdomain ) {

		$locale = $this->bundled_locale( $wp_locale );

		$this->assertNotNull( $locale, $wp_locale . ' is missing.' );
		$this->assertSame( $locale_slug, $locale->locale_slug );
		$this->assertSame( $wporg_subdomain, $locale->wporg_subdomain );
		$this->assertSame( 'https://translate.wordpress.org/projects/wp-plugins/hello-dolly/stable/' . $locale_slug, ( new Plugin_Project( 'hello-dolly' ) )->translate_url( 'stable', $locale ) );
	}


	/**
	 * Test the stats of a Locale variant are found in its translation set, not as not found.
	 */
	public function test_variant_stats() {

		update_option(
			TRANSLATION_STATS_WP_OPTION,
			array(
				'settings' => array(
					'transients_expiration' => HOUR_IN_SECONDS,
					'settings_version'      => TRANSLATION_STATS_SETTINGS_VERSION,
				),
			)
		);

		add_filter(
			'pre_http_request',
			function () {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'translation_sets' => array(
								array(
									'locale'             => 'pt',
									'slug'               => 'default',
									'percent_translated' => 100,
								),
								array(
									'locale'             => 'pt',
									'slug'               => 'ao90',
									'percent_translated' => 85,
								),
							),
						)
					),
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			}
		);

		$stats = ( new Plugin_Project( 'hello-dolly' ) )->subproject_stats( $this->bundled_locale( 'pt_PT_ao90' ), 'stable', true );

		remove_all_filters( 'pre_http_request' );

		$this->assertIsObject( $stats );
		$this->assertSame( 85, $stats->percent_translated );
	}
}
