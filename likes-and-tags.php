<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Likes_and_Tags
 *
 * @wordpress-plugin
 * Plugin Name:       Likes and Tags
 * Plugin URI:        http://github.com/thesseyren/likes-and-tags
 * Description:       Adds like button for all posts. It contains a widget showing the best posts and a widget showing the best tags.
 * Version:           1.0.0
 * Author:            thesseyren
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       likes-and-tags
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'LIKES_AND_TAGS_VERSION', '1.0.0' );

/**
 * Table name of current likes table.
 */
define( 'LIKES_AND_TAGS_LIKES_TABLE_NAME', 'userlike' );

/**
 * The metadata key of likes.
 */
define( 'LIKES_AND_TAGS_LIKES_METADATA_KEY', 'landt_like_count' );

/**
 * The code that runs during plugin activation.
 */
function activate_likes_and_tags() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-likes-and-tags-activator.php';
	Likes_and_Tags_Activator::activate( LIKES_AND_TAGS_LIKES_TABLE_NAME, LIKES_AND_TAGS_LIKES_METADATA_KEY );

}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_likes_and_tags() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-likes-and-tags-deactivator.php';
	Likes_and_Tags_Deactivator::deactivate();

}

/**
 * The code that runs during plugin uninstalling.
 */
function uninstall_likes_and_tags() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-likes-and-tags-uninstaller.php';
	Likes_and_Tags_Uninstaller::uninstall( LIKES_AND_TAGS_LIKES_TABLE_NAME, LIKES_AND_TAGS_LIKES_METADATA_KEY );
	
}

register_activation_hook( __FILE__, 'activate_likes_and_tags' );
register_deactivation_hook( __FILE__, 'deactivate_likes_and_tags' );
register_uninstall_hook( __FILE__, 'uninstall_likes_and_tags' );

/**
 * The core plugin class that is used to define internationalization,
 * widgets, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-likes-and-tags.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_likes_and_tags() {

	$plugin = new Likes_and_Tags();
	$plugin->run();

}
run_likes_and_tags();
