<?php
/**
 * Class file for the Translation Stats Globals.
 *
 * @package Translation Stats
 *
 * @since 0.9.0
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


		/**
		 * Add campaign information to URL.
		 *
		 * @since 0.9.0
		 *
		 * @param string $link      Link to costumize.
		 * @param string $source    Set utm_source, default is site URL.
		 * @param string $medium    Set utm_medium, default is 'link'.
		 * @param string $campaign  Set utm_campaign, default is 'tstats_plugin'.'
		 *
		 * @return $campaign_link  Return link with campaign parameteres.
		 */
		public function tstats_link( $link, $source, $medium, $campaign ) {

			$utm_source    = ! empty( $source ) ? $source : urlencode( home_url() );
			$utm_medium    = ! empty( $medium ) ? $medium : 'link';
			$utm_campaign  = ! empty( $campaign ) ? $campaign : 'tstats_plugin';

			$campaign_link = $link . '?utm_source=' . $utm_source . '&amp;utm_medium=' . $medium . '&amp;utm_campaign=' . $campaign;
			return $campaign_link;
		}

	}

}
