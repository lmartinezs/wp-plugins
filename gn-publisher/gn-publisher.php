<?php
/**
 * GN Publisher
 * 
 * @copyright 2020 Chris Andrews
 * 
 * Plugin Name: GN Publisher
 * Plugin URI: https://andrews.com/gn-publisher
 * Description: GN Publisher: The easy way to make Google News Publisher compatible RSS feeds.
 * Version: 1.1
 * Author: Chris Andrews
 * Author URI: https://andrews.com
 * Text Domain: gn-publisher
 * Domain Path: /languages
 * License: GPL v3 or later
 * 
 * GN Publisher is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * GN Publisher is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GN Publisher. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



//freemius insights---------

if ( ! function_exists( 'gp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function gp_fs() {
        global $gp_fs;

        if ( ! isset( $gp_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $gp_fs = fs_dynamic_init( array(
                'id'                  => '7207',
                'slug'                => 'gn-publisher',
                'type'                => 'plugin',
                'public_key'          => 'pk_e59bf91e81498bce1571a2edc9dff',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'gn-publisher-settings',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ) );
        }

        return $gp_fs;
    }

    // Init Freemius.
    gp_fs();

    // Signal that SDK was initiated.
    do_action( 'gp_fs_loaded' );

    function gp_fs_custom_connect_message(
        $message,
        $user_first_name,
        $product_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            __( '<span style="margin-top:5px;font-size: 18px;"> Hi %1$s</span>', 'my-text-domain' ) . ',<br>' .
            __( '<p style="margin-top:10px;font-size: 18px;font-size: 18px;">Chris Andrews of GN Publisher here.</p><p style="margin-top:10px;font-size: 18px;">I\'m working on a new project for news publishers.</p><p style="margin-top:10px;font-size: 18px;"><strong>I\'d love to invite you to be a beta tester!</strong></p><p style="margin-top:10px;font-size: 18px;">Please opt in here ("Allow & Continue") so I receive your email address. I\'ll email you when the beta test opens.</p><p style="margin-top:10px;font-size: 18px;">If you opt in, some data about your usage of GN Publisher will be also be sent to %5$s. This data helps me make future improvements to GN Publisher.</p><p style="margin-top:10px;font-size: 18px;">If you don\'t opt in, that\'s okay! GN Publisher will still work just fine, but you\'ll miss out on the beta testing invite!</p>', 'my-text-domain' ),
            $user_first_name,
            '<b>' . $product_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }

    gp_fs()->add_filter( 'connect_message_on_update', 'gp_fs_custom_connect_message', 10, 6 );
    gp_fs()->add_filter( 'connect_message', 'gp_fs_custom_connect_message', 10, 6 );
}

//gn publisher----------

function gnpub_feed_bootstrap() {
	
	if ( defined( 'GNPUB_VERSION' ) ) {
		return;
	}

	define( 'GNPUB_VERSION', '1.1' );
	define( 'GNPUB_PATH', plugin_dir_path( __FILE__ ) );
    define( 'GNPUB_URL', plugins_url( '', __FILE__) );
	define( 'GNPUB_PLUGIN_FILE', __FILE__ );

	add_action( 'plugins_loaded', 'gnpub_load_textdomain' );

	require_once GNPUB_PATH . 'utilities.php';
	require_once GNPUB_PATH . 'controllers/class-gnpub-feed.php';
	require_once GNPUB_PATH . 'controllers/class-gnpub-posts.php';
	require_once GNPUB_PATH . 'controllers/class-gnpub-websub.php';
	require_once GNPUB_PATH . 'class-gnpub-compat.php';

	new GNPUB_Feed();
	new GNPUB_Posts();
	new GNPUB_Websub();
	GNPUB_Compat::init();

	if ( is_admin() ) {
		require_once GNPUB_PATH . 'class-gnpub-installer.php';
		require_once GNPUB_PATH . 'class-gnpub-notices.php';
		require_once GNPUB_PATH . 'controllers/admin/class-gnpub-menu.php';
		require_once GNPUB_PATH . 'controllers/admin/class-gnpub-settings.php';

		register_activation_hook( __FILE__, array( 'GNPUB_Installer', 'install' ) );
		register_deactivation_hook( __FILE__, array( 'GNPUB_Installer', 'uninstall' ) );

		$admin_notices = new GNPUB_Notices();

		new GNPUB_Menu( $admin_notices );
		new GNPUB_Settings( $admin_notices );
	}

}

gnpub_feed_bootstrap();

function gnpub_load_textdomain() {
	load_plugin_textdomain( 'gn-publisher', false, basename( dirname( GNPUB_PLUGIN_FILE ) ) . '/languages/' );
}
