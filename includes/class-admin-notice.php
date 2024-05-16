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
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Admin_Notice' ) ) {

	/**
	 * Class Admin_Notice.
	 */
	class Admin_Notice {


		/**
		 * Display formatted admin notice.
		 *
		 * WordPress core notice types ( 'error', 'warning', 'warning-spin', 'success' and 'info' ).
		 * The child type 'warning-spin' is the spinning variation of main 'warning' icon (if 'update-icon' is set to 'true'). The css class will be kept the parent 'warning'.
		 * Use 'force_show' => true to ignore the 'show_warnings' setting.
		 *
		 * @since 0.8.0
		 * @since 0.9.5   Array with all the notice data.
		 * @since 1.1.1   Renamed from tstats_notice_message() to notice_message().
		 * @since 1.2.0   Renamed from notice_message() to message().
		 *                Added support for 'wrap' properties, defaults to 'p' tag for backwards compatibility.
		 *
		 * @param array $args   Array of message data.
		 *
		 * @return void
		 */
		public static function message( $args ) {

			// Check if 'show_warnings' is true.
			$wp_option = get_option( TRANSLATION_STATS_WP_OPTION );
			if ( empty( $wp_option['settings']['show_warnings'] ) && empty( $args['force_show'] ) ) {
				return;
			}

			// Use defaults if properties not set.
			$notice = array(
				'type'        => isset( $args['type'] ) ? ' notice-' . $args['type'] : '',                             // WordPress core notice types: 'error', 'warning', 'warning-spin', 'success' or 'info'. Defaults to none.
				'notice-alt'  => isset( $args['notice-alt'] ) && $args['notice-alt'] ? ' notice-alt' : '',             // Show message alternative color scheme with class 'notice-alt': true or false. Defaults no false.
				'inline'      => isset( $args['inline'] ) && ! $args['inline'] ? '' : ' inline',                       // Defaults to true.
				'dismissible' => isset( $args['dismissible'] ) && $args['dismissible'] ? ' is-dismissible' : '',       // Defaults to false.
				'css-class'   => isset( $args['css-class'] ) ? ' ' . $args['css-class'] : '',                          // Some extra CSS classes.
				'update-icon' => isset( $args['update-icon'] ) && $args['update-icon'] ? true : '',                    // Show update message icons. Defaults to false.
				'message'     => isset( $args['message'] ) ? $args['message'] : '',                                    // Message to show.
				'wrap'        => isset( $args['wrap'] ) && self::is_supported( $args['wrap'] ) ? $args['wrap'] : 'p',  // HTML tag to wrap the message. Defaults to 'p' (paragraph).
				'extra-html'  => isset( $args['extra-html'] ) ? $args['extra-html'] : '',                              // Some extra HTML to show.
			);

			if ( $notice['update-icon'] ) {
				switch ( $args['type'] ) {
					case 'error':
						$notice['update-icon'] = ' update-message'; // Error icon.
						break;
					case 'warning':
						$notice['update-icon'] = ' update-message'; // Update icon.
						break;
					case 'warning-spin':
						$notice['update-icon'] = ' updating-message'; // Spins the update icon.
						$notice['type']        = ' notice-warning'; // Set the notice type to the default parent 'warning' class.
						break;
					case 'success':
						$notice['update-icon'] = ' updated-message'; // Updated icon (check mark).
						break;
					case 'info':
						$notice['update-icon'] = ''; // No icon.
						break;
					default:
						$notice['update-icon'] = ''; // Defaults to none.
						break;
				}
			}

			?>
			<div class="notice<?php echo esc_attr( $notice['type'] ) . esc_attr( $notice['notice-alt'] ) . esc_attr( $notice['inline'] ) . esc_attr( $notice['update-icon'] ) . esc_attr( $notice['css-class'] ) . esc_attr( $notice['dismissible'] ); ?>">
				<?php

				$opening_tag = $notice['wrap'] ? '<' . esc_html( $notice['wrap'] ) . '>' : '';
				$closing_tag = $notice['wrap'] ? '</' . esc_html( $notice['wrap'] ) . '>' : '';

				echo wp_kses_post( $opening_tag . $notice['message'] . $closing_tag );

				// Extra HTML.
				echo wp_kses( $notice['extra-html'], Utils::allowed_html() );
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
