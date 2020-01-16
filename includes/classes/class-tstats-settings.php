<?php
/**
 * Class file for registering Translation Stats Settings.
 *
 * @since 0.8.0
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings' ) ) {

	/**
	 * Class TStats_Settings.
	 */
	class TStats_Settings {


		/**
		 * Notices.
		 *
		 * @var object
		 */
		protected $tstats_notices;

		/**
		 * Transients.
		 *
		 * @var object
		 */
		protected $tstats_transients;

		/**
		 * Settings API.
		 *
		 * @var object
		 */
		protected $tstats_settings_api;

		/**
		 * Plugins Settings.
		 *
		 * @var object
		 */
		protected $tstats_settings_plugins;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Notices.
			$this->tstats_notices = new TStats_Notices();

			// Instantiate Translation Stats Transients.
			$this->tstats_transients = new TStats_Transients();

			// Instantiate Translation Stats Settings API.
			$this->tstats_settings_api = new TStats_Settings_API();

			// Instantiate Translation Stats Settings Plugins.
			$this->tstats_settings_plugins = new TStats_Settings_Plugins();

			// Add admin menu item.
			add_action( 'admin_menu', array( $this, 'tstats_admin_menu' ) );

			// Add plugin settings sections.
			add_action( 'admin_init', array( $this, 'tstats_settings_sections' ) );

			// Initialize Settings Sidebar.
			new TStats_Settings_Sidebar();

			// Initialize Settings Widgets.
			new TStats_Settings_Widgets();

			// Initialize Settings Footer.
			new TStats_Settings_Footer();


		}


		/**
		 * Registers a new Translation Stats Settings Page.
		 *
		 * @since 0.8.0
		 */
		public function tstats_admin_menu() {
			// Add submenu page to the Settings main menu.
			add_options_page(
				esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ), // The text to be displayed in the title tag.
				esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ), // The text to be used for the menu.
				'manage_options',                                                             // The capability required to display this menu.
				TSTATS_SETTINGS_PAGE,                                                         // The unique slug name to refer to this menu.
				array( $this, 'tstats_options_page' )                                         // The function to output the page content.
			);
		}


		/**
		 * Add Settings Sections.
		 *
		 * @since 0.9.0
		 */
		public function tstats_settings_sections() {

			// Plugins settings section.
			$this->tstats_settings_section__plugins();

			// General settings section.
			$this->tstats_settings_section__general();

			// Tools settings section.
			$this->tstats_settings_section__tools();

			// Add section after Translation settings sections.
			do_action( 'tstats_settings_section__after' );

		}


		/**
		 * Registers Settings Plugins page section.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section__plugins() {

			add_settings_section(
				'tstats_settings__plugins',                                                   // String for use in the 'id' attribute of tags.
				__( 'Installed Plugins', 'translation-stats' ),                              // Title of the section.
				array( $this->tstats_settings_plugins, 'tstats_settings__plugins__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__plugins'                                                    // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings__plugins', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION           // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Registers Settings General page section.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section__general() {

			add_settings_section(
				'tstats_settings__general',                          // String for use in the 'id' attribute of tags.
				__( 'General Settings', 'translation-stats' ),      // Title of the section.
				array( $this, 'tstats_settings__general__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__general'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings__general', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION           // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Registers Settings Tools page section.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section__tools() {

			add_settings_section(
				'tstats_settings__tools__settings',                          // String for use in the 'id' attribute of tags.
				__( 'Settings', 'translation-stats' ),               // Title of the section.
				array( $this, 'tstats_settings__tools__settings__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__tools__settings'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			add_settings_section(
				'tstats_settings__tools__transients',                          // String for use in the 'id' attribute of tags.
				__( 'Cache', 'translation-stats' ),                             // Title of the section.
				array( $this, 'tstats_settings__tools__transients__callback' ), // Function that fills the section with the desired content.
				'tstats_settings__tools__transients'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings__tools__settings', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION            // The WordPress option to store Translation Stats settings.
			);

			register_setting(
				'tstats_settings__tools__transients', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION                       // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Callback function for section "Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings__general__callback() {

			$section = 'tstats_settings__general';

			/*
			Section description.
			esc_html_e( 'General settings for Translation Stats', 'translation-stats' );
			*/

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'     => $section,
					'id'          => 'show_warnings',
					'type'        => 'checkbox',
					'class'       => '',
					'title'       => __( 'Warnings', 'translation-stats' ),
					'label'       => __( 'Show translation project warnings', 'translation-stats' ),
					'description' => __( 'Check this to show translation project error messages for selected plugins.', 'translation-stats' ),
					'helper'      => __( 'Need help?', 'translation-stats' ),
					'callback'    => 'tstats_render_input_checkbox',
					'default'     => true,
				)
			);

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'        => $section,
					'id'             => 'translation_language',
					'type'           => 'select',
					'class'          => '',
					'title'          => __( 'Translation Language', 'translation-stats' ),
					'label'          => __( 'Select translation language', 'translation-stats' ),
					'description'    => __( 'Select the language for which you want to show the translation stats.', 'translation-stats' ),
					'helper'         => __( 'Need help?', 'translation-stats' ),
					'callback'       => 'tstats_render_input_select__language',
					'select_options' => '',
					'default'        => 'site-default',
				)
			);
		}


		/**
		 * Callback function for section "Tools > Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings__tools__settings__callback() {

			$section = 'tstats_settings__tools__settings';

			/*
			Section description.
			esc_html_e( 'Advanced settings for Translation Stats', 'translation-stats' );
			*/

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'     => $section,
					'id'          => 'delete_data_on_uninstall',
					'type'        => 'checkbox',
					'class'       => '',
					'title'       => __( 'Uninstall', 'translation-stats' ),
					'label'       => __( 'Delete all plugin data on uninstall', 'translation-stats' ),
					'description' => __( 'Check to delete all Translation Stats plugin settings and cache on uninstall.', 'translation-stats' ),
					'helper'      => __( 'Need help?', 'translation-stats' ),
					'callback'    => 'tstats_render_input_checkbox',
					'default'     => true,
				)
			);

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'      => $section,
					'id'           => 'reset_settings',
					'name'         => 'reset_settings',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Reset Settings', 'translation-stats' ),
					'label'        => __( 'Reset', 'translation-stats' ),
					'description'  => __( 'Click to restore the default Translation Stats plugin settings.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'callback'     => 'tstats_render_input_button',
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! Translation Stats plugin settings will be reset to default! Click \'Cancel\' to go back, \'OK\' to reset.', 'translation-stats' ),
				)
			);

		}


		/**
		 * Callback function for section "Tools > Cache".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings__tools__transients__callback() {

			$section = 'tstats_settings__tools__transients';

			/*
			Section description.
			esc_html_e( 'Transient settings for Translation Stats', 'translation-stats' );
			*/

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'        => $section,
					'id'             => 'transients_expiration',
					'type'           => 'select',
					'class'          => '',
					'title'          => __( 'Expiration', 'translation-stats' ),
					'label'          => __( 'Select cache expiration', 'translation-stats' ),
					'description'    => __( 'Set the cache expiration to update the translation stats.', 'translation-stats' ),
					'helper'         => __( 'Need help?', 'translation-stats' ),
					'callback'       => 'tstats_render_input_select',
					'select_options' => array(
						'3600'   => __( '60 Minutes', 'translation-stats' ),
						'86400'  => __( '24 Hours', 'translation-stats' ),
						'604800' => __( '7 Days', 'translation-stats' ),
					),
					'default'        => '86400',
				)
			);

			$this->tstats_settings_api->tstats_add_settings_field(
				array(
					'section'      => $section,
					'id'           => 'delete_transients',
					'name'         => 'delete_transients',
					'type'         => 'button',
					'class'        => 'primary',
					'title'        => __( 'Clean Cache', 'translation-stats' ),
					'label'        => __( 'Clean', 'translation-stats' ),
					'description'  => __( 'Click to delete all Translation Stats cache and force update translation stats.', 'translation-stats' ),
					'helper'       => __( 'Need help?', 'translation-stats' ),
					'callback'     => 'tstats_render_input_button',
					'wrap'         => false,
					'formaction'   => '',
					'confirmation' => __( 'Warning! All Translation Stats plugin cache will be deleted! Click \'Cancel\' to go back, \'OK\' to delete.', 'translation-stats' ),
				)
			);

		}


		/**
		 * Callback function for Reset Settings.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_reset_callback() {
			$action = 'reset_settings';
			if ( isset( $_POST[ $action ] ) ) {
				// Check nonce.
				if ( ! isset( $_POST['tstats_nonce_check'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tstats_nonce_check'] ), 'tstats_action' ) ) {
					$this->tstats_nonce_fail();
				}
				// Choose 'load-defaults' or 'delete'.
				$action = 'load-defaults';
				if ( 'load-defaults' === $action ) {
					update_option( TSTATS_WP_OPTION, $this->tstats_settings_defaults() );
				} elseif ( 'delete' === $action ) {
					delete_option( TSTATS_WP_OPTION );
				}
				$admin_notice = array(
					'type'        => 'success',
					'notice-alt'  => false,
					'message'     => '<strong>' . esc_html__( 'Settings restored successfully.', 'translation-stats' ) . '</strong>',
					'dismissible' => true,
				);
				$this->tstats_notices->tstats_notice_message( $admin_notice );
			}
		}


		/**
		 * Callback function for Delete Transients.
		 *
		 * @since 0.8.0
		 */
		public function tstats_transients_delete_callback() {
			$action = 'delete_transients';
			if ( isset( $_POST[ $action ] ) ) {
				// Check nonce.
				if ( ! isset( $_POST['tstats_nonce_check'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tstats_nonce_check'] ), 'tstats_action' ) ) {
					$this->tstats_nonce_fail();
				}
				// Delete translations stats and available languages transients.
				// The transient 'translation_stats_plugin_available_translations' will be immediatly rebuilt on tstats_render_settings__plugins_list() loading.
				$this->tstats_transients->tstats_delete_transients( TSTATS_TRANSIENTS_PREFIX );
				$admin_notice = array(
					'type'        => 'success',
					'notice-alt'  => false,
					'message'     => '<strong>' . esc_html__( 'Cache cleaned successfully.', 'translation-stats' ) . '</strong>',
					'dismissible' => true,
				);
				$this->tstats_notices->tstats_notice_message( $admin_notice );
			}
		}


		/**
		 * Callback function for Nonce fail.
		 *
		 * @since 0.9.5
		 */
		public function tstats_nonce_fail() {
			esc_html_e( 'Sorry, your nonce did not verify.', 'translation-stats' );
			exit;
		}


		/**
		 * Default Translation Stats Settings.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_defaults() {
			$defaults = array(
				'show_warnings'            => true,
				'translation_language'     => 'site-default',
				'delete_data_on_uninstall' => true,
				'transients_expiration'    => TSTATS_TRANSIENTS_TRANSLATIONS_EXPIRATION,
			);
			return $defaults;
		}


		/**
		 * Callback function for the options page.
		 *
		 * @since 0.8.0
		 */
		public function tstats_options_page() {
			// Check required user capability.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'translation-stats' ) );
			}
			?>

			<div class="wrap">
				<?php

				// Settings Reset Callback.
				$this->tstats_settings_reset_callback();

				// Delete Transients Callback.
				$this->tstats_transients_delete_callback();

				?>
				<h1><?php echo esc_html_x( 'Translation Stats', 'Options Page Title', 'translation-stats' ); ?></h1>
				<p><?php esc_html_e( 'Customize the translation stats you want to show.', 'translation-stats' ); ?></p>

				<div class="tstats-settings-wrapper">

					<?php
					// Add before Translation Stats settings.
					do_action( 'tstats_settings__before' );
					?>

					<div class="tstats-settings__content">

						<h2 class="nav-tab-wrapper">
							<a class="nav-tab" href="#plugins"><span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( 'Plugins', 'translation-stats' ); ?></a>
							<a class="nav-tab" href="#settings"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Settings', 'translation-stats' ); ?></a>
							<a class="nav-tab" href="#tools"><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Tools', 'translation-stats' ); ?></a>

							<?php
							// Add after Translation Stats settings tabs items.
							do_action( 'tstats_settings_tab__after' );
							?>

						</h2>

						<div class="tabs-content">
							<form action='options.php' method='post'>

								<div id="tab-plugins" class="tab-content hidden">
									<?php
									$section = 'tstats_settings__plugins';
									do_settings_sections( $section );
									settings_fields( $section );
									?>
								</div>
								<div id="tab-settings" class="tab-content hidden">
									<?php
									$section = 'tstats_settings__general';
									do_settings_sections( $section );
									settings_fields( $section );
									?>
								</div>
								<div id="tab-tools" class="tab-content hidden">
									<?php
									$section = 'tstats_settings__tools__settings';
									do_settings_sections( $section );
									settings_fields( $section );
									$section = 'tstats_settings__tools__transients';
									do_settings_sections( $section );
									settings_fields( $section );
									?>
								</div>

								<?php
								// Add after Translation Stats settings content items.
								do_action( 'tstats_settings_content__after' );
								?>

								<?php wp_nonce_field( 'tstats_action', 'tstats_nonce_check' ); ?>

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
					do_action( 'tstats_settings__after' );
					?>

				</div>
			</div>
			<?php
		}

	}

}
