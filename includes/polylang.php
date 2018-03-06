<?php

namespace WSU\Financial_Aid\Polylang;

add_filter( 'pll_the_language_link', 'WSU\Financial_Aid\Polylang\event_taxonomy_archive_link', 10, 2 );

/**
 * Filter the translation link output by the language switcher
 * for event category archive views.
 *
 * The `category` path is incorrectly being translated on Spanish pages.
 *
 * @since 0.1.3
 *
 * @param string $url  The translation URL.
 * @param string $slug Language code of the translation.
 *
 * @return string
 */
function event_taxonomy_archive_link( $url, $slug ) {
	if ( ! is_tax( 'tribe_events_cat' ) ) {
		return;
	}

	if ( 'en' !== $slug ) {
		return $url;
	}

	$url = str_replace( 'categoria', 'category', $url );

	return $url;
}
