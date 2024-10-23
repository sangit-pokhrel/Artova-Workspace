<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Data_Tag;

class Post_Url extends Data_Tag {

	public function get_name() {
		return 'ae-post-url';
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_title() {
		// TODO: Implement get_title() method.
		return __( '(AE) Post Url', 'ae-pro' );
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}




	public function get_value( array $options = [] ) {
		$post_data = Aepro::$_helper->get_demo_post_data();
		$post_url  = get_permalink( $post_data->ID );
		return $post_url;
	}

}
