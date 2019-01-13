<?php
/**
 * Class file for registering Translation Stats Settings API.
 *
 * @package Translation Stats
 *
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TStats_Settings_API' ) ) {

	/**
	 * Class TStats_Settings_API.
	 */
	class TStats_Settings_API {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Stats Debug.
			$this->tstats_debug = new TStats_Debug();

		}


		/**
		 * Adds settings field.
		 *
		 * @since 0.8.0
		 *
		 * @param array $args  Array of field arguments.
		 */
		public function tstats_add_settings_field( $args ) {

			switch ( $args['type'] ) {
				case 'text': // If it's a text field.
					break;
				case 'textarea': // If it's a textarea.
					break;
				case 'checkbox': // If it's a checkbox.
					$this->tstats_add_settings_checkbox( $args );
					break;
				case 'button': // If it's a button.
					$this->tstats_add_settings_button( $args );
					break;
				case 'select': // If it's a select dropdown.
					$this->tstats_add_settings_select( $args );
					break;
			}
		}


		/**
		 * Adds settings checkbox field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $field  Array of checkbox field data.
		 */
		public function tstats_add_settings_checkbox( $field ) {

			add_settings_field(
				$field['id'],                        // String for the 'id' attribute tags.
				$field['title'],                     // Title of the field.
				array( $this, $field['callback'] ),  // Function that fills the field with the desired inputs as part of the larger form.
				$field['section'],                   // The menu page on which to display this field.
				$field['section'],                   // The section of the settings page in which to show the box.
				array(
					'id'          => $field['id'],           // Settings field slug.
					'label'       => $field['label'],        // Settings field label.
					'description' => $field['description'],  // Settings field description.
					'helper'      => $field['helper'],       // Settings field helper.
					'class'       => $field['class'],        // Settings field class.
					'section'     => $field['section'],      // Settings field section.
					'default'     => $field['default'],      // Settings field default.
				)
			);
		}


		/**
		 * Adds settings select field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $field  Array of select field data.
		 */
		public function tstats_add_settings_select( $field ) {

			add_settings_field(
				$field['id'],                        // String for the 'id' attribute tags.
				$field['title'],                     // Title of the field.
				array( $this, $field['callback'] ),  // Function that fills the field with the desired inputs as part of the larger form.
				$field['section'],                   // The menu page on which to display this field.
				$field['section'],                   // The section of the settings page in which to show the box.
				array(
					'id'             => $field['id'],              // Settings field slug.
					'label'          => $field['label'],           // Settings field label.
					'description'    => $field['description'],     // Settings field description.
					'helper'         => $field['helper'],          // Settings field helper.
					'class'          => $field['class'],           // Settings field class.
					'section'        => $field['section'],         // Settings field section.
					'select_options' => $field['select_options'],  // Settings field options.
					'default'        => $field['default'],         // Settings field default.
				)
			);
		}


		/**
		 * Adds settings button field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $field  Array of button field data.
		 */
		public function tstats_add_settings_button( $field ) {

			add_settings_field(
				$field['id'],                        // String for the 'id' attribute tags.
				$field['title'],                     // Title of the field.
				array( $this, $field['callback'] ),  // Function that fills the field with the desired inputs as part of the larger form.
				$field['section'],                   // The menu page on which to display this field.
				$field['section'],                   // The section of the settings page in which to show the box.
				array(
					'id'           => $field['id'],            // Settings field slug.
					'name'         => $field['name'],          // Settings field name.
					'wrap'         => $field['wrap'],          // Settings field <p> wrap.
					'label'        => $field['label'],         // Settings field label.
					'description'  => $field['description'],   // Settings field description.
					'helper'       => $field['helper'],        // Settings field helper.
					'formaction'   => $field['formaction'],    // Settings field form action.
					'confirmation' => $field['confirmation'],  // Settings field confirmation.
					'class'        => $field['class'],         // Settings field class.
					'section'      => $field['section'],       // Settings field section.
				)
			);
		}


		/**
		 * Display settings checkbox field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $args  Array of checkbox field arguments.
		 */
		public function tstats_render_input_checkbox( $args ) {
			$id          = TSTATS_WP_OPTION . '[' . $args['id'] . ']';
			$label       = $args['label'];
			$description = $args['description'];
			$class       = $args['class'];
			$default     = $args['default'];
			$helper      = $args['helper'];
			$options     = get_option( TSTATS_WP_OPTION );
			$option      = empty( $options[ $args['id'] ] ) ? '' : true;
			$value       = is_array( $options ) ? $option : $default;
			?>
			<label>
				<input name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php checked( $value, true ); ?> class="<?php echo esc_attr( $class ); ?>" type="checkbox" value="true" />
				<?php
				echo esc_html( $label );
				?>
			</label>
			<p class='description'><?php echo esc_html( $description ); ?></p>
			<?php
			$this->tstats_debug->tstats_debug_setting_field( $id, $option, $default, 'info', false );
		}


		/**
		 * Display setting select field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $args  Array of select field arguments.
		 */
		public function tstats_render_input_select( $args ) {

			$id             = TSTATS_WP_OPTION . '[' . $args['id'] . ']';
			$label          = $args['label'];
			$description    = $args['description'];
			$select_options = $args['select_options'];
			$default        = $args['default'];
			$helper         = $args['helper'];
			$size           = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$options        = get_option( TSTATS_WP_OPTION );
			$option         = empty( $options[ $args['id'] ] ) ? '' : $options[ $args['id'] ];
			$value          = is_array( $options ) ? $option : $default;

			?>
			<label>
				<select class="<?php echo esc_attr( $size ); ?>" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>">
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
			$this->tstats_debug->tstats_debug_setting_field( $id, $option, $default, 'info', false );
		}


		/**
		 * Display setting select for Translation Language field.
		 *
		 * @since 0.8.0
		 *
		 * @param array $args  Array of select field arguments.
		 */
		public function tstats_render_input_select__language( $args ) {

			$id          = TSTATS_WP_OPTION . '[' . $args['id'] . ']';
			$label       = $args['label'];
			$description = $args['description'];
			$default     = $args['default'];
			$options     = get_option( TSTATS_WP_OPTION );
			$option      = empty( $options[ $args['id'] ] ) ? '' : $options[ $args['id'] ];
			$value       = is_array( $options ) ? $option : $default;
			?>
			<label>
				<?php
				$args = array(
					'id'                          => $id,    // ID attribute of the select element. Default 'locale'.
					'name'                        => $id,    // Name attribute of the select element. Default 'locale'.
					'selected'                    => $value, // Language which should be selected.
					'echo'                        => '1',    // Whether to echo the generated markup. Accepts 0, 1, or their boolean equivalents. Default 1.
					'show_available_translations' => true,   // Whether to show available translations. Default true.
					'show_option_site_default'    => true,   // Whether to show an option to fall back to the site's locale. Default false.
				);
				wp_dropdown_languages( $args );
				echo ' ' . esc_html( $label );
				?>
			</label>
			<p class='description'><?php echo esc_html( $description ); ?></p>
			<?php
			$this->tstats_debug->tstats_debug_setting_field( $id, $option, $default, 'info', false );
		}


		/**
		 * Display setting button field type.
		 *
		 * @since 0.8.0
		 *
		 * @param array $args  Array of button field arguments.
		 */
		public function tstats_render_input_button( $args ) {
			$id           = TSTATS_WP_OPTION . '[' . $args['id'] . ']';
			$name         = $args['name'];
			$label        = $args['label'];
			$description  = $args['description'];
			$class        = $args['class'];
			$wrap         = $args['wrap'];
			$formaction   = $args['formaction'];
			$confirmation = $args['confirmation'];
			$helper       = $args['helper'];
			if ( $confirmation ) {
				$onclick = sprintf( 'return confirm( \'%s\' )', esc_js( $confirmation ) );
			} else {
				$onclick = '';
			}

			$button_args = array(
				'id'         => $id,
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
