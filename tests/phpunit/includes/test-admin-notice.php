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

		$this->assertIsObject( $admin_notice );

	}

	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_sanitize_type() {
		return array(
			// Supported.
			array(
				'type'            => 'error',
				'expected_result' => 'error',
			),
			array(
				'type'            => 'warning',
				'expected_result' => 'warning',
			),
			array(
				'type'            => 'success',
				'expected_result' => 'success',
			),
			array(
				'type'            => 'info',
				'expected_result' => 'info',
			),
			// Not supported.
			array(
				'type'            => 'other',
				'expected_result' => '',
			),
			array(
				'type'            => '',
				'expected_result' => '',
			),
			array(
				'type'            => null,
				'expected_result' => '',
			),
			array(
				'type'            => false,
				'expected_result' => '',
			),
			array(
				'type'            => true,
				'expected_result' => '',
			),
			array(
				'type'            => array(),
				'expected_result' => '',
			),
		);
	}

	/**
	 * Test type sanitization.
	 *
	 * @dataProvider provide_test_sanitize_type
	 */
	public function test_sanitize_type( $type, $expected_result ) {

		$args = array(
			'type' => $type,
		);

		$admin_notice = new Admin_Notice( $args );

		$this->assertSame( $admin_notice->type, $expected_result );

	}

	/**
	 * Data provider.
	 *
	 * @var array
	 */
	public function provide_test_sanitize_wrap() {
		return array(
			// Supported.
			array(
				'wrap'            => false,
				'expected_result' => false,
			),
			array(
				'type'            => 'p',
				'expected_result' => 'p',
			),
			array(
				'type'            => 'div',
				'expected_result' => 'div',
			),
			array(
				'type'            => 'span',
				'expected_result' => 'span',
			),
			// Not supported.
			array(
				'type'            => 'other',
				'expected_result' => 'p',
			),
			array(
				'type'            => '',
				'expected_result' => 'p',
			),
			array(
				'type'            => null,
				'expected_result' => 'p',
			),
			array(
				'type'            => true,
				'expected_result' => 'p',
			),
			array(
				'type'            => array(),
				'expected_result' => 'p',
			),
		);
	}

	/**
	 * Test wrap sanitization.
	 *
	 * @dataProvider provide_test_sanitize_wrap
	 */
	public function test_sanitize_wrap( $wrap, $expected_result ) {

		$args = array(
			'wrap' => $wrap,
		);

		$admin_notice = new Admin_Notice( $args );

		$this->assertSame( $admin_notice->wrap, $expected_result );

	}
}
