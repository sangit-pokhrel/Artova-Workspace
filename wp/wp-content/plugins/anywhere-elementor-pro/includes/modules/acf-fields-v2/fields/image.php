<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;

class ACF_Image {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$image_id       = $widget->get_raw_acf_field_value($settings);

		$fallback_image = $settings['fallback_image'];
		$is_fallback    = $settings['fallback_image'];

		if ( is_array( $image_id ) ) {
			$image_id = $image_id['id'];
		} else {
			if ( ! is_numeric( $image_id ) ) {
				$image_id = attachment_url_to_postid( $image_id );
			}
		}

		if ( empty( $image_id ) && ! empty( $fallback_image['id'] ) ) {
			$image_id = $fallback_image['id'];
		}

		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}

		if ( ! is_numeric( $image_id ) || empty( $image_id ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
			}
		}

		if ( isset( $image_id ) && ! empty( $image_id ) ) {

			$image_size       = $settings['image_size_size'];
			$title            = get_the_title( $image_id );
			$alt_text         = '';
			$alt_text_type    = $settings['alt_text'];
			$alt_custom_field = $settings['custom_field_alt_text'];
			$field_args = $widget->get_field_args($settings);
			switch ( $alt_text_type ) {
				case 'default':
					$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					break;
				case 'static':
					$alt_text = $settings['static_alt_text'];
					break;
				case 'dynamic_text':
					$field_args['field_name'] = $alt_custom_field;
					$alt_text                 = AcfMaster::instance()->get_field_value( $field_args );

					break;
				case 'post':
					$alt_text = get_the_title();
			}

			if ( $settings['enable_image_link'] === 'yes' ) {
				// Get Link
				$url_type = $settings['url_type'];
				$url      = '';

				switch ( $url_type ) {

					case 'static':
						$url = $settings['static_url'];
						break;

					case 'post':
						$curr_post = Aepro::$_helper->get_demo_post_data();
						if ( isset( $curr_post ) && isset( $curr_post->ID ) ) {
							$url = get_permalink( $curr_post->ID );
						}
						break;

					case 'dynamic_url':
						$custom_field = $settings['custom_field_url'];

						if ( $custom_field !== '' ) {

							$field_args['field_name'] = $custom_field;
							$url                      = AcfMaster::instance()->get_field_value( $field_args );
						}
						break;

					case 'media':
						$url = wp_get_attachment_url( $image_id );
						break;

				}

				$widget->add_render_attribute( 'anchor', 'href', $url );

				$widget->add_render_attribute( 'anchor', [ 'data-elementor-open-lightbox' => $settings['open_lightbox'] ] );

				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					$widget->add_render_attribute( 'anchor', [ 'class' => 'elementor-clickable' ] );
				}

				$new_tab = $settings['image_new_tab'];
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $new_tab == 1 ) {
					$widget->add_render_attribute( 'anchor', 'target', '_blank' );
				}

				$no_follow = $settings['image_nofollow'];
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $no_follow == 1 ) {
					$widget->add_render_attribute( 'anchor', 'rel', 'nofollow' );
				}

				$enable_download = $settings['enable_image_download'];
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $enable_download == 1 ) {
					$widget->add_render_attribute( 'anchor', 'download', 'download' );
				}
			}
			$widget->add_render_attribute( 'image_wrapper', 'class', 'ae_acf_image_wrapper' );
			if ( $settings['enable_image_ratio'] === 'yes' ) {
				$widget->add_render_attribute( 'image_wrapper', 'class', 'ae_acf_image_ratio_yes' );
			}
			if ( empty( $alt_text ) ) {
				$alt_text = $title;
			}
			?>
		<div <?php echo $widget->get_render_attribute_string( 'image_wrapper' ); ?>>
			<?php
			if ( $settings['enable_image_link'] === 'yes' ) {
				?>
			<a <?php echo $widget->get_render_attribute_string( 'anchor' ); ?>>
				<?php
			}
			if ( $settings['enable_image_ratio'] === 'yes' ) {
				?>
			<div class="ae_acf_image_block">
			<?php } ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				$image_size,
				false,
				[
					'title' => $title,
					'alt'   => $alt_text,
				]
			);
			?>
			<?php if ( $settings['enable_image_ratio'] === 'yes' ) { ?>
			</div>
		<?php } ?>
				<div class="ae-acf-overlay-block">
					<div class="ae-acf-overlay"></div>
					<i class="<?php echo $settings['overlay_icon']; ?>"></i>
				</div>
			<?php if ( $settings['enable_image_link'] === 'yes' ) { ?>
			</a>
				<?php
			}
			?>
		</div>
			<?php
		}

	}

	public static function register_controls( $widget ) {

		$widget->add_control(
			'alt_text',
			[
				'label'   => __( 'Alt Text', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'default'      => __( 'Default', 'ae-pro' ),
					'static'       => __( 'Static Text', 'ae-pro' ),
					'post'         => __( 'Post Title', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'default',
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_control(
			'static_alt_text',
			[
				'label'     => __( 'Static Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'http://', 'ae-pro' ),
				'condition' => [
					'field_type' => 'image',
					'alt_text' => 'static',
				],
			]
		);

		$widget->add_control(
			'custom_field_alt_text',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'image',
					'alt_text' => 'dynamic_text',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'image_size',
				'exclude' => [ 'custom' ],
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_control(
			'enable_image_ratio',
			[
				'label'        => __( 'Enable Image Ratio', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_responsive_control(
			'image_ratio',
			[
				'label'          => __( 'Image Ratio', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'size' => 0.66,
				],
				'tablet_default' => [
					'size' => '',
				],
				'mobile_default' => [
					'size' => 0.5,
				],
				'range'          => [
					'px' => [
						'min'  => 0.1,
						'max'  => 2,
						'step' => 0.01,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae_acf_image_wrapper.ae_acf_image_ratio_yes .ae_acf_image_block' => 'padding-bottom: calc( {{SIZE}} * 100% );',
				],
				'condition'      => [
					'field_type' => 'image',
					'enable_image_ratio' => 'yes',
				],
			]
		);

		$widget->add_control(
			'enable_image_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'no'           => __( 'No', 'ae-pro' ),
				'yes'          => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => __( 'no', 'ae-pro' ),
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_control(
			'url_type',
			[
				'label'     => __( 'Links To', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'media'       => __( 'Full Image', 'ae-pro' ),
					'static'      => __( 'Static URL', 'ae-pro' ),
					'post'        => __( 'Post URL', 'ae-pro' ),
					'dynamic_url' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'static',
				'condition' => [
					'field_type' => 'image',
					'enable_image_link' => 'yes',
				],
			]
		);

		$widget->add_control(
			'static_url',
			[
				'label'     => __( 'Static URL', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'http://', 'ae-pro' ),
				'condition' => [
					'field_type' => 'image',
					'url_type'    => 'static',
					'enable_image_link' => 'yes',

				],
			]
		);

		$widget->add_control(
			'custom_field_url',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'image',
					'url_type'    => 'dynamic_url',
					'enable_image_link' => 'yes',
				],
			]
		);

		$widget->add_control(
			'open_lightbox',
			[
				'label'     => __( 'Lightbox', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no',
				'options'   => [
					'default' => __( 'Default', 'ae-pro' ),
					'yes'     => __( 'Yes', 'ae-pro' ),
					'no'      => __( 'No', 'ae-pro' ),
				],
				'condition' => [
					'field_type' => 'image',
					'url_type'    => 'media',
					'enable_image_link' => 'yes',
				],
			]
		);

		$widget->add_control(
			'image_new_tab',
			[
				'label'        => __( 'Open in new tab', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition'    => [
					'field_type' => 'image',
					'enable_image_link' => 'yes',
				],
			]
		);

		$widget->add_control(
			'enable_image_download',
			[
				'label'        => __( 'Enable Download', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition'    => [
					'field_type' => 'image',
				],
			]
		);

		$widget->add_control(
			'image_nofollow',
			[
				'label'        => __( 'Add nofollow', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition'    => [
					'field_type' => 'image',
					'enable_image_link' => 'yes',
				],
			]
		);

		$widget->add_control(
			'show_overlay',
			[
				'label'        => __( 'Show Overlay', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'hover'  => __( 'On Hover', 'ae-pro' ),
					'always' => __( 'Always', 'ae-pro' ),
					'never'  => __( 'Never', 'ae-pro' ),
				],
				'default'      => 'never',
				'prefix_class' => 'overlay-',
				'selectors'    => [
					'{{WRAPPER}}.overlay-always .ae-acf-overlay-block' => 'display: block;',
					'{{WRAPPER}}.overlay-hover .ae_acf_image_wrapper:hover .ae-acf-overlay-block' => 'display: block;',
				],
				'condition'    => [
					'field_type' => 'image',
				],
			]
		);

		$widget->add_control(
			'overlay_icon',
			[
				'label'       => __( 'Overlay Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => 'fa fa-link',
				'condition'    => [
					'field_type' => 'image',
				],
			]
		);
	}

	public static function register_style_controls( $widget ) {
		$widget->start_controls_section(
			'section_style_image',
			[
				'label' => __( 'Image', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_responsive_control(
			'width',
			[
				'label'          => __( 'Width', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
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
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$widget->add_responsive_control(
			'space',
			[
				'label'          => __( 'Max Width', 'ae-pro' ) . ' (%)',
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%' ],
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$widget->add_control(
			'separator_panel_style',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$widget->start_controls_tabs( 'image_effects' );

		$widget->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$widget->add_control(
			'opacity',
			[
				'label'     => __( 'Opacity', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .ae_acf_image_wrapper img',
			]
		);

		$widget->end_controls_tab();

		$widget->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$widget->add_control(
			'opacity_hover',
			[
				'label'     => __( 'Opacity', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper:hover img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .ae_acf_image_wrapper:hover img',
			]
		);

		$widget->add_control(
			'background_hover_transition',
			[
				'label'     => __( 'Transition Duration', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'transition-duration: {{SIZE}}s',
				],
			]
		);

		$widget->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'ae-pro' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .ae_acf_image_wrapper img',
				'separator' => 'before',
			]
		);

		$widget->add_responsive_control(
			'image_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .ae_acf_image_wrapper img',
			]
		);

		$widget->add_control(
			'overlay_color',
			[
				'label'     => __( 'Overlay Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-acf-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$widget->add_control(
			'blend_mode',
			[
				'label'     => __( 'Blend Mode', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => __( 'Normal', 'ae-pro' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper .ae-acf-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		/* $widget->add_control(
			'blend_mode',
			[
				'label'     => __( 'Blend Mode', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => __( 'Normal', 'ae-pro' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper img' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		); */

		$widget->add_responsive_control(
			'image_padding',
			[
				'label'     => __( 'Padding', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}}  .ae_acf_image_wrapper img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->end_controls_section();
	}

	public static function register_style_icon_controls($widget) {
		$widget->start_controls_section(
			'section_icon_style',
			[
				'label' => __( 'Icon', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .ae-acf-overlay-block i' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->add_control(
			'icon_color_hover',
			[
				'label'     => __( 'Hover', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper:hover .ae-acf-overlay-block i' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->add_responsive_control(
			'icon_size',
			[
				'label'     => __( 'Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 20,
				],
				'range'     => [
					'px' => [
						'min' => 6,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-overlay-block  i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$widget->end_controls_section();
	}

	public static function register_style_overlay_controls( $widget ) {
		$widget->start_controls_section(
			'section_overlay_style',
			[
				'label' => __( 'Overlay', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'field_type' => 'image'
				]
			]
		);

		$widget->start_controls_tabs( 'overlay_style' );

		$widget->start_controls_tab( 'overlay_style_default', [ 'label' => __( 'Default', 'ae-pro' ) ] );

		$widget->add_control(
			'overlay_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-acf-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->start_controls_tab( 'overlay_style_hover', [ 'label' => __( 'Hover', 'ae-pro' ) ] );

		$widget->add_control(
			'overlay_color_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae_acf_image_wrapper:hover .ae-acf-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->end_controls_section();
	}
	
	public function get_file_data( $file ) {

		$file_data = false;

		// Get attachemnt info
		if ( is_numeric( $file ) ) {
			$file_data = acf_get_attachment( $file );
		}

		return $file_data;
	}

}