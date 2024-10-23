<?php

namespace Aepro\Modules\AcfDynamic\Flexible;

use Aepro\Aepro;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Number extends Tag {

	public function get_name() {
		return 'ae-acf-flexible-number';
	}

	public function get_title() {
		return __( '(AE) ACF Flexible Number', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function get_categories() {

		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}
// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label'   => __( 'Select Layout', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'groups'  => Aepro::$_helper->ae_get_flexible_content_fields(),
				'default' => '',
			]
		);

		$this->add_control(
			'flex_sub_field',
			[
				'label'            => __( 'Sub Field', 'ae-pro' ),
				'type'             => 'aep-query',
				'parent_field'     => 'key',
				'supported_fields' => implode( ' ', $this->get_supported_fields() ),
				'query_type'       => 'flex-sub-fields',
				'placeholder'      => 'Select',
				'condition'        => [
					'key!' => '',
				],
				'render_type'      => 'template',
			]
		);
	}

	public function get_supported_fields() {
		return [
			'text',
			'number',
		];
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return;
		}
		list($field, $value) = AcfFlexibleDynamicHelper::instance()->get_acf_field_value( $this );
		if ( empty( $value ) ) {
			$value = ! empty( $settings['fallback'] ) ? $settings['fallback'] : 0;
		}
		echo wp_kses_post( $value );
	}
}
