<?php
namespace Aepro\Modules\AcfFlexibleContent\Skins;

use Aepro\Aepro;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Skin_Base as Elementor_Skin_Base;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;
	}
}
