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
	 * Callback function to create the import/export HTML page.
	 *
	 * @since 1.0.0
	 */
	public function import_export_page() {
	
	
	
	}
	
	/**
	 * Callback function to load any necessary items in the
	 * import/export page.
	 *
	 * @since 1.0.0
	 */
	public function import_export() {
	
	
	
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