<?php
/**
 * Class file for registering Translation Stats settings hidden field.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field_Hidden' ) ) {

	/**
	 * Class Settings_Field_Hidden.
	 */
	class Settings_Field_Hidden extends Settings_Field {


		/**
		 * Display settings hidden field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args  Array of hidden field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {

			$field_id = TRANSLATION_STATS_WP_OPTION . '[' . $args['path'] . '][' . $args['id'] . ']';
			$default  = $args['default'];
			$options  = get_option( TRANSLATION_STATS_WP_OPTION );
			$option   = empty( $options[ $args['path'] ][ $args['id'] ] ) ? '' : $options[ $args['path'] ][ $args['id'] ];
			$value    = is_array( $options ) ? $option : $default;
			?>
			<input name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" type="hidden" value="<?php echo esc_attr( $value ); ?>">
			<?php
		}
	}
}
