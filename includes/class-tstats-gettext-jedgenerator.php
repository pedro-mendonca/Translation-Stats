<?php
/**
 * Class file for the Translation Stats Gettext JedGenerator.
 *
 * Based on WP-CLi i18n Command JedGenerator.
 * https://github.com/wp-cli/i18n-command/blob/master/src/JedGenerator.php
 *
 * @package Translation Stats
 *
 * @since 0.9.5
 */

namespace Translation_Stats;

use Gettext\Generators\Jed;
use Gettext\Translations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Gettext_JedGenerator' ) ) {

	/**
	 * Class TStats_Gettext_JedGenerator.
	 *
	 * Adds some more meta data to JED translation files than the default generator.
	 */
	class TStats_Gettext_JedGenerator extends Jed {


		/**
		 * Saves the translations in a file.
		 *
		 * @param Translations $translations  Array with all translations.
		 * @param array        $options       Options.
		 *
		 * @return string|false
		 */
		public static function toString( Translations $translations, array $options = array() ) {
			$data     = '';
			$options += static::$options;
			$domain   = $translations->getDomain() ? $translations->getDomain() : 'messages';
			$messages = static::buildMessages( $translations );

			/**
			 * Set the file structure.
			 *
			 * 'wpcli' : wp-cli/i18n-command structure.
			 * 'wporg' : wordpress.org language packs structure.
			 */
			$structure = 'wporg';
			switch ( $structure ) {
				case 'wpcli':
					$configuration = array(
						'' => array(
							'domain'       => $domain,
							'lang'         => $translations->getLanguage() ? $translations->getLanguage() : 'en',
							'plural-forms' => $translations->getHeader( 'Plural-Forms' ) ? $translations->getHeader( 'Plural-Forms' ) : 'nplurals=2; plural=(n != 1);',
						),
					);
					$data          = array(
						'translation-revision-date' => $translations->getHeader( 'PO-Revision-Date' ),
						'generator'                 => 'Translation Stats/' . TSTATS_VERSION,
						'source'                    => $options['source'],
						'domain'                    => $domain,
						'locale_data'               => array(
							$domain => $configuration + $messages,
						),
					);
					break;
				case 'wporg':
					$configuration = array(
						'' => array(
							'domain'       => $domain,
							'plural-forms' => $translations->getHeader( 'Plural-Forms' ) ? $translations->getHeader( 'Plural-Forms' ) : 'nplurals=2; plural=(n != 1);',
							'lang'         => $translations->getLanguage() ? $translations->getLanguage() : 'en',
						),
					);
					$data          = array(
						'translation-revision-date' => $translations->getHeader( 'PO-Revision-Date' ),
						'generator'                 => 'Translation Stats/' . TSTATS_VERSION,
						'domain'                    => $domain,
						'locale_data'               => array(
							$domain => $configuration + $messages,
						),
						'comment'                   => array(
							'reference' => $options['source'],
						),
					);
					break;
			}

			return wp_json_encode( $data, $options['json'] );
		}


		/**
		 * Generates an array with all translations.
		 *
		 * @param Translations $translations  Array with all translations.
		 *
		 * @return array
		 */
		public static function buildMessages( Translations $translations ) {
			$plural_forms      = $translations->getPluralForms();
			$number_of_plurals = is_array( $plural_forms ) ? ( $plural_forms[0] - 1 ) : null;
			$messages          = array();
			$context_glue      = chr( 4 );

			foreach ( $translations as $translation ) {

				if ( $translation->isDisabled() ) {
					continue;
				}

				$key = $translation->getOriginal();

				if ( $translation->hasContext() ) {
					$key = $translation->getContext() . $context_glue . $key;
				}

				if ( $translation->hasPluralTranslations( true ) ) {
					$message = $translation->getPluralTranslations( $number_of_plurals );
					array_unshift( $message, $translation->getTranslation() );
				} else {
					$message = array( $translation->getTranslation() );
				}

				$messages[ $key ] = $message;
			}

			return $messages;
		}
	}

}
