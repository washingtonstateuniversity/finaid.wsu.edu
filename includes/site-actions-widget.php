<?php

class Site_Actions_Widget extends WP_Widget {

	/**
	 * Register the widget officially through the parent class.
	 */
	public function __construct() {
		parent::__construct( 'site_actions_widget', 'Site Action', array( 'description' => 'A link to be displayed at the top of every page' ) );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$default_instance = array(
			'action_link' => '',
			'action_text' => '',
		);

		$instance = shortcode_atts( $default_instance, $instance );

		?>
		<li>
			<a href="<?php echo esc_url( $instance['action_link'] ); ?>"><?php echo esc_html( $instance['action_text'] ); ?></a>
		</li>
		<?php
	}

	/**
	 * Display the form used to update the widget.
	 *
	 * @param array $instance The instance of the current widget form being displayed.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$action_link = ! empty( $instance['action_link'] ) ? $instance['action_link'] : '';
		$action_text = ! empty( $instance['action_text'] ) ? $instance['action_text'] : '';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'action_link' ); ?>">Action Link</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'action_link' ); ?>" name="<?php echo $this->get_field_name( 'action_link' ); ?>" type="text" value="<?php echo esc_attr( $action_link ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'action_text' ); ?>">Action Text</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'action_text' ); ?>" name="<?php echo $this->get_field_name( 'action_text' ); ?>" type="text" value="<?php echo esc_attr( $action_text ); ?>" />
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new instance of the widget being saved.
	 * @param array $old_instance Previous instance of the current widget.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['action_link'] = ( ! empty( $new_instance['action_link'] ) ) ? esc_url( $new_instance['action_link'] ) : '';
		$instance['action_text'] = ( ! empty( $new_instance['action_text'] ) ) ? sanitize_text_field( $new_instance['action_text'] ) : '';

		return $instance;
	}
}
