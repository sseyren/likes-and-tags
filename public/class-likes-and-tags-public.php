<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/public
 */
class Likes_and_Tags_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $likes_and_tags    The ID of this plugin.
	 */
	private $likes_and_tags;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The table name of the likes table.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $likes_table_name    The table name of the likes table.
	 */
	private $likes_table_name;

	/**
	 * The metadata key of likes.
	 * 
	 * @since	1.0.0
	 * @access	private
	 * @var		string	$metadata_key	The metadata key of likes.
	 * 
	 */
	private $metadata_key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $likes_and_tags		The name of the plugin.
	 * @param      string    $version				The version of this plugin.
	 * @param      string    $likes_table_name		The table name of likes table.
	 */
	public function __construct( $likes_and_tags, $version, $likes_table_name, $metadata_key ) {

		$this->likes_and_tags = $likes_and_tags;
		$this->version = $version;
		$this->likes_table_name = $likes_table_name;
		$this->metadata_key = $metadata_key;

	}

	/**
	 * Manipulating content for the like button.
	 * 
	 * @since	1.0.0
	 * @param	string	$content	The content.
	 * @return	string	Manipulated content.
	 */
	public function manipulate_content( $content ) {

		$end = '';
		if ( is_single() && in_the_loop() && is_main_query() ) {

			$current_user = wp_get_current_user();

			if ( $current_user->exists() ) {

				$post_id = get_the_ID();
				
				$url = add_query_arg(
				[
					"action" => "landt_like_post",
					"post" => $post_id
				],
				$_SERVER["REQUEST_URI"] );
				
				if ( $this->user_liked_already( $current_user->ID, $post_id ) )
					$end = '<p style="margin-top:20px"><a href="' . esc_url( $url ) . '">' . esc_html__( "Remove Like", "likes-and-tags" ) . '</a></p>';
				else
					$end = '<p style="margin-top:20px"><a href="' . esc_url( $url ) . '">' . esc_html__( "Like Post", "likes-and-tags" ) . '</a></p>';

			} else
				$end = '<p style="margin-top:20px">'. esc_html__( "You must logged in to give a like.", "likes-and-tags" ) . '</p>';

		}
		return $content . $end;

	}

	/**
	 * Handler for the like action.
	 * If user already liked a post and this action called again,
	 * user "like" will be removed on that post and tags.
	 * 
	 * @since	1.0.0
	 */
	public function like_post(){
		global $wpdb;
		
		$current_user = wp_get_current_user();
		
		if ( isset($_GET['action']) && $_GET['action'] === 'landt_like_post' && $current_user->exists() ) {

			$post_id = intval( isset($_GET['post']) ? $_GET['post'] : null );

			$post = get_post($post_id);
			if ( empty($post) )
				return;
			
			$post_tags = wp_get_post_tags( $post_id );

			if ( $liked_already = $this->user_liked_already( $current_user->ID, $post_id ) ) {

				$increase_value = -1;

				$wpdb->delete( $this->likes_table_name, [
					"id" => $liked_already->id
				] );

			} else {

				$increase_value = 1;
				
				$wpdb->insert( 
					$this->likes_table_name, 
					[
						'user_id' => $current_user->ID, 
						'post_id' => $post_id
					] 
				);

			}

			$this->increase_post_like( $post_id, $increase_value );

			$this->increase_tags_like( $post_tags, $increase_value );

		}

	}

	/**
	 * Returns the query result if $user_id liked $post_id.
	 * The function can return the row as an object, an associative array,
	 * or as a numerically indexed array.
	 * 
	 * @since	1.0.0
	 * @param	int		$user_id	Id of user.
	 * @param	int		$post_id	Id of post.
	 * @return	object|array	Query result.
	 */
	public function user_liked_already( $user_id, $post_id ){
		global $wpdb;

		$liked_already = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->likes_table_name WHERE user_id = %d AND post_id = %d",
				$user_id,
				$post_id
			)
		);

		return $liked_already;

	}

	/**
	 * Increasing post metadata which is specified by $this->metadata_key.
	 * 
	 * 
	 * @since	1.0.0
	 * @param	int			$post_id		The id of the post to be increased.
	 * @param	int			$value 			Increase value. It can be negative or zero.
	 */
	public function increase_post_like( $post_id, $value = 1 ) {
		
		$prev = (int)get_post_meta( $post_id, $this->metadata_key, true );
		update_post_meta( $post_id, $this->metadata_key, $prev + $value );

		/*
		Burası aşağıdaki gibi de olabilir; ama yukarıdakini tercih etmemin sebebi, aşağıdaki kodun
		uygulamanın önbellek mekanizmasını bozması. Önbelleği güncellemeyi (update_postmeta_cache)
		denedim fakat istediğim sonucu alamadım.

		global $wpdb;
		if ( get_post_meta( $post_id, $this->metadata_key ) ) {

			$update_post_like_count = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->postmeta SET meta_value = meta_value + %d WHERE meta_key = %s AND post_id = %d",
					(int)$value, $this->metadata_key, (int)$post_id
				)
			);
			
		} else
			add_post_meta( $post_id, $this->metadata_key, 1 );
		*/

	}

	/**
	 * Increasing post metadata which is specified by $this->metadata_key.
	 * 
	 * 
	 * @since	1.0.0
	 * @param	array		$tags		The id of the post to be increased.
	 * @param	int			$value		Increase value. It can be negative or zero.
	 */
	public function increase_tags_like( $tags = [], $value = 1 ) {

		foreach ( $tags as $tag ) {
			
			$prev = (int)get_term_meta( $tag->term_id, $this->metadata_key, true);
			update_term_meta( $tag->term_id, $this->metadata_key, $prev + (int)$value );

			/*
			Aynı şekilde burası böyle de olabilir...

			global $wpdb;

			if ( get_term_meta( $tag->term_id, $this->metadata_key ) ) {

				$update_post_like_count = $wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->termmeta SET meta_value = meta_value + %d WHERE meta_key = %s AND term_id = %d",
						(int)$value, $this->metadata_key, (int)$tag->term_id
					)
				);

			} else
				add_term_meta( $tag->term_id, $this->metadata_key, 1 );
			*/

		}

	}

	/**
	 * The function that runs when a post sent to trash.
	 * 
	 * @since	1.0.0
	 * @param	int		The post id.
	 */
	public function post_trash( $post_id ) {
		global $wpdb;

		$post_tags = wp_get_post_tags( $post_id );

		$increase_value = intval( get_post_meta( $post_id, $this->metadata_key, true ) ) * -1;

		$this->increase_tags_like( $post_tags, $increase_value );

		delete_post_meta( $post_id, $this->metadata_key );

		$wpdb->delete( $this->likes_table_name, [
			"post_id" => $post_id
		] );

	}

	/**
	 * The method that runs when a user deleted, for removing that users likes.
	 * 
	 * @since	1.0.0
	 * @param	$user_id	The user id.
	 */
	public function user_delete( $user_id ) {
		global $wpdb;

		$query_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM $this->likes_table_name WHERE user_id = %d",
				$user_id
			)
		);

		foreach ( $query_results as $post ) {

			$this->increase_post_like( $post->post_id, -1 );

			$post_tags = wp_get_post_tags( $post->post_id );
			$this->increase_tags_like( $post_tags, -1 );

		}

		$wpdb->delete( $this->likes_table_name, [
			"user_id" => $user_id
		] );
		
	}

	/**
	 * The method that runs when a tag deleted.
	 * 
	 * @since	1.0.0
	 * @param	$term	The term id.
	 */
	public function term_delete( $term ) {

		delete_term_meta( $term, $this->metadata_key );

	}

	/**
	 * The method that registers shortcodes.
	 * 
	 * @since	1.0.0
	 */
	public function register_shortcodes() {

		add_shortcode( "landt_top_tags", array( $this, "tags_list_shortcode" ) );

	}
	
	/**
	 * The method that defines [landt_top_tags] shortcode and properties.
	 * 
	 * @since	1.0.0
	 * @return	string	HTML output.
	 */
	public function tags_list_shortcode() {
		global $wpdb;

		$page_key = "landt_tag_list_page";

		$page_id = isset( $_GET[ $page_key ] ) && (int)$_GET[ $page_key ] > 0 ? (int)$_GET[ $page_key ] : 1;

		$page_size = 10;
		$offset = ( $page_id - 1 ) * $page_size;

		$tags = get_tags( [
			"meta_key" => $this->metadata_key,
			"orderby" => "meta_value",
			"order" => "DESC",
			"number" => $page_size,
			"offset" => $offset
		] );

		$output = "<div class=landt-top-tags><table>";
		
		if ( count( $tags ) > 0 ) {

			$next_page_tags = get_tags( [
				"meta_key" => $this->metadata_key,
				"orderby" => "meta_value",
				"order" => "DESC",
				"number" => $page_size,
				"offset" => $offset + $page_size
			] );

			$output .= "<tr><th colspan='4' style='text-align:center'>" . esc_html__( "Top Tags", "likes-and-tags" ) . " - " . esc_html__( "Page", "likes-and-tags" ) . " ". $page_id ."</th></tr>
			<tr>
				<th colspan='2'>" . esc_html__( "Name", "likes-and-tags" ) . "</th>
				<th>" . esc_html__( "Likes", "likes-and-tags" ) . "</th>
				<th>" . esc_html__( "Used", "likes-and-tags" ) . "</th>
			</tr>";
				
			foreach ( $tags as $tag ) {
	
				$likes = get_term_meta( $tag->term_id, $this->metadata_key, true );
				$output .= "<tr><td colspan='2'>$tag->name</td><td>$likes</td><td>$tag->count</td></tr>";
	
			}
			
			$prevurl = $page_id > 1 ? add_query_arg( $page_key, $page_id - 1, $_SERVER["REQUEST_URI"] ) : "#";
			$nexturl = count( $next_page_tags ) > 0 ? add_query_arg( $page_key, $page_id + 1, $_SERVER["REQUEST_URI"] ) : "#";

			$output .= "<tr>
				<td colspan='2'><a href='$prevurl'>← " . esc_html__( "Previous Page", "likes-and-tags" ) . "</a></td>
				<td colspan='2' style='text-align:right'><a href='$nexturl'>" . esc_html__( "Next Page", "likes-and-tags" ) . " →</a></td>
			</tr>";

		} else
			$output .= "<tr><td style='text-align:center'>" . esc_html__( "There are no liked tags yet.", "likes-and-tags" ) . "</td></tr>";
		
		return $output . "</table></div>";

	}

}
