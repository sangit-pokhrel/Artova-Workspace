<?php
namespace Aepro\Modules\AcfRepeater\Skins;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Aepro\Base\Widget_Base;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Skin_Accordion extends Skin_Base {


	public function get_id() {
		return 'accordion';
	}

	public function get_title() {
		return __( 'Accordion', 'ae-pro' );
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/ae-acf-repeater/repeater_section/after_section_end', [ $this, 'register_layout_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;
		parent::register_general_controls();
	}
	public function register_layout_controls( Widget_Base $widget ) {

		$this->parent = $widget;
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => __( 'Accordion', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'accordion_border',
				'label'          => __( 'Border', 'ae-pro' ),
				'selector'       => '{{WRAPPER}} .ae-accordion-item',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'    => 1,
							'right'  => 1,
							'bottom' => 1,
							'left'   => 1,
						],
					],
					'color'  => [
						'default' => '#D4D4D4',
					],
				],
			]
		);

		$this->add_control(
			'accordion_space',
			[
				'label'     => __( 'Space Between', 'ae-pro' ),
				'separator' => 'before',
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-item'       => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-accordion-tb-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-tab-content'          => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'accordion_transition_speed',
			[
				'label' => __( 'Transition Speed', 'ae-pro' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 300,
						'max'  => 1000,
						'step' => 100,
					],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_toggle_style_title',
			[
				'label' => __( 'Title', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .elementor-accordion .elementor-tab-title',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Title Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'color: {{VALUE}};',
				],
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);

		$this->add_control(
			'tab_active_color',
			[
				'label'     => __( 'Active Title Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title.ae-active' => 'color: {{VALUE}};',
				],
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
			]
		);

		$this->add_control(
			'title_background',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_background_active',
			[
				'label'     => __( 'Active Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title.ae-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'title_border',
				'label'          => __( 'Border', 'ae-pro' ),
				'selector'       => '{{WRAPPER}} .ae-tab-title',
				'seperator'      => 'after',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'    => 0,
							'right'  => 0,
							'bottom' => 1,
							'left'   => 0,
						],
					],
					'color'  => [
						'default' => '#D4D4D4',
					],
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_align',
			[
				'label'       => __( 'Alignment', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'left' => [
						'title' => __( 'Start', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'End', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => is_rtl() ? 'right' : 'left',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => [
					'{{WRAPPER}} .ae-tab-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_toggle_style_icon',
			[
				'label'     => __( 'Icon', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'       => __( 'Alignment', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'left' => [
						'title' => __( 'Start', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'End', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => is_rtl() ? 'right' : 'left',
				'toggle'      => false,
				'label_block' => false,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title .elementor-accordion-icon i:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title .elementor-accordion-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_active_color',
			[
				'label'     => __( 'Active Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title.ae-active .elementor-accordion-icon i:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-accordion .elementor-tab-title.ae-active .elementor-accordion-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_space',
			[
				'label'     => __( 'Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-accordion-icon.elementor-accordion-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-accordion .elementor-accordion-icon.elementor-accordion-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_content',
			[
				'label' => __( 'Content', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_background_color',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'content_border',
				'label'          => __( 'Border', 'ae-pro' ),
				'selector'       => '{{WRAPPER}} .ae-tab-content',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'    => 0,
							'right'  => 0,
							'bottom' => 0,
							'left'   => 0,
						],
					],
					'color'  => [
						'default' => '#D4D4D4',
					],
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .elementor-accordion .elementor-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_toggle_button',
			[
				'label'     => __( 'Toggle Button', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_toggle_button' => 'yes',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'button_typography',
				'label'          => __( 'Typography', 'ae-pro' ),
				'global'         => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector'       => '{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button',
				'fields_options' => [
					'font_family' => [
						'default' => 'Poppins',
					],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 18,
						],
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => 18,
						],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'toggle_button_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button',
			]
		);

		$this->add_control(
			'toggle_button_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->start_controls_tabs(
			'toggle_button_tabs'
		);
		$this->start_controls_tab(
			'toggle_button_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_background',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'toggle_button_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_background_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'toggle_button_active',
			[
				'label' => __( 'Active', 'ae-pro' ),
			]
		);

		$this->add_control(
			'button_color_active',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button.active' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_background_active',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button.active' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_border_color_active',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-accordion-tb-wrapper .ae-accordion-toggle-button.active' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'toggle_button_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper .ae-accordion-toggle-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'toggle_button_space',
			[
				'label'     => __( 'Space Between', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper .ae-accordion-toggle-button.collapse' => 'margin-left:calc({{SIZE}}{{UNIT}}/2);',
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper .ae-accordion-toggle-button.expand' => 'margin-right:calc({{SIZE}}{{UNIT}}/2);',
				],
			]
		);

		$this->add_control(
			'toggle_box_heading',
			[
				'label'     => __( 'Toggle Box', 'ae-pro' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'toggle_box_background',
			[
				'label'     => 'Background Color',
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'toggle_button_align',
			[
				'label'       => __( 'Alignment', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'left' => [
						'title' => __( 'Start', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'End', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => is_rtl() ? 'right' : 'left',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'toggle_box',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper',
			]
		);

		$this->add_responsive_control(
			'toggle_box_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'toggle_box_space',
			[
				'label'     => __( 'Space', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-ae-acf-repeater .ae-accordion-tb-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();
	}

	public function render() {

		$settings = $this->parent->get_settings();
		if ( ! isset( $settings['template'] ) || empty( $settings['template'] ) || get_post_status( $settings['template'] ) !== 'publish' ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' ) ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Repeater > Block Layout", 'ae-pro' ) ) );
			}
		} else {
			$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );
			$this->generate_accordion_output( $settings );
		}
	}
}
