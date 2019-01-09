<?php
/**
 * Class file for registering Translation Stats Plugin Settings.
 *
 * @package Translation Stats
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
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Translate API.
			$this->tstats_translate_api = new TStats_Translate_API();

		}

		/**
		 * Callback function for section "Plugins Settings".
		 */
		public function tstats_settings_plugins_callback() {
			?>
			<p class="description">
				<?php
				esc_html_e( 'Select the plugins and subprojects you want to show the translation stats from the list of installed plugins.', 'translation-stats' );
				?>
			</p>
			<br/>
			<?php

			$this->tstats_render_settings_plugins_list();

		}


		/**
		 * Render installed plugins settings table.
		 */
		public function tstats_render_settings_plugins_list() {

			$show_slug   = false;
			$options     = get_option( TSTATS_WP_OPTION );
			$subprojects = $this->tstats_translate_api->tstats_plugin_subprojects();
			?>
			<table class="wp-plugin-list-table widefat plugins">
				<thead>
					<tr>
						<td scope="col" id="cb" class="manage-column column-cb check-column">
							<?php
							$id      = TSTATS_WP_OPTION . '[all_plugins]';
							$checked = empty( $options['all_plugins'] ) ? '' : true;
							?>
							<label class="screen-reader-text"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
							<input name="<?php echo esc_attr( $id ); ?>" <?php checked( $checked, true ); ?> class="all_plugins" id="all_plugins" type="checkbox" value="true"/>
						</td>
						<th scope="col" id='column-name' class='manage-column column-name column-primary'>
							<?php esc_html_e( 'Plugin', 'translation-stats' ); ?>
						</th>
						<?php
						if ( $show_slug ) {
							?>
							<th scope="col" id='column-slug' class='manage-column column-slug'>
								<?php esc_html_e( 'Slug', 'translation-stats' ); ?>
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
				</thead>
				<tbody>
					<?php
					// Get all installed plugins list.
					$all_plugins = get_plugins();

					$plugin_item = '';
					?>
					<script>
					jQuery( document ).ready( function( $ ) {

						$( "#all_plugins" ).click( checkbox_all_plugins );

						function checkbox_all_plugins() {
							$( "tr.inactive" ).addClass( "active" ).removeClass( "inactive" );
							if ( $( "#all_plugins" ).is( ":checked" ) ) {
								$( "tr.inactive" ).addClass( "active" ).removeClass( "inactive" );
							} else {
								$( "tr.active" ).addClass( "inactive" ).removeClass( "active" );
							}
						}

						<?php
						foreach ( $all_plugins as $plugin ) {
							$plugin_item++;
							?>

							$( "#plugin_<?php echo esc_attr( $plugin_item ); ?>" ).click( enable_plugin_<?php echo esc_attr( $plugin_item ); ?> );

							function enable_plugin_<?php echo esc_attr( $plugin_item ); ?>() {
								$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).prop( "checked", this.checked);
								if ( $( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).parents( "tr" ).hasClass( "active" ) ) {
									$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).parents( "tr" ).addClass( "inactive" ).removeClass( "active" );
								} else {
									$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).parents( "tr" ).addClass( "active" ).removeClass( "inactive" );
								}
							}

							$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).on( "click", plugin_<?php echo esc_attr( $plugin_item ); ?>_count_subprojects );

							function plugin_<?php echo esc_attr( $plugin_item ); ?>_count_subprojects() {
								var n_<?php echo esc_attr( $plugin_item ); ?> = $( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>:checked" ).length;
								if ( n_<?php echo esc_attr( $plugin_item ); ?> === 0 ) {
									$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).parents( "tr" ).addClass( "inactive" ).removeClass( "active" );
									$( "input#plugin_<?php echo esc_attr( $plugin_item ); ?>" ).prop( "checked", false );
								} else {
									$( "input.plugin_<?php echo esc_attr( $plugin_item ); ?>" ).parents( "tr" ).addClass( "active" ).removeClass( "inactive" );
									$( "input#plugin_<?php echo esc_attr( $plugin_item ); ?>" ).prop( "checked", true );
								}
							}
							<?php
						}
						?>
					});
					</script>

					<?php
					$plugin_item = '';

					foreach ( $all_plugins as $key => $plugin ) {
						$plugin_slug = $this->tstats_translate_api->tstats_plugin_metadata( $key, 'slug' );
						$field_name  = TSTATS_WP_OPTION . '[' . $plugin_slug . '][enabled]';
						// $disabled    = empty( $plugin_slug ) ? true : '';
						// $checked     = empty( $options[ $plugin_slug ] ['enabled'] ) ? '' : true;
						// $status      = empty( $plugin_slug ) ? 'inactive' : 'active';
						// Set plugin status ( 'active', 'inactive' or 'disabled' )
						if ( empty( $plugin_slug ) ) {
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
						$plugin_item++;
						?>
						<tr class="<?php echo esc_html( $status ); ?>">
							<th scope="row" class="check-column plugin-select">
								<label class="screen-reader-text"><?php esc_html_e( 'Select Plugin', 'translation-stats' ); ?></label>
								<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> id="<?php echo esc_html( 'plugin_' . $plugin_item ); ?>" class="checkbox-plugin" type="checkbox" value="true"/>
							</th>
							<td class="plugin-title">
								<?php echo esc_html( $plugin['Name'] ); ?>
							</td>
							<?php
							if ( $show_slug ) {
								?>
								<td class="plugin-slug">
									<?php echo esc_html( $plugin_slug ); ?>
								</td>
								<?php
							}
							foreach ( $subprojects as $subproject ) {
								$field_name  = TSTATS_WP_OPTION . '[' . $plugin_slug . '][' . $subproject['slug'] . ']';
								$checked     = empty( $options[ $plugin_slug ] [ $subproject['slug'] ] ) ? '' : true;
								$plugin_item = ! $disabled ? 'plugin_' . $plugin_item : '';
								?>
								<td class="check-column plugin-subproject">
									<label class="screen-reader-text"><?php esc_html_e( 'Select Subproject', 'translation-stats' ); ?></label>
									<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?> class="checkbox-subproject <?php echo esc_attr( $plugin_item ); ?>" type="checkbox" value="true" />
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
