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

/**
 * Code With WP Custom Snippets class.
 *
 * @since 1.0.0
 *
 * @package	CWWP_Custom_Snippets
 * @author	Thomas Griffin
 */
class CWWP_Custom_Snippets {

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
	 * Holds a copy of the registered post type.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $post_type;
	
	/**
	 * Holds a copy of the custom snippets directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $snippets_dir;
	
	/**
	 * Holds a copy of the custom snippets file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $snippets_file;

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		/** Store the object in a static property */
		self::$instance = $this;
		
		/** Load the snippets file */
		add_action( 'after_setup_theme', array( $this, 'file' ) );
			
		/** Load the plugin */
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}
	
	/**
	 * Loads the custom snippets file.
	 *
	 * @since 1.0.0
	 */
	public function file() {
	
		/** Load the custom snippets file if exists; create it if it doesn't */
		if ( $this->get_snippets_file() )
			include $this->get_snippets_file();
		else
			$this->create_snippets_file();
	
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
		
		/** Load the plugin hooks and filters */
		add_action( 'init', array( $this, 'post_type' ), 0 );
		add_action( 'current_screen', array( $this, 'screen' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'submitpost_box', array( $this, 'snippet' ), 0 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_edit-cwwp-custom-snippets_columns', array( $this, 'columns' ) );
		add_filter( 'manage_cwwp-custom-snippets_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_action( 'wp_trash_post', array( $this, 'trash' ) );
		add_action( 'untrash_post', array( $this, 'untrash' ) );
		add_action( 'delete_post', array( $this, 'delete' ) );

	}
	
	/**
	 * Register the post type to hold custom snippets.
	 *
	 * @since 1.0.0
	 */
	public function post_type() {
	
		$labels = array(
			'name' 					=> __( 'Code Snippets', 'cwwp-custom-snippets' ),
			'singular_name' 		=> __( 'Code Snippet', 'cwwp-custom-snippets' ),
			'add_new' 				=> __( 'Add New', 'cwwp-custom-snippets' ),
			'add_new_item' 			=> __( 'Add New Code Snippet', 'cwwp-custom-snippets' ),
			'edit_item' 			=> __( 'Edit Code Snippet', 'cwwp-custom-snippets' ),
			'new_item' 				=> __( 'New Code Snippet', 'cwwp-custom-snippets' ),
			'view_item' 			=> __( 'View Code Snippet', 'cwwp-custom-snippets' ),
			'search_items' 			=> __( 'Search Code Snippets', 'cwwp-custom-snippets' ),
			'not_found' 			=> __( 'No Code Snippets found', 'cwwp-custom-snippets' ),
			'not_found_in_trash' 	=> __( 'No Code Snippets found in trash', 'cwwp-custom-snippets' ),
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'Snippets', 'cwwp-custom-snippets' )
		);

		$args = array(
			'labels' 				=> $labels,
			'public' 				=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'rewrite'				=> false,
			'query_var'				=> false,
			'menu_position' 		=> 157,
			'menu_icon'				=> plugins_url( '/css/images/menu-icon.png', __FILE__ ),
			'supports' 				=> array( 'title' )
		);

		/** Register post type with args */
		self::$post_type = register_post_type( 'cwwp-custom-snippets', $args );
	
	}
	
	/**
	 * Sets the screen layout to one column on the snippets add/edit screen
	 * and adds filter for change the default title text.
	 *
	 * @since 1.0.0
	 */
	public function screen() {
	
		if ( self::is_snippet_add_edit_screen() ) {
			if ( 1 !== get_user_option( 'screen_layout_' . self::$post_type->name, get_current_user_id() ) )
				update_user_option( get_current_user_id(), 'screen_layout_' . self::$post_type->name, 1, true );
				
			add_filter( 'enter_title_here', array( $this, 'title' ) );
		}
	
	}
	
	/**
	 * Filters the default "Enter title here" text for the snippet
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The default title text
	 * @return string $title The amended title text
	 */
	public function title( $title ) {
	
		return __( 'Enter Your Code Snippet Title Here', 'cwwp-custom-snippets' );
	
	}
	
	/**
	 * Enqueues necessary scripts and styles for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function assets() {
	
		global $id, $post;
			
		/** Register and enqueue scripts and styles */
		$dev = WP_DEBUG || WP_SCRIP_DEBUG ? '-dev' : '';
			
		if ( self::is_snippet_screen() ) {
			wp_register_style( 'cwwp-admin', plugins_url( '/css/admin' . $dev . '.css', __FILE__ ) );
			wp_enqueue_style( 'cwwp-admin' );
		}
		
		if ( self::is_snippet_add_edit_screen() ) {
			wp_register_script( 'cwwp-admin', plugins_url( '/js/admin' . $dev . '.js', __FILE__ ), array( 'jquery', 'cwwp-codemirror', 'cwwp-codemirror-php' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror', plugins_url( '/js/codemirror' . $dev . '.js', __FILE__ ), array(), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-php', plugins_url( '/js/codemirror-php' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-html', plugins_url( '/js/codemirror-html' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-css', plugins_url( '/js/codemirror-css' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-js', plugins_url( '/js/codemirror-js' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-xml', plugins_url( '/js/codemirror-xml' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_script( 'cwwp-codemirror-clike', plugins_url( '/js/codemirror-clike' . $dev . '.js', __FILE__ ), array( 'cwwp-codemirror' ), '1.0.0', true );
			wp_register_style( 'cwwp-codemirror', plugins_url( '/css/codemirror' . $dev . '.css', __FILE__ ) );
			wp_register_style( 'cwwp-codemirror-elegant', plugins_url( '/css/codemirror-elegant' . $dev . '.css', __FILE__ ) );
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
	 * Outputs the snippet box where a user can paste and modify a snippet.
	 *
	 * The textarea will be formatted by CodeMirror for code editing.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object
	 */
	public function snippet() {
	
		global $post;
		
		if ( ! self::is_snippet_screen() )
			return;
		
		settings_errors( 'cwwp-snippets' );
		wp_nonce_field( 'cwwp-snips', 'cwwp-snips' );
		
		?>
		<h4 class="top-title"><?php _e( 'Code Snippet', 'cwwp-custom-snippets' ); ?></h4>
		<p><?php printf( __( 'Paste your code snippet into the code editor below. Leave out any opening (%s) and closing (%s) php tags.', 'cwwp-custom-snippets' ), '<code><strong>&lt;?php</strong></code>', '<code><strong>?&gt;</strong></code>' ); ?></p>
		<textarea id="cwwp-code-snippet" name="cwwp-code-snippet" spellcheck="false"><?php echo stripslashes( trim( $this->get_code_snippet( $post->ID ) ) ); ?></textarea>
		<h4><?php _e( 'Code Snippet Description', 'cwwp-custom-snippets' ); ?></h4>
		<p><?php _e( 'Describe your code snippet using the editor below. Although optional, it is highly recommended to do this for future reference.', 'cwwp-custom-snippets' ); ?></p>
		<?php wp_editor( get_post_meta( $post->ID, '_cwwp_code_snippet_desc', true ), 'cwwp-code-snippet-desc', array( 'wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'quicktags' => false, 'textarea_rows' => 4 ) ); ?>
		<?php
	
	}
	
	/**
	 * Sanitizes the code snippet and writes it to the custom code snippet
	 * file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID
	 * @param object $post The current post object
	 */
	public function save( $post_id, $post ) {
	
		/** Bail out if we fail a security check */
		if ( ! isset( $_POST[sanitize_key( 'cwwp-snips' )] ) || ! wp_verify_nonce( $_POST[sanitize_key( 'cwwp-snips' )], 'cwwp-snips' ) )
			return $post_id;

		/** Bail out if running an autosave, ajax or a cron */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		/** Bail out if the user doesn't have the correct permissions to update the slider */
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		
		/** Save the description first */
		if ( isset( $_REQUEST['cwwp-code-snippet-desc'] ) )
			update_post_meta( $post_id, '_cwwp_code_snippet_desc', wp_kses_post( $_REQUEST['cwwp-code-snippet-desc'] ) );
			
		/** If our code key is empty, replace if it already exists or simply pass by it and return */
		if ( isset( $_REQUEST['cwwp-code-snippet'] ) && empty( $_REQUEST['cwwp-code-snippet'] ) ) {
			/** Grab our contents - we need to check and see if we already have a snippet */
			$handle 	= fopen( $this->get_snippets_file_path(), 'ab+' );
			$contents 	= fread( $handle, filesize( $this->get_snippets_file_path() ) );
			
			/** Build out our regex query */
			$delimiter 	= '|';
			$expression = '/**##** Snippet ID ' . absint( $post_id ) . ' **##**/';
			$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
			/** Parse it to find our snippet for this particular post or return an empty string if we don't have a snippet */
			if ( preg_match( $regex, $contents, $matches ) ) {
				$contents = str_replace( $matches[0], '', $contents );
				ftruncate( $handle, 0 );
				fwrite( $handle, stripslashes( trim( $contents ) ) );
				fclose( $handle );
				return;
			} else {
				return; // No need to continue any further if the snippet doesn't already exist
			}
		} else {
			/** Next we check to see if the snippet has been applied before - if so, overwrite it */
			$delimiter 	= '|';
			$expression = '/**##** Snippet ID ' . absint( $post_id ) . ' **##**/';
			$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
			/** Grab our contents - we need to check and see if we already have a snippet */
			$handle 	= fopen( $this->get_snippets_file_path(), 'ab+' );
			$contents 	= fread( $handle, filesize( $this->get_snippets_file_path() ) );
		
			/** If the snippet exists, overwrite it with our updated code */
			if ( preg_match( $regex, $contents, $matches ) ) {
				$data		= PHP_EOL . stripslashes( $_REQUEST['cwwp-code-snippet'] ) . PHP_EOL;			
				$contents 	= str_replace( $matches[1], stripslashes( $data ), $contents );
				ftruncate( $handle, 0 );
				fwrite( $handle, stripslashes( $contents ) );
				fclose( $handle );
				return;
			} else { // Looks like this snippet doesn't exist, so let's write it to the file
				/** Prepare our data - use custom comments so we can target them for deletion later */
				$beginning_line = PHP_EOL . PHP_EOL . '/**##** Snippet ID ' . absint( $post_id ) . ' **##**/' . PHP_EOL;
				$data			= stripslashes( $_REQUEST['cwwp-code-snippet'] );
				$ending_line	= PHP_EOL . '/**##** Snippet ID ' . absint( $post_id ) . ' **##**/';
				$code_result	= $beginning_line . $data . $ending_line;
		
				/** Take the information passed and append it to our custom file */
				fwrite( $handle, stripslashes( $code_result ) );
				fclose( $handle );
				add_settings_error( 'cwwp-snippets', 'snippet-added', 'This snippet has been added successfully!', 'updated' );
			}
		}
	
	}
	
	/**
	 * Attempts to retrieve the code snippet from the custom file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The current post ID
	 * @return string Code snippet on success or empty string if none exists
	 */
	private function get_code_snippet( $id ) {
	
		/** Get the contents of our custom snippets file */
		$handle 	= @fopen( $this->get_snippets_file_path(), 'rb' );
		$contents 	= @fread( $handle, filesize( $this->get_snippets_file_path() ) );
		
		/** Build out our regex query */
		$delimiter 	= '|';
		$expression = '/**##** Snippet ID ' . $id . ' **##**/';
		$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
		/** Parse it to find our snippet for this particular post or return an empty string if we don't have a snippet */
		if ( preg_match( $regex, $contents, $matches ) )
			return stripslashes( trim( $matches[1] ) );
		else
			return '';
	
	}
	
	/**
	 * Attempts to retrieve the custom code snippet file.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool File path on success, false on failure
	 */
	private function get_snippets_file() {
	
		return file_exists( $this->get_snippets_file_path() ) ? $this->get_snippets_file_path() : false;
	
	}
	
	/**
	 * Returns the custom code snippet file path.
	 *
	 * @since 1.0.0
	 *
	 * @return string The file path to the custom snippets file
	 */
	private function get_snippets_file_path() {
	
		$uploads_dir 				= wp_upload_dir();
		$snippets_dir				= explode( '/', plugin_basename( __FILE__ ) );
		return self::$snippets_file = trailingslashit( $uploads_dir['basedir'] ) . trailingslashit( $snippets_dir[0] ) . 'custom.php';
	
	}
	
	/**
	 * Creates the custom snippets file.
	 *
	 * @since 1.0.0
	 */
	private function create_snippets_file() {
	
		/** Create the snippets folder if it does not already exist */
		if ( ! $this->get_snippets_directory() )
			$this->create_snippets_directory();
			
		$handle = @fopen( $this->get_snippets_file_path(), 'wb+' );
		@fwrite( $handle, stripslashes( $this->get_snippets_default_text() ) );
		@fclose( $handle );
	
	}
	
	/**
	 * Flag to see if the snippets directory exists or not.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool Directory path on success, false on failure
	 */
	private function get_snippets_directory() {
	
		return is_dir( $this->get_snippets_directory_path() ) ? $this->get_snippets_directory_path() : false; 
	
	}
	
	/**
	 * Returns the custom snippets directory path.
	 *
	 * @since 1.0.0
	 *
	 * @return string The custom snippets directory path
	 */
	private function get_snippets_directory_path() {
	
		$uploads_dir 				= wp_upload_dir();
		$snippets_dir				= explode( '/', untrailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
		return self::$snippets_dir	= trailingslashit( $uploads_dir['basedir'] ) . array_pop( $snippets_dir );
	
	}
	
	/**
	 * Creates the custom snippets directory.
	 *
	 * @since 1.0.0
	 */
	private function create_snippets_directory() {
			
		wp_mkdir_p( self::$snippets_dir );
	
	}
	
	/**
	 * Returns the default custom snippets file text when creating the file.
	 *
	 * @since 1.0.0
	 *
	 * @return string The default snippets file text data
	 */
	private function get_snippets_default_text() {
	
		return '<?php' . PHP_EOL . '/** Do not remove this line. Custom code snippets go below. */' . PHP_EOL;
	
	}
	
	/**
	 * Customize the post columns for the custom snippets post type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns The default columns provided by WP_List_Table
	 * @return array $columns Amended columns with new data
	 */
	public function columns( $columns ) {

		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'title'		=> __( 'Title', 'cwwp-custom-snippets' ),
			'desc'		=> __( 'Description', 'cwwp-custom-snippets' ),
			'date' 		=> __( 'Date', 'cwwp-custom-snippets' )
		);

		return $columns;

	}

	/**
	 * Add data to the custom columns added to the custom snippets post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column The name of the custom column
	 * @param int $post_id The current post ID
	 */
	public function custom_columns( $column, $post_id ) {

		switch ( $column ) {
			case 'desc' :
				echo stripslashes( get_post_meta( absint( $post_id ), '_cwwp_code_snippet_desc', true ) );
				break;
		}

	}
	
	/**
	 * Filter out unnecessary row actions from the code snippets post table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Default row actions
	 * @return array $actions Amended row actions
	 */
	public function row_actions( $actions ) {
		
		if ( self::is_snippet_screen() ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}

		return $actions;

	}
	
	/**
	 * Contextualizes the post updated messages.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object
	 * @param array $messages Array of default post updated messages
	 * @return array $messages Amended array of post updated messages
	 */
	public function messages( $messages ) {

		global $post;

		$messages['cwwp-custom-snippets'] = array(
			0	=> '',
			1	=> __( 'Code snippet updated.', 'cwwp-custom-snippets' ),
			4	=> __( 'Code snippet updated.', 'cwwp-custom-snippets' ),
			6	=> __( 'Code snippet published.', 'cwwp-custom-snippets' ),
			7	=> __( 'Code snippet saved.', 'cwwp-custom-snippets' )
		);

		/** Return the amended array of post updated messages */
		return $messages;

	}
	
	/**
	 * Comments out the custom code snippet when it is trashed.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID
	 */
	public function trash( $post_id ) {
			
		/** Next we check to see if the snippet has been applied before - if so, overwrite it */
		$delimiter 	= '|';
		$expression = '/**##** Snippet ID ' . $post_id . ' **##**/';
		$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
		/** Grab our contents - we need to check and see if we already have a snippet */
		$handle 	= @fopen( $this->get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( $this->get_snippets_file_path() ) );
		
		/** If the snippet exists, overwrite it with our updated code */
		if ( preg_match( $regex, $contents, $matches ) ) {
			$commented = '';
			foreach ( explode( PHP_EOL, $matches[1] ) as $line )
				$commented .= '//' . $line . PHP_EOL;
			$contents = str_replace( $matches[1], rtrim( $commented ), $contents );
			@ftruncate( $handle, 0 );
			@fwrite( $handle, stripslashes( trim( $contents ) ) );
			@fclose( $handle );
		}

	}
	
	/**
	 * Uncomments out the custom code snippet when it is untrashed.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID
	 */
	public function untrash( $post_id ) {
			
		/** Next we check to see if the snippet has been applied before - if so, overwrite it */
		$delimiter 	= '|';
		$expression = '/**##** Snippet ID ' . $post_id . ' **##**/';
		$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
		/** Grab our contents - we need to check and see if we already have a snippet */
		$handle 	= @fopen( $this->get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( $this->get_snippets_file_path() ) );
		
		/** If the snippet exists, overwrite it with our updated code */
		if ( preg_match( $regex, $contents, $matches ) ) {
			$uncommented = '';
			foreach ( explode( PHP_EOL, $matches[1] ) as $line )
				$uncommented .= mb_substr( $line, 2 ) . PHP_EOL;
			$contents = str_replace( $matches[1], PHP_EOL . trim( $uncommented ) . PHP_EOL, $contents );
			@ftruncate( $handle, 0 );
			@fwrite( $handle, stripslashes( trim( $contents ) ) );
			@fclose( $handle );
		}

	}
	
	/**
	 * Deletes a custom code snippet when it is deleted from WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID
	 */
	public function delete( $post_id ) {
			
		/** Next we check to see if the snippet has been applied before - if so, overwrite it */
		$delimiter 	= '|';
		$expression = '/**##** Snippet ID ' . $post_id . ' **##**/';
		$regex		= $delimiter . preg_quote( $expression, $delimiter ) . '(.*?)' . preg_quote( $expression, $delimiter ) . $delimiter . 's';
		
		/** Grab our contents - we need to check and see if we already have a snippet */
		$handle 	= @fopen( $this->get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( $this->get_snippets_file_path() ) );
		
		/** If the snippet exists, overwrite it with our updated code */
		if ( preg_match( $regex, $contents, $matches ) ) {
			$contents = str_replace( $matches[0], '', $contents );
			@ftruncate( $handle, 0 );
			@fwrite( $handle, stripslashes( trim( $contents ) ) );
			@fclose( $handle );
		}

	}
	
	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
	
		return self::$instance;
	
	}
	
	/**
	 * Getter method for retrieving the main plugin filepath.
	 *
	 * @since 1.0.0
	 */
	public static function get_file() {
	
		return self::$file;
	
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

}

/** Instantiate the class */
$cwwp_custom_snippets = new CWWP_Custom_Snippets;