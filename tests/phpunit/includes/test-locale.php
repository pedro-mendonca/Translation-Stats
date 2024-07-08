<?php
/**
 * Class Locale Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Locale;
Use Translation_Stats\GP_Locale;


/**
 * Locale test case.
 */
class Test_Locale extends WP_UnitTestCase {

	/**
	 * Test developmente mode.
	 */
	public function test_construct() {

		$pt = new GP_Locale();
		$pt->english_name = 'Portuguese (Portugal)';
		$pt->native_name = 'Português';
		$pt->lang_code_iso_639_1 = 'pt';
		$pt->country_code = 'pt';
		$pt->wp_locale = 'pt_PT';
		$pt->slug = 'pt';
		$pt->google_code = 'pt-PT';
		$pt->facebook_locale = 'pt_PT';

		$locale = new Locale( $pt );

		$this->assertNull( $locale->translations );

		$this->assertEquals(
 			$locale->locale_slug,
 			'pt/default'
 		);

		$this->assertEquals(
 			$locale->wporg_subdomain,
 			'pt'
 		);

	}

}
