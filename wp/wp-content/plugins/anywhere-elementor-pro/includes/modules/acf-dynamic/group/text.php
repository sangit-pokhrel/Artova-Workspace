<?php
namespace Aepro\Modules\AcfDynamic\Group;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Text extends Tag {
	public function get_name() {
		return 'ae-acf-group-text';
	}

	public function get_title() {
		return __( '(AE) ACF Group Text', 'ae-pro' );
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
	protected function register_controls() {
		AcfGroupDynamicHelper::instance()->register_ae_dynamic_group_controls( $this, $this->get_supported_fields() );
		$this->add_control(
			'show_label',
			[
				'label'        => __( 'Show Label', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'separator',
			[
				'label'   => __( 'Separator', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ', ',
			]
		);
	}
	public function get_supported_fields() {
		return [
			'text',
			'url',
			'textarea',
			'number',
			'email',
			'password',
			'wysiwyg',
			'select',
			'checkbox',
			'radio',
			'true_false',

			// Pro
			'oembed',
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
		];
	}

	public function render() {
		$settings = $this->get_settings();
		if ( empty( $settings ) ) {
			return;
		}
		$value               = '';
		list($field, $value) = AcfGroupDynamicHelper::instance()->get_acf_field_value( $this );

		if ( $field && ! empty( $field['type'] ) ) {

			switch ( $field['type'] ) {
				case 'radio':
				case 'checkbox':
				case 'select':
					$selected_value = [];
					if ( $settings['show_label'] === 'yes' ) {
						foreach ( $value as $item ) {
							$selected_value[] = $item;
						}
					} else {
						foreach ( $value as $key => $item ) {
							$selected_value[] = $key;
						}
					}
					if ( is_array( $selected_value ) ) {
						$value = implode( $settings['separator'], $selected_value );
					} else {
						$value = $selected_value;
					}
					break;
				case 'oembed':
					$value = $value;
					// Get from db without formatting.
					break;
				case 'google_map':
					$value = $value['address'];
			}
		} else {
			// Field settings has been deleted or not available.
			$value = $value;
		}
		echo wp_kses_post( $value );
	}
}
