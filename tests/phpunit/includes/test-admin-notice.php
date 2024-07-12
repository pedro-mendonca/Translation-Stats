<?php
/**
 * Class Admin_Notice Test.
 *
 * @package Translation_Stats
 */

Use Translation_Stats\Admin_Notice;


/**
 * Admin_Notice test case.
 */
class Test_Admin_Notice extends WP_UnitTestCase {

	/**
	 * Test display formatted admin notice.
	 */
	public function test_message() {

		$admin_notice = array(
			'type'       => 'error',
			'notice-alt' => true,
			'message'    => 'Translation project not found on WordPress.org',
		);
		$admin_notice = new Admin_Notice( $admin_notice );

		echo "\nAdmin Notice:\n";
		var_dump( $admin_notice );

		$this->assertIsObject( $admin_notice );

	}

}
