<section class="row single gutter pad-ends">

	<div class="column one">

		<p>
			<a href="#">« Scholarship Search Results</a>
		</p>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'articles/post', get_post_type() ) ?>

		<?php endwhile; ?>

	</div>

</section>
