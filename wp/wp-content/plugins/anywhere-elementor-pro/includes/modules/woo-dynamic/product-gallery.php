<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;


class Product_Gallery extends Data_Tag {

	public function get_name() {
		return 'ae-product-gallery';
	}

	public function get_title() {
		return __( '(AE) Product Gallery', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
		];
	}

	public function get_value( array $options = [] ) {
		$ae_product_data = Aepro::$_helper->get_demo_post_data();
		$product_id      = $ae_product_data->ID;

		if ( ! $product_id ) {
			return;
		}
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$value = [];

		$attachment_ids = $product->get_gallery_image_ids();

		foreach ( $attachment_ids as $attachment_id ) {
			$value[] = [
				'id' => $attachment_id,
			];
		}

		return $value;
	}
}
