<?php
/**
 * Class file for the Translation Stats Locale.
 *
 * Extends GP_Locale from:
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * @package Translation_Stats
 *
 * @since 1.2.6
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\TS_Locale' ) ) {

	/**
	 * Class TS_Locale.
	 */
	class TS_Locale extends GP_Locale {


		/**
		 * Combined slug and variant for Locale. Eg. 'pt/default'.
		 *
		 * @var string|null
		 */
		public $locale_slug;

		/**
		 * Subdomain used on WordPress.org Locale Team page. Eg. 'pt.wordpress.org'.
		 *
		 * @var string|null
		 */
		public $wporg_subdomain;

	}

}
