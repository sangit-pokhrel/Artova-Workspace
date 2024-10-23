<?php
namespace Aepro\Modules\AcfDynamic\Group;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Aepro\Aepro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Gallery extends Data_Tag {


	public function get_name() {
		return 'ae-acf-group-gallery';
	}

	public function get_title() {
		return __( '(AE) ACF Group Gallery', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
		];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}
// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		AcfGroupDynamicHelper::instance()->register_ae_dynamic_group_controls( $this, $this->get_supported_fields() );
	}

	public function get_supported_fields() {
		return [
			'gallery',
		];
	}

	public function get_value( array $options = [] ) {
		$images = [];
		// TODO: Implement get_value() method.
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return [];
		}
		list($field, $value) = AcfGroupDynamicHelper::instance()->get_acf_field_value( $this );
		if ( empty( $field ) ) {
			return;
		}
		$field['return_format'] = isset( $field['save_format'] ) ? $field['save_format'] : $field['return_format'];
		if ( empty( $value ) ) {
			return [];
		}
		switch ( $field['return_format'] ) {
			case 'array':
				foreach ( $value as $image ) {
					$images[] = [
						'id' => $image['ID'],
					];
				}
				break;
			case 'id':
				foreach ( $value as $image ) {
					$images[] = [
						'id' => $image,
					];
				}
				break;
			case 'url':
				foreach ( $value as $image ) {
					$image    = attachment_url_to_postid( $image );
					$images[] = [
						'id' => $image,
					];
				}
				break;
		}

		return $images;
	}
}
