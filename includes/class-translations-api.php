<?php
/**
 * Class file for the Translation Stats translate.wordpress.org API.
 *
 * @package Translation_Stats
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
			$api_get = wp_remote_get( self::translate_url( 'plugins', true ) . $plugin );
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
			$on_wporg = get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug );
			if ( false === $on_wporg ) {
				$json = self::translations_api_get_plugin( $project_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug, $on_wporg, get_option( TRANSLATION_STATS_WP_OPTION )['settings']['transients_expiration'] );
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
			$on_wporg = get_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug );
			if ( false === $on_wporg ) {
				$json = self::translations_api_get_plugin( $project_slug . '/' . $subproject_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TRANSLATION_STATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug, $on_wporg, get_option( TRANSLATION_STATS_WP_OPTION )['settings']['transients_expiration'] );
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
		 * Get the translate site URL.
		 *
		 * Example for WordPress.org plugins URL (normal URL, not API URL):
		 * $url = Translations_API::translate_url( 'plugins', false );
		 *
		 * @since 0.9.0
		 * @since 1.2.0   Renamed from translations_api_url() to translate_url().
		 *                Added 'api' parameter to allow choose 'api' or normal URL.
		 *
		 * @param string $project   Set the project URL you want to get. Defaults to null.
		 * @param bool   $api       Set to 'true' to get the API URL. Defaults to false.
		 *
		 * @return string           Returns URL.
		 */
		public static function translate_url( $project = null, $api = false ) {

			// Set WordPress.org translate site URL.
			$translate_url = 'https://translate.wordpress.org/';

			/**
			 * Filters the translate site URL. Defaults to Translating WordPress.org site.
			 * This allows to override with a private GlotPress install with the same exact WP core structure as https://translate.w.org/projects/wp/
			 * Example: 'https://translate.my-site.com/glotpress/'
			 *
			 * @since 1.2.0
			 */
			$translate_url = apply_filters( 'translation_stats_translate_url', $translate_url );

			// Check if the request is for an API URL.
			if ( true === $api ) {
				// Add the API slug.
				$translate_url .= 'api/';
			}

			// WordPress.org translate known projects slugs.
			$wporg_projects = array(
				'languages' => 'languages/',           // Translating WordPress languages slug (deprecated).
				'wp'        => 'projects/wp/',         // Translating WordPress core slug.
				'plugins'   => 'projects/wp-plugins/', // Translating WordPress plugins slug.
				'themes'    => 'projects/wp-themes/',  // Translating WordPress themes slug.
			);

			// Check if project is one of the known ones.
			if ( array_key_exists( $project, $wporg_projects ) ) {
				// Add project slug to translate URL.
				$translate_url .= $wporg_projects[ $project ];
			}

			return $translate_url;
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
