=== GN Publisher: Google News Compatible RSS Feeds ===
Contributors: Christopheran,jarred-kennedy
Tags: google news, google, news, publisher center, rss, feed, feeds
Requires at least: 3.5
Tested up to: 5.5
Requires PHP: 5.4
Stable tag: 1.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

GN Publisher: The easy way to make Google News Publisher compatible RSS feeds.

== Description ==

GN Publisher makes RSS feeds that comply with the [Google News RSS Feed Technical Requirements](https://support.google.com/news/publisher-center/answer/9545420) for including your site in the [Google News Publisher Center](https://publishercenter.google.com/).

The plugin addresses common RSS compatiblity issues publishers experience when using the Google News Publisher Center, including:

-  Incomplete articles
-  Duplicate images
-  Missing images or media
-  Missing content (usually social media/Instagram embeds)
-  Title errors (missing or repeated titles)
-  Cached RSS feeds causing slow updating
-  Delayed crawling by Google

After installing, click on the *'Settings & Tips'* link under GN Publisher on your plugins page for additional information about applying and troubleshooting issues related to the Google News Publisher Center.

**New in 1.0.9**

GN Publisher now displays the time of the most recent ping and feed fetch from Google. This helps when troubleshooting the dreaded 'empty sections' issue in the Google News Publisher Center.

**New in 1.0.6**

GN Publisher now pings Google when feeds are updated. This can help with faster updates in your Google News Publication. 

== Frequently Asked Questions ==

= How can I get help with this plugin? =

If you need help with the plugin or anything related to the RSS feeds that are created, please ask on the official [WordPress GN Publisher plugin support forum](https://wordpress.org/support/plugin/gn-publisher/).

= Google News turned my site down, where can I get help with that? =

If you need help because your site has been turned down by Google, or you need other help related to the [Google News Publisher Center](https://publishercenter.google.com/), please ask for help on the official [Google News Publisher Help Forum](https://support.google.com/news/publisher-center/threads?hl=en).


== Installation ==

GN Publisher is a standard WordPress plugin and can be installed and activated through your WordPress admin section. Just search for GN Publisher in the WP plugins repository and install and activate.

GN Publisher may also be downloaded to your computer and uploaded, installed, and activated through your WP Admin plugins section.

= Minimum Requirements =

* PHP 5.4 or greater is required, PHP 7.2 or newer is recommended
* This plugin is compatible with all MySQL versions supported by WordPress

== Changelog ==

= 1.1 - 2020-11-29 =
Fix for some doubled images not being caught
Fix for some permalink examples on info page
Added Freemius opt in
= 1.0.9 - 2020-08-24 =
Added timestamp for most recent ping
Added timestamp for most recent fetch
Bug fix for pubdate timezone
Expanded troubleshooting section
Added refresh (cached) Pub Center articles upon activation
= 1.0.8 - 2020-05-25 =
bug fix for pre 5.3 versions of WP
fix for Yoast compatiblity
fix for Monster Insight compatability
Bug fix for Instagram embeds
Removed time restriction on posts incld in feeds
= 1.0.7 - 2020-04-23 =
Bug fixes affecting pre 5.3 versions of WP
= 1.0.6 - 2020-04-23 =	
Added google websub notification
= 1.0.4 - 2020-04-06 =
Syncing up version info
= 1.0.3 - 2020-04-06 =
Disabled select rss plugins from altering this feed
= 1.0.2 - 2020-04-02 =
Adjustments to featured image handling
Added cache disabling
Added GN Publisher to generator tag
Removed default feed option in settings
Added explicit feed urls on settings page
= 1.0.0 - 2020-03-10 =
This is the first release of GN Publisher
