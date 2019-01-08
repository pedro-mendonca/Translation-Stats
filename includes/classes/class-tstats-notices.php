<?php
/**
 * Class file for the Translation Stats notices.
 *
 * @package Translation Stats
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
		 * Display formated notice message.
		 *
		 * Usage of notice types:
		 * notice-error – error message displayed with a red border.
		 * notice-warning – warning message displayed with a yellow border.
		 * notice-success – success message displayed with a green border.
		 * notice-info - info message displayed with a blue border.
		 *
		 * @param string $notice_message   Message to display.
		 * @param string $notice_type      WordPress core notice types ( 'error', 'warning', 'success' and 'info' ).
		 */
		public function tstats_notice_message( $notice_message, $notice_type ) {
			$wp_option = get_option( TSTATS_WP_OPTION );
			if ( ! empty( $wp_option['show_warnings'] ) ) {
				ob_start(); ?>
				<div class="notice notice-alt inline notice-<?php echo esc_attr( $notice_type ); ?>">
					<p class="aria-label"><?php echo wp_kses_post( $notice_message ); ?></p>
				</div>
				<?php
				$display_notice = ob_get_clean();
				echo wp_kses_post( $display_notice );
			}
		}

	}

}
