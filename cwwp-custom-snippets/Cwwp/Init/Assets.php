<?php
/**
 * Assets class for Code With WP Custom Snippets.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init_Assets {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	
		self::$instance = $this;
		
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	
	}
	
	/**
	 * Enqueues necessary scripts and styles for the plugin.
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
			
		if ( Cwwp_Init::is_snippet_screen() ) {
			wp_register_style( 'cwwp-admin', plugins_url( '/css/admin' . $dev . '.css', dirname( dirname( __FILE__ ) ) ) );
			wp_enqueue_style( 'cwwp-admin' );
		}
		
		if ( Cwwp_Init::is_snippet_add_edit_screen() ) {
			wp_register_script( 'cwwp-admin', plugins_url( '/js/admin' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'jquery', 'cwwp-codemirror', 'cwwp-codemirror-php' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror', plugins_url( '/js/codemirror' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array(), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-php', plugins_url( '/js/codemirror-php' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-html', plugins_url( '/js/codemirror-html' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-css', plugins_url( '/js/codemirror-css' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-js', plugins_url( '/js/codemirror-js' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-xml', plugins_url( '/js/codemirror-xml' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-clike', plugins_url( '/js/codemirror-clike' . $dev . '.js', dirname( dirname( __FILE__ ) ) ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_style( 'cwwp-codemirror', plugins_url( '/css/codemirror' . $dev . '.css', dirname( dirname( __FILE__ ) ) ) );
			wp_register_style( 'cwwp-codemirror-elegant', plugins_url( '/css/codemirror-elegant' . $dev . '.css', dirname( dirname( __FILE__ ) ) ) );
			wp_enqueue_script( 'cwwp-admin' );
			wp_enqueue_script( 'cwwp-codemirror' );
			wp_enqueue_script( 'cwwp-codemirror-php' );
			wp_enqueue_script( 'cwwp-codemirror-html' );
			wp_enqueue_script( 'cwwp-codemirror-css' );
			wp_enqueue_script( 'cwwp-codemirror-js' );
			wp_enqueue_script( 'cwwp-codemirror-xml' );
			wp_enqueue_script( 'cwwp-codemirror-clike' );
			wp_enqueue_style( 'cwwp-codemirror' );
			wp_enqueue_style( 'cwwp-codemirror-elegant' );
		
			/** Localize the admin script */
			$post_id 	= ( null === $id ) ? $post->ID : $id;
			$args 		= array(
				'nonce'		=> wp_create_nonce( 'cwwp-save-snippet' ),
				'post_id' 	=> $post_id,
				'url'		=> admin_url( 'admin-ajax.php' )
			);
			wp_localize_script( 'cwwp-admin', 'cwwp', $args );
		}
	
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