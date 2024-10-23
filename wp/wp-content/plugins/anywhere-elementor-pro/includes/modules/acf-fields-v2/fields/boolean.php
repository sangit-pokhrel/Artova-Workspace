<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;

class ACF_Boolean {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$value = $widget->get_raw_acf_field_value($settings);

		$true_message  = $settings['true_message'];
		$false_message = $settings['false_message'];
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}
		if ( empty( $true_message ) && empty( $false_message ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}
		////phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( ( $value == 1 || $value === true ) && $true_message !== '' ) {
			echo '<div class="true-message">' . do_shortcode( $true_message ) . '</div>';
			////phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		} elseif ( ( $value == 0 || $value === false ) && $false_message !== '' ) {
			echo '<div class="false-message">' . do_shortcode( $false_message ) . '</div>';
		}
	}

	public static function register_controls($widget) {

		$widget->add_control(
			'message_heading',
			[
				'label'       => __( 'Messages', 'ae-pro' ),
				'type'        => Controls_Manager::HEADING,
				'separator'   => 'before',
				'description' => __( 'Message to display when field return True or False. Also supports shortcode', 'ae-pro' ),
				'condition'   => [
					'field_type' => 'true_false'
				] 
			]
		);

		$widget->add_control(
			'true_message',
			[
				'label' => __( 'True Message', 'ae-pro' ),
				'type'  => Controls_Manager::TEXTAREA,
				'condition'   => [
					'field_type' => 'true_false'
				] 
			]
		);

		$widget->add_control(
			'false_message',
			[
				'label' => __( 'False Message', 'ae-pro' ),
				'type'  => Controls_Manager::TEXTAREA,
				'condition'   => [
					'field_type' => 'true_false'
				] 
			]
		);
	}
}