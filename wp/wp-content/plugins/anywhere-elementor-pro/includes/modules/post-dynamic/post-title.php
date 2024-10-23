<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Post_Title extends Tag {

	public function get_name() {
		return 'ae-post-title';
	}

	public function get_title() {
		return __( '(AE) Post Title', 'ae-pro' );
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
		echo wp_kses_post( $post_data->post_title );
	}
}
