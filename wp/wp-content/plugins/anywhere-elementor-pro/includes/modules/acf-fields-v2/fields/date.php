<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;

class ACF_Date {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$date_format = $settings['date_format'];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $date_format == 'default' ) {
			$field_args['acf_format'] = 1;
		} else {
			$field_args['acf_format'] = 0;
		}
		
		$date = $widget->get_raw_acf_field_value($settings);

		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}
		if ( $date === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}

		if ( $settings['date_format'] === 'custom' ) {
			$format = $settings['date_custom_format'];
		} elseif ( $settings['date_format'] === 'default' ) {
			$format = $date;
		} else {
			$format = $settings['date_format'];
		}

		$date = str_replace('/', '-', $date);
		$custom_field_date = date_i18n( $format, strtotime( $date ) );
		$html_tag          = $settings['html_tag'];
		$class             = 'date ae-acf-content-wrapper';
		?>
			<div class="ae-acf-wrapper">
				<?php echo sprintf( '<%1$s class="%2$s">%3$s</%1$s>', $html_tag, $class, $custom_field_date ); ?>
			</div>
		<?php

	}

	public static function register_controls($widget){

		$date_format            = Aepro::$_helper->ae_get_date_format();
		$date_format['default'] = 'Default';

		$widget->add_control(
			'date_format',
			[
				'label'       => __( 'Date format', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => $date_format,
				'default'     => 'F j, Y',
				'description' => '<a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"> Click here</a> for documentation on date and time formatting.',
				'condition'   => [
					'field_type' => 'date',
				],
			]
		);

		$widget->add_control(
			'date_custom_format',
			[
				'label'       => __( 'Date Format', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Date Format', 'ae-pro' ),
				'default'     => 'y:m:d',
				'condition'   => [
					'field_type' => 'date',
					'date_format' => 'custom',
				],
			]
		);
	}
}