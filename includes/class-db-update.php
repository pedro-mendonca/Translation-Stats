<?php
/**
 * Class file for registering Translation Stats DB Update.
 *
 * @package Translation_Stats
 *
 * @since 1.0.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\DB_Update' ) ) {

	/**
	 * Class DB_Update.
	 */
	class DB_Update {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add database update check.
			add_action( 'plugins_loaded', array( $this, 'settings_db_check' ) );

			// Show the settings update notice in the admin notices, after the update.
			add_action( 'admin_notices', array( $this, 'settings_update_notice' ) );
		}


		/**
		 * Check settings database version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_db_check() {

			$installed_version = $this->installed_settings_version();

			if ( false === $installed_version ) {

				// If no settings found, do nothing.
				return;
			}

			if ( version_compare( $installed_version, TRANSLATION_STATS_SETTINGS_VERSION, '<' ) ) {

				// Update settings data version.
				$this->update_settings_version( $installed_version );

			}
		}


		/**
		 * Get Translation Stats currently installed settings version.
		 *
		 * @since 1.0.0
		 *
		 * @return false|string   Return settings version number, or false if no settings found.
		 */
		public function installed_settings_version() {

			$options = Utils::get_options();

			// Check if Translation Stats settings exist.
			if ( empty( $options ) ) {
				return false;
			}

			// Check if Translation Stats settings version exist.
			if ( isset( $options['settings']['settings_version'] ) ) {
				return $options['settings']['settings_version'];
			}

			return '0.0';
		}


		/**
		 * Update settings data version.
		 *
		 * @since 1.0.0
		 * @since 2.0.0   Run all the updates since the installed version, and show the notice later in the admin notices.
		 *
		 * @param string $installed_version   Currently installed settings data version.
		 *
		 * @return void
		 */
		public function update_settings_version( $installed_version ) {

			if ( version_compare( $installed_version, '1.0', '<' ) ) {

				$this->settings_update_v0_to_v1();

			}

			if ( version_compare( $installed_version, '2.0', '<' ) ) {

				$this->settings_update_v1_to_v2();

			}

			// Show the update notice in the admin notices, as the update runs before any output, in any request.
			set_transient( 'translation_stats_settings_updated', $installed_version, DAY_IN_SECONDS );
		}


		/**
		 * Show the settings update notice once, to a user who can manage the settings, after the settings update.
		 * In development mode, also show the updated settings.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function settings_update_notice() {

			$installed_version = get_transient( 'translation_stats_settings_updated' );

			if ( false === $installed_version || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			delete_transient( 'translation_stats_settings_updated' );

			$update_message = sprintf(
				wp_kses_post(
					/* translators: 1: Old settings version. 2: New settings version. */
					__( 'Translation Stats settings database successfully updated from version %1$s to version %2$s.', 'translation-stats' )
				),
				'<code>' . esc_html( is_string( $installed_version ) ? $installed_version : '' ) . '</code>',
				'<code>' . esc_html( TRANSLATION_STATS_SETTINGS_VERSION ) . '</code>'
			);

			$admin_notice = array(
				'type'        => 'success',
				'notice-alt'  => false,
				'inline'      => false,
				'dismissible' => true,
				'force_show'  => true,
				'message'     => $update_message,
			);
			new Admin_Notice( $admin_notice );

			if ( ! Utils::is_development_mode() ) {
				return;
			}

			$options = Utils::get_options();

			foreach ( array(
				'settings' => esc_html__( 'Settings', 'translation-stats' ),
				'plugins'  => esc_html__( 'Plugins', 'translation-stats' ),
			) as $key => $title ) {

				$admin_notice = array(
					'type'        => 'info',
					'notice-alt'  => true,
					'inline'      => false,
					'dismissible' => true,
					'force_show'  => true,
					'wrap'        => false,
					'message'     => sprintf(
						'<h3>%s</h3><pre>%s</pre>',
						$title,
						esc_html( (string) wp_json_encode( $options[ $key ] ?? array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )
					),
				);
				new Admin_Notice( $admin_notice );
			}
		}


		/**
		 * Update settings data version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_update_v0_to_v1() {

			// Settings without the 'settings' and 'plugins' keys.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );

			if ( ! is_array( $options ) ) {
				return;
			}

			$settings_v0 = array(
				'show_warnings',
				'translation_language',
				'delete_data_on_uninstall',
				'transients_expiration',
			);

			$settings_v0_deprecated = array(
				'all_plugins',
			);

			$settings_v1 = array();

			// Check for existing settings to move to sub array.
			foreach ( $settings_v0 as $option ) {
				if ( array_key_exists( $option, $options ) ) {
					$settings_v1['settings'][ $option ] = $options[ $option ];
					unset( $options[ $option ] );
				}
			}

			// Check for existing deprecated settings to remove.
			foreach ( $settings_v0_deprecated as $option ) {
				if ( array_key_exists( $option, $options ) ) {
					unset( $options[ $option ] );
				}
			}

			$settings_v1['settings']['settings_version'] = '1.0';

			$settings_v1['plugins'] = $options;

			update_option( TRANSLATION_STATS_WP_OPTION, $settings_v1 );
		}


		/**
		 * Update settings data from version 1.0 to version 2.0.
		 *
		 * Saves the checkboxes settings as booleans, instead of the string 'true' from the settings form.
		 * Unchecked checkboxes weren't saved, so missing settings are unchecked.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function settings_update_v1_to_v2() {

			$options = Utils::get_options();

			if ( false === $options ) {
				return;
			}

			foreach ( Settings::BOOLEAN_SETTINGS as $setting ) {
				$options['settings'][ $setting ] = ! empty( $options['settings'][ $setting ] );
			}

			$options['settings']['settings_version'] = '2.0';

			update_option( TRANSLATION_STATS_WP_OPTION, $options );
		}
	}
}
