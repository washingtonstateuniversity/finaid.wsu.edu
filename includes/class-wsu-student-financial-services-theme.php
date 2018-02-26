<?php

class WSU_Student_Financial_Services_Theme {
	/**
	 * Track the version number of the theme for script enqueues.
	 *
	 * @since 0.0.1
	 *
	 * @var string String used for busting cache on scripts.
	 */
	public $script_version = '0.1.2';

	/**
	 * @since 0.0.1
	 *
	 * @var WSU_Student_Financial_Services_Theme
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance and initiate hooks when called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSU_Student_Financial_Services_Theme
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Student_Financial_Services_Theme();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
	 */
	public function setup_hooks() {
		add_filter( 'spine_child_theme_version', array( $this, 'theme_version' ) );
		add_action( 'init', array( $this, 'register_menu' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'body_class', array( $this, 'browser_body_class' ) );
		add_filter( 'wsuwp_content_syndicate_json', array( $this, 'announcements_html' ), 10, 2 );
	}

	/**
	 * Provide a theme version for use in cache busting.
	 *
	 * @since 0.0.1
	 *
	 * @return string
	 */
	public function theme_version() {
		return $this->script_version;
	}

	/**
	 * Register the additional menu used in the theme footer.
	 *
	 * @since 0.0.1
	 */
	public function register_menu() {
		register_nav_menu( 'footer', 'Footer' );
	}

	/**
	 * Enqueue the scripts used in the theme.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {
		if ( is_front_page() ) {
			wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/home.min.js', array( 'jquery' ), $this->script_version, true );
		}

		$post = get_post();

		if ( isset( $post->post_content ) && strpos( $post->post_content, 'js-accordion' ) ) {
			wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/accordion.min.js', array( 'jquery' ), $this->script_version, true );
		}

		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wsuwp_toc' ) ) {
			$body_classes = get_post_meta( $post->ID, '_wsuwp_body_class', true );

			if ( false !== strpos( $body_classes, 'convert-toc-to-select' ) ) {
				wp_enqueue_script( 'sfs-convert-toc', get_stylesheet_directory_uri() . '/js/toc-select.min.js', array( 'wsuwp-toc-generator', 'jquery' ), $this->script_version, true );
			}
		}
	}

	/**
	 * Apply 'gecko' as a body class on the home page for Firefox users.
	 *
	 * @since 0.0.3
	 *
	 * @param array $classes Original body classes.
	 *
	 * @return array $classes Modified body classes.
	 */
	public function browser_body_class( $classes ) {
		global $is_gecko;

		if ( is_front_page() && $is_gecko ) {
			$classes[] = 'gecko';
		}

		return $classes;
	}

	/**
	 * Provide a custom HTML template for use with syndicated content.
	 *
	 * @param string   $content The unfiltered content.
	 * @param stdClass $atts    Shortcode attributes.
	 *
	 * @return string Modified HTML to output.
	 */
	public function announcements_html( $content, $atts ) {
		return str_replace( '</a>', ' <span class="read-more">&raquo;&nbsp;Read&nbsp;More</span></a>', $content );
	}
}
