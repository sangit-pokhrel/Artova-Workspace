<?php

namespace Aepro\Modules\Trigger;

use Aepro\Base\ModuleBase;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Aepro\Aepro;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-trigger',
		];
	}

	public function __construct() {
		parent::__construct();
		add_action( 'elementor/element/ae-post-blocks-adv/layout_style/before_section_start', [ $this, 'add_widget_id' ] );
		add_action( 'elementor/element/ae-post-blocks/layout_style/before_section_start', [ $this, 'add_widget_id' ] );
		add_action( 'elementor/element/ae-acf-repeater/layout_style/before_section_start', [ $this, 'add_widget_id' ] );
		add_action( 'elementor/element/ae-acf-gallery/section_style/before_section_start', [ $this, 'add_widget_id' ] );
		add_action( 'elementor/element/ae-acf-flexible-content/layout_style/before_section_start', [ $this, 'add_widget_id' ] );
	}

	public function add_widget_id( Widget_Base $widget_Base ) {

		$widget_Base->start_controls_section(
			'section_trigger',
			[
				'label' => __( 'Trigger', 'ae-pro' ),
			]
		);

		$widget_Base->add_control(
			'ae_widget_id_hidden',
			[
				'label' => 'Widget ID',
				'type'  => Controls_Manager::HIDDEN,
			]
		);
		$shortcode_template = '{{ view.container.settings.get( \'ae_widget_id_hidden\' ) }}';

		$widget_Base->add_control(
			'ae_widget_id',
			[
				'type'    => Controls_Manager::RAW_HTML,
				'classes' => 'ae-widget-id',
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'raw'     => __( '<span class="ae-trigger-id"><span><i>Widget ID</i> - </span><span><input readonly type="text" value="' . $shortcode_template . '" /></span></span>', 'ae-pro' ),
			]
		);

		$widget_Base->add_control(
			'ae-trigger-module-doc',
			[
				'type'    => Controls_Manager::RAW_HTML,
				'classes' => 'ae-widget-id',
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'raw'     => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Trigger', 'https://wpvibes.link/go/widget-ae-trigger' ), 'ae-pro' ),
			]
		);

		$widget_Base->end_controls_section();
	}
}
