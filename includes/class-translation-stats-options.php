<?php
/**
 * Class file for registering a new Translation Stats options page under Settings.
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Translation_Stats_Options' ) ) {

	/**
	 * Class Translation_Stats_Options.
	 */
	class Translation_Stats_Options {

		/**
		 * Constructor.
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'ts_admin_menu' ) );
		}


		/**
		 * Registers a new Translation Stats options page under Settings.
		 */
		function ts_admin_menu() {
			add_options_page(
				esc_html__( 'Translation Stats Options', 'translation-stats' ), // Page title.
				esc_html_x( 'Translation Stats', 'Menu title', 'translation-stats' ), // Menu title, context to separate from Plugin Name.
				'manage_options', // Slug.
				'translation-stats', // Slug.
				array( $this, 'ts_options' ) // Callback function.
			);
		}


		/**
		 * Options page display callback.
		 */
		function ts_options() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'translation-stats' ) );
			}
			?>
			<div class="wrap">
				<h1><?php echo esc_html_x( 'Translation Stats', 'Page title', 'translation-stats' ); ?></h1>
				<p></p>
			</div>
			<?php
		}

	}

}

new Translation_Stats_Options();
