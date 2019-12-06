<?php
/**
 * Class file for the Translation Stats Gettext.
 *
 * Use Gettext/Gettext v.4.8.1 library to extract .po translations and generate .json files.
 * https://github.com/php-gettext/Gettext/tree/4.x
 *
 * Based on WP-CLi i18n Command 'wp i18n make-json'.
 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/plugins/wporg-gp-customizations/inc/cli/class-language-pack.php#L435
 *
 * @package Translation Stats
 *
 * @since 0.9.5
 */

use Gettext\Translations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Gettext' ) ) {

	/**
	 * Class TStats_Gettext.
	 */
	class TStats_Gettext {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Notices.
			$this->tstats_notices = new TStats_Notices();

		}


		/**
		 * Options passed to json_encode().
		 *
		 * @var int JSON options.
		 */
		protected $json_options = 0;

		/**
		 * Splits a single PO file into multiple JSON files.
		 *
		 * Based on WP-CLi Command 'wp i18n make-json'.
		 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php#L120
		 *
		 * The default .json file names are ${domain}-${locale}-${md5}.json.
		 * For WordPress core translation files, all .json files must be named as ${locale}-${md5}.json.
		 * In this case the WP_Locale used is the one configured in Translation Stats settings: $tstats_locale.
		 * https://developer.wordpress.org/block-editor/developers/internationalization/#create-translation-file
		 *
		 * @since 0.9.5
		 *
		 * @param string $destination      Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project          Project array.
		 * @param array  $locale           Locale array.
		 * @param object $translations     Extracted translations to export.
		 * @param bool   $include_domain   Include the ${domain} in the file name. Set to true or false. Defaults to true.
		 * @return array                   List of created JSON files.
		 */
		public function tstats_make_json( $destination, $project, $locale, $translations, $include_domain = true ) {

			$mapping = array();
			$result  = array();

			// Set the file naming convention. ( e.g.: {domain}-{locale}-{hash}.json ).
			// If $include_domain is set to false, use file name convention ${locale}-${md5}.json.
			$domain         = $project['domain'] && $include_domain ? $project['domain'] . '-' : '';
			$base_file_name = $domain . $locale['wp_locale'];

			foreach ( $translations as $index => $translation ) {

				// Find all unique sources this translation originates from.
				$sources = array_map(
					function ( $reference ) {
						$file = $reference[0];

						if ( substr( $file, - 7 ) === '.min.js' ) {
							return substr( $file, 0, - 7 ) . '.js';
						}

						if ( substr( $file, - 3 ) === '.js' ) {
							return $file;
						}

						return null;
					},
					$translation->getReferences()
				);

				$sources = array_unique( array_filter( $sources ) );

				foreach ( $sources as $source ) {
					if ( ! isset( $mapping[ $source ] ) ) {
						$mapping[ $source ] = new Translations();

						$mapping[ $source ]->setDomain( $translations->getDomain() );
						$mapping[ $source ]->setHeader( 'Language', $translations->getLanguage() );
						$mapping[ $source ]->setHeader( 'PO-Revision-Date', $translations->getHeader( 'PO-Revision-Date' ) );
						$plural_forms = $translations->getPluralForms();

						if ( $plural_forms ) {
							list( $count, $rule ) = $plural_forms;
							$mapping[ $source ]->setPluralForms( $count, $rule );
						}
					}

					$mapping[ $source ][] = $translation;
				}
			}

			$result += $this->build_json_files( $mapping, $base_file_name, $destination );
			return $result;

		}


		/**
		 * Builds a mapping of JS file names to translation entries.
		 *
		 * Exports translations for each JS file to a separate translation file.
		 *
		 * Based on WP-CLi Command 'wp i18n make-json'.
		 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php#L192
		 *
		 * @param array  $mapping         A mapping of files to translation entries.
		 * @param string $base_file_name  Base file name for JSON files.
		 * @param string $destination     Path to the destination directory.
		 *
		 * @return array List of created JSON files.
		 */
		protected function build_json_files( $mapping, $base_file_name, $destination ) {

			$result['log'] = array();

			$result['data'] = true;

			foreach ( $mapping as $file => $translations ) {

				$hash             = md5( $file );
				$destination_file = "${destination}/{$base_file_name}-{$hash}.json";

				// Report message.
				$result['log'][] = sprintf(
					/* translators: %s: File name. */
					esc_html__( 'Saving file %s…', 'translation-stats' ),
					'<code>' . esc_html( $base_file_name ) . '-' . esc_html( $hash ) . '.json</code>'
				);

				$success = TStats_Gettext_JedGenerator::toFile(
					$translations,
					$destination_file,
					array(
						'json'   => $this->json_options,
						'source' => $file,
					)
				);
				if ( ! $success ) {

					// Report message.
					$result['data'] = new WP_Error(
						'generate-json',
						esc_html__( 'Could not create file.', 'translation-stats' )
					);
					return $result;

				}
			}

			return $result;
		}

	}

}
