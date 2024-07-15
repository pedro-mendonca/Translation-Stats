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
		 * WordPress core notice types: 'error', 'warning', 'success' or 'info'. Defaults to empty.
		 *
		 * @var string
		 */
		public $type;

		/**
		 * Show message alternative color scheme with class 'notice-alt': true or false. Defaults no false.
		 *
		 * @var bool
		 */
		public $notice_alt;

		/**
		 * Is inline. Defaults to true.
		 *
		 * @var bool
		 */
		public $inline;

		/**
		 * Is dismissible. Defaults to false.
		 *
		 * @var bool
		 */
		public $dismissible;

		/**
		 * Array some extra CSS classes. Defaults to empty array.
		 *
		 * @var array
		 */
		public $additional_classes;

		/**
		 * Show update message icons. Defaults to false.
		 *
		 * @var bool
		 */
		public $update_icon;

		/**
		 * Message to show. Defaults to empty.
		 *
		 * @var string
		 */
		public $message;

		/**
		 * HTML tag of the message wrapper. Defaults to 'p' (<p>... </p>).
		 *
		 * @var string
		 */
		public $wrap;

		/**
		 * Some extra HTML to show. Defaults to empty.
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

			// Default properties.
			$defaults = array(
				'type'               => '',
				'notice-alt'         => false,
				'inline'             => true,
				'dismissible'        => false,
				'additional-classes' => array(),
				'update-icon'        => false,
				'message'            => '',
				'wrap'               => '',
				'extra-html'         => '',
			);

			$args = wp_parse_args( $args, $defaults );

			// Set all the properties.
			$this->type               = $this->sanitize_type( $args['type'] );
			$this->notice_alt         = $args['notice-alt'] === true ? true : false;
			$this->inline             = $args['inline'] === false ? false : true;
			$this->dismissible        = $args['dismissible'] === true ? true : false;
			$this->additional_classes = is_array( $args['additional-classes'] ) ? $args['additional-classes'] : array();
			$this->update_icon        = $args['update-icon'] === true ? true : false;
			$this->message            = $args['message'];
			$this->wrap               = $this->sanitize_wrap( $args['wrap'] );
			$this->extra_html         = $args['extra-html'];
		}


		/**
		 * Sanitize the Admin Notice type. Defaults to empty.
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
		 * Sanitize the Admin Notice HTML wrapper tag. Defaults to 'p' (paragraph HTML tag).
		 *
		 * @since 1.3.2
		 *
		 * @param string $wrap  Notice supported HTML wrapper.
		 *
		 * @return string   Admin Notice wrapper.
		 */
		public function sanitize_wrap( $wrap = '' ) {

			$wrappers = array(
				false,
				'p',
				'div',
				'span',
			);

			// Check if field wrapper exist in the supported wrappers array.
			if ( in_array( $wrap, $wrappers, true ) ) {
				return $wrap;
			}

			return 'p';
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
	}
}
