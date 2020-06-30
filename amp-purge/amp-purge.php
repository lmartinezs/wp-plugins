<?php
/*
Plugin Name: AMP CACHE Purge
Plugin URI: https://github.com/lmartinezs/wp-plugins
Description: Purge AMP Cache on Update Post.
Version: 1.0.0
Author: Lázaro M
AuthorURI: https://amp.dev/documentation/examples/guides/using_the_google_amp_cache/
*/
require_once("lib/ivsAMPCacheUpdate.php");
if( !defined( 'AMPCACHE_VER' ) )
	define( 'AMPCACHE_VER', '1.0.0' );

// Start up the engine
class AMP_Cache_Purge
{

	/**
	 * Static property to hold our instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		// back end
        //add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
        add_action('transition_post_status',array( $this, 'check_if_draft'), 10, 3 );
        add_action('wp_insert_post',array( $this, 'send_new_post'), 10, 3);
        add_action('wp_trash_post', array( $this,'send_delete_post'), 10, 1);
        
        //Panel Admin
        add_action('admin_menu', array( $this,'amp_cache_purge_options'));
        add_action( 'admin_init', array( $this,'amp_cache_purge_settings' ));
        
		//add_action		( 'admin_enqueue_scripts',				array( $this, 'admin_scripts'			)			);
		//add_action		( 'do_meta_boxes',						array( $this, 'create_metaboxes'		),	10,	2	);
		//add_action		( 'save_post',							array( $this, 'save_custom_meta'		),	1		);

		// front end
		//add_action		( 'wp_enqueue_scripts',					array( $this, 'front_scripts'			),	10		);
		//add_filter		( 'comment_form_defaults',				array( $this, 'custom_notes_filter'		) 			);
    }

    function amp_cache_purge_options() {
        add_options_page(	
            'AMP Cache Purge Admin  | Televisa',
            'AMP Cache Purge',
            'administrator',
            'amp-cache-purge',
            array( $this,'amp_cache_purge_setup_page')
        );
    }

    function amp_cache_purge_settings() {
	
        register_setting( 'amp-cache-purge-settings-group', 'amp_cache_purge_private_key' );
        register_setting( 'amp-cache-purge-settings-group', 'amp_cache_purge_domain' );                
        register_setting( 'amp-cache-purge-settings-group', 'amp_cache_purge_activate_log' );                
        
    }

    function amp_cache_purge_setup_page(){
        ?>
        <div class="wrap">
            <h2>AMP Cache Purge Settings</h2>
            <form method="post">
            <?php
			settings_fields( 'amp-cache-purge-settings-group' ); 
			do_settings_sections( 'amp-cache-purge-settings-group' );
			
			if(isset($_POST) && !empty($_POST)){
				
				if(isset($_POST["amp_cache_purge_private_key"])){
					if(get_option('amp_cache_purge_private_key')!=$_POST["amp_cache_purge_private_key"]){
						update_option('amp_cache_purge_private_key',trim(esc_sql($_POST["amp_cache_purge_private_key"])));
					}
                }
                if(isset($_POST["amp_cache_purge_domain"])){
					if(get_option('amp_cache_purge_domain')!=$_POST["amp_cache_purge_domain"]){
						update_option('amp_cache_purge_domain',trim(esc_sql($_POST["amp_cache_purge_domain"])));
					}
                }
                
                if(isset($_POST["amp_cache_purge_activate_log"]) && $_POST["amp_cache_purge_activate_log"] == "activado"){
					if(get_option('amp_cache_purge_activate_log')!=$_POST["amp_cache_purge_activate_log"]){
						update_option('amp_cache_purge_activate_log',trim(esc_sql($_POST["amp_cache_purge_activate_log"])));					
					}
				} else {
					update_option('amp_cache_purge_activate_log',trim(esc_sql("desactivado")));
				}
            }
            ?>

                <table class="form-table">
                    <tr>
                    <th scope="row">Private Key:</th>
                    <td><input type="text" name="amp_cache_purge_private_key" value="<?php echo esc_attr( get_option('amp_cache_purge_private_key') ); ?>" /></td>
                    </tr>
                    
                    <tr>
                    <th scope="row">Domain:</th>
                    <td><input type="text" name="amp_cache_purge_domain" value="<?php echo esc_attr( get_option('amp_cache_purge_domain') ); ?>" /></td>
                    </tr>

                    <tr>
						<th colspan=2 scope="row"><input type="checkbox" name="amp_cache_purge_activate_log" value="activado" <?php if( esc_attr( get_option('amp_cache_purge_activate_log') ) == "activado") echo "checked";  ?> > Enable Logs</th>
					</tr>

                    <tr>
                    <th scope="row">To create public-key.pub:</th>
                    <td>$ openssl genrsa 2048 > private-key.pem <br>
                        $ openssl rsa -in private-key.pem -pubout >public-key.pem <br>
                        $ cp public-key.pem [document-root-of-website]/.well-known/amphtml/apikey.pub</td>
                    </tr>

                </table>
                <?php submit_button(); ?>

            </form>
        </div>
    
    <?php

    }


    //
    function check_if_draft($new_status, $old_status, $post){
        if($old_status == 'publish' && $new_status == 'draft' && ($post->post_type == 'page' || $post->post_type == 'post')){
            $this->update_cache($post->id);
        }
    }

    // Listen for publishing of a post
    function send_new_post($post_id,$post){
        if($post->post_status == 'publish' && $post->post_type == 'post') {
            $this->update_cache($post_id);
        }
    }


    // Listen for deleting of a post
    function send_delete_post($post_id){
        //echo "deleting";
        if($post_status == 'publish' && ($post_type == 'post' || $post_type == 'page')) {
            $this->update_cache($post_id);
        }
    }

    function update_cache($post_id){
        $private_key = get_option('amp_cache_purge_private_key');
        $domain = get_option('amp_cache_purge_domain');
        if($private_key !== '' && $domain!==''){
            //$urls = ["url"];
            $urls = [get_post_permalink($post_id)];
            
            $cache = new Lib\IVS_AMP_CACHE_UPDATE(
                $domain,
                $urls,
                $private_key);
            
            $url = $cache->update(false);                
            $response = wp_remote_get( esc_url_raw( $url[0] ) );
        
            $api_response = wp_remote_retrieve_body( $response );
            if(get_option('amp_cache_purge_activate_log') == "activado"){
                $this->inLogAk(date('l jS \of F Y h:i:s A')." url: ".$url[0]."\n RESPONSE: ".$api_response." \n");
            }
            //print_r($api_response);die;
        }
    }

    function inLogAk($newLine){        
        $dir = plugin_dir_path( __FILE__ );        
        $file_log = $dir."log_amp_cache_".date("Y_m").".txt";                
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $file_log = "C:".str_replace("/","\\",$file_log);
        }
        $actual = file_get_contents($file_log);
        $actual .= $newLine;
        file_put_contents($file_log, $actual);
    }


	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return AMP_Cache_Purge
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	

/// end class
}


// Instantiate our class
$AMP_Cache_Purge = AMP_Cache_Purge::getInstance();
