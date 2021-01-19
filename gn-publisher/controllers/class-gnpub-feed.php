<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This controller is responsible for registering and managing the display of
 * the GN Publisher RSS feed.
 * 
 * @since 1.0.0
 */
class GNPUB_Feed {

	/**
	 * This is used in the feed URL to select the GN Publisher feed.
	 */
	const FEED_ID = 'gn';

	/**
	 * This text will be present in the Google FeedFetcher user-agent string, used
	 * by the plugin to detect when Google is reading the feed.
	 * 
	 * @deprecated 1.0.5 Use gnpub_is_feedfetcher
	 */
	const FEED_FETCHER_UA = "FeedFetcher-Google";

	public function __construct() {
		add_action( 'init', array( $this, 'add_google_news_feed' ) );
		add_action( 'wp', array( $this, 'remove_problematic_functions' ) );

		// Documented in wp-includes/class-wp-query.php -> WP_Query::parse_query()
		add_action( 'parse_query', array( $this, 'apply_feed_constraints' ) );

		// Documented in wp-includes/feed.php -> get_the_content_feed()
		add_filter( 'the_content_feed', array( $this, 'add_feature_image_to_item' ), 10, 2 );
		add_filter( 'the_content_feed', array( $this, 'strip_srcset_from_content' ), 50, 2 );
		add_filter( 'the_content_feed', array( $this, 'remove_duplicate_images' ), 60, 2 );

		// Documented in wp-includes/feed.php -> get_default_feed()
		add_filter( 'default_feed', array( $this, 'set_default_feed' ) );

		// Documented in wp-includes/general-template.php -> get_the_generator()
		add_filter( 'get_the_generator_rss2', array( $this, 'set_feed_generator' ), 15, 2 );

	}

	/**
	 * Adds the GN Publisher feed to WordPress. The add_feed function will add the feed
	 * rewrite rule, but the rules need to be flushed for the rule to be included
	 * 
	 * @since 1.0.0
	 * @uses add_feed
	 */
	public function add_google_news_feed() {
		add_feed( self::FEED_ID, array( $this, 'do_google_news_feed' ) );
	}

	/**
	 * Includes the google news publisher feed template.
	 * 
	 * @since 1.0.0
	 * @uses load_template
	 * 
	 * @param bool $for_comments Whether the feed request was for comments.
	 */
	public function do_google_news_feed( $for_comments ) {
		load_template( GNPUB_PATH . 'templates/google-news-feed.php' );
	}

	/**
	 * Applies the google news feed constraints to the posts query.
	 * 
	 * @since 1.0.0
	 * 
	 * @param WP_Query $query The global posts query instance.
	 */
	public function apply_feed_constraints( $query ) {
		if ( ! $query->is_feed ) {
			return;
		}

		/*
			This checks:
			1. Is the queried feed the GN Publisher feed, if so continue.
			2. Is the queried feed the default feed, and
			3. Is the default feed the GN Publisher feed, if so continue.
		*/
		if ( $query->get( 'feed' ) !== self::FEED_ID && ( $query->get( 'feed' ) !== 'feed' && get_default_feed() !== self::FEED_ID ) ) {
			return;
		}

		if ( gnpub_is_feedfetcher() ) {
			update_option( 'gnpub_google_last_fetch', current_time( 'timestamp' ) );
		}

		// The maximum number of posts which can be displayed in the feed.
		// Default: 30 posts.
		$max_posts = apply_filters( 'gnpub_feed_max_posts', 30 );

			}

	/**
	 * Adds a post's feature image to the beginning of the content.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $content The HTML content for the feed item.
	 * @param string $feed_type The type of feed the item is in.
	 * 
	 * @return string
	 */
	public function add_feature_image_to_item( $content, $feed_type ) {
		if ( $feed_type !== self::FEED_ID ) {
			return $content;
		}

		$use_featured_image = get_option( 'gnpub_include_featured_image', 1 );

		if ( empty( $use_featured_image ) ) {
			return $content;
		}

		$featured_image_url = $this->get_original_feature_image_url( get_the_ID() );

		if ( $featured_image_url ) {
			$content = "<figure><img src=\"{$featured_image_url}\" class=\"type:primaryImage\" /></figure>" . $content;
		}

		return $content;
	}

	/**
	 * Strips srcset attributes from feed output.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $content The HTML content for the feed item.
	 * @param string $feed_type The type of feed the item is in.
	 * 
	 * @return string
	 */
	public function strip_srcset_from_content( $content, $feed_type ) {
		if ( $feed_type !== self::FEED_ID ) {
			return $content;
		}

		$content = preg_replace( '/srcset=[\'|"].*?[\'|"]/i', '', $content );

		return $content;
	}

	/**
	 * Remove any duplicate images from a feed item's content.
	 * 
	 * @since 1.0.1
	 * 
	 * @param string $content The HTML content for the feed item.
	 * @param string $feed_type The type of feed the item is in.
	 * 
	 * @return string
	 */
	public function remove_duplicate_images( $content, $feed_type ) {
		$occurances = array();
		$images = array();
		preg_match_all( '/<img[^>]* src=[\"|\']([^\"]*)[\"|\'][^>]*>/i', $content, $images );

		foreach ( $images[1] as $image ) {
			$base_image = $this->get_base_image_src( $image );

			if ( ! isset( $occurances[$base_image] ) ) {
				$occurances[$base_image] = array();
			}

			$occurances[$base_image][] = $image;
		}

		foreach ( $occurances as $image_base => $image_srcs ) {
			if ( count( $image_srcs ) < 2 ) {
				continue;
			}

			$shortest = null;
			$tallied = array_reduce( $image_srcs, function( $tallied, $src ) use ( &$shortest ) {
				if ( ! isset( $tallied[$src] ) ) {
					$tallied[$src] = 0;
				}

				$tallied[$src]++;

				if ( is_null( $shortest ) || strlen( $src ) < strlen( $shortest ) ) {
					$shortest = $src;
				}

				return $tallied;
			}, array() );

			foreach ( $tallied as $image_src => $tally ) {
				$limit = -1;

				if ( $shortest === $image_src ) {
					$limit = $tally - 1;
				}

				$pattern = '/<img[^>]* src=[\"|\']' . preg_quote( $image_src, '/' ) . '[\"|\'][^>]*>/i';

				$content = preg_replace( $pattern, '', $content, $limit );
			}
		}

		// Remove empty <figure></figure> tags.
		$content = preg_replace( '/<figure[^>]*>s*<\/figure>/', '', $content );

		return $content;
	}

	/**
	 * Returns the path the path to the originally uploaded image.
	 * 
	 * @since 1.0.1
	 * 
	 * @param string $image_src A URL to a WordPress image.
	 * 
	 * @return string
	 */
	protected function get_base_image_src( $image_src ) {
		if ( preg_match( '/(-\d{1,4}x\d{1,4})\.(jpg|jpeg|png|gif)$/i', $image_src, $matches ) ) {
			$image_src = str_ireplace( $matches[1], '', $image_src );
		}

		if ( preg_match( '/uploads\/(\d{1,4}\/)?(\d{1,2}\/)?(.+)$/i', $image_src, $matches ) ) {
			unset( $matches[0] );
			$image_src = implode( '', $matches );
		}

		return $image_src;
	}

	/**
	 * For the specified post, find the full size image that was uploaded and set as its featured image.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $post_id The ID of the post.
	 * 
	 * @return bool|string
	 */
	protected function get_original_feature_image_url( $post_id ) {
		$attachment_id = get_post_thumbnail_id( $post_id );

		if ( empty( $attachment_id ) ) {
			return false;
		}

		// This function is only available since 5.3
		if ( function_exists( 'wp_get_original_image_url' ) ) {
			return wp_get_original_image_url( $attachment_id );
		}

		return wp_get_attachment_url( $attachment_id );
	}

	/**
	 * Sets the GN Publisher feed as the default feed if the setting to do so
	 * has been enabled.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $default_feed The default feed
	 * 
	 * @return string
	 */
	public function set_default_feed( $default_feed ) {
		$is_default = boolval( get_option( 'gnpub_is_default_feed', true ) );

		if ( $is_default ) {
			$default_feed = self::FEED_ID;
		}

		return $default_feed;
	}

	/**
	 * Changes the <generator> to be the name and version of the plugin.
	 * 
	 * @since 1.0.2
	 * 
	 * @param string $gen The generator tag.
	 * @param string $feed_type The type of feed.
	 * 
	 * @return string
	 */
	public function set_feed_generator( $gen, $feed_type ) {
		if ( is_feed( self::FEED_ID ) ) {
			$gen = '<generator>GN Publisher v' . GNPUB_VERSION . ' https://wordpress.org/plugins/gn-publisher/</generator>';
		}

		return $gen;
}

	/**
	 * Remove functions which are known to conflict with the gn feed.
	 * 
	 * @since 1.0.3
	 */
	public function remove_problematic_functions() {
		if ( ! is_feed( self::FEED_ID ) ) {
			return;
		}

		/**
		 * This array is in the following format:
		 * [
		 *		'filter name' => [
		 * 			'filter callable' => 'filter priority'
		 * 		]
		 * ]
		 */
		$problematic_filters = array(
			'the_content_feed' => array(
				'firss_featured_images_in_rss' => 1000,
				'salzano_add_featured_image_to_feed' => 1000
			)
		);

		foreach ( $problematic_filters as $filter => $problematic_functions ) {
			foreach ( $problematic_functions as $function => $priority ) {
				remove_filter( $filter, $function, $priority );
			}
		}
	}

}
