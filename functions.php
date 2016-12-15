<?php

class WSU_Student_Financial_Services_Theme {
	/**
	 *Track the version number of the theme for script enqueues.
	 *
	 * @since 0.0.1
	 *
	 * @var string String used for busting cache on scripts.
	 */
	var $script_version = '0.0.9';

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
			self::$instance = new WSU_Student_Financial_Services_Theme;
			self::$instance->load_plugins();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Load "plugins" included with the theme.
	 *
	 * @since 0.0.1
	 */
	public function load_plugins() {
		require_once( __DIR__ . '/includes/site-actions-widget.php' );
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
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
	 * Register the sidebars and custom widgets used by the theme.
	 *
	 * @since 0.0.1
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

		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'sfs_cost_tables' ) ) {
			wp_enqueue_script( 'sfs-cost-tables', get_stylesheet_directory_uri() . '/js/cost-tables.min.js', array( 'jquery' ), $this->script_version, true );
			wp_localize_script( 'sfs-cost-tables', 'cost_tables', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'sfs-cost-tables' ),
			) );
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
	 * Provide a filter for searching post titles.
	 *
	 * @since 0.0.8
	 *
	 * @param string   $where     The original where clause of the query.
	 * @param WP_Query &$wp_query The WP_Query instance.
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
	 * Cost of attendance tables query.
	 *
	 * @since 0.0.8
	 *
	 * @param string $type    Whether the default form is being set up or an AJAX request is being made.
	 * @param string $session The session being requested (table titles are searched for matches).
	 * @param array  $classes The campus and career path being requested (table classes are searched for matches).
	 *
	 * @return array Table ID and class arrays.
	 */
	public function cost_tables_query( $type, $session, $classes ) {
		if ( ! class_exists( 'TablePress' ) || '' === $session ) {
			return;
		}

		$tablepress_model = new TablePress_Table_Model();
		$table_options = $tablepress_model->_debug_get_tables();

		if ( ! is_array( $table_options ) && ! isset( $table_options['table_post'] ) ) {
			return;
		}

		$table_ids = array_flip( $table_options['table_post'] );
		$sessions = array();
		$available_campuses = array();
		$available_careers = array();
		$all_campuses = array();
		$all_careers = array();

		$table_query_args = array(
			'post_type' => 'tablepress_table',
			'posts_per_page' => -1,
		);

		if ( 'ajax_request' === $type ) {
			$table_query_args['title_contains'] = $session;
		}

		add_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

		$table_query = new WP_Query( $table_query_args );

		remove_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

		if ( $table_query->have_posts() ) {
			while ( $table_query->have_posts() ) {
				$table_query->the_post();

				$title = get_the_title();

				// Build array of session options.
				if ( 'default' === $type ) {
					if ( preg_match( '/[0-9]{4}-[0-9]{4}/', $title, $session_prefix ) || preg_match( '/Summer [0-9]{4}/', $title, $session_prefix ) ) {
						if ( ! in_array( $session_prefix[0], $sessions, true ) ) {
							$sessions[] = $session_prefix[0];
						}
					}
				}

				$meta = json_decode( get_post_meta( get_the_ID(), '_tablepress_table_options', true ), true );

				if ( '' !== $meta['extra_css_classes'] ) {
					$table_classes = explode( ' ', $meta['extra_css_classes'] );

					foreach ( $table_classes as $class ) {
						if ( false !== strpos( $class, 'campus-' ) ) {
							// Build an array of campus options offered during the default session.
							if ( 'ajax_request' === $type || ( 'default' === $type && false !== strpos( $title, $session ) ) ) {
								if ( ! in_array( $class, $available_campuses, true ) ) {
									$available_campuses[] = $class;
								}
							} else { // Build an array of all campus options.
								$campus_name = substr( $class, 7 );
								$all_campuses[ $campus_name ] = $class;
							}
						}

						if ( false !== strpos( $class, 'path-' ) ) {
							// Build an array of career path options offered during the default session at the default campus.
							if ( false !== strpos( $meta['extra_css_classes'], $classes[0] ) && ( 'ajax_request' === $type || ( 'default' === $type && false !== strpos( $title, $session ) ) ) ) {
								if ( ! in_array( $class, $available_careers, true ) ) {
									$available_careers[] = $class;
								}
							} else { // Build an array of all career path options
								$career_name = substr( $class, 5 );
								$all_careers[ $career_name ] = $class;
							}
						}
					}

					// Try to find a table that meets all the critera.
					sort( $classes );
					sort( $table_classes );

					if ( $classes === $table_classes ) {
						if ( 'default' === $type && false !== strpos( $title, $session ) ) {
							$table_id = false;
						} else {
							$table_id = $table_ids[ get_the_ID() ];
						}
					}
				}
			}
		}

		$data = array(
			'table_id' => $table_id,
			'available_campuses' => $available_campuses,
			'available_careers' => $available_careers,
		);

		if ( 'default' === $type ) {
			$data['sessions'] = $sessions;
			$data['all_campuses'] = $all_campuses;
			$data['all_careers'] = $all_careers;
		}

		return $data;

		wp_reset_postdata();
	}

	/**
	 * Display a form for viewing cost of attendance tables.
	 *
	 * @since 0.0.8
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string $html HTML output.
	 */
	public function display_sfs_cost_tables( $atts ) {
		$defaults = array(
			'default_session' => '',
			'default_campus' => 'campus-pullman',
			'default_career' => 'path-undergrad',
		);
		$atts = shortcode_atts( $defaults, $atts );

		if ( ! $atts['default_session'] ) {
			return '';
		}

		$default_session = sanitize_text_field( $atts['default_session'] );
		$default_campus = sanitize_text_field( $atts['default_campus'] );
		$default_career = sanitize_text_field( $atts['default_career'] );

		$data = $this->cost_tables_query( 'default', $default_session, array( $default_campus, $default_career ) );

		if ( ! $data ) {
			return '';
		}

		asort( $data['all_campuses'] );
		asort( $data['all_careers'] );

		ob_start();
		?>
		<form class="sfs-cost-tables flex-form">

			<div>
				<label for="cost-table-session">Year/Session</label><br />
				<div class="select-wrap">
					<select id="cost-table-session" name="session">
						<option value="">- Select -</option>
						<?php foreach ( $data['sessions'] as $session ) { ?>
						<option value="<?php echo esc_attr( $session ); ?>"<?php selected( $session, $default_session ); ?>><?php echo esc_html( $session ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div>
				<label for="cost-table-campus">Campus</label><br />
				<div class="select-wrap">
					<select id="cost-table-campus" name="campus">
						<option value="">- Select -</option>
						<?php foreach ( $data['all_campuses'] as $name => $value ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php
						// Disable campus options not offered during the default session.
						if ( ! in_array( $value, $data['available_campuses'], true ) ) {
							echo ' disabled';
						}
						selected( $value, $default_campus );
						?>><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div>
				<label for="cost-table-career">Career Path</label><br />
				<div class="select-wrap">
					<select id="cost-table-career" name="career">
						<option value="">- Select -</option>
						<?php foreach ( $data['all_careers'] as $name => $value ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php
						// Disable career options not offered during the default session at the default campus.
						if ( ! in_array( $value, $data['available_careers'], true ) ) {
							echo ' disabled';
						}
						selected( $value, $default_career );
						?>><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

		</form>

		<div class="cost-table-placeholder">
			<?php
			if ( $data['table_id'] ) {
				tablepress_print_table( 'id=' . $data['table_id'] );
			}
			?>
		</div>
		<?php

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Handle the ajax callback for the cost of attendance tables form.
	 *
	 * @since 0.0.8
	 */
	public function cost_tables_ajax_callback() {
		check_ajax_referer( 'sfs-cost-tables', 'nonce' );

		$session = sanitize_text_field( $_POST['session'] );
		$campus = sanitize_text_field( $_POST['campus'] );
		$career = sanitize_text_field( $_POST['career'] );

		$data = $this->cost_tables_query( 'ajax_request', $session, array( $campus, $career ) );

		if ( $data['table_id'] ) {
			// `tablepress_get_table()` won't work in this context because it is frontend-only.
			$tablepress = TablePress::load_controller( 'frontend' );
			$table = $tablepress->shortcode_table( array( 'id' => $data['table_id'] ) );
		} else {
			// Try to be helpful if no matching table is found.
			$campus_name = ucfirst( substr( $campus, 7 ) );
			$career_name = ucfirst( substr( $career, 5 ) );
			$notice = $career_name . ' is not offered at the ' . $campus_name . ' campus during the ' . $session . ' session.';

			switch ( $_POST['updated'] ) {
				case 'session':
					$table = '<p>' . $notice . ' Please select another session, or check the campus or career path drop-downs for other options offered during this session.</p>';
					break;
				case 'campus':
					$table = '<p>' . $notice . ' Please check the Career Path drop-down for other options offered at ' . $campus_name . ' during this session, or select another campus.</p>';
					break;
				case 'career':
					// Theoretically, no one should make it in here...
					$table = "<p>We don't seem to have an estimate for the selected options. Please try another combination.</p>";
					break;
			}
		}

		$data['table'] = $table;

		echo wp_json_encode( $data );

		exit();
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

add_action( 'after_setup_theme', 'WSU_Student_Financial_Services_Theme' );
/**
 * Start things up.
 *
 * @since 0.0.1
 *
 * @return \WSU_Student_Financial_Services_Theme
 */
function WSU_Student_Financial_Services_Theme() {
	return WSU_Student_Financial_Services_Theme::get_instance();
}

/**
 * Determine what should be displayed in the main header area.
 *
 * @since 0.0.1
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
	} elseif ( is_singular( 'post' ) || ( is_archive() && ! is_post_type_archive( 'tribe_events' ) ) ) {
		// For posts and archive views (excluding events archives), use "Latest".
		$sfs_headers['section_title'] = 'Latest';
	} elseif ( is_single() && ! is_singular( 'tribe_events' ) ) {
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
	} elseif ( is_singular( 'tribe_events' ) ) {
		// For individual events, retrieve:
		// 1) an event category name; or
		// 2) "Events".
		$event_category = get_the_terms( get_queried_object_id(), 'tribe_events_cat' );
		if ( $event_category ) {
			$sfs_headers['page_sub'] = $event_category[0]->name;
		} else {
			$sfs_headers['page_sub'] = 'Events';
		}
	} elseif ( is_post_type_archive( 'tribe_events' ) ) {
		if ( is_tax() ) {
			// Retrieve the term title for Event taxonomy archives.
			$sfs_headers['page_sub'] = single_term_title( '', false );
		} else {
			// Output "Full Calendar" for the main Events archive view.
			$sfs_headers['page_sub'] = 'Full Calendar';
		}
	} elseif ( is_archive() ) {
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
