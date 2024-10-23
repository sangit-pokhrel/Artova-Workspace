<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;

class ACF_Text {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$post       = Aepro::$_helper->get_demo_post_data();
		
		$title_raw = $widget->get_raw_acf_field_value($settings);

		if ( is_array( $title_raw ) || is_object( $title_raw ) ) {
			return;
		}
		$placeholder  = $settings['placeholder'];
		$before_text  = $settings['prefix'];
		$after_text   = $settings['suffix'];
		$links_to     = $settings['links_to'];
		$link_new_tab = $settings['link_new_tab'];
		$link         = '';

		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}

		if ( $title_raw === '' && $placeholder === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		} elseif ( $title_raw === '' & $placeholder !== '' ) {
			$title = $placeholder;
		} else {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['strip_text'] == 'yes' ) {
				$strip_mode   = $settings['strip_mode'];
				$strip_size   = $settings['strip_size'];
				$strip_append = $settings['strip_append'];
				if ( $strip_mode === 'word' ) {
					$title_raw = wp_trim_words( $title_raw, $strip_size, $strip_append );
				} else {
					$title_raw = Aepro::$_helper->ae_trim_letters( $title_raw, 0, $strip_size, $strip_append );
				}
			}
			if($before_text != ''){
				$title_raw = '<span class="ae-prefix">' . $before_text . '</span>' . $title_raw;
			}
			if($after_text != ''){
				$title_raw = $title_raw . '<span class="ae-suffix">' . $after_text . '</span>';
			}

			$title = $title_raw;
			//$title = '<span class="ae-prefix">' . $before_text . '</span>' . $title_raw . '<span class="ae-suffix">' . $after_text . '</span>';
		}

		// Process Content
		$title = self::process_content( $title, $settings );

		if ( $links_to !== '' ) {

			switch ( $links_to ) {

				case 'post':
					$link = get_permalink( $post->ID );
					break;

				case 'static':
					$link = $settings['link_url'];
					break;

				case 'custom_field':
					$link_cf                                      = $settings['link_cf'];
										$field_args['field_name'] = $link_cf;
										$link                     = AcfMaster::instance()->get_field_value( $field_args );

					break;

			}
		}

		$widget->add_render_attribute( 'wrapper-class', 'class', 'ae-acf-wrapper' );
		$widget->add_render_attribute( 'title-class', 'class', 'ae-acf-content-wrapper' );

		$html_tag = $settings['html_tag'];

		if ( $link !== '' ) {

			$widget->add_render_attribute( 'anchor', 'title', $title_raw );
			$widget->add_render_attribute( 'anchor', 'href', $link );
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $link_new_tab == 'yes' ) {
				$widget->add_render_attribute( 'anchor', 'target', '_blank' );
			}

			$title_html = '<a ' . $widget->get_render_attribute_string( 'anchor' ) . '>' . $title . '</a>';
		} else {

			$title_html = $title;
		}

		$html = sprintf( '<%1$s itemprop="name" %2$s>%3$s</%1$s>', $html_tag, $widget->get_render_attribute_string( 'title-class' ), $title_html );
		if ( $title === '' ) {
			$widget->add_render_attribute( 'wrapper-class', 'class', 'ae-hide' );
		}
		?>
		<div <?php echo $widget->get_render_attribute_string( 'wrapper-class' ); ?>>
		<?php
		echo $html;
		?>
		</div>
		<?php
	}

	public static function register_controls($widget) {

		$widget->add_control(
			'prefix',
			[
				'label'     => __( 'Before Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'separator' => 'before',
				'condition' => [
					'field_type' => 'text'
				]
			]
		);

		$widget->add_control(
			'suffix',
			[
				'label' => __( 'After Text', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'text'
				]
			]
		);

		$widget->add_control(
			'placeholder',
			[
				'label'       => __( 'Placeholder Text', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To be used as default text when there is no data in ACF Field', 'ae-pro' ),
				'condition' => [
					'field_type' => ['text', 'textarea']
				]
			]
		);

		$widget->add_control(
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
				'condition' => [
					'field_type' => ['text', 'textarea']
				]
			]
		);

		$widget->add_control(
			'link_url',
			[
				'label'       => __( 'Static URL', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter URL', 'ae-pro' ),
				'condition'   => [
					'field_type' => ['text', 'textarea', 'number'],
					'links_to' => 'static',
				],
			]
		);
		$widget->add_control(
			'link_cf',
			[
				'label'       => __( 'Enter Field Key', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Field Key', 'ae-pro' ),
				'condition'   => [
					'field_type' => ['text', 'textarea', 'number'],
					'links_to' => 'custom_field',
				],
				'description' => __( 'Mention ACF field that contains an url', 'ae-pro' ),
			]
		);

		$widget->add_control(
			'link_new_tab',
			[
				'label'     => __( 'Open in new tab', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_off' => __( 'No', 'ae-pro' ),
				'label_on'  => __( 'Yes', 'ae-pro' ),
				'default'   => __( 'label_off', 'ae-pro' ),
				'condition' => [
					'field_type' => ['text', 'textarea', 'number'],
					'links_to!' => '',
				],
			]
		);

		$widget->add_control(
			'strip_text',
			[
				'label'        => __( 'Strip Text', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'strip_yes'    => __( 'Yes', 'ae-pro' ),
				'strip_no'     => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition' => [
					'field_type' => ['text', 'textarea', 'wysiwyg']
				]
			]
		);

		$widget->add_control(
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
					'field_type' => ['text', 'textarea', 'wysiwyg'],
					'strip_text' => 'yes',
				],
			]
		);

		$widget->add_control(
			'strip_size',
			[
				'label'       => __( 'Strip Size', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Strip Size', 'ae-pro' ),
				'default'     => __( '5', 'ae-pro' ),
				'condition'   => [
					'field_type' => ['text', 'textarea', 'wysiwyg'],
					'strip_text' => 'yes',
				],
				'description' => __( 'Number of words to show.', 'ae-pro' ),
			]
		);

		$widget->add_control(
			'strip_append',
			[
				'label'       => __( 'Append Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Append Text', 'ae-pro' ),
				'default'     => __( '...', 'ae-pro' ),
				'condition'   => [
					'field_type' => ['text', 'textarea', 'wysiwyg'],
					'strip_text' => 'yes',
				],
				'description' => __( 'What to append if Title needs to be trimmed.', 'ae-pro' ),
			]
		);
	}

	public static function register_unfold_controls($widget) {

		$widget->start_controls_section(
			'section_unfold_layout',
			[
				'label' => __( 'Unfold', 'ae-pro' ),
				'condition' => [
					'field_type' => ['text', 'textarea', 'wysiwyg'],
				]
			]
		);

		$widget->add_control(
			'enable_unfold',
			[
				'label'        => __( 'Enable Unfold', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$widget->add_responsive_control(
			'unfold_animation_speed',
			[
				'label'     => __( 'Animation Speed', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 500,
						'max'  => 5000,
						'step' => 100,
					],
				],
				'default'   => [
					'size' => 500,
				],
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->add_control(
			'auto_hide_unfold_button',
			[
				'label'        => __( 'Auto Hide Unfold Button', 'ae-pro' ),
				'description'  => __( 'When Content is less than Unfold height', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->add_control(
			'button_controls_heading',
			[
				'label'     => __( 'Button', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->start_controls_tabs( 'tabs_button_controls' );

		$widget->start_controls_tab(
			'tab_button_unfold',
			[
				'label'     => __( 'Unfold', 'ae-pro' ),
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);
		$widget->add_control(
			'unfold_text',
			[
				'label'     => __( 'Show More Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Show More',
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->add_control(
			'unfold_icon',
			[
				'label'       => __( 'Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => '',
				'condition'   => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->start_controls_tab(
			'tab_button_fold',
			[
				'label'     => __( 'Fold', 'ae-pro' ),
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->add_control(
			'fold_text',
			[
				'label'     => __( 'Show Less Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Show Less',
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->add_control(
			'fold_icon',
			[
				'label'       => __( 'Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => '',
				'condition'   => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_control(
			'button_icon_position',
			[
				'label'     => __( 'Icon Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'  => __( 'Before', 'ae-pro' ),
					'right' => __( 'After', 'ae-pro' ),
				],
				'condition' => [
					'enable_unfold' => 'yes',
					'unfold_icon!'  => '',
				],
			]
		);

		$widget->add_control(
			'button_icon_indent',
			[
				'label'     => __( 'Icon Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'default'   => [
					'size' => 10,
				],
				'condition' => [
					'enable_unfold' => 'yes',
					'unfold_icon!'  => '',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-acf-unfold-button-icon.elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-acf-unfold-button-icon.elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$widget->add_responsive_control(
			'unfold_button_align',
			[
				'label'     => __( 'Button Align', 'ae-pro' ),
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
					'{{WRAPPER}} .ae-acf-unfold' => 'text-align: {{VALUE}}',
				],
				'condition' => [
					'enable_unfold' => 'yes',
				],
			]
		);

		$widget->end_controls_section();
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

}

ACF_Text::instance();