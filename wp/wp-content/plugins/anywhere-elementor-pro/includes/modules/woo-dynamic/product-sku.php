<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_SKU extends Tag {

	public function get_name() {
		return 'ae-product-sku';
	}

	public function get_title() {
		return __( '(AE) Product SKU', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
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

		if ( $product->get_sku() ) {
			$value = esc_html( $product->get_sku() );
		}

		echo $value;
	}
}
