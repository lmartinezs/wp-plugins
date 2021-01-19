<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns true if the user agent making the current request is Google FeedFetcher.
 * 
 * @since 1.0.5
 * 
 * @return boolean
 */
function gnpub_is_feedfetcher() {
	if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		return false;
	}

	$user_agent_signature = "FeedFetcher-Google";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	return ( stripos( $user_agent, $user_agent_signature ) !== false );
}

/**
 * Returns a list of feeds which have been accessed by Google FeedFetcher.
 * 
 * @since 1.0.5
 * 
 * @return array
 */
function gnpub_feed_list() {
	$feed_list = (array) get_option( 'gnpub_feed_list', array() );

	return $feed_list;
}

/**
 * Add a feed to the list of feeds the plugin is aware of.
 * 
 * @since 1.0.5
 * 
 * @param string $feed_url
 * @param \WP_Query $wp_query
 */
function gnpub_add_feed( $feed_url, $wp_query ) {
	$feed_list = (array) get_option( 'gnpub_feed_list', array() );

	$query = gnpub_reduce_query( $wp_query );
	$query['query_timestamp'] = current_time( 'timestamp' );

	$feed_list[$feed_url] = gnpub_reduce_query( $wp_query );

	update_option( 'gnpub_feed_list', $feed_list );
}

/**
 * Takes a WP_Query instance and transforms it into a simple array
 * with the query parameters we care about.
 * 
 * @since 1.0.5
 * 
 * @param \WP_Query $wp_query
 * 
 * @return array
 */
function gnpub_reduce_query( $wp_query ) {
	$keys = array(
		'cat',
		'category_name',
		'category__and',
		'category__in',
		'author',
		'author_name',
		'author__in',
		'author__not_in',
		'tag',
		'tag_id',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_slug__and',
		'tag_slug__in',
		's'
	);

	return array_intersect_key(
		$wp_query->query_vars,
		array_flip( $keys )
	);
}

/**
 * Remove URLs from the feed list which haven't been fetched in over thirty days.
 * 
 * @since 1.0.5
 */
function gnpub_purge_feed() {
	$month_ago = current_time( 'timestamp' ) - MONTH_IN_SECONDS;
	$feed_list = gnpub_feed_list();

	$feed_list = array_filter( $feed_list, function( $feed_query ) use ( $month_ago ) {
		return $feed_query['query_timestamp'] >= $month_ago;
	} );

	update_option( 'gnpub_feed_list', $feed_list );
}

/**
 * Publish feeds to the hub.
 * 
 * @since 1.0.5
 * 
 * @param array $feed_urls
 */
function gnpub_publish_feeds( $feed_urls ) {
	$post_string = 'hub.mode=publish';

	foreach ( $feed_urls as $feed_url ) {
		$post_string .= '&hub.url=' . esc_url( $feed_url );
	}

	$wp_version = get_bloginfo( 'version' );
	$user_agent = apply_filters( 'http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );

	$args = array(
		'timeout' => 100,
		'limit_response_size' => 1048576,
		'redirection' => 20,
		'user-agent' => $user_agent . "; PubSubHubbub/WebSub",
		'body' => $post_string,
		'blocking' => false, // We do not need the response.
		'headers' => array(
			'Content-Type' => 'application/x-www-form-urlencoded'
		)
	);

	update_option( 'gnpub_websub_last_ping', current_time( 'timestamp' ) );

	wp_remote_post( 'https://pubsubhubbub.appspot.com', $args );

}

/**
 * Does the same as get_self_link which is only available in WP
 * >= 5.3.
 * 
 * @since 1.0.8
 * 
 * @return string
 */
function gnpub_current_feed_link() {
	$host = @parse_url( home_url() );
	return set_url_scheme( 'http://' . $host['host'] . stripslashes_deep( $_SERVER['REQUEST_URI'] ) );
}