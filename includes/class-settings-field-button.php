<?php
/**
 * Class file for registering Translation Stats settings button field.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field_Button' ) ) {

	/**
	 * Class Settings_Field_Button.
	 */
	class Settings_Field_Button extends Settings_Field {


		/**
		 * Display settings button field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args  Array of button field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {

			$field_id     = TRANSLATION_STATS_WP_OPTION . '[' . $args['path'] . '][' . $args['id'] . ']';
			$name         = $args['name'];
			$label        = $args['label'];
			$description  = $args['description'];
			$class        = $args['class'];
			$wrap         = $args['wrap'];
			$formaction   = $args['formaction'];
			$confirmation = $args['confirmation'];
			if ( $confirmation ) {
				$onclick = sprintf( 'return confirm( \'%s\' )', esc_js( $confirmation ) );
			} else {
				$onclick = '';
			}

			$button_args = array(
				'id'         => $field_id,
				'formaction' => $formaction,
				'onclick'    => $onclick,
			);
			submit_button( esc_attr( $label ), $class, $name, $wrap, $button_args );
			?>
			<p class='description'><?php echo esc_html( $description ); ?></p>
			<?php
		}
	}
}
