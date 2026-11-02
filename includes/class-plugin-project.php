<?php
/**
 * Class file for the Translation Stats plugin translation projects.
 *
 * @package Translation_Stats
 *
 * @since 2.0.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Plugin_Project' ) ) {

	/**
	 * Class Plugin_Project.
	 *
	 * A plugin translation project, with the Development and Stable subprojects, and their Readme.
	 */
	class Plugin_Project extends Project {


		/**
		 * Get the project type.
		 *
		 * @since 2.0.0
		 *
		 * @return string   Project type.
		 */
		public function get_type() {
			return 'plugins';
		}


		/**
		 * Get the plugin translation subprojects.
		 *
		 * @since 2.0.0
		 *
		 * @return array<int, array{slug: string, name: string}>   Subprojects.
		 */
		public function get_subprojects() {
			return Translations_API::plugin_subprojects();
		}
	}
}
