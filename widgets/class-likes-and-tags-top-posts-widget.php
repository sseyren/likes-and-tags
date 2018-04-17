<?php

/**
 * The file that defines the Likes_and_Tags_Top_Posts_Widget widget.
 *
 * @since      1.0.0
 *
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/widgets
 */

/**
 * The Likes_and_Tags_Top_Posts_Widget widget.
 *
 * @since      1.0.0
 * @package    Likes_and_Tags
 * @subpackage Likes_and_Tags/widgets
 */
class Likes_and_Tags_Top_Posts_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'landt_top_posts_widget',
			esc_html__( 'Top Posts', 'likes-and-tags' ),
			array( 'description' => esc_html__( 'A Likes-and-Tags Top Posts widget.', 'likes-and-tags' ), )
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		/*
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.guid, $wpdb->postmeta.meta_value FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				AND $wpdb->postmeta.meta_key = %s
				AND $wpdb->posts.post_status = %s
				ORDER BY $wpdb->postmeta.meta_value DESC LIMIT %d",
				"landt_like_count",
				"publish",
				(int)$instance['list_size']
			)
		);
		*/
		
		$posts = get_posts( [
			"meta_key" => "landt_like_count",
			"meta_value" => 0,
			"meta_compare" => "!=",
			"orderby" => "meta_value",
			"order" => "DESC",
			"posts_per_page" => 10
		] );

		if ( count( $posts ) > 0 ) {

			echo "<ul>";
			foreach ( $posts as $post ) {
	
				$likes = get_post_meta( $post->ID, "landt_like_count", true );
				echo "<li><a href='" . esc_html( $post->guid ) . "'>" . esc_html( $post->post_title ) . " (" . esc_html( $likes ) . " " . esc_html__( "likes", "likes-and-tags" ) . ")</a></li>";
			
			}
			echo "</ul>";

		} else
			echo "<p>" . esc_html__( "There are no liked posts yet.", "likes-and-tags" )  . " :(</p>";

		echo $args['after_widget'];

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'likes-and-tags' );

		$list_size = empty( $instance['list_size'] ) ? 10 : (int)$instance['list_size'];

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'likes-and-tags' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>"><?php esc_attr_e( 'List Size:', 'likes-and-tags' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'list_size' ) ); ?>" type="number" value="<?php echo esc_attr( $list_size ); ?>">
		</p>
		<?php 

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		$list_size = (int)$new_instance['list_size'];
		$instance['list_size'] = $list_size > 0 ? $list_size : 10;

		return $instance;

	}

}