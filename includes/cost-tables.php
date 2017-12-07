<?php

namespace WSU\Financial_Aid\Cost_Tables;

/**
 * Provides the Cost Tables post type slug.
 *
 * @since 0.1.0
 *
 * @return string
 */
function post_type_slug() {
	return 'cost-table';
}

/**
 * Provides the slugs for the Cost Tables associated taxonomies.
 *
 * @since 0.1.0
 *
 * @return string
 */
function taxonomies() {
	return array(
		array(
			'singular' => 'Session',
			'plural' => 'Sessions',
			'description' => '',
			'slug' => 'session',
		),
		array(
			'singular' => 'Campus',
			'plural' => 'Campuses',
			'description' => '',
			'slug' => 'campus',
		),
		array(
			'singular' => 'Career Path',
			'plural' => 'Career Paths',
			'description' => '',
			'slug' => 'career-path',
		),
	);
}

add_action( 'init', 'WSU\Financial_Aid\Cost_Tables\register_post_type' );
add_filter( 'pll_get_post_types', 'WSU\Financial_Aid\Cost_Tables\add_to_pll', 10, 2 );
add_action( 'add_meta_boxes_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\add_meta_boxes' );
add_action( 'admin_enqueue_scripts', 'WSU\Financial_Aid\Cost_Tables\admin_enqueue_scripts' );
add_action( 'save_post_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\save_post', 10, 2 );

add_action( 'init', 'WSU\Financial_Aid\Cost_Tables\register_taxonomies' );
add_action( 'init', 'WSU\Financial_Aid\Cost_Tables\register_meta', 25 );
foreach ( taxonomies() as $taxonomy ) {
	if ( 'campus' === $taxonomy['slug'] ) {
		continue;
	}

	add_action( "{$taxonomy['slug']}_edit_form_fields", 'WSU\Financial_Aid\Cost_Tables\edit_term_meta_fields', 10 );
	add_action( "edit_{$taxonomy['slug']}", 'WSU\Financial_Aid\Cost_Tables\save_term_fields' );
}
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Financial_Aid\Cost_Tables\taxonomy_meta_box' );

add_shortcode( 'sfs_cost_tables', 'WSU\Financial_Aid\Cost_Tables\display_sfs_cost_tables' );
add_action( 'wp_ajax_nopriv_cost_tables', 'WSU\Financial_Aid\Cost_Tables\ajax_callback' );
add_action( 'wp_ajax_cost_tables', 'WSU\Financial_Aid\Cost_Tables\ajax_callback' );

/**
 * Registers a post type for tracking cost of attendance data.
 *
 * @since 0.1.0
 */
function register_post_type() {
	$args = array(
		'labels' => array(
			'name' => 'Cost Tables',
			'singular_name' => 'Cost Table',
			'all_items' => 'All Cost Tables',
			'view_item' => 'View Cost Table',
			'add_new_item' => 'Add New Cost Table',
			'edit_item' => 'Edit Cost Table',
			'update_item' => 'Update Cost Table',
			'search_items' => 'Search Cost Tables',
			'not_found' => 'No Cost Tables found',
			'not_found_in_trash' => 'No Cost Tables found in Trash',
		),
		'description' => 'Cost of attendance breakdowns.',
		'public' => false,
		'show_ui' => true,
		'menu_position' => 7,
		'menu_icon' => 'dashicons-clipboard',
		'supports' => array(
			'title',
		),
	);

	\register_post_type( post_type_slug(), $args );
}

/**
 * Adds Polylang support to the Cost Tables post type.
 *
 * @since 0.1.0
 *
 * @param array $post_types Post types with Polylang support.
 *
 * @return array
 */
function add_to_pll( $post_types ) {
	$post_types[ post_type_slug() ] = post_type_slug();

	return $post_types;
}

/**
 * Adds the metaboxes used to capture attendance cost data.
 *
 * @since 0.1.0
 */
function add_meta_boxes() {
	add_meta_box(
		'cost-table-meta',
		'Attendance Cost Details',
		'WSU\Financial_Aid\Cost_Tables\display_cost_meta_box',
		post_type_slug(),
		'normal',
		'high'
	);
}

/**
 * Displays the metabox used to capture attendance cost data.
 *
 * @since 0.1.0
 *
 * @param WP_Post $post Object for the post currently being edited.
 */
function display_cost_meta_box( $post ) {
	wp_nonce_field( 'save-cost-table-meta', '_cost_table_meta_nonce' );

	$data = get_post_meta( $post->ID, '_cost_table_data', true );
	?>

	<table>

	<?php if ( $data ) { ?>

		<?php foreach ( $data as $row => $cells ) { ?>

		<?php // Output a row of buttons for deleting columns.
		if ( 0 === $row ) { ?>
		<tr>
			<td></td>
			<?php foreach ( $cells as $index => $cell ) { ?>
			<td>
				<button type="button" class="coa-meta-delete delete-column">Delete Column</button>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>

		<tr>
			<?php // Output a column of buttons for deleting rows. ?>
			<td>
				<button type="button" class="coa-meta-delete delete-row"">Delete Row</button>
			</td>

			<?php foreach ( $cells as $index => $cell ) { ?>
			<td>
				<input type="text" name="_cost_table_data[<?php echo esc_attr( $row ); ?>][]" autocomplete="off" value="<?php echo esc_attr( $cell ); ?>" />
			</td>
			<?php } ?>
		</tr>

		<?php } ?>

	<?php } else { ?>

		<tr>
			<td></td>
			<td>
				<button type="button" class="coa-meta-delete delete-column">Delete Column</button>
			</td>
			<td>
				<button type="button" class="coa-meta-delete delete-column">Delete Column</button>
			</td>
		</tr>
		<tr>
			<td>
				<button type="button" class="coa-meta-delete delete-row">Delete Row</button>
			</td>
			<td>
				<input type="text" name="_cost_table_data[0][]" autocomplete="off" value="" />
			</td>
			<td>
				<input type="text" name="_cost_table_data[0][]" autocomplete="off" value="" />
			</td>
		</tr>

	<?php } ?>

	</table>

	<button type="button" class="coa-meta-add add-row"><span>+</span> Add row</button>
	<button type="button" class="coa-meta-add add-column"><span>+</span> Add column</button>
	<?php
}

/**
 * Enqueues scripts and styles for the cost table metabox.
 *
 * @since 0.1.0
 *
 * @param string $hook The current admin page.
 */
function admin_enqueue_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && get_current_screen()->id !== post_type_slug() ) {
		return;
	}

	wp_enqueue_style( 'cost-tables', get_stylesheet_directory_uri() . '/admin-css/cost-tables.css', array(), null );
	wp_enqueue_script( 'cost-tables', get_stylesheet_directory_uri() . '/js/admin-cost-tables.min.js', array( 'jquery' ), null, true );
}

/**
 * Saves the cost of attendance data.
 *
 * @since 0.1.0
 *
 * @param int     $post_id ID of the post being saved.
 * @param WP_Post $post    Post object of the post being saved.
 */
function save_post( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	if ( ! isset( $_POST['_cost_table_meta_nonce'] ) || ! wp_verify_nonce( $_POST['_cost_table_meta_nonce'], 'save-cost-table-meta' ) ) {
		return;
	}

	$sanitized_data = array();
	$rendered_table = '';

	if ( isset( $_POST['_cost_table_data'] ) && '' !== $_POST['_cost_table_data'] ) {
		foreach ( $_POST['_cost_table_data'] as $row => $cells ) {
			foreach ( $cells as $index => $cell ) {
				$sanitized_data[ $row ][] = wp_kses_post( $cell );
			}
		}

		update_post_meta( $post_id, '_cost_table_data', $sanitized_data );
	} else {
		delete_post_meta( $post_id, '_cost_table_data' );
	}

	// Render a table from the sanitized data and save as the post content.
	if ( ! empty( $sanitized_data ) ) {
		$rendered_table = '<table>';

		foreach ( $sanitized_data as $row => $cells ) {
			$rendered_table .= '<tr>';

			foreach ( $cells as $index => $cell ) {
				$rendered_table .= ( 0 === $row ) ? '<th>' : '<td>';
				$rendered_table .= $cell;
				$rendered_table .= ( 0 === $row ) ? '</th>' : '</td>';
			}

			$rendered_table .= '</tr>';
		}

		$rendered_table .= '</table>';
	}

	// Unhook this function to prevent an infinite loop.
	remove_action( 'save_post_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\save_post', 10 );

	wp_update_post( array(
		'ID' => $post_id,
		'post_content' => $rendered_table,
	) );

	// Rehook this function.
	add_action( 'save_post_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\save_post', 10 );
}

/**
 * Registers taxonomies attached to the Cost Tables post type.
 *
 * @since 0.1.0
 */
function register_taxonomies() {
	foreach ( taxonomies() as $taxonomy ) {
		$args = array(
			'labels' => array(
				'name' => $taxonomy['plural'],
				'singular_name' => $taxonomy['singular'],
				'all_items' => 'All ' . $taxonomy['plural'],
				'edit_item' => 'Edit ' . $taxonomy['singular'],
				'view_item' => 'View ' . $taxonomy['singular'],
				'update_item' => 'Update ' . $taxonomy['singular'],
				'add_new_item' => 'Add New ' . $taxonomy['singular'],
				'new_item_name' => 'New ' . $taxonomy['singular'] . ' Name',
				'search_items' => 'Search ' . $taxonomy['plural'],
				'popular_items' => 'Popular ' . $taxonomy['plural'],
				'separate_items_with_commas' => 'Separate ' . $taxonomy['plural'] . ' with commas',
				'add_or_remove_items' => 'Add or remove ' . $taxonomy['plural'],
				'choose_from_most_used' => 'Choose from the most used ' . $taxonomy['plural'],
				'not_found' => 'No ' . $taxonomy['plural'] . ' found',
			),
			'description' => $taxonomy['description'],
			'public' => true,
			'hierarchical' => false,
			'show_admin_column' => true,
		);

		register_taxonomy( $taxonomy['slug'], post_type_slug(), $args );
	}
}

/**
 * Registers taxonomies attached to the Cost Tables post type.
 *
 * @since 0.1.0
 */
function term_meta_keys() {
	if ( function_exists( 'pll_languages_list' ) ) {
		$meta_keys = array();
		$languages = pll_languages_list();

		foreach ( $languages as $language ) {
			if ( 'en' === $language ) {
				continue;
			}

			$term = get_term_by( 'slug', $language, 'language' );

			$meta_keys[ "_$language-name" ] = array(
				'description' => $term->name . ' Name',
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'single' => true,
			);
		}

		return $meta_keys;
	}

	return array();
}

/**
 * Registers the meta keys used with terms for capturing language names.
 *
 * @since 0.1.0
 */
function register_meta() {
	if ( ! empty( term_meta_keys() ) ) {
		foreach ( term_meta_keys() as $key => $args ) {
			\register_meta( 'term', $key, $args );
		}
	}
}

/**
 * Captures language meta assigned to a term.
 *
 * @since 0.1.0
 *
 * @param WP_Term $term
 */
function edit_term_meta_fields( $term ) {
	$term_meta = get_registered_metadata( 'term', $term->term_id );

	foreach ( term_meta_keys() as $key => $meta ) {
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?></label>
			</th>
			<td>
				<input type="text"
					   name="<?php echo esc_attr( $key ); ?>"
					   id="<?php echo esc_attr( $key ); ?>"
					   value="<?php if ( isset( $term_meta[ $key ][0] ) ) { echo esc_attr( $term_meta[ $key ][0] ); } ?>" />
			</td>
		</tr>
		<?php
	}
}

/**
 * Saves the additional form fields when a term is updated.
 *
 * @since 0.1.0
 *
 * @param int $term_id The ID of the term being edited.
 */
function save_term_fields( $term_id ) {
	global $wp_list_table;

	if ( 'editedtag' !== $wp_list_table->current_action() ) {
		return;
	}

	// Reuse the default nonce that is checked in `edit-tags.php`.
	check_admin_referer( 'update-tag_' . $term_id );

	$keys = get_registered_meta_keys( 'term' );

	foreach ( term_meta_keys() as $key => $meta ) {
		if ( isset( $_POST[ $key ] ) && isset( $keys[ $key ] ) && isset( $keys[ $key ]['sanitize_callback'] ) ) {
			// Each piece of meta is registered with sanitization.
			update_term_meta( $term_id, $key, $_POST[ $key ] );
		}
	}
}

/**
 * Displays a meta box with the Select2 interface provided by the University Taxonomy plugin.
 *
 * @since 0.1.0
 *
 * @param array $post_types Post types and their associated taxonomies.
 */
function taxonomy_meta_box( $post_types ) {
	$post_types[ post_type_slug() ] = array_column( taxonomies(), 'slug' );

	return $post_types;
}

/**
 * Retrieves a cost table and the associated options.
 *
 * @since 0.1.0
 *
 * @param string $language The current page language, provided as a term slug.
 * @param string $session  The provided session slug.
 * @param string $campus   The provided campus slug.
 * @param string $career   The provided career slug.
 *
 * @return array
 */
function cost_table_query( $language, $session, $campus, $career ) {
	if ( ! $language || ! $session || ! $campus || ! $career ) {
		return;
	}

	// Set up the array of data to fill in and return.
	$data = array(
		'table' => false,
		'campuses' => array(),
		'careers' => array(),
	);

	// Find all tables for the given language and session.
	$query_args = array(
		'post_type' => post_type_slug(),
		'posts_per_page' => 60,
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'language',
				'field' => 'slug',
				'terms' => $language,
			),
			array(
				'taxonomy' => 'session',
				'field' => 'slug',
				'terms' => $session,
			),
		),
		'no_found_rows' => true,
	);

	add_filter( 'posts_groupby', '__return_false' );

	$query = new \WP_Query( $query_args );

	remove_filter( 'posts_groupby', '__return_false' );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			// Build the array of campus options offered during the given session.
			$campuses = get_the_terms( get_the_ID(), 'campus' );

			if ( $campuses && ! is_wp_error( $campuses ) ) {
				foreach ( $campuses as $term ) {
					if ( ! in_array( $term->slug, $data['campuses'], true ) ) {
						$data['campuses'][] = $term->slug;
					}
				}
			}

			// Narrow results down to tables for the given campus.
			if ( ! has_term( $campus, 'campus', get_the_ID() ) ) {
				continue;
			}

			// Build the array of career path options offered during the given session at the given campus.
			$careers = get_the_terms( get_the_ID(), 'career-path' );

			if ( $careers && ! is_wp_error( $careers ) ) {
				foreach ( $careers as $term ) {
					if ( ! in_array( $term->slug, $data['campuses'], true ) ) {
						$data['careers'][] = $term->slug;
					}
				}
			}

			// Attempt to find a table that meets all the given critera.
			if ( has_term( $career, 'career-path', get_the_ID() ) ) {
				$data['table'] = get_the_content();
			}
		}
		wp_reset_postdata();
	}

	return $data;
}

/**
 * Display a form for viewing cost of attendance tables.
 *
 * @since 0.1.0
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string $html HTML output.
 */
function display_sfs_cost_tables( $atts ) {
	$defaults = array(
		'default_session' => '',
		'default_campus' => 'pullman',
		'default_career' => 'undergraduate',
		'language_code' => '',
		'session_label' => 'Year/Session',
		'campus_label' => 'Campus',
		'career_label' => 'Career Path',
	);

	$atts = shortcode_atts( $defaults, $atts );

	// Bail if no default session was provided - we can't find an initial table without it.
	if ( ! $atts['default_session'] ) {
		return '';
	}

	// Could probably cache this indefinitely unless an attribute is changed.

	$default_session = sanitize_text_field( $atts['default_session'] );
	$default_campus = sanitize_text_field( $atts['default_campus'] );
	$default_career = sanitize_text_field( $atts['default_career'] );

	if ( '' === $atts['language_code'] ) {
		$language_terms = get_the_terms( get_the_ID(), 'language' );
		if ( $language_terms ) {
			$language = $language_terms[0]->slug;
		} else {
			$language = 'en';
		}
	} else {
		$language = sanitize_text_field( $atts['language_code'] );
	}

	$session_terms = get_terms( 'session' );
	$campus_terms = get_terms( 'campus' );
	$career_terms = get_terms( 'career-path' );

	$data = cost_table_query( $language, $default_session, $default_campus, $default_career );

	// Bail if no terms were found or no data was returned.
	if ( ! $session_terms || ! $campus_terms || ! $career_terms || ! $data ) {
		return '';
	}

	wp_enqueue_script( 'sfs-cost-tables', get_stylesheet_directory_uri() . '/js/cost-tables.min.js', array( 'jquery' ), \WSU_Student_Financial_Services_Theme()->theme_version(), true );

	wp_localize_script( 'sfs-cost-tables', 'cost_tables', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'sfs-cost-tables' ),
		'language' => $language,
	) );

	ob_start();
	?>
	<form class="sfs-cost-tables flex-form">

		<div>
			<label for="cost-table-session"><?php echo esc_html( $atts['session_label'] ); ?></label><br />
			<div class="select-wrap">
				<select id="cost-table-session" name="session">
					<?php foreach ( $session_terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>"<?php

					// Define the display name for the term.
					$name = $term->name;

					if ( 'en' !== $language ) {
						$translated_name = get_term_meta( $term->term_id, "_$language-name", true );
						if ( $translated_name ) {
							$name = $translated_name;
						}
					}

					selected( $term->slug, $default_session );
					?>><?php echo esc_html( $name ); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div>
			<label for="cost-table-campus"><?php echo esc_html( $atts['campus_label'] ); ?></label><br />
			<div class="select-wrap">
				<select id="cost-table-campus" name="campus">
					<?php foreach ( $campus_terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>"<?php

					// Disable campus options not offered during the default session.
					if ( ! in_array( $term->slug, $data['campuses'], true ) ) {
						echo ' disabled';
					}

					selected( $term->slug, $default_campus );
					?>><?php echo esc_html( $term->name ); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div>
			<label for="cost-table-career"><?php echo esc_html( $atts['career_label'] ); ?></label><br />
			<div class="select-wrap">
				<select id="cost-table-career" name="career">
					<?php foreach ( $career_terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>"<?php

					// Disable career options not offered during the default session at the default campus.
					if ( ! in_array( $term->slug, $data['careers'], true ) ) {
						echo ' disabled';
					}

					// Define the display name for the term.
					$name = $term->name;

					if ( 'en' !== $language ) {
						$translated_name = get_term_meta( $term->term_id, "_$language-name", true );
						if ( $translated_name ) {
							$name = $translated_name;
						}
					}

					selected( $term->slug, $default_career );
					?>><?php echo esc_html( $name ); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

	</form>

	<div class="cost-table-placeholder">
		<?php echo wp_kses_post( $data['table'] ); ?>
	</div>

	<?php

	$html = ob_get_clean();

	return $html;
}

/**
 * Handle the ajax callback for the cost of attendance tables form.
 *
 * @since 0.1.0
 */
function ajax_callback() {
	check_ajax_referer( 'sfs-cost-tables', 'nonce' );

	$language = sanitize_text_field( $_POST['language'] );
	$session = sanitize_text_field( $_POST['session'] );
	$campus = sanitize_text_field( $_POST['campus'] );
	$career = sanitize_text_field( $_POST['career'] );

	$data = cost_table_query( $language, $session, $campus, $career );

	if ( ! $data['table'] ) {
		// Try to be helpful if no matching table is found.
		$session_term = get_term_by( 'slug', $session, 'session' );
		$campus_term = get_term_by( 'slug', $campus, 'campus' );
		$career_term = get_term_by( 'slug', $career, 'career-path' );
		$session_name = $session_term->name;
		$campus_name = $campus_term->name;
		$career_name = $career_term->name;

		if ( 'en' !== $language ) {
			$translated_session = get_term_meta( $session_term->term_id, "_$language-name", true );
			$translated_career = get_term_meta( $career_term->term_id, "_$language-name", true );

			if ( $translated_session ) {
				$session_name = $translated_session;
			}

			if ( $translated_career ) {
				$career_name = $translated_career;
			}
		}

		$notice = $career_name . ' is not offered at the ' . $campus_name . ' campus during the ' . $session_name . ' session.';

		switch ( $_POST['updated'] ) {
			case 'session':
				$data['table'] = '<p>' . $notice . ' Please select another session, or check the <em>Campus</em> or <em>Career Path</em> drop-downs for other options offered during this session.</p>';
				break;
			case 'campus':
				$data['table'] = '<p>' . $notice . ' Please check the <em>Career Path</em> drop-down for other options offered at ' . $campus_name . ' during this session, or select another campus.</p>';
				break;
			case 'career':
				// Theoretically, no one should make it in here...
				$data['table'] = "<p>We don't seem to have an estimate for the selected options. Please try another combination.</p>";
				break;
		}
	}

	echo wp_json_encode( $data );

	exit();
}
