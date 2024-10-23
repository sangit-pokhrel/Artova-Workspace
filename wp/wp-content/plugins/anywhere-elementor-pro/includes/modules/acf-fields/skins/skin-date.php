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
use DateTime;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as EPlugin;


class Skin_Date extends Skin_Base {

	public function get_id() {
		return 'date';
	}

	public function get_title() {
		return __( 'Date', 'ae-pro' );
	}
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'register_fallback' ] );
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'register_style_controls' ] );
		add_action( 'elementor/element/ae-acf/date_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$date_format            = Aepro::$_helper->ae_get_date_format();
		$date_format['default'] = 'Default';

		$this->add_control(
			'date_format',
			[
				'label'       => __( 'Date format', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => $date_format,
				'default'     => 'F j, Y',
				'description' => '<a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"> Click here</a> for documentation on date and time formatting.',
			]
		);

		$this->add_control(
			'date_custom_format',
			[
				'label'       => __( 'Date Format', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Date Format', 'ae-pro' ),
				'default'     => 'y:m:d',
				'condition'   => [
					$this->get_control_id( 'date_format' ) => 'custom',
				],
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label'   => __( 'HTML Tag', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'   => __( 'H1', 'ae-pro' ),
					'h2'   => __( 'H2', 'ae-pro' ),
					'h3'   => __( 'H3', 'ae-pro' ),
					'h4'   => __( 'H4', 'ae-pro' ),
					'h5'   => __( 'H5', 'ae-pro' ),
					'h6'   => __( 'H6', 'ae-pro' ),
					'div'  => __( 'div', 'ae-pro' ),
					'span' => __( 'span', 'ae-pro' ),
					'p'    => __( 'p', 'ae-pro' ),
				],
				'default' => 'h3',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => __( 'Alignment', 'ae-pro' ),
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
				'selectors' => [
					'{{WRAPPER}} .ae-acf-wrapper' => 'text-align: {{VALUE}};',
				],

			]
		);
	}

	public function register_fallback() {

		if(!$this->load_skin_controls(['date'])){
			return;
		}

		
		$this->register_fallback_controls();
	}

	public function register_style_controls() {

		if(!$this->load_skin_controls(['date'])){
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
				'selector' => '{{WRAPPER}} .date',
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
					'{{WRAPPER}} .date' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .date' => 'background:{{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .date',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .date' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .date',
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
					'{{WRAPPER}} .date:hover' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .date:hover' => 'background:{{VALUE}}',
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
					'{{WRAPPER}} .date:hover' => 'border-color:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'border_radius_hover',
			[
				'label'     => __( 'Border Radius', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .date:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'hover_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .date:hover',
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
					'{{WRAPPER}} .date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->add_responsive_control(
			'margin',
			[
				'label'     => __( 'Margin', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->end_controls_section();
	}

	public function register_fallback_style() {
		if(!$this->load_skin_controls(['date'])){
			return;
		}
		$this->fallback_style_controls();
	}


	public function render() {
		$settings    = $this->parent->get_settings_for_display();
		$field_args  = [
			'field_type'   => $settings['field_type'],
			'is_sub_field' => $settings['is_sub_field'],
		];
		$date_format = $this->get_instance_value( 'date_format' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $date_format == 'default' ) {
			$field_args['acf_format'] = 1;
		} else {
			$field_args['acf_format'] = 0;
		}
		
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
		$date = AcfMaster::instance()->get_field_value( $field_args );

		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'preview_fallback' ) == 'yes' ) {
				$this->render_fallback_content( $settings );
			}
		}
		if ( $date === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'enable_fallback' ) != 'yes' ) {
				return;
			} else {
				$this->render_fallback_content( $settings );
				return;
			}
		}

		if ( $this->get_instance_value( 'date_format' ) === 'custom' ) {
			$format = $this->get_instance_value( 'date_custom_format' );
		} elseif ( $this->get_instance_value( 'date_format' ) === 'default' ) {
			$format = $date;
		} else {
			$format = $this->get_instance_value( 'date_format' );
		}

		$date = str_replace('/', '-', $date);
		$custom_field_date = date_i18n( $format, strtotime( $date ) );
		$html_tag          = $this->get_instance_value( 'html_tag' );
		$class             = 'date';
		?>
			<div class="ae-acf-wrapper">
				<?php echo sprintf( '<%1$s class="%2$s">%3$s</%1$s>', $html_tag, $class, $custom_field_date ); ?>
			</div>
		<?php
	}
}
