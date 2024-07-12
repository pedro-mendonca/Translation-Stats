<?php
/**
 * Class file for the Translation Stats admin notices.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 * @since 1.2.0   Renamed from Notices to Admin_Notice.
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
		 * WordPress core notice types: 'error', 'warning', 'warning-spin', 'success' or 'info'. Defaults to none.
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
		public $css_class = array();

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

			// Prepare all the admin notice fields.
			$this->prepare_notice( $args );

			// Check if 'show_warnings' is true.
			$wp_option = get_option( TRANSLATION_STATS_WP_OPTION );
			if ( empty( $wp_option['settings']['show_warnings'] ) && empty( $args['force_show'] ) ) {
				return;
			}

			// Render the Admin Notice.
			$this->render_notice();
		}


		/**
		 * Prepare all the admin notice fields.
		 *
		 * @since 1.3.2
		 *
		 * @param array $args   Array of message data.
		 */
		public function prepare_notice( $args ) {

			// Set Type.
			$this->type = $this->sanitize_type( isset( $args['type'] ) ?? '' );

			// TODO: Sanitize fields.
			$this->notice_alt  = isset( $args['notice-alt'] ) && $args['notice-alt'] ? ' notice-alt' : '';
			$this->inline      = isset( $args['inline'] ) && ! $args['inline'] ? '' : ' inline';
			$this->dismissible = isset( $args['dismissible'] ) && $args['dismissible'] ? ' is-dismissible' : '';
			$this->css_class   = isset( $args['css-class'] ) ? ' ' . $args['css-class'] : '';
			if ( isset( $args['update-icon'] ) && $args['update-icon'] ) {
				switch ( $this->type ) {
					case 'error':
						$this->update_icon = ' update-message'; // Error icon.
						break;
					case 'warning':
						$this->update_icon = ' update-message'; // Update icon.
						break;
					case 'warning-spin':
						$this->update_icon = ' updating-message'; // Spins the update icon.
						$this->type        = ' notice-warning';   // Set the notice type to the default parent 'warning' class.
						break;
					case 'success':
						$this->update_icon = ' updated-message'; // Updated icon (check mark).
						break;
					case 'info':
						$this->update_icon = ''; // No icon.
						break;
					default:
						$this->update_icon = ''; // Defaults to none.
						break;
				}
			}
			$this->message    = isset( $args['message'] ) ? $args['message'] : '';
			$this->wrap       = isset( $args['wrap'] ) && self::is_supported( $args['wrap'] ) ? $args['wrap'] : 'p';
			$this->extra_html = isset( $args['extra-html'] ) ? $args['extra-html'] : '';
		}


		/**
		 * Sanitize the Admin Notice type.
		 * WordPress core notice types: 'error', 'warning', 'warning-spin', 'success' or 'info'. Defaults to none.
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
				'warning-spin',
				'success',
				'info',
			);

			// Check if field type exist in the supported types array.
			if ( in_array( $type, $types, true ) ) {
				return 'notice-' . $type;
			}

			return '';
		}


		/**
		 * Display formatted admin notice.
		 *
		 * WordPress core notice types ( 'error', 'warning', 'warning-spin', 'success' and 'info' ).
		 * The child type 'warning-spin' is the spinning variation of main 'warning' icon (if 'update-icon' is set to 'true'). The css class will be kept the parent 'warning'.
		 * Use 'force_show' => true to ignore the 'show_warnings' setting.
		 *
		 * @since 1.3.1
		 *
		 * @return void
		 */
		public function render_notice() {

			// TODO: return the admin notice.

			?>
			<div class="notice<?php echo esc_attr( $this->type ) . esc_attr( $this->notice_alt ) . esc_attr( $this->inline ) . esc_attr( $this->update_icon ) . esc_attr( $this->css_class ) . esc_attr( $this->dismissible ); ?>">
				<?php

				$opening_tag = $this->wrap ? '<' . esc_html( $this->wrap ) . '>' : '';
				$closing_tag = $this->wrap ? '</' . esc_html( $this->wrap ) . '>' : '';

				echo wp_kses_post( $opening_tag . $this->message . $closing_tag );

				// Extra HTML.
				echo wp_kses( $this->extra_html, Utils::allowed_html() );
				?>
			</div>
			<?php
		}


		/**
		 * Check if HTML wrap tag type is supported.
		 *
		 * @since 1.2.0
		 *
		 * @param string $type   Type of HTML tag to check if is supported.
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
