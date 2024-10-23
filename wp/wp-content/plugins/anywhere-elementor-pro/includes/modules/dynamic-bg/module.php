<?php
namespace Aepro\Modules\DynamicBg;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Aepro\Aepro_Control_Manager;
use AePro\AePro;
class Module {
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'add_fields' ], 10, 2 );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'add_fields' ], 10, 2 );
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_fields' ], 10, 2 );

		add_action( 'elementor/frontend/element/before_render', [ $this, 'before_section_render' ], 10, 1 );

		add_action( 'elementor/frontend/section/before_render', [ $this, 'before_section_render' ], 10, 1 );
		add_action( 'elementor/frontend/column/before_render', [ $this, 'before_section_render' ], 10, 1 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'before_section_render' ], 10, 1 );

		add_action( 'elementor/section/print_template', [ $this, '_print_template' ], 10, 2 );
		add_action( 'elementor/column/print_template', [ $this, '_print_template' ], 10, 2 );
		add_action( 'elementor/container/print_template', [ $this, '_print_template' ], 10, 2 );
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function _print_template( $template, $widget ) {
		?>
		<?php
		if ( $widget->get_name() !== 'section' && $widget->get_name() !== 'column' && $widget->get_name() !== 'container' ) {
			return $template;
		}
		$old_template = $template;
		ob_start();
		?>
		<#
			acf_field_type = '';
			dynamic_background = {};
			if( typeof settings.ae_acf_field_type != 'undefined' ){
				acf_field_type = settings.ae_acf_field_type;	
				//for flexible
				if(acf_field_type == 'flexible_content'){
					dynamic_background['acf_field_type'] = acf_field_type;
					dynamic_background['show_featured_bg'] = settings.show_featured_bg;
					dynamic_background['bg_source'] = settings.ae_featured_bg_source;
					dynamic_background['bg_flexible_field'] = settings.is_featured_bg_flexible_field;
					dynamic_background['image_size'] = settings.ae_featured_image_size;
					dynamic_background['cf_field_key'] = settings.flexible_fields;
					dynamic_background['flex_sub_field'] = settings['flex-sub-fields'];

					dynamic_background['enable_bg_color'] = settings.enable_bg_color;
					dynamic_background['bg_color_source'] = settings.ae_bg_color_source;
					dynamic_background['bg_color_flexible_field'] = settings.is_featured_bg_color_flexible_field;
					dynamic_background['cf_color_field_key'] = settings.flexible_fields_color;
					dynamic_background['flex_color_sub_field'] = settings['flex-sub-fields-color'];
				}

				//for repeater
				if(acf_field_type == 'repeater'){
					dynamic_background['acf_field_type'] = acf_field_type;
					dynamic_background['show_featured_bg'] = settings.show_featured_bg;
					dynamic_background['bg_source'] = settings.ae_featured_bg_source;
					dynamic_background['image_size'] = settings.ae_featured_image_size;
					dynamic_background['cf_field_key'] = settings.ae_featured_bg_cf_field_key;

					dynamic_background['enable_bg_color'] = settings.enable_bg_color;
					dynamic_background['bg_color_source'] = settings.ae_bg_color_source;
					dynamic_background['bg_color_flexible_field'] = settings.is_featured_bg_color_flexible_field;
					dynamic_background['cf_color_field_key'] = settings.ae_bg_color_cf_field_key;
				}

			}
			if(acf_field_type == 'flexible_content' || acf_field_type == 'repeater'){
				dynamic_bg_strified = JSON.stringify(dynamic_background);
		#>
			<div class="dynamic-background" data-dynamic_bg='{{{ dynamic_bg_strified }}}' style="position:absolute;"></div>
		<#
			}
			#>
		<?php
		$slider_content = ob_get_contents();
		ob_end_clean();
		$template = $slider_content . $old_template;
		return $template;
	}

	public function add_fields( $element, $args ) {
			$post        = get_post();
			$post_type   = get_post_type();
			$render_mode = '';
			$field_type  = '';

		if ( ! empty( $post ) ) {
			if ( $post_type === 'ae_global_templates' ) {
				$render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
				$field_type  = get_post_meta( $post->ID, 'ae_acf_field_type', true );
			}
		}
		if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
			$img_cf_field_key_condition = [
				'ae_featured_bg_source!'         => [ 'post', '' ],
				'show_featured_bg'               => 'yes',
				'is_featured_bg_flexible_field!' => 'yes',
			];
		} else {
			$img_cf_field_key_condition = [
				'ae_featured_bg_source!' => [ 'post', '' ],
				'show_featured_bg'       => 'yes',
			];
		}

			$element->start_controls_section(
				'post_featured_bg',
				[
					'tab'   => Aepro_Control_Manager::TAB_AE_PRO,
					'label' => __( 'Dynamic Background', 'ae-pro' ),
				]
			);

		if ( $field_type !== '' ) {
			$element->add_control(
				'ae_acf_field_type',
				[
					'label'   => 'ACF Field Type',
					'type'    => Controls_Manager::HIDDEN,
					'default' => $field_type,
				]
			);
		}

			$element->add_control(
				'show_featured_bg',
				[
					'label'        => __( 'Background Image', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Show', 'ae-pro' ),
					'label_off'    => __( 'Hide', 'ae-pro' ),
					'return_value' => 'yes',
					'prefix_class' => 'ae-featured-bg-',
				]
			);
			$ae_featured_bg_source['']     = __( 'Select', 'ae-pro' );
			$ae_featured_bg_source['post'] = __( 'Post', 'ae-pro' );
		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {
			$ae_featured_bg_source['custom_field']             = __( 'Post Custom Field', 'ae-pro' );
			$ae_featured_bg_source['term_custom_field']        = __( 'Term Custom Field', 'ae-pro' );
			$ae_featured_bg_source['option_page_custom_field'] = __( 'Option Page Custom Field', 'ae-pro' );
		}

			$element->add_control(
				'ae_featured_bg_source',
				[
					'label'        => __( 'Source', 'ae-pro' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => $ae_featured_bg_source,
					'default'      => 'post',
					'selectors'    => [
						'{{WRAPPER}}' => 'background-size: {{VALUE}};',
					],
					'prefix_class' => 'ae-featured-bg-source-',
					'condition'    => [
						'show_featured_bg' => 'yes',
					],
				]
			);
		if ( $post_type === 'ae_global_templates' ) {
			if ( ( $render_mode !== 'acf_repeater_layout' ) || ( $field_type !== 'flexible_content' && $render_mode === 'acf_repeater_layout' ) ) {
				$element->add_control(
					'is_featured_bg_group_field',
					[
						'label'        => __( 'Is Group Field', 'ae-pro' ),
						'type'         => Controls_Manager::SWITCHER,
						'default'      => '',
						'label_on'     => __( 'Yes', 'ae-pro' ),
						'label_off'    => __( 'No', 'ae-pro' ),
						'return_value' => 'yes',
						'prefix_class' => 'ae-featured-bg-',
						'condition'    => [
							'ae_featured_bg_source!' => [ 'post', '' ],
							'show_featured_bg'       => 'yes',
						],
					]
				);
			}
		} else {
			$element->add_control(
				'is_featured_bg_group_field',
				[
					'label'        => __( 'Is Group Field New', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'prefix_class' => 'ae-featured-bg-',
					'condition'    => [
						'ae_featured_bg_source!' => [ 'post', '' ],
						'show_featured_bg'       => 'yes',
					],
				]
			);
		}

		if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
			$element->add_control(
				'is_featured_bg_flexible_field',
				[
					'label'        => __( 'Is Flexible Field', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'condition'    => [
						'ae_featured_bg_source!' => [ 'post', '', 'term_custom_field' ],
						'show_featured_bg'       => 'yes',
					],
				]
			);
		}

			$element->add_control(
				'ae_featured_bg_cf_parent_key',
				[
					'label'       => __( 'Group Field key', 'ae-pro' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Group Field Key', 'ae-pro' ),
					'default'     => '',
					'condition'   => [
						'ae_featured_bg_source!'     => 'post',
						'show_featured_bg'           => 'yes',
						'is_featured_bg_group_field' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_bg_cf_field_key',
				[
					'label'              => __( 'Field key', 'ae-pro' ),
					'type'               => Controls_Manager::TEXT,
					'placeholder'        => __( 'Custom Field Key', 'ae-pro' ),
					'default'            => '',
					'prefix_class'       => 'ae-feature-bg-custom-field-',
					'condition'          => $img_cf_field_key_condition,
					'frontend_available' => true,
					'render_type'        => 'template',
				]
			);

			$element->add_control(
				'flexible_fields',
				[
					'label'       => __( 'Parent Field', 'ae-pro' ),
					'type'        => Controls_Manager::SELECT,
					'groups'      => Aepro::$_helper->ae_get_flexible_content_fields(),
					'default'     => 'post',
					'description' => __( 'Choose parent flexible field.', 'ae-pro' ),
					'condition'   => [
						'ae_featured_bg_source!'        => [ 'post', '' ],
						'show_featured_bg'              => 'yes',
						'is_featured_bg_flexible_field' => 'yes',
					],
				]
			);
			$element->add_control(
				'flex-sub-fields',
				[
					'label'        => __( 'Sub Field', 'ae-pro' ),
					'type'         => 'aep-query',
					'parent_field' => 'flexible_fields',
					'query_type'   => 'flex-sub-fields',
					'placeholder'  => 'Select',
					'condition'    => [
						'ae_featured_bg_source!'        => [ 'post', '' ],
						'show_featured_bg'              => 'yes',
						'is_featured_bg_flexible_field' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_fallback_image',
				[
					'label'     => __( 'Fallback Image', 'ae-pro' ),
					'type'      => Controls_Manager::MEDIA,
					'condition' => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_image_size',
				[
					'label'        => __( 'Image Size', 'ae-pro' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => AePro::$_helper->ae_get_intermediate_image_sizes(),
					'default'      => 'large',
					'prefix_class' => 'ae-featured-img-size-',
					'render_type'  => 'template',
					'condition'    => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_bg_size',
				[
					'label'     => __( 'Background Size', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						'auto'    => __( 'Auto', 'ae-pro' ),
						'cover'   => __( 'Cover', 'ae-pro' ),
						'contain' => __( 'Contain', 'ae-pro' ),
					],
					'default'   => 'cover',
					'selectors' => [
						'{{WRAPPER}}' => 'background-size: {{VALUE}};',
					],
					'condition' => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_bg_position',
				[
					'label'     => __( 'Background Position', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						''              => __( 'Default', 'ae-pro' ),
						'top left'      => __( 'Top Left', 'ae-pro' ),
						'top center'    => __( 'Top Center', 'ae-pro' ),
						'top right'     => __( 'Top Right', 'ae-pro' ),
						'center left'   => __( 'Center Left', 'ae-pro' ),
						'center center' => __( 'Center Center', 'ae-pro' ),
						'center right'  => __( 'Center Right', 'ae-pro' ),
						'bottom left'   => __( 'Bottom Left', 'ae-pro' ),
						'bottom center' => __( 'Bottom Center', 'ae-pro' ),
						'bottom right'  => __( 'Bottom Right', 'ae-pro' ),
					],
					'selectors' => [
						'{{WRAPPER}}' => 'background-position: {{VALUE}};',
					],
					'condition' => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_bg_attachment',
				[
					'label'     => __( 'Background Attachment', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						''       => __( 'Default', 'ae-pro' ),
						'scroll' => __( 'Scroll', 'ae-pro' ),
						'fixed'  => __( 'Fixed', 'ae-pro' ),
					],
					'selectors' => [
						'{{WRAPPER}}' => 'background-attachment: {{VALUE}};',
					],
					'condition' => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_featured_bg_repeatae_featured_bg_repeat',
				[
					'label'     => __( 'Background Repeat', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						''          => __( 'Default', 'ae-pro' ),
						'no-repeat' => __( 'No-repeat', 'ae-pro' ),
						'repeat'    => __( 'Repeat', 'ae-pro' ),
						'repeat-x'  => __( 'Repeat-x', 'ae-pro' ),
						'repeat-y'  => __( 'Repeat-y', 'ae-pro' ),
					],
					'selectors' => [
						'{{WRAPPER}}' => 'background-repeat: {{VALUE}};',
					],
					'condition' => [
						'show_featured_bg' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_section_column_background_alert',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'ae_pro_alert',
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'raw'             => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Section/Column Backgrounds', 'https://wpvibes.link/go/dynamic-section-column-backgrounds' ), 'ae-pro' ),
					'separator'       => 'none',
				]
			);

		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {

			$element->add_control(
				'bg_image_divider',
				[
					'type'  => Controls_Manager::DIVIDER,
					'style' => 'thick',
				]
			);

			$element->add_control(
				'enable_bg_color',
				[
					'label'        => __( 'Background Color', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Show', 'ae-pro' ),
					'label_off'    => __( 'Hide', 'ae-pro' ),
					'return_value' => 'yes',
					'prefix_class' => 'ae-bg-color-',
				]
			);

			$ae_bg_color_source['custom_field']             = __( 'Post Custom Field', 'ae-pro' );
			$ae_bg_color_source['term_custom_field']        = __( 'Term Custom Field', 'ae-pro' );
			$ae_bg_color_source['option_page_custom_field'] = __( 'Option Page Custom Field', 'ae-pro' );

			$element->add_control(
				'ae_bg_color_source',
				[
					'label'        => __( 'Source', 'ae-pro' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => $ae_bg_color_source,
					'default'      => 'custom_field',
					'prefix_class' => 'ae-bg-color-source-',
					'condition'    => [
						'enable_bg_color' => 'yes',
					],
				]
			);

			if ( $post_type === 'ae_global_templates' ) {
				if ( ( $render_mode !== 'acf_repeater_layout' ) || ( $field_type !== 'flexible_content' && $render_mode === 'acf_repeater_layout' ) ) {
					$element->add_control(
						'is_bg_color_group_field',
						[
							'label'        => __( 'Is Group Field', 'ae-pro' ),
							'type'         => Controls_Manager::SWITCHER,
							'default'      => '',
							'label_on'     => __( 'Yes', 'ae-pro' ),
							'label_off'    => __( 'No', 'ae-pro' ),
							'return_value' => 'yes',
							'prefix_class' => 'ae-featured-bg-',
							'condition'    => [
								'ae_bg_color_source!' => 'post',
								'enable_bg_color'     => 'yes',
							],
						]
					);
				}
			} else {
				$element->add_control(
					'is_bg_color_group_field',
					[
						'label'        => __( 'Is Group Field', 'ae-pro' ),
						'type'         => Controls_Manager::SWITCHER,
						'default'      => '',
						'label_on'     => __( 'Yes', 'ae-pro' ),
						'label_off'    => __( 'No', 'ae-pro' ),
						'return_value' => 'yes',
						'prefix_class' => 'ae-featured-bg-',
						'condition'    => [
							'ae_bg_color_source!' => 'post',
							'enable_bg_color'     => 'yes',
						],
					]
				);
			}
			if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
				$element->add_control(
					'is_featured_bg_color_flexible_field',
					[
						'label'        => __( 'Is Flexible Field', 'ae-pro' ),
						'type'         => Controls_Manager::SWITCHER,
						'default'      => '',
						'label_on'     => __( 'Yes', 'ae-pro' ),
						'label_off'    => __( 'No', 'ae-pro' ),
						'return_value' => 'yes',
						'prefix_class' => 'ae-featured-bg-color-flexible-',
						'condition'    => [
							'ae_bg_color_source' => [ 'custom_field', 'option_page_custom_field' ],
							'enable_bg_color'    => 'yes',
						],
					]
				);
			}

			$element->add_control(
				'ae_bg_color_cf_parent_key',
				[
					'label'       => __( 'Group Field key', 'ae-pro' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Group Field Key', 'ae-pro' ),
					'default'     => '',
					'condition'   => [
						'ae_bg_color_source!'     => 'post',
						'enable_bg_color'         => 'yes',
						'is_bg_color_group_field' => 'yes',
					],
				]
			);

			if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
				$color_cf_field_key = [
					'enable_bg_color'                      => 'yes',
					'is_featured_bg_color_flexible_field!' => 'yes',
				];
			} else {
				$color_cf_field_key = [
					'enable_bg_color' => 'yes',
				];
			}

			$element->add_control(
				'ae_bg_color_cf_field_key',
				[
					'label'        => __( 'Custom Field key', 'ae-pro' ),
					'type'         => Controls_Manager::TEXT,
					'placeholder'  => __( 'Custom Field Key', 'ae-pro' ),
					'default'      => '',
					'prefix_class' => 'ae-bg-color-custom-field-',
					'condition'    => $color_cf_field_key,
				]
			);

			$element->add_control(
				'flexible_fields_color',
				[
					'label'       => __( 'Parent Field', 'ae-pro' ),
					'type'        => Controls_Manager::SELECT,
					'groups'      => Aepro::$_helper->ae_get_flexible_content_fields(),
					'description' => __( 'Choose parent flexible field.', 'ae-pro' ),
					'default'     => '',
					'condition'   => [
						'ae_bg_color_source' => [ 'custom_field', 'option_page_custom_field' ],
						'enable_bg_color'    => 'yes',
						'is_featured_bg_color_flexible_field' => 'yes',
					],
				]
			);
			$element->add_control(
				'flex-sub-fields-color',
				[
					'label'        => __( 'Sub Field', 'ae-pro' ),
					'type'         => 'aep-query',
					'parent_field' => 'flexible_fields_color',
					'query_type'   => 'flex-sub-fields',
					'placeholder'  => 'Select',
					'condition'    => [
						'ae_bg_color_source' => [ 'custom_field', 'option_page_custom_field' ],
						'enable_bg_color'    => 'yes',
						'is_featured_bg_color_flexible_field' => 'yes',
					],
				]
			);
		}

			$element->end_controls_section();

			$element->start_controls_section(
				'dynamic_link_section',
				[
					'tab'   => Aepro_Control_Manager::TAB_AE_PRO,
					'label' => __( 'Dynamic Link', 'ae-pro' ),
				]
			);

			$element->add_control(
				'enable_link',
				[
					'label'        => __( 'Enable Link', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'prefix_class' => 'ae-link-',
				]
			);

			$ae_bg_link_source['post'] = __( 'Post', 'ae-pro' );
		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {
			$ae_bg_link_source['custom_field_url'] = __( 'Custom Field (URL)', 'ae-pro' );
		}
			$ae_bg_link_source['static_url'] = __( 'Static Url', 'ae-pro' );

			$element->add_control(
				'dynamic_link_source',
				[
					'label'     => __( 'Links To', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => $ae_bg_link_source,
					'default'   => 'post',
					'condition' => [
						'enable_link' => 'yes',
					],
				]
			);

			$element->add_control(
				'is_dynamic_link_flexible_field',
				[
					'label'        => __( 'Is Flexible Field', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'condition'    => [
						'enable_link'         => 'yes',
						'dynamic_link_source' => 'custom_field_url',
					],
				]
			);

			$element->add_control(
				'dynamic_link_custom_field',
				[
					'label'       => __( 'Custom Field key', 'ae-pro' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Custom Field Key', 'ae-pro' ),
					'default'     => '',
					'condition'   => [
						'dynamic_link_source'             => 'custom_field_url',
						'enable_link'                     => 'yes',
						'is_dynamic_link_flexible_field!' => 'yes',
					],
				]
			);

			$element->add_control(
				'flexible_fields_link',
				[
					'label'     => __( 'Flexible Fields', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'groups'    => Aepro::$_helper->ae_get_flexible_content_fields(),
					'default'   => '',
					'condition' => [
						'dynamic_link_source'            => 'custom_field_url',
						'enable_link'                    => 'yes',
						'is_dynamic_link_flexible_field' => 'yes',
					],
				]
			);

			$element->add_control(
				'flex-sub-fields-link',
				[
					'label'        => __( 'Sub Field', 'ae-pro' ),
					'type'         => 'aep-query',
					'parent_field' => 'flexible_fields_link',
					'query_type'   => 'flex-sub-fields',
					'placeholder'  => 'Select',
					'condition'    => [
						'dynamic_link_source'            => 'custom_field_url',
						'enable_link'                    => 'yes',
						'is_dynamic_link_flexible_field' => 'yes',
					],
				]
			);

			$element->add_control(
				'static_url',
				[
					'label'       => __( 'Url', 'ae-pro' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'https://example.com', 'ae-pro' ),
					'default'     => '',
					'condition'   => [
						'dynamic_link_source' => 'static_url',
						'enable_link'         => 'yes',
					],
				]
			);

			$element->add_control(
				'enable_open_in_new_window',
				[
					'label'        => __( 'Enable Open In New Window', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'prefix_class' => 'ae-new-window-',
					'condition'    => [
						'enable_link' => 'yes',
					],
				]
			);

			$element->add_control(
				'ae_section_column_clickable_alert',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'ae_pro_alert',
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'raw'             => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Section/Column Clickable', 'https://wpvibes.link/go/section-column-clickable' ), 'ae-pro' ),
					'separator'       => 'none',
				]
			);

			$element->end_controls_section();
	}

	public function before_section_render( $element ) {

		if ( $element->get_settings( 'show_featured_bg' ) === 'yes' ) {

			$img_size   = $element->get_settings( 'ae_featured_image_size' );
			$img_source = $element->get_settings( 'ae_featured_bg_source' );

			$settings       = $element->get_settings_for_display();
			$fallback_image = $settings['ae_featured_fallback_image'];
			$image          = '';
			switch ( $img_source ) {
				case 'post':
					$img = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), $img_size );
					if ( is_array( $img ) ) {
						$image = $img[0];
					}
					if ( empty( $image ) && ! empty( $fallback_image ) ) {
						$image_id = $fallback_image['id'];
						$img      = wp_get_attachment_image_src( $image_id, $img_size, false );
						if ( is_array( $img ) ) {
							$image = $img[0];
						}
					}
					break;

				case 'custom_field':
					if ( ! \Aepro\Plugin::show_acf() && ! \Aepro\Plugin::show_acf( true ) ) {
						$image = '';
						break;
					}

					$img      = wp_get_attachment_image_src( get_field( $element->get_settings( 'ae_featured_bg_cf_field_key' ), get_the_ID() ), $img_size );
					$repeater = Aepro::$_helper->is_repeater_block_layout();
					if ( $repeater['is_repeater'] ) {
						$img_id = $this->is_bg_field_in_repeater( $element, $img, 'ae_featured_bg_cf_field_key' );
						$img    = wp_get_attachment_image_src( $img_id, $img_size );
					}
					if ( $element->get_settings( 'is_featured_bg_flexible_field' ) === 'yes' ) {
						$parent_field = $settings['flexible_fields'];
						$field_name   = $settings['flex-sub-fields'];
						$value        = $this->is_bg_field_in_flexible_content( $settings, $parent_field, $field_name );
						$img          = wp_get_attachment_image_src( $value, $img_size );
					}
					if ( $element->get_settings( 'is_featured_bg_group_field' ) === 'yes' ) {
						$group_field = get_field( $element->get_settings( 'ae_featured_bg_cf_parent_key' ), get_the_ID() );
						$img         = wp_get_attachment_image_src( $group_field[ $element->get_settings( 'ae_featured_bg_cf_field_key' ) ], $img_size );
					}
					if ( is_array( $img ) ) {
						$image = $img[0];
					}
					if ( empty( $image ) && ! empty( $fallback_image ) ) {
						$image_id = $fallback_image['id'];
						$img      = wp_get_attachment_image_src( $image_id, $img_size, false );
						if ( is_array( $img ) ) {
							$image = $img[0];
						}
					}
					break;

				case 'term_custom_field':
					if ( ! \Aepro\Plugin::show_acf() && ! \Aepro\Plugin::show_acf( true ) ) {
						$image = '';
						break;
					}

					if ( Plugin::instance()->editor->is_edit_mode() ) {
						$term = Aepro::$_helper->get_preview_term_data();
					} else {
						$term = get_queried_object();
					}

					$img = wp_get_attachment_image_src( get_field( $element->get_settings( 'ae_featured_bg_cf_field_key' ), $term ), $img_size );

					if ( is_array( $img ) ) {
						$image = $img[0];
					}

					if ( empty( $image ) && ! empty( $fallback_image ) ) {
						$image_id = $fallback_image['id'];
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $image_id == 0 && ! empty( $fallback_image['url'] ) ) {
							$image_id = attachment_url_to_postid( $fallback_image['url'] );
						}

						$img = wp_get_attachment_image_src( $image_id, $img_size, false );
						if ( is_array( $img ) ) {
							$image = $img[0];
						}
					}
					break;

				case 'option_page_custom_field':
					if ( ! \Aepro\Plugin::show_acf() && ! \Aepro\Plugin::show_acf( true ) ) {
						$image = '';
						break;
					}
					$img = wp_get_attachment_image_src( get_field( $element->get_settings( 'ae_featured_bg_cf_field_key' ), 'option', true ), $img_size );

					$repeater = Aepro::$_helper->is_repeater_block_layout();
					if ( $repeater['is_repeater'] ) {
						$img_id = $this->is_bg_field_in_repeater( $element, $img, 'ae_featured_bg_cf_field_key' );
						$img    = wp_get_attachment_image_src( $img_id, $img_size );
					}

					if ( $element->get_settings( 'is_featured_bg_group_field' ) === 'yes' ) {
						$group_field = get_field( $element->get_settings( 'ae_featured_bg_cf_parent_key' ), 'option', true );
						$img         = wp_get_attachment_image_src( $group_field[ $element->get_settings( 'ae_featured_bg_cf_field_key' ) ], $img_size );
					}
					if ( $element->get_settings( 'is_featured_bg_flexible_field' ) === 'yes' ) {
						$parent_field = $settings['flexible_fields'];
						$field_name   = $settings['flex-sub-fields'];
						$value        = $this->is_bg_field_in_flexible_content( $settings, $parent_field, $field_name );
						$img          = wp_get_attachment_image_src( $value, $img_size );
					}

					if ( is_array( $img ) ) {
						$image = $img[0];
					}
					if ( empty( $image ) && ! empty( $fallback_image ) ) {
						$image_id = $fallback_image['id'];
						$img      = wp_get_attachment_image_src( $image_id, $img_size, false );
						if ( is_array( $img ) ) {
							$image = $img[0];
						}
					}
					break;
				default:
					$image = '';
			}

			if ( ! empty( $image ) ) {
				$element->add_render_attribute(
					'_wrapper',
					[
						'data-ae-bg' => $image,
					]
				);
			}
		}

		if ( $element->get_settings( 'enable_bg_color' ) === 'yes' ) {
			$color_settings = $element->get_settings_for_display();

			$color_source = $element->get_settings( 'ae_bg_color_source' );
			$color        = '';
			switch ( $color_source ) {
				case 'custom_field':
					$color = get_field( $element->get_settings( 'ae_bg_color_cf_field_key' ), get_the_ID() );
					$color = $this->is_bg_field_in_repeater( $element, $color, 'ae_bg_color_cf_field_key' );
					$color = $this->is_bg_field_in_group( $element, $color );
					if ( isset( $color_settings['is_featured_bg_color_flexible_field'] ) ) {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $color_settings['is_featured_bg_color_flexible_field'] == 'yes' ) {
							$parent_field = $color_settings['flexible_fields_color'];
							$field_name   = $color_settings['flex-sub-fields-color'];
							$color        = $this->is_bg_field_in_flexible_content( $color_settings, $parent_field, $field_name );
						}
					}
					break;
				case 'term_custom_field':
					if ( Plugin::instance()->editor->is_edit_mode() ) {
						$term = Aepro::$_helper->get_preview_term_data();
					} else {
						$term = get_queried_object();
					}
					$color = get_field( $element->get_settings( 'ae_bg_color_cf_field_key' ), $term );
					break;
				case 'option_page_custom_field':
					$color = get_field( $element->get_settings( 'ae_bg_color_cf_field_key' ), 'option', true );
					$color = $this->is_bg_field_in_repeater( $element, $color, 'ae_bg_color_cf_field_key' );
					$color = $this->is_bg_field_in_group( $element, $color );
					if ( isset( $color_settings['is_featured_bg_color_flexible_field'] ) ) {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $color_settings['is_featured_bg_color_flexible_field'] == 'yes' ) {
							$parent_field = $color_settings['flexible_fields_color'];
							$field_name   = $color_settings['flex-sub-fields-color'];
							$color        = $this->is_bg_field_in_flexible_content( $color_settings, $parent_field, $field_name );
						}
					}
					break;
			}
			$element->add_render_attribute(
				'_wrapper',
				[
					'data-ae-bg-color' => $color,
				]
			);
		}

		if ( $element->get_settings( 'enable_link' ) === 'yes' ) {
			$link_source = $element->get_settings( 'dynamic_link_source' );

			switch ( $link_source ) {
				case 'post':
					$bg_link = get_permalink();
					break;
				case 'custom_field_url':
					if ( ! empty( $element->get_settings( 'dynamic_link_custom_field' ) ) ) {
						$bg_link  = get_post_meta( get_the_id(), $element->get_settings( 'dynamic_link_custom_field' ), true );
						$repeater = Aepro::$_helper->is_repeater_block_layout();
						if ( $repeater['is_repeater'] ) {
							if ( isset( $repeater['field'] ) ) {
								$repeater_field = get_field( $repeater['field'], get_the_ID() );
								$bg_link        = $repeater_field[0][ $element->get_settings( 'dynamic_link_custom_field' ) ];

							} else {
								$bg_link = get_sub_field( $element->get_settings( 'dynamic_link_custom_field' ) );
							}
						}
					}
					if ( $element->get_settings( 'is_dynamic_link_flexible_field' ) === 'yes' ) {
						$parent_field = $element->get_settings( 'flexible_fields_link' );
						$field_name   = $element->get_settings( 'flex-sub-fields-link' );
						$bg_link      = $this->is_bg_field_in_flexible_content( $element->get_settings(), $parent_field, $field_name );
					}

					break;
				case 'static_url':
					$bg_link = $element->get_settings( 'static_url' );
					break;
				default:
					$bg_link = '';
			}

			$element->add_render_attribute(
				'_wrapper',
				[
					'data-ae-url' => $bg_link,
				]
			);

		}
	}

	public function is_bg_field_in_repeater( $element, $bg, $field_key ) {
		$repeater = Aepro::$_helper->is_repeater_block_layout();
		if ( $repeater['is_repeater'] ) {
			if ( isset( $repeater['field'] ) ) {
				$repeater_field = get_field( $repeater['field'], get_the_ID() );
				$bg             = $repeater_field[0][ $element->get_settings( $field_key ) ];
			} else {
				$bg = get_sub_field( $element->get_settings( $field_key ) );
			}
		}
		return $bg;
	}

	public function is_bg_field_in_group( $element, $bg ) {
		if ( $element->get_settings( 'is_bg_color_group_field' ) === 'yes' ) {
			$parent_field = get_field( $element->get_settings( 'ae_bg_color_cf_parent_key' ), get_the_ID() );
			$bg           = $parent_field[ $element->get_settings( 'ae_bg_color_cf_field_key' ) ];
		}
		return $bg;
	}

	public function is_bg_field_in_flexible_content( $settings, $parent_field = '', $field_name = '' ) {
		$parent_field_data = explode( ':', $parent_field );
		$post              = Aepro::$_helper->get_demo_post_data();
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			if ( $parent_field_data[0] === 'option' ) {
				$parent_field_name = $parent_field_data[2];
				$layout            = $parent_field_data[3];
				$data              = 'option';
			} else {
				$parent_field_name = $parent_field_data[1];
				$layout            = $parent_field_data[2];
				$data              = $post->ID;
			}
			if ( get_post_type() === 'ae_global_templates' ) {
				global $post;
				$ae_render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
				if ( $ae_render_mode === 'acf_repeater_layout' ) {
					$flexible_content = get_field( $parent_field_name, $data );
					foreach ( $flexible_content as $key => $fc ) {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $fc['acf_fc_layout'] == $layout ) {
							$index = $key;
							break;
						}
					}
					$value = $flexible_content[ $index ][ $field_name ];
				} else {
					$value = get_sub_field( $field_name );
				}
			} else {
				$value = get_sub_field( $field_name );
			}
		} else {
			$value = get_sub_field( $field_name );
		}

		return $value;
	}
}

