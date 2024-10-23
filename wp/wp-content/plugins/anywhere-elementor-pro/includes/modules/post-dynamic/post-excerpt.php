<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Post_Excerpt extends Tag {

	public function get_name() {
		return 'ae-post-excerpt';
	}

	public function get_title() {
		return __( '(AE) Post Excerpt', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function render() {
		$post_data = Aepro::$_helper->get_demo_post_data();
		if ( empty( $post_data->post_excerpt ) ) {
			return;
		}
		echo wp_kses_post( $post_data->post_excerpt );
	}
}
