<?php
/**
 * Class file for the Translation Stats theme translation projects.
 *
 * @package Translation_Stats
 *
 * @since 1.5.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Theme_Project' ) ) {

	/**
	 * Class Theme_Project.
	 *
	 * A theme translation project, without subprojects: the stats are in the project itself.
	 */
	class Theme_Project extends Project {


		/**
		 * Get the project type.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Project type.
		 */
		public function get_type() {
			return 'themes';
		}


		/**
		 * Get the theme translation subprojects: only the project itself, named as the theme.
		 *
		 * @since 1.5.0
		 *
		 * @return array<int, array{slug: string, name: string}>   Subprojects.
		 */
		public function get_subprojects() {

			$theme = wp_get_theme( $this->get_slug() );

			return array(
				array(
					'slug' => '',
					'name' => $theme->exists() ? (string) $theme->get( 'Name' ) : $this->get_slug(),
				),
			);
		}


		/**
		 * Get the prefix of the theme transients, to not conflict with a plugin with the same slug.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Transients prefix.
		 */
		public function transients_prefix() {
			return TRANSLATION_STATS_TRANSIENTS_THEMES_PREFIX;
		}
	}
}
