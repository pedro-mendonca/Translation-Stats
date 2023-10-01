<?php
/**
 * Class file for registering Translation Stats settings checkbox field.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field_Checkbox' ) ) {

	/**
	 * Class Settings_Field_Checkbox.
	 */
	class Settings_Field_Checkbox extends Settings_Field {


		/**
		 * Display settings checkbox field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args  Array of checkbox field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {

			$field_id    = TRANSLATION_STATS_WP_OPTION . '[' . $args['path'] . '][' . $args['id'] . ']';
			$label       = $args['label'];
			$description = $args['description'];
			$class       = $args['class'];
			$default     = $args['default'];
			$options     = get_option( TRANSLATION_STATS_WP_OPTION );
			$option      = empty( $options[ $args['path'] ][ $args['id'] ] ) ? '' : true;
			$value       = is_array( $options ) ? $option : $default;
			?>
			<label>
				<input name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" <?php checked( $value, true ); ?> class="<?php echo esc_attr( $class ); ?>" type="checkbox" value="true" />
				<?php
				echo esc_html( $label );
				?>
			</label>
			<p class='description'><?php echo esc_html( $description ); ?></p>
			<?php

			// Add setting field info action hook for debugging.
			do_action( 'translation_stats_setting_field__after', $field_id, $option, $default );
		}
	}
}
