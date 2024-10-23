<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_Price extends Tag {

	public function get_name() {
		return 'ae-product-price';
	}

	public function get_title() {
		return __( '(AE) Product Price', 'ae-pro' );
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
			'format',
			[
				'label'   => __( 'Format', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'both'     => __( 'Both', 'ae-pro' ),
					'original' => __( 'Original', 'ae-pro' ),
					'sale'     => __( 'Sale', 'ae-pro' ),
				],
				'default' => 'both',
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

		$format = $this->get_settings( 'format' );
		$value  = '';
		switch ( $format ) {
			case 'both':
				$value = $product->get_price_html();
				break;
			case 'original':
				$value = wc_price( $product->get_regular_price() ) . $product->get_price_suffix();
				break;
			case 'sale' && $product->is_on_sale():
				$value = wc_price( $product->get_sale_price() ) . $product->get_price_suffix();
				break;
		}

		echo $value;
	}
}
