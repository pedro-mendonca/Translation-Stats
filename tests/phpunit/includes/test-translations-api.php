<?php
/**
 * Class Translations_API Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Translations_API;


/**
 * Translations_API test case.
 */
class Test_Translations_API extends WP_UnitTestCase {


	/**
	 * Test get plugin data from translate.WordPress.org API.
	 */
	public function test_translations_api_get_plugin() {

		// Existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'translation-stats' );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertEquals( $response_code, 200 );

		// Non-existent plugin slug in Translating WordPress.
		$response = Translations_API::translations_api_get_plugin( 'wrong-slug-translation-stats' );

		$response_code = wp_remote_retrieve_response_code( $response );

		$this->assertEquals( $response_code, 404 );

	}

}
