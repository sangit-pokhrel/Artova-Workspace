<?php

namespace Aepro\Modules\AcfDynamic\Repeater;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Plugin;
use Aepro\Classes\AcfMaster;

class Boolean extends Tag {

	public function get_name() {
		return 'ae-boolean';
	}

	public function get_title() {
		return __( '(AE) ACF Repeater Boolean', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-dynamic';
	}

	public function get_categories() {

		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
		];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => __( 'Repeater Boolean Support True/False field of ACF.', 'ae-pro' ),
			]
		);
		DynamicHelper::instance()->ae_get_group_fields( $this, $this->get_supported_fields() );

		$this->add_control(
			'true_message',
			[
				'label'       => __( 'True Message', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'render_type' => 'template',
			]
		);
		$this->add_control(
			'false_message',
			[
				'label'       => __( 'False Message', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'render_type' => 'template',
			]
		);
	}

	public function get_supported_fields() {
		return [
			'true_false',
			// Pro
		];
	}

	public function render() {
		$settings = $this->get_settings();
		$value    = DynamicHelper::instance()->get_repeater_data( $settings );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $value == 1 ) {
			$value = $settings['true_message'];
		} else {
			$value = $settings['false_message'];
		}

		echo $value;
	}
}
