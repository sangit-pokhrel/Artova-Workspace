<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Group_Control_Image_Size;

class Post_Featured_Image extends Data_Tag {

	public function get_name() {
		return 'ae-post-featured-image';
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_title() {
		// TODO: Implement get_title() method.
		return __( '(AE) Post Featured Image', 'ae-pro' );
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
		];
	}


    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'thumbnail', // // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
				'exclude' => [ 'custom' ],
				'include' => [],
				'default' => 'large',
			]
		);
		$this->add_control(
			'fallback',
			[
				'label' => __( 'Fallback', 'ae-pro' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);
	}
	public function get_value( array $options = [] ) {
		$settings     = $this->get_settings_for_display();
		$post_data    = Aepro::$_helper->get_demo_post_data();
		$post_id      = $post_data->ID;
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$image_data = [
				'id'  => $thumbnail_id,
				'url' => wp_get_attachment_image_src( $thumbnail_id, $settings['thumbnail_size'] )[0],
			];
		} else {
			$image_data = $settings['fallback'];
		}
		return $image_data;
	}

}
