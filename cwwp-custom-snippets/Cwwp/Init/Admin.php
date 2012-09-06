<?php
/**
 * Admin class for Code With WP Custom Snippets.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init_Admin {

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
		
		add_action( 'current_screen', array( $this, 'screen' ) );
		add_action( 'submitpost_box', array( $this, 'snippet' ), 0 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_action( 'wp_trash_post', array( $this, 'trash' ) );
		add_action( 'untrash_post', array( $this, 'untrash' ) );
		add_action( 'delete_post', array( $this, 'delete' ) );
	
	}
	
	/**
	 * Sets the screen layout to one column on the snippets add/edit screen
	 * and adds filter for change the default title text.
	 *
	 * @since 1.0.0
	 */
	public function screen() {
	
		if ( Cwwp_Init::is_snippet_add_edit_screen() ) {
			if ( 1 !== get_user_option( 'screen_layout_' . Cwwp_Init_Posttype::$post_type->name, get_current_user_id() ) )
				update_user_option( get_current_user_id(), 'screen_layout_' . Cwwp_Init_Posttype::$post_type->name, 1, true );
				
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
		
		if ( ! Cwwp_Init::is_snippet_screen() )
			return;
		
		settings_errors( 'cwwp-snippets' );
		wp_nonce_field( 'cwwp-snips', 'cwwp-snips' );
		
		?>
		<h4 class="top-title"><?php _e( 'Code Snippet', 'cwwp-custom-snippets' ); ?></h4>
		<p><?php printf( __( 'Paste your code snippet into the code editor below. Leave out any opening (%s) and closing (%s) php tags.', 'cwwp-custom-snippets' ), '<code><strong>&lt;?php</strong></code>', '<code><strong>?&gt;</strong></code>' ); ?></p>
		<textarea id="cwwp-code-snippet" name="cwwp-code-snippet" spellcheck="false"><?php echo stripslashes( trim( self::get_code_snippet( $post->ID ) ) ); ?></textarea>
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
			$handle 	= fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'ab+' );
			$contents 	= fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
			
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
			$handle 	= fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'ab+' );
			$contents 	= fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
		
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
	public static function get_code_snippet( $id ) {
	
		/** Get the contents of our custom snippets file */
		$handle 	= @fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'rb' );
		$contents 	= @fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
		
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
		$handle 	= @fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
		
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
		$handle 	= @fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
		
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
		$handle 	= @fopen( Cwwp_Init_Snippets::get_snippets_file_path(), 'ab+' );
		$contents 	= @fread( $handle, filesize( Cwwp_Init_Snippets::get_snippets_file_path() ) );
		
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
	 *
	 * @return object $instance The object instance
	 */
	public static function get_instance() {
	
		return self::$instance;
	
	}
	
}