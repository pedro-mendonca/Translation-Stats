<?php
/**
 * Class file for the Translation Stats notices.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Notices' ) ) {

	/**
	 * Class TStats_Notices.
	 */
	class TStats_Notices {


		/**
		 * Globals.
		 *
		 * @var object
		 */
		protected $tstats_globals;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

		}


		/**
		 * Display formated admin notice.
		 *
		 * WordPress core notice types ( 'error', 'warning', 'success' and 'info' ).
		 * Use 'force_show' => true to ignore the 'show_warnings' setting.
		 *
		 * @since 0.8.0
		 * @since 0.9.5  Array with all the notice data.
		 *
		 * @param array $args  Array of message data.
		 */
		public function tstats_notice_message( $args ) {

			// Check if 'show_warnings' is true.
			$wp_option = get_option( TSTATS_WP_OPTION );
			if ( empty( $wp_option['show_warnings'] ) && empty( $args['force_show'] ) ) {
				return;
			}

			// Use defaults if properties not set.
			$notice = array(
				'type'        => isset( $args['type'] ) ? ' notice-' . $args['type'] : '',                       // WordPress core notice types: 'error', 'warning', 'success' or 'info'. Defaults to none.
				'notice-alt'  => isset( $args['notice-alt'] ) && $args['notice-alt'] ? ' notice-alt' : '',       // Show message alternative color scheme with class 'notice-alt': true or false. Defaults no false.
				'inline'      => isset( $args['inline'] ) && ! $args['inline'] ? '' : ' inline',                 // Defaults to true.
				'dismissible' => isset( $args['dismissible'] ) && $args['dismissible'] ? ' is-dismissible' : '', // Defaults to false.
				'css-class'   => isset( $args['css-class'] ) ? ' ' . $args['css-class'] : '',                    // Some extra CSS classes.
				'update-icon' => isset( $args['update-icon'] ) && $args['update-icon'] ? true : '',              // Show update message icons. Defaults to false.
				'message'     => isset( $args['message'] ) ? $args['message'] : '',                              // Message to show.
				'extra-html'  => isset( $args['extra-html'] ) ? $args['extra-html'] : '',                        // Some extra HTMLto show.
			);
			if ( $notice['update-icon'] ) {
				// Defaults to none.
				$notice['update-icon'] = '';
				switch ( $args['type'] ) {
					case 'error':
						$notice['update-icon'] = ' update-message';
						break;
					case 'warning':
						$notice['update-icon'] = ' update-message updating-message';
						break;
					case 'success':
						$notice['update-icon'] = ' update-message updated-message';
						break;
					case 'info':
						$notice['update-icon'] = '';
						break;
				}
			}
			?>

			<div class="notice<?php echo esc_attr( $notice['type'] ) . esc_attr( $notice['notice-alt'] ) . esc_attr( $notice['inline'] ) . esc_attr( $notice['update-icon'] ) . esc_attr( $notice['css-class'] ) . esc_attr( $notice['dismissible'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
				<?php
				// Extra HTML.
				echo wp_kses( $notice['extra-html'], $this->tstats_globals->tstats_allowed_html() );
				?>
			</div>

			<?php

		}

	}

}
