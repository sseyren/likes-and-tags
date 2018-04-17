<?php

/**
 * The file that defines the widgets class.
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */

/**
 * The widgets class.
 *
 * This is loads defined widget classes and registers them.
 *
 * @since      1.0.0
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */
class Likes_and_Tags_Widgets {

	/**
	 * Includes defined widget classes.
	 * 
	 * @since	1.0.0
	 */
	public function __construct() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-likes-and-tags-top-posts-widget.php';

	}

	/**
	 * Registers widgets.
	 * 
	 * @since	1.0.0
	 */
	public function register_widgets() {

		register_widget( "Likes_and_Tags_Top_Posts_Widget" );

	}

}