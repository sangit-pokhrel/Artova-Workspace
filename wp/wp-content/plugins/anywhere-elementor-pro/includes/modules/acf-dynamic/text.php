<?php
namespace Aepro\Modules\AcfDynamic;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Text extends Tag {
	public function get_name() {
		return 'ae-acf-text';
	}

	public function get_title() {
		return __( '(AE) ACF Text', 'ae-pro' );
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
		//
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
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return;
		}
		list($field, $meta_key, $value) = AcfDynamicHelper::instance()->get_acf_field_value( $this );
		if ( $field && ! empty( $field['type'] ) ) {

			switch ( $field['type'] ) {

				case 'radio':
				case 'checkbox':
				case 'select':
					$selected_value = [];
					if ( $settings['show_label'] === 'yes' ) {
						if ( is_array( $value ) ) {
							foreach ( $value as $item ) {
								$selected_value[] = $item;
							}
						} else {
							$selected_value = $value;
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
			$value = get_field( $meta_key );
		}

		echo wp_kses_post( $value );
	}
}
