<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site.
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, widgets, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */
class Likes_and_Tags {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Likes_and_Tags_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $likes_and_tags    The string used to uniquely identify this plugin.
	 */
	protected $likes_and_tags;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The table name of the likes table.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $landt_likes_table_name    The table name of the likes table.
	 */
	protected $likes_table_name;

	/**
	 * The metadata key of likes.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $metadata_key    The metadata key of likes.
	 */
	protected $metadata_key;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and start widgets and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $wpdb;

		if ( defined( 'LIKES_AND_TAGS_VERSION' ) ) {
			$this->version = LIKES_AND_TAGS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->likes_and_tags = 'likes-and-tags';

		if ( defined( 'LIKES_AND_TAGS_LIKES_TABLE_NAME' ) ) {
			$this->likes_table_name = $wpdb->prefix . LIKES_AND_TAGS_LIKES_TABLE_NAME;
		} else {
			$this->likes_table_name = $wpdb->prefix . 'userlike';
		}

		if ( defined( 'LIKES_AND_TAGS_LIKES_METADATA_KEY' ) ) {
			$this->metadata_key = LIKES_AND_TAGS_LIKES_METADATA_KEY;
		} else {
			$this->metadata_key = "landt_like_count";
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();
		$this->define_widgets();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Likes_and_Tags_Loader. Orchestrates the hooks of the plugin.
	 * - Likes_and_Tags_i18n. Defines internationalization functionality.
	 * - Likes_and_Tags_Public. Defines all hooks for the public side of the site.
	 * - Likes_and_Tags_Widgets. Defines all widgets.
	 * 
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-likes-and-tags-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-likes-and-tags-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-likes-and-tags-public.php';

		/**
		 * The class responsible for defining all widgets.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-likes-and-tags-widgets.php';

		$this->loader = new Likes_and_Tags_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Likes_and_Tags_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Likes_and_Tags_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Likes_and_Tags_Public( $this->get_likes_and_tags(), $this->get_version(), $this->get_likes_table_name(), $this->get_metadata_key() );

		$this->loader->add_action( "init", $plugin_public, "like_post" );
		$this->loader->add_action( "init", $plugin_public, "register_shortcodes" );
		$this->loader->add_action( "wp_trash_post", $plugin_public, "post_trash" );
		$this->loader->add_action( "delete_user", $plugin_public, "user_delete" );
		$this->loader->add_action( "delete_term", $plugin_public, "term_delete" );

		$this->loader->add_filter( "the_content", $plugin_public, "manipulate_content" );

	}

	/**
	 * Register all of the hooks related to the widgets
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_widgets() {

		$plugin_widget = new Likes_and_Tags_Widgets( $this->get_likes_and_tags(), $this->get_version(), $this->get_likes_table_name() );

		$this->loader->add_action( 'widgets_init', $plugin_widget, 'register_widgets' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_likes_and_tags() {
		return $this->likes_and_tags;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Likes_and_Tags_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the table name of the likes table.
	 *
	 * @since     1.0.0
	 * @return    string    The table name of the likes table.
	 */
	public function get_likes_table_name() {
		return $this->likes_table_name;
	}

	/**
	 * Retrieve the metadata key of likes.
	 *
	 * @since     1.0.0
	 * @return    string    The metadata key of likes.
	 */
	public function get_metadata_key() {
		return $this->metadata_key;
	}

}
