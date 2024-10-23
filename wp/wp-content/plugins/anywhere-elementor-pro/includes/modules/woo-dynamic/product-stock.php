<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_Stock extends Tag {

	public function get_name() {
		return 'ae-product-stock';
	}

	public function get_title() {
		return __( '(AE) Product Stock', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'show_text',
			[
				'label'     => __( 'Show Text', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Show', 'ae-pro' ),
				'label_off' => __( 'Hide', 'ae-pro' ),
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

		if ( 'yes' === $this->get_settings( 'show_text' ) ) {
			$value = wc_get_stock_html( $product );
		} else {
			$value = $product->get_stock_quantity();
		}

		echo $value;
	}
}
