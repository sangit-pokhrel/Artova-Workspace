<?php

namespace Aepro\Modules\PostBlocksAdv;

use Aepro\Aepro;
use Aepro\Base\ModuleBase;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-blocks-adv',
		];
	}

	public function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_ae_post_adv_data', [ $this, 'ajax_post_adv_data' ] );
		add_action( 'wp_ajax_nopriv_ae_post_adv_data', [ $this, 'ajax_post_adv_data' ] );

		add_shortcode( 'ae_woo_product_discount_percentage', [ $this, 'get_ae_woo_product_discount_percentage' ] );
	}

	public function ajax_post_adv_data() {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$fetch_mode = $_REQUEST['fetch_mode'];

		$results = [];
		switch ( $fetch_mode ) {
			case 'paged':
				ob_start();
				//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->get_widget_output( $_POST['pid'], $_POST['wid'] );
				$results = ob_get_contents();
				ob_end_clean();
				break;
		}

		wp_send_json_success( $results );
	}

	public function get_widget_output( $post_id, $widget_id ) {
		$elementor = Plugin::$instance;

		$meta = $elementor->documents->get( $post_id )->get_elements_data();

		$widget = $this->find_element_recursive( $meta, $widget_id );

		$widget_instance = $elementor->elements_manager->create_element_instance( $widget );

		$widget['settings'] = $widget_instance->get_active_settings();

		if ( isset( $widget['settings'] ) ) {

			if ( $widget['widgetType'] === 'ae-post-blocks-adv' ) {

				$current_skin = $widget_instance->get_current_skin();
				$current_skin->set_parent( $widget_instance );
				$current_skin->generate_output( $widget['settings'], false );

			}
		}
	}

	private function find_element_recursive( $elements, $widget_id ) {
		foreach ( $elements as $element ) {
			if ( $widget_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $widget_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}

	public function get_ae_woo_product_discount_percentage() {
		if ( get_post_type() !== 'product' ) {
			return;
		}
		$product = Aepro::$_helper->get_demo_post_data();
		$product = wc_get_product( $product->ID );
		if ( ! $product->is_on_sale() ) {
			return;
		}
		if ( $product->is_type( 'simple' ) ) {
			$max_percentage = ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100;
		} elseif ( $product->is_type( 'variable' ) ) {
			$max_percentage = 0;
			foreach ( $product->get_children() as $child_id ) {
				$variation = wc_get_product( $child_id );
				$price     = $variation->get_regular_price();
				$sale      = $variation->get_sale_price();
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $price != 0 && ! empty( $sale ) ) {
					$percentage = ( $price - $sale ) / $price * 100;
				}
				if ( $percentage > $max_percentage ) {
					$max_percentage = $percentage;
				}
			}
		}
		return ceil( $max_percentage );
	}
}
