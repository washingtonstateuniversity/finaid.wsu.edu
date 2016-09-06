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
		require_once( __DIR__ . '/includes/site-actions-widget.php' );
	}

	/**
	 * Setup hooks to include.
	 */
	public function setup_hooks() {
		add_filter( 'spine_child_theme_version', array( $this, 'theme_version' ) );
		add_action( 'init', array( $this, 'register_menu' ), 10 );
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
		if ( is_front_page() ) {
			wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/home.js', array( 'jquery' ), $this->script_version, true );
		}

		$post = get_post();

		if ( isset( $post->post_content ) && strpos( $post->post_content, 'js-accordion' ) ) {
			wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/accordion.js', array( 'jquery' ), $this->script_version, true );
		}

		if ( isset( $post->post_content ) && strpos( $post->post_content, 'dropdown' ) ) {
			wp_enqueue_script( 'sfs-scripts', get_stylesheet_directory_uri() . '/js/table-toggle.js', array( 'jquery' ), $this->script_version, true );
		}
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

/**
 * Determine what should be displayed in the main header area.
 *
 * @return array List of elements for output in main header.
 */
function sfs_get_header_elements() {
	$sfs_headers = array();

	// Section title.
	if ( is_page() ) {
		// Retrieve the title of the top-level category for pages.
		$category = get_the_category();
		if ( $category ) {
			$category_parents = get_category_parents( $category[0]->term_id );
			$top_category = explode( '/', $category_parents );
			$section = rtrim( $top_category[0], '/' );
			if ( $section ) {
				$sfs_headers['section_title'] = $section;
			}
		} else {
			// Fall back to the page title if the page has no categories.
			$sfs_headers['section_title'] = get_the_title();
		}
	} else if ( is_singular( 'post' ) || ( is_archive() && ! is_post_type_archive( 'tribe_events' ) ) ) {
		// For posts and archive views (excluding events archives), use "Latest".
		$sfs_headers['section_title'] = 'Latest';
	} else if ( is_single() && ! is_singular( 'tribe_events' ) ) {
		// For all other post types except events, retrieve:
		// 1) a category name; or
		// 2) the post type name.
		$category = get_the_category();
		if ( $category ) {
			$sfs_headers['section_title'] = $category[0]->cat_name;
		} else {
			$post_type = get_post_type_object( get_post_type() );
			$sfs_headers['section_title'] = $post_type->labels->name;
		}
	} else {
		// Use the Spine parent theme's sub header value for anything else.
		$spine_main_header_values = spine_get_main_header();
		$sfs_headers['section_title'] = $spine_main_header_values['sub_header_default'];
	}

	// Page sup header.
	$page_sup = get_post_meta( get_the_ID(), 'sup-header', true );
	if ( $page_sup ) {
		$sfs_headers['page_sup'] = $page_sup;
	}

	// Page sub header.
	if ( is_singular( 'post' ) ) {
		// For posts, retrieve:
		// 1) a category name; or
		// 2) the post type name.
		$category = get_the_category();
		if ( $category ) {
			$sfs_headers['page_sub'] = $category[0]->cat_name;
		} else {
			$post_type = get_post_type_object( get_post_type() );
			$sfs_headers['page_sub'] = $post_type->labels->name;
		}
	} else if ( is_singular( 'tribe_events' ) ) {
		// For individual events, retrieve:
		// 1) an event category name; or
		// 2) "Events".
		$event_category = get_the_terms( get_queried_object_id(), 'tribe_events_cat' );
		if ( $event_category ) {
			$sfs_headers['page_sub'] = $event_category[0]->name;
		} else {
			$sfs_headers['page_sub'] = 'Events';
		}
	} else if ( is_post_type_archive( 'tribe_events' ) ) {
		if ( is_tax() ) {
			// Retrieve the term title for Event taxonomy archives.
			$sfs_headers['page_sub'] = single_term_title( '', false );
		} else {
			// Output "Full Calendar" for the main Events archive view.
			$sfs_headers['page_sub'] = 'Full Calendar';
		}
	} else if ( is_archive() ) {
		// Use the Spine parent theme's sub header value for anything else.
		$spine_main_header_values = spine_get_main_header();
		$sfs_headers['page_sub'] = $spine_main_header_values['sub_header_default'];
	} else {
		// For everything else, grab the Spine Main Header Bottom Header Text, or the post title
		$page_sub = get_post_meta( get_the_ID(), 'sub-header', true );
		$sfs_headers['page_sub'] = ( $page_sub ) ? $page_sub : get_the_title();
	}

	return apply_filters( 'sfs_theme_header_elements', $sfs_headers );
}
