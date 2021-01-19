<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class contains methods used to install the plugin.
 * 
 * @since 1.0.0
 */
final class GNPUB_Installer {

	private static $default_options = array(
		'gnpub_include_featured_image' => true,
		'gnpub_is_default_feed' => false
	);

	/**
	 * Install the plugin.
	 * 
	 * @since 1.0.0
	 */
	public static function install() {
		self::set_option_defaults();

		update_option( 'gnpub_installed_version', GNPUB_VERSION, false );
		update_option( 'gnpub_last_activation', current_time( 'timestamp', true ) );

		$feed = new GNPUB_Feed();
		$feed->add_google_news_feed();

		GNPUB_Compat::yoast_seo_strip_category_base();

		// The rewrite rules need a soft-flush to register our custom /feed/gn route rule.
		flush_rewrite_rules( false );
	}

	/**
	 * Uninstall the plugin, actually called on deactivation not uninstall.
	 * 
	 * @since 1.0.9
	 */
	public static function uninstall() {
		update_option( 'gnpub_last_deactivation', current_time( 'timestamp', true ) );
	}

	/**
	 * Sets the default options for the plugin. These options are only read when the feed is loaded, since the
	 * feed is not loaded often, and only loaded by automated readers, having the options not autoloaded makes
	 * sense.
	 * 
	 * @since 1.0.0
	 */
	private static function set_option_defaults() {
		foreach ( self::$default_options as $option_name => $option_default ) {
			add_option( $option_name, $option_default, false );
		}
	}

}