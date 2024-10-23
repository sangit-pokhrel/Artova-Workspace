<?php
namespace Aepro\Modules\Woo\Skins\WooProducts;

use Aepro\Base\Widget_Base;

class Skin_Carousel extends Skin_Base {

	public function get_id() {
		return 'carousel';
	}

	public function get_title() {
		return __( 'Carousel', 'ae-pro' );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		parent::product_query_controls();
		parent::product_carousel_control();
	}
	public function register_style_controls() {
		parent::grid_style_control();
		parent::product_carousel_style();
	}
	public function render() {
		parent::swiper_html();
	}
	public function register_layout_controls() {
		$this->update_control(
			'effect',
			[
				'options' => [
					'slide'     => __( 'Slide', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
				],
			]
		);
	}
}
