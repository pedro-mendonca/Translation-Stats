<?php
/**
 * Class file for the Translation Stats Locale.
 *
 * @package Translation_Stats
 *
 * @since 1.2.6
 */

declare( strict_types = 1 );

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
