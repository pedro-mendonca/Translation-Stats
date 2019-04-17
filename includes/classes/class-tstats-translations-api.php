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
		 * @since 0.9.0
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
		 * @since 0.9.0
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
		 * @since 0.9.0
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

					// Set an array for 'slug' to separate 'locale' and 'variant' slugs.
					$tstats_locale['slug'] = $this->tstats_locale_slug( $tstats_locale );

					// Add 'wporg_subdomain'.
					$tstats_locale['wporg_subdomain'] = $this->tstats_wporg_subdomain( $tstats_locale );

				}
			}
			return $tstats_locale;

		}


		/**
		 * Separate Locale slug in array( locale, variant ) to support GlotPress 2.x pseudo-variants.
		 *
		 * Example:
		 * GlotPress Slug: 'slug' => 'pt/ao90'.
		 * TStats Slug: 'slug' => array( 'locale' => 'pt', 'variant' => 'ao90').
		 *
		 * @since 0.9.1
		 *
		 * @param array $tstats_locale   Locale array.
		 *
		 * @return array $tstats_locale  Returns locale array with slug separated in array.
		 */
		public function tstats_locale_slug( $tstats_locale ) {

			$tstats_locale_slug = $tstats_locale['slug'];

			// Check if slug contain '/' and non default variant.
			if ( false !== strpos( $tstats_locale_slug, '/' ) ) {

				// In case there is a '/' separator, set the slug as an array 'locale' and 'variant'.
				$tstats_locale_slug = array(
					'locale'  => substr( $tstats_locale_slug, 0, strpos( $tstats_locale['slug'], '/' ) ),
					'variant' => substr( $tstats_locale_slug, 1 + strpos( $tstats_locale['slug'], '/' ) ),
				);

			} else {

				// In case there is no '/' separator, set slug as array with pseudo-variant as 'default.
				$tstats_locale_slug = array(
					'locale'  => $tstats_locale_slug,
					'variant' => 'default',
				);

			}

			return $tstats_locale_slug;

		}


		/**
		 * Add WordPress.org Locale subdomain to $tstats_locale.
		 * Defaults to Locale 'slug'.
		 * Custom subdomains use custom criteria from Translation Teams page (https://make.wordpress.org/polyglots/teams/).
		 * Updated on 2019-04-17.
		 *
		 * Example: 'pt_BR' => 'br'.
		 *
		 * @since 0.9.2
		 *
		 * @param array $tstats_locale      Locale array.
		 *
		 * @return string $wporg_subdomain  Returns WordPress Locale Subdomain.
		 */
		public function tstats_wporg_subdomain( $tstats_locale ) {

			// Set default criteria.
			$wporg_subdomain = $tstats_locale['slug']['locale'];

			/**
			 * The below Variants aren't included in the array because Translation Stats separates the Locale Slug from the Variant Slug in tstats_locale_slug().
			 * The subdomain of the Variants fallbacks automatically to its parent subdomain.
			 *
			 * 'ca_valencia'    => 'ca',    // Variant. Fallback to parent subdomain.
			 * 'nl_NL_formal'   => 'nl',    // Variant. Fallback to parent subdomain.
			 * 'de_DE_formal'   => 'de',    // Variant. Fallback to parent subdomain.
			 * 'de_CH_informal' => 'de-ch', // Variant. Fallback to parent subdomain.
			 * 'pt_PT_ao90'     => 'pt',    // Variant. Fallback to parent subdomain.
			 */
			$wporg_custom_subdomains = array(
				'ba'         => null,
				'bre'        => $tstats_locale['wp_locale'],
				'zh_CN'      => 'cn',
				'zh_TW'      => $tstats_locale['country_code'],
				'art_xemoji' => 'emoji',
				'ewe'        => null,
				'fo'         => $tstats_locale['country_code'],
				'gn'         => null,
				'haw_US'     => null,
				'ckb'        => $tstats_locale['lang_code_iso_639_1'],
				'lb_LU'      => 'ltz',
				'xmf'        => null,
				'mn'         => 'khk', // Code 'lang_code_iso_639_3' not present in GlotPress.
				'pt_BR'      => $tstats_locale['country_code'],
				'pa_IN'      => $tstats_locale['lang_code_iso_639_2'],
				'rue'        => null,
				'sa_IN'      => $tstats_locale['lang_code_iso_639_1'],
				'es_CL'      => $tstats_locale['country_code'],
				'es_PE'      => $tstats_locale['country_code'],
				'es_VE'      => $tstats_locale['country_code'],
				'gsw'        => null,
				'wa'         => null,
			);

			// Check if 'wp_locale' exist in the custom subdomain criteria array.
			if ( array_key_exists( $tstats_locale['wp_locale'], $wporg_custom_subdomains ) ) {
				$wporg_subdomain = $wporg_custom_subdomains[ $tstats_locale['wp_locale'] ];
			}

			return $wporg_subdomain;

		}

	}

}
