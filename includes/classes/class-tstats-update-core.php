<?php
/**
 * Class file for the Translation Stats Update Core.
 *
 * @package Translation Stats
 *
 * @since 0.9.5
 */

use Gettext\Translations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Update_Core' ) ) {

	/**
	 * Class TStats_Update_Core.
	 */
	class TStats_Update_Core {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

			// Instantiate Translation Stats Notices.
			$this->tstats_notices = new TStats_Notices();

			// Instantiate Translation Stats Translations API.
			$this->tstats_translations_api = new TStats_Translations_API();

			// Instantiate Translation Stats Gettext.
			$this->tstats_gettext = new TStats_Gettext();

			// Add Translation Stats WordPress info.
			add_action( 'core_upgrade_preamble', array( $this, 'tstats_update_wordpress_translation_info' ) );

			// Load WordPress translation updater.
			add_action( 'wp_ajax_tstats_update_core_content_load', array( $this, 'tstats_update_core_content_load' ) );

			// Add notice to Dashboard.
			// add_action( 'admin_notices', array( $this, 'tstats_dashboard_wordpress_translation_info' ) ); // phpcs:ignore - Recheck if needed.

			// Filter 'update_core' transient to prevent update of previous WordPress version language pack.
			add_filter( 'pre_set_site_transient_update_core', array( $this, 'tstats_remove_previous_wp_translation' ) );

		}


		/**
		 * Show WordPress core translation info message on Dashboard.
		 *
		 * @since 0.9.5
		 */
		public function tstats_dashboard_wordpress_translation_info() {

			// Get current screen.
			$current_screen = get_current_screen();
			// Check for Dashboard page.
			if ( 'dashboard' !== $current_screen->id ) {
				return;
			}

			// End if Translation Stats language is 'en_US'.
			if ( $this->tstats_globals->tstats_language_is_english() ) {
				return;
			}

			$notice_args = array(
				'inline'      => false,
				'dismissible' => true,
			);
			$this->tstats_wordpress_translation_info_message( $notice_args );

		}


		/**
		 * Add WordPress core info and update button on the Updates page bottom.
		 *
		 * @since 0.9.5
		 */
		public function tstats_update_wordpress_translation_info() {

			// End if Translation Stats language is 'en_US'.
			if ( $this->tstats_globals->tstats_language_is_english() ) {
				// Do nothing.
				return;
			}

			// Check if user can update languages.
			if ( ! current_user_can( 'update_languages' ) ) {
				// Do nothing.
				return;
			}

			// Get core update transient data.
			$update_core = get_site_transient( 'update_core' );
			// Check if there is a core translation available to autoupdate.
			if ( isset( $update_core->translations[0]['autoupdate'] ) ) {
				// Do nothing.
				return;
			}
			?>

			<div class="translation-stats-update-core-info">

				<?php
				// Show force update WordPress translation button.
				$form_action = 'update-core.php?tstats=force_update_core';
				?>

				<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-wordpress-translation" class="upgrade">
					<?php wp_nonce_field( 'upgrade-wordpress-translation' ); ?>
					<p>
						<input type="submit" name="force_update_core" class="button button-primary" value="<?php esc_attr_e( 'Update WordPress Translation', 'translation-stats' ); ?>">
					</p>
				</form>

				<?php
				// Show the Translation Stats admin notice for WordPress core translation status.
				$notice_args = array();
				$this->tstats_wordpress_translation_info_message( $notice_args );
				?>

			</div>

			<?php

			if ( ! isset( $_GET['tstats'] ) ) { // phpcs:ignore
				return;
			}

			$url_var = $_GET['tstats']; // phpcs:ignore

			// Check for correct URL parameter.
			if ( 'force_update_core' !== $url_var ) {
				return;
			}

			$this->tstats_update_core_content();

		}


		/**
		 * WordPress core translation info message.
		 *
		 * @since 0.9.5
		 *
		 * @param array $notice_args  Arguments for admin notice.
		 */
		public function tstats_wordpress_translation_info_message( $notice_args ) {

			// Get Translation Stats Locale data.
			$tstats_language = $this->tstats_globals->tstats_translation_language();
			$locale          = $this->tstats_translations_api->tstats_locale( $tstats_language );

			// Get WordPress core version info.
			$wp_version = $this->tstats_translations_api->tstats_wordpress_version();

			// Get available translations transient data.
			$available_translations = get_site_transient( 'available_translations' );
			if ( empty( $available_translations ) ) {
				require_once ABSPATH . 'wp-admin/includes/translation-install.php';
				wp_get_available_translations();
				$available_translations = get_site_transient( 'available_translations' );
			}

			// Check for translations update in core update data.
			if ( isset( $available_translations[ $locale['wp_locale'] ]['updated'] ) ) {
				// Get language pack creation date.
				$translations_date = $available_translations[ $locale['wp_locale'] ]['updated'];
			}

			$admin_notice_type           = 'info';
			$admin_notice_message_status = sprintf(
				wp_kses_post(
					/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
					__( 'The translation of WordPress %1$s for %2$s was updated on %3$s.', 'translation-stats' )
				),
				'<strong>' . esc_html( $wp_version['name'] ) . '</strong>',
				'<strong>' . esc_html( $locale['native_name'] ) . '</strong>',
				'<code>' . esc_html( $translations_date ) . '</code>'
			);

			$admin_notice_message_forceupdate = __( 'Click the button above to force update the latest approved translations.', 'translation-stats' );

			// Check if the current translation version is different the WordPress installed version.
			if ( substr( $available_translations[ $locale['wp_locale'] ]['version'], 0, 3 ) !== substr( $wp_version['number'], 0, 3 ) ) {

				$admin_notice_type           = 'warning';
				$admin_notice_message_status = sprintf(
					'%s</br>%s',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. */
							__( 'The translation of WordPress %1$s for %2$s is not complete.', 'translation-stats' )
						),
						'<strong>' . esc_html( $wp_version['name'] ) . '</strong>',
						'<strong>' . esc_html( $locale['native_name'] ) . '</strong>'
					),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. 3: Opening link tag <a href="[link]">. 4: Locale name. */
							__( 'Please register at %1$sTranslating WordPress%2$s and join the %3$sTranslation Team%2$s to help translating WordPress to %4$s!', 'translation-stats' )
						),
						'<a href="https://translate.wordpress.org/locale/' . esc_html( $locale['slug']['locale'] ) . '/' . $locale['slug']['variant'] . '/wp/' . esc_html( $wp_version['slug'] ) . '/" target="_blank">',
						'</a>',
						'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale['wp_locale'] ) . '" target="_blank">',
						'<strong>' . esc_html( $locale['native_name'] ) . '</strong>'
					)
				);

			}

			$admin_notice = array(
				'type'        => $admin_notice_type,
				'inline'      => isset( $notice_args['inline'] ) ? $notice_args['inline'] : null,
				'dismissible' => isset( $notice_args['dismissible'] ) ? $notice_args['dismissible'] : null,
				'force_show'  => true,
				'message'     => sprintf(
					'%s</p><p>%s',
					$admin_notice_message_status,
					$admin_notice_message_forceupdate
				),
			);
			$this->tstats_notices->tstats_notice_message( $admin_notice );

		}


		/**
		 * Load WordPress core update loading placeholder.
		 *
		 * @since 0.9.5
		 */
		public function tstats_update_core_content() {

			$admin_notice = array(
				'type'        => 'warning',
				'notice-alt'  => false,
				'inline'      => false,
				'update-icon' => true,
				'css-class'   => 'translation-stats-loading update-core',
				'message'     => esc_html__( 'The update process is starting. This process may take a while on some hosts, so please be patient.', 'translation-stats' ),
			);
			$this->tstats_notices->tstats_notice_message( $admin_notice );

		}


		/**
		 * Load WordPress core update content.
		 *
		 * @since 0.9.5
		 */
		public function tstats_update_core_content_load() {

			$result = array();

			$projects = $this->tstats_translations_api->tstats_wordpress_subprojects();

			$tstats_language = $this->tstats_globals->tstats_translation_language();

			$project_count = 0;

			// Destination of translation files.
			$destination = WP_LANG_DIR . '/';

			foreach ( $projects as $project ) {

				$project_count ++;
				?>

				<h4>
					<?php
					printf(
						/* translators: 1: Translation name, 2: Number of the translation, 3: Total number of translations being updated. */
						esc_html__( 'Updating Translation of %1$s (%2$d/%3$d)', 'translation-stats' ),
						'<em>' . esc_html( $project['name'] ) . '</em>',
						esc_html( $project_count ),
						esc_html( count( $projects ) )
					);
					?>
				</h4>

				<?php
				$result = $this->tstats_update_translation( $destination, $project, $tstats_language );
				if ( is_wp_error( $result['data'] ) ) {
					$error_message = $result['data']->get_error_message();
					$admin_notice  = array(
						'type'    => 'error',
						'message' => sprintf(
							/* translators: 1: Title of an update, 2: Error message. */
							esc_html__( 'An error occurred while updating %1$s: %2$s', 'translation-stats' ),
							'<em>' . esc_html( $project['name'] ) . '</em>',
							'<strong>' . esc_html( $error_message ) . '</strong>'
						),
					);
					?>

					<p>
						<?php
						foreach ( $result['log'] as $result_log_item ) {
							echo wp_kses_post( $result_log_item ) . '<br>';
						}
						?>
					</p>

					<?php
					$this->tstats_notices->tstats_notice_message( $admin_notice );
				} else {
					?>

					<script type="text/javascript">jQuery('.waiting-<?php echo esc_attr( $project_count ); ?>').css("display", "inline-block");</script>
					<div class="updated js-update-details" data-update-details="progress-<?php echo esc_attr( $project_count ); ?>">
						<p>
							<?php
							printf(
								/* translators: %s: Project name. */
								esc_html__( '%s updated successfully.', 'translation-stats' ),
								'<em>' . esc_html( $project['name'] ) . '</em>'
							);
							?>
							<button type="button" class="hide-if-no-js button-link js-update-details-toggle" aria-expanded="false"><?php esc_attr_e( 'Show details.', 'translation-stats' ); ?></button>
						</p>
					</div>

					<div class="update-messages hide-if-js update-details-moved" id="progress-<?php echo esc_attr( $project_count ); ?>" style="display: none;">
						<p>
							<?php
							foreach ( $result['log'] as $result_log_item ) {
								echo wp_kses_post( $result_log_item ) . '<br>';
							}
							esc_html_e( 'Translation updated successfully.', 'translation-stats' );
							?>
							<br>
						</p>
					</div>

					<?php
				}
			}
			?>

			<p>
				<?php
				esc_html_e( 'All updates have been completed.', 'translation-stats' );
				?>
			</p>

			<?php
			wp_die();

		}


		/**
		 * Update project translation.
		 * Download .po file, extract with Gettext to generate .po and .json files.
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param string $wp_locale     WP Locale ( e.g.: 'pt_PT' ).
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function tstats_update_translation( $destination, $project, $wp_locale ) {

			// Set array of log entries.
			$result['log'] = array();

			// Get Translation Stats Locale data.
			$locale = $this->tstats_translations_api->tstats_locale( $wp_locale );

			// Download file from WordPress.org translation table.
			$download = $this->tstats_download_translations( $project, $locale );
			array_push( $result['log'], $download['log'] );
			$result['data'] = $download['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .po from WordPress.org response.
			$generate_po = $this->tstats_generate_po( $destination, $project, $locale, $download['data'] );
			array_push( $result['log'], $generate_po['log'] );
			$result['data'] = $generate_po['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Extract translations from file.
			$translations   = $this->tstats_extract_translations( $destination, $project, $locale );
			$result['data'] = $translations['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .mo file from extracted translations.
			$generate_mo = $this->tstats_generate_mo( $destination, $project, $locale, $translations['data'] );
			array_push( $result['log'], $generate_mo['log'] );
			$result['data'] = $generate_mo['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .json files from extracted translations.
			$generate_jsons = $this->tstats_gettext->tstats_make_json( $destination, $project, $locale, $translations['data'], false );
			$result['log']  = array_merge( $result['log'], $generate_jsons['log'] );
			$result['data'] = $generate_jsons['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			return $result;

		}


		/**
		 * Download file from WordPress.org translation table.
		 *
		 * @param array $project    Project array.
		 * @param array $locale     Locale array.
		 * @return array|WP_Error   Array on success, WP_Error on failure.
		 */
		public function tstats_download_translations( $project, $locale ) {

			// Get WordPress core version info.
			$wp_version = $this->tstats_translations_api->tstats_wordpress_version();

			// Set translation data path.
			$source = $this->tstats_translations_api->tstats_translation_path( $wp_version, $project, $locale );

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: URL. */
				esc_html__( 'Downloading translation from %s…', 'translation-stats' ),
				'<code>' . esc_html( $source ) . '</code>'
			);

			// Get the translation data.
			$response = wp_remote_get( $source );

			if ( ! is_array( $response ) || 'application/octet-stream' !== $response['headers']['content-type'] ) {

				// Report message.
				$result['data'] = new WP_Error(
					'download-translation',
					sprintf(
						'%s %s',
						esc_html__( 'Download failed.', 'translation-stats' ),
						esc_html__( 'A valid URL was not provided.', 'translation-stats' )
					)
				);
				return $result;

			}

			$result['data'] = $response;

			return $result;

		}


		/**
		 * Generate .po from WordPress.org response.
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param array  $locale        Locale array.
		 * @param array  $response      HTTP response.
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function tstats_generate_po( $destination, $project, $locale, $response ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale['wp_locale'] . '.po';

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: File name. */
				esc_html__( 'Saving file %s…', 'translation-stats' ),
				'<code>' . esc_html( $file_name ) . '</code>'
			);

			// Generate .po file.
			$success = file_put_contents( $destination . $file_name, $response['body'] ); // phpcs:ignore

			if ( ! $success ) {

				// Report message.
				$result['data'] = new WP_Error(
					'generate-po',
					esc_html__( 'Could not create file.', 'translation-stats' )
				);
				return $result;

			}

			$result['data'] = true;

			return $result;

		}


		/**
		 * Extract translations from file.
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param array  $locale        Locale array.
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function tstats_extract_translations( $destination, $project, $locale ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale['wp_locale'] . '.po';

			$translations = Gettext\Translations::fromPoFile( $destination . $file_name );

			if ( ! $translations ) {

				// Report message.
				$result['data'] = new WP_Error(
					'extract-translations',
					esc_html__( 'Could not extract file.', 'translation-stats' )
				);

				return $result;

			}

			$result['data'] = $translations;

			return $result;

		}


		/**
		 * Generate .mo file from extracted translations.
		 *
		 * @param string $destination    Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project        Project array.
		 * @param array  $locale         Locale array.
		 * @param object $translations   Extracted translations to export.
		 * @return array|WP_Error        Array on success, WP_Error on failure.
		 */
		public function tstats_generate_mo( $destination, $project, $locale, $translations ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale['wp_locale'] . '.mo';

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: File name. */
				esc_html__( 'Saving file %s…', 'translation-stats' ),
				'<code>' . $file_name . '</code>'
			);

			// Generate .mo file.
			$generate = $translations->toMoFile( $destination . $file_name );

			if ( ! $generate ) {

				// Report message.
				$result['data'] = new WP_Error(
					'generate-mo',
					esc_html__( 'Could not create file.', 'translation-stats' )
				);
				return $result;

			}

			$result['data'] = true;

			return $result;

		}


		/**
		 * Filter 'update_core' to remove language pack update info of previous WordPress version.
		 *
		 * @since 0.9.5
		 *
		 * @param object $transient    The 'update_core' transient object.
		 * @return object $transient   The same or a modified version of the transient.
		 */
		public function tstats_remove_previous_wp_translation( $transient ) {

			if ( ! empty( $transient->translations ) ) {
				if ( $transient->version_checked !== $transient->translations[0]['version'] ) {
					// Empty update info of language pack for previous WordPress version..
					$transient->translations = array();
				}
			}

			return $transient;
		}

	}

}

new TStats_Update_Core();
