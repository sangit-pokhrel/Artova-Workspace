<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;

class ACF_Number {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings){

		$singular_string = '';
		$plural_string   = '';
		$output_string   = '';
		$print_plain     = false;

		$number = $widget->get_raw_acf_field_value($settings);

		$default_blank = $settings['default_blank'];
		$default_zero  = $settings['default_zero'];
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}
		if ( $number === '' && $default_blank === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}
		if ( $number === '' && $default_blank !== '' ) {
			$number      = $default_blank;
			$print_plain = true;
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		} elseif ( $number == 0 && $default_zero !== '' ) {
			$number      = $default_zero;
			$print_plain = true;
		}

		$widget->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );

		if ( $print_plain ) {
			?>

			<div <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
				<div class="ae-acf-content-wrapper">
					<?php echo esc_html( $number ); ?>
				</div>
			</div>

			<?php

			return;
		}

		if ( $number !== '' ) {

			$singular_prefix   = $settings['singular_prefix'];
			$plural_prefix     = $settings['plural_prefix'];
			$singular_suffix   = $settings['singular_suffix'];
			$plural_suffix     = $settings['plural_suffix'];
			$decimal_precision = 0;

			if ( ! empty( $singular_prefix ) ) {
				$singular_string = '<span class="ae-prefix">' . $singular_prefix . '</span>';
			}

			$singular_string .= '%s';

			if ( ! empty( $singular_suffix ) ) {
				$singular_string .= '<span class="ae-suffix">' . $singular_suffix . '</span>';
			}

			if ( ! empty( $plural_prefix ) ) {
				$plural_string = '<span class="ae-prefix">' . $plural_prefix . '</span>';
			}

			$plural_string .= '%s';

			if ( ! empty( $plural_suffix ) ) {
				$plural_string .= '<span class="ae-suffix">' . $plural_suffix . '</span>';
			}

			if ( $settings['enable_decimals'] === 'yes' ) {
				$decimal_precision = $settings['decimal_precision'];
			}
			//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralSingle
			$output_string = sprintf( _n( $singular_string, $plural_string, $number, 'ae-pro' ), number_format_i18n( $number, $decimal_precision ) );

		} else {
			$widget->add_render_attribute( 'wrapper', 'class', 'ae-hide' );
		}

		?>


		<div <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="ae-acf-content-wrapper">
				<?php echo $output_string; ?>
			</div>
		</div>
		<?php
	}

	public static function register_controls($widget) {

		$widget->add_control(
			'enable_decimals',
			[
				'label'        => __( 'Enable Decimals', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'decimal_precision',
			[
				'label'       => __( 'Decimal Precision', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Precision of the number of decimal places', 'ae-pro' ),
				'condition'   => [
					'field_type' => 'number',
					'enable_decimals' => 'yes',
				],
			]
		);

		$widget->add_control(
			'default_blank',
			[
				'label'       => __( 'Default Value (Blank)', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To be use when field value is blank', 'ae-pro' ),
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'default_zero',
			[
				'label'       => __( 'Default Value (Zero)', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To be use when field value is zero', 'ae-pro' ),
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'singular_prefix',
			[
				'label' => __( 'Singular Prefix', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'plural_prefix',
			[
				'label' => __( 'Plural Prefix', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'singular_suffix',
			[
				'label' => __( 'Singular Suffix', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_control(
			'plural_suffix',
			[
				'label' => __( 'Plural Suffix', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => 'number'
				]
			]
		);

		$widget->add_responsive_control(
			'align',
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
				'condition' => [
					'field_type' => 'number'
				]
			]
		);
	}
}

ACF_Text::instance();