<?php
/**
*
* Word of the day widget
*
* @package				Word of the Day
* @author				Roger Song <roger.song.ny@gmail.com>
* @license				GPL-2.0+
* @link					https://github.com/RogerSong/word-of-the-day
* @copyright			2015 Roger Song, Roger Song LLC
*
* @wordpress-plugin
* Plugin Name:			Word of the Day
* Plugin URI:			https://github.com/RogerSong/word-of-the-day
* Description:			Creates a custom post type called 'word of the day' and displays the latest post inside a widget
* Version:				1.0
* Author:				Roger Song
* Author URI:			http://rogersong.com
* Text Domain:			word-of-the-day
* License:				GPL-2.0+
* License URI:			http://www.gnu.org/licenses/gpl-2.0.txt
* GitHub Plugin URI:	https://github.com/RogerSong/word-of-the-day
* GitHub Branch:		master
*
**/


/********** Blocking direct access to php file **********/
if ( ! defined( 'WPINC' ) ) {
	die;
}


/********** Register Word of the Day custom post type **********/
function wotd_custom_post_type() {
	$labels = array(
		'name'                => _x( 'Word of the Day', 'Post Type General Name' ),
		'singular_name'       => _x( 'Word of the Day', 'Post Type Singular Name' ),
		'menu_name'           => __( 'Word of the Day' ),
	);
	$args = array(
		'label'               => __( 'wotd', 'text_domain' ),
		'description'         => __( 'Word of the day', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'wotd', $args );
}
// Hook into the 'init' action
add_action( 'init', 'wotd_custom_post_type', 0 );


/********** WOTD WIDGET **********/
// Creating the widget 
class wotd_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
		// Widget information
		'wotd_widget', 
		__('Word of the Day Widget', 'wotd_widget_domain'), 
		array( 'description' => __( 'Word of the Day widget', 'wotd_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// Grabbing the latest post from Word of the Day custom post type
		$post_args = array(
			'post_type' => 'wotd',
			'order' => 'DESC',
			'order_by' => 'post_date',
			'posts_per_page' => '1'
		);
		$posts = get_posts( $post_args );

		// Output for widget
		foreach( $posts as $post ) : ?>

			<div class="wotd_header">
				<?php
				// Checking if the WOTD post has an image. Otherwise, serve default WOTD image.
				if (has_post_thumbnail( $post->ID )) {
					echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'wotd_feat_img' ) );
				} else {
					echo '<img src="'. plugins_url( 'images/wotd-default.jpg', __FILE__ ) . '" class="wotd_feat_img">';
				}
				?>
				<div class="wotd_title_wrapper">
					<span class="wotd_title">
						<?php echo get_the_title($post->ID); ?>
					</span>
				</div>
			</div>

			<div class="wotd_definition">
				<?php
				// Grabbing body content from WOTD post
				$thispost = get_post( $post->ID );
				$content = $thispost->post_content;
				echo $content
				?>
			</div>

		<?php
		endforeach; 

		echo $args['after_widget'];
	}
		
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Word of the Day', 'wotd_widget_domain' );
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	
	// Updating widget and replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wotd_widget ends here

// Register and load the widget
function wotd_load_widget() {
	register_widget( 'wotd_widget' );
}
add_action( 'widgets_init', 'wotd_load_widget' );


/********** Loading Styles **********/
function wotd_styles()
{
	wp_register_style( 'wotd-style', plugins_url( 'css/wotd-style.css', __FILE__ ));
	wp_enqueue_style( 'wotd-style' );
}
add_action( 'wp_enqueue_scripts', 'wotd_styles' );

?>