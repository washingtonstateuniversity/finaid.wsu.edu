<?php

/**
 * Retrieve an array of values to be used in the header.
 *
 * site_name
 * site_tagline
 * page_title
 * post_title
 * section_title
 * subsection_title
 * posts_page_title
 * sup_header_default
 * sub_header_default
 * sup_header_alternate
 * sub_header_alternate
 */
$spine_main_header_values = spine_get_main_header();

if ( true === spine_get_option( 'main_header_show' ) ) :

	$page_sup_header = get_post_meta( get_the_ID(), 'sup-header', true );
	$page_sub_header = get_post_meta( get_the_ID(), 'sub-header', true );
	$page_sub_header = ( '' !== $page_sub_header ) ? $page_sub_header : get_the_title();

?>
<header class="main-header">

	<div class="site-header-group">
		<span class="sup-header">
			<a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"><?php bloginfo( 'name' ); ?></a>
		</span>
		<span class="sub-header">
			<?php
				$ancestor = get_page( array_pop( get_post_ancestors( $post->ID ) ) );
				echo esc_html( $ancestor->post_title );
			?>
		</span>
	</div>

	<nav class="site-action-links">
		<ul>
			<?php dynamic_sidebar( 'site-actions' ); ?>
		</ul>
	</nav>

	<div class="page-header-group">
		<span class="sup-header">
			<?php echo esc_html( $page_sup_header ); ?>
		</span>
		<span class="sub-header">
			<?php echo esc_html( $page_sub_header ); ?>
		</span>
	</div>

</header>

<?php
endif;

if ( is_front_page() && ! is_home() && true === spine_get_option( 'front_page_title' ) ) :
?>
<section class="row single gutter pad-ends">
	<div class="column one">
		<h1><?php the_title(); ?></h1>
	</div>
</section>
<?php
endif;

if ( is_home() && ! is_front_page() && true === spine_get_option( 'page_for_posts_title' ) ) :
	$page_for_posts_id = get_option( 'page_for_posts' );
	if ( $page_for_posts_id ) {
		$page_for_posts_title = get_the_title( $page_for_posts_id );
	} else {
		$page_for_posts_title = '';
	}
	?>
<section class="row single gutter pad-ends">
	<div class="column one">
		<h1><?php echo esc_html( $page_for_posts_title ); ?></h1>
	</div>
</section>
<?php
endif;
