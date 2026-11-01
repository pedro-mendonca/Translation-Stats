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
		 * Get the theme translation subprojects: only the project itself.
		 *
		 * @since 1.5.0
		 *
		 * @return array<int, array{slug: string, name: string}>   Subprojects.
		 */
		public function get_subprojects() {
			return array(
				array(
					'slug' => '',
					'name' => _x( 'Theme', 'Project type', 'translation-stats' ),
				),
			);
		}


		/**
		 * Get the theme key for its transients, to not conflict with a plugin with the same slug.
		 *
		 * @since 1.5.0
		 *
		 * @return string   Transients key.
		 */
		protected function transients_key() {
			return 'theme_' . $this->get_slug();
		}
	}
}
