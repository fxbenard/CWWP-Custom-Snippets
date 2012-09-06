<?php
/**
 * Transport class for Code With WP Custom Snippets.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init_Transport {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Holds a copy of the import/export menu slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $import_export;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	
		self::$instance = $this;
		
		add_action( 'admin_menu', array( $this, 'menu' ) );
	
	}
	
	/**
	 * Generates the submenu items for the code snippets post type.
	 *
	 * @since 1.0.0
	 */
	public function menu() {
	
		/** Create the import/export page */
		self::$import_export = add_submenu_page( 'edit.php?post_type=' . Cwwp_Init_Posttype::$post_type->name, __( 'Import/Export Code Snippets', 'cwwp-custom-snippets' ), __( 'Import/Export', 'cwwp-custom-snippets' ), 'manage_options', 'import-export-code-snippets', array( $this, 'import_export_page' ) );
		if ( self::$import_export )
			add_action( 'load-' . self::$import_export, array( $this, 'import_export' ) );
	
	}
	
	/**
	 * Callback function to load any necessary items in the
	 * import/export page.
	 *
	 * @since 1.0.0
	 */
	public function import_export() {
	
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	
	}
	
	/**
	 * Enqueues necessary scripts and styles for the import/export page.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id The current post ID
	 * @global object $post The current post object
	 */
	public function assets() {
	
		global $id, $post;
			
		/** Register and enqueue scripts and styles */
		$dev = WP_DEBUG || WP_SCRIP_DEBUG ? '-dev' : '';
			
		wp_register_script( 'cwwp-import-export', plugins_url( '/js/import-export' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'jquery' ), '1.0.0', true );
		wp_register_style( 'cwwp-import-export', plugins_url( '/css/import-export' . $dev . '.css', dirname( dirname( __FILE__ ) ) ) );
		wp_enqueue_script( 'cwwp-import-export' );
		wp_enqueue_style( 'cwwp-import-export' );
	
	}
	
	/**
	 * Callback function to create the import/export HTML page.
	 *
	 * @since 1.0.0
	 */
	public function import_export_page() {
	
		echo '<div class="wrap cwwp-import-export">';
			screen_icon();
			echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';
			echo '<div class="cwwp-import-export-tabs">';
				echo '<h2 class="nav-tab-wrapper">';
					echo '<a href="#import" class="nav-tab">' . __( 'Import', 'cwwp-custom-snippets' ) . '</a>';
					echo '<a href="#export" class="nav-tab">' . __( 'Export', 'cwwp-custom-snippets' ) . '</a>';
				echo '</h2>';
				echo '<div id="import" style="display: none;">';
					echo $this->get_import_content();
				echo '</div>';
				echo '<div id="export" style="display: none;">';
					echo $this->get_export_content();
				echo '</div>';
			echo '</div>';
		echo '</div>';
	
	}
	
	/**
	 * Returns the HTML content for the import tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string $html HTML content for the import tab
	 */
	private function get_import_content() {
	
	
	
	}
	
	/**
	 * Returns the HTML content for the export tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string $html HTML content for the export tab
	 */
	private function get_export_content() {
	
	
	
	}
	
	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 *
	 * @return object $instance The object instance
	 */
	public static function get_instance() {
	
		return self::$instance;
	
	}
	
}