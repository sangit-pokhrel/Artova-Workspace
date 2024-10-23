<?php

namespace Aepro\Modules\Woo\Skins\WooProducts;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Skin_Base as Ae_Skin_Base;
use Aepro\Base\Widget_Base;
use \WP_Query;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Plugin;
use Aepro\Classes\CacheManager;

abstract class Skin_Base extends Ae_Skin_Base {
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		add_action( 'elementor/element/ae-woo-products/section_layout/before_section_end', [ $this, 'register_controls' ] );

		add_action( 'elementor/element/ae-woo-products/section_style/before_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;
	}

	public function render() {
		// TODO: Implement render() method.
	}

	protected function product_types() {
		return [
			'related' => __( 'Related Products', 'ae-pro' ),
			'upsell'  => __( 'Upsell Products', 'ae-pro' ),
		];
	}

	protected function product_query_controls() {
		$this->add_control(
			'product_type',
			[
				'label'   => __( 'Product Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->product_types(),
				'default' => 'related',
			]
		);

		$block_layouts[''] = 'Select Block Layout';
		$block_layouts     = $block_layouts + CacheManager::instance()->get_block_layouts();
		$this->add_control(
			'template',
			[
				'label'       => __( 'Template', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( 'Know more about templates <a href="http://aedocs.webtechstreet.com/article/9-creating-block-layout-in-anywhere-elementor-pro" target="_blank">Click Here</a>', 'ae-pro' ),
			]
		);

		$this->add_control(
			'count',
			[
				'label'   => __( 'Count', 'ae-pro' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => '4',
			]
		);
	}

	protected function grid_view_controls() {
		$this->add_control(
			'gird_view_control',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'           => __( 'Columns', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '4',
				'tablet_default'  => '2',
				'mobile_default'  => '1',
				'min'             => 1,
				'max'             => 6,
				'selectors'       => [
					'{{WRAPPER}} .ae-grid-item' => 'width: calc(100%/{{VALUE}})',
				],
			]
		);

		$this->add_control(
			'masonry',
			[
				'label'        => __( 'Masonry Layout', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					$this->get_control_id( 'columns!' ) => 1,
				],
				'prefix_class' => 'ae-masonry-',
			]
		);

		$this->add_responsive_control(
			'gutter',
			[
				'label'     => __( 'Gutter', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-grid-item' => 'padding:calc({{SIZE}}{{UNIT}}/2);',
				],
			]
		);
	}

	protected function grid_style_control() {

		$this->start_controls_tabs( 'style_tabs' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'grid_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-grid-item .ae-grid-item-inner ',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-grid-item .ae-grid-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-grid-item .ae-grid-item-inner',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'grid_border_hover',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-grid-item .ae-grid-item-inner:hover ',
			]
		);

		$this->add_control(
			'item_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-grid-item .ae-grid-item-inner:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow_hover',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-grid-item .ae-grid-item-inner:hover ',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
	}

	protected function product_carousel_control() {

		$this->add_control(
			'image_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'effect',
			[
				'label'   => __( 'Effects', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'fade'      => __( 'Fade', 'ae-pro' ),
					'slide'     => __( 'Slide', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
					'flip'      => __( 'Flip', 'ae-pro' ),
				],
				'default' => 'slide',
			]
		);

		$this->add_responsive_control(
			'slide_per_view',
			[
				'label'              => __( 'Slides Per View', 'ae-pro' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 1,
				'max'                => 100,
				'default'            => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'condition'          => [
					$this->get_control_id( 'effect' ) => [ 'slide', 'coverflow' ],
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'slides_per_group',
			[
				'label'              => __( 'Slides Per Group', 'ae-pro' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 1,
				'max'                => 100,
				'default'            => 1,
				'tablet_default'     => 1,
				'mobile_default'     => 1,
				'condition'          => [
					$this->get_control_id( 'effect' ) => [ 'slide', 'coverflow' ],
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'carousel_settings_heading',
			[
				'label'     => __( 'Setting', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'speed',
			[
				'label'       => __( 'Speed', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 5000,
				],
				'description' => __( 'Duration of transition between slides (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 300,
						'max'  => 10000,
						'step' => 300,
					],
				],

			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'duration',
			[
				'label'       => __( 'Duration', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 900,
				],
				'description' => __( 'Delay between transitions (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 300,
						'max'  => 10000,
						'step' => 300,
					],
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'              => __( 'Space Between Slides', 'ae-pro' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => [
					'size' => 15,
				],
				'tablet_default'     => [
					'size' => 10,
				],
				'mobile_default'     => [
					'size' => 5,
				],
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 5,
					],
				],
				'condition'          => [
					$this->get_control_id( 'effect' ) => [ 'slide', 'coverflow' ],
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => __( 'Loop', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'auto_height',
			[
				'label'        => __( 'Auto Height', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'        => __( 'Pause on Hover', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pagination_heading',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'ptype',
			[
				'label'   => __( ' Pagination Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' =>
					[
						''         => __( 'None', 'ae-pro' ),
						'bullets'  => __( 'Bullets', 'ae-pro' ),
						'fraction' => __( 'Fraction', 'ae-pro' ),
						'progress' => __( 'Progress', 'ae-pro' ),
					],
				'default' => 'bullets',
			]
		);

		$this->add_control(
			'clickable',
			[
				'label'     => __( 'Clickable', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'ae-pro' ),
				'label_off' => __( 'No', 'ae-pro' ),
				'condition' => [
					'ptype' => 'bullets',
				],
			]
		);

		$this->add_control(
			'navigation_button',
			[
				'label'        => __( 'Previous/Next Button', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'keyboard',
			[
				'label'        => __( 'Keyboard Control', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'scrollbar',
			[
				'label'        => __( 'Scroll bar', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);
	}

	protected function product_carousel_style() {

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Arrow', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);
		$this->start_controls_tabs( 'tabs_arrow_styles' );

		$this->start_controls_tab(
			'tab_arrow_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'fill:{{VAlUE}};',
				],
				'default'   => '#444',
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'arrow_border',
				'label'     => __( 'Border', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-next',
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrow_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);
		$this->add_control(
			'arrow_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev:hover svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover svg' => 'fill:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color_hover',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_color_hover',
			[
				'label'     => __( ' Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'border-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'border-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'arrow_size',
			[
				'label'     => __( 'Arrow Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 50,
					],
				'range'     =>
					[
						'min'  => 20,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'width : {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'width : {{SIZE}}{{UNIT}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'navigation_button' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label'   => __( 'Arrows Layout', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inside',
				'options' => [
					'inside'  => __( 'Inside', 'ae-pro' ),
					'outside' => __( 'Outside', 'ae-pro' ),
				],

			]
		);

		$this->add_responsive_control(
			'arrow_horizontal_position',
			[
				'label'       => __( 'Horizontal Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => 'center',
				'condition'   => [
					$this->get_control_id( 'navigation_button' ) => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_vertical_position',
			[
				'label'       => __( 'Vertical Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
				'condition'   => [
					$this->get_control_id( 'navigation_button' ) => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'horizontal_arrow_offset',
			[
				'label'          => __( 'Horizontal Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-hpos-left .ae-swiper-button-wrapper' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-right .ae-swiper-button-wrapper' => 'right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-next' => 'right: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					$this->get_control_id( 'navigation_button' ) => 'yes',
				],
			]
		);
		$this->add_responsive_control(
			'vertical_arrow_offset',
			[
				'label'          => __( 'Vertical Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-vpos-top .ae-swiper-button-wrapper' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-bottom .ae-swiper-button-wrapper' => 'bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-prev' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-next' => 'top: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					$this->get_control_id( 'navigation_button' ) => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_style_dots',
			[
				'label'     => __( 'Dots', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'bullets',
					],
			]
		);

		$this->add_control(
			'dots_size',
			[
				'label'     => __( 'Dots Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width:{{SIZE}}px; height:{{SIZE}}px;',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'bullets',
					],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label'     => __( 'Active Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color:{{VAlUE}} !important;',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'bullets',
					],
			]
		);

		$this->add_control(
			'inactive_dots_color',
			[
				'label'     => __( 'Inactive Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'bullets',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						$this->get_control_id( 'ptype' ) => 'bullets',
					],
			]
		);

		$this->add_control(
			'heading_style_scroll',
			[
				'label'     => __( 'Scrollbar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						$this->get_control_id( 'scrollbar' ) => 'yes',
					],
			]
		);
		$this->add_control(
			'scroll_size',
			[
				'label'     => __( 'Scrollbar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-vertical .ae-swiper-scrollbar' => 'width:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-horizontal .ae-swiper-scrollbar' => 'height:{{SIZE}}px;',
				],
				'condition' =>
					[
						$this->get_control_id( 'scrollbar' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'scrollbar_color',
			[
				'label'     => __( 'Scrollbar Drag Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-scrollbar-drag' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'scrollbar' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'scroll_color',
			[
				'label'     => __( 'Scrollbar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-scrollbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'scrollbar' ) => 'yes',
					],
			]
		);

		$this->add_control(
			'heading_style_progress',
			[
				'label'     => __( 'Progress Bar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'progress',
					],
			]
		);
		$this->add_control(
			'progressbar_color',
			[
				'label'     => __( 'Prgress Bar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'progress',
					],
			]
		);

		$this->add_control(
			'progress_color',
			[
				'label'     => __( 'Prgress Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'progress',
					],
			]
		);

		$this->add_control(
			'progressbar_size',
			[
				'label'     => __( 'Prgress Bar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-horizontal .swiper-pagination-progressbar' => 'height:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-vertical .swiper-pagination-progressbar' => 'width:{{SIZE}}px;',
				],
				'condition' =>
					[
						$this->get_control_id( 'ptype' ) => 'progress',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_progress_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						$this->get_control_id( 'ptype' ) => 'progress',
					],
			]
		);
	}

	protected function common_controls() {
		$this->add_control(
			'common_comtrols',
			[
				'label'     => __( 'Setting', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'orientation',
			[
				'label'   => __( 'Orientation', 'ae-pro' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'horizontal' => [
						'title' => __( 'Horizontal', 'ae-pro' ),
						'icon'  => 'fa fa-arrows-h',
					],
					'vertical' => [
						'title' => __( 'Vertical', 'ae-pro' ),
						'icon'  => 'fa fa-arrows-v',
					],
				],
				'default' => 'horizontal',
			]
		);

		$this->add_control(
			'height',
			[
				'label'     => __( 'Height ', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 300,
				],
				'range'     => [
					'px' => [
						'min'  => 100,
						'max'  => 1000,
						'step' => 50,
					],
				],
				'condition' => [
					$this->get_control_id( 'orientation' ) => 'vertical',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-outer-wrapper' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label'   => __( 'Speed', 'ae-pro' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 300,
				],
				'range'   => [
					'px' => [
						'min'  => 100,
						'max'  => 900,
						'step' => 1,
					],
				],

			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'duration',
			[
				'label'     => __( 'Duration', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 3000,
				],
				'range'     => [
					'px' => [
						'min'  => 1000,
						'max'  => 10000,
						'step' => 1000,
					],
				],
				'condition' => [
					$this->get_control_id( 'autoplay' ) => 'yes',
				],
			]
		);

		// Todo:: different effects management
		$this->add_control(
			'effect',
			[
				'label'   => __( 'Effects', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'fade'      => __( 'Fade', 'ae-pro' ),
					'slide'     => __( 'Slide', 'ae-pro' ),
					'cube'      => __( 'Cube', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
					'flip'      => __( 'Flip', 'ae-pro' ),
				],
				'default' => 'slide',
			]
		);

		$this->add_control(
			'space',
			[
				'label'   => __( 'Space Between Slides', 'ae-pro' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 15,
				],
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 5,
					],
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => __( 'Loop', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'zoom',
			[
				'label'        => __( 'Zoom', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);
	}

	protected function get_products_query_args() {
		$data_type = $this->get_instance_value( 'product_type' );
		$count     = $this->get_instance_value( 'count' );

		switch ( $data_type ) {
			case 'related':
				$product = Aepro::$_helper->get_ae_woo_product_data();
				if ( is_null( $product ) || $product->post_type !== 'product' ) {
					return [];
				}
				$related_products = wc_get_related_products( $product->get_id(), $count, $product->get_upsell_ids() );
				$args             = [
					'post_type' => 'product',
					'stock'     => 1,
					'orderby'   => 'date',
					'order'     => 'DESC',
					'post__in'  => $related_products,
				];
				break;

			case 'upsell':
				$product = Aepro::$_helper->get_ae_woo_product_data();
				if ( is_null( $product ) || $product->post_type !== 'product' ) {
					return [];
				}
				$upsell_products = $product->get_upsell_ids();
				$args            = [
					'post_type'      => 'product',
					'posts_per_page' => $count,
					'stock'          => 1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'post__in'       => $upsell_products,
				];
				break;
		}

		return $args;
	}

	protected function swiper_html() {
		$args      = $this->get_products_query_args();
		$templates = $this->get_instance_value( 'template' );

		$pagination_type   = $this->get_instance_value( 'ptype' );
		$navigation_button = $this->get_instance_value( 'navigation_button' );
		$scrollbar         = $this->get_instance_value( 'scrollbar' );

		$swiper_data = $this->get_swiper_data();

		$this->parent->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper' );

		$this->parent->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );
		$this->parent->add_render_attribute( 'swiper-container', 'class', 'ae-swiper-container' );
		$swiper_class = '';
		$swiper_class = Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' ) ? 'swiper' : 'swiper-container';
		//$this->parent->add_render_attribute( 'swiper-container', 'class', $swiper_class );
		?>
			<div <?php echo $this->parent->get_render_attribute_string( 'outer-wrapper' ); ?> >
				<div <?php echo $this->parent->get_render_attribute_string( 'swiper-container' ); ?>>
					<div class="ae-swiper-wrapper swiper-wrapper">
						<?php

						$loop = new WP_Query( $args );
						while ( $loop->have_posts() ) {
							$loop->the_post();
							global $product;
							?>
							<div class="ae-swiper-slide swiper-slide">
								<?php echo Plugin::instance()->frontend->get_builder_content( $templates ); ?>
							</div>
							<?php

						}
						wp_reset_postdata();
						?>
					</div>

					<?php if ( $pagination_type !== '' ) { ?>
						<div class = "ae-swiper-pagination swiper-pagination"></div>
					<?php } ?>

					<?php if ( $navigation_button === 'yes' ) { ?>
						<div class = "ae-swiper-button-prev swiper-button-prev"></div>
						<div class = "ae-swiper-button-next swiper-button-next"></div>
					<?php } ?>

					<?php if ( $scrollbar === 'yes' ) { ?>
						<div class = "ae-swiper-scrollbar swiper-scrollbar"></div>

					<?php } ?>

				</div>
			</div>
		<?php
	}

	public function get_swiper_data() {

		$loop = $this->get_instance_value( 'loop' );

		$zoom              = $this->get_instance_value( 'zoom' );
		$pagination_type   = $this->get_instance_value( 'ptype' );
		$navigation_button = $this->get_instance_value( 'navigation_button' );
		$clickable         = $this->get_instance_value( 'clickable' );
		$keyboard          = $this->get_instance_value( 'keyboard' );

		$ptype = $this->get_instance_value( 'ptype' );

		// TODO:: Swiper Data Populate
		if ( $this->get_instance_value( 'speed' )['size'] ) {
			$swiper_data['speed'] = $this->get_instance_value( 'speed' )['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}

		$swiper_data['direction'] = 'horizontal';

		if ( $this->get_instance_value( 'autoplay' ) === 'yes' ) {
			$duration                                        = $this->get_instance_value( 'duration' );
			$swiper_data['autoplay']['delay']                = $duration['size'];
			$swiper_data['autoplay']['disableOnInteraction'] = true;
		} else {
			$swiper_data['autoplay'] = false;
		}

		$swiper_data['effect'] = $this->get_instance_value( 'effect' );

		$swiper_data['loop']       = $this->get_instance_value( 'loop' );
		$swiper_data['autoHeight'] = ( $this->get_instance_value( 'auto_height' ) === 'yes' );

		$active_devices = Plugin::$instance->breakpoints->get_active_devices_list();
		$break_value    = [];
		foreach ( $active_devices as $active_device ) {
			$min_breakpoint                = Plugin::$instance->breakpoints->get_device_min_breakpoint( $active_device );
			$break_value[ $active_device ] = $min_breakpoint;
		}

		if ( $this->get_instance_value( 'effect' ) === 'fade' || $this->get_instance_value( 'effect' ) === 'flip' ) {
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( $active_device === 'desktop' ) {
					$active_device = 'default';
				}
				$swiper_data['spaceBetween'][ $active_device ] = 0;
			}
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( $active_device === 'desktop' ) {
					$active_device = 'default';
				}
				$swiper_data['slidesPerView'][ $active_device ] = 1;
			}
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( $active_device === 'desktop' ) {
					$active_device = 'default';
				}
				$swiper_data['slidesPerGroup'][ $active_device ] = 1;
			}
		} else {
			// SpaceBetween
			foreach ( $active_devices as $break_key => $active_device ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $this->get_instance_value( 'space_' . $active_device )['size'] !== '' ? $this->get_instance_value( 'space_' . $active_device )['size'] : 5 );
							break;
						case 'tablet':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $this->get_instance_value( 'space_' . $active_device )['size'] !== '' ? $this->get_instance_value( 'space_' . $active_device )['size'] : 10 );
							break;
						case 'desktop':
							$swiper_data['spaceBetween']['default'] = intval( $this->get_instance_value( 'space' )['size'] !== '' ? $this->get_instance_value( 'space' )['size'] : 15 );
							break;
					}
				} else {
					$swiper_data['spaceBetween'][ $active_device ] = intval( $this->get_instance_value( 'space_' . $active_device )['size'] !== '' ? $this->get_instance_value( 'space_' . $active_device )['size'] : 15 );
				}
			}

			// SlidesPerView
			foreach ( $active_devices as $break_key => $active_device ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $this->get_instance_value( 'slide_per_view_' . $active_device ) !== '' ? $this->get_instance_value( 'slide_per_view_' . $active_device ) : 1 );
							break;
						case 'tablet':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $this->get_instance_value( 'slide_per_view_' . $active_device ) !== '' ? $this->get_instance_value( 'slide_per_view_' . $active_device ) : 2 );
							break;
						case 'desktop':
							$swiper_data['slidesPerView']['default'] = intval( $this->get_instance_value( 'slide_per_view' ) !== '' ? $this->get_instance_value( 'slide_per_view' ) : 3 );
							break;
					}
				} else {
					$swiper_data['slidesPerView'][ $active_device ] = intval( $this->get_instance_value( 'slide_per_view_' . $active_device ) !== '' ? $this->get_instance_value( 'slide_per_view_' . $active_device ) : 2 );
				}
			}

			// SlidesPerGroup
			foreach ( $active_devices as $break_key => $active_device ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['slidesPerGroup'][ $active_device ] = $this->get_instance_value( 'slides_per_group_' . $active_device ) !== '' ? $this->get_instance_value( 'slides_per_group_' . $active_device ) : 1;
							break;
						case 'tablet':
							$swiper_data['slidesPerGroup'][ $active_device ] = $this->get_instance_value( 'slides_per_group_' . $active_device ) !== '' ? $this->get_instance_value( 'slides_per_group_' . $active_device ) : 1;
							break;
						case 'desktop':
							$swiper_data['slidesPerGroup']['default'] = $this->get_instance_value( 'slides_per_group' ) !== '' ? $this->get_instance_value( 'slides_per_group' ) : 1;
							break;
					}
				} else {
					$swiper_data['slidesPerGroup'][ $active_device ] = $this->get_instance_value( 'slides_per_group_' . $active_device ) !== '' ? $this->get_instance_value( 'slides_per_group_' . $active_device ) : 1;
				}
			}
		}

		$swiper_data['ptype'] = $this->get_instance_value( 'ptype' );
		if ( $swiper_data['ptype'] !== '' ) {
			if ( $swiper_data['ptype'] === 'progress' ) {
				$swiper_data['ptype'] = 'progressbar';
			}
		}
		$swiper_data['breakpoints_value'] = $break_value;
		$swiper_data['clickable']         = $this->get_instance_value( 'clickable' );
		$swiper_data['navigation']        = $this->get_instance_value( 'navigation_button' );
		$swiper_data['scrollbar']         = $this->get_instance_value( 'scrollbar' );

		return $swiper_data;
	}
}
