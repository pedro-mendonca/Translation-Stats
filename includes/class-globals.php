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

if ( ! class_exists( __NAMESPACE__ . '\Globals' ) ) {

	/**
	 * Class Globals.
	 */
	class Globals {


		/**
		 * Set the Translation Language.
		 *
		 * @since 0.8.0
		 * @since 1.1.1   Renamed from tstats_translation_language() to translation_language().
		 *
		 * @return string   Translation Language as WordPress Locale ( e.g. 'pt_PT' ).
		 */
		public function translation_language() {
			// Get Translation Language from Settings.
			$wp_locale = get_option( TSTATS_WP_OPTION )['settings']['translation_language'];
			if ( ! $wp_locale || 'site-default' === $wp_locale ) {
				$wp_locale = get_locale();
			}
			return $wp_locale;
		}


		/**
		 * Add campaign information to URL.
		 *
		 * @since 0.9.0
		 * @since 1.1.1   Renamed from tstats_link() to campaign_link().
		 *
		 * @param string $link      Link to customize.
		 * @param string $source    Set utm_source, default is 'tstats'.
		 * @param string $medium    Set utm_medium, default is 'link'.
		 * @param string $campaign  Set utm_campaign, default is 'plugin_link'.
		 *
		 * @return string  Link with campaign parameters.
		 */
		public function campaign_link( $link, $source, $medium, $campaign ) {

			$utm_source   = ! empty( $source ) ? $source : 'plugin';
			$utm_medium   = ! empty( $medium ) ? $medium : 'link';
			$utm_campaign = ! empty( $campaign ) ? $campaign : 'plugin_link';

			$campaign_link = $link . '?utm_source=' . $utm_source . '&amp;utm_medium=' . $utm_medium . '&amp;utm_campaign=' . $utm_campaign;

			return $campaign_link;
		}


		/**
		 * Returns array of allowed HTML elements for use in wp_kses().
		 *
		 * @since 0.8.5
		 * @since 1.1.1   Renamed from tstats_allowed_html() to allowed_html().
		 *
		 * @return array  Array of allowed HTML elements.
		 */
		public function allowed_html() {
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
