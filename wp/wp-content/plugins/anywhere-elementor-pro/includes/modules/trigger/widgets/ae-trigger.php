<?php

namespace Aepro\Modules\Trigger\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Aepro\Modules\Trigger\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AeTrigger extends Widget_Base {

	protected $_access_level = 1;

	public function get_name() {

		return 'ae-trigger';
	}

	public function get_title() {

		return __( 'AE - Trigger', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'trigger',
			'carousel',
			'slider',
			'accordion',
			'tabs',
		];
	}

	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Style1( $this ) );
		$this->add_skin( new Skins\Skin_Style2( $this ) );
	}

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			[
				'label' => __( 'General', 'ae-pro' ),
			]
		);

		$this->add_control(
			'trigger_target',
			[
				'label'              => __( 'Target', 'ae-pro' ),
				'type'               => Controls_Manager::TEXT,
				'placeholder'        => 'Widget Id',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'ae-trigger-widget-doc',
			[
				'type'    => Controls_Manager::RAW_HTML,
				'classes' => 'ae-widget-id',
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'raw'     => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Trigger', 'https://wpvibes.link/go/widget-ae-trigger' ), 'ae-pro' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_triggers',
			[
				'label' => esc_html__( 'Triggers', 'ae-pro' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'trigger_type',
			[
				'label'              => __( 'Type', 'ae-pro' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'carousel'  => __( 'Carousel / Slider', 'ae-pro' ),
					'accordion' => __( 'Accordion', 'ae-pro' ),
				],
				'default'            => 'carousel',
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'trigger_action_carousel',
			[
				'label'              => __( 'Action', 'ae-pro' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'next_slide'  => __( 'Next Slide', 'ae-pro' ),
					'prev_slide'  => __( 'Prev Slide', 'ae-pro' ),
					'first_slide' => __( 'First Slide', 'ae-pro' ),
					'last_slide'  => __( 'Last Slide', 'ae-pro' ),
					'play_slide'  => __( 'Play', 'ae-pro' ),
					'pause_slide' => __( 'Pause', 'ae-pro' ),
					'play_pause'  => __( 'Play / Pause', 'ae-pro' ),
				],
				'default'            => 'next_slide',
				'frontend_available' => true,
				'condition'          => [
					'trigger_type' => 'carousel',
				],
			]
		);

		$repeater->add_control(
			'trigger_action_accordion',
			[
				'label'              => __( 'Action', 'ae-pro' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'expand'          => __( 'Expand', 'ae-pro' ),
					'collapse'        => __( 'Collapse', 'ae-pro' ),
					'next'            => __( 'Next', 'ae-pro' ),
					'prev'            => __( 'Prev', 'ae-pro' ),
					'expand_collapse' => __( 'Expand / Collapse', 'ae-pro' ),
				],
				'default'            => 'expand',
				'frontend_available' => true,
				'condition'          => [
					'trigger_type' => 'accordion',
				],
			]
		);

		$repeater->add_control(
			'target_divider',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$repeater->add_control(
			'text',
			[
				'label' => esc_html__( 'Text', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label'              => esc_html__( 'Icon', 'ae-pro' ),
				'type'               => Controls_Manager::ICONS,
				'fa4compatibility'   => 'icon',
				'skin'               => 'inline',
				'label_block'        => false,
				'default'            => [
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'secondary_text',
			[
				'label'      => esc_html__( 'Secondary Text', 'ae-pro' ),
				'type'       => Controls_Manager::TEXT,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => 'trigger_action_carousel',
									'operator' => '==',
									'value'    => 'play_pause',
								],
								[
									'name'     => 'trigger_type',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'trigger_action_accordion',
									'operator' => '==',
									'value'    => 'expand_collapse',
								],
								[
									'name'     => 'trigger_type',
									'operator' => '==',
									'value'    => 'accordion',
								],
							],
						],
					],
				],
			]
		);

		$repeater->add_control(
			'selected_icon_secondary',
			[
				'label'            => esc_html__( 'Secondary Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin'             => 'inline',
				'label_block'      => false,
				'conditions'       => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => 'trigger_action_carousel',
									'operator' => '==',
									'value'    => 'play_pause',
								],
								[
									'name'     => 'trigger_type',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'trigger_action_accordion',
									'operator' => '==',
									'value'    => 'expand_collapse',
								],
								[
									'name'     => 'trigger_type',
									'operator' => '==',
									'value'    => 'accordion',
								],
							],
						],
					],
				],
			]
		);

		$repeater->add_control(
			'icon_align',
			[
				'label'     => esc_html__( 'Icon Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'  => esc_html__( 'Before', 'ae-pro' ),
					'right' => esc_html__( 'After', 'ae-pro' ),
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
				'condition' => [
					'text!' => '',
				],
			]
		);

		$repeater->add_responsive_control(
			'icon_indent',
			[
				'label'     => esc_html__( 'Icon Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .ae-trigger-inner .ae-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .ae-trigger-inner .ae-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'text!' => '',
				],
			]
		);

		$repeater->add_responsive_control(
			'icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .ae-trigger-inner .ae-trigger-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'triggers',
			[
				'label'       => __( 'Triggers', 'ae-pro' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ elementor.helpers.renderIcon( this, selected_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}} {{{ text }}}',
				'default'     => [
					[
						'trigger_action_carousel' => 'prev_slide',
						'selected_icon'           => [
							'value'   => 'fas fa-angle-left',
							'library' => 'fa-solid',
						],
					],
					[
						'trigger_action_carousel' => 'play_pause',
						'selected_icon'           => [
							'value'   => 'fas fa-play',
							'library' => 'fa-solid',
						],
						'selected_icon_secondary' => [
							'value'   => 'fas fa-pause',
							'library' => 'fa-solid',
						],
					],
					[
						'trigger_action_carousel' => 'next_slide',
						'selected_icon'           => [
							'value'   => 'fas fa-angle-right',
							'library' => 'fa-solid',
						],
					],

				],
			]
		);

		$this->add_responsive_control(
			'trigger_layout',
			[
				'label'          => __( 'Layout', 'ae-pro' ),
				'type'           => Controls_Manager::CHOOSE,
				'default'        => 'inline',
				'options'        => [
					'traditional' => [
						'title' => __( 'Default', 'ae-pro' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'inline' => [
						'title' => __( 'Inline', 'ae-pro' ),
						'icon'  => 'eicon-ellipsis-h',
					],
				],
				'label_block'    => false,
				'style_transfer' => true,
				'prefix_class'   => 'ae-trigger-layout-',
			]
		);

		$this->add_responsive_control(
			'trigger_align',
			[
				'label'     => esc_html__( 'Alignment', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'start' => [
						'title' => esc_html__( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}.ae-trigger-layout-inline .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}};',
					'{{WRAPPER}}.ae-trigger-layout-inline .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}};',
				],
				'default'   => 'center',
			]
		);

		$this->end_controls_section();
	}

	protected $_has_template_content = false;

}
