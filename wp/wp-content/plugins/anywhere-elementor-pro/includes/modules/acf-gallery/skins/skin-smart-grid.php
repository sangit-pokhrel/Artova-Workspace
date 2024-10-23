<?php
namespace Aepro\Modules\AcfGallery\Skins;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Aepro\Modules\AcfGallery\Skins\Skin_grid;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Skin_Smart_Grid extends Skin_Grid {

	public function get_id() {
		return 'smart_grid';
	}

	public function get_title() {
		return __( 'Smart Grid', 'ae-pro' );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;
		parent::field_control();

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
			]
		);

		parent::grid_view();

		$this->remove_control( 'masonry' );

		$this->update_responsive_control(
			'columns',
			[
				'selectors' => [
					'{{WRAPPER}} .ae-grid-smart' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr))',
				],
				'condition' => [
					'_skin' => 'smart_grid',
				],
			]
		);

		$this->update_responsive_control(
			'gutter',
			[
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-grid > .ae-grid-item' => 'margin-bottom:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-grid-smart > .ae-grid-item' => 'margin-bottom:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-grid'                 => 'grid-column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-grid-smart'           => 'grid-column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
	}
}
