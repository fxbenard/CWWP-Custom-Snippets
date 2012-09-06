<?php
/**
 * Snippets class for Code With WP Custom Snippets.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init_Snippets {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Holds a copy of the custom snippets directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $snippets_dir;
	
	/**
	 * Holds a copy of the custom snippets file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $snippets_file;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	
		self::$instance = $this;
		
		add_action( 'after_setup_theme', array( $this, 'file' ) );
	
	}
	
	/**
	 * Loads the custom snippets file.
	 *
	 * @since 1.0.0
	 */
	public function file() {
	
		/** Load the custom snippets file if exists; create it if it doesn't */
		if ( self::get_snippets_file() )
			include self::get_snippets_file();
		else
			self::create_snippets_file();
	
	}
	
		/**
	 * Attempts to retrieve the custom code snippet file.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool File path on success, false on failure
	 */
	public static function get_snippets_file() {
	
		return file_exists( self::get_snippets_file_path() ) ? self::get_snippets_file_path() : false;
	
	}
	
	/**
	 * Returns the custom code snippet file path.
	 *
	 * @since 1.0.0
	 *
	 * @return string The file path to the custom snippets file
	 */
	public static function get_snippets_file_path() {
	
		$uploads_dir 				= wp_upload_dir();
		$snippets_dir				= explode( '/', plugin_basename( __FILE__ ) );
		return self::$snippets_file = trailingslashit( $uploads_dir['basedir'] ) . trailingslashit( $snippets_dir[0] ) . 'custom.php';
	
	}
	
	/**
	 * Creates the custom snippets file.
	 *
	 * @since 1.0.0
	 */
	public function create_snippets_file() {
	
		/** Create the snippets folder if it does not already exist */
		if ( ! self::get_snippets_directory() )
			self::create_snippets_directory();
			
		$handle = @fopen( self::get_snippets_file_path(), 'wb+' );
		@fwrite( $handle, stripslashes( self::get_snippets_default_text() ) );
		@fclose( $handle );
	
	}
	
	/**
	 * Flag to see if the snippets directory exists or not.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool Directory path on success, false on failure
	 */
	public function get_snippets_directory() {
	
		return is_dir( self::get_snippets_directory_path() ) ? self::get_snippets_directory_path() : false; 
	
	}
	
	/**
	 * Returns the custom snippets directory path.
	 *
	 * @since 1.0.0
	 *
	 * @return string The custom snippets directory path
	 */
	public function get_snippets_directory_path() {
	
		$uploads_dir 				= wp_upload_dir();
		$snippets_dir				= explode( '/', untrailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
		return self::$snippets_dir	= trailingslashit( $uploads_dir['basedir'] ) . array_pop( $snippets_dir );
	
	}
	
	/**
	 * Creates the custom snippets directory.
	 *
	 * @since 1.0.0
	 */
	public function create_snippets_directory() {
			
		wp_mkdir_p( self::$snippets_dir );
	
	}
	
	/**
	 * Returns the default custom snippets file text when creating the file.
	 *
	 * @since 1.0.0
	 *
	 * @return string The default snippets file text data
	 */
	public function get_snippets_default_text() {
	
		return '<?php' . PHP_EOL . '/** Do not remove this line. Custom code snippets go below. */' . PHP_EOL;
	
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