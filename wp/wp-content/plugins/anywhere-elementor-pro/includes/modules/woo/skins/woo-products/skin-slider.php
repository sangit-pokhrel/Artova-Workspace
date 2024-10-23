<?php
namespace Aepro\Woo_Products\Skins;

use Elementor\Widget_Base;

class Skin_Slider extends Skin_Base {

	public function get_id() {
		return 'slider';
	}

	public function get_title() {
		return __( 'Slider', 'ae-pro' );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		parent::product_query_controls();
		parent::common_controls();
		parent::pagination_controls();
	}
	public function register_style_controls() {
		parent::common_style_control();
	}
	public function render() {
		parent::swiper_html();
	}
}
