<?php
namespace Aepro\Modules\AcfGallery\Skins;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Skin_Carousel extends Skin_Base {

	public function get_id() {
		return 'carousel';
	}

	public function get_title() {
		return __( 'Carousel', 'ae-pro' );
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/ae-acf-gallery/section_layout/before_section_end', [ $this, 'register_layout_controls' ] );
		add_action( 'elementor/element/ae-acf-gallery/section_layout/after_section_end', [ $this, 'register_thumbnail_controls' ] );
		add_action( 'elementor/element/ae-acf-gallery/section_style/after_section_end', [ $this, 'register_thumbnail_styles' ] );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		parent::field_control();
		parent::image_carousel_control();
		parent::common_controls();
		parent::pagination_controls();
		parent::navigation_controls();
	}

	public function register_thumbnail_controls(){
		parent::thumbnail_controls();
	}

	public function register_thumbnail_styles(){
		parent::thumbnail_styles();
	}

	public function render() {
		// TODO: Implement render() method.
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

	public function register_style_controls() {

		parent::carousel_styles();
	}

	public function register_overlay_controls() {
	}

	public function register_overlay_style_controls() {
	}



}
