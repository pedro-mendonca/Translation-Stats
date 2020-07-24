<?php
/**
 * Class file for the Translation Stats Globals.
 *
 * @package Translation Stats
 *
 * @since 0.9.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Globals' ) ) {

	/**
	 * Class Globals.
	 */
	class Globals {


		/**
		 * Set the Translation Language.
		 *
		 * @since 0.8.0
		 *
		 * @return string $tstats_language  Translation Language as WordPress Locale ( e.g. 'pt_PT' ).
		 */
		public function tstats_translation_language() {
			// Get Translation Language from Settings.
			$tstats_language = get_option( TSTATS_WP_OPTION )['settings']['translation_language'];
			if ( ! $tstats_language || 'site-default' === $tstats_language ) {
				$tstats_language = get_locale();
			}
			return $tstats_language;
		}


		/**
		 * Check if Translation Stats language is 'en_US'.
		 *
		 * @since 0.9.5
		 *
		 * @return bool  True if Translation Stats language is 'en_US', false otherwise.
		 */
		public function tstats_language_is_english() {

			// Get Translation Stats language.
			$tstats_language = $this->tstats_translation_language();

			// Check if user locale is 'en_US'.
			if ( 'en_US' === $tstats_language ) {
				return true;
			}

			return false;
		}


		/**
		 * Add campaign information to URL.
		 *
		 * @since 0.9.0
		 *
		 * @param string $link      Link to customize.
		 * @param string $source    Set utm_source, default is 'tstats'.
		 * @param string $medium    Set utm_medium, default is 'link'.
		 * @param string $campaign  Set utm_campaign, default is 'tstats_link'.
		 *
		 * @return string  Link with campaign parameters.
		 */
		public function tstats_link( $link, $source, $medium, $campaign ) {

			$utm_source   = ! empty( $source ) ? $source : 'tstats';
			$utm_medium   = ! empty( $medium ) ? $medium : 'link';
			$utm_campaign = ! empty( $campaign ) ? $campaign : 'tstats_link';

			$campaign_link = $link . '?utm_source=' . $utm_source . '&amp;utm_medium=' . $utm_medium . '&amp;utm_campaign=' . $utm_campaign;

			return $campaign_link;
		}


		/**
		 * Returns array of allowed HTML elements for use in wp_kses().
		 *
		 * @since 0.8.5
		 *
		 * @return array  Array of allowed HTML elements.
		 */
		public function tstats_allowed_html() {
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
