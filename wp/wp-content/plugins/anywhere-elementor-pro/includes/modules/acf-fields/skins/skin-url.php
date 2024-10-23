<?php

namespace Aepro\Modules\AcfFields\Skins;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Aepro\Base\Widget_Base;
use Aepro\Classes\AcfMaster;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as EPlugin;



class Skin_Url extends Skin_Base {

	public function get_id() {
		return 'url';
	}

	public function get_title() {
		return __( 'Url', 'ae-pro' );
	}
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {

		parent::_register_controls_actions();
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'register_style_controls' ] );
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'register_fallback' ] );
		add_action( 'elementor/element/ae-acf/url_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->add_control(
			'links_to',
			[
				'label'   => __( 'Links To', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'static'       => __( 'Static Text', 'ae-pro' ),
					'post'         => __( 'Post Title', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'static',
			]
		);

		$this->add_control(
			'static_text',
			[
				'label'     => __( 'Static Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Click Here', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'links_to' ) => 'static',
				],
			]
		);

		$this->add_control(
			'custom_field_text',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					$this->get_control_id( 'links_to' ) => 'dynamic_text',
				],
			]
		);

		$this->add_control(
			'new_tab',
			[
				'label'        => __( 'Open in new tab', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
			]
		);

		$this->add_control(
			'enable_download',
			[
				'label'        => __( 'Enable Download', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
			]
		);

		$this->add_control(
			'nofollow',
			[
				'label'        => __( 'Add nofollow', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
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
						'title' => __( 'Justify', 'ae-pro' ),
						'icon'  => 'fa fa-align-justify',
					],
				],
				'prefix_class' => 'ae-align-',
				'selectors'    => [
					'{{WRAPPER}} .ae-acf-wrapper'         => 'text-align:{{VALUE}}',
					'{{WRAPPER}} .ae-acf-content-wrapper' => 'display:inline-block;',
					'{{WRAPPER}}.ae-align-justify .ae-acf-content-wrapper' => 'width:100%; text-align:center;',
				],
			]
		);
	}
	public function register_fallback() {

		if(!$this->load_skin_controls(['url', 'file', 'image', 'email' ])){
			return;
		}

		$this->register_fallback_controls();
	}

	public function register_fallback_style() {

		if(!$this->load_skin_controls(['url', 'file', 'image', 'email' ])){
			return;
		}
		
		$this->fallback_style_controls();
	}

	public function render() {

		$settings  = $this->parent->get_settings_for_display();
		$link_text = '';

		$field_args = [
			'field_type'   => $settings['field_type'],
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

		$url = AcfMaster::instance()->get_field_value( $field_args );
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'preview_fallback' ) == 'yes' ) {
				$this->render_fallback_content( $settings );
			}
		}
		if ( $url === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'enable_fallback' ) != 'yes' ) {
				return;
			} else {
				$this->render_fallback_content( $settings );
				return;
			}
		}
		$this->parent->add_render_attribute( 'anchor', 'href', $url );
		$this->parent->add_render_attribute( 'anchor', 'class', 'ae-acf-content-wrapper' );

		$new_tab = $this->get_instance_value( 'new_tab' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $new_tab == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'target', '_blank' );
		}
		
		$no_follow = $this->get_instance_value( 'nofollow' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $no_follow == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'rel', 'nofollow' );
		}

		$enable_download = $this->get_instance_value( 'enable_download' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $enable_download == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'download', 'download' );
		}
		// Get Link Text
		$links_to = $this->get_instance_value( 'links_to' );

		switch ( $links_to ) {

			case 'static':
				$link_text = $this->get_instance_value( 'static_text' );
				break;

			case 'post':
				$curr_post = Aepro::$_helper->get_demo_post_data();
				if ( isset( $curr_post ) && isset( $curr_post->ID ) ) {
					$link_text = get_the_title( $curr_post->ID );
				}
				break;

			case 'dynamic_text':
				$custom_field = $this->get_instance_value( 'custom_field_text' );

				if ( $custom_field !== '' ) {

					$field_args['field_name'] = $custom_field;
					$link_text                = AcfMaster::instance()->get_field_value( $field_args );
				}
				break;

		}

		$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );

		if ( $url === '' || is_null( $url ) ) {
			$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-hide' );
		}
		?>

		<div <?php echo $this->parent->get_render_attribute_string( 'wrapper' ); ?>>
			<a <?php echo $this->parent->get_render_attribute_string( 'anchor' ); ?>><?php echo esc_html( $link_text ); ?></a>
		</div>

		<?php
	}

	public function register_style_controls() {

		if(!$this->load_skin_controls(['url', 'file', 'image', 'email' ])){
			return;
		}

		$this->start_controls_section(
			'general-style',
			[
				'label' => __( 'General', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} a',
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
					'{{WRAPPER}} a' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a' => 'background:{{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} a',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} a   ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} span',
			]
		);

		$this->end_controls_tab();

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
					'{{WRAPPER}} a:hover' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a:hover' => 'background:{{VALUE}}',
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
					'{{WRAPPER}} a:hover' => 'border-color:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'border_radius_hover',
			[
				'label'     => __( 'Border Radius', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} a:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'hover_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} a:hover',
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
					'{{WRAPPER}} a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->add_responsive_control(
			'margin',
			[
				'label'     => __( 'Margin', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->end_controls_section();
	}

}
