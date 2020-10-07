<?php
/**
 * Class file for registering Translation Stats settings footer.
 *
 * @package Translation Stats
 *
 * @since 0.9.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Footer' ) ) {

	/**
	 * Class Settings_Footer.
	 */
	class Settings_Footer {


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

			// Replace admin footer text with customized message.
			add_filter( 'admin_footer_text', array( $this, 'tstats_admin_footer_text' ), 1, 2 );

			// Replace admin footer WordPress version with plugin version.
			// add_filter( 'update_footer', array( $this, 'tstats_admin_footer_version' ), 11 ); // phpcs: ignore.
		}


		/**
		 * Replace admin footer text with customized message.
		 *
		 * @since 0.9.0
		 *
		 * @param string $text   Footer text.
		 *
		 * @return string   Return translation stats footer text.
		 */
		public function tstats_admin_footer_text( $text ) {

			global $current_screen;

			if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'translation-stats' ) !== false ) {

				$external_link_url = 'https://translationstats.com';

				$text = sprintf(
					/* translators: 1: Translation Stats plugin name. 2: Plugin version. */
					esc_html__( 'Thank you for translating with %1$s version %2$s.', 'translation-stats' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $this->globals->campaign_link( $external_link_url, 'tstats', 'link', 'tstats_footer_link' ) ),
						/* translators: Plugin name, do not translate! */
						esc_html__( 'Translation Stats', 'translation-stats' )
					),
					TSTATS_VERSION
				);
				$text .= ' ' . sprintf(
					/* translators: %s: Author name. */
					esc_html__( 'By %s', 'translation-stats' ),
					sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
						esc_url( $this->globals->campaign_link( $external_link_url, 'tstats', 'link', 'tstats_footer_link' ) ),
						esc_html__( 'Pedro Mendonça', 'translation-stats' )
					)
				);
			}
			return $text;
		}


		/**
		 * Replace admin footer WordPress version with plugin version.
		 *
		 * @since 0.9.0
		 *
		 * @param string $text   Footer version.
		 *
		 * @return string $text  Return translation stats footer version.
		 */
		public function tstats_admin_footer_version( $text ) {
			global $current_screen;
			if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'translation-stats' ) !== false ) {
				$text = sprintf(
					/* translators: Plugin Name and version - Do not translate! */
					esc_html__( 'Translation Stats %s', 'translation-stats' ),
					'<small>v.' . esc_html( TSTATS_VERSION ) . '</small>'
				);
			}
			return $text;
		}

	}

}
