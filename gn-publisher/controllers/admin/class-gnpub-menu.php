<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This controllers handles admin menu related actions.
 * 
 * @since 1.0.0
 */
class GNPUB_Menu {

	/**
	 * @var GNPUB_Notices
	 */
	protected $notices;

	public function __construct( $notices ) {
		$this->notices = $notices;

		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( GNPUB_PLUGIN_FILE ), array( $this, 'add_settings_plugin_action' ), 10, 4 );
	}

	/**
	 * Add the GN Publisher submenu to the Settings top-level menu.
	 * 
	 * @since 1.0.0
	 */
	public function register_menus() {
		add_options_page(
			__( 'GN Publisher Settings', 'gn-publisher' ),
			__( 'GN Publisher', 'gn-publisher' ),
			'manage_options',
			'gn-publisher-settings',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Outputs the settings page.
	 * 
	 * @since 1.0.0
	 */
	public function display_settings_page() {
		$custom_permalinks_enabled = ! empty( get_option( 'permalink_structure' ) );

		$include_featured_image = boolval( get_option( 'gnpub_include_featured_image', true ) );
		$is_default_feed = boolval( get_option( 'gnpub_is_default_feed', false ) );

		$last_websub_ping = get_option( 'gnpub_websub_last_ping', null );
		$last_google_fetch = get_option( 'gnpub_google_last_fetch', null );

		if ( $last_websub_ping ) {
			$last_websub_ping = date_i18n( 'Y-m-d H:i:s O', $last_websub_ping );
		}

		if ( $last_google_fetch ) {
			$last_google_fetch = date_i18n( 'Y-m-d H:i:s O', $last_google_fetch );
		}

		include GNPUB_PATH . 'templates/settings.php';
	}

	/**
	 * Displays any GN Publisher admin notices.
	 * 
	 * @since 1.0.0
	 */
	public function display_admin_notices() {
		$this->notices->display_notices();
	}

	/**
	 * Adds a convenient 'Settings' action link to the GN Publisher row on the Plugins admin page. Since the
	 * plugin doesn't include a top-level menu of its own, this link will help the user more easily find the
	 * settings page.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $actions An array of plugin action links.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array $plugin_data An array of plugin data.
	 * @param string $context The plugin context. By default this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 * 
	 * @return array
	 */
	public function add_settings_plugin_action( $actions, $plugin_file, $plugin_data, $context ) {
		$plugin_actions['settings'] = sprintf(
			'<a href="%s">' . _x( 'Info & Tips', 'Text for GN Publisher plugin settings link', 'gn-publisher' ) . '</a>',
			admin_url( 'options-general.php?page=gn-publisher-settings' )
		);

		return array_merge( $plugin_actions, $actions );
	}

}
