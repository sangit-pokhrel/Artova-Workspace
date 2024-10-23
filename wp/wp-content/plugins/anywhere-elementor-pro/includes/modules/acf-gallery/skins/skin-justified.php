<?php
namespace Aepro\Modules\AcfGallery\Skins;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Skin_Justified extends Skin_Base {

	public function get_id() {
		return 'justified';
	}

	public function get_title() {
		return __( 'Justified', 'ae-pro' );
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;
		parent::field_control();
		$this->remove_control( 'enable_image_ratio' );
		parent::justified_control();
	}

	public function register_layout_controls() {
		$this->remove_control( 'enable_image_ratio' );
	}

	public function register_style_controls() {
		parent::grid_style_control();
	}

	public function register_overlay_controls() {
		parent::grid_overlay_controls();
	}
	public function register_overlay_style_controls() {
		parent::grid_overlay_style_control();
	}

	public function render() {
		// TODO: Implement render() method.
		parent::justified_html();
	}
}
