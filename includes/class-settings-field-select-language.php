<?php
/**
 * Class file for registering Translation Stats settings select language field.
 *
 * @package Translation_Stats
 *
 * @since 1.2.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field_Select_Language' ) ) {

	/**
	 * Class Settings_Field_Select_Language.
	 */
	class Settings_Field_Select_Language extends Settings_Field {


		/**
		 * Display settings select language field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args  Array of select language field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {

			$field_id    = TRANSLATION_STATS_WP_OPTION . '[' . $args['path'] . '][' . $args['id'] . ']';
			$label       = $args['label'];
			$description = $args['description'];
			$default     = $args['default'];
			$options     = get_option( TRANSLATION_STATS_WP_OPTION );
			$option      = empty( $options[ $args['path'] ][ $args['id'] ] ) ? '' : $options[ $args['path'] ][ $args['id'] ];
			$value       = is_array( $options ) ? $option : $default;

			// Get installed languages.
			$languages = get_available_languages();

			// Add WPLANG to installed languages.
			if ( ! is_multisite() && defined( 'WPLANG' ) && '' !== WPLANG && 'en_US' !== WPLANG && ! in_array( WPLANG, $languages, true ) ) {
				$languages[] = WPLANG;
			}

			?>
			<label>
				<?php
				$args = array(
					'id'                          => $field_id,  // ID attribute of the select element. Default 'locale'.
					'name'                        => $field_id,  // Name attribute of the select element. Default 'locale'.
					'selected'                    => $value,     // Language which should be selected.
					'echo'                        => true,       // Whether to echo the generated markup. Accepts 0, 1, or their boolean equivalents. Default 1.
					'show_available_translations' => true,       // Whether to show available translations. Default true.
					'show_option_site_default'    => true,       // Whether to show an option to fall back to the site's locale. Default false.
					'show_option_en_us'           => false,      // Whether to show an option for English (United States). Default true.
					'languages'                   => $languages, // Array of available languages.
				);
				wp_dropdown_languages( $args );
				echo ' ' . esc_html( $label );
				?>
			</label>
			<p class='description'><?php echo esc_html( $description ); ?></p>
			<?php

			// Add setting field info action hook for debugging.
			do_action( 'translation_stats_setting_field__after', $field_id, $option, $default );
		}
	}
}
