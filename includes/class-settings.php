<?php
/**
 * Class file for registering Translation Stats Settings.
 *
 * @package Translation_Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings' ) ) {

	/**
	 * Class Settings.
	 */
	class Settings {


		/**
		 * Transients.
		 *
		 * @var object
		 */
		protected $transients;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Transients.
			$this->transients = new Transients();

			// Add admin menu item.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			// Add plugin settings sections.
			add_action( 'admin_init', array( $this, 'settings_sections' ) );

			// Initialize Settings Sidebar.
			new Settings_Sidebar();

			// Initialize Settings Widgets.
			new Settings_Widgets();

			// Initialize Settings Footer.
			new Settings_Footer();
		}


		/**
		 * Registers a new Translation Stats Settings Page.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Renamed from tstats_admin_menu() to admin_menu().
		 *
		 * @return void
		 */
		public function admin_menu() {
			// Add submenu page to the Settings main menu.
			add_options_page(
				esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ), // The text to be displayed in the title tag.
				esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ), // The text to be used for the menu.
				'manage_options',                                                             // The capability required to display this menu.
				TRANSLATION_STATS_SETTINGS_PAGE,                                              // The unique slug name to refer to this menu.
				array( $this, 'add_options_page' )                                            // The function to output the page content.
			);
		}


		/**
		 * Add Settings Sections.
		 *
		 * @since 0.9.0
		 * @since 0.9.9   Renamed from tstats_settings_sections() to settings_sections().
		 *
		 * @return void
		 */
		public function settings_sections() {

			// Get the settings pages tabs and sections.
			$settings_pages = self::get_settings_pages();

			// Loop through settings sections.
			foreach ( $settings_pages as $page ) {

				// Loop through settings page sections.
				foreach ( $page['sections'] as $section ) {

					// First letter uppercase to match class names.
					$section = ucfirst( $section );

					// Class name with namespace and section suffix to load the matching class.
					$class = __NAMESPACE__ . "\Settings_Section_{$section}";

					// Actual section instantiation.
					new $class();

				}
			}
		}


		/**
		 * Callback function for Reset Settings.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Renamed from tstats_settings_reset_callback() to settings_reset_callback().
		 *
		 * @return void
		 */
		public function settings_reset_callback() {
			$action = 'reset_settings';
			if ( isset( $_POST[ $action ] ) ) {
				// Check nonce.
				if ( ! isset( $_POST['translation_stats_nonce_check'] ) || ! wp_verify_nonce( sanitize_key( $_POST['translation_stats_nonce_check'] ), 'translation_stats_action' ) ) {
					$this->nonce_fail();
				}

				// Update to default settings.
				update_option( TRANSLATION_STATS_WP_OPTION, $this->settings_defaults() );

				$admin_notice = array(
					'type'        => 'success',
					'notice-alt'  => false,
					'inline'      => false,
					'dismissible' => true,
					'force_show'  => true,
					'message'     => '<strong>' . esc_html__( 'Settings restored successfully.', 'translation-stats' ) . '</strong>',
				);
				Admin_Notice::message( $admin_notice );
			}
		}


		/**
		 * Callback function for Delete Transients.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Renamed from tstats_transients_delete_callback() to transients_delete_callback().
		 *
		 * @return void
		 */
		public function transients_delete_callback() {
			$action = 'delete_transients';
			if ( isset( $_POST[ $action ] ) ) {
				// Check nonce.
				if ( ! isset( $_POST['translation_stats_nonce_check'] ) || ! wp_verify_nonce( sanitize_key( $_POST['translation_stats_nonce_check'] ), 'translation_stats_action' ) ) {
					$this->nonce_fail();
				}
				// Delete translations stats and available languages transients.
				// The transient 'translation_stats_plugin_available_translations' will be immediately rebuilt on Settings_Section_Plugins->plugins_list() loading.
				$this->transients->delete_transients( TRANSLATION_STATS_TRANSIENTS_PREFIX );
				$admin_notice = array(
					'type'        => 'success',
					'notice-alt'  => false,
					'inline'      => false,
					'dismissible' => true,
					'force_show'  => true,
					'message'     => '<strong>' . esc_html__( 'Cache cleaned successfully.', 'translation-stats' ) . '</strong>',
				);
				Admin_Notice::message( $admin_notice );
			}
		}


		/**
		 * Callback function for Nonce fail.
		 *
		 * @since 0.9.5
		 * @since 0.9.9   Renamed from tstats_nonce_fail() to nonce_fail().
		 *
		 * @return void
		 */
		public function nonce_fail() {
			esc_html_e( 'Sorry, your nonce did not verify.', 'translation-stats' );
			exit;
		}


		/**
		 * Default Translation Stats Settings.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Renamed from tstats_settings_defaults() to settings_defaults().
		 *
		 * @return array  Array of default settings.
		 */
		public function settings_defaults() {
			$defaults = array(
				'settings' => array(
					'show_warnings'            => true,
					'translation_language'     => 'site-default',
					'delete_data_on_uninstall' => true,
					'transients_expiration'    => TRANSLATION_STATS_TRANSIENTS_TRANSLATIONS_EXPIRATION,
					'settings_version'         => TRANSLATION_STATS_SETTINGS_VERSION,
				),
			);
			return $defaults;
		}


		/**
		 * Get the tabs and content sections for the settings pages.
		 *
		 * The sections keys match the Section IDs from the section classes.
		 *
		 * @since 1.2.0
		 *
		 * @return array  Array of settings pages tabs and content sections.
		 */
		public static function get_settings_pages() {

			$settings_pages = array(
				'plugins'  => array(
					'tab'      => array(
						'title' => esc_html__( 'Plugins', 'translation-stats' ),
						'icon'  => 'dashicons dashicons-admin-plugins',
					),
					'sections' => array(
						'plugins',
					),
				),
				'settings' => array(
					'tab'      => array(
						'title' => esc_html__( 'Settings', 'translation-stats' ),
						'icon'  => 'dashicons dashicons-admin-settings',
					),
					'sections' => array(
						'general',
					),
				),
				'tools'    => array(
					'tab'      => array(
						'title' => esc_html__( 'Tools', 'translation-stats' ),
						'icon'  => 'dashicons dashicons-admin-tools',
					),
					'sections' => array(
						'tools_data',
						'tools_transients',
					),
				),
				'hidden'   => array(
					'tab'      => null,
					'sections' => array(
						'hidden',
					),
				),
			);

			/**
			 * Action hook to filter the settings pages.
			 *
			 * @since 1.2.0
			 */
			return apply_filters( 'translation_stats_settings_pages', $settings_pages );
		}


		/**
		 * Callback function for the options page.
		 *
		 * @since 0.8.0
		 * @since 0.9.9   Renamed from tstats_options_page() to options_page().
		 * @since 1.2.0   Renamed from options_page() to add_options_page().
		 *
		 * @return void
		 */
		public function add_options_page() {

			// Check required user capability.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'translation-stats' ) );
			}

			?>
			<div class="wrap">
				<?php

				// Settings Reset Callback.
				$this->settings_reset_callback();

				// Delete Transients Callback.
				$this->transients_delete_callback();

				// Get the settings pages tabs and sections.
				$settings_pages = self::get_settings_pages();

				?>
				<h1><?php echo esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ); ?></h1>
				<p><?php esc_html_e( 'Customize the translation stats you want to show.', 'translation-stats' ); ?></p>

				<div class="tstats-settings-wrapper">
					<?php

					// Add before Translation Stats settings.
					do_action( 'translation_stats_settings__before' );

					?>
					<div class="tstats-settings__content">

						<h2 class="nav-tab-wrapper">
							<?php

							// Render settings pages tabs navigation.
							foreach ( $settings_pages as $key => $page ) {
								// Check if tab exist.
								if ( ! is_null( $page['tab'] ) ) {
									?>
									<a class="nav-tab" href="#<?php echo esc_attr( $key ); ?>"><span class="<?php echo esc_attr( $page['tab']['icon'] ); ?>"></span> <?php echo esc_html( $page['tab']['title'] ); ?></a>
									<?php
								}
							}

							?>
						</h2>

						<div class="tabs-content">
							<form action='options.php' method='post'>
								<?php

								// Render settings sections content.
								foreach ( $settings_pages as $key => $page ) {
									?>
									<div id="tab-<?php echo esc_attr( $key ); ?>" class="tab-content hidden">
										<?php

										// Prefix settings section.
										$key = TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . $key;
										do_settings_sections( $key );
										settings_fields( $key );

										?>
									</div>
									<?php
								}

								// Add after Translation Stats settings content items.
								do_action( 'translation_stats_settings_content__after' );

								// Add nonce check.
								wp_nonce_field( 'translation_stats_action', 'translation_stats_nonce_check' );

								?>
								<p class="submit">
									<?php
									submit_button( __( 'Save Changes', 'translation-stats' ), 'primary', 'submit', false );
									?>
								</p>
							</form>
						</div>
					</div>

					<?php
					// Add after Translation Stats settings.
					do_action( 'translation_stats_settings__after' );
					?>

				</div>
			</div>
			<?php
		}
	}
}
