<?php
/**
 * Class file for the Translation Stats Utils.
 *
 * @package Translation_Stats
 *
 * @since 0.9.0
 * @since 1.2.0   Renamed from Globals to Utils.
 */

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
		 * @return string|false   Complete URL for the asset. Return false if extension is not supported.
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

			// Check if path has dirname and extension.
			if ( ! isset( $path['dirname'] ) || ! isset( $path['extension'] ) ) {
				return false;
			}

			// Check if type is supported.
			if ( ! in_array( $path['extension'], $types, true ) ) {
				return false;
			}

			// Only provide minified assets if in development mode or SCRIPT_DEBUG is set to true.
			$suffix = $minify && ! self::is_development_mode() && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? '.min' : '';

			return TRANSLATION_STATS_DIR_URL . 'assets/' . $path['dirname'] . '/' . $path['filename'] . $suffix . '.' . $path['extension'];
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
		 * @return array  Array of allowed HTML elements.
		 */
		public static function allowed_html() {

			$allowed_html = array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'class'  => array(),
					'data'   => array(),
					'rel'    => array(),
					'target' => array(),
				),
				'br'     => array(),
				'button' => array(
					'aria-expanded' => array(),
					'class'         => array(),
					'id'            => array(),
					'type'          => array(),
				),
				'div'    => array(
					'class' => array(),
					'data'  => array(),
					'style' => array(),
				),
				'em'     => array(),
				'form'   => array(
					'action' => array(),
					'class'  => array(),
					'method' => array(),
					'name'   => array(),
				),
				'img'    => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'input'  => array(
					'class' => array(),
					'name'  => array(),
					'type'  => array(),
					'value' => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'ol'     => array(
					'class' => array(),
				),
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
				'p'      => array(
					'class' => array(),
				),
				'script' => array(),
				'select' => array(
					'id'    => array(),
					'class' => array(),
					'name'  => array(),
				),
				'span'   => array(
					'class' => array(),
					'style' => array(),
				),
				'strong' => array(),
				'style'  => array(),

				'ul'     => array(
					'class' => array(),
				),
			);

			return $allowed_html;
		}
	}
}
