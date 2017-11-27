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
add_action( 'init', 'WSU\Financial_Aid\Cost_Tables\register_taxonomies' );
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Financial_Aid\Cost_Tables\taxonomy_meta_box' );
add_action( 'add_meta_boxes_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\add_meta_boxes' );
add_action( 'admin_enqueue_scripts', 'WSU\Financial_Aid\Cost_Tables\admin_enqueue_scripts' );
add_action( 'save_post_' . post_type_slug(), 'WSU\Financial_Aid\Cost_Tables\save_post', 10, 2 );
add_filter( 'wp_insert_post_data', 'WSU\Financial_Aid\Cost_Tables\insert_post_data', 11, 2 );

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
			'editor',
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

	<?php if ( $data ) { ?>

	<table>
		<?php foreach ( $data as $row => $cells ) { ?>
		<tr>
			<?php foreach ( $cells as $index => $cell ) { ?>
			<td>
				<input type="text" name="_cost_table_data[<?php echo esc_attr( $row ); ?>][]" autocomplete="off" value="<?php echo esc_attr( $cell ); ?>" />
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</table>

	<?php } else { ?>

	<table>
		<tr>
			<td>
				<input type="text" name="_cost_table_data[0][]" autocomplete="off" value="" />
			</td>
			<td>
				<input type="text" name="_cost_table_data[0][]" autocomplete="off" value="" />
			</td>
			<td>
				<input type="text" name="_cost_table_data[0][]" autocomplete="off" value="" />
			</td>
		</tr>
	</table>

	<?php } ?>

	<button type="button" class="add-row">+ Add row</button>
	<button type="button" class="add-column">+ Add column</button>
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
	wp_enqueue_script( 'cost-tables', get_stylesheet_directory_uri() . '/src/js/admin-cost-tables.js', array( 'jquery' ), null, true );
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

	if ( isset( $_POST['_cost_table_data'] ) && '' !== $_POST['_cost_table_data'] ) {
		$sanitized_data = array();

		foreach ( $_POST['_cost_table_data'] as $row => $cells ) {
			foreach ( $cells as $index => $cell ) {
				$sanitized_data[ $row ][] = wp_kses_post( $cell );
			}
		}

		update_post_meta( $post_id, '_cost_table_data', $sanitized_data );
	} else {
		delete_post_meta( $post_id, '_cost_table_data' );
	}
}

/**
 * Removes post content before saving a person on a secondary site.
 *
 * @since 0.1.0
 *
 * @param array $data    Slashed post data.
 * @param array $postarr Sanitized, but otherwise unmodified post data.
 *
 * @return array
 */
function insert_post_data( $data, $postarr ) {
	if ( post_type_slug() === $data['post_type'] ) {
		$cost_data = get_post_meta( $postarr['ID'], '_cost_table_data', true );

		if ( $cost_data ) {
			$built_table = '<table>';
			foreach ( $cost_data as $row => $cells ) {
				$built_table .= '<tr>';
				foreach ( $cells as $index => $cell ) {
					$built_table .= ( 0 === $row ) ? '<th>' : '<td>';
					$built_table .= wp_kses_post( $cell );
					$built_table .= ( 0 === $row ) ? '</th>' : '</td>';
				}
				$built_table .= '</tr>';
			}
			$built_table .= '</table>';
			$data['post_content'] = $built_table;
		} else {
			$data['post_content'] = '';
		}
	}

	return $data;
}
