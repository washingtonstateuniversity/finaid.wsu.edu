<?php

/*
 * Include a footer navigation area inside the `main` element on all pages.
 */
$footer_menu_args = array(
	'theme_location' => 'footer',
	'menu' => 'footer',
	'container' => false,
	'container_class' => false,
	'fallback_cb' => false,
	'menu_class' => 'site-footer-menu',
	'menu_id' => false,
	'depth' => 2,
);
?>
<footer class="single row gutter padded-ends pad-ends site-footer">
	<?php dynamic_sidebar( 'give-link' ); ?>
	<div class="column one">
		<?php wp_nav_menu( $footer_menu_args ); ?>
	</div>
</footer>
