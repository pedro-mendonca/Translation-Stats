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
		 * @param string $campaign  Set utm_campaign, default is 'tstats_plugin'.
		 *
		 * @return string  Link with campaign parameters.
		 */
		public function tstats_link( $link, $source, $medium, $campaign ) {

			$utm_source   = ! empty( $source ) ? $source : rawurlencode( home_url() );
			$utm_medium   = ! empty( $medium ) ? $medium : 'link';
			$utm_campaign = ! empty( $campaign ) ? $campaign : 'tstats_plugin';

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
				'em'     => array(),
				'ul'     => array(
					'class' => array(),
				),
				'ol'     => array(
					'class' => array(),
				),
				'p'      => array(
					'class' => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'strong' => array(),
				'div'    => array(
					'class' => array(),
					'data'  => array(),
					'style' => array(),
				),
				'span'   => array(
					'class' => array(),
					'style' => array(),
				),
				'img'    => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'select' => array(
					'id'    => array(),
					'class' => array(),
					'name'  => array(),
				),
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
				'style'  => array(),
				'script' => array(),
			);

			return $allowed_html;
		}

	}

}
