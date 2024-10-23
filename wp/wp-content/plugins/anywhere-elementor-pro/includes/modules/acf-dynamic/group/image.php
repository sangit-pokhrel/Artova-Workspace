<?php

namespace Aepro\Modules\AcfDynamic\Group;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Aepro\Aepro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Image extends Data_Tag {


	public function get_name() {
		return 'ae-acf-group-image';
	}

	public function get_title() {
		return __( '(AE) ACF Group Image', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
		];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function get_value( array $options = [] ) {
		$image_data = [
			'id'  => null,
			'url' => '',
		];

		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return [];
		}
		
		list($field, $value) = AcfGroupDynamicHelper::instance()->get_acf_field_value( $this );
		if ( $field && is_array( $field ) ) {
			if ( $field['type'] === 'url' ) {
				$value = [
					'id'  => 0,
					'url' => $value,
				];
			} else {
				$field['return_format'] = isset( $field['save_format'] ) ? $field['save_format'] : $field['return_format'];
				if ( ! empty( $value ) ) {
					switch ( $field['return_format'] ) {
						case 'object':
						case 'array':
							$value = $value;
							break;
						case 'url':
							$value = [
								'id'  => 0,
								'url' => $value,
							];
							break;
						case 'id':
							$src   = wp_get_attachment_url( $value );
							$value = [
								'id'  => $value,
								'url' => $src,
							];
							break;
					}
				}
			}
		}

		if ( ! empty( $value ) && is_array( $value ) ) {
			$image_data['id']  = $value['id'];
			$image_data['url'] = $value['url'];
		}

		if ( empty( $value ) && $settings['fallback'] ) {
			$image_data = [
				'id'  => $settings['fallback']['id'],
				'url' => $settings['fallback']['url'],
			];
		}
		return $image_data;
	}
// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		AcfGroupDynamicHelper::instance()->register_ae_dynamic_group_controls( $this, $this->get_supported_fields() );

		$this->add_control(
			'fallback',
			[
				'label' => __( 'Fallback', 'ae-pro' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);
	}

	public function get_supported_fields() {

		return [
			'image',
			'file',
			'url',
		];
	}
}
