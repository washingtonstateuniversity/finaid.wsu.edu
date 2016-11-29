<?php

class WSU_Student_Financial_Services_Theme {
	/**
	 * @var string String used for busting cache on scripts.
	 */
	var $script_version = '0.0.7';

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
		add_filter( 'body_class', array( $this, 'browser_body_class' ) );
		add_shortcode( 'sfs_cost_tables', array( $this, 'display_sfs_cost_tables' ) );
		add_action( 'wp_ajax_nopriv_cost_tables', array( $this, 'cost_tables_ajax_callback' ) );
		add_action( 'wp_ajax_cost_tables', array( $this, 'cost_tables_ajax_callback' ) );
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

		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'sfs_cost_tables' ) ) {
			wp_enqueue_script( 'sfs-cost-tables', get_stylesheet_directory_uri() . '/js/cost-tables.js', array( 'jquery' ), $this->script_version, true );
			wp_localize_script( 'sfs-cost-tables', 'cost_tables', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'sfs-cost-tables' ),
			) );
		}

		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wsuwp_toc' ) ) {
			$body_classes = get_post_meta( $post->ID, '_wsuwp_body_class', true );

			if ( false !== strpos( $body_classes, 'convert-toc-to-select' ) ) {
				wp_enqueue_script( 'sfs-convert-toc', get_stylesheet_directory_uri() . '/js/toc-select.js', array( 'wsuwp-toc-generator', 'jquery' ), $this->script_version, true );
			}
		}
	}

	/**
	 * Apply 'gecko' as a body class on the home page for Firefox users.
	 *
	 * Pretty hacky, but perhaps less so than most other methods.
	 */
	public function browser_body_class( $classes ) {
		global $is_gecko;

		if ( is_front_page() && $is_gecko ) {
			$classes[] = 'gecko';
		}

		return $classes;
	}

	/**
	 * Display a form for viewing cost of attendance tables.
	 *
	 */
	public function display_sfs_cost_tables() {
		$latest_pullman_undergrad_table = false;
		$sessions = array();
		$campuses = array();
		$careers = array();

		$table_query = new WP_Query( array( 'post_type' => 'tablepress_table', 'posts_per_page' => -1 ) );

		if ( $table_query->have_posts() ) {
			while ( $table_query->have_posts() ) {
				$table_query->the_post();
				$title = get_the_title();

				if ( preg_match( '/[0-9]{4}-[0-9]{4}/', $title, $session ) || preg_match( '/Summer [0-9]{4}/', $title, $session ) ) {
					if ( ! in_array( $session[0], $sessions, true ) ) {
						$sessions[] = $session[0];
					}

					$meta = json_decode( get_post_meta( get_the_ID(), '_tablepress_table_options', true ), true );

					if ( '' !== $meta['extra_css_classes'] ) {
						$table_classes = explode( ' ', $meta['extra_css_classes'] );

						foreach ( $table_classes as $class ) {
							if ( false !== strpos( $class, 'campus' ) ) {
								$campus_name = substr( $class, 7 );
								$campuses[ $campus_name ] = $class;
							}

							if ( false !== strpos( $class, 'path' ) ) {
								$career_name = substr( $class, 5 );
								$careers[ $career_name ] = $class;
							}
						}

						// Try to find the most recent cost table for Pullman Campus Undergraduates.
						$tablepress_model = new TablePress_Table_Model();
						$table_options = $tablepress_model->_debug_get_tables();
						$pullman_undergrad_classes = array( 'campus-pullman', 'path-undergrad' );
						sort( $pullman_undergrad_classes );
						sort( $table_classes );
						if ( is_array( $table_options ) && isset( $table_options['table_post'] ) &&
							 $pullman_undergrad_classes === $table_classes && false !== strpos( $title, $sessions[0] ) ) {
							$table_ids = array_flip( $table_options['table_post'] );
							$latest_pullman_undergrad_table = $table_ids[ get_the_ID() ];
						}
					}
				}
			}
		}
		wp_reset_postdata();

		asort( $campuses );
		asort( $careers );

		ob_start();
		?>
		<form class="sfs-cost-tables flex-form">

			<div>
				<label for="cost-table-session">Year/Session</label><br />
				<div class="select-wrap">
					<select id="cost-table-session" name="session">
						<option value="">- Select -</option>
						<?php foreach ( $sessions as $index => $session ) { ?>
						<option value="<?php echo esc_attr( $session ); ?>"<?php if ( 0 === $index ) { echo ' selected="selected"'; } ?>><?php echo esc_html( $session ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div>
				<label for="cost-table-campus">Campus</label><br />
				<div class="select-wrap">
					<select id="cost-table-campus" name="campus">
						<option value="">- Select -</option>
						<?php foreach ( $campuses as $name => $value ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php if ( 'campus-pullman' === $value ) { echo ' selected="selected"'; } ?>><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div>
				<label for="cost-table-career">Career</label><br />
				<div class="select-wrap">
					<select id="cost-table-career" name="career">
						<option value="">- Select -</option>
						<?php foreach ( $careers as $name => $value ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php if ( 'path-undergrad' === $value ) { echo ' selected="selected"'; } ?>><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

		</form>

		<div class="cost-table-placeholder">
			<?php
			if ( $latest_pullman_undergrad_table ) {
				tablepress_print_table( 'id=' . $latest_pullman_undergrad_table );
			}
			?>
		</div>
		<?php

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Provide a filter for searching post titles.
	 */
	public function title_contains( $where, &$wp_query ) {
		global $wpdb;

		if ( $search_term = $wp_query->get( 'title_contains' ) ) {
			$search_term = $wpdb->esc_like( $search_term );
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $search_term . '%\'';
		}

		return $where;
	}

	/**
	 * Handle the ajax callback for the cost of attendance tables form.
	 */
	public function cost_tables_ajax_callback() {
		check_ajax_referer( 'sfs-cost-tables', 'nonce' );

		if ( class_exists( 'TablePress' ) ) {

			$tablepress_model = new TablePress_Table_Model();
			$table_options = $tablepress_model->_debug_get_tables();

			if ( is_array( $table_options ) && isset( $table_options['table_post'] ) ) {
				$table_ids = array_flip( $table_options['table_post'] );

				$table_query_args = array(
					'post_type' => 'tablepress_table',
					'posts_per_page' => -1,
					'title_contains' => sanitize_text_field( $_POST['session'] ),
				);

				add_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

				$table_query = new WP_Query( $table_query_args );

				remove_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

				if ( $table_query->have_posts() ) {
					while ( $table_query->have_posts() ) {
						$table_query->the_post();

						$meta = json_decode( get_post_meta( get_the_ID(), '_tablepress_table_options', true ), true );

						if ( '' !== $meta['extra_css_classes'] ) {
							$table_classes = explode( ' ', $meta['extra_css_classes'] );
							$selected_classes = array( sanitize_text_field( $_POST['campus'] ), sanitize_text_field( $_POST['career'] ) );

							sort( $table_classes );
							sort( $selected_classes );

							if ( $table_classes === $selected_classes ) {
								$tablepress = TablePress::load_controller( 'frontend' );
								$id = $table_ids[ get_the_ID() ];

								$table = $tablepress->shortcode_table( array( 'id' => $id ) );

								// We only want one table.
								break;
							}
						}
					}
				}

				wp_reset_postdata();

				if ( ! $table ) {
					switch ( $_POST['updated'] ) {
						case 'session': $field = 'campus or career'; break;
						case 'campus': $field = 'session or career'; break;
						case 'career': $field = 'session or campus'; break;
					}

					$table = '<p>Please select another ' . $field . ' option</p>';
				}
			} else {
				$table = '<p>Something</p>';
			}

			echo wp_json_encode( $table );
		}

		exit();
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
	$sfs_headers = array(
		'section_title' => '',
		'page_sup' => '',
		'page_sub' => '',
	);

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
	}

	if ( '' === $sfs_headers['section_title'] ) {
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
