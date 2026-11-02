<?php
/**
 * Class file for the Translation Stats stats bar.
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

if ( ! class_exists( __NAMESPACE__ . '\Stats_Bar' ) ) {

	/**
	 * Class Stats_Bar.
	 *
	 * Renders the translation stats bar of a project subproject, for plugins and themes.
	 */
	class Stats_Bar {


		/**
		 * Render the stats bar of a subproject, or of the project itself without subprojects.
		 *
		 * @since 0.8.0   Plugins::render_stats_bar().
		 * @since 2.0.0   Moved to Stats_Bar, for all the project types.
		 *
		 * @param Project      $project            Translation project.
		 * @param object|false $subproject_stats   Subproject stats object, or false if the subproject doesn't exist.
		 * @param Locale       $locale             Locale object.
		 * @param string       $subproject         Translation subproject name ( e.g. 'Stable' ).
		 * @param string       $subproject_slug    Translation subproject slug ( e.g. 'stable' ), or empty for the project.
		 * @param string       $container          CSS selector of the element with the project 'data-slug' attribute, to set the bar width ( e.g. 'tr' ).
		 *
		 * @return void
		 */
		public static function render( $project, $subproject_stats, $locale, $subproject, $subproject_slug, $container ) {
			/*
			 * Check if subproject is an object.
			 *
			 * Example of the object properties:
			 * [id] => 416518
			 * [name] => Portuguese (Portugal)
			 * [slug] => default | ao90 | informal
			 * [project_id] => 3333
			 * [locale] => pt
			 * [current_count] => 136
			 * [untranslated_count] => 0
			 * [waiting_count] => 0
			 * [fuzzy_count] => 0
			 * [all_count] => 136
			 * [warnings_count] => 0
			 * [percent_translated] => 100
			 * [wp_locale] => pt_PT
			 * [last_modified] => 2018-10-11 10:05:30
			 */
			$subproject_exist = is_object( $subproject_stats ) ? true : false;

			$class = $subproject_exist ? 'enabled' : 'disabled';
			$href  = $subproject_exist ? 'href="' . esc_url( $project->translate_url( $subproject_slug, $locale ) ) . '"' : '';

			// CSS class of the subproject, or of the project itself without subproject.
			$subproject_class = '' === $subproject_slug ? 'project' : $subproject_slug;

			// Percent translated, current count and all count.
			$percent_translated = self::get_stat( $subproject_stats, 'percent_translated' );
			$current_count      = self::get_stat( $subproject_stats, 'current_count' );
			$all_count          = self::get_stat( $subproject_stats, 'all_count' );

			?>
			<div class="content__subproject <?php echo esc_attr( $subproject_class ); ?>">
				<a class="<?php echo esc_attr( $class ); ?>" target="_blank" <?php echo wp_kses_post( $href ); ?>>
				<?php
				if ( $subproject_exist ) {

					?>
					<style>
						<?php echo esc_html( $container ); ?>[data-slug="<?php echo esc_attr( $project->get_slug() ); ?>"] div.subproject.<?php echo esc_attr( $subproject_class ); ?> {
							width: <?php echo esc_attr( (string) $percent_translated ); ?>%;
						}
					</style>
					<div class="subproject <?php echo esc_attr( 'percent' . 10 * floor( $percent_translated / 10 ) . ' ' . $subproject_class ); ?>">
						<div class="subproject-bar">
							<span class="subproject-bar__percentage"><?php echo esc_html( (string) $percent_translated ); ?>%</span>
							<span class="subproject-bar__name"><?php echo esc_html( $subproject ); ?></span>
							<span class="subproject-bar__count">
								<?php
								printf(
									// translators: 1: Current count. 2: All count.
									esc_html__( '(%1$d/%2$d)', 'translation-stats' ),
									esc_html( (string) $current_count ),
									esc_html( (string) $all_count )
								);
								?>
							</span>
						</div>
					</div>
					<?php

				} else {

					?>
					<div class="subproject">
						<div class="subproject-bar">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: 1: Name of subproject. 2: Error message. */
									__( '%1$s: %2$s', 'translation-stats' ),
									$subproject,
									'<strong>' . __( 'Not found', 'translation-stats' ) . '</strong>'
								)
							);
							?>
						</div>
					</div>
					<?php

				}
				?>
				</a>
			</div>
			<?php
		}


		/**
		 * Get a numeric stat of the subproject stats object.
		 *
		 * @since 2.0.0
		 *
		 * @param object|false $subproject_stats   Subproject stats object, or false if the subproject doesn't exist.
		 * @param string       $stat               Stat name ( e.g. 'percent_translated' ).
		 *
		 * @return int   The stat value, or 0 if not available.
		 */
		private static function get_stat( $subproject_stats, $stat ) {
			return is_object( $subproject_stats ) && isset( $subproject_stats->$stat ) && is_numeric( $subproject_stats->$stat ) ? (int) $subproject_stats->$stat : 0;
		}
	}
}
