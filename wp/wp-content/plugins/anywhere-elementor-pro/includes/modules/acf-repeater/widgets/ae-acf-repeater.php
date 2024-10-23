<?php

namespace Aepro\Modules\AcfRepeater\Widgets;

use Aepro\Aepro;
use Aepro\Modules\AcfRepeater\Skins;

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
use Aepro\Classes\CacheManager;
use Aepro\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AeAcfRepeater extends Widget_Base {

	protected $_access_level = 2;

	public function get_name() {

		return 'ae-acf-repeater';
	}

	public function is_enabled() {

		if ( AE_ACF_PRO ) {
			return true;
		}

		return false;
	}

	public function get_title() {

		return __( 'AE - ACF Repeater', 'ae-pro' );
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
			'repeater',
			'carousel',
			'grid',
			'accordion',
		];
	}

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Tabs( $this ) );
		$this->add_skin( new Skins\Skin_Accordion( $this ) );
		$this->add_skin( new Skins\Skin_List( $this ) );
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		// Register Controls

		$this->setting_controls();

		$this->layout_controls();

		$this->pagination_controls();

		$this->carousel_controls();

		$this->get_widget_title_controls();

		// Register Styles

		$this->layout_styles();

		$this->pagination_style_controls();

		$this->load_more_button_style_controls();

		$this->carousel_styles();

		$this->get_widget_title_style_controls();
	}

	protected function render() {
		if ( $this->is_debug_on() ) {
			return;
		}

		$settings = $this->get_settings();
		if ( ! isset( $settings['template'] ) || empty( $settings['template'] ) || get_post_status( $settings['template'] ) !== 'publish' ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' ) ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Repeater > Block Layout", 'ae-pro' ) ) );
			}
		} else {
			$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );
			$this->generate_output( $settings );
		}
	}

	public function generate_output( $settings, $with_wrapper = true ) {
		$masonry = $settings['masonry_grid'];
		$template = $settings['template'];
		$number_of_items = 0;
		$data_settings = [];
		if ( $settings['layout_mode'] === 'carousel' ) {
			$pagination_type       = $settings['ptype'];
			$navigation_button     = $settings['navigation_button'];
			$scrollbar             = $settings['scrollbar'];
			$arrows_layout         = $settings['arrows_layout'];
			$settings['direction'] = 'horizontal';
			$swiper_data           = $this->get_swiper_data( $settings );
			$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper' );
			if ( $settings['layout_mode'] === 'carousel' ) {
				$ae_slider_id = wp_rand( 0, 99999 );
				$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-slider-id-' . $ae_slider_id );
			}
			$this->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );
			/*-- Carousel */
		}
		$this->add_render_attribute( 'acf-repeater-wrapper', 'class', 'ae-acf-repeater-wrapper' );
		$this->add_render_attribute( 'acf-repeater-inner', 'class', 'ae-acf-repeater-inner' );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'data-pid', get_the_ID() );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'data-wid', $this->get_id() );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-acf-repeater-widget-wrapper' );
		if ( $settings['layout_mode'] === 'carousel' ) {
			$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-carousel-yes' );
			if ( ( ! isset( $arrow_horizontal_position ) || $arrow_horizontal_position !== 'center' ) && $arrows_layout === 'outside' ) {
				$settings['arrow_horizontal_position'] = 'center';
			} else {
				$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-hpos-' . $settings['arrow_horizontal_position'] );
				$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-vpos-' . $settings['arrow_vertical_position'] );
			}
		} else {
			$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-masonry-' . $masonry );
		}
		$this->add_render_attribute( 'acf-repeater-item', 'class', 'ae-acf-repeater-item' );
		$with_css = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}
		$post_data     = Aepro::$_helper->get_demo_post_data();
		$repeater_data = Aepro::$_helper->get_repeater_data( $settings, $post_data->ID );
		?>
		<?php
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() && isset( $repeater_data['parents'] ) ) {
			$loop = acf_get_loop();
			if ( $loop === false ) {
				foreach ( $repeater_data['parents'] as $parent ) {
					have_rows( $parent, $repeater_data['repeater_type'] );
					the_row();
				}
			}
		}
		if ( ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) || $settings['no_posts_message'] ) {
			$this->get_widget_title_html();
		}
		if ( $settings['layout_mode'] === 'carousel' ) {
			$swiper_class = '';
		$swiper_class = Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' ) ? 'swiper' : 'swiper-container';
			$this->add_render_attribute( 'swiper-container', 'data-ae-slider-id', $ae_slider_id );
			$this->add_render_attribute( 'swiper-container', 'class', [ 'ae-swiper-container' ] );
			$this->add_render_attribute( 'acf-repeater-wrapper', 'class', [ 'ae-swiper-wrapper', 'swiper-wrapper' ] );
			$this->add_render_attribute( 'acf-repeater-item', 'class', [ 'ae-swiper-slide', 'swiper-slide' ] );
			$this->add_render_attribute( 'acf-repeater-inner', 'class', [ 'ae-swiper-slide-wrapper', 'swiper-slide-wrapper' ] );
		}
		if ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
			Frontend::$_in_repeater_block = true;
			if ( $with_wrapper ) {
				?>
				<div <?php echo $this->get_render_attribute_string( 'acf-repeater-widget-wrapper' ); ?>> 
								<?php
			}
			if ( $settings['layout_mode'] === 'carousel' ) {
				?>
				<div <?php echo $this->get_render_attribute_string( 'outer-wrapper' ); ?> >
					<div <?php echo $this->get_render_attribute_string( 'swiper-container' ); ?> >
			<?php }

				$sg_layout = [];
				if ( in_array( $settings['layout_mode' ], ['smart_grid', 'checker_board'] ) ) {
					$sg_layouts         = $this->smart_grid_layouts();
					$selected_sg_layout = $settings[ 'sg_layout' ];
					$sg_layout          = $sg_layouts[ $selected_sg_layout ]['alternate_layouts'];
				}
				$item_index = 0;
				//Pagination
				if( in_array($settings['layout_mode'], ['grid', 'smart_grid', 'checker_board']) && $settings['grid_pagination'] == 'yes' ){
					$repeater_items = count(get_field($repeater_data['repeater_name'], $repeater_data['repeater_type']));
					$items_per_page = (trim($settings['items_per_page']) != '' ) ? $settings['items_per_page'] : 0 ;
					$number_of_pages = ceil($repeater_items / $items_per_page);
					$item_page = 1;
					$item_page_class = 'page-' . $item_page;
					$pagination_offset = $items_per_page;
				}
				//Pagination
					?>
				<div <?php echo $this->get_render_attribute_string( 'acf-repeater-wrapper' ); ?>>
					<?php
					$seq = 0;
					$current_page = 1;
					while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
						the_row();
						$seq++;
						//Pagination
						if( in_array($settings['layout_mode'], ['grid', 'smart_grid', 'checker_board']) && $settings['grid_pagination'] == 'yes' ){

							// update current page
							if( $item_index == $items_per_page ){
								$item_index = 0;
								$current_page = $current_page + 1;
							}


							$item_classes = ['ae-acf-repeater-item', 'page-'.$current_page];
							
							// need to show only first page on load, so hide items of all other pages
							if($current_page > 1){
								$item_classes[] = 'ae-hide';
							}

							$this->set_render_attribute( 'acf-repeater-item', 'class', $item_classes );
						}
						$template = $this->get_layout( $seq, $settings, $sg_layout );
						//Pagination
						?>
						<div <?php echo $this->get_render_attribute_string( 'acf-repeater-item' ); ?>>
							<div <?php echo $this->get_render_attribute_string( 'acf-repeater-inner' ); ?>>
								<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
								<div class="ae_data elementor elementor-<?php echo esc_html( $template ); ?>">
								<?php } ?>
									<?php echo Plugin::instance()->frontend->get_builder_content( $template, $with_css ); ?>
								<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
								</div>
								<?php } ?>
							</div>
						</div>
					<?php
					//Pagination
					 	$item_index = $item_index + 1;
						//Pagination
						} ?>
				</div>
				<?php if($settings['masonry_grid'] == 'yes'){ ?>
					<div class="grid-gap"></div>
				<?php } ?>
				<?php 
				if(in_array($settings['layout_mode'], ['grid', 'smart_grid', 'checker_board'])){
					//Pagination
					if( $settings['grid_pagination'] == 'yes' ){
						// last value of current_page is the total number of pages
						$this->get_pagination_html($settings, $current_page);
					}
					//Pagination
				}
				?>
			<?php
			Frontend::$_in_repeater_block = false;
			?>
			<?php if ( $settings['layout_mode'] === 'carousel' ) { ?>
				<?php if ( $pagination_type !== '' ) { ?>
			<div class = "ae-swiper-pagination swiper-pagination"></div>
		<?php } ?>
				<?php if ( $navigation_button === 'yes' && $arrows_layout === 'inside' ) { ?>
					<?php
					if ( $settings['arrow_horizontal_position'] !== 'center' ) {
						;
						?>
				<div class="ae-swiper-button-wrapper swiper-button-wrapper">
				<?php } ?>
			<div class = "ae-swiper-button-prev swiper-button-prev">
					<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-up"></i>
				<?php } else { ?>
						<?php
						if ( is_rtl() ) {
							Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
						} else {
							Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
						}
						?>
				<?php } ?>
			</div>
			<div class = "ae-swiper-button-next swiper-button-next">
					<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-down"></i>
				<?php } else { ?>
						<?php
						if ( is_rtl() ) {
							Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
						} else {
							Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
						}
						?>
				<?php } ?>
			</div>
					<?php
					if ( $settings['arrow_horizontal_position'] !== 'center' ) {
						;
						?>
				</div>
				<?php } ?>
		<?php } ?>

				<?php if ( $scrollbar === 'yes' ) { ?>
			<div class = "ae-swiper-scrollbar swiper-scrollbar"></div>

		<?php } ?>
		<!-- swiper container closed -->
		</div>
				<?php if ( $navigation_button === 'yes' && $arrows_layout === 'outside' ) { ?>
					<?php
					if ( $settings['arrow_horizontal_position'] !== 'center' ) {
						;
						?>
				<div class="ae-swiper-button-wrapper">
				<?php } ?>
			<div class = "ae-swiper-button-prev swiper-button-prev">
					<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-up"></i>
				<?php } else { ?>
						<?php
						if ( is_rtl() ) {
							Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
						} else {
							Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
						}
						?>
				<?php } ?>
			</div>
			<div class = "ae-swiper-button-next swiper-button-next">
					<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-down"></i>
				<?php } else { ?>
						<?php
						if ( is_rtl() ) {
							Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
						} else {
							Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
						}
						?>
				<?php } ?>
			</div>
					<?php
					if ( $settings['arrow_horizontal_position'] !== 'center' ) {
						;
						?>
				</div>
				<?php } ?>
		<?php } ?>
		<!-- outer wrapper closed -->
		</div>
		<?php } ?>
			<?php if ( $with_wrapper ) { ?>
			</div>
		<?php } ?>
			<?php
		} else {
			if ( empty( $settings['no_posts_message'] ) ) {
				return;
			} else {
				?>
			<div class="ae-no-posts">
				<?php
				echo do_shortcode( $settings['no_posts_message'] );
				$settings['layout_mode'] = '';
				?>
			</div>
			<?php } ?>
			<?php
		}
	}

	public function setting_controls() {
		$this->start_controls_section(
			'repeater_section',
			[
				'label' => __( 'Repeater', 'ae-pro' ),
			]
		);

		$block_layouts[''] = 'Select Template';
		$block_layouts     = $block_layouts + Aepro::$_helper->ae_acf_repeater_layouts();

		$this->add_control(
			'template',
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( 'Know more about layouts <a href="https://wpvibes.link/go/feature-creating-repeater-block-layout" target="_blank">Click Here</a>', 'ae-pro' ),
				'condition' => [
					'_skin!' => [ 'list' ],
				],
			]
		);

		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$this->add_control(
			'acf_repeater_field_name',
			[
				'label'       => __( 'Repeater Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $repeater_fields,
				'placeholder' => __( 'Repeater Field', 'ae-pro' ),
				'default'     => '',
			]
		);

		$this->add_control(
			'repeater_sub_field',
			[
				'label'   => __( 'Sub Fields', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'acf_repeater_field_name',
				'is_repeater_control' => false, // set to true if this control is in the Elementor repeater controls
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'condition'   => [
					'_skin' => [ 'list' ],
				],
			]
		);

		$this->add_control(
			'content_template',
			[
				'label' => __( 'Content Template', 'ae-pro' ),
				'type' => Controls_Manager::TEXTAREA,
				'description' => esc_html__( 'Add text around this tag {{value}} and tag will be replace by the field value.', 'ae-pro' ),
				'condition'   => [
					'_skin' => [ 'list' ],
				],
			]
		);

		$this->add_control(
			'tab_title',
			[
				'label'       => __( 'Tab Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => 'Sub Field name for tab title',
				'condition'   => [
					'_skin' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'tab_layout',
			[
				'label'        => __( 'Layout', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'horizontal' => __( 'Horizontal', 'ae-pro' ),
					'vertical'   => __( 'Vertical', 'ae-pro' ),
				],
				'seperator'    => 'before',
				'prefix_class' => 'ae-acf-repeater-tabs-view-',
				'default'      => 'horizontal',
				'condition'    => [
					'_skin' => 'tabs',
				],

			]
		);

		$this->add_responsive_control(
			'tab_align',
			[
				'label'     => __( 'Tab Align', 'ae-pro' ),
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
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}}.ae-acf-repeater-tabs-view-horizontal .ae-acf-repeater-tabs-wrapper' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'tab_layout' => 'horizontal',
					'_skin'      => 'tabs',
				],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'            => __( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'separator'        => 'before',
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'_skin' => 'accordion',
				],
			]
		);

		$this->add_control(
			'selected_active_icon',
			[
				'label'            => __( 'Active Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon_active',
				'default'          => [
					'value'   => 'fas fa-minus',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'selected_icon[value]!' => '',
					'_skin'                 => 'accordion',
				],
			]
		);

		$this->add_control(
			'title_html_tag',
			[
				'label'     => __( 'Title HTML Tag', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'h6'  => 'H6',
					'div' => 'div',
				],
				'default'   => 'div',
				'separator' => 'before',
				'condition' => [
					'_skin' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'tab_state',
			[
				'label'     => __( 'State on Load', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'       => __( 'Default', 'ae-pro' ),
					'open_specific' => __( 'Open Specific', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin' => [ 'tabs' ],
				],
			]
		);

		$this->add_control(
			'accordion_state',
			[
				'label'              => __( 'State on Load', 'ae-pro' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'default'       => __( 'Default', 'ae-pro' ),
					'all_open'      => __( 'All Open', 'ae-pro' ),
					'all_closed'    => __( 'All Close', 'ae-pro' ),
					'open_specific' => __( 'Open Specific', 'ae-pro' ),
				],
				'frontend_available' => 'true',
				'default'            => 'default',
				'condition'          => [
					'_skin' => [ 'accordion' ],
				],
			]
		);

		$this->add_control(
			'specific_tab',
			[
				'label'      => __( 'Specific Tab', 'ae-pro' ),
				'type'       => Controls_Manager::NUMBER,
				'default'    => '2',
				'min'        => 1,
				'max'        => 100,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[

							'relation' => 'or',
							'terms'    => [
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'tabs',
								],
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'accordion',
								],
							],
						],
						[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'accordion_state',
									'operator' => '==',
									'value'    => 'open_specific',
								],
								[
									'name'     => 'tab_state',
									'operator' => '==',
									'value'    => 'open_specific',
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'enable_url_hashtag',
			[
				'label'        => __( 'Enable Hashtag', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin' => [ 'accordion', 'tabs' ],
				],
			]
		);

		$this->add_control(
			'fragment_type',
			[
				'label'     => __( 'Fragment', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'      => __( 'Default', 'ae-pro' ),
					'custom_field' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin'              => [ 'accordion', 'tabs' ],
					'enable_url_hashtag' => 'yes',
				],
			]
		);

		$this->add_control(
			'fragment_custom_field',
			[
				'label'       => __( 'Fragment Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field', 'ae-pro' ),
				'condition'   => [
					'_skin'              => [ 'accordion', 'tabs' ],
					'enable_url_hashtag' => 'yes',
					'fragment_type'      => 'custom_field',
				],
			]
		);

		$this->add_control(
			'enable_toggle_button',
			[
				'label'        => __( 'Enable Toggle Button', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin' => [ 'accordion' ],
				],
			]
		);

		$this->add_control(
			'expand_button_text',
			[
				'label'     => __( 'Expend Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Expand All', 'ae-pro' ),
				'condition' => [
					'_skin'                => [ 'accordion' ],
					'enable_toggle_button' => 'yes',
				],
			]
		);
		$this->add_control(
			'collapse_button_text',
			[
				'label'     => __( 'Collapse Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Collapse All', 'ae-pro' ),
				'condition' => [
					'_skin'                => [ 'accordion' ],
					'enable_toggle_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_button_separator',
			[
				'label'     => __( 'Separator', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( ' | ', 'ae-pro' ),
				'condition' => [
					'_skin'                => [ 'accordion' ],
					'enable_toggle_button' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'condition' => [
					'_skin!' => [ 'tabs', 'accordion', 'list' ],
				],
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'        => __( 'Layout Mode', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'grid'     => __( 'Grid', 'ae-pro' ),
					'carousel' => __( 'Carousel', 'ae-pro' ),
					'smart_grid'=> __( 'Smart Grid', 'ae-pro' ),
					'checker_board' => __( 'Checker Board', 'ae-pro' ),
				],
				'default'      => 'grid',
				'prefix_class' => 'ae-acf-repeater-layout-',
				'frontend_available' => true,
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'sg_layout',
			[
				'label'        => __( 'Choose Grid Layout', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => $this->smart_grid_layouts(),
				'label_block'  => true,
				'prefix_class' => 'ae-sg-',
				'render_type'  => 'template',
				'default'      => 'layout1',
				'toggle'       => false,
				'condition'    => [
					'layout_mode' => 'smart_grid',
				],
			]
		);
		
		$block_layouts[''] = 'Select Template';
		$block_layouts     = $block_layouts + Aepro::$_helper->ae_acf_repeater_layouts();
		$this->add_control(
			'alt_template',
			[
				'label'       => __( 'Secondary Template', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( 'Know more about layouts <a href="https://wpvibes.link/go/feature-creating-repeater-block-layout" target="_blank">Click Here</a>', 'ae-pro' ),
				'condition'   => [
					'layout_mode' => [ 'smart_grid', 'checker_board' ],
				],
			]
		);

		$this->add_control(
			'masonry_grid',
			[
				'label'        => __( 'Masonry', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'masonry_on'   => __( 'On', 'ae-pro' ),
				'masonry_off'  => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'grid',
				],
				'frontend_available' => true,
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
				'condition'       => [
					'layout_mode' => ['grid', 'checker_board'],
				],
				'selectors'       => [
					'{{WRAPPER}}.ae-acf-repeater-layout-checker_board .ae-acf-repeater-wrapper' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); display:grid',
					'{{WRAPPER}}.ae-acf-repeater-layout-grid .ae-acf-repeater-widget-wrapper:not(.ae-masonry-yes) .ae-acf-repeater-item' => 'width: calc(100%/{{VALUE}})',
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper.ae-masonry-yes .ae-acf-repeater-wrapper' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
				'frontend_available' => true,
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
				'condition' => [
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
				],
				'selectors' => [
					'{{WRAPPER}}.ae-acf-repeater-layout-smart_grid .ae-acf-repeater-wrapper' => 'grid-column-gap:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-acf-repeater-layout-checker_board .ae-acf-repeater-wrapper' => 'grid-column-gap:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-acf-repeater-layout-grid .ae-acf-repeater-widget-wrapper:not(.ae-masonry-yes) .ae-acf-repeater-item' => 'padding-left:{{SIZE}}{{UNIT}}; padding-right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper.ae-masonry-yes .ae-acf-repeater-wrapper' => 'grid-column-gap: {{SIZE}}{{UNIT}}',
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
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}.ae-acf-repeater-layout-smart_grid .ae-acf-repeater-wrapper' => 'grid-row-gap:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-acf-repeater-layout-checker_board .ae-acf-repeater-wrapper' => 'grid-row-gap:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-acf-repeater-layout-grid .ae-acf-repeater-widget-wrapper:not(.ae-masonry-yes) .ae-acf-repeater-item' => 'margin-bottom:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper.ae-masonry-yes .grid-gap' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
				],
			]
		);

		$this->add_responsive_control(
			'carousel_item_row_gap',
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
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-repeater-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'grid_pagination',
			[
				'label'        => __( 'Pagination', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin!'	   => [ 'tabs', 'accordion', 'list' ],
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'no_posts_message',
			[
				'label'     => __( 'No Posts Message', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	public function carousel_controls() {

		$this->start_controls_section(
			'carousel_control',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'condition' => [
					'layout_mode' => 'carousel',
					'_skin!'      => [ 'tabs', 'accordion', 'list' ],
				],
			]
		);

		$this->add_control(
			'image_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
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
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'carousel',
				],
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
					'layout_mode' => 'carousel',
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

	public function pagination_controls() {

		$this->start_controls_section(
			'pagination_controls',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'condition' => [
					'_skin!'	   => [ 'tabs', 'accordion', 'list' ],
					'grid_pagination' => 'yes',
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
				],
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'   => __( 'Pagination', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'numbers' => __( 'Numbers', 'ae-pro' ),
					'prev_next'    => __( 'Prev Next', 'ae-pro' ),
					'numbers_and_prev_next'   => __( 'Numbers + Prev/Next', 'ae-pro' ),
					'load_more_on_click'     => __( 'Load More on Click', 'ae-pro' ),
				],
				'default' => 'numbers_and_prev_next',
			]
		);

		$this->add_control(
			'items_per_page',
			[
				'label'     => __( 'Items Count', 'ae-pro' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'description' => __( 'Leave blank to show all items', 'ae-pro' ),
			]
		);

		$this->add_control(
			'load_more_button_text',
			[
				'label'     => __( 'Button Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load More', 'ae-pro' ),
				'condition' => [
					'pagination_type' => 'load_more_on_click',
				],
			]
		);

		$this->add_control(
			'no_load_more_text',
			[
				'label'     => __( 'No More Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'No More Posts', 'ae-pro' ),
				'condition' => [
					'pagination_type' => 'load_more_on_click',
				],
			]
		);

		$this->add_control(
			'load_more_loader',
			[
				'label'   => __( 'Loader', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'eclipse'  => __( 'Eclipse', 'ae-pro' ),
					'infinity' => __( 'Infinity', 'ae-pro' ),
					'pulse'    => __( 'Pulse', 'ae-pro' ),
					'ripple'   => __( 'Ripple', 'ae-pro' ),
					'spin'     => __( 'Spin', 'ae-pro' ),
				],
				'default' => 'eclipse',
				'condition' => [
					'pagination_type' => 'load_more_on_click',
				],
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'     => __( 'Previous Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( '&laquo; Previous', 'ae-pro' ),
				'condition' => [
					'pagination_type' => ['prev_next', 'numbers_and_prev_next'],
				],
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'     => __( 'Next Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Next &raquo;', 'ae-pro' ),
				'condition' => [
					'pagination_type' => ['prev_next', 'numbers_and_prev_next'],
				],
			]
		);

		$this->add_control(
			'enable_scroll_to_top',
			[
				'label'        => __( 'Enable Scroll To Top', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition' => [
					'pagination_type' => ['prev_next', 'numbers_and_prev_next', 'numbers'],
				],
			]
		);
		
		$this->add_responsive_control(
			'pagination_scroll_top_offset',
			[
				'label'              => __( 'Scroll To Top Offset', 'ae-pro' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => [
					'size' => 0,
				],
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'frontend_available' => true,
				'condition' => [
					'enable_scroll_to_top' => 'yes',
					'pagination_type' => ['prev_next', 'numbers_and_prev_next', 'numbers'],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_align',
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
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .ae-load-more-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function layout_styles() {
		$this->start_controls_section(
			'layout_style',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin!' => [ 'tabs', 'accordion', 'list' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-acf-repeater-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
			]
		);

		$this->end_controls_section();
	}

	public function carousel_styles() {
		$this->start_controls_section(
			'carousel_style',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_mode' => 'carousel',
					'_skin!'      => [ 'tabs', 'accordion', 'list' ],
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
				'label'              => __( 'Horizontal Position', 'ae-pro' ),
				'type'               => Controls_Manager::CHOOSE,
				'label_block'        => false,
				'options'            => [
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
				'default'            => 'center',
				'condition'          => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
				'frontend_available' => true,
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
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper .ae-swiper-container' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper .ae-swiper-outer-wrapper' => 'position: relative',
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

	public function pagination_style_controls() {

		$this->start_controls_section(
			'pagination_style',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'grid_pagination' => 'yes',
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
					'pagination_type' => ['prev_next', 'numbers_and_prev_next', 'numbers'],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'     => __( 'Item Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'margin-left:{{SIZE}}{{UNIT}}; margin-right:{{SIZE}}{{UNIT}};',
				],

			]
		);

		$this->add_responsive_control(
			'pi_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_pagination_styles' );

		$this->start_controls_tab(
			'tab_pagination_style_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'pi_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pi_bg',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'background-color:{{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pagination_style_hover',
			[
				'label' => __( 'Hover/Active', 'ae-pro' ),
			]
		);

		$this->add_control(
			'pi_hover_active_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper .current' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper span:hover' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper a:hover' => 'color:{{VALUE}}',
				],
				'default' => '#a4a4a4'
			]
		);

		$this->add_control(
			'pi_hover_active_bg',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper .current' => 'background-color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper span:hover' => 'background-color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper a:hover' => 'background-color:{{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pi_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->add_control(
			'pi_border_hover_color',
			[
				'label'     => __( 'Border Hover Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *:hover' => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'pi_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'pi_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_box_shadow',
				'label'    => __( 'Box Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->add_control(
			'pagination_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-pagination-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function load_more_button_style_controls() {

		$this->start_controls_section(
			'load_more_button_style',
			[
				'label'     => __( 'Load More Button', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin!'	   => [ 'tabs', 'accordion', 'list' ],
					'grid_pagination' => 'yes',
					'layout_mode' => ['grid', 'smart_grid', 'checker_board'],
					'pagination_type' => ['load_more_on_click'],
				],
			]
		);

		$this->add_control(
			'load_more_heading',
			[
				'label'     => __( 'Load More', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'load_more_button_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .ae-load-more-button',
			]
		);

		$this->start_controls_tabs( 'tabs_load_more_button_styles' );

		$this->start_controls_tab(
			'tab_load_more_button_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'load_more_button_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default' 	=> '',
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_bg',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
					
				],
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'load_more_button_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'default'  => '1px',
				'selector' => '{{WRAPPER}} .ae-load-more-button',
			]
		);

		$this->add_control(
			'load_more_button_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-load-more-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_text_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-load-more-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_text_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-load-more-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_load_more_button_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'load_more_button_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_bg_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_border_color_hover',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_button_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-load-more-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'load_more_last_message_heading',
			[
				'label'     => __( 'No More Text', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'load_more_last_message_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .ae-no-load-more-text',
			]
		);

		$this->add_control(
			'no_load_more_text_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-no-load-more-text' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'no_load_more_text_bg',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-no-load-more-text' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'no_load_more_text_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'default'  => '1px',
				'selector' => '{{WRAPPER}} .ae-no-load-more-text',
			]
		);

		$this->add_control(
			'no_load_more_text_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-no-load-more-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'no_load_more_text_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-no-load-more-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'no_load_more_text_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-no-load-more-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'no_load_more_text_align',
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
				'selectors' => [
					'{{WRAPPER}} .ae-load-more-wrapper .ae-no-load-more-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function smart_grid_layouts() {

		$smart_grid = [

			'layout1' => [
				'title'             => __( 'Layout 1', 'ae-pro' ),
				'icon'              => 'aep aep-sg-1',
				'count'             => 3,
				'alternate_layouts' => [ 1 ],
			],

			'layout2' => [
				'title'             => __( 'Layout 2', 'ae-pro' ),
				'icon'              => 'aep aep-sg-2',
				'alternate_layouts' => [ 2 ],
			],

			'layout3' => [
				'label'             => __( 'Layout 3', 'ae-pro' ),
				'icon'              => 'aep aep-sg-3',
				'alternate_layouts' => [ 1 ],
			],

			'layout4' => [
				'label'             => __( 'Layout 4', 'ae-pro' ),
				'icon'              => 'aep aep-sg-4',
				'alternate_layouts' => [ 1 ],
			],
		];

		return $smart_grid;
	}

	public function get_layout( $seq, $settings, $sg_layout = 0 ) {

		$template          = $settings['template'];
		$alt_template      = $settings['alt_template'];
		$layout_mode     = $settings['layout_mode'];

		switch ( $layout_mode ) {

			case 'smart_grid':
				if ( in_array( $seq, $sg_layout, true ) && $alt_template !== '' ) {
									$template = $alt_template;
				}
				break;

			case 'checker_board':
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $settings['columns'] % 2 != 0 ) {
					//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $seq % 2 == 0 ) {
						$template = $alt_template;
					}
				} else {
					// more complex
					$row = ceil( $seq / $settings['columns'] );
					//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $row % 2 == 0 ) {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $seq % 2 == 0 ) {
							$template = $alt_template;
						}
					} else {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $seq % 2 == 1 ) {
							$template = $alt_template;
						}
					}
				}
		}

		return $template;
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['direction'] = $settings['direction'];

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
		$swiper_data['autoHeight'] = ( $settings['auto_height'] === 'yes' ) ? true : false;

		$active_devices = Plugin::$instance->breakpoints->get_active_devices_list();

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
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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

		$swiper_data['ptype'] = $settings['ptype'];
		if ( $settings['ptype'] !== '' ) {
			if ( $settings['ptype'] === 'progress' ) {
				$swiper_data['ptype'] = 'progressbar';
			}
		}
		$swiper_data['breakpoints_value'] = Aepro::$_helper->get_breakpoints();
		$swiper_data['clickable']         = isset( $settings['clickable'] ) ? $settings['clickable'] : false;
		$swiper_data['navigation']        = $settings['navigation_button'];
		$swiper_data['scrollbar']         = $settings['scrollbar'];

		return $swiper_data;
	}

	public function get_pagination_html( $settings, $number_of_pages ) {
		if($settings['pagination_type'] == 'none'){
			return;
		}
		if($settings['pagination_type'] == 'load_more_on_click'){
			$this->get_load_more_html( $settings, $number_of_pages );
			return;
		}else{
			$pagination = '<div class="ae-pagination-wrapper" data-page-count="' . $number_of_pages . '" data-current-page="1">';
			if( $settings['pagination_type'] == 'prev_next' || $settings['pagination_type'] == 'numbers_and_prev_next' ){
				$pagination = $pagination . '<a href="#" class="prev page-numbers current" disabled="disabled">' . $settings['prev_text'] . '</a>';
			}
			if( $settings['pagination_type'] == 'numbers' || $settings['pagination_type'] == 'numbers_and_prev_next' ){
				for($page = 1; $page<= $number_of_pages; $page++) {
					$page_class = 'page-numbers page';
					$disable_attr = '';
					if($page == 1 ){
						$page_class = $page_class . ' current';
						$disable_attr = 'disabled="disabled"';
					}
					$pagination = $pagination . '<a href="#" class="' . $page_class . '" data-page="' . $page . '" '. $disable_attr .'>' . $page . '</a>';  
				}
			}
			if( $settings['pagination_type'] == 'prev_next' || $settings['pagination_type'] == 'numbers_and_prev_next' ){
				$pagination = $pagination . '<a href="#" class="next page-numbers">' . $settings['next_text'] . '</a>';
			}
			$pagination = $pagination . '</div>'; 
			echo $pagination;
		}
	}

	public function get_load_more_html( $settings, $number_of_pages ) {
		$hide_load_more_status = ' ae-hide';
		if(Plugin::instance()->editor->is_edit_mode() ){
			$hide_load_more_status = '';
		}
		$load_more_loader = $settings['load_more_loader'];
		$load_more = '<div class="ae-load-more-wrapper" data-page-count="' . $number_of_pages . '" data-current-page="1">';
		$load_more = $load_more . '<div class="ae-load-more-loader loader-' .$load_more_loader . ' ' . $hide_load_more_status . '">' . file_get_contents( AE_PRO_PATH . '/includes/assets/lib/infinite-scroll/shapes/' . $load_more_loader . '.svg' ) . '</div>';
		$load_more = $load_more . '<button class="ae-load-more-button">' . $settings['load_more_button_text'] . '</button>';
		$load_more = $load_more . '<div class="ae-no-load-more-text' . $hide_load_more_status . '">' . $settings['no_load_more_text'] . '</div>';
		$load_more = $load_more . '</div>';
		echo $load_more;
	}
}
