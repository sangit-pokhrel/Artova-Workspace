<?php

namespace Aepro\Modules\Woo\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Aepro\Modules\Woo\Skins\WooProducts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AeWooProducts extends Widget_Base {

	protected $_has_template_content = false;

	protected $_access_level = 2;

	public function get_name() {
		return 'ae-woo-products';
	}

	public function is_enabled() {

		if ( AE_WOO ) {
			return true;
		}

		return false;
	}

	public function get_title() {
		return __( 'AE - Woo Products', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-woocommerce';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_script_depends() {
		return [ 'jquery-masonry' ];
	}

	public function get_keywords() {
		return [
			'woocommerce',
			'shop',
			'store',
			'upsell',
			'cross-sell',
			'related',
			'product',
		];
	}

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new WooProducts\Skin_Carousel( $this ) );
		$this->add_skin( new WooProducts\Skin_Grid( $this ) );
	}


	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->end_controls_section();
	}


}
