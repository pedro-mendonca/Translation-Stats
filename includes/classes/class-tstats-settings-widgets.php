<?php
/**
 * Class file for registering Translation Stats settings widgets.
 *
 * @since 0.9.0
 *
 * @package Translation Stats
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_Widgets' ) ) {

	/**
	 * Class TStats_Settings_Widgets.
	 */
	class TStats_Settings_Widgets {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Globals.
			$this->tstats_globals = new TStats_Globals();

			// Add Sidebar before Translation Stats settings.
			add_action( 'tstats_settings__sidebar__content', array( $this, 'tstats_settings_widget__about' ) );

		}


		/**
		 * Show plugin info widget.
		 *
		 * @since 0.9.0
		 *
		 * @param string $show  True or false.
		 */
		public function tstats_settings_widget__about() {

			$wp_org_review_link      = 'https://wordpress.org/support/view/plugin-reviews/translation-stats?filter=5';
			$five_stars_rating       = '<span class="star-rating"><span class="star star-full"></span><span class="star star-full"></span><span class="star star-full"></span><span class="star star-full"></span><span class="star star-full"></span></span>';
			$external_site_link_url  = 'https://translationstats.com';
			$external_site_link_icon = '<i aria-hidden="true" class="dashicons dashicons-external"></i>';
			?>

			<div class="postbox" id="tstats_settings_metabox__about">
				<div class="inside">

					<div class="tstats-logo">
						<a href="<?php echo esc_url( $this->tstats_globals->tstats_link( $external_site_link_url, urlencode( home_url() ), 'link', 'tstats_plugin_logo' ) ); ?>">
							<img src="<?php echo TSTATS_PATH . '/img/logo-color-transparent.png'; ?>" alt="<?php esc_attr_e( 'Translation Stats', 'translation-stats' ); ?>">
						</a>
					</div>
					<p><?php esc_html_e( 'Show plugins translation stats on your WordPress install.','translation-stats'); ?></p>

					<!-- <h3><?php esc_html_e( 'Changelog', 'translation-stats' ); ?></h3>
					<p>
						<?php
						printf(
							__(
								'See what\'s new in %1$sversion %2$s%3$s.',
								'translation-stats'),
							'<a href="' . esc_url( $this->tstats_globals->tstats_link( $external_site_link_url . '/changelog/', urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ) . '" target="_blank">',
							TSTATS_VERSION,
							'</a>'
						);
						?>
					</p>
				-->

					<h3><?php esc_html_e( 'Resources', 'translation-stats' ); ?></h3>
					<ul>
						<li><a href="<?php echo esc_url( $this->tstats_globals->tstats_link( $external_site_link_url, urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ); ?>" target="_blank"><?php echo $external_site_link_icon; ?> <?php esc_html_e( 'Site', 'translation-stats' ); ?></a></li>
						<li><a href="<?php echo esc_url( $this->tstats_globals->tstats_link( $external_site_link_url . '/faq/', urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ); ?>" target="_blank"><?php echo $external_site_link_icon; ?> <?php esc_html_e( 'FAQ', 'translation-stats' ); ?></a></li>
						<li><a href="<?php echo esc_url( $this->tstats_globals->tstats_link( $external_site_link_url . '/changelog/', urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ); ?>" target="_blank"><?php echo $external_site_link_icon; ?> <?php esc_html_e( 'Changelog', 'translation-stats' ); ?></a></li>
						<li><a href="https://wordpress.org/support/plugin/translation-stats/" target="_blank"><?php echo $external_site_link_icon; ?> <?php esc_html_e( 'Support', 'translation-stats' ); ?></a></li>
					</ul>

					<h3><?php esc_html_e( 'Contact', 'translation-stats' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Found an issue, have a feature suggestion or just want to send some feedback?', 'translation-stats' ); ?></li>
						<li><a href="<?php echo esc_url( $this->tstats_globals->tstats_link( $external_site_link_url . '/contact/', urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ); ?>" target="_blank"><?php echo $external_site_link_icon; ?> <?php esc_html_e( 'Write me!', 'translation-stats' ); ?></a></li>
					</ul>
				</div>

				<div class="footer">

					<!--
					<p>
						<?php
						printf(
							__(
								/* translators: %1$s Translation Stats plugin name. %2$s Plugin version. */
								'Thank you for translating with %1$s version %2$s.',
								'translation-stats'
							),
							'<a href="' . esc_url( $this->tstats_globals->tstats_link( $external_site_link_url, urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ) . '">' . /* translators: Plugin name, do not translate! */ __( 'Translation Stats', 'translation-stats' ) . '</a>',
							TSTATS_VERSION
						);
						?>
					</p>-->

					<p>
						<?php
						printf(
							/* translators: %s Five stars icons. */
							esc_html__( 'Please rate %s on WordPress.org.', 'translation-stats' ),
							'<a href="' . $wp_org_review_link . '" target="_blank">' . $five_stars_rating . '</a>'
						);
						?>
					</p>

					<p>
						<?php
						printf(
							__(
								/* translators: Plugin name and version - Do not translate! */
								'Translation Stats %s',
								'translation-stats'

							),
							'<small>v.' . esc_html( TSTATS_VERSION ) . '</small>'
						);
						?>
						<!--
						<br />
						<?php
						printf(
							esc_html__(
								/* translators: %s Author name. */
								'By %s',
								'translation-stats'
							),
							'<a href="' . esc_url( $this->tstats_globals->tstats_link( $external_site_link_url, urlencode( home_url() ), 'link', 'tstats_plugin_link' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Pedro Mendonça', 'translation-stats' ) . '</a>'
						);
						?>
					</p>
					-->
				</div>
			</div>
			<?php
		}




	}

}

new TStats_Settings_Widgets();
