<header class="spine-header">
	<a href="<?php echo esc_url( spine_get_campus_data( 'url' ) ); ?>" id="wsu-signature"><?php echo esc_html( spine_get_campus_data( 'link-text' ) ); ?></a>
</header>

<section id="wsu-actions" class="spine-actions clearfix">

	<ul id="wsu-actions-tabs" class="spine-actions-tabs spine-tabs clearfix">
		<li class="spine-search-tab closed"><button onclick="location.href='<?php echo esc_url( get_site_url() . '/?s=' ); ?>';" value="Search this site">Search</button></li>
		<li id="wsu-contact-tab" class="spine-contact-tab closed"><button>Contact</button></li>
		<li id="wsu-share-tab" class="spine-share-tab closed"><button>Share</button></li>
	</ul>

</section><!--/#wsu-actions-->
