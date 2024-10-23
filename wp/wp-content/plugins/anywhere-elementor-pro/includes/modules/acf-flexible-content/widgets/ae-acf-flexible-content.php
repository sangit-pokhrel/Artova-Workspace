<?php
namespace Aepro\Modules\AcfFlexibleContent\Widgets;

use Aepro\Aepro;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Plugin;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Frontend;
use Elementor\Repeater;
use Aepro\Modules\AcfFlexibleContent\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AeAcfFlexibleContent extends Widget_Base {

	protected $_access_level = 2;

	public function get_name() {

		return 'ae-acf-flexible-content';
	}

	public function is_enabled() {

		if ( AE_ACF_PRO ) {
			return true;
		}

		return false;
	}

	public function get_title() {

		return __( 'AE - ACF Flexible Content', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-post-list';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_script_depends() {

		return [ 'jquery-masonry', 'ae-swiper' ];
	}

	public function get_keywords() {
		return [
			'acf',
			'fields',
			'custom fields',
			'meta',
			'carousel',
			'grid',
			'list',
			'flexible content',
		];
	}

	protected $_has_template_content = false;
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Grid( $this ) );
		$this->add_skin( new Skins\Skin_Carousel( $this ) );
		/* Tab skin moved for next release(for better functionality) */
	}

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->start_controls_section(
			'content',
			[
				'label' => __( 'Content', 'ae-pro' ),
			]
		);
		$groups                  = ['Select Field'];
		$blank_element           = [
			'' => __( 'Select', 'ae-pro' ),
		];
		$post_data               = Aepro::$_helper->get_demo_post_data();
		$acf_groups              = acf_get_field_groups();
		$block_layouts['']       = 'Select Template';
		$block_layouts           = $block_layouts + Aepro::$_helper->ae_acf_repeater_flexible_layouts();
		$flexible_content        = [];
		$flexible_content_layout = [];
		foreach ( $acf_groups as $acf_group ) {
			$group_fields     = acf_get_fields( $acf_group );
			$flexible_content = [];
			foreach ( $group_fields as $field ) {
				$is_on_option_page = false;
				foreach ( $acf_group['location'] as $locations ) {
					foreach ( $locations as $location ) {
						if ( $location['param'] === 'options_page' ) {
							$is_on_option_page = true;
						}
					}
				}
				$only_on_option_page = '';
				if ( $is_on_option_page === true && ( is_array( $acf_group['location'] ) && 1 === count( $acf_group['location'] ) ) ) {
					$only_on_option_page = true;
				}
				$flexible_content_layout_ob = [];
				if ( $field['type'] === 'flexible_content' ) {
					if ( $only_on_option_page ) {
						$flexible_content[ 'option:' . $field['key'] . ':' . $field['name'] ] = 'Option:' . $field['label'];
					} else {
						if ( $is_on_option_page === true ) {
							$flexible_content[ 'option:' . $field['key'] . ':' . $field['name'] ] = 'Option:' . $field['label'];
						}
						$flexible_content[ $field['key'] . ':' . $field['name'] ] = $field['label'];
					}
					$flexible_content_layout_ob = get_field_object( $field['key'] );
					foreach ( $flexible_content_layout_ob['layouts'] as $fc_layout_ob ) {

						if ( $only_on_option_page ) {
							$flexible_content_layout[ 'option:' . $field['key'] . ':' . $flexible_content_layout_ob['name'] ][ $fc_layout_ob['name'] ] = $fc_layout_ob['label'];
						} else {
							if ( $is_on_option_page === true ) {
								$flexible_content_layout[ 'option:' . $field['key'] . ':' . $flexible_content_layout_ob['name'] ][ $fc_layout_ob['name'] ] = $fc_layout_ob['label'];

							}
							$flexible_content_layout[ $field['key'] . ':' . $flexible_content_layout_ob['name'] ][ $fc_layout_ob['name'] ] = $fc_layout_ob['label'];
						}
					}
				}
			}

			if ( ! empty( $flexible_content ) ) {
				$groups[] = [
					'label'   => $acf_group['title'],
					'options' => $flexible_content,
				];
			}
		}

		$flexible_content = array_merge( $blank_element, $flexible_content );

		$this->add_control(
			'flexible_content',
			[
				'label'       => __( 'Flexible Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $groups,
				'description' => __( 'Choose the Flexible Field', 'ae-pro' ),

			]
		);

		foreach ( $flexible_content_layout as $key => $fc_layout ) {
			if ( ! empty( $key ) ) {
				$fc_layout = array_merge( $blank_element, $fc_layout );
				$repeater  = new Repeater();
				$repeater->add_control(
					'flexible_content_layout',
					[
						'label'   => __( 'Layout', 'ae-pro' ),
						'type'    => Controls_Manager::SELECT,
						'options' => $fc_layout,
						'default' => '',
					]
				);
				$repeater->add_control(
					'flexible_content_template',
					[
						'label'   => __( 'Template', 'ae-pro' ),
						'type'    => Controls_Manager::SELECT,
						'options' => $block_layouts,
					]
				);
				$this->add_control(
					$key . '_flexible_layout',
					[
						'label'       => __( 'Manage Layouts & Templates', 'ae-pro' ),
						'type'        => \Elementor\Controls_Manager::REPEATER,
						'fields'      => $repeater->get_controls(),
						'description' => __( 'Add Layouts and assign them templates.', 'ae-pro' ),
						'title_field' => 'Layout - ({{{ flexible_content_layout }}})',
						'condition'   => [
							'flexible_content' => $key,
						],
					]
				);
			}
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'layout_style',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin!' => [ 'accordion', 'tabs' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-acf-fc-item-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-fc-item-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-acf-fc-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-fc-item-inner',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
	}









}
