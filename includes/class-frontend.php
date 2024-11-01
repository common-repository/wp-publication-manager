<?php
namespace WPUM;
use WPUM\Assets;

class Frontend {

	public function __construct() {
		add_shortcode( 'publisher', [ $this, 'render_book' ] );
		add_action( 'wpum_book_list', [ $this, 'render_book'] );
	}

	public function render_book() {
		ob_start();
		Assets::init()->register_frontend();
		Assets::init()->enqueue_frontend_scripts();
		include WPUM_PATH . '/templates/book-form.php';
		include WPUM_PATH . '/templates/book-list.php';
		$contents = ob_get_contents();
		ob_end_clean();
		echo $contents; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}
}