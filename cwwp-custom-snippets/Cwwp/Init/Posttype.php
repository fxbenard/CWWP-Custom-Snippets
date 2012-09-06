<?php
/**
 * Posttype class for Code With WP Custom Snippets.
 *
 * @since 1.0.0
 *
 * @package	Code With WP Custom Snippets
 * @author	Thomas Griffin
 */
class Cwwp_Init_Posttype {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Holds a copy of the registered post type.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $post_type;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	
		self::$instance = $this;
		
		add_action( 'init', array( $this, 'post_type' ), 0 );
		add_filter( 'manage_edit-cwwp-custom-snippets_columns', array( $this, 'columns' ) );
		add_filter( 'manage_cwwp-custom-snippets_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
	
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
			'menu_icon'				=> plugins_url( '/css/images/menu-icon.png', dirname( dirname( __FILE__ ) ) ),
			'supports' 				=> array( 'title' )
		);

		/** Register post type with args */
		self::$post_type = register_post_type( 'cwwp-custom-snippets', $args );
	
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
		
		if ( Cwwp_Init::is_snippet_screen() ) {
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