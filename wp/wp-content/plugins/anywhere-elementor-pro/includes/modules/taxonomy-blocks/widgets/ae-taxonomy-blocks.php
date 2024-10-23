<?php

namespace Aepro\Modules\TaxonomyBlocks\Widgets;

use Aepro\Aepro;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Helper;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Plugin;
use ElementorPro\Modules\Woocommerce\Widgets\Elements;
use WP_Query;
use Aepro\Post_Helper;
use Aepro\Modules\TaxonomyBlocks\Skins;


class AeTaxonomyBlocks extends Widget_Base {

	protected $_access_level = 1;

	public function get_name() {
		return 'ae-taxonomy-blocks';
	}

	public function get_title() {
		return __( 'AE - Taxonomy Blocks', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-post-list';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'taxonomy',
			'term',
			'category',
			'blocks',
			'loop',
			'query',
			'grid',
			'carousel',
			'custom taxonomy',
		];
	}

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Classic( $this ) );
		$this->add_skin( new Skins\Skin_Card( $this ) );
		$this->add_skin( new Skins\Skin_List( $this ) );
		$this->add_skin( new Skins\Skin_Term_Post_Loop( $this ) );
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

	public function ae_taxonomy_terms( $taxonomy ) {

		$parent = '0';
		$terms  = [];
		if ( $taxonomy === 'child_of_current' ) {
			if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
				$preview_term = Aepro::$_helper->get_preview_term_data();
				if ( isset( $preview_term['prev_term_id'] ) ) {
					$taxonomy = $preview_term['taxonomy'];
					$parent   = $preview_term['prev_term_id'];
				}
			} else {
				// get child of current term
				$current_term = get_queried_object();
				if ( ! is_archive() || is_post_type_archive() ) {
					return;
				}
				$taxonomy = $current_term->taxonomy;
				$parent   = $current_term->term_id;
			}
		}
		if ( empty( $taxonomy ) ) {
			return $terms;
		}
		$args = [
			'taxonomy'   => $taxonomy,
			'parent'     => $parent,
			'hide_empty' => false,
		];
		if ( $taxonomy !== 'child_of_current' ) {
				unset( $args['parent'] );
		}
		$terms_data = get_terms(
			$args
		);
		foreach ( $terms_data as $term ) {
			$terms[ $term->term_id ] = $term->name;
		}

		return $terms;
	}
	protected function register_controls() {

		$ae_taxonomies                             = Aepro::$_helper->get_rules_taxonomies();
		$ae_taxonomies_options                     = $ae_taxonomies;
		$ae_taxonomies_options['child_of_current'] = __( 'Child of Current', 'ae-pro' );

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'grid'     => __( 'Grid', 'ae-pro' ),
					'carousel' => __( 'Carousel', 'ae-pro' ),
				],
				'default'   => 'grid',
				'condition' => [
					'_skin' => [ 'classic', 'card' ],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'ae-pro' ),
			]
		);
		$this->add_control(
			'ae_taxonomy',
			[
				'label'   => __( 'Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $ae_taxonomies_options,
				'default' => key( $ae_taxonomies ),
			]
		);

		$this->start_controls_tabs( 'terms_tabs' );
		$this->start_controls_tab(
			'tab_query_include',
			[
				'label' => __( 'Include', 'ae-pro' ),

			]
		);
		foreach ( $ae_taxonomies_options as $taxonomie => $ae_taxonomie ) {
			$this->add_control(
				$taxonomie . '_inc_terms_ids',
				[
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'label'       => __( $ae_taxonomie . ' Terms', 'ae-pro' ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $this->ae_taxonomy_terms( $taxonomie ),
					'condition'   => [
						'ae_taxonomy' => $taxonomie,
					],
				]
			);
		}
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_query_excludes',
			[
				'label' => __( 'Exclude', 'ae-pro' ),

			]
		);
		foreach ( $ae_taxonomies_options as $taxonomie => $ae_taxonomie ) {
			$this->add_control(
				$taxonomie . '_exc_terms_ids',
				[
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'label'       => __( $ae_taxonomie . ' Terms', 'ae-pro' ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $this->ae_taxonomy_terms( $taxonomie ),
					'condition'   => [
						'ae_taxonomy' => $taxonomie,
					],
				]
			);
		}
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$term_order = [
					'id'          => __( 'ID', 'ae-pro' ),
					'slug'        => __( 'Slug', 'ae-pro' ),
					'name'        => __( 'Name', 'ae-pro' ),
					'description' => __( 'Description', 'ae-pro' ),
					'term_group'  => __( 'Term Group', 'ae-pro' ),
					'menu_order'  => __( 'Menu Order', 'ae-pro' ),
					'count'       => __( 'Count', 'ae-pro' ),
					'none'        => __( 'None', 'ae-pro' ),
		];

		$this->add_control(
			'term_orderby',
			[
				'label'   => __( 'Order By', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $term_order,
				'description' => '"Menu Order" is only for Woocommerce Product Categories',
				'default' => 'name',
			]
		);

		$this->add_control(
			'term_order',
			[
				'label'   => __( 'Order', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'ASC'  => __( 'ASC', 'ae-pro' ),
					'DESC' => __( 'DESC', 'ae-pro' ),
				],
				'default' => 'ASC',
			]
		);

		$this->add_control(
			'show_hide_empty',
			[
				'label'        => __( 'Hide Empty', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'only_top_level',
			[
				'label'        => __( 'Only Top Level', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'ae_taxonomy!' => 'child_of_current',
				],
			]
		);

		$this->add_control(
			'term_query_filter',
			[
				'label'       => __( 'Query Filter', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'separator' => 'before',
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( '<span style="color:red">Danger Ahead!!</span> It is a developer oriented feature. Only use if you know how exaclty WordPress queries and filters works.', 'https://wpvibes.link/go/feature-post-blocks-query-filter', 'Read Instructions' ), 'ae-pro' ),
			]
		);

		$this->end_controls_section();

		$this->register_carousel_controls();

		$this->register_carousel_style_controls();
	}

	public function register_carousel_controls() {

		$this->start_controls_section(
			'section_carousel',
			[
				'label'      => __( 'Carousel', 'ae-pro' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => '_skin',
									'operator' => 'in',
									'value'    => [ 'card', 'classic' ],
								],
								[
									'name'     => 'layout',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'term_post_loop',
								],
								[
									'name'     => 'term_post_loop_content_layout',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
					],
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
					'effect' => [ 'slide', 'coverflow' ],
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
					'effect' => [ 'slide', 'coverflow' ],
				],
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
					'size' => 2000,
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
				'condition'          => [
					'effect' => [ 'slide', 'coverflow' ],
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
				'default'      => 'yes',
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
				'condition'    => [
					'autoplay' => 'yes',
				],
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
			'navigation_arrow_heading',
			[
				'label'     => __( 'Prev/Next Navigaton', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',

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

		$this->end_controls_section();
	}

	public function register_carousel_style_controls() {
		$this->start_controls_section(
			'carousel_style',
			[
				'label'      => __( 'Carousel', 'ae-pro' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => '_skin',
									'operator' => 'in',
									'value'    => [ 'card', 'classic' ],
								],
								[
									'name'     => 'layout',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'term_post_loop',
								],
								[
									'name'     => 'term_post_loop_content_layout',
									'operator' => '==',
									'value'    => 'carousel',
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Arrow', 'ae-pro' ),
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
						'size' => 25,
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
			'carousel_row_gap',
			[
				'label'     => __( 'Offset', 'ae-pro' ),
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
					'{{WRAPPER}} .ae-swiper-outer-wrapper.ae-vpos-bottom .ae-post-widget-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-outer-wrapper.ae-vpos-top .ae-post-widget-wrapper' => 'margin-top:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'_skin'         => 'term_post_loop',
					'arrows_layout' => 'inside',
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
					'{{WRAPPER}} .ae-taxonomy-widget-wrapper .ae-swiper-container' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-taxonomy-widget-wrapper .ae-swiper-outer-wrapper' => 'position: relative',
					'{{WRAPPER}} .ae-swiper-button-prev' => 'left: 0',
					'{{WRAPPER}} .ae-swiper-button-next' => 'right: 0',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'outside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_spacing',
			[
				'label'      => __( 'Arrow Gap', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      =>
					[
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-outer-wrapper.ae-swiper-nav-align-title .ae-swiper-button-prev' => 'margin-right : calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .ae-swiper-outer-wrapper.ae-swiper-nav-align-title .ae-swiper-button-next' => 'margin-left : calc({{SIZE}}{{UNIT}}/2)',

				],
				'condition'  => [
					'_skin'         => 'term_post_loop',
					'arrows_layout' => 'with-heading',
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
				'name'      => 'fraction_typography',
				'label'     => __( 'Typography', 'ae-pro' ),
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
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
			'fraction_offset',
			[
				'label'     => __( 'Top Offset', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-fraction' => 'margin-bottom:{{SIZE}}px;',
				],
				'condition' =>
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
						'ptype' => 'progress',
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
						'ptype' => 'progress',
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
						'ptype' => 'progress',
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
						'ptype' => 'progress',
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
						'ptype' => 'progress',
					],
			]
		);

		$this->end_controls_section();
	}



	protected function render() {
		$settings = $this->get_settings();
		parent::render(); // TODO: Change the autogenerated stub
	}

	protected $_has_template_content = false;

}
