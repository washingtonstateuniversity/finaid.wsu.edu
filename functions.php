<?php

class WSU_Student_Financial_Services_Theme {
	/**
	 * @var string String used for busting cache on scripts.
	 */
	var $script_version = '0.0.1';

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
			self::$instance->load_plugins();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Load "plugins" included with the theme.
	 */
	public function load_plugins() {
		require_once( dirname( __FILE__ ) . '/includes/site-actions-widget.php' );
	}

	/**
	 * Setup hooks to include.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_menu' ), 10 );
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register the additional menu used in the theme footer.
	 */
	public function register_menu() {
		register_nav_menu( 'footer', 'Footer' );
	}

	/**
	 * Register the sidebars and custom widgets used by the theme.
	 */
	public function register_sidebars() {
		register_widget( 'Site_Actions_Widget' );

		$footer_args = array(
			'name' => 'Actions',
			'id' => 'site-actions',
			'description' => 'Displays the action links on the top of every page.',
		);

		register_sidebar( $footer_args );
	}

	/**
	 * Enqueue the scripts used in the theme.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery' ), $this->script_version, true );
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
