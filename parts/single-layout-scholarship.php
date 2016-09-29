<?php
$settings = get_option( 'scholarships_settings' );
if ( $settings['search_page_url'] ) {
	$search_page_url = get_permalink( $settings['search_page_url'] );
}
?>
<section class="row single gutter pad-ends">

	<div class="column one">

		<?php if ( $search_page_url ) { ?>
		<p>
			<a href="<?php echo esc_url( $search_page_url ); ?>">« Scholarship Search Results</a>
		</p>
		<?php } ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'articles/post', get_post_type() ) ?>

		<?php endwhile; ?>

	</div>

</section>
