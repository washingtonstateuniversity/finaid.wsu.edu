<?php
if ( true === spine_get_option( 'main_header_show' ) ) :

	$header_elements = sfs_get_header_elements();

	?>
	<header class="main-header">

		<div class="site-header-group">
			<span class="sup-header">
				<a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"><?php bloginfo( 'name' ); ?></a>
			</span>
			<span class="sub-header">
				<?php echo esc_html( $header_elements['section_title'] ); ?>
			</span>
		</div>

		<nav class="site-action-links">
			<ul>
				<?php dynamic_sidebar( 'site-actions' ); ?>
			</ul>
		</nav>

		<div class="page-header-group">
			<?php if ( array_key_exists( 'page_sup', $header_elements ) ) { ?>
			<span class="sup-header">
				<?php echo esc_html( $header_elements['page_sup'] ); ?>
			</span>
			<?php } ?>
			<span class="sub-header">
				Search
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
