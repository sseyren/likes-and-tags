<?php

/**
 * Fired during plugin uninstalling.
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */

/**
 * Fired during plugin uninstalling.
 *
 * This class defines all code necessary to run during the plugin's uninstalling.
 *
 * @since      1.0.0
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/includes
 */
class Likes_and_Tags_Uninstaller {

	/**
	 * This method deletes term metadatas and post metadatas which counts likes
     * and drops the main table.
	 *
	 * @since	1.0.0
	 * @param	$likes_table_name	Table name of current likes table.
	 * @param	$metadata_key		The metadata key of likes.
	 */
	public static function uninstall( $likes_table_name, $metadata_key ) {
        global $wpdb;
        $likes_table_name = $wpdb->prefix . $likes_table_name;

        foreach ( [ "term", "post" ] as $type )
            delete_metadata( $meta_type = $type, $meta_key = $metadata_key, $delete_all = true );

        delete_option( "landt_db_version" );

        $wpdb->query( "DROP TABLE IF EXISTS $likes_table_name" );

	}

}
