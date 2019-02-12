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
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Debug.
			$this->tstats_debug = new TStats_Debug();

			// Instantiate Translation Stats Transients.
			$this->tstats_transients = new TStats_Transients();

			// Instantiate Translation Stats Settings API.
			$this->tstats_settings_api = new TStats_Settings_API();

			// Instantiate Translation Stats Settings Plugins.
			$this->tstats_settings_plugins = new TStats_Settings_Plugins();

			// Add admin menu item.
			add_action( 'admin_menu', array( $this, 'tstats_admin_menu' ) );

			// Add plugin settings.
			add_action( 'admin_init', array( $this, 'tstats_settings_section_plugins' ) );
			add_action( 'admin_init', array( $this, 'tstats_settings_section_general' ) );
			add_action( 'admin_init', array( $this, 'tstats_settings_section_advanced' ) );

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
		 * Registers Settings Page sections.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section_plugins() {

			add_settings_section(
				'tstats_settings_plugins',                                                   // String for use in the 'id' attribute of tags.
				__( 'Installed Plugins', 'translation-stats' ),                              // Title of the section.
				array( $this->tstats_settings_plugins, 'tstats_settings_plugins_callback' ), // Function that fills the section with the desired content.
				'tstats_settings_plugins'                                                    // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings_plugins', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION           // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Registers Settings Page sections.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section_general() {

			add_settings_section(
				'tstats_settings_general',                          // String for use in the 'id' attribute of tags.
				__( 'General Settings', 'translation-stats' ),      // Title of the section.
				array( $this, 'tstats_settings_general_callback' ), // Function that fills the section with the desired content.
				'tstats_settings_general'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings_general', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION           // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Registers Settings Page sections.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_section_advanced() {

			add_settings_section(
				'tstats_settings_advanced',                          // String for use in the 'id' attribute of tags.
				__( 'Settings', 'translation-stats' ),               // Title of the section.
				array( $this, 'tstats_settings_advanced_callback' ), // Function that fills the section with the desired content.
				'tstats_settings_advanced'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			add_settings_section(
				'tstats_settings_advanced_transients',                          // String for use in the 'id' attribute of tags.
				__( 'Cache', 'translation-stats' ),                             // Title of the section.
				array( $this, 'tstats_settings_advanced_transients_callback' ), // Function that fills the section with the desired content.
				'tstats_settings_advanced_transients'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			add_settings_section(
				'tstats_settings_advanced_debug',                          // String for use in the 'id' attribute of tags.
				__( 'Debug', 'translation-stats' ),                        // Title of the section.
				array( $this, 'tstats_settings_advanced_debug_callback' ), // Function that fills the section with the desired content.
				'tstats_settings_advanced_debug'                           // The menu page on which to display this section. Should match $menu_slug.
			);

			register_setting(
				'tstats_settings_advanced', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION            // The WordPress option to store Translation Stats settings.
			);

			register_setting(
				'tstats_settings_advanced_transients', // The menu page on which to display this section. Should match $menu_slug.
				TSTATS_WP_OPTION                       // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Callback function for section "General Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_general_callback() {

			$section = 'tstats_settings_general';

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
		 * Callback function for section "Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_advanced_callback() {

			$section = 'tstats_settings_advanced';

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
		 * Callback function for section "Cache".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_advanced_transients_callback() {

			$section = 'tstats_settings_advanced_transients';

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
		 * Callback function for section "Debug".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_advanced_debug_callback() {

			?>
			<p class="description">
				<?php
				esc_html_e( 'List of settings and transients of Translation Stats.', 'translation-stats' );
				?>
			</p>
			<?php

			// Display debug formated message with plugin options.
			$this->tstats_debug->tstats_debug( 'info', true, false );

		}


		/**
		 * Callback function for Reset Settings.
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_reset_callback() {
			$action = 'reset_settings';
			if ( isset( $_POST[ $action ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->tstats_nonce_verify_callback();

				// Choose 'load-defaults' or 'delete'.
				$action = 'load-defaults';
				if ( 'load-defaults' === $action ) {
					update_option( TSTATS_WP_OPTION, $this->tstats_settings_defaults() );
				} elseif ( 'delete' === $action ) {
					delete_option( TSTATS_WP_OPTION );
				}
				?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Settings restored successfully.', 'translation-stats' ); ?></strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'translation-stats' ); ?></span>
					</button>
				</div>
				<?php
			}
		}


		/**
		 * Callback function for Delete Transients.
		 *
		 * @since 0.8.0
		 */
		public function tstats_transients_delete_callback() {
			$action = 'delete_transients';
			if ( isset( $_POST[ $action ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->tstats_nonce_verify_callback();
				$this->tstats_transients->tstats_delete_transients( TSTATS_TRANSIENTS_PREFIX );
				?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Cache cleaned successfully.', 'translation-stats' ); ?></strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'translation-stats' ); ?></span>
					</button>
				</div>
				<?php
			}
		}


		/**
		 * Callback function for Nonce verification.
		 *
		 * @since 0.8.0
		 */
		public function tstats_nonce_verify_callback() {
			if ( ! isset( $_POST['tstats_nonce_field'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tstats_nonce_field'] ), 'tstats_action' ) ) {
				esc_html_e( 'Sorry, your nonce did not verify.', 'translation-stats' );
				exit;
			}
		}


		/**
		 * Callback function for section "Plugins Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings_defaults() {
			$defaults = array(
				'show_warnings'            => true,
				'translation_language'     => 'site-default',
				'delete_data_on_uninstall' => true,
				'transients_expiration'    => DAY_IN_SECONDS,
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

				<div id="tstats-settings">

					<h2 class="nav-tab-wrapper">
						<a class="nav-tab nav-tab-active" href="#plugins"><?php esc_html_e( 'Plugins', 'translation-stats' ); ?></a>
						<a class="nav-tab" href="#settings"><?php esc_html_e( 'Settings', 'translation-stats' ); ?></a>
						<a class="nav-tab" href="#tools"><?php esc_html_e( 'Tools', 'translation-stats' ); ?></a>
						<?php if ( TSTATS_DEBUG ) { ?>
						<a class="nav-tab" href="#debug"><?php esc_html_e( 'Debug', 'translation-stats' ); ?></a>
						<?php } ?>
						<span class="tstats-version-info">
							<?php
							printf(
								/* translators: Plugin Name and version - Do not translate! */
								esc_html__( 'Translation Stats %s', 'translation-stats' ),
								'<small>v.' . esc_html( TSTATS_VERSION ) . '</small>'
							);
							?>
						</span>
					</h2>

					<div class="tabs-content">
						<form action='options.php' method='post'>

							<div id="tab-plugins" class="tab-content">
								<?php
								$section = 'tstats_settings_plugins';
								do_settings_sections( $section );
								settings_fields( $section );
								?>
							</div>
							<div id="tab-settings" class="tab-content hidden">
								<?php
								$section = 'tstats_settings_general';
								do_settings_sections( $section );
								settings_fields( $section );
								?>
							</div>
							<div id="tab-tools" class="tab-content hidden">
								<?php
								$section = 'tstats_settings_advanced';
								do_settings_sections( $section );
								settings_fields( $section );
								$section = 'tstats_settings_advanced_transients';
								do_settings_sections( $section );
								settings_fields( $section );
								?>
							</div>
							<?php if ( TSTATS_DEBUG ) { ?>
							<div id="tab-debug" class="tab-content hidden">
								<?php
								$section = 'tstats_settings_advanced_debug';
								do_settings_sections( $section );
								?>
							</div>
							<?php } ?>

							<?php wp_nonce_field( 'tstats_action', 'tstats_nonce_field' ); ?>
							<p class="submit">
								<?php
								submit_button( __( 'Save Changes', 'translation-stats' ), 'primary', 'submit', false );
								?>
							</p>
						</form>
					</div>
				</div>
			</div>
			<?php
		}

	}

}

new TStats_Settings();
