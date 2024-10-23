<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_Rating extends Tag {

	public function get_name() {
		return 'ae-product-rating';
	}

	public function get_title() {
		return __( '(AE) Product Rating', 'ae-pro' );
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
			'field',
			[
				'label'   => __( 'Format', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'average_rating' => __( 'Average Rating', 'ae-pro' ),
					'rating_count'   => __( 'Rating Count', 'ae-pro' ),
					'review_count'   => __( 'Review Count', 'ae-pro' ),
				],
				'default' => 'average_rating',
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
			return '';
		}

		$field = $this->get_settings( 'field' );
		$value = '';
		switch ( $field ) {
			case 'average_rating':
				$value = $product->get_average_rating();
				break;
			case 'rating_count':
				$value = $product->get_rating_count();
				break;
			case 'review_count':
				$value = $product->get_review_count();
				break;
		}

		echo $value;
	}
}
