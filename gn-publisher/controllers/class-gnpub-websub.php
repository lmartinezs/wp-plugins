<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This controller is responsible for sending feed update notifications to PubSubHubbub
 * 
 * @since 1.0.5
 */
class GNPUB_Websub {

	private $posts_updated = array();

	public function __construct() {
		add_action( 'parse_query', array( $this, 'feed_list_check' ), 10, 1 );
		add_action( 'save_post', array( $this, 'post_saved' ), 10, 3 );
		add_action( 'shutdown', array( $this, 'notify_hubs' ) );
	}

	/**
	 * Detect Google FeedFetcher fetching feeds and record the feed fetched.
	 * 
	 * @since 1.0.5
	 * 
	 * @param WP_Query $query The global posts query instance.
	 */
	public function feed_list_check( $query ) {
		if ( ! $query->is_feed || $query->get( 'feed' ) !== GNPUB_Feed::FEED_ID || ! gnpub_is_feedfetcher() || $query->is_comment_feed || $query->is_paged ) {
			return;
		}

		$feed_url = untrailingslashit( gnpub_current_feed_link() );

		gnpub_add_feed( $feed_url, $query );
	}

	/**
	 * When a post has been updated, check if that post would be in any feed in the
	 * feed list. If it is, mark that feed as updated.
	 * 
	 * @since 1.0.5
	 * 
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param bool $update
	 */
	public function post_saved( $post_id, $post, $update ) {
		$this->posts_updated[] = $post_id;
	}

	/**
	 * If there are feeds which have changed, notify the hub.
	 * 
	 * @since 1.0.5
	 */
	public function notify_hubs() {
		if ( ! empty( $this->posts_updated ) ) {
			$lock = get_transient( 'gnpub_websub_lock' );

			if ( $lock ) {
				return;
			}

			$feed_list = gnpub_feed_list();
			$modified_feeds = array();

			// The maximum number of posts which can be displayed in the feed.
			// Default: 30 posts.
			$max_posts = apply_filters( 'gnpub_feed_max_posts', 30 );

			// The period for which to query posts.
			// Default: Last 7 days.
			$query_period = apply_filters( 'gnpub_feed_query_period', 30 * DAY_IN_SECONDS );

			$now = current_time( 'timestamp' );
			$after = $now - $query_period;
		
			$local_date = date_i18n( 'Y-m-d H:i:s', $after );

			$date_query = array(
				'column' => 'post_modified',

				array(
					'after' => $local_date
				)
			);

			foreach ( $feed_list as $feed_url => $query_args ) {
				$query_args['fields'] = 'ids';
				$query_args['posts_per_page'] = $max_posts;
				$query_args['date_query'] = $date_query;
				$query_args['post_status'] = 'publish';

				$query = new WP_Query( $query_args );
			
				if ( count( array_intersect( $this->posts_updated, $query->get_posts() ) ) > 0 ) {
					$modified_feeds[] = $feed_url;
				}
			}

			if ( count( $modified_feeds ) < 1 ) {
				return;
			}
			
			set_transient( 'gnpub_websub_lock', 1, 10 );
			gnpub_publish_feeds( $modified_feeds );
		}
	}

}