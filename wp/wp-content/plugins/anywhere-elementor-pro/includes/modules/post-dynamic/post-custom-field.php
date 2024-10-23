<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Post_Custom_Field extends Tag {

	public function get_name() {
		return 'ae-post-custom-field';
	}

	public function get_title() {
		return __( '(AE) Post Custom Field', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function is_settings_required() {
		return true;
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY,
		];
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label'   => __( 'Key', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_custom_keys_array(),
			]
		);

		$this->add_control(
			'custom_key',
			[
				'label'       => __( 'Custom Key', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'key',
				'condition'   => [
					'key' => '',
				],
			]
		);
	}

	public function render() {
		$post_data = Aepro::$_helper->get_demo_post_data();
		$post_id   = $post_data->ID;
		$key       = $this->get_settings( 'key' );

		if ( empty( $key ) ) {
			$key = $this->get_settings( 'custom_key' );
		}

		if ( empty( $key ) ) {
			return;
		}

		$value = get_post_meta( $post_id, $key, true );

		echo wp_kses_post( $value );
	}

	public function get_custom_keys_array() {
		$post_data   = Aepro::$_helper->get_demo_post_data();
		$custom_keys = get_post_custom_keys( $post_data->ID );
		$options     = [
			'' => __( 'Select...', 'ae-pro' ),
		];

		if ( ! empty( $custom_keys ) ) {
			foreach ( $custom_keys as $custom_key ) {
				if ( '_' !== substr( $custom_key, 0, 1 ) ) {
					$options[ $custom_key ] = $custom_key;
				}
			}
		}

		return $options;
	}
}
