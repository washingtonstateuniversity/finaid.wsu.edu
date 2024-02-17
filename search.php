<?php get_header(); ?>

<?php do_action( 'spine_theme_template_before_main', 'page.php' ); ?>

<main id="wsuwp-main" class="spine-page-default">

<?php do_action( 'spine_theme_template_before_headers', 'page.php' ); ?>

<?php wsuwp_spine_get_template_part( 'page.php', 'parts/headers-search' ); ?>

<?php do_action( 'spine_theme_template_after_headers', 'page.php' ); ?>

<?php wsuwp_spine_get_template_part( 'page.php', 'parts/featured-images' ); ?>

<?php do_action( 'spine_theme_template_before_content', 'page.php' ); ?>

<section class="row single gutter pad-ends">

	<div class="column one">

		<?php do_action( 'spine_theme_template_before_articles', 'page.php' ); ?>

        <h2>Search <?php echo get_bloginfo( 'name' ); ?>
        <form class="wsu-search " method="get" action="<?php echo get_site_url(); ?>">
            <div class="wsu-search__search-bar">
                <input class="wsu-search__input" type="text" aria-label="Search input" placeholder="Search" name="s" value="<?php echo esc_attr( $_REQUEST['s'] ?? "" ); ?>" />
                <button class="wsu-search__submit" aria-label="Submit Search"></button>
            </div>
        </form>
        <style>
            .wsu-search__search-bar {
                padding: 2rem;
                background-color: #eee;
                border: 1px solid #ddd;
                display: flex;
            }
            .wsu-search__input {
                flex-grow: 1;
                padding: 0 8px !important;
                font-size: 18px !important;
                border-radius: 3px !important;
                margin: 0 !important;
                height: 45px !important;
                line-height: 45px !important; 
            }
            .wsu-search__submit {
                background-color: #981e32 !important;
                color: #fff;
                margin: 0 !important;
                padding: 0 !important;
                width: 80px;
                flex-grow: 0;
                border-radius: 3px !important;
                height: 45px !important;
            }

            .wsu-search__submit::before {
                font-family: Spine-Icons;
                content: "$";
            }
            .wsu-search__submit:focus,
            .wsu-search__submit:hover {
                background-color: #222 !important;
                color: #fff !important;;
            }
            .wsu-search__submit:focus::before,
            .wsu-search__submit:hover::before {
                font-family: Spine-Icons;
                content: "$";
            }
        </style>
		<script async src="https://cse.google.com/cse.js?cx=54e8a42262bb5e5f2"></script>
        <div class="gcse-searchresults-only" data-queryParameterName="s" data-as_sitesearch="<?php echo get_site_url(); ?>"></div>

		<?php do_action( 'spine_theme_template_after_articles', 'page.php' ); ?>

	</div><!--/column-->

</section>

<?php do_action( 'spine_theme_template_after_content', 'page.php' ); ?>

<?php do_action( 'spine_theme_template_before_footer', 'page.php' ); ?>

<?php wsuwp_spine_get_template_part( 'page.php', 'parts/footers' ); ?>

<?php do_action( 'spine_theme_template_after_footer', 'page.php' ); ?>

</main>

<?php do_action( 'spine_theme_template_after_main', 'page.php' ); ?>

<?php get_footer();
