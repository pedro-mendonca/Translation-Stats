<?php
/**
 * Class file for the Translation Stats translation projects.
 *
 * @package Translation_Stats
 *
 * @since 1.5.0
 */

namespace Translation_Stats;

use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Project' ) ) {

	/**
	 * Class Project.
	 *
	 * A translation project on translate.WordPress.org, with its subprojects and translation stats.
	 */
	abstract class Project {


		/**
		 * Project slug on translate.WordPress.org ( e.g. 'translation-stats' ).
		 *
		 * @var string
		 */
		protected $slug;


		/**
		 * Constructor.
		 *
		 * @param string $slug   Project slug.
		 */
		public function __construct( $slug ) {
			$this->slug = $slug;
		}


		/**
		 * Get the project type, as in the translate.WordPress.org URL.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Project type ( 'plugins' or 'themes' ).
		 */
		abstract public function get_type();


		/**
		 * Get the translation subprojects, with 'slug' and 'name'. A subproject with an empty slug is the project itself.
		 *
		 * @since 1.5.0
		 *
		 * @return array<int, array{slug: string, name: string}>   Subprojects.
		 */
		abstract public function get_subprojects();


		/**
		 * Get the project slug.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Project slug.
		 */
		public function get_slug() {
			return $this->slug;
		}


		/**
		 * Get the prefix of the project transients, for each project type.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Transients prefix.
		 */
		public function transients_prefix() {
			return TRANSLATION_STATS_TRANSIENTS_PLUGINS_PREFIX;
		}


		/**
		 * Get the path of the project, or of one of its subprojects, on translate.WordPress.org.
		 *
		 * @since 1.5.0
		 *
		 * @param string $subproject_slug   Subproject slug, or empty for the project.
		 *
		 * @return string   Path ( e.g. 'translation-stats/stable' ).
		 */
		public function get_path( $subproject_slug = '' ) {
			return '' === $subproject_slug ? $this->get_slug() : $this->get_slug() . '/' . $subproject_slug;
		}


		/**
		 * Get the translate.WordPress.org URL of a subproject translation for a Locale.
		 *
		 * @since 1.5.0
		 *
		 * @param string $subproject_slug   Subproject slug, or empty for the project.
		 * @param Locale $locale            Locale object.
		 *
		 * @return string   Translation URL.
		 */
		public function translate_url( $subproject_slug, $locale ) {
			return Translations_API::translate_url( $this->get_type(), false ) . $this->get_path( $subproject_slug ) . '/' . $locale->locale_slug;
		}


		/**
		 * Get the project, or one of its subprojects, from translate.WordPress.org API.
		 *
		 * @since 1.5.0
		 *
		 * @param string $subproject_slug   Subproject slug, or empty for the project.
		 *
		 * @return array|WP_Error   The translate.WordPress.org API response.
		 */
		public function api_get( $subproject_slug = '' ) {
			return wp_remote_get( Translations_API::translate_url( $this->get_type(), true ) . $this->get_path( $subproject_slug ) );
		}


		/**
		 * Check if the translation project exists on translate.WordPress.org, cached in a transient.
		 * If translate.WordPress.org is unavailable, assume the project exists and don't cache the result.
		 *
		 * @since 1.5.0   Moved from Translations_API::plugin_project_on_translate_wporg().
		 *
		 * @return bool   True if the translation project exists on translate.WordPress.org.
		 */
		public function on_translate_wporg() {

			$transient = $this->transients_prefix() . $this->get_slug();

			$on_wporg = get_transient( $transient );

			if ( false === $on_wporg ) {

				$response = $this->api_get();

				if ( Translations_API::is_api_unavailable( $response ) ) {
					// Unknown, the stats request will show the error.
					return true;
				}

				$on_wporg = 200 === wp_remote_retrieve_response_code( $response );

				set_transient( $transient, $on_wporg, Utils::transients_expiration() );
			}

			// Cached values are strings.
			return (bool) $on_wporg;
		}


		/**
		 * Get the transient name of the subproject stats for a Locale.
		 *
		 * @since 1.5.0   Moved from Plugins::plugin_subproject_stats_transient().
		 *
		 * @param Locale $locale            Locale object.
		 * @param string $subproject_slug   Subproject slug, or empty for the project.
		 *
		 * @return string   Transient name.
		 */
		public function subproject_stats_transient( $locale, $subproject_slug ) {

			$key = '' === $subproject_slug ? $this->get_slug() : $this->get_slug() . '_' . $subproject_slug;

			return $this->transients_prefix() . $key . '_' . $locale->wp_locale;
		}


		/**
		 * Get the subproject stats for a Locale, cached in a transient.
		 *
		 * @since 1.5.0   Moved from Plugins::plugin_subproject_stats().
		 *
		 * @param Locale $locale            Locale object.
		 * @param string $subproject_slug   Subproject slug, or empty for the project.
		 * @param bool   $force_update      True: Force get new stats. False: Use transients.
		 *
		 * @return object|false|WP_Error   Translation set stats if exist, false if not, or WP_Error if translate.WordPress.org is unavailable.
		 */
		public function subproject_stats( $locale, $subproject_slug, $force_update ) {

			$transient = $this->subproject_stats_transient( $locale, $subproject_slug );

			// Check for force update setting.
			$stats = true === $force_update ? false : get_transient( $transient );

			// A cached subproject without stats is an empty string.
			if ( false !== $stats ) {
				return is_object( $stats ) ? $stats : false;
			}

			$response = $this->api_get( $subproject_slug );

			if ( Translations_API::is_api_unavailable( $response ) ) {

				// Don't cache, to get the stats again on the next request.
				return new WP_Error(
					'translation_stats_api_unavailable',
					__( 'The translation stats couldn\'t be loaded from translate.wordpress.org. Please try again later.', 'translation-stats' ),
					array( 'status' => 503 )
				);

			}

			// Subproject not found (Error 404) - Project is not properly prepared for localization.
			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {

				$body = json_decode( wp_remote_retrieve_body( $response ) );

				if ( is_object( $body ) && ! empty( $body->translation_sets ) ) {

					foreach ( $body->translation_sets as $translation_set ) {

						// Check for exact match of locale/variant (translation set variant/slug).
						if ( $translation_set->locale . '/' . $translation_set->slug === $locale->locale_slug ) {
							$stats = $translation_set;
							break;
						}
					}
				}
			}

			set_transient( $transient, $stats, Utils::transients_expiration() );

			return $stats;
		}


		/**
		 * Get the stats of all the subprojects for a Locale.
		 *
		 * @since 1.5.0
		 *
		 * @param Locale $locale         Locale object.
		 * @param bool   $force_update   True: Force get new stats. False: Use transients.
		 *
		 * @return array<string, object|false>|WP_Error   Stats of each subproject slug, or WP_Error if translate.WordPress.org is unavailable.
		 */
		public function stats( $locale, $force_update ) {

			$stats = array();

			foreach ( $this->get_subprojects() as $subproject ) {

				$subproject_stats = $this->subproject_stats( $locale, $subproject['slug'], $force_update );

				if ( is_wp_error( $subproject_stats ) ) {
					return $subproject_stats;
				}

				$stats[ $subproject['slug'] ] = $subproject_stats;
			}

			return $stats;
		}


		/**
		 * Check if the stats of all the subprojects are cached for a Locale.
		 *
		 * @since 1.5.0   Moved from Plugins::plugin_stats_cached().
		 *
		 * @param Locale $locale   Locale object.
		 *
		 * @return bool   True if all the subprojects stats are cached.
		 */
		public function stats_cached( $locale ) {

			foreach ( $this->get_subprojects() as $subproject ) {
				if ( false === get_transient( $this->subproject_stats_transient( $locale, $subproject['slug'] ) ) ) {
					return false;
				}
			}

			return true;
		}
	}
}
