<?php
/**
 * Class file for registering Translation Stats Plugin Settings.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Plugins' ) ) {

	/**
	 * Class TStats_Settings_Plugins.
	 */
	class TStats_Settings_Plugins {


		/**
		 * Globals.
		 *
		 * @var object
		 */
		protected $tstats_globals;

		/**
		 * Translations API.
		 *
		 * @var object
		 */
		protected $tstats_translations_api;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

			// Instantiate Translation Stats Translate API.
			$this->tstats_translations_api = new TStats_Translations_API();

		}

		/**
		 * Callback function for section "Plugins Settings".
		 *
		 * @since 0.8.0
		 */
		public function tstats_settings__plugins__callback() {
			?>
			<p class="description">
				<?php
				esc_html_e( 'Select the plugins and subprojects you want to show the translation stats from the list of installed plugins.', 'translation-stats' );
				?>
			</p>
			<br>
			<?php

			$this->tstats_render_settings__plugins_list();

		}


		/**
		 * Render translation projects settings table.
		 *
		 * @since 0.8.0
		 * @since 0.9.6.2   Added table settings and separated methods for table header and rows.
		 */
		public function tstats_render_settings__plugins_list() {

			// Configure projects table.
			$table_args = array(
				'table_prefix'          => 'plugins', // Set project table prefix.
				'show_author'           => true,      // Set to 'true' to show Author column.
				'show_slug_text_domain' => true,      // Set to 'true' to show Slug and Text Domain column.
			);
			?>

			<table class="tstats-plugin-list-table widefat plugins">
				<thead>

					<?php
					$this->tstats_settings_projects_table_header( $table_args );
					?>

				</thead>
				<tbody>

					<?php
					// Get all installed plugins list.
					$all_plugins = get_plugins();

					foreach ( $all_plugins as $plugin_file => $plugin ) {
						$plugin['plugin_file'] = $plugin_file;
						$this->tstats_settings_projects_table_row( $table_args, $plugin );
					}
					?>

				</tbody>
			</table>
			<?php

		}


		/**
		 * Render translation projects settings table header.
		 *
		 * @since 0.9.6.2
		 *
		 * @param array $table_args   Array of table settings.
		 */
		public function tstats_settings_projects_table_header( $table_args ) {

			$options               = get_option( TSTATS_WP_OPTION );
			$show_author           = $table_args['show_author'];
			$show_slug_text_domain = $table_args['show_slug_text_domain'];
			$subprojects           = $this->tstats_translations_api->tstats_plugin_subprojects();

			?>
			<tr>
				<td scope="col" id="cb" class="manage-column column-cb check-column">
					<?php
					$input_id = TSTATS_WP_OPTION . '[all_plugins]';
					$checked  = empty( $options['all_plugins'] ) ? '' : true;
					?>
					<label class="screen-reader-text"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
					<input name="<?php echo esc_attr( $input_id ); ?>" <?php checked( $checked, true ); ?> class="all_plugins" id="all_plugins" type="checkbox" value="true"/>
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
					<th scope="col" id="column-<?php echo esc_attr( $subproject['slug'] ); ?>" class="manage-column column-<?php echo esc_attr( $subproject['slug'] ); ?> column-subproject">
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
		 * @since 0.9.6.2
		 *
		 * @param array $table_args   Array of table settings.
		 * @param array $plugin       Array of plugin data.
		 */
		public function tstats_settings_projects_table_row( $table_args, $plugin ) {

			// Get general options.
			$options         = get_option( TSTATS_WP_OPTION );
			$tstats_language = $this->tstats_globals->tstats_translation_language();
			$locale          = $this->tstats_translations_api->tstats_locale( $tstats_language );

			// Table options.
			$table_prefix          = $table_args['table_prefix'];
			$show_author           = $table_args['show_author'];
			$show_slug_text_domain = $table_args['show_slug_text_domain'];

			// Plugin data.
			$plugin_file        = $plugin['plugin_file'];
			$plugin_slug        = $this->tstats_translations_api->tstats_plugin_metadata( $plugin_file, 'slug' );
			$plugin_url         = $this->tstats_translations_api->tstats_plugin_metadata( $plugin_file, 'url' );
			$plugin_text_domain = $plugin['TextDomain'];
			$subprojects        = $this->tstats_translations_api->tstats_plugin_subprojects();

			$row_id = $table_prefix . '_' . $plugin_slug;

			if ( 'en_US' !== $tstats_language ) {
				// If current locale is not 'en_US', add Locale WP.org subdomain to plugin URL (e.g. https://pt.wordpress.org/plugins/translation-stats/ ).
				$wporg_subdomain = isset( $locale['wporg_subdomain'] ) ? $locale['wporg_subdomain'] . '.' : '';
				$plugin_url      = 'https://' . $wporg_subdomain . substr( $this->tstats_translations_api->tstats_plugin_metadata( $plugin_file, 'url' ), strlen( 'https://' ) );
			}
			$plugin_name   = $this->tstats_translations_api->tstats_plugin_on_wporg( $plugin_file ) ? '<a href="' . $plugin_url . '" target="_blank">' . $plugin['Name'] . '</a>' : $plugin['Name'];
			$plugin_author = $this->tstats_translations_api->tstats_plugin_on_wporg( $plugin_file ) && $plugin['AuthorURI'] ? '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['AuthorName'] . '</a>' : $plugin['AuthorName'];

			// Check if plugin exist on WordPress.org.
			if ( ! $this->tstats_translations_api->tstats_plugin_on_wporg( $plugin_file ) ) {
				$status   = 'disabled';
				$checked  = false;
				$disabled = true;
			} else {
				$disabled = false;
				if ( empty( $options[ $plugin_slug ] ['enabled'] ) ) {
					$status  = 'inactive';
					$checked = false;
				} else {
					$status  = 'active';
					$checked = true;
				}
			}

			$field_name = TSTATS_WP_OPTION . '[' . $plugin_slug . '][enabled]';
			?>

			<tr class="<?php echo esc_html( $status ); ?>">
				<th scope="row" class="check-column plugin-select">
					<label class="screen-reader-text"><?php esc_html_e( 'Select Plugin', 'translation-stats' ); ?></label>
					<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> id="<?php echo esc_attr( $row_id ); ?>" class="checkbox-plugin" type="checkbox" value="true"/>
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
					$field_name   = TSTATS_WP_OPTION . '[' . $plugin_slug . '][' . $subproject['slug'] . ']';
					$checked      = empty( $options[ $plugin_slug ] [ $subproject['slug'] ] ) ? '' : true;
					$plugin_class = ! $disabled ? $row_id : '';
					?>
					<td class="check-column plugin-subproject">
						<label class="screen-reader-text"><?php esc_html_e( 'Select Subproject', 'translation-stats' ); ?></label>
						<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> class="checkbox-subproject <?php echo esc_attr( $plugin_class ); ?>" type="checkbox" value="true" />
					</td>
					<?php
				}
				?>
			</tr>

			<?php
		}
	}
}
