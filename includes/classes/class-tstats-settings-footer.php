<?php
/**
 * Class file for registering Translation Stats settings footer.
 *
 * @package Translation Stats
 *
 * @since 0.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Footer' ) ) {

	/**
	 * Class TStats_Settings_Footer.
	 */
	class TStats_Settings_Footer {


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

			// Replace admin footer text with invitation to rate the plugin on WordPress.org.
			// Uncomment the following to add admin footer text: add_filter( 'admin_footer_text',  array( $this, 'tstats_admin_footer_text' ), 1, 2 );.
			// Replace admin footer WordPress version with plugin version.
			// Uncomment the following to add admin footer version: add_filter( 'update_footer',  array( $this, 'tstats_admin_footer_version' ), 11 );.
		}


		/**
		 * Replace admin footer text with invitation to rate the plugin on WordPress.org.
		 *
		 * @since 0.9.0
		 *
		 * @param string $text   Footer text.
		 * @return string $text  Return translation stats footer text.
		 */
		public function tstats_admin_footer_text( $text ) {

			global $current_screen;

			if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'translation-stats' ) !== false ) {

				$external_link_url = 'https://translationstats.com';

				$text = sprintf(
					/* translators: %1$s Translation Stats plugin name. %2$s Plugin version. */
					__(
						'Thank you for translating with %1$s version %2$s.',
						'translation-stats'
					),
					'<a href="' . esc_url( $this->tstats_globals->tstats_link( $external_link_url, rawurlencode( home_url() ), 'link', 'tstats_plugin_link' ) ) . '">' . /* translators: Plugin name, do not translate! */ __( 'Translation Stats', 'translation-stats' ) . '</a>',
					TSTATS_VERSION
				);
				$text .= ' ' . sprintf(
					/* translators: %s Author name. */
					esc_html__(
						'By %s',
						'translation-stats'
					),
					'<a href="' . esc_url( $this->tstats_globals->tstats_link( $external_link_url, rawurlencode( home_url() ), 'link', 'tstats_plugin_footer_link' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Pedro Mendonça', 'translation-stats' ) . '</a>'
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

new TStats_Settings_Footer();
