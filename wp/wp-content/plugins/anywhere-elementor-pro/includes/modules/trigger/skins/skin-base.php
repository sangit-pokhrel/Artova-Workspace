<?php
namespace Aepro\Modules\Trigger\Skins;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Helper;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		add_action( 'elementor/element/ae-trigger/section_triggers/after_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_style_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'General', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_align',
			[
				'label'     => esc_html__( 'Justify', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'start'         => esc_html__( 'Left', 'ae-pro' ),
					'center'        => esc_html__( 'Center', 'ae-pro' ),
					'end'           => esc_html__( 'Right', 'ae-pro' ),
					'space-between' => esc_html__( 'Space Between', 'ae-pro' ),
					'space-around'  => esc_html__( 'Space Around', 'ae-pro' ),
					'space-evenly'  => esc_html__( 'Space Evenly', 'ae-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}}.ae-trigger-layout-inline .ae-trigger-container' => 'display: flex; justify-content: {{VALUE}};',
					'{{WRAPPER}}.ae-trigger-layout-traditional .ae-trigger-container' => 'display: flex; align-items: {{VALUE}};',
				],
				'default'   => 'center',
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'          => esc_html__( 'Width', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => [
					'%' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
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
				'selectors'      => [
					'{{WRAPPER}} .ae-trigger-container' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'background',
				'label'    => esc_html__( 'Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-trigger-container',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .ae-trigger-container',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-trigger-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .ae-trigger-container',
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label'      => esc_html__( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-trigger-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_trigger',
			[
				'label' => esc_html__( 'Trigger', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'trigger_icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-trigger-inner .ae-trigger-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'     => esc_html__( 'Item Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.ae-trigger-layout-inline .ae-triggers:not(:first-child)' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-trigger-layout-inline .ae-triggers:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-trigger-layout-traditional .ae-triggers:not(:first-child)' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-trigger-layout-traditional .ae-triggers:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					$this->get_control_id( 'item_align' ) => [ 'start', 'center', 'end' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'trigger_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .ae-trigger',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'trigger_text_shadow',
				'selector' => '{{WRAPPER}} .ae-trigger-inner',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_trigger_normal',
			[
				'label' => esc_html__( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'trigger_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-trigger-inner' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'trigger_background',
				'label'    => esc_html__( 'Background', 'ae-pro' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .ae-trigger-inner',
			]
		);

		$this->add_control(
			'trigger_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-trigger-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'trigger_box_shadow',
				'selector' => '{{WRAPPER}} .ae-trigger-inner',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_hover',
			[
				'label' => esc_html__( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'trigger_text_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-trigger-inner:hover, {{WRAPPER}} .ae-trigger-inner:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}} .ae-trigger-inner:hover svg, {{WRAPPER}} .ae-trigger-inner:focus svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'trigger_background_hover',
				'label'          => esc_html__( 'Background', 'ae-pro' ),
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'selector'       => '{{WRAPPER}} .ae-trigger-inner:hover, {{WRAPPER}} .ae-trigger-inner:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'trigger_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					$this->get_control_id( 'border_border!' ) => '',
				],
				'selectors' => [
					'.ae-trigger-inner, {{WRAPPER}} .ae-trigger-inner:hover, {{WRAPPER}} .ae-trigger-inner:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'trigger_border_radius_hover',
			[
				'label'      => esc_html__( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-trigger-inner:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'trigger_box_shadow_hover',
				'selector' => '{{WRAPPER}} .ae-trigger-inner:hover',
			]
		);

		$this->add_control(
			'trigger_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'ae-pro' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_active',
			[
				'label' => esc_html__( 'Active', 'ae-pro' ),
			]
		);

		$this->add_control(
			'trigger_text_active_color',
			[
				'label'     => esc_html__( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .active .ae-trigger-inner' => 'color: {{VALUE}};',
					'{{WRAPPER}} .active .ae-trigger-inner svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'trigger_background_active',
				'label'          => esc_html__( 'Background', 'ae-pro' ),
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'selector'       => '{{WRAPPER}} .active .ae-trigger-inner',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'trigger_active_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					$this->get_control_id( 'border_border!' ) => '',
				],
				'selectors' => [
					'{{WRAPPER}} .active .ae-trigger-inner' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'trigger_border_radius_active',
			[
				'label'      => esc_html__( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .active .ae-trigger-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'trigger_box_shadow_active',
				'selector' => '{{WRAPPER}} .active .ae-trigger-inner',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'trigger_border',
				'selector'  => '{{WRAPPER}} .ae-trigger-inner',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'trigger_text_padding',
			[
				'label'      => esc_html__( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-trigger-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
				'default'    => [
					'top'    => '12',
					'right'  => '24',
					'bottom' => '12',
					'left'   => '24',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;
	}

	public function render_trigger() {
		$settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute( 'trigger', 'class', 'ae-trigger' );
		$this->parent->add_render_attribute( 'trigger', 'role', 'button' );
		?>
		<div class="ae-trigger-container">
			<?php
			foreach ( $settings['triggers'] as $trigger ) {

				if ( ! empty( $trigger['size'] ) ) {
					$this->parent->add_render_attribute( 'trigger', 'class', 'trigger-size-' . $trigger['size'] );
				}

				if ( $this->get_instance_value( 'trigger_hover_animation' ) ) {
					$this->parent->add_render_attribute( 'trigger', 'class', 'elementor-animation-' . $this->get_instance_value( 'trigger_hover_animation' ) );
				}

				$this->parent->set_render_attribute( 'wrapper', 'class', 'ae-triggers elementor-repeater-item-' . $trigger['_id'] );

				$trigger_data = [
					'trigger_type'   => $trigger['trigger_type'],
					'trigger_action' => $trigger[ 'trigger_action_' . $trigger['trigger_type'] ],
					'selected_icon'  => $trigger['selected_icon'],
					'text'           => $trigger['text'],
				];
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $trigger_data['trigger_action'], [ 'play_pause', 'expand_collapse' ] ) ) {
					if ( $trigger['selected_icon_secondary'] ) {
						$trigger_data['selected_icon_secondary'] = $trigger['selected_icon_secondary'];
					}
					if ( $trigger['secondary_text'] ) {
						$trigger_data['secondary_text'] = $trigger['secondary_text'];
					}
				}

				$this->parent->set_render_attribute( 'trigger', 'title', ucwords( str_replace( '_', ' ', $trigger_data['trigger_action'] ) ) );

				$this->parent->set_render_attribute( 'wrapper', 'data-trigger_settings', wp_json_encode( $trigger_data ) );
				?>
			<div <?php echo $this->parent->get_render_attribute_string( 'wrapper' ); ?>>
				<a href='#' <?php echo $this->parent->get_render_attribute_string( 'trigger' ); ?>>
					<?php $this->render_text( $trigger ); ?>
				</a>
			</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	protected function render_text( $settings ) {

		$this->parent->set_render_attribute(
			[
				'trigger-inner' => [
					'class' => 'ae-trigger-inner',
				],
				'icon-align' => [
					'class' => [
						'ae-trigger-icon',
						'ae-align-icon-' . $settings['icon_align'],
					],
				],
				'text' => [
					'class' => 'ae-trigger-text',
				],
			]
		);

		?>
		<span <?php echo $this->parent->get_render_attribute_string( 'trigger-inner' ); ?>>
			<?php if ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon']['value'] ) ) : ?>
			<span <?php echo $this->parent->get_render_attribute_string( 'icon-align' ); ?>>
				<?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php endif; ?>
			<span <?php echo $this->parent->get_render_attribute_string( 'text' ); ?>><?php echo $settings['text']; ?></span>
		</span>
		<?php
	}
}
