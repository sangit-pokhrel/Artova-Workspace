<?php

namespace Aepro\Modules\AcfDynamic;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;


class Color extends Data_Tag {

	public function get_name() {
		return 'ae-acf-color';
	}

	public function get_title() {
		return __( '(AE) ACF Color', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY,
		];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label'   => __( 'Select Field', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'groups'  => AcfDynamicHelper::instance()->ae_get_acf_group( $this->get_supported_fields() ),
				'default' => '',
			]
		);
		$this->add_control(
			'fallback',
			[
				'label' => __( 'Fallback', 'ae-pro' ),
				'type'  => Controls_Manager::COLOR,
			]
		);
	}

	protected function get_supported_fields() {
		return [
			'color_picker',
		];
	}

	protected function get_value( array $options = [] ) {
		// TODO: Implement get_value() method.
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return [];
		}
		list($field, $meta_key, $value) = AcfDynamicHelper::instance()->get_acf_field_value( $this );

		if ( empty( $value ) && $this->get_settings( 'fallback' ) ) {
			$value = $this->get_settings( 'fallback' );
		}

		return $value;
	}
}
