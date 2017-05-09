<?php

class WSU_Student_Financial_Services_COA_Tables_Shortcode {
	/**
	 * @since 0.1.0
	 *
	 * @var WSU_Student_Financial_Services_COA_Tables_Shortcode
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance and initiate hooks when called the first time.
	 *
	 * @since 0.1.0
	 *
	 * @return \WSU_Student_Financial_Services_COA_Tables_Shortcode
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Student_Financial_Services_COA_Tables_Shortcode;
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
		add_shortcode( 'sfs_cost_tables', array( $this, 'display_sfs_cost_tables' ) );
		add_action( 'wp_ajax_nopriv_cost_tables', array( $this, 'cost_tables_ajax_callback' ) );
		add_action( 'wp_ajax_cost_tables', array( $this, 'cost_tables_ajax_callback' ) );
	}

	/**
	 * Set up the options for the cost of attendance table lookup form.
	 *
	 * @since 0.1.0
	 *
	 * @param string $session The session being requested (table titles are searched for matches).
	 * @param array  $classes The campus and career path being requested (table classes are searched for matches).
	 *
	 * @return array Table ID and class arrays.
	 */
	public function form_options_setup( $session, $classes ) {
		if ( ! class_exists( 'TablePress' ) || '' === $session ) {
			return;
		}

		$tablepress_model = new TablePress_Table_Model();
		$table_options = $tablepress_model->_debug_get_tables();

		if ( ! is_array( $table_options ) && ! isset( $table_options['table_post'] ) ) {
			return;
		}

		$table_ids = array_flip( $table_options['table_post'] );
		$table_id = false;
		$sessions = array();
		$campuses = array();
		$careers = array();
		$available_campuses = array();
		$available_careers = array();

		$table_query_args = array(
			'post_type' => 'tablepress_table',
			'posts_per_page' => -1,
		);

		$data = array();

		$table_query = new WP_Query( $table_query_args );

		if ( $table_query->have_posts() ) {
			while ( $table_query->have_posts() ) {
				$table_query->the_post();

				$title = get_the_title();
				$session_in_title = strpos( $title, $session );

				// Build the array of session options.
				if ( preg_match( '/[0-9]{4}-[0-9]{4}/', $title, $session_prefix ) || preg_match( '/Summer [0-9]{4}/', $title, $session_prefix ) ) {
					if ( ! in_array( $session_prefix[0], $sessions, true ) ) {
						$sessions[] = $session_prefix[0];
					}
				}

				$table_meta = json_decode( get_post_meta( get_the_ID(), '_tablepress_table_options', true ), true );

				if ( '' === $title || '' === $table_meta['extra_css_classes'] ) {
					continue;
				}

				$table_classes = explode( ' ', $table_meta['extra_css_classes'] );

				foreach ( $table_classes as $class ) {

					if ( false !== strpos( $class, 'campus-' ) ) {
						// Build the array of all campus options.
						$campus_name = substr( $class, 7 );
						$campuses[ $campus_name ] = $class;

						// Build the array of campus options offered during the default session.
						if ( false !== $session_in_title && ! in_array( $class, $available_campuses, true ) ) {
							$available_campuses[] = $class;
						}
					}

					if ( false !== strpos( $class, 'path-' ) ) {
						// Build the array of all career path options.
						$career_name = substr( $class, 5 );
						$careers[ $career_name ] = $class;

						// Build the array of career path options offered during the default session at the default campus.
						if ( false !== $session_in_title && ! in_array( $class, $available_careers, true ) && in_array( $classes[0], $table_classes, true ) ) {
							$available_careers[] = $class;
						}
					}
				}

				// Try to find a table that meets all the critera.
				if ( false !== $session_in_title && empty( array_diff( $classes, $table_classes ) ) ) {
					$table_id = $table_ids[ get_the_ID() ];
				}
			}

			wp_reset_postdata();
		}

		$data = array(
			'sessions' => $sessions,
			'campuses' => $campuses,
			'careers' => $careers,
			'table_id' => $table_id,
			'available_campuses' => $available_campuses,
			'available_careers' => $available_careers,
		);

		return $data;
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
			'default_campus' => 'campus-Pullman',
			'default_career' => 'path-undergrad',
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( ! $atts['default_session'] ) {
			return '';
		}

		$default_session = sanitize_text_field( $atts['default_session'] );
		$default_campus = sanitize_text_field( $atts['default_campus'] );
		$default_career = sanitize_text_field( $atts['default_career'] );

		$data = $this->form_options_setup( $default_session, array( $default_campus, $default_career ) );

		if ( ! $data ) {
			return '';
		}

		wp_enqueue_script( 'sfs-cost-tables', get_stylesheet_directory_uri() . '/js/cost-tables.min.js', array( 'jquery' ), WSU_Student_Financial_Services_Theme()->theme_version(), true );

		wp_localize_script( 'sfs-cost-tables', 'cost_tables', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'sfs-cost-tables' ),
		) );

		asort( $data['campuses'] );
		asort( $data['careers'] );

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
						<?php foreach ( $data['campuses'] as $name => $value ) { ?>
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
						<?php foreach ( $data['careers'] as $name => $value ) { ?>
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
	 * Provide a filter for searching post titles.
	 *
	 * @since 0.0.8
	 *
	 * @param string   $where     The original where clause of the query.
	 * @param WP_Query &$wp_query The WP_Query instance.
	 */
	public function title_contains( $where, &$wp_query ) {
		global $wpdb;

		$search_term = $wp_query->get( 'title_contains' );

		if ( $search_term ) {
			$search_term = $wpdb->esc_like( $search_term );
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $search_term . '%\'';
		}

		return $where;
	}

	/**
	 * Attempt to find a table matching the given critera.
	 *
	 * @since 0.0.8
	 * @since 0.1.0 Used exclusively for AJAX requests.
	 *
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
		$table_id = false;
		$available_campuses = array();
		$available_careers = array();

		$table_query_args = array(
			'post_type' => 'tablepress_table',
			'posts_per_page' => -1,
			'title_contains' => sanitize_text_field( $session ),
		);

		add_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

		$table_query = new WP_Query( $table_query_args );

		remove_filter( 'posts_where', array( $this, 'title_contains' ), 10, 2 );

		if ( $table_query->have_posts() ) {
			while ( $table_query->have_posts() ) {
				$table_query->the_post();

				$meta = json_decode( get_post_meta( get_the_ID(), '_tablepress_table_options', true ), true );

				if ( '' === $meta['extra_css_classes'] ) {
					continue;
				}

				$table_classes = explode( ' ', $meta['extra_css_classes'] );

				foreach ( $table_classes as $class ) {
					// Build an array of campus options offered during the given session.
					if ( false !== strpos( $class, 'campus-' ) && ! in_array( $class, $available_campuses, true ) ) {
						$available_campuses[] = $class;
					}

					// Build an array of career path options offered during the given session at the given campus.
					if ( false !== strpos( $class, 'path-' ) && ! in_array( $class, $available_careers, true ) && in_array( $classes[0], $table_classes, true ) ) {
						$available_careers[] = $class;
					}
				}

				// Try to find a table that meets all the critera.
				if ( empty( array_diff( $classes, $table_classes ) ) ) {
					$table_id = $table_ids[ get_the_ID() ];
				}
			}

			wp_reset_postdata();
		}

		$data = array(
			'table_id' => $table_id,
			'available_campuses' => $available_campuses,
			'available_careers' => $available_careers,
		);

		return $data;
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
			$table = $tablepress->shortcode_table( array(
				'id' => $data['table_id'],
			) );
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
}
