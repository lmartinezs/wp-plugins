<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$last_deactivation = get_option( 'gnpub_last_deactivation', 0 );
$last_activation = get_option( 'gnpub_last_activation', 0 );

/**
 * RSS2 Feed Template for displaying RSS2 Posts feed specifically for Google News Publisher.
 * 
 * This template is based on wp-includes/feed-rss2.php
 */

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

///////////////
// Disable caching @since 1.0.2 -ca
//////////////
header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define( 'DONOTCACHEPAGE', true);

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
/**
 * Fires between the xml and rss tags in a feed.
 *
 * @since 4.0.0
 *
 * @param string $context Type of feed. Possible values include 'rss2', 'rss2-comments',
 *                        'rdf', 'atom', and 'atom-comments'.
 */
do_action( 'rss_tag_pre', 'rss2' );
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/" <?php
	/**
	 * Fires at the end of the RSS root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_ns' );
	echo '>';
	?> 

	<channel>
		<title><?php wp_title_rss(); ?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<lastBuildDate><?php
			$date = get_lastpostmodified( 'GMT' );
			echo $date ? mysql2date( 'D, d M Y H:i:s +0000', $date, false ) : gmdate( 'r' );
		?></lastBuildDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<sy:updatePeriod> <?php $duration = 'hourly'; echo apply_filters( 'rss_update_period', $duration );?> </sy:updatePeriod>
		<sy:updateFrequency> <?php $frequency = '1'; echo apply_filters( 'rss_update_frequency', $frequency );?> </sy:updateFrequency>
		<atom:link rel="hub" href="https://pubsubhubbub.appspot.com/" />
		<generator>GN Publisher v<?=GNPUB_VERSION;?> https://wordpress.org/plugins/gn-publisher/</generator>
<?php
	while ( have_posts() ) :
		the_post();

		$mod_counter = intval( get_post_meta( get_the_ID(), 'gnpub_modified_count', true ) );

		$last_modified = get_post_modified_time( 'U', true );
		if ( $last_modified > $last_deactivation && $last_modified < $last_activation ) {
			$mod_counter++;
		}

		if ( $mod_counter ) {
			$pub_date_object = new DateTime;
			$pub_date_object->setTimestamp( get_post_time( 'U', true ) );
			$pub_date_object->modify( '+' . $mod_counter . ' seconds' );

			$pub_date = date( 'D, d M Y H:i:s +0000', $pub_date_object->getTimestamp() );
		} else {
			 $pub_date = mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false );

		}

		?>

		<item>
			<title><?php the_title_rss(); ?></title>
			<link><?php the_permalink(); ?></link>
			<pubDate><?php echo $pub_date; ?></pubDate>
			<dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator>
			<guid isPermaLink="true"><?php the_permalink(); ?></guid>
<?php $content = get_the_content_feed( GNPUB_Feed::FEED_ID ); 
 if ( strlen( $content ) > 0 ) : 
?>
			<?php /* <description><![CDATA[<?php echo wp_trim_words($content,15,'...');?>]]></description>

			<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded> */ ?>
			<?php 
				//Replace aria-describedby
				$content = preg_replace('/aria-describedby=\"caption-attachment-(\d+)\"/m', '', $content);
				//replace script tags
				$content = preg_replace('/<\s*script.*?>.*?(<\s*\/script.*?>|$)/m', '', $content);
				//replace iframe tags
				$content = preg_replace('/<\s*iframe.*?>.*?(<\s*\/iframe.*?>|$)/m', '', $content);		
			 ?>
			<description><![CDATA[<?php echo $content; ?>]]></description>
			
<?php 		else : ?>
			<?php /* <content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded> */ ?>
			<description><![CDATA[<?php echo the_excerpt_rss(); ?>]]></description>
<?php 		endif; ?>
<?php 		rss_enclosure(); ?>
		</item>
<?php 	endwhile; ?>
	</channel>
</rss>
<!-- last GN Pub feeds fetch (not specifically this feed): <?php echo (get_option( 'gnpub_google_last_fetch', null )) ? date_i18n( 'Y-m-d H:i:s', get_option( 'gnpub_google_last_fetch' ) ) : 'has not fetched'; ?> -->
