<?php
/**
 * Class file for registering Translation Stats settings field.
 *
 * @package Translation_Stats
 *
 * @since 1.2.0
 */

declare( strict_types = 1 );

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Settings_Field' ) ) {

	/**
	 * Class Settings_Field.
	 */
	abstract class Settings_Field {


		/**
		 * Constructor.
		 *
		 * @param array{
		 *          page: string,
		 *          section: string,
		 *          path: string,
		 *          id: string,
		 *          type: string,
		 *          class: string,
		 *          title: string,
		 *          label: string,
		 *          description: string,
		 *          helper: string,
		 *          default: bool,
		 *          wrap?: bool,
		 *          formaction?: string,
		 *          confirmation?: string,
		 *          select_options?: string
		 *        } $field   Array of field data.
		 *
		 * @return void
		 */
		public function __construct( $field = null ) {

			// If field not provided, do nothing.
			if ( is_null( $field ) ) {
				return;
			}

			// Add field.
			$this->add_field( $field );

		}


		/**
		 * Adds settings field type.
		 *
		 * @since 1.2.0
		 *
		 * @param array{
		 *          page: string,
		 *          section: string,
		 *          path: string,
		 *          id: string,
		 *          type: string,
		 *          class: string,
		 *          title: string,
		 *          label: string,
		 *          description: string,
		 *          helper: string,
		 *          default: bool,
		 *          wrap?: bool,
		 *          formaction?: string,
		 *          confirmation?: string,
		 *          select_options?: string
		 *        } $field   Array of field data.
		 *
		 * @return void
		 */
		public function add_field( $field ) {

			add_settings_field(
				$field['id'],                // The ID of the field.
				$field['title'],             // Title of the field.
				array( $this, 'callback' ),  // Function that fills the field with the desired inputs as part of the larger form.
				$field['page'],              // The menu page on which to display this field.
				$field['section'],           // The section of the settings page on which to show the box.
				$field                       // The array of the field data.
			);

		}


		/**
		 * Default callback.
		 *
		 * @since 1.2.6
		 *
		 * @param array<string, bool|string> $args  Array of select field arguments.
		 *
		 * @return void
		 */
		public function callback( $args ) {
			// Empty callback.
		}


		/**
		 * Check if field type is supported.
		 *
		 * @since 1.2.0
		 *
		 * @param string $type   Type of field to check if is supported.
		 * @return bool   True if type is supported, defaults to false.
		 */
		public static function is_supported( $type ) {

			$types = array(
				/* phpcs:ignore.
				'text',
				'textarea',
				*/
				'button',
				'checkbox',
				'hidden',
				'select',
				'select_language',
			);

			/**
			 * Action hook to filter the settings tabs.
			 *
			 * @since 1.2.0
			 */
			$types = apply_filters( 'translation_stats_settings_field_types', $types );

			// Check if field type exist in the supported types array.
			if ( in_array( $type, $types, true ) ) {
				return true;
			}

			return false;

		}

	}

}
