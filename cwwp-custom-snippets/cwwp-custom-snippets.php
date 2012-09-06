<?php
/*
Plugin Name: Code With WP Custom Snippets
Plugin URI: http://codewithwp.com/
Description: This plugin holds custom code snippets that interact with both themes and plugins related to this website.
Author: Thomas Griffin
Author URI: http://thomasgriffinmedia.com/
Version: 1.0.0
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*
	Copyright 2012	 Thomas Griffin	 (email : thomas@thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

/** Load all of the necessary class files for the plugin */
spl_autoload_register( 'Cwwp_Init::autoload' );

/**
 * Code With WP Custom Snippets init class.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Holds a copy of the main plugin filepath.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private static $file = __FILE__;

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		/** Store the object in a static property */
		self::$instance = $this;
			
		/** Load the plugin */
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}
		
	/**
	 * Loads the plugin upgrader, registers the post type and
	 * loads all the actions and filters for the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
	
		/** Load the plugin textdomain for internationalizing strings */
		load_plugin_textdomain( 'cwwp-custom-snippets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		/** Instantiate all the necessary components of the plugin */
		$cwwp_init_admin		= new Cwwp_Init_Admin;
		$cwwp_init_ajax			= new Cwwp_Init_Ajax;
		$cwwp_init_assets		= new Cwwp_Init_Assets;
		$cwwp_init_help			= new Cwwp_Init_Help;
		$cwwp_init_posttype		= new Cwwp_Init_Posttype;
		$cwwp_init_snippets		= new Cwwp_Init_Snippets;
		$cwwp_init_transport	= new Cwwp_Init_Transport;

	}
	
	/**
	 * PSR-0 compliant autoloader to load classes as needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classname The name of the class
	 * @return null Return early if the class name does not start with the correct prefix
	 */
	public static function autoload( $classname ) {
	
		if ( 'Cwwp_Init' !== substr( $classname, 0, 9 ) )
			return;
			
		$filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		require $filename;
	
	}
	
	/**
	 * Helper method for determing if we are viewing the snippet add/edit screen or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if on a snippet screen, false if not
	 */
	public static function is_snippet_add_edit_screen() {
	
		if ( 'cwwp-custom-snippets' == get_current_screen()->post_type && 'post' == get_current_screen()->base )
			return true;
			
		return false;
	
	}
	
	/**
	 * Helper method for determing if we are viewing the snippet screen or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if on a snippet screen, false if not
	 */
	public static function is_snippet_screen() {
	
		if ( 'cwwp-custom-snippets' == get_current_screen()->post_type )
			return true;
			
		return false;
	
	}
	
	/**
	 * Flag for determing whether or not the exec() function is available.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if it is enabled, false is disabled
	 */
	public static function exec_enabled() {
	
		$disabled = (array) explode( ',', @ini_get( 'disable_functions' ) );
		return ! in_array( 'exec', $disabled );
	
	}
	
	/**
	 * Flag for determing whether or not the shell_exec() function is available.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if it is enabled, false is disabled
	 */
	public static function shell_exec_enabled() {
	
		$disabled = (array) explode( ',', @ini_get( 'disable_functions' ) );
		return ! in_array( 'shell_exec', $disabled );
	
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
	
	/**
	 * Getter method for retrieving the plugin filepath.
	 *
	 * @since 1.0.0
	 *
	 * @return string $file The plugin filepath
	 */
	public static function get_file() {
	
		return self::$file;
	
	}

}

/** Instantiate the class */
$cwwp_init = new Cwwp_Init;