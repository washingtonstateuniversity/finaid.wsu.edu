<?php

class Give_Link_Widget extends WP_Widget {

	/**
	 * Register the widget officially through the parent class.
	 */
	public function __construct() {
		parent::__construct( 'give_link_widget', 'Give Link', array( 'description' => 'A "give" link to be displayed in the site footer' ) );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$default_instance = array(
			'url' => '',
			'text' => '',
		);

		$instance = shortcode_atts( $default_instance, $instance );

		if ( ! $instance['url'] || ! $instance['text'] ) {
			return;
		}

		?>
		<a href="<?php echo esc_url( $instance['url'] ); ?>" class="give-link">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 99.7 83.9"><path d="M79.9 17.8c-0.4-6.2-2.9-10.1-5-12.3C71.6 1.9 67 0 61.9 0c-4 0-8.2 1.2-11.9 3.4C46.3 1.2 42.1 0 38.1 0c-5.1 0-9.7 1.9-13 5.5 -2.1 2.2-4.5 6.2-5 12.3H0.3v66.1h99.4V17.8H79.9zM90.1 74.2H10V27.5h11.1c0 0.1 0 0.1 0 0.2h11.2c0-0.1 0-0.1-0.1-0.2l0 0 0 0c-0.9-2.5-1.4-5.1-1.4-7.8 0-0.7 0.1-1.3 0.1-1.9l0 0 0 0c0.8-6.4 5.4-7.8 9.9-6.6 0 0 0 0 0 0 0.2 0.1 0.5 0.1 0.7 0.2 0 0 0 0 0 0 1.2 0.4 2.4 1 3.5 1.8l0 0c0.2 0.1 0.4 0.3 0.6 0.5 0 0 0 0 0 0 1.3 1 2.3 2.2 3 3.4 0 0 0 0 0 0 0.1 0.2 0.2 0.4 0.3 0.7l0 0 0 0c0.4 1 0.7 2 0.7 3 0-1 0.3-2 0.7-3l0 0 0 0c0.1-0.2 0.2-0.4 0.3-0.6 0 0 0 0 0 0 0.7-1.3 1.8-2.5 3-3.4 0 0 0 0 0 0 0.2-0.2 0.4-0.3 0.6-0.5l0 0c1.1-0.8 2.3-1.4 3.5-1.8 0 0 0 0 0 0 0.2-0.1 0.5-0.2 0.7-0.2 0 0 0 0 0 0 4.9-1.3 10.1 0.5 10.1 8.5 0 2.7-0.5 5.3-1.4 7.8l0 0h0c0 0.1 0 0.1-0.1 0.2H79c0-0.1 0-0.1 0-0.2h11.1V74.2z" fill="#fff"/><path d="M65.5 35.3c-4.9 8.9-13.3 15.5-15.2 17.1 -0.1 0.1-0.2 0.1-0.2 0.2 0 0 0 0 0 0 0 0 0 0 0 0 -0.1-0.1-0.2-0.1-0.3-0.2 -1.9-1.6-10.3-8.2-15.2-17.1H22.6c1.7 4.3 4.3 8.7 7.7 13 4.9 6.1 10.2 10.4 12.2 12.1 0.2 0.1 0.3 0.2 0.4 0.3L50 67l7.1-6.3c0.1-0.1 0.2-0.2 0.4-0.3 2-1.6 7.3-6 12.2-12.1 3.4-4.3 6-8.6 7.7-13H65.5z" fill="#fff"/></svg>
			<?php echo esc_html( $instance['text'] ); ?>
		</a>
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
		$url = ! empty( $instance['url'] ) ? $instance['url'] : '';
		$text = ! empty( $instance['text'] ) ? $instance['text'] : '';

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>">URL</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>">Text</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" />
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
		$instance['url'] = ( ! empty( $new_instance['url'] ) ) ? esc_url_raw( $new_instance['url'] ) : '';
		$instance['text'] = ( ! empty( $new_instance['text'] ) ) ? sanitize_text_field( $new_instance['text'] ) : '';

		return $instance;
	}
}
