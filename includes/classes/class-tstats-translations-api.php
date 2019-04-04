<?php
/**
 * Primary class file for the Translation Stats translate.wordpress.org API.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Translations_API' ) ) {

	/**
	 * Class TStats_Translations_API.
	 */
	class TStats_Translations_API {

		/**
		 * Check if plugin is on WordPress.org by checking if ID (from Plugin wp.org info) exists in 'response' or 'no_update' in 'update_plugins' transient.
		 *
		 * @since 0.8.0
		 *
		 * @param string $plugin_file  Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 * @return string              Returns 'true' if the plugin exists on WordPress.org.
		 */
		public function tstats_plugin_on_wporg( $plugin_file ) {
			$plugin_state = get_site_transient( 'update_plugins' );
			if ( isset( $plugin_state->response[ $plugin_file ]->id ) || isset( $plugin_state->no_update[ $plugin_file ]->id ) ) {
				return true;
			}
		}


		/**
		 * Get plugin metadata, if the plugin exists on WordPress.org.
		 *
		 * Example:
		 * $plugin_metadata = $this->tstats_translations_api->tstats_plugin_metadata( $plugin_file, 'metadata' ) (e.g. 'slug').
		 *
		 * @since 0.8.0
		 *
		 * @param string $plugin_file       Plugin ID ( e.g. 'slug/plugin-name.php' ).
		 * @param string $metadata          Metadata field ( e.g. 'slug' ).
		 * @return string $plugin_metadata  Returns metadata value from plugin.
		 */
		public function tstats_plugin_metadata( $plugin_file, $metadata ) {
			$plugin_state = get_site_transient( 'update_plugins' );
			// Check if plugin is on WordPress.org.
			if ( $this->tstats_plugin_on_wporg( $plugin_file ) ) {
				if ( isset( $plugin_state->response[ $plugin_file ]->$metadata ) ) {
					$plugin_metadata = $plugin_state->response[ $plugin_file ]->$metadata;
				}
				if ( isset( $plugin_state->no_update[ $plugin_file ]->$metadata ) ) {
					$plugin_metadata = $plugin_state->no_update[ $plugin_file ]->$metadata;
				}
				return $plugin_metadata;
			}
		}


		/**
		 * Get plugin data from translate.WordPress.org API.
		 *
		 * @since 0.8.0
		 *
		 * @param string $plugin    Plugin slug (project or project/subproject).
		 * @return string $api_get  Returns the response from translate.WordPress.org API URL.
		 */
		public function tstats_translations_api_get_plugin( $plugin ) {
			$api_get = wp_remote_get( $this->tstats_translations_api_url( 'plugins' ) . $plugin );
			return $api_get;
		}


		/**
		 * Check if translation project exist without /subproject slug (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis).
		 *
		 * @since 0.8.0
		 *
		 * @param string $project_slug  Plugin Slug (e.g. 'plugin-slug').
		 * @return string $on_wporg     Returns 'true' if the translation project exist on WordPress.org.
		 */
		public function tstats_plugin_project_on_translate_wporg( $project_slug ) {
			// Check project transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translations_api_get_plugin( $project_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug, $on_wporg, get_option( TSTATS_WP_OPTION )['transients_expiration'] );
			}
			return $on_wporg;
		}


		/**
		 * Check if translation subproject exist (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis/stable).
		 *
		 * @since 0.8.0
		 *
		 * @param string $project_slug     Plugin Slug (e.g. 'plugin-slug').
		 * @param string $subproject_slug  Plugin Subproject Slug (e.g. 'dev', 'dev-readme', 'stable', 'stable-readme').
		 * @return string                  Returns 'true' if the translation subproject exist on WordPress.org.
		 */
		public function tstats_plugin_subproject_on_translate_wporg( $project_slug, $subproject_slug ) {
			// Check subproject transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translations_api_get_plugin( $project_slug . '/' . $subproject_slug );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {
					$on_wporg = false;
				} else {
					$on_wporg = true;
				}
				set_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug, $on_wporg, get_option( TSTATS_WP_OPTION )['transients_expiration'] );
			}
			return $on_wporg;
		}


		/**
		 * Set the translate.wordpress.org plugins subprojects structure with 'slug' and 'name'.
		 *
		 * @since 0.8.0
		 *
		 * @return array $subprojects  Returns array of the plugins translation subprojects structure.
		 */
		public function tstats_plugin_subprojects() {
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
		 * Get Translate API URL.
		 *
		 * Example:
		 * $api_url = $this->tstats_translations_api->tstats_translations_api_url( 'plugins' );
		 *
		 * @since 0.8.6
		 *
		 * @param string $project   Set the project API URL you want to get.
		 * @return string $api_url  Returns API URL.
		 */
		public function tstats_translations_api_url( $project ) {

			$translations_api = array(
				'languages' => 'https://translate.wordpress.org/api/languages',            // Translate API languages URL.
				'plugins'   => 'https://translate.wordpress.org/api/projects/wp-plugins/', // Translate API plugins URL.
				'themes'    => 'https://translate.wordpress.org/api/projects/wp-themes/',  // Translate API themes URL.
				'wordpress' => 'https://translate.wordpress.org/api/projects/wp/',         // Translate API WordPress core URL.
			);

			$api_url = $translations_api[ $project ];

			return $api_url;

		}


		/**
		 * Get available translations locales data from translate.WordPress.org API.
		 * Store the available translation locales in transient.
		 *
		 * @since 0.8.6
		 *
		 * @return object $tstats_locales  Returns all the locales with 'wp_locale' available in translate.WordPress.org.
		 */
		public function tstats_locales() {
			// Translate API languages URL.
			$url = $this->tstats_translations_api_url( 'languages' );

			// Translation Stats languages transient name.
			$transient_name = 'available_translations';

			// Check languages transients.
			$tstats_locales = get_transient( TSTATS_TRANSIENTS_PREFIX . $transient_name );

			if ( false === $tstats_locales ) {

				$json = wp_remote_get( $url );
				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {

					// API Unreachable (Error 404).
					$tstats_locales = false;

				} else {

					$body = json_decode( $json['body'], true );
					if ( empty( $body ) ) {

						// No languages found.
						$tstats_locales = false;

					} else {

						$tstats_locales = array();
						foreach ( $body as $key => $tstats_locale ) {

							// List locales based on existent 'wp_locale'.
							if ( $tstats_locale['wp_locale'] ) {
								unset( $key );
								$tstats_locales[ $tstats_locale['wp_locale'] ] = $tstats_locale;
							}
						}
					}
				}

				set_transient( TSTATS_TRANSIENTS_PREFIX . $transient_name, $tstats_locales, TSTATS_TRANSIENTS_LOCALES_EXPIRATION );
			}
			return $tstats_locales;
		}


		/**
		 * Get locale data.
		 *
		 * Example:
		 * $locale = $this->tstats_translations_api->tstats_locale( 'pt_PT' );
		 * $locale_english_name = $locale['english_name'].
		 *
		 * @since 0.8.6
		 *
		 * @param string $wp_locale       WordPress Locale ( e.g. 'pt_PT' ).
		 *
		 * @return array $tstats_locale  Returns locale array from GlotPress (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
		 */
		public function tstats_locale( $wp_locale ) {

			$tstats_locales = $this->tstats_locales();

			$tstats_locale = null;

			foreach ( $tstats_locales as $key => $value ) {
				if ( $value['wp_locale'] === $wp_locale ) {
					unset( $key );
					$tstats_locale = $value;
				}
			}
			return $tstats_locale;

		}

	}

}
