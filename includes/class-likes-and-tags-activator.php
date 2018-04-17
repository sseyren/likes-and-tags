<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */
class Likes_and_Tags_Activator {

	/**
	 * This method creates or updates a database table which includes likes of users;
	 * and recalculates amounts of likes for deleted posts, tags or users.
	 *
	 * @since	1.0.0
	 * @param	$likes_table_name	Table name of current likes table.
	 * @param	$metadata_key		The metadata key of likes.
	 */
	public static function activate( $likes_table_name, $metadata_key ) {
		global $wpdb;

		$landt_db_version = "1.0";
		$charset_collate = $wpdb->get_charset_collate();
		$likes_table_name = $wpdb->prefix . $likes_table_name;

		$sql = "CREATE TABLE $likes_table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			post_id bigint(20) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'landt_db_version', $landt_db_version );



		$current_likes = $wpdb->get_results( "SELECT post_id FROM $likes_table_name" );

		$post_likes = $term_likes = [];

		foreach ( $current_likes as $row ) {
			$post_id = $row->post_id;

			$post_likes[ $post_id ] += 1;

			$post_tags = wp_get_post_tags( $post_id );
			
			foreach ( $post_tags as $tag )
				$term_likes[ $tag->term_id ] += 1;

		}

		foreach ( $post_likes as $post_id => $likes ) 
			update_post_meta( $key, $metadata_key, $likes );

		foreach ( $term_likes as $term_id => $likes )
			update_term_meta( $term_id, $metadata_key, $likes );
		
	}

}
