<?php

namespace Aepro\Modules\AcfDynamic\Repeater;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Plugin;

class Text extends Tag {

	public function get_name() {
		return 'ae-text';
	}

	public function get_title() {
		return __( '(AE) ACF Repeater Text', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_categories() {

		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		DynamicHelper::instance()->ae_get_group_fields( $this, $this->get_supported_fields() );
	}

	public function get_supported_fields() {
		return [
			'text',
			'textarea',
			'number',
			'email',
			'password',
			'wysiwyg',
			'url',

			// Pro
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
		];
	}

	public function render() {
		$settings = $this->get_settings();
		$value    = DynamicHelper::instance()->get_repeater_data( $settings );
		echo $value;
	}
}
