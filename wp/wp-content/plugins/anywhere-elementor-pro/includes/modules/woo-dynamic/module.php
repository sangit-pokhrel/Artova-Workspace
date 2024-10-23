<?php

namespace Aepro\Modules\WooDynamic;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
	}

	public function register_dynamic_tags( $dynamic_tags ) {

		if ( class_exists( 'woocommerce' ) ) {
			\Elementor\Plugin::$instance->dynamic_tags->register_group(
				'ae-woo-dynamic',
				[
					'title' => __( 'WooCommerce (AE)', 'ae-pro' ),
				]
			);
			$dynamic_tags->register( new Product_Title() );
			$dynamic_tags->register( new Product_Price() );
			$dynamic_tags->register( new Product_Sale() );
			$dynamic_tags->register( new Product_SKU() );
			$dynamic_tags->register( new Product_Rating() );
			$dynamic_tags->register( new Product_Stock() );
			$dynamic_tags->register( new Product_Short_Description() );
			$dynamic_tags->register( new Product_Term() );
			$dynamic_tags->register( new Product_Image() );
			$dynamic_tags->register( new Product_Gallery() );
			$dynamic_tags->register( new Product_Cat_Image() );
		}
	}

}
