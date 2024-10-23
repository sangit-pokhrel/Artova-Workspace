<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;


class Product_Image extends Data_Tag {

	public function get_name() {
		return 'ae-product-image';
	}

	public function get_title() {
		return __( '(AE) Product Image', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,

		];
	}

	public function get_value( array $options = [] ) {
		$ae_product_data = Aepro::$_helper->get_demo_post_data();
		$product_id      = $ae_product_data->ID;
		if ( ! $ae_product_data ) {
			return;
		}
		if ( ! $product_id ) {
			return;
		}
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$image_id = $product->get_image_id();

		if ( ! $image_id ) {
			return [];
		}

		$src = wp_get_attachment_image_src( $image_id, 'full' );

		return [
			'id'  => $image_id,
			'url' => $src[0],
		];
	}
}
