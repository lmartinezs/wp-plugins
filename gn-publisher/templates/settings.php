<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h1><?php _e( 'GN Publisher Info and Tips', 'gn-publisher' ); ?></h1>

<?php // don't think we need this anymore, let's test without, but leave in code for now in case
	//it needs to be reenabled - ca (11/29/2020)
/*
	<form action="" method="post">

		<table class="form-table">

			<tr>
				<th><?php _e( 'Include the featured image for a post in the feed', 'gn-publisher' ); ?></th>
				<td>
					<input type="checkbox" name="gnpub_include_featured_image" id="gnpub_include_featured_image" <?php checked( $include_featured_image, true ); ?> value="1" />
					<label for="gnpub_include_featured_image"><?php _e( 'Include featured image', 'gn-publisher' ); ?></label>
					<p class="description"><?php _e( 'Deactivate this option if images in the Publisher Center appear twice in your articles.', 'gn-publisher' ); ?></p>
				</td>
			</tr>

		</table>

		<p class="submit">
			<?php wp_nonce_field( 'save_gnpub_settings', '_wpnonce' ); ?>
			<input type="submit" name="save_gnpub_settings" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'gn-publisher' ); ?>" />
		</p>

	</form>
*/ ?>
	<div class="information" style="font-weight:bold;background-color:#dedede;padding:30px;">

	<p><?php
			printf(
				__( 'Hi,</p><p style="font-size:110%;"> I\'m Chris Andrews, a Platinum Level Product Expert on the <a href="%1$s">Google News Publisher Help forum</a>, the creator of <a href="%2$s">GN Publisher</a>, and the owner of <a href="%3$s">Andrews Consulting</a>.</p>', 'gn-publisher' ),
				'https://support.google.com/news/publisher-center/community?hl=en',
				'https://wordpress.org/plugins/gn-publisher/',
				'https://andrews.com'
			);
	?></p>

	<p><?php
			printf(
				__( 'GN Publisher is a WordPress plugin designed to output RSS feeds that comply with the <a href="%1$s">Google News RSS Feed Technical Requirements</a> for inclusion in the <a href="%2$s">Google News Publisher Center</a>.', 'gn-publisher' ),
				'https://support.google.com/news/publisher-center/answer/9545420?hl=en',
				'https://publishercenter.google.com/'
			);
	?></p>

	<p><?php _e( 'The plugin addresses common issues publishers experience when using the Google News Publisher Center, including:', 'gn-publisher' ); ?></p>

		<ul style="list-style-type:circle;padding-left: 40px;">
			<li><?php _e( 'Incomplete articles', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Duplicate images', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Missing images or media', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Missing content (usually social media/Instagram embeds)', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Title errors (missing or repeated title)', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Cached RSS feeds causing slow updating', 'gn-publisher' ); ?></li>
			<li><?php _e( 'Delayed crawling by Google', 'gn-publisher' ); ?></li>
		</ul>

		<p style="font-size:18px;padding-left: 20px;padding-top:15px;"><u><em><?php _e( 'Feed URLs', 'gn-publisher' ); ?></em></u></p>

	<p><?php _e( 'Once installed and activated, you can find your GN Publisher RSS feeds at:', 'gn-publisher' ); ?></p>

		<ul style="padding-left:40px">
 		<?php 

	/////// display feed urls, @since 1.0.2 -ca ///////////////////
			$permalinks_enabled = ! empty( get_option( 'permalink_structure' ) );

			echo '<li>' . esc_url( $permalinks_enabled ? trailingslashit( site_url() ) . 'feed/gn' : add_query_arg( 'feed', 'gn', site_url() ) ) . '</li>';
			
			$categories = get_categories(); 
			foreach( $categories as $category ) {
				$gn_category_link = get_category_link( $category->term_id );
				$gn_category_link = $permalinks_enabled ? trailingslashit( $gn_category_link ) . 'feed/gn' : add_query_arg( 'feed', 'gn', $gn_category_link );
				echo '<li>' . esc_url( $gn_category_link ) . '</li>';
			} 
 		?>
		</ul>

	<p><?php _e( 'You are not required to use all of the feeds listed above. Just use the ones you want to include in your Publisher Center. Each feed will contain the thirty most recently updated articles in its category.', 'gn-publisher' ); ?></p>

	<p><?php _e( 'When setting up a new section in the Publisher Center, I recommend setting it up as a "Feed". For the "Feed options", select "Generate articles from feed". Under "Rendering preference", select "Use website or AMP". If you have AMP on your site, the Publisher Center will render the AMP version. If you do not have AMP available, the Publisher Center will usually generate your articles from the feed.', 'gn-publisher' ); ?></p>

	<p><?php _e( 'Be sure to click that blue "Save" button in the upper right hand corner of the Publisher Center to save your changes (it\'s surprisingly easy to miss). After saving, wait ten minutes for Google to fetch your feed and render your articles. Then reload the entire page using your browser\'s reload/refresh button before checking to see if your articles appear in the Publisher Center.', 'gn-publisher' ); ?></p>

	<p><?php _e( 'After the initial setup, GN Publisher will ping Google with an alert each time your feed is updated.', 'gn-publisher' ); ?></p>

<?php //hold this and remove if all goes ok with removing the featured image setting
/*

		<p style="font-size:18px;padding-left: 20px;padding-top:15px;"><u><em><?php _e( 'GN Publisher Settings', 'gn-publisher' ); ?></em></u></p>

		<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( 'Include featured image', 'gn-publisher' ); ?></p>

	<p><?php _e( 'Enabled by default. If you see your featured image twice in your articles in the Publisher Center, disable this setting and save.', 'gn-publisher' ); ?></p>
		
*/ ?>
		<p style="font-size:18px;padding-left: 20px;padding-top:15px;"><u><em><?php _e( 'Publisher Center Troubleshooting', 'gn-publisher' ); ?></em></u></p>

<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( 'Publisher Center says "There are no articles in this section"', 'gn-publisher' ); ?></p>

	<p><?php _e( 'If you are getting the dreaded "There are no articles in this section" message in the Publisher Center:', 'gn-publisher' ); ?></p>

<ol>

<li style="margin: 15px 0;">
<?php _e( 'Refresh the section in the Publisher Center. Wait 10 to 15 minutes, then reload the entire page using your browser\'s "reload" button and recheck to see if articles appear.' ); ?>
</li>

	<li style="margin: 15px 0;">
<?php _e( 'If you\'ve refreshed the page in the Publisher Center and continue to get the same results, visit the URL you entered for the section and make sure there are articles included in the feed.' ); ?>
	
<ul style="list-style-type: circle;padding-left:20px;">
	<li style="margin: 10px 0;">
<?php _e( 'If you get a 404 or "missing" page when visiting the feed url, please review the notes in the "feed urls" section above.' ); ?>
	</li>

	<li style="margin: 10px 0;">
<?php _e( 'If there are no articles in the feed, please make sure there are articles published in that section (category) within the last 30 days.' ); ?>
	</li>
</ul>

</li>


<li style="margin: 15px 0;">
<?php _e( 'If the url works and there are articles, check the "Most Recent Feedfetcher Fetch" below:' ); ?>

		<ul style="padding-left:30px;">

		<li style="margin: 10px 0;font-size:110%;">
<?php echo '&#10132; '; ?>
<?php _e( 'Most Recent Feedfetcher Fetch: ', 'gn-publisher' ); ?>
<span style="color: MidnightBlue;"><?php echo ( is_null( $last_google_fetch ) ) ? __( 'None recorded.', 'gn-publisher' ) : $last_google_fetch; ?></span> <span style="font-weight:500;"><?php _e( '(if testing, refresh this page for most recent fetch time)', 'gn-publisher' ); ?></span>
		</li>
		</ul>

		<ul style="list-style-type: circle;padding-left:60px;">

		<li style="margin: 10px 0;">

<?php _e( 'If the "Most Recent Feedfetcher fetch" is "None recorded" or the date is more than 24 hours old, it\'s likely that your host or firewall is blocking Google\'s feed crawler, Feedfetcher. Because Feedfetcher is not a well-known bot and doesn\'t follow some of the standard crawler procedures, it is often mistakenly blocked by hosting companies and firewalls. Ask your hosting company or server administrator to whitelist the user-agent "Feedfetcher-Google". Note: If you are using AWS Cloudfront, Amazon does not pass the user-agent through to GN Publisher, so the "Most Recent Feedfetcher Fetch" timestamp will not work for you.' ); ?>

		</ul>


		<ul style="padding-left:30px;">

 		<li style="margin: 10px 0;font-size:110%;">
<?php echo '&#10132; '; ?>
<?php _e( 'Most Recent Update Ping Sent: ', 'gn-publisher' ); ?>
<span style="color: MidnightBlue;"><?php echo ( is_null( $last_websub_ping ) ) ? __( 'None recorded.', 'gn-publisher' ) : $last_websub_ping; ?></span> <span style="font-weight:500;"><?php _e( '(if testing, refresh this page for most recent ping time)', 'gn-publisher' ); ?></span>
		</li>
		</ul>

		<ul style="list-style-type: circle;padding-left:60px;">

		<li style="margin: 10px 0;">
<?php _e( 'When you publish or update a post, GN Publisher pings Google to let them know there is an update to one of your feeds. The "Most Recent Update Ping" indicates when the most recent ping was sent. Google normally fetches the feed soon thereafter (often within a minute).' ); ?>
		</li>
		</ul>

</li>



</li>

<li style="margin: 15px 0;">
<?php printf(
				__( 'If there are articles and the feed is being fetched, try running your url through the <a href="%1$s">RSS Feed Validator</a>. The feed will need to successfully validate with no html errors.', 'gn-publisher' ),
				'https://validator.w3.org/feed/'
			);
		?>


		<ul style="list-style-type: circle;padding-left:40px;">

		<li style="margin: 10px 0;">
<?php _e( 'The validator may validate but warn about iframe and script tags - those are okay for our purposes.' ); ?>
		</li>


		<li style="margin: 10px 0;">
<?php _e( 'If the validator does not validate, or validates but warns of "invalid html" (for example, a "missing &lt;p&gt; tag"), those issues can cause the crawler to not accept the feed. These errors are sometimes caused by poorly coded themes or plugins and require further investigation to correct. The &lt;p&gt; tag issue is a common one that is often caused by a &lt;figure&gt; or &lt;blockquote&gt; (or other block level element) being inside a paragraph, which is not valid html.' ); ?>
		</li>

		<li style="margin: 10px 0;">
<?php _e( 'If some Publisher Center sections are being fetched okay and others are reporting "no articles" - it\'s likely an html error that is included in an article on the specific feed that isn\'t loading properly in the Publisher Center.' ); ?>
		</li>
		</ul>

</li>
</ol>

		<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( '"Missing Images"', 'gn-publisher' ); ?></p>


	<p><?php _e( 'The Publisher Center requires that large images be used as the featured image - at least 800px on the shortest side. GN Publisher will try to use your original image, which is generally the largest. If you upload a featured image that is smaller than 800px on its shortest side, it might not appear with the article in the Publisher Center.', 'gn-publisher' ); ?></p>

	<p><?php _e( 'Note - the Publisher Center preview pane can only display .jpg and .png image files. If you are using a CDN like CloudFlare or KeyCDN, even if you have the images set up correctly, the CDN may serve them as WebP files. That will cause the images to not be displayed, or be displayed inconsistently, in the preview pane. If you are experiencing this, go the the "Review and Publish" tab in the Publisher Center, subscribe to your publication if you haven\'t already, and then click on link for your publication and make sure the images are displayed correctly there. If they are displayed on your publication in Google News, you can ignore them not being in the preview pane.', 'gn-publisher' ); ?></p>


		<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( '"Missing Media"', 'gn-publisher' ); ?></p>


	<p><?php _e( 'Social media embeds that are included in your articles should also appear as part of the article in your Publisher Center. GN Publisher is designed to properly adjust the embeds for use in the Publisher Center. If your embeds don\'t appear as they should, please contact me through the GN Publisher support forum on WordPress.org', 'gn-publisher' ); ?></p>



	<p>
<?php _e( '<br />If you continue to have trouble, please see the "Where to get help!" section below.' ); ?>
	</p>


		<p style="font-size:18px;padding-left: 20px;padding-top:15px;"><u><em><?php _e( 'General Info', 'gn-publisher' ); ?></em></u></p>

	
	<p><?php
			printf(
				__( 'Be aware that Google has certain <a href="%1$s">Content Policies</a> for sites included on Google News properties. More information about applying is available on the <a href="%2$s">Google News Publisher Help Center</a>.', 'gn-publisher' ),
				'https://support.google.com/news/publisher-center/answer/6204050',
				'https://support.google.com/news/publisher-center/'
			);
		?></p>

<p><?php _e( 'You\'ll need to meet additional requirements in the Publisher Center, such as verifying your domain, selecting an appropriate publication name, and setting up your logos correctly.', 'gn-publisher' ); ?></p>

	<p><?php _e( 'Because of the huge number of ways that publishers, plugins, and themes can manipulate WordPress posts, I can\'t guarantee that this plugin will result in the technical requirements being met.', 'gn-publisher' ); ?></p>
		

		<p style="font-size:18px;padding-left: 20px;padding-top:15px;"><u><em><?php _e( 'Where to get help!', 'gn-publisher' ); ?></em></u></p>

		<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( 'Free', 'gn-publisher' ); ?></p>

		<p><?php
			printf(
				__( 'If you need help with the GN Publisher plugin or anything related to the RSS feeds that are created, please ask on the official <a href="%1$s">WordPress GN Publisher plugin support forum</a>.', 'gn-publisher' ),
				'https://wordpress.org/support/plugin/gn-publisher/'
			);
		?></p>

	<p><?php
			printf(
				__( 'If you need general help as a Google News publisher, or help with the Google News Publisher Center, please ask for help in the official <a href="%1$s">Google News Publisher Help Forum</a>. I or some of the other regulars on the forum will try to help.', 'gn-publisher' ),
				'https://support.google.com/news/publisher-center/threads?hl=en'
			);
		?></p>

	<p><?php
			printf(
				__( 'You can also find help on my <a href="%1$s">YouTube channel</a>.', 'gn-publisher' ),
				'http://www.youtube.com/c/ChrisAndrews1'
			);
	?></p>

		<p style="font-size:15px;padding-left:10px;">&#8211;  <?php _e( 'Private Consulting', 'gn-publisher' ); ?></p>

	<p><?php _e( 'I work with a small number of news publishers on a year-round basis. My consulting focuses on discoverability and optimization for surfacing on Google News, Newsstand, Top Stories, Discover, Articles for You, and other Google related (and emerging) properties. Consultations include regular technical SEO audits, advice on content and readership development, a quarterly newsletter with advice, recommendations and updates on what\'s going on with Google News and related properties and how to best use them to gain more exposure.', 'gn-publisher' ); ?></p>

		<p><?php _e( 'Want to optimize your site and grow your visibility on Google? Contact me at <a href="mailto:chris@andrews.com">chris@andrews.com</a> for more information.', 'gn-publisher' ); ?></p>

		</div>
</div>
