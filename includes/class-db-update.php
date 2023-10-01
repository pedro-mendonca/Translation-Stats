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
	exit;
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

			if ( TRANSLATION_STATS_SETTINGS_VERSION !== $installed_version ) {

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

			$options = get_site_option( TRANSLATION_STATS_WP_OPTION );

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
		 *
		 * @param string $installed_version   Currently installed settings data version.
		 *
		 * @return void
		 */
		public function update_settings_version( $installed_version ) {

			if ( '0.0' === $installed_version ) {

				$this->settings_update_v0_to_v1();

			}

			$update_message = sprintf(
				wp_kses_post(
					/* translators: 1: Old settings version. 2: New settings version. */
					__( 'Translation Stats settings database successfully updated from version %1$s to version %2$s.', 'translation-stats' )
				),
				'<code>' . esc_html( $installed_version ) . '</code>',
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
			Admin_Notice::message( $admin_notice );
		}


		/**
		 * Update settings data version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_update_v0_to_v1() {

			$options = get_site_option( TRANSLATION_STATS_WP_OPTION );

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

			update_site_option( TRANSLATION_STATS_WP_OPTION, $settings_v1 );

			if ( Utils::is_development_mode() ) {

				$message = sprintf(
					'<h3>%s</h3><pre>%s</pre>',
					esc_html__( 'Settings', 'translation-stats' ),
					wp_json_encode( get_site_option( TRANSLATION_STATS_WP_OPTION )['settings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
				);

				$admin_notice = array(
					'type'        => 'info',
					'notice-alt'  => true,
					'inline'      => false,
					'dismissible' => true,
					'force_show'  => true,
					'wrap'        => false,
					'message'     => $message,
				);
				Admin_Notice::message( $admin_notice );

				$message = sprintf(
					'<h3>%s</h3><pre>%s</pre>',
					esc_html__( 'Plugins', 'translation-stats' ),
					wp_json_encode( get_site_option( TRANSLATION_STATS_WP_OPTION )['plugins'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
				);

				$admin_notice = array(
					'type'        => 'info',
					'notice-alt'  => true,
					'inline'      => false,
					'dismissible' => true,
					'force_show'  => true,
					'wrap'        => false,
					'message'     => $message,
				);
				Admin_Notice::message( $admin_notice );
			}
		}
	}
}
