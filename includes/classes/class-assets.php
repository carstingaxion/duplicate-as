<?php
/**
 * Assets Manager
 *
 * Handles enqueueing of editor scripts and styles for the
 * duplicate post button UI.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Assets' ) ) {
	/**
	 * Manages editor asset enqueueing for the duplicate post button.
	 *
	 * Enqueues the JavaScript and CSS files needed for the block editor
	 * sidebar UI (PluginPostStatusInfo panel).
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Assets {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Assets|null
		 */
		private static ?Duplicate_As_Assets $instance = null;

		/**
		 * Plugin file path.
		 *
		 * @since 0.3.0
		 * @var string
		 */
		private string $plugin_file;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.3.0
		 * @param string $plugin_file Absolute path to the main plugin file.
		 */
		private function __construct( string $plugin_file ) {
			$this->plugin_file = $plugin_file;
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @param string $plugin_file Absolute path to the main plugin file.
		 * @return Duplicate_As_Assets The singleton instance.
		 */
		public static function get_instance( string $plugin_file = '' ): Duplicate_As_Assets {
			if ( null === self::$instance ) {
				self::$instance = new self( $plugin_file );
			}
			return self::$instance;
		}

		/**
		 * Initialize WordPress hooks.
		 *
		 * @since 0.3.0
		 * @return void
		 */
		private function init_hooks(): void {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		}

		/**
		 * Enqueue editor scripts and styles.
		 *
		 * Loads JavaScript and CSS for the block editor UI.
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public function enqueue_editor_assets(): void {
			/**
			 * Safe types coming from the wp-scripts package.
			 *
			 * @var array{version:string, dependencies:array<string>} $asset_file
			 */
			$asset_file = include plugin_dir_path( $this->plugin_file ) . 'build/index.asset.php';

			wp_enqueue_script(
				'duplicate-as-editor',
				plugins_url( 'build/index.js', $this->plugin_file ),
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);

			wp_set_script_translations(
				'duplicate-as-editor',
				'duplicate-as',
				plugin_dir_path( $this->plugin_file ) . 'languages'
			);

			wp_enqueue_style(
				'duplicate-as-editor',
				plugins_url( 'build/index.css', $this->plugin_file ),
				array( 'wp-components' ),
				$asset_file['version']
			);
		}
	}
}
