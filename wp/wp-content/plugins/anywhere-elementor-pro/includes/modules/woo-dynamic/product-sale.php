<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_Sale extends Tag {

	public function get_name() {
		return 'ae-product-sale';
	}

	public function get_title() {
		return __( '(AE) Product Sale', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'text',
			[
				'label'   => __( 'Text', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Sale!', 'ae-pro' ),
			]
		);
	}

	public function render() {
		$ae_product_data = Aepro::$_helper->get_demo_post_data();
		$product_id      = $ae_product_data->ID;

		if ( ! $product_id ) {
			return;
		}
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$value = '';

		if ( $product->is_on_sale() ) {
			$value = $this->get_settings( 'text' );
		}

		echo $value;
	}
}
