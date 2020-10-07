<?php
/**
 * Class file for the Translation Stats translate.wordpress.org API.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

namespace Translation_Stats;

use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Translations_API' ) ) {


	/**
	 * Class Translations_API.
	 */
	class Translations_API {

		/**
		 * Check if plugin is on WordPress.org by checking if ID (from Plugin wp.org info) exists in 'response' or 'no_update' in 'update_plugins' transient.
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 *
		 * @return bool                Returns 'true' if the plugin exists on WordPress.org.
		 */
		public static function plugin_on_wporg( $plugin_file ) {
			$update_plugins = get_site_transient( 'update_plugins' );

			return ( isset( $update_plugins->response[ $plugin_file ]->id ) || isset( $update_plugins->no_update[ $plugin_file ]->id ) );
		}


		/**
		 * Get plugin metadata, if the plugin exists on WordPress.org.
		 *
		 * Example:
		 * $plugin_metadata = Translations_API::plugin_metadata( $plugin_file, 'metadata' ) (e.g. 'slug').
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $plugin_file   Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 * @param string $metadata      Metadata field ( e.g. 'slug' ).
		 *
		 * @return string|null          Returns metadata value from plugin.
		 */
		public static function plugin_metadata( $plugin_file, $metadata ) {
			$update_plugins = get_site_transient( 'update_plugins' );
			// Check if plugin is on WordPress.org.
			if ( ! self::plugin_on_wporg( $plugin_file ) ) {
				// If plugin doesn't have 'slug' key in metadata, get it from its file path.
				if ( 'slug' === $metadata ) {
					return self::get_plugin_slug( $plugin_file );
				}
				return '';
			}
			if ( isset( $update_plugins->response[ $plugin_file ]->$metadata ) ) {
				return $update_plugins->response[ $plugin_file ]->$metadata;
			}
			if ( isset( $update_plugins->no_update[ $plugin_file ]->$metadata ) ) {
				return $update_plugins->no_update[ $plugin_file ]->$metadata;
			}
			return null;
		}


		/**
		 * Get plugin slug from its file path.
		 *
		 * @since 0.9.6
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 *
		 * @return string              Plugin slug.
		 */
		public static function get_plugin_slug( $plugin_file ) {
			if ( false !== strpos( $plugin_file, '/' ) ) {
				$plugin_file_parts = explode( '/', $plugin_file );
			} else {
				$plugin_file_parts = explode( '.', $plugin_file );
			}
			return sanitize_title( $plugin_file_parts[0] );
		}


		/**
		 * Get plugin data from translate.WordPress.org API.
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $plugin   Plugin slug (project or project/subproject).
		 *
		 * @return array|WP_Error  Returns the response from translate.WordPress.org API URL.
		 */
		public static function translations_api_get_plugin( $plugin ) {
			$api_get = wp_remote_get( self::translations_api_url( 'plugins' ) . $plugin );
			return $api_get;
		}


		/**
		 * Check if translation project exist without /subproject slug (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis).
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $project_slug  Plugin Slug (e.g. 'plugin-slug').
		 *
		 * @return string $on_wporg     Returns 'true' if the translation project exist on WordPress.org.
		 */
		public static function plugin_project_on_translate_wporg( $project_slug ) {
			// Check project transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug );
			if ( false === $on_wporg ) {
				$json = self::translations_api_get_plugin( $project_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug, $on_wporg, get_option( TSTATS_WP_OPTION )['settings']['transients_expiration'] );
			}
			return $on_wporg;
		}


		/**
		 * Check if translation subproject exist (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis/stable).
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @param string $project_slug     Plugin Slug (e.g. 'plugin-slug').
		 * @param string $subproject_slug  Plugin Subproject Slug (e.g. 'dev', 'dev-readme', 'stable', 'stable-readme').
		 *
		 * @return string                  Returns 'true' if the translation subproject exist on WordPress.org.
		 */
		public function plugin_subproject_on_translate_wporg( $project_slug, $subproject_slug ) {
			// Check subproject transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug );
			if ( false === $on_wporg ) {
				$json = self::translations_api_get_plugin( $project_slug . '/' . $subproject_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug, $on_wporg, get_option( TSTATS_WP_OPTION )['settings']['transients_expiration'] );
			}
			return $on_wporg;
		}


		/**
		 * Set the translate.wordpress.org plugins subprojects structure with 'slug' and 'name'.
		 *
		 * @since 0.8.0
		 * @since 1.1.0  Remove method prefix.
		 *
		 * @return array $subprojects  Returns array of the plugins translation subprojects structure.
		 */
		public static function plugin_subprojects() {
			$subprojects = array(
				array(
					'slug' => 'dev',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name' => _x( 'Development', 'Subproject name', 'translation-stats' ),
				),
				array(
					'slug' => 'dev-readme',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name' => _x( 'Development Readme', 'Subproject name', 'translation-stats' ),
				),
				array(
					'slug' => 'stable',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name' => _x( 'Stable', 'Subproject name', 'translation-stats' ),
				),
				array(
					'slug' => 'stable-readme',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name' => _x( 'Stable Readme', 'Subproject name', 'translation-stats' ),
				),
			);
			return $subprojects;
		}


		/**
		 * Set the translate.wordpress.org WordPress core subprojects structure with 'slug', 'name' and language file 'domain'.
		 *
		 * @since 0.9.5
		 *
		 * @return array $subprojects  Returns array of the supported WordPress translation known subprojects.
		 */
		public function tstats_wordpress_subprojects() {

			$subprojects = array(
				array(
					'slug'   => '',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Development', 'Subproject name', 'translation-stats' ),
					'domain' => '',
				),
				array(
					'slug'   => 'admin/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Administration', 'Subproject name', 'translation-stats' ),
					'domain' => 'admin',
				),
				array(
					'slug'   => 'admin/network/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Network Admin', 'Subproject name', 'translation-stats' ),
					'domain' => 'admin-network',
				),
				array(
					'slug'   => 'cc/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Continents & Cities', 'Subproject name', 'translation-stats' ),
					'domain' => 'continents-cities',
				),
			);

			return $subprojects;
		}


		/**
		 * Get Translate API URL.
		 *
		 * Example:
		 * $api_url = Translations_API::translations_api_url( 'plugins' );
		 *
		 * @since 0.9.0
		 *
		 * @param string $project   Set the project API URL you want to get.
		 *
		 * @return string $api_url  Returns API URL.
		 */
		public static function translations_api_url( $project = null ) {

			$translations_api_url = array(
				'wp'        => 'https://translate.wordpress.org/api/projects/wp/',         // Translate API WordPress core URL.
				'languages' => 'https://translate.wordpress.org/api/languages',            // Translate API languages URL.
				'plugins'   => 'https://translate.wordpress.org/api/projects/wp-plugins/', // Translate API plugins URL.
				'themes'    => 'https://translate.wordpress.org/api/projects/wp-themes/',  // Translate API themes URL.
			);

			$api_url = $translations_api_url[ $project ];

			return $api_url;

		}


		/**
		 * Get Translate URL.
		 *
		 * Example:
		 * $url = Translations_API::translations_api->translations_url( 'plugins' );
		 *
		 * @since 0.9.5
		 *
		 * @param string $project  Set the project URL you want to get.
		 *
		 * @return string $url     Returns URL.
		 */
		public static function translations_url( $project ) {

			$translations_url = array(
				'wp'      => 'https://translate.wordpress.org/projects/wp/',         // Translate WordPress core URL.
				'plugins' => 'https://translate.wordpress.org/projects/wp-plugins/', // Translate plugins URL.
				'themes'  => 'https://translate.wordpress.org/projects/wp-themes/',  // Translate themes URL.
			);

			$url = $translations_url[ $project ];

			return $url;

		}


		/**
		 * Get locale data from wordpress.org and Translation Stats.
		 *
		 * Example:
		 * $locale = Translations_API::locale('pt_PT' );
		 * $locale_english_name = $locale->english_name.
		 *
		 * @since 0.9.0
		 * @since 1.1.0  Use Locale object.
		 *
		 * @param string $wp_locale  Locale ( e.g. 'pt_PT' ).
		 *
		 * @return object            Return selected Locale object data from Translation Tools and wordpress.org (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
		 */
		public static function locale( $wp_locale ) {

			// Get wordpress.org Locales.
			$locales = Locales::locales();

			$current_locale = null;

			foreach ( $locales as $locale ) {

				if ( $locale->wp_locale === $wp_locale ) {

					$current_locale = $locale;
					break;

				}
			}

			return $current_locale;

		}

	}

}
