<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;


class Product_Cat_Image extends Data_Tag {

	public function get_name() {
		return 'ae-product-cat-image';
	}

	public function get_title() {
		return __( '(AE) Product Category Image', 'ae-pro' );
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
		$GLOBALS['post'];
		$image_data  = [];
		$category_id = '';

		if ( is_product_category() ) {
			$category_id = get_queried_object_id();
		} elseif ( is_product() ) {
			$ae_product_data = Aepro::$_helper->get_demo_post_data();
			$product_id      = $ae_product_data->ID;
			$product         = wc_get_product( $product_id );
			if ( ! $product ) {
				return;
			}
			if ( $product ) {
				$category_ids = $product->get_category_ids();
				if ( ! empty( $category_ids ) ) {
					$category_id = $category_ids[0];
				}
			}
		}

		if ( $GLOBALS['post']->post_type === 'ae_global_templates' ) {
			$ae_post_ID     = $GLOBALS['post']->ID;
			$ae_render_mode = get_post_meta( $ae_post_ID, 'ae_render_mode', true );
			switch ( $ae_render_mode ) {
				case 'archive_template':
					$term_data   = Aepro::$_helper->get_preview_term_data();
					$category_id = $term_data['prev_term_id'];
					break;
				case 'post_template':
				case 'block_layout':
					$ae_product_data = Aepro::$_helper->get_demo_post_data();
					$product_id      = $ae_product_data->ID;
					if ( ! $product_id ) {
						return [];
					}
					$product = wc_get_product( $product_id );
					if ( ! $product ) {
						return [];
					}
					if ( $product ) {
						$category_ids = $product->get_category_ids();
						if ( ! empty( $category_ids ) ) {
							$category_id = $category_ids[0];
						}
					}
					break;
			}
		}
		if ( ! $category_id ) {
			return [];
		}

		$image_id = get_term_meta( $category_id, 'thumbnail_id', true );

		if ( empty( $image_id ) ) {
			return [];
		}

		$src = wp_get_attachment_image_src( $image_id, 'full' );

		$image_data = [
			'id'  => $image_id,
			'url' => $src[0],
		];
		return $image_data;
	}
}
