<?php
/*
Plugin Name: WP publication manager
Description: Use this [publisher] shortcode to show the contact form anywhere you want.
Version: 1.0.0
Author: Md Kamrul islam
Author URI: https://profiles.wordpress.org/rajib00002/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-publication-manager
Domain Path: /languages
*/

// don't call the file directly

if ( !defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/vendor/autoload.php';

final class WPUM {

    public $version    = '1.0.0';
    private $container = [];

    public function __construct() {
        $this->define_constants();
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
    }

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Self();
        }

        return $instance;
    }

    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    public function define_constants() {
        define( 'WPUM_VERSION', $this->version );
        define( 'WPUM_SEPARATOR', ' | ');
        define( 'WPUM_FILE', __FILE__ );
        define( 'WPUM_ROOT', __DIR__ );
        define( 'WPUM_PATH', dirname( WPUM_FILE ) );
        define( 'WPUM_INCLUDES', WPUM_PATH . '/includes' );
        define( 'WPUM_URL', plugins_url( '', WPUM_FILE ) );
        define( 'WPUM_ASSETS', WPUM_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin() {
        $this->init_classes();
        $this->init_hooks();
        do_action( 'wpum_loaded' );
    }

    public function activate() {

    }

    public function deactivate() {

    }

    public function init_classes() {
        new WPUM\Frontend();
        $this->container['admin']    = new WPUM\Admin();
        $this->container['assets']   = new WPUM\Assets();
    }

    public function init_hooks() {
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
    }

    public function localization_setup() {
        load_plugin_textdomain( 'wp-publication-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'book_price';
        $vars[] = 'book_rating';

        return $vars;
    }
}

function wpum() {
    return WPUM::init();
}

wpum();
