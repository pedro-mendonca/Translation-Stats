<?php
/**
 * Class file for registering Translation Stats settings section.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section' ) ) {

	/**
	 * Class Settings_Section.
	 */
	abstract class Settings_Section {


		/**
		 * Settings section data.
		 *
		 * @var array{
		 *        id: string,
		 *        title: string,
		 *        description: null|string,
		 *        page: string
		 *      }
		 */
		protected $section;

		/**
		 * Settings section fields.
		 *
		 * @var list<
		 *     array{
		 *         id: string,
		 *         type: string,
		 *         class: string,
		 *         title: string,
		 *         label: string,
		 *         description: string,
		 *         helper: string
		 *     }
		 * >
		 */
		protected $fields;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Set section.
			$this->get_section();

			// Set fields.
			$this->get_fields();

			add_settings_section(
				$this->section['id'],       // ID of the section.
				$this->section['title'],    // Title of the section.
				array( $this, 'callback' ), // Function that fills the section with the desired content.
				$this->section['page']      // The prefixed menu page on which to display this section.  Should match $menu_slug.
			);

			register_setting(
				$this->section['page'],      // The prefixed menu page on which to display this section. Should match $menu_slug.
				TRANSLATION_STATS_WP_OPTION  // The WordPress option to store Translation Stats settings.
			);

		}


		/**
		 * Get section data.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function get_section() {

			$this->section = $this->section();

		}


		/**
		 * Get section fields.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function get_fields() {

			$fields = $this->fields();

			if ( ! empty( $fields ) ) { // @phpstan-ignore-line

				// Prepare fields with defaults merged.
				foreach ( $fields as $key => $field ) {

					// Populate the field with default data.
					$fields[ $key ] = wp_parse_args( $field, $this->field_defaults() );

				}

				// Set the fields with added defaults.
				$this->fields = $fields;

			}

		}


		/**
		 * Data for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array{
		 *        id: string,
		 *        title: string,
		 *        description: null,
		 *        page: string
		 *      }   Array of settings section data.
		 */
		public function section() {

			return array(
				'page'        => '',   // Parent settings page.
				'id'          => '',   // Section ID.
				'title'       => '',   // Section title.
				'description' => null, // Section description.
			);

		}


		/**
		 * Fields for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array{}    Array of settings section fields.
		 */
		public function fields() {

			return array();

		}


		/**
		 * Callback function for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return callable|void
		 */
		public function callback() {

			// Get section fields.
			$fields = $this->fields();

			if ( ! empty( $fields ) ) { // @phpstan-ignore-line

				// Add settings section fields.
				foreach ( $this->fields as $field ) {
					$this->add_field( $field );
				}
			}

			// Show section description.
			$this->render_description();

			// Show custom section data.
			$this->render_custom_section();

		}


		/**
		 * Render section description.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function render_description() {

			$description = $this->section['description'];

			// Check if description exist.
			if ( ! is_null( $description ) ) {
				?>

				<p><?php echo esc_html( $description ); ?></p>

				<?php
			}

		}


		/**
		 * Render custom section data.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		public function render_custom_section() {
			// Intentionally left empy.
		}


		/**
		 * Add settings section field.
		 *
		 * @since 1.2.0
		 *
		 * @param array<string, string|bool> $field   Field data.
		 * @return void
		 */
		public function add_field( $field ) {

			// Get field type.
			$type = $field['type'];

			// Check it field type is supported.
			if ( Settings_Field::is_supported( strval( $type ) ) ) {

				// First letter uppercase to match class names.
				$type = ucfirst( strval( $type ) );

				// Class name with namespace and field type sufix to load the mathing class.
				$class = __NAMESPACE__ . "\Settings_Field_{$type}";

				// Actual field type class instantiation.
				new $class( $field );
			}

		}


		/**
		 * Default data for the section fields.
		 *
		 * @since 1.2.0
		 *
		 * @return array{page: string, section: string, path: string}   Array of default fields data.
		 */
		public function field_defaults() {

			return array(
				'page'    => $this->section['page'],  // The default page of the section of the field.
				'section' => $this->section['id'],    // The default section of the field.
				'path'    => 'settings',              // The default path of the settings array.
			);

		}

	}

}
