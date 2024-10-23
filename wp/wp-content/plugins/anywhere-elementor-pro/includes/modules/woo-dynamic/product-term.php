<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Product_Term extends Tag {

	public function get_name() {
		return 'ae-product-term';
	}

	public function get_title() {
		return __( '(AE) Product Term', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-woo-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_advanced_section() {
		parent::register_advanced_section();

		$this->update_control(
			'before',
			[
				'default' => __( 'Categories', 'ae-pro' ) . ': ',
			]
		);
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$taxonomy_filter_args = [
			'show_in_nav_menus' => true,
			'object_type'       => [ 'product' ],
		];

		$taxonomies = get_taxonomies( $taxonomy_filter_args, 'objects' );

		$options = [
			'' => __( 'Select', 'ae-pro' ),
		];

		foreach ( $taxonomies as $taxonomy => $object ) {
			$options[ $taxonomy ] = $object->label;
		}

		$this->add_control(
			'taxonomy',
			[
				'label'   => __( 'Taxonomy', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $options,
				'default' => 'product_cat',
			]
		);

		$this->add_control(
			'separator',
			[
				'label'   => __( 'Separator', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ', ',
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

		$settings = $this->get_settings();

		$value = get_the_term_list( $product_id, $settings['taxonomy'], '', $settings['separator'] );

		echo $value;
	}
}
