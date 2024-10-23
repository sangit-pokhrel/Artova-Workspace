<?php
namespace Aepro;

class AeFacetWP_Integration {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'elementor/element/ae-post-blocks/section_layout/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/ae-post-blocks-adv/section_query/after_section_end', [ $this, 'register_controls_for_post_block_adv' ], 10, 2 );
		add_action( 'elementor/widget/before_render_content', [ $this, 'add_facetwp_class' ] );

		add_action( 'aepro/post-blocks-adv/custom-source-query', [ $this, 'add_facet' ], 10, 2 );
	}

	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'facetwp_section',
			[
				'label'     => __( 'FacetWP', 'ae-pro' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'ae_post_type' => 'current_loop',
				],
			]
		);

		$element->add_control(
			'enable_facetwp',
			[
				'label'        => __( 'Enable FacetWP', 'ae-pro' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'ae_post_type' => 'current_loop',
				],
			]
		);

		$element->end_controls_section();
	}

	public function register_controls_for_post_block_adv( $element, $args ) {
		$element->start_controls_section(
			'facetwp_section',
			[
				'label' => __( 'FacetWP', 'ae-pro' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,

			]
		);

		$element->add_control(
			'enable_facetwp',
			[
				'label'        => __( 'Enable FacetWP', 'ae-pro' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => '',

			]
		);

		$element->end_controls_section();
	}

	public function add_facetwp_class( $widget ) {
		$widget_array = [
			'ae-post-blocks',
			'ae-post-blocks-adv',
		];
		if ( ! in_array( $widget->get_name(), $widget_array, true ) ) {
			return;
		}
		$settings = $widget->get_settings();
		if ( $settings['enable_facetwp'] === 'yes' ) {
			$widget->add_render_attribute( '_wrapper', 'class', [ 'facetwp-template' ] );
		}
	}

	public function add_facet( $query_args, $settings ) {
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( isset( $settings['enable_facetwp'] ) && $settings['enable_facetwp'] == 'yes' ) {
			$query_args['facetwp'] = true;
			$paged                 = 0;
			// get pagination query var
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET[ FWP()->helper->get_setting( 'prefix' ) . 'paged' ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$paged = $_GET[ FWP()->helper->get_setting( 'prefix' ) . 'paged' ];
				if ( is_numeric( $paged ) && $paged > 0 ) {
					$query_args['offset'] = ( $paged - 1 ) * $query_args['posts_per_page'];
				}
			}
		} else {
			$query_args['facetwp'] = false;
		}

		return $query_args;
	}
}
AeFacetWP_Integration::instance();
