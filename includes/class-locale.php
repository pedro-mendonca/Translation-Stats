<?php
/**
 * Class file for the Translation Stats Locale.
 *
 * Extends GP_Locale from:
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * @package Translation_Stats
 *
 * @since 1.2.8
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Locale' ) ) {

	/**
	 * Class Locale.
	 */
	class Locale extends GP_Locale {


		/**
		 * Array of available translations data obtained with wp_get_available_translations() for the locale.
		 *
		 * @var array
		 */
		public $translations;

		/**
		 * Locale slug. Eg.: 'pt/default'.
		 *
		 * @var string
		 */
		public $locale_slug;

		/**
		 * Subdomain of the Locale team page on wp.org.
		 *
		 * @var string
		 */
		public $wporg_subdomain;


		/**
		 * Constructor.
		 *
		 * @param GP_Locale $locale  GP_Locale object.
		 *
		 * @return void
		 */
		public function __construct( $locale ) {

			// Import parent object properties.
			foreach ( get_object_vars( $locale ) as $key => $value ) {
				$this->$key = $value;
			}

			// Add 'wporg_subdomain' property.
			$this->wporg_subdomain = Locales::wporg_subdomain( $locale );

			// Add 'locale_slug' property.
			$this->locale_slug = Locales::locale_slug( $locale );
		}
	}
}
