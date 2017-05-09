<?php

require_once( dirname( __FILE__ ) . '/includes/class-wsu-student-financial-services-theme.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wsu-student-financial-services-site-actions-widget.php' );
require_once( dirname( __FILE__ ) . '/includes/class-wsu-student-financial-services-give-link-widget.php' );


/**
 * Starts the main class controlling the theme.
 *
 * @since 0.0.1
 *
 * @return \WSU_Student_Financial_Services_Theme
 */
add_action( 'after_setup_theme', function() {
	return WSU_Student_Financial_Services_Theme::get_instance();
} );

/**
 * Register the custom sidebars and widgets used by the theme.
 *
 * @since 0.0.1
 * @since 0.0.12 Added Give Link widget.
 */
add_action( 'widgets_init', function() {
	register_widget( 'WSU_Student_Financial_Services_Site_Actions_Widget' );
	register_widget( 'WSU_Student_Financial_Services_Give_Link_Widget' );

	$header_args = array(
		'name' => 'Actions',
		'id' => 'site-actions',
		'description' => 'Displays the action links on the top of every page.',
	);

	register_sidebar( $header_args );

	$footer_args = array(
		'name' => 'Give Link',
		'id' => 'give-link',
		'description' => 'Displays the "give" link in the site footer.',
	);

	register_sidebar( $footer_args );

} );

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
