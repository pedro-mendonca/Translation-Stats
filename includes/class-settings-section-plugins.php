<?php
/**
 * Class file for registering Translation Stats plugins settings.
 *
 * @package Translation_Stats
 *
 * @since 1.2.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Plugins' ) ) {

	/**
	 * Class Settings_Section_Plugins.
	 */
	class Settings_Section_Plugins extends Settings_Section {


		/**
		 * Data for the plugins section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'plugins', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'Installed Plugins', 'translation-stats' ),
				'description' => null,  // Added below with custom HTML.
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'plugins',
			);
		}


		/**
		 * Callback function for section "Plugins Settings".
		 *
		 * @since 1.2.0
		 *
		 * @return callable|void
		 */
		public function render_custom_section() {
			?>

			<p class="search-box">
				<label class="screen-reader-text"><?php esc_html_e( 'Search plugins...', 'translation-stats' ); ?></label>
				<input id="plugins-search-input" class="search" type="search" data-column="all" placeholder="<?php esc_html_e( 'Search plugins...', 'translation-stats' ); ?>">
				<!-- targeted by the "filter_reset" option -->
				<button type="button" id="plugins-search-reset" class="button reset"><?php esc_html_e( 'Clean', 'translation-stats' ); ?></button>
			</p>

			<p>
				<?php
				printf(
					'%s %s',
					esc_html__( 'Select the plugins and subprojects you want to show the translation stats from the list of installed plugins.', 'translation-stats' ),
					sprintf(
						'<a href="%1$s" aria-label="%2$s">%3$s</a>',
						esc_url( add_query_arg( 'plugin_status', 'translation_stats', admin_url( 'plugins.php' ) ) ),
						esc_attr__( 'View selected plugins', 'translation-stats' ),
						esc_html__( 'View selected plugins', 'translation-stats' )
					)
				);
				?>
			</p>
			<br>
			<?php

			$this->plugins_list();
		}


		/**
		 * Render translation projects settings table.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function plugins_list() {

			// Configure projects table.
			$table_args = array(
				'table_prefix'          => 'plugins', // Set project table prefix.
				'show_author'           => true,      // Set to 'true' to show Author column.
				'show_slug_text_domain' => true,      // Set to 'true' to show Slug and Text Domain column.
			);
			?>

			<table id="tstats-table-plugins" class="tstats-plugin-list-table widefat plugins tablesorter">
				<thead>

					<?php
					$this->settings_projects_table_header( $table_args );
					?>

				</thead>
				<tbody>

					<?php
					// Get all installed plugins list.
					$plugins = get_plugins();

					// Count available plugins to check stats.
					$count = 0;

					// Count enabled plugins.
					$enabled = 0;

					// Count partially enabled plugins.
					$indeterminate = 0;

					foreach ( $plugins as $plugin_file => $plugin ) {

						$plugin['plugin_file'] = $plugin_file;

						$row_status = $this->settings_projects_table_row( $table_args, $plugin );
						//var_dump( $row_status );

						// Skip if row is disabled.
						if ( false === $row_status ) {
							continue;
						}

						// Do if row is enabled.
						++$count;

						// Check if project row is active and all 4 subprojects are enabled.
						if ( $row_status > 0 ) {
							++$enabled;

							// Check if project row is active with only some of the subprojects enabled.
							if ( $row_status < 4 ) {
								++$indeterminate;
							}
						}

					}

					// $all_plugins_checked       = $enabled ? true : false;
					$indeterminate = $enabled && ( $indeterminate || $count !== $enabled ) ? true : false;

					// Set all plugins checkbox status.
					?>
					<script>
					jQuery( document ).ready( function( $ ) {
						$( 'input#all_plugins' ).prop(
							{
								checked: <?php echo esc_html( $enabled ? 'true' : 'false' ); ?>,
								indeterminate: <?php echo esc_html( $indeterminate ? 'true' : 'false' ); ?>,
							}
						);
					} );
					</script>
				</tbody>
			</table>
			<?php
		}


		/**
		 * Render translation projects settings table header.
		 *
		 * @since 1.2.0
		 *
		 * @param array $table_args   Array of table settings.
		 *
		 * @return void
		 */
		public function settings_projects_table_header( $table_args ) {

			$show_author           = $table_args['show_author'];
			$show_slug_text_domain = $table_args['show_slug_text_domain'];
			$subprojects           = Translations_API::plugin_subprojects();

			?>
			<tr>
				<td scope="col" id="cb" class="manage-column column-cb check-column" data-sorter="false">
					<label class="screen-reader-text"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
					<input class="all_plugins" id="all_plugins" type="checkbox" value="true"/>
				</td>
				<th scope="col" id='column-name' class='manage-column column-name column-primary'>
					<?php esc_html_e( 'Plugin', 'translation-stats' ); ?>
				</th>
				<?php
				if ( $show_author ) {
					?>
					<th scope="col" id='column-author' class='manage-column column-author'>
						<?php esc_html_e( 'Author', 'translation-stats' ); ?>
					</th>
					<?php
				}
				if ( $show_slug_text_domain ) {
					?>
					<th scope="col" id='column-slug-text-domain' class='manage-column column-slug-text-domain'>
						<?php
						printf(
							/* translators: 1: Slug. 2: Text Domain. */
							esc_html__( '%1$s and %2$s', 'translation-stats' ),
							esc_html__( 'Slug', 'translation-stats' ),
							esc_html__( 'Text Domain', 'translation-stats' )
						);
						?>
					</th>
					<?php
				}
				foreach ( $subprojects as $subproject ) {
					?>
					<th scope="col" id="column-<?php echo esc_attr( $subproject['slug'] ); ?>" class="manage-column column-<?php echo esc_attr( $subproject['slug'] ); ?> column-subproject" data-sorter="false">
						<?php echo esc_html( $subproject['name'] ); ?>
					</th>
					<?php
				}
				?>
			</tr>
			<?php
		}


		/**
		 * Render translation projects settings table row.
		 *
		 * @since 1.2.0
		 *
		 * @param array $table_args        Array of table settings.
		 * @param array $plugin            Array of plugin data.
		 *
		 * @return int|false  Row status   Return number of active subprojects, or false if plugin doesn't exist on WP.org.
		 */
		public static function settings_projects_table_row( $table_args, $plugin ) {

			// Get general options.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );

			// Get the Translation Stats configured language.
			$translationstats_language = Utils::translation_language();

			// Get locale data.
			$locale = Translations_API::locale( $translationstats_language );

			// Table options.
			$table_prefix          = $table_args['table_prefix'];
			$show_author           = $table_args['show_author'];
			$show_slug_text_domain = $table_args['show_slug_text_domain'];

			// Plugin data.
			$plugin_file        = $plugin['plugin_file'];
			$plugin_slug        = Translations_API::plugin_metadata( $plugin_file, 'slug' );
			$plugin_url         = Translations_API::plugin_metadata( $plugin_file, 'url' );
			$plugin_text_domain = $plugin['TextDomain'];
			$subprojects        = Translations_API::plugin_subprojects();

			$row_id = $table_prefix . '_' . $plugin_slug;

			// Set CSS 'indeterminate' property for partially enabled projects.
			$subprojects_count = 0;
			foreach ( $subprojects as $subproject ) {
				if ( ! empty( $options['plugins'][ $plugin_slug ][ $subproject['slug'] ] ) ) {
					++$subprojects_count;
				}
			}
			$indeterminate = ( 0 !== $subprojects_count && $subprojects_count < count( $subprojects ) ) ? 'true' : 'false';
			?>
			<script>
			jQuery( document ).ready( function( $ ) {
				$( 'input#<?php echo esc_html( $row_id ); ?>' ).prop( 'indeterminate', <?php echo esc_html( $indeterminate ); ?> );
			} );
			</script>

			<?php
			if ( 'en_US' !== $translationstats_language ) {
				// If current locale is not 'en_US', add Locale WP.org subdomain to plugin URL (e.g. https://pt.wordpress.org/plugins/translation-stats/ ).
				$wporg_subdomain = isset( $locale->wporg_subdomain ) ? $locale->wporg_subdomain . '.' : '';
				$plugin_url      = 'https://' . $wporg_subdomain . substr( Translations_API::plugin_metadata( $plugin_file, 'url' ), strlen( 'https://' ) );
			}
			$plugin_name   = Translations_API::plugin_on_wporg( $plugin_file ) ? '<a href="' . $plugin_url . '" target="_blank">' . $plugin['Name'] . '</a>' : $plugin['Name'];
			$plugin_author = Translations_API::plugin_on_wporg( $plugin_file ) && $plugin['AuthorURI'] ? '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['AuthorName'] . '</a>' : $plugin['AuthorName'];

			// Check if plugin exist on WordPress.org.
			if ( ! Translations_API::plugin_on_wporg( $plugin_file ) ) {
				$status   = 'disabled';
				$checked  = false;
				$disabled = true;
			} else {
				$disabled = false;
				if ( empty( $options['plugins'][ $plugin_slug ]['enabled'] ) ) {
					$status  = 'inactive';
					$checked = false;
				} else {
					$status  = 'active';
					$checked = true;
				}
			}

			$field_name = TRANSLATION_STATS_WP_OPTION . '[plugins][' . $plugin_slug . '][enabled]';
			?>

			<tr class="<?php echo esc_html( $status ); ?>">
				<th scope="row" class="check-column plugin-select">
					<?php
					if ( ! $disabled ) {
						?>
						<label class="screen-reader-text"><?php esc_html_e( 'Select Plugin', 'translation-stats' ); ?></label>
						<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> id="<?php echo esc_attr( $row_id ); ?>" class="checkbox-plugin" type="checkbox" value="true"/>
						<?php
					}
					?>
				</th>
				<td class="plugin-name">
					<?php echo wp_kses_post( $plugin_name ); ?>
				</td>
				<?php
				if ( $show_author ) {
					?>
					<td class="plugin-author">
						<?php echo wp_kses_post( $plugin_author ); ?>
					</td>
					<?php
				}
				if ( $show_slug_text_domain ) {
					$plugin_data = array(
						'slug'       => $plugin_slug,
						'textdomain' => $plugin_text_domain,
					);
					// Check if Slug is equal to Text Domain.
					$slug_and_text_domain = ( $plugin_slug === $plugin_text_domain ) ? true : false;
					if ( $slug_and_text_domain ) {
						$dashicon = 'dashicons-yes';
						unset( $plugin_data['textdomain'] );
					} else {
						$dashicon = 'dashicons-no';
					}
					$dashicon = ( $plugin_slug === $plugin_text_domain ) ? 'dashicons-yes' : 'dashicons-no';
					?>
					<td class="plugin-slug-text-domain">
						<div class="plugin-slug-text-domain-icon">
							<span class="dashicons <?php echo esc_attr( $dashicon ); ?>"></span>
						</div>
						<div class="plugin-slug-text-domain-message">
							<?php
							foreach ( $plugin_data as $key => $item ) {
								$code_class = 'textdomain' === $key ? 'code-error' : '';
								?>
								<code class="<?php echo esc_attr( $code_class ); ?>"><?php echo esc_html( $item ); ?></code><br>
								<?php
							}
							?>
						</div>
					</td>
					<?php
				}

				if ( $disabled ) {
					$row_status = false;
				} else {
					$row_status = 0;
				}

				foreach ( $subprojects as $subproject ) {
					$field_name   = TRANSLATION_STATS_WP_OPTION . '[plugins][' . $plugin_slug . '][' . $subproject['slug'] . ']';
					$checked      = empty( $options['plugins'][ $plugin_slug ][ $subproject['slug'] ] ) ? '' : true;
					$plugin_class = ! $disabled ? $row_id : '';
					?>
					<td class="check-column plugin-subproject">
						<?php
						if ( ! $disabled ) {
							?>
							<label class="screen-reader-text"><?php esc_html_e( 'Select Subproject', 'translation-stats' ); ?></label>
							<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> class="checkbox-subproject <?php echo esc_attr( $plugin_class ); ?>" type="checkbox" value="true" />
							<?php
						}
						?>
					</td>
					<?php

					// If subproject is selected, increase $row_status.
					if ( $checked ) {
						++$row_status;
					}
				}
				?>
			</tr>

			<?php

			// Return row status.
			return $row_status;
		}
	}
}
