<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Data_Tag;

class Post_Gallery extends Data_Tag {
	public function get_name() {
		return 'ae-post-gallery';
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_title() {
		// TODO: Implement get_title() method.
		return __( '(AE) Post Image Attachments', 'ae-pro' );
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
		];
	}

	public function get_value( array $options = [] ) {
		$settings  = $this->get_settings_for_display();
		$post_data = Aepro::$_helper->get_demo_post_data();
		$post_id   = $post_data->ID;
		$images    = get_attached_media( 'image', $post_id );
		if ( empty( $images ) ) {
			return;
		}
		$value = [];

		foreach ( $images as $image ) {
			$value[] = [
				'id' => $image->ID,
			];
		}

		return $value;
	}
}
