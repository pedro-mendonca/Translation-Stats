<?php
/**
 * Class file for registering Translation Stats settings select field.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field_Select' ) ) {

	/**
	 * Class Settings_Field_Select.
	 */
	class Settings_Field_Select extends Settings_Field {


		/**
		 * Display settings select field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args  Array of select field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {

			$field_id       = TRANSLATION_STATS_WP_OPTION . '[' . $args['path'] . '][' . $args['id'] . ']';
			$label          = $args['label'];
			$description    = $args['description'];
			$select_options = $args['select_options'];
			$default        = $args['default'];
			$options        = get_option( TRANSLATION_STATS_WP_OPTION );
			$option         = empty( $options[ $args['path'] ][ $args['id'] ] ) ? '' : $options[ $args['path'] ][ $args['id'] ];
			$value          = is_array( $options ) ? $option : $default;

			?>
			<label>
				<select name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>">
				<?php
				foreach ( $select_options as $key => $option_label ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $option_label ) );
				}
				?>
				</select>
				<?php
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
