<?php
/**
 * Class file for the Translation Stats stats widget parts.
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

if ( ! class_exists( __NAMESPACE__ . '\Stats_Widget' ) ) {

	/**
	 * Class Stats_Widget.
	 *
	 * Renders the parts of the translation stats widget shared by plugins and themes.
	 */
	class Stats_Widget {


		/**
		 * Render the widget update button, with the update status announced to screen readers.
		 *
		 * @since 0.9.4   Plugins::plugin_update_button().
		 * @since 1.5.0   Moved to Stats_Widget, for plugins and themes.
		 *
		 * @return void
		 */
		public static function render_update_button() {
			?>

			<span class="tstats-update-link">
				<span class="tstats-update-status" aria-live="polite"></span>
				<button class="handlediv button-link tstats-update-button" type="button" aria-expanded="true">
					<span class="dashicons dashicons-update"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Update', 'translation-stats' ); ?></span>
				</button>
			</span>

			<?php
		}


		/**
		 * Render the widget status notice, hidden to show the errors, below the stats to not move them when shown.
		 *
		 * @since 1.5.0
		 *
		 * @param bool $cached   Whether the stats are cached, or are loaded with the REST API.
		 *
		 * @return void
		 */
		public static function render_status_notice( $cached ) {

			$additional_classes = array(
				'translation-stats-loading',
				'hidden',
			);

			if ( $cached ) {
				$additional_classes[] = 'translation-stats-cached';
			}

			new Admin_Notice(
				array(
					'type'               => 'warning',
					'notice-alt'         => true,
					'additional-classes' => $additional_classes,
					'update-icon'        => true,
					'force_show'         => true,
					'message'            => '',
				)
			);
		}


		/**
		 * Render an empty stats placeholder, with a bar for each subproject, while loading the stats.
		 *
		 * @since 1.5.0
		 *
		 * @param array<int, array{slug: string, name: string}> $subprojects   Subprojects to show, with an empty slug for the project itself.
		 *
		 * @return void
		 */
		public static function render_placeholder( $subprojects ) {
			?>
			<div class="translation-stats-content-stats widget-inside translation-stats-placeholder" aria-hidden="true">
				<?php
				foreach ( $subprojects as $subproject ) {
					?>
					<div class="content__subproject <?php echo esc_attr( '' === $subproject['slug'] ? 'project' : $subproject['slug'] ); ?>">
						<a class="placeholder">
							<div class="subproject">
								<div class="subproject-bar">
									<span class="subproject-bar__name"><?php echo esc_html( $subproject['name'] ); ?></span>
								</div>
							</div>
						</a>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
}
