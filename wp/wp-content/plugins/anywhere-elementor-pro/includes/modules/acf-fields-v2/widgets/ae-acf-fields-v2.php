<?php
namespace Aepro\Modules\AcfFieldsV2\Widgets;

use Elementor\Plugin as EPlugin;
use Elementor\Controls_Manager;
use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Aepro\Classes\AcfMaster;
use Aepro\Classes\CacheManager;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Choice;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Date;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Text;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Number;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_URL;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Boolean;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Image;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Post;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Taxonomy;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;

class AeAcfFieldsV2 extends Widget_Base {

	protected $_has_template_content = false;

	protected $_access_level = 2;

	public static  $_acf_text = null;

	public function get_name() {
		return 'ae-acf-fields-v2';
	}

	public function is_enabled() {

		if ( AE_ACF ) {
			return true;
		}

		return false;
	}


	public function get_title() {
		return __( 'AE - ACF Fields V2', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'acf',
			'fields',
			'custom fields',
			'meta',
			'group',
			'repeater',
			'flexible content',
		];
	}

	public function render(){

		$settings = $this->get_settings_for_display();

		$field_type = $settings['field_type'];

		switch($field_type){

			case 'text': ACF_Text::render($this, $settings);
						 break;
			case 'textarea': ACF_Text::render($this, $settings);
						 break;
			case 'wysiwyg': ACF_Text::render($this, $settings);
						 break;
			case 'number': ACF_Number::render($this, $settings);
						 break;
			case 'date': ACF_Date::render($this, $settings);
						 break;
			case 'select': ACF_Choice::render($this, $settings);
						 break;
			case 'checkbox': ACF_Choice::render($this, $settings);
						 break;
			case 'radio': ACF_Choice::render($this, $settings);
						 break;
			case 'button_group': ACF_Choice::render($this, $settings);
						 break;
			case 'true_false': ACF_Boolean::render($this, $settings);
						 break;
			case 'url': ACF_URL::render($this, $settings);
						 break;
			case 'file': ACF_URL::render($this, $settings);
						 break;
			case 'email': ACF_URL::render($this, $settings);
						 break;
			case 'image': ACF_Image::render($this, $settings);
						 break;
			case 'taxonomy': ACF_Taxonomy::render($this, $settings);
						 break;
			case 'post_object':  ACF_Post::render($this, $settings);
						 break;
			case 'relationship': ACF_Post::render($this, $settings);
						 break;
		}
		
	}

	public function register_controls() {

		$post      = get_post();
		$post_type = get_post_type();

		if ( ! empty( $post ) ) {
			$post_meta   = get_post_meta( $post->ID );
			$render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
			$field_type  = get_post_meta( $post->ID, 'ae_acf_field_type', true );

		}

		$this->start_controls_section(
			'general',
			[
				'label' => __( 'General', 'ae-pro' ),
			]
		);

		$this->add_control(
			'field_type',
			[
				'label'   => __( 'Field Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'text'   => __( 'Text', 'ae-pro' ),
					'textarea'   => __( 'Text Area', 'ae-pro' ),
					'wysiwyg'   => __( 'WSIWYG', 'ae-pro' ),
					'number'   => __( 'Number', 'ae-pro' ),
					'date'   => __( 'Date', 'ae-pro' ),
					'select'   => __( 'Select', 'ae-pro' ),
					'checkbox'   => __( 'Checkbox', 'ae-pro' ),
					'radio'   => __( 'Radio', 'ae-pro' ),
					'button_group'   => __( 'Button Group', 'ae-pro' ),
					'true_false'   => __( 'True False', 'ae-pro' ),
					'url'   => __( 'URL', 'ae-pro' ),
					'file'   => __( 'File', 'ae-pro' ),
					'email'   => __( 'Email', 'ae-pro' ),
					'image'   => __( 'Image', 'ae-pro' ),
					'taxonomy'   => __( 'Taxonomy', 'ae-pro' ),
					'post_object'   => __( 'Post Object', 'ae-pro' ),
					'relationship'   => __( 'Relationship', 'ae-pro' ),
				],
				'default' => 'text',
				'prefix_class' => 'ae-acf-field-type-',
				'render_type' => 'template'
			]
		);

		$this->add_control(
			'source',
			[
				'label'   => __( 'Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'post'   => __( 'Post Field', 'ae-pro' ),
					'term'   => __( 'Term Field', 'ae-pro' ),
					'user'   => __( 'User', 'ae-pro' ),
					'option' => __( 'Option', 'ae-pro' ),
				],
				'default' => 'post',
			]
		);

		$parent_field_type = [
			''         => __( 'None', 'ae-pro' ),
			'repeater' => __( 'Repeater Field', 'ae-pro' ),
			'group'    => __( 'Group Field', 'ae-pro' ),
		];
		if ( AE_ACF_PRO ) {
			if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
				$parent_field_type['flexible'] = __( 'Flexible Field', 'ae-pro' );
			}
		}
		$this->add_control(
			'is_sub_field',
			[
				'label'       => __( 'Parent Field Type', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $parent_field_type,
				'description' => __( 'Choose if you want to fetch data from a sub field', 'ae-pro' ),
				'condition'   => [
					'source' => [ 'post', 'option' ],
				],
			]
		);
		if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
			$this->add_control(
				'option_alert',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'ae-editor-note',
					'raw'             => __( '<span style="color:red; font-weight:bold;">Note: </span> Only Flexible Field is supported for Option’s field.', 'ae-pro' ),
					'separator'       => 'none',
					'condition'       => [
						'source' => 'option',
					],
				]
			);
		}

		$this->add_control(
			'parent_field',
			[
				'label'     => __( 'Parent Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'is_sub_field' => [ 'group' ],
					'source'   => [ 'post', 'option' ],
				],
			]
		);

		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$this->add_control(
			'repeater_field',
			[
				'label'     => __( 'Repeater Field', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'groups'    => $repeater_fields,
				'condition' => [
					'is_sub_field' => [ 'repeater' ],
					'source'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'repeater_sub_field',
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'repeater_field',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'is_sub_field' => [ 'repeater' ],
					'source'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'flexible_field',
			[
				'label'       => __( 'Parent Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => Aepro::$_helper->ae_get_flexible_content_fields(),
				'description' => __( 'Choose parent flexible field', 'ae-pro' ),
				'condition'   => [
					'is_sub_field' => [ 'flexible' ],
					'source'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'field_name',
			[
				'label'       => __( 'Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'Enter your acf field name',
				'condition'   => [
					'is_sub_field!' => [ 'flexible', 'repeater' ],
				],
			]
		);

		$this->add_control(
			'flex_sub_field',
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'flexible_field',
				'query_type'   => 'flex-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'is_sub_field' => [ 'flexible' ],
					'source'   => [ 'post', 'option' ],
				],
			]
		);

		ACF_Text::register_controls($this);

		ACF_Number::register_controls($this);
		
		ACF_URL::register_controls($this);

		ACF_Date::register_controls($this);

		ACF_Choice::register_controls($this);

		ACF_Boolean::register_controls($this);

		ACF_Image::register_controls($this);

		//ACF_User::register_controls($this);

		$this->add_control(
			'html_tag',
			[
				'label'   => __( 'Html Tag', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'      => 'H1',
					'h2'      => 'H2',
					'h3'      => 'H3',
					'h4'      => 'H4',
					'h5'      => 'H5',
					'h6'      => 'H6',
					'div'     => 'div',
					'span'    => 'span',
					'p'       => 'p',
					'article' => 'article',
				],
				'default' => 'h2',
				'condition' => [
					'field_type' => ['text', 'textarea', 'wysiwyg', 'number', 'date']
				]
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label'        => __( 'Align', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'fa fa-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'ae-pro' ),
						'icon'  => 'fa fa-align-justify',
					],
				],
				'prefix_class' => 'ae-align-',
				'default'      => '',
				'selectors'    => [
					'{{WRAPPER}}.ae-acf-field-type-image' => 'text-align: {{VALUE}}', 
					'{{WRAPPER}} .ae-acf-wrapper'         => 'text-align: {{VALUE}}',
					'{{WRAPPER}} .ae-acf-content-wrapper' => 'display:inline-block;',
					'{{WRAPPER}}.ae-align-justify .ae-acf-content-wrapper' => 'width:100%; text-align:center;',
				],
				'condition' => [
					'field_type' => ['text', 'textarea', 'url', 'number', 'date', 'file', 'email', 'image']
				]
			]
		);

		$this->end_controls_section();

		ACF_TEXT::register_unfold_controls($this);

		$this->register_style_controls();

		ACF_Choice::register_style_controls($this);

		$this->register_fallback_controls();

		$this->register_fallback_style_controls();
	}

	public function register_style_controls() {

		$this->start_controls_section(
			'general_style',
			[
				'label' => __( 'General', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'field_type!' => 'image'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .ae-acf-content-wrapper, {{WRAPPER}} .ae-acf-content-wrapper a, {{WRAPPER}} span',
			]
		);

			$this->start_controls_tabs( 'style' );

				$this->start_controls_tab(
					'normal_style',
					[
						'label' => __( 'Normal', 'ae-pro' ),
					]
				);

				$this->add_control(
					'color',
					[
						'label'     => __( 'Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper, {{WRAPPER}} .ae-acf-content-wrapper a' => 'color:{{VALUE}}',
							'{{WRAPPER}} span' => 'color:{{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color',
					[
						'label'     => __( 'Background Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper' => 'background:{{VALUE}}',
							'{{WRAPPER}} span' => 'background:{{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					[
						'name'     => 'border',
						'label'    => __( 'Border', 'ae-pro' ),
						'selector' => '{{WRAPPER}} .ae-acf-content-wrapper',
						'selector' => '{{WRAPPER}} span',
					]
				);

				$this->add_control(
					'border_radius',
					[
						'label'      => __( 'Border Radius', 'ae-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%' ],
						'selectors'  => [
							'{{WRAPPER}} .ae-acf-content-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} span   ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					[
						'name'     => 'box_shadow',
						'label'    => __( 'Shadow', 'ae-pro' ),
						'selector' => '{{WRAPPER}} .ae-acf-content-wrapper',
						'selector' => '{{WRAPPER}} span',
					]
				);

				$this->end_controls_tab();  // Normal Tab End

				$this->start_controls_tab(
					'hover_style',
					[
						'label' => __( 'Hover', 'ae-pro' ),
					]
				);

				$this->add_control(
					'color_hover',
					[
						'label'     => __( 'Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper:hover, {{WRAPPER}} .ae-acf-content-wrapper:hover a' => 'color:{{VALUE}}',
							'{{WRAPPER}} span:hover' => 'color:{{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color_hover',
					[
						'label'     => __( 'Background Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper:hover' => 'background:{{VALUE}}',
							'{{WRAPPER}} span:hover' => 'background:{{VALUE}}',
						],
					]
				);

				$this->add_control(
					'border_color_hover',
					[
						'label'     => __( 'Border Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper:hover' => 'border-color:{{VALUE}}',
							'{{WRAPPER}} span:hover' => 'border-color:{{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'border_radius_hover',
					[
						'label'     => __( 'Border Radius', 'ae-pro' ),
						'type'      => Controls_Manager::DIMENSIONS,
						'selectors' => [
							'{{WRAPPER}} .ae-acf-content-wrapper:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
						],

					]
				);

				$this->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					[
						'name'     => 'hover_box_shadow',
						'label'    => __( 'Shadow', 'ae-pro' ),
						'selector' => '{{WRAPPER}} .ae-acf-content-wrapper:hover, {{WRAPPER}} span:hover',
					]
				);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_responsive_control(
				'padding',
				[
					'label'     => __( 'Padding', 'ae-pro' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'separator' => 'before',
					'selectors' => [
						'{{WRAPPER}} .ae-acf-content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],

				]
			);

			$this->add_responsive_control(
				'margin',
				[
					'label'     => __( 'Margin', 'ae-pro' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => [
						'{{WRAPPER}} .ae-acf-content-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],

				]
			);

		$this->end_controls_section();

		ACF_Image::register_style_controls($this);
		
		ACF_Image::register_style_icon_controls($this);

		//ACF_Image::register_style_overlay_controls($this);
	}

	public function register_fallback_controls() {
		
		$fallback_type = [
			''         => __( 'Select', 'ae-pro' ),
			'text'     => __( 'Text', 'ae-pro' ),
			'template' => __( 'Template', 'ae-pro' ),
			'image'    => __( 'Image', 'ae-pro' ),
		];


		$this->start_controls_section(
			'fallback_content',
			[
				'label' => __( 'Fallback Content', 'ae-pro' ),
			]
		);
		$this->add_control(
			'enable_fallback',
			[
				'label'        => __( 'Enable Fallback', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label__off'   => __( 'No', 'ae-pro' ),
				'label__on'    => __( 'Yes', 'ae-pro' ),
				'default'      => 'no',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'preview_fallback',
			[
				'label'        => __( 'Preview Fallback', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label__off'   => __( 'No', 'ae-pro' ),
				'label__on'    => __( 'Yes', 'ae-pro' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'condition'    => [
					'enable_fallback' => 'yes',
				],
			]
		);

		$this->add_control(
			'fallback_type',
			[
				'label'     => __( 'Fallback Type', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $fallback_type,
				'default'   => '',
				'condition' => [
					'enable_fallback' => 'yes',
				],
			]
		);
		$this->add_control(
			'fallback_text',
			[
				'label'     => __( 'Fallback Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'text',
				],
			]
		);

		$this->add_control(
			'fallback_image',
			[
				'label'     => __( 'Fallback Image', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'image',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'fallback_image_size', // Actually its `image_size`
				'default'   => 'medium_large',
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'image',
				],
			]
		);

		$this->add_control(
			'content_type',
			[
				'label'     => __( 'Type', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''              => __( 'Select', 'ae-pro' ),
					'saved_section' => __( 'Saved Section', 'ae-pro' ),
					'ae_templates'  => __( 'AE-Template', 'ae-pro' ),
				],
				'default'   => '',
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'template',
				],
			]
		);
		$saved_sections[''] = __( 'Select Section', 'ae-pro' );
		$saved_sections     = $saved_sections + Aepro::$_helper->select_elementor_page( 'section' );
		$this->add_control(
			'saved_section',
			[
				'label'     => __( 'Sections', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $saved_sections,
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'template',
					'content_type'  => 'saved_section',
				],
			]
		);

		$saved_ae_template[''] = __( 'Select AE Template', 'ae-pro' );
		$saved_ae_template     = $saved_ae_template + CacheManager::instance()->get_block_layouts();
		$this->add_control(
			'ae_templates',
			[
				'label'     => __( 'AE-Templates', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $saved_ae_template,
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'template',
					'content_type'  => 'ae_templates',
				],
			]
		);
		$this->end_controls_section();
	}

	public function register_fallback_style_controls( $add_img_controls = true ) {
		$this->start_controls_section(
			'fallback_style',
			[
				'label'     => __( 'Fallback', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type!' => 'template',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'fallback_typography',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector'  => '{{WRAPPER}} .ae-fallback-wrapper .ae-fallback',
				'condition' => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'text',
				],
			]
		);

		$this->add_responsive_control(
			'fallback_img_width',
			[
				'label'          => __( 'Width', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae-fallback-wrapper .ae-fallback img' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'      => [
					'enable_fallback' => 'yes',
					'fallback_type' => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'fallback_align',
			[
				'label'     => esc_html__( 'Alignment', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => esc_html__( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-fallback-wrapper .ae-fallback' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'enable_fallback' => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'fb_style' );

				$this->start_controls_tab(
					'fallback_normal_style',
					[
						'label' => __( 'Normal', 'ae-pro' ),
					]
				);

				$this->add_control(
					'fb_text_color',
					[
						'label'     => __( 'Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper .ae-fallback' => 'color:{{VALUE}}',
						],
						'condition' => [
							'enable_fallback' => 'yes',
							'fallback_type' => 'text',
						],
					]
				);

				$this->add_control(
					'fb_bg_color',
					[
						'label'     => __( 'Background Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper' => 'background-color:{{VALUE}}',
						],
						'condition' => [
							'enable_fallback' => 'yes',
							'fallback_type' => 'text',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					[
						'name'      => 'fb_border',
						'label'     => __( 'Border', 'ae-pro' ),
						'selector'  => '{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-text, {{WRAPPER}} .ae-fallback-wrapper.ae-fallback-image img',
						'condition' => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->add_responsive_control(
					'fb_border_radius',
					[
						'label'      => __( 'Border Radius', 'ae-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%' ],
						'selectors'  => [
							'{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
						'condition'  => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					[
						'name'      => 'fb_box_shadow',
						'label'     => __( 'Shadow', 'ae-pro' ),
						'selector'  => '{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-text , {{WRAPPER}} .ae-fallback-wrapper.ae-fallback-image img',
						'condition' => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->end_controls_tab();  // Normal Tab End

				$this->start_controls_tab(
					'fallback_hover_style',
					[
						'label' => __( 'Hover', 'ae-pro' ),
					]
				);

				$this->add_control(
					'fb_text_color_hover',
					[
						'label'     => __( 'Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper:hover .ae-fallback' => 'color:{{VALUE}}',
						],
						'condition' => [
							'enable_fallback' => 'yes',
							'fallback_type' => 'text',
						],
					]
				);

				$this->add_control(
					'fb_bg_color_hover',
					[
						'label'     => __( 'Background Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper:hover' => 'background:{{VALUE}}',
						],
						'condition' => [
							'enable_fallback' => 'yes',
							'fallback_type' => 'text',
						],
					]
				);

				$this->add_control(
					'fb_border_color_hover',
					[
						'label'     => __( 'Border Color', 'ae-pro' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper:hover' => 'border-color:{{VALUE}}',
						],
						'condition' => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->add_responsive_control(
					'fb_border_radius_hover',
					[
						'label'     => __( 'Border Radius', 'ae-pro' ),
						'type'      => Controls_Manager::DIMENSIONS,
						'selectors' => [
							'{{WRAPPER}} .ae-fallback-wrapper:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
						'condition' => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					[
						'name'      => 'fb_hover_box_shadow',
						'label'     => __( 'Shadow', 'ae-pro' ),
						'selector'  => '{{WRAPPER}} .ae-fallback-wrapper:hover',
						'condition' => [
							'enable_fallback' => 'yes',
						],
					]
				);

				$this->end_controls_tab();

			$this->end_controls_tabs();
			$this->add_responsive_control(
				'fallback_padding',
				[
					'label'     => __( 'Padding', 'ae-pro' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'separator' => 'before',
					'selectors' => [
						'{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .ae-fallback-wrapper.ae-fallback-image img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'enable_fallback' => 'yes',
					],
				]
			);

			$this->add_responsive_control(
				'fallback_margin',
				[
					'label'     => __( 'Margin', 'ae-pro' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => [
						'{{WRAPPER}} .ae-fallback-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'enable_fallback' => 'yes',
					],
				]
			);

		$this->end_controls_section();
	}

	public function render_fallback_content( $settings, $image_size_field = '' ) {
		$type = $settings['fallback_type'];
		switch ( $type ) {
			case 'text':
						$text = $settings['fallback_text'];
				?>
									<div class="ae-fallback-wrapper ae-fallback-text">
										<div class="ae-fallback">
									<?php echo ACF_TEXT::process_content( $text, $settings ); ?>
										</div>
									</div>
									<?php
				break;

			case 'image':
				//print_pre($settings, true);

				$size_field = 'fallback_image_size';
				$img_field = 'fallback_image';
				$img_html  = Group_Control_Image_Size::get_attachment_image_html( $settings, $size_field, $img_field );
				?>
					<div class="ae-fallback-wrapper ae-fallback-image">
						<div class="ae-fallback">
					<?php echo $img_html; ?>
						</div>
					</div>
				<?php
				break;

			case 'template':
						$template_type       = $settings['content_type'];
								$template_id = $settings[$template_type];
				?>
											<div class="ae-fallback-wrapper ae-fallback-image">
												<div class="ae-fallback">
											<?php echo EPlugin::instance()->frontend->get_builder_content_for_display( $template_id, true ); ?>
												</div>
											</div>			
										<?php
				break;
		}
	}

	public function get_field_args($settings){
		$field_args = [
			'field_type'   => $settings['source'],
			'is_sub_field' => $settings['is_sub_field'],
		];

		$accepted_parent_fields = [ 'repeater', 'group', 'flexible' ];
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $settings['is_sub_field'], $accepted_parent_fields ) ) {
			switch ( $settings['is_sub_field'] ) {

				case 'flexible':
					$field_args['field_name']                     = $settings['flex_sub_field'];
									$field_args['flexible_field'] = $settings['flexible_field'];
					break;

				case 'repeater':
					$field_args['field_name']                   = $settings['repeater_sub_field'];
									$field_args['parent_field'] = $settings['repeater_field'];
					break;

				case 'group':
					$field_args['field_name']                   = $settings['field_name'];
									$field_args['parent_field'] = $settings['parent_field'];
					break;
			}
		} else {
			$field_args['field_name'] = $settings['field_name'];
		}
		return $field_args;
	}

	public function get_raw_acf_field_value($settings){
		$value = '';

		$field_args = $this->get_field_args($settings);

		$value = AcfMaster::instance()->get_field_value( $field_args );

		return $value;
	}
}