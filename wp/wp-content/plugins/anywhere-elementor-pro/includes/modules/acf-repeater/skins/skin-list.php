<?php
namespace Aepro\Modules\AcfRepeater\Skins;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Classes\AcfMaster;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager as ElementorIcons_Manager;
use ElementorPro\Modules\AssetsManager\AssetTypes\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Skin_List extends Skin_Base {


	public function get_id() {
		return 'list';
	}

	public function get_title() {
		return __( 'List', 'ae-pro' );
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/ae-acf-repeater/repeater_section/after_section_end', [ $this, 'register_list_controls' ] );
		add_action( 'elementor/element/ae-acf-repeater/repeater_section/after_section_end', [ $this, 'register_list_style_controls' ] );
	}

	public function register_list_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->start_controls_section(
			'section_list',
			[
				'label' => __( 'List Item', 'ae-pro' ),
			]
		);

		$this->add_control(
			'view',
			[
				'label' => esc_html__( 'Layout', 'ae-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'traditional',
				'options' => [
					'traditional' => [
						'title' => esc_html__( 'Default', 'ae-pro' ),
						'icon' => 'eicon-editor-list-ul',
					],
					'inline' => [
						'title' => esc_html__( 'Inline', 'ae-pro' ),
						'icon' => 'eicon-ellipsis-h',
					],
				],
				'render_type' => 'template',
				'prefix_class' => 'ae-list-layout-',
			]
		);

		// add columns control if view is inline. Use slider control with max limit of 10
		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'ae-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 3,
				],
				'condition' => [
					$this->get_control_id('view') => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}}.ae-list-layout-inline .ae-acf-repeater-list' => 'grid-template-columns: repeat({{SIZE}}, 1fr);',
				],
			]
		);

		$this->add_control(
			'list_marker',
			[
				'label'	=> 'List Marker',
				'type'  => Controls_Manager::SELECT,
				'options' => [
					'none' => __( 'None', 'ae-pro' ),
					'circle' => __( 'Circle', 'ae-pro' ),
					'square' => __( 'Square', 'ae-pro' ),
					'disc' => __( 'Disc', 'ae-pro' ),
					'decimal' => __( 'Number', 'ae-pro' ),
					'upper-roman' => __( 'Upper Roman', 'ae-pro' ),
					'lower-roman' => __( 'Lower Roman', 'ae-pro' ),
					'upper-alpha' => __( 'Upper Alpha', 'ae-pro' ),
					'lower-alpha' => __( 'Lower Alpha', 'ae-pro' ),
					'custom' => __( 'Custom', 'ae-pro' ),
				],
				'default' => 'none',
				'render_type' => 'template',
				'selectors' => [
					'{{WRAPPER}} .ae-acf-repeater-list:not(.ae-list-style-icon_svg, .ae-list-style-image)' => 'list-style-type:{{VALUE}};',
				],
			]
		);

		$this->add_control(
			'list_marker_custom',
			[
				'label'     => __( 'Custom', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'image' => [
						'title' => __( 'Image', 'ae-pro' ),
						'icon'  => 'fa fa-image',
					],
					'icon_svg' => [
						'title' => __( 'Icon/SVG', 'ae-pro' ),
						'icon'  => 'eicon-circle',
					],
				],
				'default'   => 'image',
				'condition' => [
					$this->get_control_id( 'list_marker' ) => 'custom',
					'_skin'      => 'list',
				],
			]
		);

		$this->add_control(
			'list_style_type_image',
			[
				'label'     => __( 'List Image', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'selectors' => [
					//'{{WRAPPER}} .ae-acf-repeater-list .ae-acf-repeater-list-item::before' => 'background-image: url("{{URL}}");',
				],
				'render_type' => 'template',
				'condition' => [
					$this->get_control_id( 'list_marker' ) => 'custom',
					$this->get_control_id( 'list_marker_custom' ) => 'image',
				],
			]
		);

		$this->add_control(
			'list_style_type_icon',
			[
				'label'            => __( 'List Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-right',
					'library' => 'fa-solid',
				],
				'condition'        => [
					$this->get_control_id( 'list_marker' ) => 'custom',
					$this->get_control_id( 'list_marker_custom' ) => 'icon_svg',
				],
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

		$this->add_control(
			'fallback_text',
			[
				'label'       => __( 'Fallback', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To be used as default text when there is no data in ACF Field', 'ae-pro' ),
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->parent->get_settings();
		$this->generate_list_output( $settings );
	}

	public function generate_list_output( $settings ) {
		$post_data = Aepro::$_helper->get_demo_post_data();
		$repeater_data = Aepro::$_helper->get_repeater_data( $settings, $post_data->ID );

		if(! have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ){
			return;
		}
		$this->parent->add_render_attribute( 'acf-repeater-wrapper', 
			[ 
				'class'       => 'ae-acf-repeater-wrapper',
			]
		);
		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'acf-repeater-wrapper' ); ?>>
			<?php $counter = 1;
			$list_style = '';
			$ul_class[] = 'ae-acf-repeater-list';
			if( $settings[$this->get_control_id('list_marker')] == 'custom' ){
				$list_style = $settings[$this->get_control_id('list_marker_custom')];	
			}else{
				$list_style = $settings[$this->get_control_id('list_marker')];
			}
			$ul_class[] = 'ae-list-style-' . $list_style;
			$this->parent->add_render_attribute( 'acf-repeater-list', 
				[ 
					'class'       => $ul_class,
				]
			);
			?>
			<ul <?php echo $this->parent->get_render_attribute_string( 'acf-repeater-list' ); ?>>
			<?php
				$this->parent->add_render_attribute( 'acf-repeater-list-item', 
					[ 
						'class'       => 'ae-acf-repeater-list-item',
					]
				);
				while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
					the_row();
					$text = get_sub_field( $settings['repeater_sub_field'], $repeater_data['repeater_type'] );
					if(is_array($text)){
						$text = '';
					}
					$text = $this->get_acf_text_base_value( $this->parent, $settings, $text, $post_data );
					if($text){
					?>
						<li <?php echo $this->parent->get_render_attribute_string( 'acf-repeater-list-item' ); ?>>
						<?php if( $list_style == 'icon_svg' ){
							$icon = $settings[$this->get_control_id('list_style_type_icon')];
							?>
							<span class="ae-list-style-icon"><?php ElementorIcons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?></span>
							<?php
						}elseif ( $list_style == 'image' ){
							$image = $settings[$this->get_control_id('list_style_type_image')];
							?>
							<span class="ae-list-style-icon"><img src="<?php echo $image['url']; ?>" /></span>
							<?php
						} ?>
						<div class="ae-acf-repeater-list-item-content">
						<?php
							echo $text;
						?>
						</div>
						</li>
					<?php
					}
					$counter++;
				}
				?>
			</ul>
		</div>
		<?php
	}

	public function register_list_style_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->start_controls_section(
			'section_list_style',
			[
				'label' => __( 'List Item', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'list_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .ae-acf-repeater-list, {{WRAPPER}} .ae-acf-repeater-list a',
			]
		);

		$this->add_responsive_control(
			'list_item_indent',
			[
				'label'     => __( 'List Item Indent', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-repeater-list-item-content' => 'margin-left: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'list_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'selectors' => [
					// margin botton for list item except last item
					'{{WRAPPER}}.ae-list-layout-traditional .ae-acf-repeater-list-item:not(:last-child)' => 'margin-bottom: {{SIZE}}px;',
					'{{WRAPPER}}.ae-list-layout-inline .ae-acf-repeater-list' => 'row-gap: {{SIZE}}px;',
				],
			]
		);

		// list column gap if view is inline. Use slider control
		$this->add_responsive_control(
			'list_column_gap',
			[
				'label'     => __( 'Column Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'condition' => [
					$this->get_control_id('view') => 'inline'
				],
				'selectors' => [
					'{{WRAPPER}}.ae-list-layout-inline .ae-acf-repeater-list' => 'column-gap: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'list-style-type-heading',
			[
				'label'     => __( 'List Marker', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'list_marker_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-repeater-list-item::marker' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-acf-repeater-list .ae-list-style-icon i' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-acf-repeater-list .ae-list-style-icon svg' => 'fill:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'list_marker_size',
			[
				'label'     => __( 'Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-repeater-list li::marker' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .ae-acf-repeater-list.ae-list-style-image .ae-list-style-icon img' => 'width: {{SIZE}}px; height: auto;',
					'{{WRAPPER}} .ae-acf-repeater-list.ae-list-style-icon_svg .ae-list-style-icon i' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .ae-acf-repeater-list.ae-list-style-icon_svg .ae-list-style-icon svg' => 'width: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_self_vertical_align',
			[
				'label' => esc_html__( 'Vertical Alignment', 'ae-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'ae-pro' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'ae-pro' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'ae-pro' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'center',
				'condition' => [
					$this->get_control_id('list_marker') => 'custom'
				],
				'selectors' => [
					'{{WRAPPER}} li .ae-list-style-icon' => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_vertical_offset',
			[
				'label' => esc_html__( 'Adjust Vertical Position', 'ae-pro' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -15,
						'max' => 15,
					],
					'em' => [
						'min' => -1,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'condition' => [
					$this->get_control_id('list_marker') => 'custom'
				],
				'selectors' => [
					'{{WRAPPER}} li .ae-list-style-icon' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'target_divider',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'list-text-heading',
			[
				'label'     => __( 'List Text', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs( 'list_style_tabs' );

		$this->start_controls_tab(
			'normal_list_style',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'list_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} li, {{WRAPPER}} li a' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} li' => 'background:{{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'list_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} li',
			]
		);

		$this->add_control(
			'list_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'list_box_shadow',
				'label'    => __( 'Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} li',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover_list_style',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'list_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} li:hover, {{WRAPPER}} li:hover a' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_bg_color_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} li:hover' => 'background:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_border_color_hover',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} li:hover' => 'border-color:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'list_border_radius_hover',
			[
				'label'     => __( 'Border Radius', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} li:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'list_hover_box_shadow',
				'label'    => __( 'Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} li:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'list_padding',
			[
				'label'     => __( 'Padding', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_acf_text_base_value($widget, $settings, $data, $post){
		$title_raw = $data;
		$fallback_text  = $settings[$this->get_control_id('fallback_text')];
		$links_to     = $settings[$this->get_control_id('links_to')];
		$link_new_tab = $settings[$this->get_control_id('link_new_tab')];
		$link         = '';

		$content_template = $settings['content_template'];
		if ( $content_template != '' ) {
			$title_raw = $this->findReplace( $content_template, $title_raw, '{{value}}' );
		}

		if ( $title_raw === '' & $fallback_text !== '' ) {
			$title = $fallback_text;
		} else {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings[$this->get_control_id('strip_text')] == 'yes' ) {
				$strip_mode   = $settings[$this->get_control_id('strip_mode')];
				$strip_size   = $settings[$this->get_control_id('strip_size')];
				$strip_append = $settings[$this->get_control_id('strip_append')];
				if ( $strip_mode === 'word' ) {
					$title_raw = wp_trim_words( $title_raw, $strip_size, $strip_append );
				} else {
					$title_raw = Aepro::$_helper->ae_trim_letters( $title_raw, 0, $strip_size, $strip_append );
				}
			}

			$title = $title_raw;
		}

		// Process Content
		$title = self::process_content( $title, $settings );

		if ( $links_to !== '' ) {

			switch ( $links_to ) {

				case 'post':
					$link = get_permalink( $post->ID );
					break;

				case 'static':
					$link = $settings[$this->get_control_id('link_url')];
					break;

				case 'custom_field':
					$acf_repeater_field_name = $settings['acf_repeater_field_name'];
					$link_cf                = $settings[$this->get_control_id('link_cf')];
					$field_type = 'post';
					if ( strpos( $acf_repeater_field_name, 'options' ) !== false ) {
						$field_type = 'option';
					}
					$field_args['is_sub_field'] = 'repeater';
					$field_args['field_type']   = $field_type;
					$field_args['field_name']   = $link_cf;
					$field_args['parent_field'] = $acf_repeater_field_name;
					$link                     = AcfMaster::instance()->get_field_value( $field_args );

					break;

			}
		}

		$widget->add_render_attribute( 'wrapper-class', 'class', 'ae-acf-wrapper' );
		$widget->add_render_attribute( 'title-class', 'class', 'ae-acf-content-wrapper' );

		if ( $link !== '' ) {

			$widget->set_render_attribute( 'anchor', 'title', $title_raw );
			$widget->set_render_attribute( 'anchor', 'href', $link );
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $link_new_tab == 'yes' ) {
				$widget->add_render_attribute( 'anchor', 'target', '_blank' );
			}

			$title_html = '<a ' . $widget->get_render_attribute_string( 'anchor' ) . '>' . $title . '</a>';
		} else {

			$title_html = $title;
		}

		$html = $title_html;
		if($html == ''){
			return '';
		}
		return $html;
	}

	public static function process_content( $content, $settings ) {
		/** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
		$content = apply_filters( 'widget_text', $content, $settings );

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}

	public function findReplace( $content_template, $title_raw, $find ) {
		if ( strpos( $content_template, $find ) !== false ) {
			$title_raw = str_replace( $find, $title_raw, $content_template );
		}

		return $title_raw;
	}
}