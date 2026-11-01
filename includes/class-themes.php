<?php
/**
 * Class file for the Translation Stats themes.
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

if ( ! class_exists( __NAMESPACE__ . '\Themes' ) ) {

	/**
	 * Class Themes.
	 *
	 * Renders the themes translation stats.
	 */
	class Themes {


		/**
		 * Load the theme widget content stats, with the stats bar of the theme project.
		 *
		 * @since 1.5.0
		 *
		 * @param string $theme_slug     Theme slug.
		 * @param Locale $locale         Locale object.
		 * @param bool   $force_update   True: Force get new stats. False: Use transients.
		 *
		 * @return true|WP_Error   True if rendered, or WP_Error if translate.WordPress.org is unavailable, without rendering.
		 */
		public function theme_widget_content_stats( $theme_slug, $locale, $force_update ) {

			$project = new Theme_Project( $theme_slug );

			$project_stats = $project->stats( $locale, $force_update );

			if ( is_wp_error( $project_stats ) ) {
				return $project_stats;
			}

			?>
			<div class="translation-stats-content-stats widget-inside" data-slug="<?php echo esc_attr( $theme_slug ); ?>">
				<?php
				foreach ( $project->get_subprojects() as $subproject ) {
					Stats_Bar::render( $project, $project_stats[ $subproject['slug'] ], $locale, $subproject['name'], $subproject['slug'], 'div.translation-stats-content-stats' );
				}
				?>
			</div>
			<?php

			return true;
		}
	}
}
