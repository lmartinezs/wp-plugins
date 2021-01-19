<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This controller is responsible for handling actions related to WordPress posts.
 * 
 * @since 1.0.0
 */
class GNPUB_Posts {
	
	public function __construct() {
		add_action( 'pre_post_update', array( $this, 'increment_post_modified_counter' ), 20, 2 );
		add_filter( 'embed_oembed_html', array( $this, 'modify_embed_markup' ), 10, 4 );
	}

	/**
	 * Because Google uses the pubDate to determine whether an item has been updated, the plugin
	 * keeps track of when changes are made to a post so it can post-date the pubDate by that many
	 * seconds when Google next fetches the feed.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $post_id The ID of the post being updated.
	 * @param array $post_data The previous data of the post being created or updated.
	 */
	public function increment_post_modified_counter( $post_id, $post_data ) {
		$post = get_post( $post_id );

		if ( $post->post_type !== 'post' || $post->post_status !== 'publish' ) {
			return;
		}

		$last_fetched = intval( get_option( 'gnpub_google_last_fetch', 0 ) );

		// If the timestamp of when this post was previously modified is more recent than google last fetched the feed,
		// then we must have already incremented the counter since last fetched.
		 //if ( strtotime( $post->post_modified, current_time( 'timestamp' ) ) > $last_fetched ) {
		//	return;
		//}

		$counter = intval( get_post_meta( $post_id, 'gnpub_modified_count', true ) );
		$counter++;

		update_post_meta( $post_id, 'gnpub_modified_count', $counter );
	}

	/**
	 * The Google News RSS reader apparently prefers iframes, so convert some embeds to iframes where
	 * we know how to - which is currently just Facebook and Instagram embeds.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $markup The HTML embed content
	 * @param string $url The URL of the embedded resource
	 * @param array $attr Attributes of the embed
	 * @param int $post_ID The ID of the post the resource is embedded in.
	 * 
	 * @return string
	 */
	public function modify_embed_markup( $markup, $url, $attr, $post_ID ) {
		if ( is_feed( GNPUB_Feed::FEED_ID ) ) {
			
			if ( preg_match( '#https?://(www\.)?instagr(\.am|am\.com)/(p|tv)/([a-zA-Z0-9_\-]*)#i', $url, $matches ) && count( $matches ) >= 4 ) {
				// Instagram
				$markup = '<iframe width="320" height="320" frameBorder="0" src="https://www.instagram.com/p/' . $matches[4] . '/embed" ></iframe>';

				} elseif ( preg_match( '#https?://www\.facebook\.com/.*#', $url ) ) {
				// Facebook
				$is_video = ( stripos( $url, 'video' ) !== false );
				$base_url =  $is_video ? 'https://www.facebook.com/plugins/video.php' : 'https://www.facebook.com/plugins/post.php';

				$frame_url = add_query_arg( array(
					'href' => urlencode( $url ),
					'width' => $attr['width'],
					'height' => $attr['height'],
					'show_text' => $is_video ? 'false' : 'true',
					'appId' => ''
				), $base_url );

				$markup = '<iframe src="' . esc_url( $frame_url ) . '"';
				$markup .= ' width="' . esc_attr( $attr['width'] ) . '" height="' . esc_attr( $attr['height'] ) . '"';
				$markup .= ' style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>';
			}
		}

		return $markup;
	}

}