<?php

namespace Aepro;

use Elementor\Plugin;
use Aepro\Frontend;

class Shortcode {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		add_shortcode( 'INSERT_ELEMENTOR', [ $this, 'render_shortcode' ] );

		add_filter( 'widget_text', 'do_shortcode' );
	}

	public function render_shortcode( $atts ) {
		if ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
			return '';
		}

		$template_id = (int)$atts['id'];

		if(is_null(get_post($template_id))){
			return '';
		}

		return Frontend::instance()->render_insert_elementor( $template_id );
	}
}
