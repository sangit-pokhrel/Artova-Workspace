<?php

namespace Aepro\Modules\AcfDynamic\Group;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;


class Color extends Data_Tag {

	public function get_name() {
		return 'ae-acf-group-color';
	}

	public function get_title() {
		return __( '(AE) ACF Group Color', 'ae-pro' );
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
		AcfGroupDynamicHelper::instance()->register_ae_dynamic_group_controls( $this, $this->get_supported_fields() );

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
		list($field, $value) = AcfGroupDynamicHelper::instance()->get_acf_field_value( $this );

		if ( empty( $value ) && $this->get_settings( 'fallback' ) ) {
			$value = $this->get_settings( 'fallback' );
		}

		return $value;
	}
}
