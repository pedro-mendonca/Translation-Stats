<?php
/**
 * Class file for the Translation Stats Locales.
 *
 * Extends GP_Locales from:
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * @package Translation_Stats
 *
 * @since 1.1.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Locales' ) ) {

	/**
	 * Class Locales.
	 */
	class Locales extends GP_Locales {


		/**
		 * Set custom 'translation_stats_locales' global variable.
		 * This avoids conflicts with other plugins that might use the 'gp_locales' global.
		 *
		 * @return Locales  Object with all the Locales.
		 */
		public static function &instance() {

			if ( ! isset( $GLOBALS['translation_stats_locales'] ) ) {

				$locales = new Locales();

				foreach ( $locales->locales as $key => $locale ) {
					$locales->locales[ $key ] = new Locale( $locale );
				}

				$GLOBALS['translation_stats_locales'] = $locales;
			}

			return $GLOBALS['translation_stats_locales'];
		}


		/**
		 * Locales from WordPress.org extended with Translation Stats data.
		 *
		 * @since 1.1.0
		 *
		 * @return array  Array of Locales objects.
		 */
		public static function locales() {

			$instance = self::instance();

			$locales = $instance->locales;

			// Exclude 'en_US' from the Locales array.
			unset( $locales['en'] );

			// Get Available Translations (Locales with language packs).
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$translations = wp_get_available_translations();

			foreach ( $locales as $key => $locale ) {

				// If Locale don't have 'wp_locale', remove from the list.
				if ( ! isset( $locale->wp_locale ) ) {
					unset( $locales[ $key ] );
					continue;
				}

				// Add 'wporg_subdomain' property.
				$locales[ $key ]->wporg_subdomain = self::wporg_subdomain( $locale );

				// Add 'locale_slug' property.
				$locales[ $key ]->locale_slug = self::locale_slug( $locale );

				// Check if 'wp_locale' exist in the Available Translations.
				if ( array_key_exists( $locale->wp_locale, $translations ) ) {

					// Add 'translations' property.
					$locales[ $key ]->translations = $translations[ $locale->wp_locale ];

				}
			}

			return $locales;
		}


		/**
		 * Add WordPress.org Locale 'wporg_subdomain' to $locale.
		 * Defaults to Locale 'slug', or 'root_slug' if is variant.
		 * Custom subdomains use custom criteria from Translation Teams page (https://make.wordpress.org/polyglots/teams/) and 'locales.php' in https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php.
		 * Updated on 2019-04-17.
		 *
		 * Example: 'pt_BR' => 'br'.
		 *
		 * @since 1.1.0
		 *
		 * @param object $locale  Locale object.
		 *
		 * @return string         Returns WordPress Locale Subdomain.
		 */
		public static function wporg_subdomain( $locale ) {

			// Defaults to 'slug', or 'root_slug' if is variant.
			$wporg_subdomain = isset( $locale->root_slug ) ? $locale->root_slug : $locale->slug;

			/**
			 * The below Variants aren't included in the array because the variant slug is set in 'root_slug'.
			 * The subdomain of the Variants fallbacks to its parent subdomains.
			 *
			 * 'nl_NL_formal'   => 'nl',    // Variant. Fallback to parent subdomain.
			 * 'de_DE_formal'   => 'de',    // Variant. Fallback to parent subdomain.
			 * 'de_CH_informal' => 'de-ch', // Variant. Fallback to parent subdomain.
			 * 'pt_PT_ao90'     => 'pt',    // Variant. Fallback to parent subdomain.
			 */
			$wporg_custom_subdomains = array(
				'ba'         => null,
				'bre'        => 'bre',   // As in 'wp_locale'.
				'zh_CN'      => 'cn',    // As in 'country_code'.
				'zh_TW'      => 'tw',    // As in 'country_code'.
				'art_xemoji' => 'emoji', // Custom, doesn't exist in GlotPress.
				'ewe'        => null,
				'fo'         => 'fo',    // As in 'country_code'.
				'gn'         => null,
				'haw_US'     => null,
				'ckb'        => 'ku',    // As in 'lang_code_iso_639_1'.
				'lb_LU'      => 'ltz',   // Custom, doesn't exist in GlotPress.
				'xmf'        => null,
				'mn'         => 'khk',   // As in 'lang_code_iso_639_3', doesn't exist in GlotPress.
				'pt_BR'      => 'br',    // As in 'country_code'.
				'pa_IN'      => 'pan',   // As in 'lang_code_iso_639_2'.
				'rue'        => null,
				'sa_IN'      => 'sa',    // As in 'lang_code_iso_639_1'.
				'es_CL'      => 'cl',    // As in 'country_code'.
				'es_PE'      => 'pe',    // As in 'country_code'.
				'es_VE'      => 've',    // As in 'country_code'.
				'gsw'        => null,
				'wa'         => null,
			);

			// Check if 'wp_locale' exist in the custom subdomain criteria array.
			if ( array_key_exists( $locale->wp_locale, $wporg_custom_subdomains ) ) {
				// Set custom subdomain.
				$wporg_subdomain = $wporg_custom_subdomains[ $locale->wp_locale ];
			}

			return $wporg_subdomain;
		}


		/**
		 * Set Locale slug to support WordPress 2.x pseudo-variants.
		 *
		 * The 'slug' for a root Locale don't include the '/default' variant.
		 * The 'locale_slug' equals the 'slug' for variants ( e.g.: 'pt/ao90' ), adds '/default' to root Locales ( e.g.: 'pt/default' ).
		 *
		 * Example of Root Locale 'pt':
		 * 'slug': 'pt'.
		 * 'locale_slug': 'pt/default'.
		 *
		 * Example of Variant Locale 'pt/ao90':
		 * 'slug': 'pt/ao90'.
		 * 'locale_slug': 'pt/ao90'.
		 *
		 * @since 1.1.0
		 *
		 * @param object $locale  Locale object.
		 *
		 * @return string         Returns locale complete slug.
		 */
		public static function locale_slug( $locale ) {

			// Defaults to 'slug/default' if is a Root Locale, 'slug/variant' if is variant.
			$locale_slug = $locale->slug;
			if ( ! isset( $locale->root_slug ) ) {
				$locale_slug .= '/default';
			}

			return $locale_slug;
		}
	}
}
