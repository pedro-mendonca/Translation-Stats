<?php
/**
 * Primary class file for the Translation Stats translate.wordpress.org API.
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Translate_API' ) ) {

	/**
	 * Class TStats_Translate_API.
	 */
	class TStats_Translate_API {

		/**
		 * Check if plugin is on WordPress.org by checking if ID (from Plugin wp.org info) exists in 'response' or 'no_update' in 'update_plugins' transient.
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
		 * $plugin_metadata = $this->tstats_translate_api->tstats_plugin_metadata( $plugin_file, 'metadata' ) (e.g. 'slug').
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
		 * Get data from translate.WordPress.org API.
		 *
		 * @param string $url       URL to get the data from.
		 * @return string $api_get  Returns the response from translate.WordPress.org API URL.
		 */
		public function tstats_translate_api_get( $url ) {
			$api_get = wp_remote_get( 'https://translate.wordpress.org/api/projects/wp-plugins/' . $url );
			return $api_get;
		}


		/**
		 * Check if translation project exist without /subproject slug (e.g. https://translate.wordpress.org/api/projects/wp-plugins/wp-seo-acf-content-analysis).
		 *
		 * @param string $project_slug  Plugin Slug (e.g. 'plugin-slug').
		 * @return string $on_wporg     Returns 'true' if the translation project exist on WordPress.org.
		 */
		public function tstats_plugin_project_on_translate_wporg( $project_slug ) {
			// Check project transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translate_api_get( $project_slug );
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
		 * @param string $project_slug     Plugin Slug (e.g. 'plugin-slug').
		 * @param string $subproject_slug  Plugin Subproject Slug (e.g. 'dev', 'dev-readme', 'stable', 'stable-readme').
		 * @return string                  Returns 'true' if the translation subproject exist on WordPress.org.
		 */
		public function tstats_plugin_subproject_on_translate_wporg( $project_slug, $subproject_slug ) {
			// Check subproject transients.
			$on_wporg = get_transient( TSTATS_TRANSIENTS_PREFIX . $project_slug . '_' . $subproject_slug );
			if ( false === $on_wporg ) {
				$json = $this->tstats_translate_api_get( $project_slug . '/' . $subproject_slug );
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

	}

}
