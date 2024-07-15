<?php
/**
 * Class file for the Translation Stats admin notices.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 * @since 1.2.0   Renamed from Notices to Admin_Notice.
 * @since 1.3.2   Property names inspired by the new 6.4 admin notices: https://github.com/WordPress/wordpress-develop/pull/4119/
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Admin_Notice' ) ) {

	/**
	 * Class Admin_Notice.
	 */
	class Admin_Notice {


		/**
		 * WordPress core notice types: 'error', 'warning', 'success' or 'info'. Defaults to none.
		 *
		 * @var string
		 */
		public $type;

		/**
		 * Show message alternative color scheme with class 'notice-alt': true or false. Defaults no false.
		 *
		 * @var bool
		 */
		public $notice_alt = false;

		/**
		 * Is inline. Defaults to true.
		 *
		 * @var bool
		 */
		public $inline = true;

		/**
		 * Is dismissible. Defaults to false.
		 *
		 * @var bool
		 */
		public $dismissible = false;

		/**
		 * Array some extra CSS classes.
		 *
		 * @var array
		 */
		public $additional_classes = array();

		/**
		 * Show update message icons. Defaults to false.
		 *
		 * @var bool
		 */
		public $update_icon = false;

		/**
		 * Message to show.
		 *
		 * @var string
		 */
		public $message;

		/**
		 * HTML tag of the message wrapper. Defaults to 'p' (<p>... </p>).
		 *
		 * @var string
		 */
		public $wrap = 'p';

		/**
		 * Some extra HTML to show.
		 *
		 * @var string
		 */
		public $extra_html;


		/**
		 * Constructor.
		 *
		 * @param array $args   Array of message data.
		 */
		public function __construct( $args ) {

			// Set all the admin notice fields.
			$this->set( $args );

			// Check if 'show_warnings' is true.
			$wp_option = get_option( TRANSLATION_STATS_WP_OPTION );
			if ( empty( $wp_option['settings']['show_warnings'] ) && empty( $args['force_show'] ) ) {
				return;
			}

			// Render the admin notice.
			$this->render();
		}


		/**
		 * Set all the admin notice fields.
		 *
		 * @since 1.3.2
		 *
		 * @param array $args   Array of message data.
		 *
		 * @return void
		 */
		public function set( $args ) {

			// Set all the fields.
			$this->type               = $this->sanitize_type( isset( $args['type'] ) ? $args['type'] : '' );
			$this->notice_alt         = isset( $args['notice-alt'] ) && $args['notice-alt'] === true ? true : false;
			$this->inline             = isset( $args['inline'] ) && $args['inline'] === false ? false : true;
			$this->dismissible        = isset( $args['dismissible'] ) && $args['dismissible'] === true ? true : false;
			$this->additional_classes = isset( $args['additional-classes'] ) && is_array( $args['additional-classes'] ) ? $args['additional-classes'] : array();
			$this->update_icon        = isset( $args['update-icon'] ) && $args['update-icon'] === true ? true : false;
			$this->message            = isset( $args['message'] ) ? $args['message'] : '';
			$this->wrap               = isset( $args['wrap'] ) && self::is_supported( $args['wrap'] ) ? $args['wrap'] : 'p';
			$this->extra_html         = isset( $args['extra-html'] ) ? $args['extra-html'] : '';
		}


		/**
		 * Sanitize the Admin Notice type.
		 *
		 * @since 1.3.2
		 *
		 * @param string $type  WordPress core notice types.
		 *
		 * @return string   Admin Notice type.
		 */
		public function sanitize_type( $type = '' ) {

			$types = array(
				'error',
				'warning',
				'success',
				'info',
			);

			// Check if field type exist in the supported types array.
			if ( in_array( $type, $types, true ) ) {
				return $type;
			}

			return '';
		}


		/**
		 * Generate the markup for an admin notice.
		 *
		 * @since 1.3.2
		 *
		 * @return string   The markup for an admin notice.
		 */
		public function notice_html() {

			// Set minimal CSS class.
			$css_classes = array( 'notice' );

			// Set type CSS class.
			if ( $this->type !== '' ) {
				array_push( $css_classes, 'notice-' . $this->type );
			}

			// Set notice-alt CSS class.
			if ( $this->notice_alt === true ) {
				array_push( $css_classes, 'notice-alt' );
			}

			// Set inline CSS class.
			if ( $this->inline === true ) {
				array_push( $css_classes, 'inline' );
			}

			// Set dismissible CSS class.
			if ( $this->dismissible === true ) {
				array_push( $css_classes, 'is-dismissible' );
			}

			if ( $this->update_icon === true ) {
				switch ( $this->type ) {
					case 'error':
						array_push( $css_classes, 'update-message' ); // Error icon.
						break;
					case 'warning':
						array_push( $css_classes, 'update-message' ); // Update icon.
						break;
					case 'success':
						array_push( $css_classes, 'updated-message' ); // Updated icon (check mark).
						break;
					case 'info':
						break; // No icon.
					default:
						break; // Defaults to none.
				}
			}

			// Set extra CSS classes.
			if ( ! empty( $this->additional_classes ) ) {
				array_push( $css_classes, implode( ' ', $this->additional_classes ) );
			}

			$css_classes = implode( ' ', $css_classes );

			return sprintf(
				'%s%s%s%s%s',
				'<div class="' . esc_attr( $css_classes ) . '">',
				$this->wrap ? '<' . esc_html( $this->wrap ) . '>' : '',
				$this->message,
				$this->wrap ? '</' . esc_html( $this->wrap ) . '>' : '',
				'</div>'
			) . wp_kses( $this->extra_html, Utils::allowed_html() );
		}


		/**
		 * Render notice HTML.
		 *
		 * @since 1.3.2
		 *
		 * @return void
		 */
		public function render() {

			echo wp_kses_post( $this->notice_html() );
		}


		/**
		 * Check if HTML wrap tag type is supported.
		 *
		 * @since 1.2.0
		 *
		 * @param string $type   Type of HTML tag to check if is supported.
		 *
		 * @return bool   True if type is supported, defaults to false.
		 */
		public static function is_supported( $type ) {

			$types = array(
				false,
				'p',
				'div',
				'span',
			);

			// Check if field type exist in the supported types array.
			if ( in_array( $type, $types, true ) ) {
				return true;
			}

			return false;
		}
	}
}
