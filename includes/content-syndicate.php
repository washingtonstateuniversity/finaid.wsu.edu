<?php

namespace WSU\Financial_Aid\Content_Syndicate;

add_filter( 'wsuwp_content_syndicate_default_atts', 'WSU\Financial_Aid\Content_Syndicate\append_default_attributes' );
add_filter( 'wsuwp_content_syndicate_taxonomy_filters', 'WSU\Financial_Aid\Content_Syndicate\language_filter', 10, 2 );

/**
 * Add support for a language flag.
 *
 * @param array $atts WSUWP Content Syndicate shortcode attributes.
 *
 * @return array Modified list of default shortcode attributes.
 */
function append_default_attributes( $atts ) {
	$atts['language'] = '';

	return $atts;
}

/**
 * Include the language flag as part of the REST API request.
 *
 * @param string $request_url
 * @param array  $atts
 *
 * @return string
 */
function language_filter( $request_url, $atts ) {
	if ( ! in_array( $atts['language'], array( 'es', 'en' ), true ) ) {
		return $request_url;
	}

	$request_url = add_query_arg( array(
		'filter[lang]' => $atts['language'],
	), $request_url );

	return $request_url;
}
