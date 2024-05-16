<?php
/**
 * Class file for registering Translation Stats settings section.
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

if ( ! class_exists( __NAMESPACE__ . '\Settings_Section' ) ) {

	/**
	 * Class Settings_Section.
	 */
	abstract class Settings_Section {


		/**
		 * Settings section data.
		 *
		 * @var array
		 */
		protected $section;

		/**
		 * Settings section fields.
		 *
		 * @var array
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

			$section = $this->section();

			if ( ! empty( $section ) ) {
				$this->section = $section;
			}
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

			if ( ! empty( $fields ) ) {

				// Prepare fields with defaults merged.
				foreach ( $fields as $key => $field ) {

					// Populate the field with default data.
					$fields[ $key ] = $this->prepare_field( $field );

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
		 * @return array   Array of settings section data.
		 */
		public function section() {

			return array(
				'page'        => null, // Parent settings page.
				'id'          => null, // Section ID.
				'title'       => null, // Section title.
				'description' => null, // Section description.
			);
		}


		/**
		 * Fields for the settings section.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Array of settings section fields.
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

			if ( ! empty( $fields ) ) {

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
			// Intentionally left empty.
		}


		/**
		 * Add settings section field.
		 *
		 * @since 1.2.0
		 *
		 * @param array $field   Field data.
		 * @return void
		 */
		public function add_field( $field ) {

			// Get field type.
			$type = $field['type'];

			// Check it field type is supported.
			if ( Settings_Field::is_supported( $type ) ) {

				// First letter uppercase to match class names.
				$type = ucfirst( $type );

				// Class name with namespace and field type suffix to load the matching class.
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
		 * @return array   Array of default fields data.
		 */
		public function field_defaults() {

			return array(
				'page'    => $this->section['page'],  // The default page of the section of the field.
				'section' => $this->section['id'],    // The default section of the field.
				'path'    => 'settings',              // The default path of the settings array.
			);
		}


		/**
		 * Merge field data with field defaults, field data overrides defaults..
		 *
		 * @since 1.2.0
		 *
		 * @param array $field   Array of field data.
		 * @return array         Array of field data.
		 */
		public function prepare_field( $field ) {

			// Get default field values.
			$field_defaults = $this->field_defaults();

			// Merge and override field data over defaults.
			$field = array_merge( $field_defaults, $field );

			return $field;
		}
	}
}
