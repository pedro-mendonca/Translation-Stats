<?php
/**
 * Class file for the Translation Stats notices.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Notices' ) ) {

	/**
	 * Class Notices.
	 */
	class Notices {


		/**
		 * Globals.
		 *
		 * @var object
		 */
		protected $globals;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->globals = new Globals();

		}


		/**
		 * Display formated admin notice.
		 *
		 * WordPress core notice types ( 'error', 'warning', 'warning-spin', 'success' and 'info' ).
		 * The child type 'warning-spin' is the spinning variation of main 'warning' icon (if 'update-icon' is set to 'true'). The css class will be kept the parent 'warning'.
		 * Use 'force_show' => true to ignore the 'show_warnings' setting.
		 *
		 * @since 0.8.0
		 * @since 0.9.5   Array with all the notice data.
		 * @since 1.1.1   Renamed from tstats_notice_message() to notice_message().
		 *
		 * @param array $args   Array of message data.
		 *
		 * @return void
		 */
		public function notice_message( $args ) {

			// Check if 'show_warnings' is true.
			$wp_option = get_option( TSTATS_WP_OPTION );
			if ( empty( $wp_option['settings']['show_warnings'] ) && empty( $args['force_show'] ) ) {
				return;
			}

			// Use defaults if properties not set.
			$notice = array(
				'type'        => isset( $args['type'] ) ? ' notice-' . $args['type'] : '',                       // WordPress core notice types: 'error', 'warning', 'warning-spin', 'success' or 'info'. Defaults to none.
				'notice-alt'  => isset( $args['notice-alt'] ) && $args['notice-alt'] ? ' notice-alt' : '',       // Show message alternative color scheme with class 'notice-alt': true or false. Defaults no false.
				'inline'      => isset( $args['inline'] ) && ! $args['inline'] ? '' : ' inline',                 // Defaults to true.
				'dismissible' => isset( $args['dismissible'] ) && $args['dismissible'] ? ' is-dismissible' : '', // Defaults to false.
				'css-class'   => isset( $args['css-class'] ) ? ' ' . $args['css-class'] : '',                    // Some extra CSS classes.
				'update-icon' => isset( $args['update-icon'] ) && $args['update-icon'] ? true : '',              // Show update message icons. Defaults to false.
				'message'     => isset( $args['message'] ) ? $args['message'] : '',                              // Message to show.
				'extra-html'  => isset( $args['extra-html'] ) ? $args['extra-html'] : '',                        // Some extra HTMLto show.
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
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
				<?php
				// Extra HTML.
				echo wp_kses( $notice['extra-html'], $this->globals->allowed_html() );
				?>
			</div>

			<?php

		}

	}

}
