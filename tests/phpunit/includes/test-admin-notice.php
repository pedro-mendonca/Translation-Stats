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
	public function provide_test_notice_html() {
		return array(
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => false,
				'extra_html'         => '',
				'expected_result'    => '<div class="notice"></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice"><p></p></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => true,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => false,
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-alt"></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => true,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice inline"><p></p></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => true,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice is-dismissible"><p></p></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(
					'class-1',
					'class-2',
				),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice class-1 class-2"><p></p></div>',
			),
			array(
				'type'               => 'info',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-info"><p></p></div>',
			),
			array(
				'type'               => 'info',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => true,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-info"><p></p></div>',
			),
			array(
				'type'               => 'warning',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-warning"><p></p></div>',
			),
			array(
				'type'               => 'warning',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => true,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-warning update-message"><p></p></div>',
			),
			array(
				'type'               => 'success',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-success"><p></p></div>',
			),
			array(
				'type'               => 'success',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => true,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-success updated-message"><p></p></div>',
			),
			array(
				'type'               => 'error',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-error"><p></p></div>',
			),
			array(
				'type'               => 'error',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => true,
				'message'            => '',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice notice-error update-message"><p></p></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => 'Hello World!',
				'wrap'               => 'p',
				'extra_html'         => '',
				'expected_result'    => '<div class="notice"><p>Hello World!</p></div>',
			),
			array(
				'type'               => '',
				'notice_alt'         => false,
				'inline'             => false,
				'dismissible'        => false,
				'additional_classes' => array(),
				'update_icon'        => false,
				'message'            => 'Hello World!',
				'wrap'               => 'p',
				'extra_html'         => '<div>Done!</div>',
				'expected_result'    => '<div class="notice"><p>Hello World!</p></div><div>Done!</div>',
			),
		);
	}

	/**
	 * Test wrap sanitization.
	 *
	 * @dataProvider provide_test_notice_html
	 */
	public function test_notice_html( $type, $notice_alt, $inline, $dismissible, $additional_classes, $update_icon, $message, $wrap, $extra_html, $expected_result ) {

		// Default properties.
		$args = array(
			'type'               => $type,
			'notice-alt'         => $notice_alt,
			'inline'             => $inline,
			'dismissible'        => $dismissible,
			'additional-classes' => $additional_classes,
			'update-icon'        => $update_icon,
			'message'            => $message,
			'wrap'               => $wrap,
			'extra-html'         => $extra_html,
		);

		$admin_notice = new Admin_Notice( $args );

		$markup = $admin_notice->notice_html();

		$this->assertSame( $markup, $expected_result );

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

	/**
	 * Test admin notice render.
	 */
	public function test_render() {

		$this->expectOutputString( '<div class="notice inline"><p></p></div><div class="notice inline"><p></p></div>' );

		$args = array(
			'force_show' => true,
		);

		$admin_notice = new Admin_Notice( $args );

		$admin_notice->render();

	}
}
