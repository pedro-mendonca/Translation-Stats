<?php
/**
 * Class file for registering Translation Stats Activation.
 *
 * @package Translation_Stats
 *
 * @since 1.0.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Activation' ) ) {

	/**
	 * Class Activation.
	 */
	class Activation {


		/**
		 * Notices.
		 *
		 * @var object
		 */
		protected $notices;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Register activation hook.
			register_activation_hook( 'translation-stats/translation-stats.php', array( $this, 'activate' ) );

			// Add activation admin notice.
			add_action( 'admin_notices', array( $this, 'activate_notice' ) );
		}

		/**
		 * Runs only when the plugin is activated.
		 *
		 * @since 1.0.0
		 * @since 1.1.1   Renamed from tstats_activate() to activate().
		 *
		 * @return void
		 */
		public function activate() {

			// Cache plugin activation data.
			set_transient( 'translation_stats_activate', true, 5 );
		}


		/**
		 * Admin notice on activation.
		 *
		 * @since 1.0.0
		 * @since 1.1.1   Renamed from tstats_activate_notice() to activate_notice().
		 *
		 * @return void
		 */
		public function activate_notice() {

			// Check transient, if available display notice.
			if ( get_transient( 'translation_stats_activate' ) ) {

				$activation_message = sprintf(
					'%s %s',
					__( 'Thank you for installing Translation Stats.', 'translation-stats' ),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							__( 'Go to %1$ssettings%2$s to start selecting some of your favorite plugins.', 'translation-stats' )
						),
						'<a href="' . esc_url( add_query_arg( 'page', 'translation-stats', admin_url( 'options-general.php' ) ) ) . '">',
						'</a>'
					)
				);

				$admin_notice = array(
					'type'        => 'success',
					'notice-alt'  => false,
					'inline'      => false,
					'force_show'  => true,
					'dismissible' => true,
					'message'     => $activation_message,
				);
				Admin_Notice::message( $admin_notice );

				// Delete transient, only display this notice once.
				delete_transient( 'translation_stats_activate' );
			}
		}
	}
}
