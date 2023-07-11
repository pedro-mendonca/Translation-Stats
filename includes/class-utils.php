<?php
/**
 * Class file for the Translation Stats Utils.
 *
 * @package Translation_Stats
 *
 * @since 0.9.0
 * @since 1.2.0   Renamed from Globals to Utils.
 */

declare( strict_types = 1 );

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Utils' ) ) {

	/**
	 * Class Utils.
	 */
	class Utils {


		/**
		 * Determine if Translation Stats is in development mode.
		 *
		 * Inspired by Yoast (https://github.com/Yoast/wordpress-seo/blob/f174ad88636f9115a8c25f66daafbf84c747679b/inc/class-wpseo-utils.php#L716).
		 *
		 * @since 1.2.0
		 *
		 * @return bool
		 */
		public static function is_development_mode() {

			$development_mode = false;

			// Enable if WP_DEBUG is true.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$development_mode = true;
			}

			// Enable if TRANSLATION_STATS_DEBUG is true.
			if ( defined( 'TRANSLATION_STATS_DEBUG' ) ) {
				$development_mode = TRANSLATION_STATS_DEBUG;
			}

			/**
			 * Filter the Translation Stats development mode status.
			 *
			 * @since 1.2.0
			 *
			 * @param bool $development_mode   Set development mode to true or false.
			 */
			return apply_filters( 'translation_stats_development_mode', $development_mode );

		}


		/**
		 * Get asset URL, according the minification status.
		 *
		 * @since 1.2.0
		 * @since 1.2.1   Minify is optional, defaults to false.
		 *
		 * @param string $asset    Name of asset excluding the extension.
		 * @param bool   $minify   Determine if the asset has a minified version. Defaults to false.
		 *
		 * @return string|false   Complete URL for the asset. Return false if extension is not suported.
		 */
		public static function get_asset_url( $asset, $minify = false ) {

			$path = pathinfo( $asset );

			// Supported asset types and folders.
			$types = array(
				'css',
				'js',
				'jpg',
				'png',
				'svg',
			);

			// Check if type is supported.
			if ( ! isset( $path['extension'] ) || ! in_array( $path['extension'], $types, true ) ) {
				return false;
			}

			// Set dirname with trailing slash, if dirname is set.
			$dirname = ( isset( $path['dirname'] ) && $path['dirname'] ) ? $path['dirname'] . '/' : '';

			// Set filename.
			$filename = $path['filename'];

			// Only provide minified assets if in development mode or SCRIPT_DEBUG is set to true.
			$suffix = $minify && ! self::is_development_mode() && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? '.min' : '';

			// Set extension.
			$extension = $path['extension'];

			return TRANSLATION_STATS_DIR_URL . 'assets/' . $dirname . $filename . $suffix . '.' . $extension;

		}


		/**
		 * Set the Translation Language.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_translation_language() to translation_language().
		 * @since 1.2.0   Moved to Utils class.
		 *
		 * @return string   Translation Language as WordPress Locale ( e.g. 'pt_PT' ). Fallback to current Locale.
		 */
		public static function translation_language() {

			// Default translation language.
			$wp_locale = get_locale();

			// Get plugin settings.
			$settings = get_option( TRANSLATION_STATS_WP_OPTION );
			if ( ! $settings ) {
				return $wp_locale;
			}

			// Get Translation Language from Settings.
			$translation_language = $settings['settings']['translation_language'];
			if ( ! $translation_language || 'site-default' === $translation_language ) {
				return $wp_locale;
			}

			// Return settings translation language.
			return $translation_language;

		}


		/**
		 * Add campaign information to URL.
		 *
		 * @since 0.9.0
		 * @since 1.1.1   Renamed from tstats_link() to campaign_link().
		 * @since 1.2.0   Moved to Utils class.
		 *
		 * @param string $link      Link to customize.
		 * @param string $source    Set utm_source, default is 'plugin'.
		 * @param string $medium    Set utm_medium, default is 'link'.
		 * @param string $campaign  Set utm_campaign, default is 'plugin_link'.
		 *
		 * @return string  Link with campaign parameters.
		 */
		public static function campaign_link( $link, $source, $medium, $campaign ) {

			$utm_source   = ! empty( $source ) ? $source : 'plugin';
			$utm_medium   = ! empty( $medium ) ? $medium : 'link';
			$utm_campaign = ! empty( $campaign ) ? $campaign : 'plugin_link';

			return $link . '?utm_source=' . $utm_source . '&amp;utm_medium=' . $utm_medium . '&amp;utm_campaign=' . $utm_campaign;

		}


		/**
		 * Returns array of allowed HTML elements for use in wp_kses().
		 *
		 * @since 0.8.5
		 * @since 1.1.1   Renamed from tstats_allowed_html() to allowed_html().
		 * @since 1.2.0   Moved to Utils class.
		 *
		 * @return array<
		 *           string, bool|array<
		 *             string, bool
		 *           >
		 *         >  Array of allowed HTML elements.
		 */
		public static function allowed_html() {

			$allowed_html = array(
				'a'      => array(
					'href'   => true,
					'title'  => true,
					'class'  => true,
					'data'   => true,
					'rel'    => true,
					'target' => true,
				),
				'br'     => true,
				'button' => array(
					'aria-expanded' => true,
					'class'         => true,
					'id'            => true,
					'type'          => true,
				),
				'div'    => array(
					'class' => false,
					'data'  => true,
					'style' => true,
				),
				'em'     => true,
				'form'   => array(
					'action' => true,
					'class'  => true,
					'method' => true,
					'name'   => true,
				),
				'img'    => array(
					'alt'    => true,
					'class'  => true,
					'height' => true,
					'src'    => true,
					'width'  => true,
				),
				'input'  => array(
					'class' => true,
					'name'  => true,
					'type'  => true,
					'value' => true,
				),
				'li'     => array(
					'class' => true,
				),
				'ol'     => array(
					'class' => true,
				),
				'option' => array(
					'value'    => true,
					'selected' => true,
				),
				'p'      => array(
					'class' => true,
				),
				'script' => true,
				'select' => array(
					'id'    => true,
					'class' => true,
					'name'  => true,
				),
				'span'   => array(
					'class' => true,
					'style' => true,
				),
				'strong' => true,
				'style'  => true,

				'ul'     => array(
					'class' => true,
				),
			);

			return $allowed_html;

		}

	}

}
