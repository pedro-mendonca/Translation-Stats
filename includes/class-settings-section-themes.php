<?php
/**
 * Class file for registering Translation Stats themes settings.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section_Themes' ) ) {

	/**
	 * Class Settings_Section_Themes.
	 */
	class Settings_Section_Themes extends Settings_Section {


		/**
		 * Data for the themes section.
		 *
		 * @since 1.5.0
		 *
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'id'          => 'themes', // Match the section ID from the settings pages of get_settings_pages().
				'title'       => __( 'Installed Themes', 'translation-stats' ),
				'description' => null,  // Added below with custom HTML.
				'page'        => TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX . 'themes',
			);
		}


		/**
		 * Callback function for section "Themes Settings".
		 *
		 * @since 1.5.0
		 *
		 * @return void
		 */
		public function render_custom_section() {
			?>

			<p class="search-box">
				<label class="screen-reader-text" for="themes-search-input"><?php esc_html_e( 'Search themes...', 'translation-stats' ); ?></label>
				<input id="themes-search-input" class="search" type="search" data-column="all" placeholder="<?php esc_attr_e( 'Search themes...', 'translation-stats' ); ?>">
				<button type="button" id="themes-search-reset" class="button reset"><?php esc_html_e( 'Clean', 'translation-stats' ); ?></button>
			</p>

			<p><?php esc_html_e( 'Select the themes you want to show the translation stats from the list of installed themes.', 'translation-stats' ); ?></p>
			<br>
			<?php

			$this->themes_list();
		}


		/**
		 * Render the themes settings table.
		 *
		 * @since 1.5.0
		 *
		 * @return void
		 */
		public function themes_list() {

			$options = Utils::get_options();

			$themes_settings = isset( $options['themes'] ) ? $options['themes'] : array();

			$themes = wp_get_themes();

			// Locale WordPress.org subdomain for the themes links ( e.g. 'pt.' ).
			$locale          = Translations_API::locale( Utils::translation_language() );
			$wporg_subdomain = isset( $locale->wporg_subdomain ) && 'en_US' !== $locale->wp_locale ? $locale->wporg_subdomain . '.' : '';

			?>
			<table id="tstats-table-themes" class="tstats-theme-list-table widefat plugins tablesorter">
				<thead>
					<tr>
						<td scope="col" class="manage-column column-cb check-column plugin-select" data-sorter="false">
							<?php $this->render_select_all( array_map( 'strval', array_keys( $themes ) ), $themes_settings ); ?>
						</td>
						<th scope="col" class="manage-column column-name column-primary">
							<?php esc_html_e( 'Theme', 'translation-stats' ); ?>
						</th>
						<th scope="col" class="manage-column column-author">
							<?php esc_html_e( 'Author', 'translation-stats' ); ?>
						</th>
						<th scope="col" class="manage-column column-slug-text-domain">
							<?php
							printf(
								/* translators: 1: Slug. 2: Text Domain. */
								esc_html__( '%1$s and %2$s', 'translation-stats' ),
								esc_html__( 'Slug', 'translation-stats' ),
								esc_html__( 'Text Domain', 'translation-stats' )
							);
							?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $themes as $theme_slug => $theme ) {
						$this->render_theme_row( (string) $theme_slug, $theme, ! empty( $themes_settings[ $theme_slug ]['enabled'] ), $wporg_subdomain );
					}
					?>
				</tbody>
			</table>
			<?php
		}


		/**
		 * Render the select all checkbox, checked if some themes on WordPress.org are enabled, and indeterminate if not all.
		 *
		 * @since 1.5.0
		 *
		 * @param array<int, string>                   $theme_slugs       Slugs of the installed themes.
		 * @param array<string, array{enabled?: bool}> $themes_settings   Themes settings.
		 *
		 * @return void
		 */
		private function render_select_all( $theme_slugs, $themes_settings ) {

			$themes_on_wporg = array_filter( $theme_slugs, array( Translations_API::class, 'theme_on_wporg' ) );

			$themes_enabled = count(
				array_filter(
					$themes_on_wporg,
					function ( $theme_slug ) use ( $themes_settings ) {
						return ! empty( $themes_settings[ $theme_slug ]['enabled'] );
					}
				)
			);

			?>
			<label class="screen-reader-text" for="all_themes"><?php esc_html_e( 'Select All', 'translation-stats' ); ?></label>
			<input class="all_themes" id="all_themes" type="checkbox" value="true" <?php checked( $themes_enabled > 0, true ); ?> data-indeterminate="<?php echo esc_attr( $themes_enabled > 0 && $themes_enabled < count( $themes_on_wporg ) ? 'true' : 'false' ); ?>" />
			<?php
		}


		/**
		 * Render a theme row, with its checkbox if the theme is on WordPress.org.
		 *
		 * @since 1.5.0
		 *
		 * @param string    $theme_slug        Theme slug.
		 * @param \WP_Theme $theme             Theme object.
		 * @param bool      $enabled           Whether the theme is enabled in the settings.
		 * @param string    $wporg_subdomain   Locale WordPress.org subdomain ( e.g. 'pt.' ).
		 *
		 * @return void
		 */
		private function render_theme_row( $theme_slug, $theme, $enabled, $wporg_subdomain ) {

			$theme_on_wporg    = Translations_API::theme_on_wporg( $theme_slug );
			$theme_text_domain = (string) $theme->get( 'TextDomain' );

			if ( ! $theme_on_wporg ) {
				$status = 'disabled';
			} else {
				$status = $enabled ? 'active' : 'inactive';
			}

			$field_name = TRANSLATION_STATS_WP_OPTION . '[themes][' . $theme_slug . '][enabled]';
			$row_id     = 'themes_' . $theme_slug;

			?>
			<tr class="<?php echo esc_attr( $status ); ?>" data-theme="<?php echo esc_attr( $theme_slug ); ?>">
				<th scope="row" class="plugin-select">
					<?php
					if ( $theme_on_wporg ) {
						?>
						<label class="screen-reader-text" for="<?php echo esc_attr( $row_id ); ?>"><?php esc_html_e( 'Select Theme', 'translation-stats' ); ?></label>
						<input name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $enabled, true ); ?> id="<?php echo esc_attr( $row_id ); ?>" class="checkbox-theme" type="checkbox" value="true" />
						<?php
					}
					?>
				</th>
				<td class="plugin-name">
					<?php
					$this->render_link(
						(string) $theme->get( 'Name' ),
						$theme_on_wporg ? 'https://' . $wporg_subdomain . 'wordpress.org/themes/' . $theme_slug . '/' : ''
					);
					?>
				</td>
				<td class="plugin-author">
					<?php $this->render_link( (string) $theme->get( 'Author' ), $theme_on_wporg ? (string) $theme->get( 'AuthorURI' ) : '' ); ?>
				</td>
				<td class="plugin-slug-text-domain">
					<div class="plugin-slug-text-domain-icon">
						<span class="dashicons <?php echo esc_attr( $theme_slug === $theme_text_domain ? 'dashicons-yes' : 'dashicons-no' ); ?>"></span>
					</div>
					<div class="plugin-slug-text-domain-message">
						<code><?php echo esc_html( $theme_slug ); ?></code><br>
						<?php
						if ( $theme_slug !== $theme_text_domain ) {
							?>
							<code class="code-error"><?php echo esc_html( $theme_text_domain ); ?></code><br>
							<?php
						}
						?>
					</div>
				</td>
			</tr>
			<?php
		}


		/**
		 * Render a text, linked to a URL if not empty.
		 *
		 * @since 1.5.0
		 *
		 * @param string $text   Text.
		 * @param string $url    URL, or empty for no link.
		 *
		 * @return void
		 */
		private function render_link( $text, $url ) {

			if ( '' === $url ) {
				echo esc_html( $text );
				return;
			}

			printf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $text ) );
		}
	}
}
