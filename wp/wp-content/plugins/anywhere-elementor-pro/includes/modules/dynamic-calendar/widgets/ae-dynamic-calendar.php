<?php
namespace Aepro\Modules\DynamicCalendar\Widgets;

use Aepro\Aepro;
use Elementor\Plugin;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Aepro\Post_Helper;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Background;
use Aepro\Modules\DynamicCalendar\Classes\Query;
use Aepro\Modules\DynamicCalendar\Skins;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Aepro\Frontend;
use Aepro\Classes\CacheManager;

class AeDynamicCalendar extends Widget_Base {

	protected $_access_level = 1;

	public function get_name() {
		return 'ae-dynamic-calendar';
	}

	public function get_title() {
		return __( 'AE - Dynamic Calendar', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-calendar';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_script_depends() {

		// load all scripts in editor and preview mode
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {

			return [ 'swiper' ];

		}

		$scripts  = [];
		$settings = $this->get_settings_for_display();

		if ( $settings['listing_layout'] === 'carousel' ) {
			$scripts[] = 'swiper';
		}

		return $scripts;
	}

	public function get_keywords() {
		return [
			'calendar',
			'post calendar',
			'dynamic calendar',
		];
	}

	protected $_has_template_content = false;
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_1( $this ) );
	}

    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		$query = new Query( [] );

		//Controls
		$this->get_calendar_section();
		$query->get_query_section( $this );
		$this->get_layout_section();
		$this->get_listing_section();
		$this->get_grid_controls();
		$this->get_carousel_section();
		$this->loading_overlay();
		$this->get_widget_title_controls();

		//Styles
		$this->get_calendar_styles();
		$this->get_listing_styles();
		$this->get_carousel_style();
		$this->loading_overlay_style();
		$this->noPostMsg_style();
		$this->get_widget_title_style_controls();
	}

	public function get_calendar_section() {

		$this->start_controls_section(
			'section_calendar',
			[
				'label' => __( 'Calendar', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'calendar_source',
			[
				'label'   => __( 'Calendar', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'current_month' => __( 'Current Month', 'ae-pro' ),
					'custom_month'  => __( 'Custom', 'ae-pro' ),
				],
				'default' => 'current_month',
			]
		);

		$this->add_control(
			'calendar_month',
			[
				'label'     => __( 'Month', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => Aepro::$_helper->get_months(),
				'default'   => 1,
				'condition' => [
					'calendar_source' => [ 'custom_month' ],
				],
			]
		);

		$this->add_control(
			'calendar_year',
			[
				'label'     => __( 'Year', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => Aepro::$_helper->get_year(),
				'default'   => 2000,
				'condition' => [
					'calendar_source' => [ 'custom_month' ],
				],
			]
		);

		$this->add_control(
			'date_source',
			[
				'label'   => __( 'Date Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'post_date'     => __( 'Post Date', 'ae-pro' ),
					'modified_date' => __( 'Modified Date', 'ae-pro' ),
					'custom_field'  => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'post_date',
			]
		);

		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {
			$date_source['acf_field'] = __( 'ACF Field', 'ae-pro' );
		}
		$date_source['custom_field'] = __( 'Custom Field', 'ae-pro' );

		$this->add_control(
			'field_type',
			[
				'label'     => __( 'Field Type', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $date_source,
				'default'   => 'acf_field',
				'condition' => [
					'date_source' => [ 'custom_field' ],
				],
			]
		);

		$date_field = Aepro::$_helper->get_acf_field_groups( [ 'date_picker', 'text' ] );
		$this->add_control(
			'acf_date_field',
			[
				'label'     => __( 'Field', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'groups'    => $date_field,
				'condition' => [
					'field_type'  => 'acf_field',
					'date_source' => [ 'custom_field' ],
				],
			]
		);

		$this->add_control(
			'custom_date_field',
			[
				'label'       => __( 'Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your custom field key', 'ae-pro' ),
				'condition'   => [
					'field_type'  => 'custom_field',
					'date_source' => [ 'custom_field' ],
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_layout_section() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_overlay',
			[
				'label'        => __( 'Loading Overlay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	public function get_listing_section() {

		$this->start_controls_section(
			'section_listing',
			[
				'label' => __( 'Listing', 'ae-pro' ),
			]
		);

		$this->add_control(
			'listing_layout',
			[
				'label'   => __( 'Layout', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'grid'     => __( 'Grid', 'ae-pro' ),
					'carousel' => __( 'Carousel', 'ae-pro' ),
				],
				'default' => 'grid',
			]
		);

		$block_layouts[''] = 'Select Block Layout';
		$block_layouts     = $block_layouts + CacheManager::instance()->get_block_layouts();

		$this->add_control(
			'listing_block_layout',
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Block Layouts', 'https://wpvibes.link/go/feature-creating-block-layout/' ), 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'listing_position',
			[
				'label'        => esc_html__( 'Listing Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'right' => [
						'title' => esc_html__( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
					'left' => [
						'title' => esc_html__( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'overlap' => [
						'title' => esc_html__( 'Overlap', 'ae-pro' ),
						'icon'  => 'eicon-instagram-nested-gallery',
					],
				],
				'default'      => 'right',
				'toggle'       => false,
				'prefix_class' => 'ae-dc-listing-align-',
			]
		);

		$this->add_control(
			'preview_listing',
			[
				'label'                => __( 'Preview', 'ae-pro' ),
				'type'                 => Controls_Manager::SWITCHER,
				'label_on'             => __( 'Yes', 'ae-pro' ),
				'label_off'            => __( 'No', 'ae-pro' ),
				'return_value'         => 'yes',
				'selectors_dictionary' => [
					'yes' => 'z-index:10',
				],
				'selectors'            => [
					'.elementor-editor-active {{WRAPPER}} .ae-dc-listing' => '{{VALUE}}',
				],
				'condition'            => [
					'listing_position' => 'overlap',
				],
			]
		);

		$this->add_control(
			'listing_render',
			[
				'label'              => __( 'Render', 'ae-pro' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'load'  => __( 'On Load', 'ae-pro' ),
					'click' => __( 'Click', 'ae-pro' ),
				],
				'default'            => 'load',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'space_between',
			[
				'label'     => __( 'Space Between', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}}.ae-dc-listing-align-bottom .ae-dc-render' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-dc-listing-align-right .ae-dc-render' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-dc-listing-align-left .ae-dc-render' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'listing_width',
			[
				'label'     => __( 'Width', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default'   => [
					'unit' => '%',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-dc-render'  => 'width: calc(100{{UNIT}} - {{SIZE}}{{UNIT}})',
					'{{WRAPPER}} .ae-dc-listing' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'listing_position' => [ 'left', 'right' ],
				],
			]
		);

		$this->add_responsive_control(
			'listing_height',
			[
				'label'      => __( 'Height', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%', 'vh', 'vw' ],
				/* 'default' => [
					'unit' => 'px',
					'size' => 400,
				], */
				'selectors'  => [
					'{{WRAPPER}} .ae-dc-wrapper .ae-dc-listing' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'listing_position' => [ 'left', 'right' ],
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_grid_controls() {

		$this->start_controls_section(
			'section_grid',
			[
				'label'     => __( 'Grid', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'listing_layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'           => __( 'Columns', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '3',
				'tablet_default'  => '2',
				'mobile_default'  => '1',
				'min'             => 1,
				'max'             => 12,
				'selectors'       => [
					'{{WRAPPER}} .ae-dc-listing-wrapper' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); display:grid',
				],
			]
		);

		$this->add_responsive_control(
			'item_col_gap',
			[
				'label'     => __( 'Column Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-dc-listing-wrapper' => 'column-gap: {{SIZE}}{{UNIT}}; grid-column-gap: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'columns!' => 1,
				],
			]
		);

		$this->add_responsive_control(
			'item_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-dc-listing-wrapper' => 'row-gap: {{SIZE}}{{UNIT}}; grid-row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_carousel_section() {

		$this->start_controls_section(
			'section_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'listing_layout' => 'carousel',
				],
			]
		);

		/* $this->add_control(
			'direction',
			[
				'label'     => __( 'Direction', 'ae-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'toggle' => false,
				'classes' => 'ae-widget-dynamic-map',
				'options'   => [
					'horizontal' => [
						'title' => esc_html__( 'Horizontal', 'ae-pro' ),
						'icon' => 'eicon-slides',
					],
					'vertical' => [
						'title' => esc_html__( 'Vertical', 'ae-pro' ),
						'icon' => 'eicon-slider-vertical',
					],
				],
				'default'   => 'horizontal',
				'render_type' => 'template',
				'prefix_class' => 'ae-listing-swiper-dir-',
			]
		); */

		$this->add_control(
			'layout_mode_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_layout_mode_alert',
				'raw'             => __( '<p class="ae-editor-note"><i><b>Note:</b> Vertical Direction only used in Left / Right Listing Position</i></p>', 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'direction' => 'vertical',
				],
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
				'conditions'         => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'slide',
						],
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'coverflow',
						],
					],
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
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'speed',
			[
				'label'       => __( 'Speed', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 1000,
				],
				'description' => __( 'Duration of transition between slides (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 1000,
						'max'  => 10000,
						'step' => 1000,
					],
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
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
						'max'  => 3000,
						'step' => 300,
					],
				],
				'condition'   => [
					'autoplay' => 'yes',
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

		$this->add_control(
			'navigation_heading',
			[
				'label' => __( 'Navigation', 'ae-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'navigation_button',
			[
				'label'        => __( 'Enable', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label'     => __( 'Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inside',
				'options'   => [
					'inside'  => __( 'Inside', 'ae-pro' ),
					'outside' => __( 'Outside', 'ae-pro' ),
				],
				'condition' => [
					'navigation_button' => 'yes',
				],

			]
		);

		$this->add_control(
			'arrow_icon_left',
			[
				'label'            => __( 'Icon Prev', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-left',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrow_icon_right',
			[
				'label'            => __( 'Icon Next', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-right',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
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
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
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
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',

				],
			]
		);

		$this->end_controls_section();
	}

	public function get_calendar_styles() {

		$this->start_controls_section(
			'section_calendar_styles',
			[
				'label'     => __( 'Calendar', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_overlay' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_listing_styles() {

		$this->start_controls_section(
			'section_listing_style',
			[
				'label' => __( 'Listing', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .dc-listing-item-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .dc-listing-item-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dc-listing-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .dc-listing-item-inner',
			]
		);

		$this->end_controls_section();
	}

	public function get_carousel_style() {
		$this->start_controls_section(
			'section_carousel_style',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'listing_layout' => 'carousel',
				],
			]
		);

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Prev/Next Navigation', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' =>
					[
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
						'navigation_button' => 'yes',
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
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
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
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_gap',
			[
				'label'          => __( 'Arrow Gap', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => 'px',
					'size' => '25',
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
					'{{WRAPPER}} .ae-swiper-container'     => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-swiper-outer-wrapper' => 'position: relative',
					'{{WRAPPER}} .ae-swiper-button-prev'   => 'left: 0',
					'{{WRAPPER}} .ae-swiper-button-next'   => 'right: 0',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'outside',
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
						'ptype' => 'bullets',
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
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_responsive_control(
			'dot_top_offset',
			[
				'label'     => __( 'Top Offset', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-post-widget-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'ptype' => 'bullets',
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
						'ptype' => 'bullets',
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
						'ptype' => 'bullets',
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
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'heading_style_fraction',
			[
				'label'     => __( 'Fraction', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_control(
			'fraction_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-current, {{WRAPPER}} .swiper-pagination-total' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_control(
			'fraction_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-fraction' => 'color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_typography',
				'label'     => __( 'Typography', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_responsive_control(
			'fraction_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-current, {{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-total' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'fraction',
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
						'scrollbar' => 'yes',
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
						'scrollbar' => 'yes',
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
						'scrollbar' => 'yes',
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
						'scrollbar' => 'yes',
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
						'ptype' => 'progressbar',
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
						'ptype' => 'progressbar',
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
						'ptype' => 'progressbar',
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
						'ptype' => 'progressbar',
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
						'ptype' => 'progressbar',
					],
			]
		);

		$this->end_controls_section();
	}

	public function loading_overlay() {

		$this->start_controls_section(
			'overlay_section',
			[
				'label'     => __( 'Overlay', 'ae-pro' ),
				'condition' => [
					'enable_overlay' => 'yes',
				],
			]
		);

		$this->add_control(
			'overlay_loading_text_icon',
			[
				'label' => __( 'Icon', 'ae-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'overlay_loading_text_icon_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'overlay_icon',
			[
				'label'            => __( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-sync',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'overlay_text',
			[
				'label'   => __( 'Text', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Loading...',
			]
		);

		$this->add_control(
			'overlay_icon_animation',
			[
				'label'        => __( 'Animate', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'prefix_class' => 'overlay-icon-animation-',
			]
		);

		$this->add_control(
			'overlay_loading_text_alignment',
			[
				'label' => __( 'Alignment', 'ae-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'overlay_loading_text_alignment_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_responsive_control(
			'overlay_text_horizontal_position',
			[
				'label'                => __( 'Horizontal', 'ae-pro' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
				],
				'selectors_dictionary' => [
					'left'   => 'left: 0;',
					'right'  => 'right: 0;',
					'center' => 'left: 50%; transform: translateX(-50%)',
				],
				'selectors'            => [
					'{{WRAPPER}}.overlay-h-pos-left .ae-post-overlay-wrapper' => '{{VALUE}}',
					'{{WRAPPER}}.overlay-h-pos-right .ae-post-overlay-wrapper' => '{{VALUE}}',
					'{{WRAPPER}}.overlay-h-pos-center .ae-post-overlay-wrapper' => '{{VALUE}}',
				],
				'default'              => 'center',
				'prefix_class'         => 'overlay-h-pos-',
			]
		);

		$this->add_responsive_control(
			'overlay_text_vertical_position',
			[
				'label'                => __( 'Vertical', 'ae-pro' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
				],
				'selectors_dictionary' => [
					'top'    => 'top 0;',
					'bottom' => 'bottom: 0;',
					'middle' => 'top: 50%; transform: translateY(-50%)',
				],
				'selectors'            => [
					'{{WRAPPER}}.overlay-v-pos-top .ae-post-overlay-wrapper' => '{{VALUE}}',
					'{{WRAPPER}}.overlay-v-pos-middle .ae-post-overlay-wrapper' => '{{VALUE}}',
					'{{WRAPPER}}.overlay-v-pos-bottom .ae-post-overlay-wrapper' => '{{VALUE}}',
				],
				'default'              => 'middle',
				'prefix_class'         => 'overlay-v-pos-',
			]
		);

		$this->add_responsive_control(
			'overlay_text_horizontal_offset',
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
					'{{WRAPPER}}.overlay-h-pos-right .ae-post-overlay-wrapper' => 'right: {{SIZE}}{{UNIT}}; transform: translateX({{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-left .ae-post-overlay-wrapper' => 'left: {{SIZE}}{{UNIT}}; transform: translateX(-{{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-left.overlay-v-pos-middle .ae-post-overlay-wrapper' => 'left: {{SIZE}}{{UNIT}}; top: 50%; transform: translate(-{{SIZE}}{{UNIT}},-50%);',
					'{{WRAPPER}}.overlay-h-pos-right.overlay-v-pos-middle .ae-post-overlay-wrapper' => 'right: {{SIZE}}{{UNIT}}; top: 50%; transform: translate({{SIZE}}{{UNIT}},-50%);',

				],
			]
		);
		$this->add_responsive_control(
			'overlay_text_vertical_offset',
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
					'{{WRAPPER}}.overlay-h-pos-left.overlay-v-pos-top .ae-post-overlay-wrapper' => 'top: {{SIZE}}{{UNIT}}; transform: translate(-{{overlay_text_horizontal_offset.SIZE}}{{overlay_text_horizontal_offset.UNIT}}, -{{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-right.overlay-v-pos-top .ae-post-overlay-wrapper' => 'top: {{SIZE}}{{UNIT}}; transform: translate({{overlay_text_horizontal_offset.SIZE}}{{overlay_text_horizontal_offset.UNIT}}, -{{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-left.overlay-v-pos-bottom .ae-post-overlay-wrapper' => 'bottom: {{SIZE}}{{UNIT}}; transform: translate(-{{overlay_text_horizontal_offset.SIZE}}{{overlay_text_horizontal_offset.UNIT}},{{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-right.overlay-v-pos-bottom .ae-post-overlay-wrapper' => 'bottom: {{SIZE}}{{UNIT}}; transform: translate({{overlay_text_horizontal_offset.SIZE}}{{overlay_text_horizontal_offset.UNIT}},{{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-center.overlay-v-pos-middle .ae-post-overlay-wrapper' => 'left: 50%; top: 50%; transform: translate(-50%,-50%);',
					'{{WRAPPER}}.overlay-v-pos-bottom .ae-post-overlay-wrapper' => 'bottom: {{SIZE}}{{UNIT}}; transform: translateY({{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-center.overlay-v-pos-bottom .ae-post-overlay-wrapper' => 'bottom: {{SIZE}}{{UNIT}}; transform: translate(-50%, {{SIZE}}{{UNIT}});',
					'{{WRAPPER}}.overlay-h-pos-center.overlay-v-pos-top .ae-post-overlay-wrapper' => 'top: {{SIZE}}{{UNIT}}; transform: translate(-50%, -{{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->add_control(
			'preview_overlay',
			[
				'label'                => __( 'Preview Overlay', 'ae-pro' ),
				'type'                 => Controls_Manager::SWITCHER,
				'default'              => '',
				'label_on'             => __( 'Yes', 'ae-pro' ),
				'label_off'            => __( 'No', 'ae-pro' ),
				'return_value'         => 'yes',
				'selectors_dictionary' => [
					'yes' => 'display: block',
				],
				'selectors'            => [
					'.elementor-editor-active {{WRAPPER}} .ae-post-overlay' => '{{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function loading_overlay_style() {
		$this->start_controls_section(
			'overlay_style_section',
			[
				'label'     => __( 'Overlay', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_overlay' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_color',
				'label'          => __( 'Overlay Color', 'ae-pro' ),
				'types'          => [ 'none', 'classic', 'gradient' ],
				'selector'       => '{{WRAPPER}} .ae-post-overlay',
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(0,0,0,0.5)',
					],
				],
			]
		);

		$this->add_control(
			'overlay_icon_color',
			[
				'label'     => __( 'Icon Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-icon i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-post-overlay-icon svg' => 'fill:{{VAlUE}};',
				],
				'default'   => '#FFFFFF',
			]
		);

		$this->add_control(
			'overlay_icon_size',
			[
				'label'     => __( 'Icon Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 50,
					],
				'range'     =>
					[
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-icon i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-post-overlay-icon svg' => 'width : {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'overlay_text_color',
			[
				'label'     => __( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-text' => 'color:{{VAlUE}};',
				],
				'default'   => '#FFFFFF',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'overlay_text_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-post-overlay-text',
			]
		);

		$this->end_controls_section();
	}

	public function noPostMsg_style() {
		$this->start_controls_section(
			'noPostMsg_style',
			[
				'label'     => __( 'No Post Message', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'no_posts_message!' => '',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'no_posts_msg_typography',
				'selector' => '{{WRAPPER}} .ae-no-posts',
			]
		);
		$this->add_responsive_control(
			'no_posts_msg_align',
			[
				'label'     => __( 'Alignment', 'ae-pro' ),
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
					'{{WRAPPER}} .ae-no-posts' => 'text-align: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'no_posts_msg_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'no_posts_bg_color',
			[
				'label'     => __( 'Background Color' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'no_posts_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-no-posts',
			]
		);

		$this->add_responsive_control(
			'no_posts_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-no-posts' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'no_posts_padding',
			[
				'label'     => __( 'Padding', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['direction'] = 'horizontal';

		if ( $settings['autoplay'] === 'yes' ) {
			$swiper_data['autoplay']['delay'] = $settings['duration']['size'];
		} else {
			$swiper_data['autoplay'] = false;
		}

		if ( $settings['pause_on_hover'] === 'yes' ) {
			$swiper_data['pause_on_hover'] = $settings['pause_on_hover'];
		}

		$swiper_data['effect'] = $settings['effect'];

		$swiper_data['loop']       = $settings['loop'];
		$height                    = $settings['auto_height'];
		$swiper_data['autoHeight'] = ( $height === 'yes' ) ? true : false;
		$ele_breakpoints           = Plugin::$instance->breakpoints->get_active_breakpoints();
		$active_devices            = Plugin::$instance->breakpoints->get_active_devices_list();
		$active_breakpoints        = array_keys( $ele_breakpoints );
		$break_value               = [];
		foreach ( $active_devices as $active_device ) {
			$min_breakpoint                = Plugin::$instance->breakpoints->get_device_min_breakpoint( $active_device );
			$break_value[ $active_device ] = $min_breakpoint;
		}

		if ( $settings['effect'] === 'fade' || $settings['effect'] === 'flip' ) {
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

			foreach ( $active_devices as $break_key => $active_device ) {
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 5 );
							break;
						case 'tablet':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 10 );
							break;
						case 'desktop':
							$swiper_data['spaceBetween']['default'] = intval( $settings['space']['size'] !== '' ? $settings['space']['size'] : 15 );
							break;
					}
				} else {
					$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 15 );
				}
			}
			// SlidesPerView
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 1 );
							break;
						case 'tablet':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 2 );
							break;
						case 'desktop':
							$swiper_data['slidesPerView']['default'] = intval( $settings['slide_per_view'] !== '' ? $settings['slide_per_view'] : 3 );
							break;
					}
				} else {
					$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 2 );
				}
			}

			// SlidesPerGroup
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
							break;
						case 'tablet':
							$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
							break;
						case 'desktop':
							$swiper_data['slidesPerGroup']['default'] = $settings['slides_per_group'] !== '' ? $settings['slides_per_group'] : 1;
							break;
					}
				} else {
					$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
				}
			}
		}

		if ( $settings['ptype'] !== '' ) {
			$swiper_data['ptype'] = $settings['ptype'];
		}
		$swiper_data['breakpoints_value'] = $break_value;
		$clickable                        = $settings['clickable'];
		$swiper_data['clickable']         = isset( $clickable ) ? $clickable : false;
		$swiper_data['navigation']        = $settings['navigation_button'];
		$swiper_data['scrollbar']         = $settings['scrollbar'];

		return $swiper_data;
	}

}
