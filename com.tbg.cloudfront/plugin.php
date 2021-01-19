<?php
/*
Plugin Name: TBG - AWS Cloudfront
Plugin URI: http://twitter.com/jojagawi
Version: 1.3.2
Description: Purge content from clodfront
Author: JoJaGaWi
Author URI: http://twitter.com/jojagawi
License:

*/

/**
 * The actual plugin
 */
require_once( 'class-wpcdnpurge.php' );

//this is technically the activation hook but WP 3.3.1 doesn't run those anymore apparently...
// so this is kind of a hack
add_action('wp_loaded', array('WP_CDN_Purge', 'activate'));
$wpCdnPurge = new WP_CDN_Purge();


if(get_option( WP_CDN_Purge::OVERWTITE_IS_MOBILE)=="1"){
    add_filter( 'wp_is_mobile', function( $is_mobile ) {
        if ( isset($_SERVER['HTTP_CLOUDFRONT_IS_MOBILE_VIEWER']) && "true" === $_SERVER['HTTP_CLOUDFRONT_IS_MOBILE_VIEWER'] ) {
            $is_mobile = true;
        }
        if ( isset($_SERVER['HTTP_CLOUDFRONT_IS_TABLET_VIEWER']) && "true" === $_SERVER['HTTP_CLOUDFRONT_IS_TABLET_VIEWER'] ) {
            $is_mobile = true;
        }
        return $is_mobile;
    });
}

