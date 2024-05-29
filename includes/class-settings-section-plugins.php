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

			// Get Translation Stats general options.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );

			// Get Translation Stats plugins settings.
			$plugins_settings = isset( $options['plugins'] ) ? $options['plugins'] : array();

			// Configure projects table.
			$table_args = array(
				'table_prefix'          => 'plugins', // Set project table prefix.
				'show_author'           => true,      // Set to 'true' to show Author column.
				'show_slug_text_domain' => true,      // Set to 'true' to show Slug and Text Domain column.
			);

			/**
			 * Filters the arguments of the plugins table in the Translation Stats settings.
			 *
			 * @since 1.3.0
			 *
			 * @param array $table_args   Array of table parameters.
			 */
			$table_args = apply_filters( 'translation_stats_settings_plugins_table_args', $table_args );

			$show_author           = $table_args['show_author'];
			$show_slug_text_domain = $table_args['show_slug_text_domain'];
			$subprojects           = Translations_API::plugin_subprojects();

			// Get all installed plugins list.
			$plugins = get_plugins();

			?>
			<table id="tstats-table-plugins" class="tstats-plugin-list-table widefat plugins tablesorter">
				<?php

				// Prepare table header.
				$plugins_on_wporg_count = 0;
				$subprojects_count      = array();
				foreach ( $subprojects as $subproject ) {
					$subprojects_count[ $subproject['slug'] ] = 0;
				}

				foreach ( $plugins as $plugin_file => $plugin ) {
					// Plugin data.
					if ( ! Translations_API::plugin_on_wporg( $plugin_file ) ) {
						continue;
					}
					++$plugins_on_wporg_count;

					// Plugin data.
					$plugin_slug = Translations_API::plugin_metadata( $plugin_file, 'slug' );

					foreach ( $subprojects as $subproject ) {
						if ( isset( $plugins_settings[ $plugin_slug ][ $subproject['slug'] ] ) ) {
							++$subprojects_count[ $subproject['slug'] ];
						}
					}
				}

				// Set subprojects column header checked and data-indeterminate status.
				$subprojects_checkboxes_status = array();
				foreach ( $subprojects as $subproject ) {
					$subprojects_checkboxes_status[ $subproject['slug'] ]['checked']       = $subprojects_count[ $subproject['slug'] ] > 0 ? true : false;
					$subprojects_checkboxes_status[ $subproject['slug'] ]['indeterminate'] = $subprojects_count[ $subproject['slug'] ] > 0 && $subprojects_count[ $subproject['slug'] ] < $plugins_on_wporg_count ? true : false;
				}

				$subprojects_checked = 0;
				foreach ( $subprojects as $subproject ) {
					if ( $subprojects_checkboxes_status[ $subproject['slug'] ]['checked'] === true && $subprojects_checkboxes_status[ $subproject['slug'] ]['indeterminate'] === false ) {
						++$subprojects_checked;
					}
				}

				?>
				<thead>
					<tr>
						<td scope="col" id="cb" class="manage-column column-cb check-column plugin-select" data-sorter="false">
							<label class="screen-reader-text"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
							<input class="all_plugins" id="all_plugins" type="checkbox" value="true" <?php checked( $subprojects_checked > 0, true ); ?> data-indeterminate="<?php echo esc_attr( $subprojects_checked > 0 && $subprojects_checked < 4 ? 'true' : 'false' ); ?>" />
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
							<th scope="col" id="column-<?php echo esc_attr( $subproject['slug'] ); ?>" class="manage-column column-subproject" data-subproject="<?php echo esc_attr( $subproject['slug'] ); ?>" data-sorter="false">
								<div class="column-name">
									<?php echo esc_html( $subproject['name'] ); ?>
								</div>
								<div class="column-checkbox">
									<label class="screen-reader-text"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
									<input class="checkbox-subproject" id="subprojects_<?php echo esc_html( $subproject['slug'] ); ?>" type="checkbox" value="true" <?php checked( $subprojects_checkboxes_status[ $subproject['slug'] ]['checked'], true ); ?> data-indeterminate="<?php echo esc_attr( $subprojects_checkboxes_status[ $subproject['slug'] ]['indeterminate'] ? 'true' : 'false' ); ?>" />
								</div>
							</th>
							<?php

						}
						?>

					</tr>
				</thead>
				<tbody>
					<?php

					// Get the Translation Stats configured language.
					$translationstats_language = Utils::translation_language();

					// Get locale data.
					$locale = Translations_API::locale( $translationstats_language );

					// Table options.
					$table_prefix          = $table_args['table_prefix'];
					$show_author           = $table_args['show_author'];
					$show_slug_text_domain = $table_args['show_slug_text_domain'];

					foreach ( $plugins as $plugin_file => $plugin ) {

						// Plugin data.
						$plugin_slug        = Translations_API::plugin_metadata( $plugin_file, 'slug' );
						$plugin_url         = Translations_API::plugin_metadata( $plugin_file, 'url' );
						$plugin_text_domain = $plugin['TextDomain'];
						$subprojects        = Translations_API::plugin_subprojects();

						$row_id = $table_prefix . '_' . $plugin_slug;

						// Set CSS 'data-indeterminate' data attribute for partially enabled projects.
						$subprojects_count = 0;

						foreach ( $subprojects as $subproject ) {
							if ( ! empty( $plugins_settings[ $plugin_slug ][ $subproject['slug'] ] ) ) {
								++$subprojects_count;
							}
						}

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
							if ( empty( $plugins_settings[ $plugin_slug ]['enabled'] ) ) {
								$status  = 'inactive';
								$checked = false;
							} else {
								$status  = 'active';
								$checked = true;
							}
						}

						$field_name = TRANSLATION_STATS_WP_OPTION . '[plugins][' . $plugin_slug . '][enabled]';

						?>
						<tr class="<?php echo esc_html( $status ); ?>" data-plugin="<?php echo esc_attr( $plugin_slug ); ?>" data-subprojects="<?php echo esc_attr( strval( $subprojects_count ) ); ?>">
							<th scope="row" class="plugin-select">
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

							foreach ( $subprojects as $subproject ) {
								$field_name = TRANSLATION_STATS_WP_OPTION . '[plugins][' . $plugin_slug . '][' . $subproject['slug'] . ']';
								$checked    = empty( $plugins_settings[ $plugin_slug ][ $subproject['slug'] ] ) ? '' : true;

								?>
								<td class="plugin-subproject">
									<?php
									if ( ! $disabled ) {
										?>
										<label class="screen-reader-text"><?php esc_html_e( 'Select Subproject', 'translation-stats' ); ?></label>
										<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> class="checkbox-plugin-subproject" data-plugin="<?php echo esc_attr( $plugin_slug ); ?>" data-subproject="<?php echo esc_attr( $subproject['slug'] ); ?>" type="checkbox" value="true" />
										<?php
									}
									?>
								</td>
								<?php

							}

							?>
						</tr>
						<?php

					}

					?>
				</tbody>
			</table>
			<?php
		}
	}
}
