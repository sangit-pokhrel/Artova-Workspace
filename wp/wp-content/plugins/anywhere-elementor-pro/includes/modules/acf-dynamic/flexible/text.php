<?php
namespace Aepro\Modules\AcfDynamic\Flexible;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Aepro\Aepro;

class Text extends Tag {
	public function get_name() {
		return 'ae-acf-flexible-text';
	}

	public function get_title() {
		return __( '(AE) ACF Flexible Text', 'ae-pro' );
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
		$settings            = $this->get_settings_for_display();
		list($field, $value) = AcfFlexibleDynamicHelper::instance()->get_acf_field_value( $this );

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
