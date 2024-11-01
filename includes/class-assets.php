<?php
namespace WPUM;

class Assets {

	public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend' ] );
	}

	public static function init() {
		static $instance = false;

		if( !$instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public static function enqueue_frontend_scripts() {
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'library-frontend' );
		wp_enqueue_style( 'library-star' );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-slider' );

		wp_enqueue_script( 'library-rating' );
		wp_enqueue_script( 'library-frontend' );
	}

	public function register_frontend() {
		wp_register_style( 'jquery-ui', WPUM_ASSETS . '/css/jquery-ui-1.9.1.custom.css', false, false, 'all' );
		wp_register_style( 'jquery-selectize', WPUM_ASSETS . '/css/selectize.css', false, false, 'all' );
		wp_register_style( 'library-frontend', WPUM_ASSETS . '/css/frontend.css', false, false, 'all' );
		wp_register_style( 'library-star', WPUM_ASSETS . '/css/stars.css', false, false, 'all' );
		wp_register_script( 'library-rating',WPUM_ASSETS . '/js/jquery.barrating.min.js' , [ 'jquery' ], false, true );
		wp_register_script( 'jquery-selectize',WPUM_ASSETS . '/js/selectize.min.js' , [ 'jquery' ], false, true );
		wp_register_script( 'library-frontend',WPUM_ASSETS . '/js/frontend.js' , [ 'jquery', 'jquery-ui-core', 'jquery-ui-slider'], false, true );
	}
}

Assets::init();