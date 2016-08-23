<?php

class WSU_Student_Financial_Services_Theme {
	/**
	 * @var WSU_Student_Financial_Services_Theme
	 */
	private static $instance;
	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Student_Financial_Services_Theme
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Student_Financial_Services_Theme;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_menu' ), 10 );
	}

	/**
	 * Register the additional menu used in the theme footer.
	 */
	public function register_menu() {
		register_nav_menu( 'footer', 'Footer' );
	}

}
add_action( 'after_setup_theme', 'WSU_Student_Financial_Services_Theme' );
/**
 * Start things up.
 *
 * @return \WSU_Student_Financial_Services_Theme
 */
function WSU_Student_Financial_Services_Theme() {
	return WSU_Student_Financial_Services_Theme::get_instance();
}
