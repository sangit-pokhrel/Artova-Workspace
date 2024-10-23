<?php

namespace Aepro\Modules\AcfFields\Skins;

use Elementor\Controls_Manager;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Base\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Plugin as EPlugin;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Classes\CacheManager;
use Elementor\Core\Files\CSS\Global_CSS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		add_action( 'elementor/element/ae-acf/general/before_section_end', [ $this, 'register_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;
	}

	public function register_text_controls() {

		

		$this->add_control(
			'prefix',
			[
				'label'     => __( 'Before Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'suffix',
			[
				'label' => __( 'After Text', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label'       => __( 'Placeholder Text', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To be used as default text when there is no data in ACF Field', 'ae-pro' ),
			]
		);

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
			]
		);

		$this->add_control(
			'links_to',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Link to', 'ae-pro' ),
				'options' => [
					''             => __( 'None', 'ae-pro' ),
					'post'         => __( 'Post', 'ae-pro' ),
					'static'       => __( 'Static URL', 'ae-pro' ),
					'custom_field' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => '',
			]
		);

		$this->add_control(
			'link_url',
			[
				'label'       => __( 'Static URL', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter URL', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'links_to' ) => 'static',
				],
			]
		);
		$this->add_control(
			'link_cf',
			[
				'label'       => __( 'Enter Field Key', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Field Key', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'links_to' ) => 'custom_field',
				],
				'description' => __( 'Mention ACF field that contains an url', 'ae-pro' ),
			]
		);

		$this->add_control(
			'link_new_tab',
			[
				'label'     => __( 'Open in new tab', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_off' => __( 'No', 'ae-pro' ),
				'label_on'  => __( 'Yes', 'ae-pro' ),
				'default'   => __( 'label_off', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'links_to!' ) => '',
				],
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
					'{{WRAPPER}} .ae-acf-wrapper'         => 'text-align: {{VALUE}}',
					'{{WRAPPER}} .ae-acf-content-wrapper' => 'display:inline-block;',
					'{{WRAPPER}}.ae-align-justify .ae-acf-content-wrapper' => 'width:100%; text-align:center;',
				],
			]
		);

		$this->add_control(
			'strip_text',
			[
				'label'        => __( 'Strip Text', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'strip_yes'    => __( 'Yes', 'ae-pro' ),
				'strip_no'     => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'strip_mode',
			[
				'label'     => __( 'Strip Mode', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'word'   => __( 'Word', 'ae-pro' ),
					'letter' => __( 'Letter', 'ae-pro' ),
				],
				'default'   => 'word',
				'condition' => [
					$this->get_control_id( 'strip_text' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'strip_size',
			[
				'label'       => __( 'Strip Size', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Strip Size', 'ae-pro' ),
				'default'     => __( '5', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'strip_text' ) => 'yes',
				],
				'description' => __( 'Number of words to show.', 'ae-pro' ),
			]
		);

		$this->add_control(
			'strip_append',
			[
				'label'       => __( 'Append Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Append Text', 'ae-pro' ),
				'default'     => __( '...', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'strip_text' ) => 'yes',
				],
				'description' => __( 'What to append if Title needs to be trimmed.', 'ae-pro' ),
			]
		);
	}

	public function add_unfold_section() {

		

		$this->start_controls_section(
			'section_unfold_layout',
			[
				'label' => __( 'Unfold', 'ae-pro' ),
			]
		);

		$this->add_control(
			'enable_unfold',
			[
				'label'        => __( 'Enable Unfold', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_responsive_control(
			'unfold_animation_speed',
			[
				'label'     => __( 'Animation Speed', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 500,
						'max'  => 5000,
						'step' => 100,
					],
				],
				'default'   => [
					'size' => 500,
				],
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'auto_hide_unfold_button',
			[
				'label'        => __( 'Auto Hide Unfold Button', 'ae-pro' ),
				'description'  => __( 'When Content is less than Unfold height', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'button_controls_heading',
			[
				'label'     => __( 'Button', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_controls' );

		$this->start_controls_tab(
			'tab_button_unfold',
			[
				'label'     => __( 'Unfold', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);
		$this->add_control(
			'unfold_text',
			[
				'label'     => __( 'Show More Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Show More',
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'unfold_icon',
			[
				'label'       => __( 'Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => '',
				'condition'   => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_fold',
			[
				'label'     => __( 'Fold', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'fold_text',
			[
				'label'     => __( 'Show Less Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Show Less',
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'fold_icon',
			[
				'label'       => __( 'Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => '',
				'condition'   => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'button_icon_position',
			[
				'label'     => __( 'Icon Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'  => __( 'Before', 'ae-pro' ),
					'right' => __( 'After', 'ae-pro' ),
				],
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
					$this->get_control_id( 'unfold_icon!' )  => '',
				],
			]
		);

		$this->add_control(
			'button_icon_indent',
			[
				'label'     => __( 'Icon Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'default'   => [
					'size' => 10,
				],
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
					$this->get_control_id( 'unfold_icon!' )  => '',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-unfold-button-icon.elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-acf-unfold-button-icon.elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'unfold_button_align',
			[
				'label'     => __( 'Button Align', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
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
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-acf-unfold' => 'text-align: {{VALUE}}',
				],
				'condition' => [
					$this->get_control_id( 'enable_unfold' ) => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_select_controls() {

		if(!$this->load_skin_controls(['select', 'button_group', 'checkbox', 'radio' ])){
			return;
		}
		$this->add_control(
			'data_type',
			[
				'label'     => __( 'Display Data', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'label' => __( 'Label', 'ae-pro' ),
					'key'   => __( 'Key', 'ae-pro' ),
				],
				'separator' => 'before',
				'default'   => 'label',
			]
		);

		$this->add_control(
			'show_all_choices',
			[
				'label'       => __( 'Show All Options/Choices', 'ae-pro' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_off'   => __( 'No', 'ae-pro' ),
				'label_on'    => __( 'Yes', 'ae-pro' ),
				'default'     => __( 'label_off', 'ae-pro' ),
				'description' => __( 'This will even display choices that were not selected. You can style them separately.', 'ae-pro' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'       => __( 'Layout', 'ae-pro' ),
				'label_block' => false,
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'vertical' => [
						'title' => __( 'Vertical', 'ae-pro' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'horizontal' => [
						'title' => __( 'Horizontal', 'ae-pro' ),
						'icon'  => 'eicon-ellipsis-h',
					],
				],
				'default'     => 'horizontal',
			]
		);

		$this->add_responsive_control(
			'horizontal_align',
			[
				'label'        => __( 'Align', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => false,
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
				],
				'prefix_class' => 'ae-icl-align-',
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'ae-pro' ),
				'type'  => Controls_Manager::ICON,
			]
		);

		$this->add_control(
			'icon_unchecked',
			[
				'label' => __( 'Icon (Unchecked)', 'ae-pro' ),
				'type'  => Controls_Manager::ICON,
			]
		);

		$this->add_control(
			'divider',
			[
				'label'        => __( 'Enable Divider', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'Off', 'ae-pro' ),
				'label_on'     => __( 'On', 'ae-pro' ),
				'return_value' => 'yes',
				'render_type'  => 'template',
				'prefix_class' => 'ae-sep-divider-',
				'selectors'    => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'content: ""',
				],
			]
		);

		$this->add_control(
			'separator',
			[
				'label'       => __( 'Separator', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'render_type' => 'template',
				'condition'   => [
					$this->get_control_id( 'layout' )   => 'horizontal',
					$this->get_control_id( 'divider!' ) => 'yes',
				],
				'selectors'   => [
					'{{WRAPPER}} .ae-custom-sep .ae-icon-list-item:not(:last-child):after' => 'content:"{{VALUE}}"; white-space:pre;',
				],
			]
		);
	}

	public function register_fallback_controls( $add_img_controls = true ) {

		$fallback_type = [
			''         => __( 'Select', 'ae-pro' ),
			'text'     => __( 'Text', 'ae-pro' ),
			'template' => __( 'Template', 'ae-pro' ),
		];
		if ( $add_img_controls ) {
			$fallback_type['image'] = __( 'Image', 'ae-pro' );
		}

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
					$this->get_control_id( 'enable_fallback' ) => 'yes',
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
					$this->get_control_id( 'enable_fallback' ) => 'yes',
				],
			]
		);
		$this->add_control(
			'fallback_text',
			[
				'label'     => __( 'Fallback Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'text',
				],
			]
		);

		if ( $add_img_controls ) {
			$this->add_control(
				'fallback_image',
				[
					'label'     => __( 'Fallback Image', 'ae-pro' ),
					'type'      => Controls_Manager::MEDIA,
					'condition' => [
						$this->get_control_id( 'enable_fallback' ) => 'yes',
						$this->get_control_id( 'fallback_type' ) => 'image',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Image_Size::get_type(),
				[
					'name'      => 'fallback_image_size', // Actually its `image_size`
					'default'   => 'medium_large',
					'condition' => [
						$this->get_control_id( 'enable_fallback' ) => 'yes',
						$this->get_control_id( 'fallback_type' ) => 'image',
					],
				]
			);
		}

		$this->add_control(
			'content_type',
			[
				'label'     => __( 'Type', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''              => __( 'Select', 'ae-pro' ),
					'saved_section' => __( 'Saved Section', 'ae-pro' ),
					'saved_pages'   => __( 'Saved Page', 'ae-pro' ),
					'ae_templates'  => __( 'AE-Template', 'ae-pro' ),
				],
				'default'   => '',
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'template',
				],
			]
		);
		$saved_sections[''] = __( 'Select Section', 'ae-pro' );
		$saved_sections     = $saved_sections + $this->select_elementor_page( 'section' );
		$this->add_control(
			'saved_section',
			[
				'label'     => __( 'Sections', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $saved_sections,
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'template',
					$this->get_control_id( 'content_type' )  => 'saved_section',
				],
			]
		);
		$saved_page[''] = __( 'Select Pages', 'ae-pro' );
		$saved_page     = $saved_page + $this->select_elementor_page( 'page' );
		$this->add_control(
			'saved_pages',
			[
				'label'     => __( 'Pages', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $saved_page,
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'template',
					$this->get_control_id( 'content_type' )  => 'saved_pages',
				],
			]
		);

		$saved_ae_template[''] = __( 'Select AE Template', 'ae-pro' );
		$saved_ae_template     = $saved_ae_template + $this->select_ae_templates();
		$this->add_control(
			'ae_templates',
			[
				'label'     => __( 'AE-Templates', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $saved_ae_template,
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'template',
					$this->get_control_id( 'content_type' )  => 'ae_templates',
				],
			]
		);
		$this->end_controls_section();
	}

	/* Need to remove this function after we migrate fallback image control in skin image */
	public function fallback_style_controls( $add_img_controls = true ) {
		
		$this->start_controls_section(
			'fallback_style',
			[
				'label'     => __( 'Fallback', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type!' ) => 'template',
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
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'text',
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
					$this->get_control_id( 'enable_fallback' ) => 'yes',
					$this->get_control_id( 'fallback_type' ) => 'image',
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
					$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
							$this->get_control_id( 'fallback_type' ) => 'text',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
							$this->get_control_id( 'fallback_type' ) => 'text',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
							$this->get_control_id( 'fallback_type' ) => 'text',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
							$this->get_control_id( 'fallback_type' ) => 'text',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
							$this->get_control_id( 'enable_fallback' ) => 'yes',
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
						$this->get_control_id( 'enable_fallback' ) => 'yes',
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
						$this->get_control_id( 'enable_fallback' ) => 'yes',
					],
				]
			);

		$this->end_controls_section();
	}

	public static function select_elementor_page( $type ) {
		$args  = [
			'tax_query'      => [
				[
					'taxonomy' => 'elementor_library_type',
					'field'    => 'slug',
					'terms'    => $type,
				],
			],
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
		];
		$query = new \WP_Query( $args );

		$posts = $query->posts;
		foreach ( $posts as $post ) {
			$items[ $post->ID ] = $post->post_title;
		}

		if ( empty( $items ) ) {
			$items = [];
		}

		return $items;
	}

	public static function select_ae_templates() {
		$block_layouts = [];
		$block_layouts = CacheManager::instance()->get_block_layouts();
		return $block_layouts;
	}

	public function render_fallback_content( $settings, $image_size_field = '' ) {
		$type = $this->get_instance_value( 'fallback_type' );
		switch ( $type ) {
			case 'text':
						$text = $this->get_instance_value( 'fallback_text' )
				?>
									<div class="ae-fallback-wrapper ae-fallback-text">
										<div class="ae-fallback">
									<?php echo $this->process_content( $text ); ?>
										</div>
									</div>
									<?php
				break;

			case 'image':
				if ( empty( $image_size_field ) ) {
						$size_field = str_replace( '-', '_', $settings['_skin'] . '_fallback_image_size' );
				} else {
					$size_field = $settings['_skin'] . '_image_size';
				}
							$img_field = str_replace( '-', '_', $settings['_skin'] . '_fallback_image' );
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
						$template_type       = $this->get_instance_value( 'content_type' );
								$template_id = $this->get_instance_value( $template_type );
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

	public function process_content( $content ) {
		/** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
		$content = apply_filters( 'widget_text', $content, $this->parent->get_settings() );

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}

	public function load_skin_controls($allowed_skins = []){
		
		// error_log('Edit Mode - '. (\Elementor\Plugin::instance()->editor->is_edit_mode()?'Yes':'No') );
		// error_log('Is Type Instance - '.($this->parent->is_type_instance()?'Yes':'No'));

		// if(\Elementor\Plugin::instance()->editor->is_edit_mode()){
		// 	return true;
		// }


		// if( $this->parent->is_type_instance()){
		// 	return true;
		// }

		// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 	return true;
		// }

		$load_skin_controls = false;
		$load_skin_controls = apply_filters('ae/skin_control_loading', $load_skin_controls);
		
		if($load_skin_controls){
			
			return true;
		}

		if(!is_admin() && !$this->parent->is_type_instance()){

			$skin = $this->parent->get_current_skin_id();
			

			if(!in_array($skin, $allowed_skins)){
				
				return false;
			}
		}

		
		return true;
	}



}
