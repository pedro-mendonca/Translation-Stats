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
		 * Display formated admin notice.
		 *
		 * WordPress core notice types ( 'error', 'warning', 'success' and 'info' ).
		 *
		 * @since 0.8.0
		 * @since 0.9.5  Array with all the notice data.
		 *
		 * @param array $args  Array of message data.
		 */
		public function tstats_notice_message( $args ) {

			// Todo: Update icons.

			$wp_option = get_option( TSTATS_WP_OPTION );
			if ( empty( $wp_option['show_warnings'] ) ) {
				return;
			}

			$default = array(
				'type'        => 'info',  // WordPress core notice types: 'error', 'warning', 'success' or 'info'.
				'notice-alt'  => false,   // Show message alternative color scheme with class 'notice-alt': true or false.
				'inline'      => false,   // Show message class 'inline': true or false.
				'dismissible' => false,   // Show message class 'is-dismissible': true or false.
				'css_class'   => '',      // Some extra CSS classes.
				'message'     => '',      // Message to show.
			);

			$type        = empty( $args['type'] ) ? $default['type'] : $args['type'];
			$notice_alt  = empty( $args['notice-alt'] ) ? '' : ' notice-alt';
			$inline      = empty( $args['inline'] ) ? '' : ' inline';
			$dismissible = empty( $args['dismissible'] ) ? '' : ' is-dismissible';
			$css_class   = empty( $args['css_class'] ) ? $default['css_class'] : ' ' . $args['css_class'];
			$message     = empty( $args['message'] ) ? $default['message'] : $args['message'];

			?>

			<div class="notice notice-<?php echo esc_attr( $type ) . esc_attr( $notice_alt ) . esc_attr( $inline ) . esc_attr( $css_class ) . esc_attr( $dismissible ); ?>">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>

			<?php

		}

	}

}
