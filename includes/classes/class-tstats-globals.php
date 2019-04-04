<?php
/**
 * Class file for the Translation Stats Globals.
 *
 * @package Translation Stats
 *
 * @since 0.8.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Globals' ) ) {

	/**
	 * Class TStats_Globals.
	 */
	class TStats_Globals {


		/**
		 * Set the Translation Language.
		 *
		 * @since 0.8.0
		 *
		 * @return string $tstats_language  Translation Language.
		 */
		public function tstats_translation_language() {
			// Get Translation Language from Settings.
			$tstats_language = get_option( TSTATS_WP_OPTION )['translation_language'];
			if ( ! $tstats_language || 'site-default' === $tstats_language ) {
				$tstats_language = get_locale();
			}
			return $tstats_language;
		}

	}

}
