<?php 
/**
 * A widget to display places as a list.
 */
class PlacesWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'places_widget', // Base ID
			'PlacesWidget', // Name
			array( 'description' => __( 'Widgets lieux', 'mba-places-manager-locale' ), ) // Args
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
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		// Personnalisation du content
		$this->generateWidgetContent();

		echo $after_widget;
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
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Nouveau titre', 'mba-places-manager-locale' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titre :' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Widget content (Dynamic list of places)
	 */
	private function generateWidgetContent() {
		// TODO Ici coder le widget

	    $myplaces = array( 'post_type' => 'places', );
	    $loop = new WP_Query( $myplaces );

		echo '<ul>'; 

	    while ( $loop->have_posts() ) : $loop->the_post();
	        ?>
	        <li><a href="<?php the_permalink() ?>" title=""><?php the_title() ?></a></li>
	        <?php
	    endwhile; 

		echo '</ul>';
	}

} // class PlacesWidget
?>