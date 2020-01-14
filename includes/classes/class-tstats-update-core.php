<?php
/**
 * Class file for the Translation Stats Update Core.
 *
 * @package Translation Stats
 *
 * @since 0.9.5
 */

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

			// Instantiate Translation Stats Update Translations.
			$this->tstats_update_translations = new TStats_Update_Translations();

			// Add WordPress translation info and update button to updates page.
			add_action( 'core_upgrade_preamble', array( $this, 'tstats_updates_wp_translation_notice' ) );

			// Load WordPress translation updater.
			add_action( 'wp_ajax_tstats_update_core_content_load', array( $this, 'tstats_update_core_content_load' ) );

			// Add WordPress translation info admin notice to Dashboard.
			add_action( 'admin_notices', array( $this, 'tstats_dashboard_wp_translation_notice' ) );

			// Filter 'update_core' transient to prevent update of previous WordPress version language pack.
			add_filter( 'pre_set_site_transient_update_core', array( $this, 'tstats_remove_previous_wp_translation' ) );

		}


		/**
		 * Show WordPress core translation info message on Dashboard.
		 *
		 * @since 0.9.5.2
		 */
		public function tstats_dashboard_wp_translation_notice() {

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

			// Check if the current translation version is different the WordPress installed version.
			if ( substr( $available_translations[ $locale['wp_locale'] ]['version'], 0, 3 ) === substr( $wp_version['number'], 0, 3 ) ) {
				return;
			}

			$notice_message_status = sprintf(
				'%s<br>%s',
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
			$notice_message_forceupdate = sprintf(
				/* translators: %s: Button label. */
				esc_html__( 'Click the %s button to force update the latest approved translations.', 'translation-stats' ),
				'<strong>&#8220;' . __( 'Update WordPress Translation', 'translation-stats' ) . '&#8221;</strong>'
			);

			$admin_notice = array(
				'type'        => 'warning',
				'inline'      => false,
				'dismissible' => true,
				'force_show'  => true,
				'message'     => sprintf(
					'%s</p><p>%s',
					$notice_message_status,
					$notice_message_forceupdate
				),
				'extra-html'  => $this->tstats_form_update_wordpress_translation(),
			);
			$this->tstats_notices->tstats_notice_message( $admin_notice );

		}


		/**
		 * Add WordPress core info and update button on the Updates page bottom.
		 *
		 * @since 0.9.5.2
		 */
		public function tstats_updates_wp_translation_notice() {

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
				// Add form with action button to update WordPress core translation.
				echo wp_kses( $this->tstats_form_update_wordpress_translation(), $this->tstats_globals->tstats_allowed_html() );

				// Show the Translation Stats admin notice for WordPress core translation status.
				$notice_args = array();
				$this->tstats_updates_wp_translation_notice_message( $notice_args );
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
		 * Add form with action button to update WordPress core translation.
		 *
		 * @since 0.9.5.2
		 */
		public function tstats_form_update_wordpress_translation() {

			// Show force update WordPress translation button.
			$form_action = 'update-core.php?tstats=force_update_core';
			ob_start();
			?>

			<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-wordpress-translation" class="upgrade">
				<?php wp_nonce_field( 'upgrade-wordpress-translation' ); ?>
				<p>
					<input type="submit" name="force_update_core" class="button button-primary" value="<?php esc_attr_e( 'Update WordPress Translation', 'translation-stats' ); ?>">
				</p>
			</form>

			<?php
			return ob_get_clean();
		}


		/**
		 * WordPress updates translation info message.
		 *
		 * @since 0.9.5.2
		 *
		 * @param array $notice_args  Arguments for admin notice.
		 */
		public function tstats_updates_wp_translation_notice_message( $notice_args ) {

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

			$notice_type           = 'info';
			$notice_message_status = sprintf(
				wp_kses_post(
					/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
					__( 'The translation <em>language pack</em> of WordPress %1$s for %2$s was updated on %3$s.', 'translation-stats' )
				),
				'<strong>' . esc_html( $wp_version['name'] ) . '</strong>',
				'<strong>' . esc_html( $locale['native_name'] ) . '</strong>',
				'<code>' . esc_html( $translations_date ) . '</code>'
			);

			$notice_message_forceupdate = sprintf(
				/* translators: %s: Button label. */
				esc_html__( 'Click the %s button to force update the latest approved translations.', 'translation-stats' ),
				'<strong>&#8220;' . __( 'Update WordPress Translation', 'translation-stats' ) . '&#8221;</strong>'
			);

			// Check if the current translation version is different the WordPress installed version.
			if ( substr( $available_translations[ $locale['wp_locale'] ]['version'], 0, 3 ) !== substr( $wp_version['number'], 0, 3 ) ) {

				$notice_type           = 'warning';
				$notice_message_status = sprintf(
					'%s<br>%s',
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
				'type'        => $notice_type,
				'inline'      => isset( $notice_args['inline'] ) ? $notice_args['inline'] : null,
				'dismissible' => isset( $notice_args['dismissible'] ) ? $notice_args['dismissible'] : null,
				'force_show'  => true,
				'message'     => sprintf(
					'%s</p><p>%s',
					$notice_message_status,
					$notice_message_forceupdate
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
						/* translators: 1: Translation name, 2: WordPress Locale, 3: Number of the translation, 4: Total number of translations being updated. */
						esc_html__( 'Updating translations for %1$s (%2$s) (%3$d/%4$d)', 'translation-stats' ),
						'<em>' . esc_html( $project['name'] ) . '</em>',
						esc_html( $tstats_language ),
						esc_html( $project_count ),
						esc_html( count( $projects ) )
					);
					?>
				</h4>

				<?php
				$result = $this->tstats_update_translations->tstats_update_translation( $destination, $project, $tstats_language );

				$log_display = is_wp_error( $result['data'] ) ? 'block' : 'none';
				?>

				<div class="update-messages hide-if-js" id="progress-<?php echo esc_attr( $project_count ); ?>" style="display: <?php echo esc_attr( $log_display ); ?>;">
					<p>
						<?php
						foreach ( $result['log'] as $result_log_item ) {
							echo wp_kses_post( $result_log_item ) . '<br>';
						}
						?>
					</p>
				</div>

				<?php
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
					$this->tstats_notices->tstats_notice_message( $admin_notice );

				} else {
					?>

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
