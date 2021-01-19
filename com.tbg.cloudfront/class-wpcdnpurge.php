<?php
/**
 * External/shared functions
 */
// require_once( 'functions.php' );

class WP_CDN_Purge {
    /**
     * The name of the plugin
     */
    const NAME = 'Cloudfront';

    /**
     * The slug to use for plugin URLs
     */
    const SLUG = 'wpcdnpurge';

    /**
     * Required user capability
     */
    const REQUIRED_CAPABILITY = 'manage_options';

    /**
     * Version of the plugin
     */
    const VERSION = '1.2';

    /**
     * wp_options key for the plugin version
     */
    const VERSION_KEY = 'wpcdnpurge-version';

    private $debug = false;

    const COLDFRONTDOMAIN_KEY           = 'wpcdnpurge-cfdomain';
    const COLDFRONTKEYID_KEY            = 'wpcdnpurge-cfkey';
    const COLDFRONTDECRETKEY_KEY        = 'wpcdnpurge-cfsecret';
    const COLDFRONTDISTRIBUTIONID_KEY   = 'wpcdnpurge-cfdistibution';
    const RECURSIVE_KEY                 = 'wpcdnpurge-recursive';
    const OVERWTITE_IS_MOBILE           = 'wpcdnpurge-is_mobile';
    const SINGLE_PATH                   = 'wpcdnpurge-single_path';
    const FLUSHDEBUG                    = 'wpcdnpurge-debug';

    var $arrFlush = array ();


    /**
     * Creates a new WP_CDN_Rewrite object
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    object  A new WP_CDN_Rewrite object
     */
    public function __construct() {
        // Only register the admin call backs if we're in the admin app
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_javascript' ) );
        }

        register_uninstall_hook( __FILE__, array( 'WP_CDN_Purge', 'uninstall' ) );

        add_action( 'save_post', array( $this, 'startup' ) );


        $host = $_SERVER['HTTP_HOST'];
        // add_option only runs if the option doesn't exist
        add_option( self::VERSION_KEY, self::VERSION );

        add_option( self::COLDFRONTDOMAIN_KEY, $host );
        add_option( self::COLDFRONTKEYID_KEY, "" );
        add_option( self::COLDFRONTDECRETKEY_KEY, "" );
        add_option( self::COLDFRONTDISTRIBUTIONID_KEY, "" );
        add_option( self::RECURSIVE_KEY, "" );
        add_option( self::OVERWTITE_IS_MOBILE, "" );
        add_option( self::FLUSHDEBUG, "" );

        if(get_option( WP_CDN_Purge::FLUSHDEBUG)=="1"){
            $this->debug = true;
        }
    }

    /**
     * Filter to start buffering at the start of WordPress' work
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public function startup($post_id) {
	    if(get_post_status($post_id)=="publish") {
		    if ($this->debug) error_log( "Iniciando Flush: ".$post_id);
            $path = $this->obtienePath($post_id);
            $this->arrFlush = array ();
            $this->setArrayFlush($path,get_option( WP_CDN_Purge::RECURSIVE_KEY));
            $this->updateCategoryHomes($post_id);
            if ($this->debug) error_log('{"objects":['.implode(",",$this->arrFlush).']}');
            $this->flushCloudFront($this->arrFlush);

	    } else {
		    return;
	    }
    }
    public function clearPath($post_url){
        $post_url = str_replace($_SERVER['HTTP_HOST'],"",$post_url);
        $post_url = str_replace("http://","",$post_url);
        $post_url = str_replace("https://","",$post_url);
        return($post_url);
    }
    private function obtienePath($post_id){
        return $this->clearPath(get_permalink( $post_id ));
    }
    private function setArrayFlush($path, $recursive) {
        // if ($this->debug) error_log("Path Flush: ".$path);
        $this->arrFlush[]=$path;
        if($recursive) {
            $arrPath = explode("/",$path);
            if (count($arrPath)>2){
                $tmpPath="/";
                if ($arrPath[count($arrPath)-1]==""){
                    $tot = count($arrPath)-2;
                }else {
                    $tot = count($arrPath)-1;
                }
                for($x=1;$x<$tot;$x++)
                {
                    $tmpPath.=$arrPath[$x]."/";
                }
                self::setArrayFlush($tmpPath,$recursive);
            }
        }
    }
    private function updateCategoryHomes($post_id) {
        $post_categories = wp_get_post_categories( $post_id );
        foreach($post_categories as $c){
            $catUrl = get_category_link($c);
            $catUrl = $this->clearPath($catUrl);
            $this->arrFlush[] = $catUrl;
	    $this->arrFlush[] = $catUrl."feed/";
	    $this->arrFlush[] = $catUrl."feed/gn";
            $this->pushtoFeed($catUrl."feed/");
	    $this->pushtoFeed($catUrl."feed/gn");
            if ($this->debug) error_log("link home: ".$catUrl);
        }
    }

    public function pushtoFeed($url){
        //Update Google PubSubHubbub
        $data = array(
          'hub.mode' => 'publish',
          'hub.url' => get_site_url().$url
          );
        if ($this->debug) error_log("push feed: ".get_site_url().$url);
        $handle2 = curl_init('https://pubsubhubbub.appspot.com/');
        curl_setopt($handle2, CURLOPT_POST, true);
        curl_setopt($handle2, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handle2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle2, CURLOPT_HTTPHEADER,
         array('Content-Type: application/x-www-form-urlencoded'));
        $result = curl_exec($handle2);
        curl_close($handle2);
    }


    public function flushCloudFront ($path) {
        if ($this->debug) error_log("Flush CloudFront ");

        $includePath = str_replace("class-wpcdnpurge.php","lib/PEAR/",__FILE__);

        set_include_path($includePath);
        require_once 'HTTP/Request2.php';
        require_once 'lib/CloudFront.php';


        $cfUser = get_option( WP_CDN_Purge::COLDFRONTKEYID_KEY);
        $cfPass = get_option( WP_CDN_Purge::COLDFRONTDECRETKEY_KEY);
        $cfDist = get_option( WP_CDN_Purge::COLDFRONTDISTRIBUTIONID_KEY);

        if ($cfUser=="" or $cfPass=="" or $cfDist=="") {
            error_log("Falta agregar la cuenta de ClodFront");
            return 0;
        } else {
            $cf = new CloudFront($cfUser, $cfPass, $cfDist);
            $wait4me = [];
            if(isset($path)){
                $wait4me = $cf->invalidate($path);
                if ($this->debug) error_log("CloudFront => ".$wait4me);
                if(strpos($wait4me, "400") === 0){
                    if ($this->debug) error_log("CloudFront => Cola Llena");
                    sleep(10);
                    $this->flushCloudFront($path);
                }
            }else{
                $wait4me = $cf->invalidate($this->arrFlush);
                if ($this->debug) error_log("CloudFront => ".$wait4me);
                if(strpos($wait4me, "400") === 0){
                    if ($this->debug) error_log("CloudFront => Cola Llena");
                    sleep(10);
                    $this->flushCloudFront();
                }
            }
            return 1;
        }
    }


    /**
     * The admin_init hook runs as soon as the admin initializes and we use it
     * to add our settings to the whitelist of allowed options
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public function admin_init() {
        register_setting( 'wpcdnpurge', self::COLDFRONTDOMAIN_KEY, "" );
        register_setting( 'wpcdnpurge', self::COLDFRONTKEYID_KEY, "" );
        register_setting( 'wpcdnpurge', self::COLDFRONTDECRETKEY_KEY, "" );
        register_setting( 'wpcdnpurge', self::COLDFRONTDISTRIBUTIONID_KEY, "" );
        register_setting( 'wpcdnpurge', self::RECURSIVE_KEY, "" );
        register_setting( 'wpcdnpurge', self::OVERWTITE_IS_MOBILE, "" );
        register_setting( 'wpcdnpurge', self::FLUSHDEBUG, "" );
    }

    /**
     * Adds a link to our settings page under the Settings menu
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public function admin_menu() {
        add_options_page( self::NAME, self::NAME, self::REQUIRED_CAPABILITY, self::SLUG, array( $this, 'show_config' ) );
    }

    /**
     * adds the necessary wordpress options for the plugin to use later. Only runs on activation
     *
     * @return    void
     */
    public function activate() {
        $host = $_SERVER['HTTP_HOST'];
        // add_option only runs if the option doesn't exist
        add_option( self::VERSION_KEY, self::VERSION );

        add_option( self::COLDFRONTDOMAIN_KEY, $host );
        add_option( self::COLDFRONTKEYID_KEY, "" );
        add_option( self::COLDFRONTDECRETKEY_KEY, "" );
        add_option( self::COLDFRONTDISTRIBUTIONID_KEY, "" );
        add_option( self::RECURSIVE_KEY, "" );
        add_option( self::OVERWTITE_IS_MOBILE, "" );
        add_option( self::FLUSHDEBUG, "" );

    }

    /**
     * Adds admin.js to the <head>
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public function include_admin_javascript() {
        wp_enqueue_script( 'admin.js', plugins_url( 'html/admin.js', __FILE__ ), array( 'jquery' ) );
    }

    /**
     * Shows the configuration page within the settings
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public function show_config() {
        if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        require_once( 'html/config.php' );
    }


    /**
     * Deletes all of the stuff we put into the database so that we don't leave anything behind to corrupt future installs
     *
     * @package WP CDN Rewrite
     * @since 1.0
     *
     * @return    void
     */
    public static function uninstall() {
        delete_option( self::VERSION_KEY );

        delete_option( self::COLDFRONTDOMAIN_KEY );
        delete_option( self::COLDFRONTKEYID_KEY );
        delete_option( self::COLDFRONTDECRETKEY_KEY );
        delete_option( self::COLDFRONTDISTRIBUTIONID_KEY);
        delete_option( self::RECURSIVE_KEY);
        delete_option( self::OVERWTITE_IS_MOBILE);
        delete_option( self::FLUSHDEBUG);
    }
}
