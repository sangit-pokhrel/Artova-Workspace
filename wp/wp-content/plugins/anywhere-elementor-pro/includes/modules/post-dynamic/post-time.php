<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Post_Time extends Tag {

	public function get_name() {
		return 'ae-post-time';
	}

	public function get_title() {
		return __( '(AE) Post Time', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'type',
			[
				'label'   => __( 'Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'post_date_gmt'     => __( 'Post Published', 'ae-pro' ),
					'post_modified_gmt' => __( 'Post Modified', 'ae-pro' ),
				],
				'default' => 'post_date_gmt',
			]
		);

		$this->add_control(
			'format',
			[
				'label'   => __( 'Format', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'default' => __( 'Default', 'ae-pro' ),
					'g:i a'   => gmdate( 'g:i a' ),
					'g:i A'   => gmdate( 'g:i A' ),
					'H:i'     => gmdate( 'H:i' ),
					'custom'  => __( 'Custom', 'ae-pro' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'custom_format',
			[
				'label'       => __( 'Custom Format', 'ae-pro' ),
				'default'     => '',
				'description' => sprintf( '<a href="https://go.elementor.com/wordpress-date-time/" target="_blank">%s</a>', __( 'Documentation on date and time formatting', 'ae-pro' ) ),
				'condition'   => [
					'format' => 'custom',
				],
			]
		);
	}

	public function render() {
		$settings  = $this->get_settings_for_display();
		$post_data = Aepro::$_helper->get_demo_post_data();
		$time_type = $settings['type'];
		$format    = $settings['format'];
		switch ( $format ) {
			case 'default':
				$date_format = '';
				break;
			case 'custom':
				$date_format = $this->get_settings( 'custom_format' );
				break;
			default:
				$date_format = $format;
				break;
		}

		if ( 'post_date_gmt' === $time_type ) {
			$value = get_the_time( $date_format, $post_data->ID );
		} else {
			$value = get_the_modified_time( $date_format, $post_data->ID );
		}
		echo wp_kses_post( $value );
	}
}
