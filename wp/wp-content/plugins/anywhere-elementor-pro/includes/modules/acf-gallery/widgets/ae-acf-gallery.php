<?php
namespace Aepro\Modules\AcfGallery\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Aepro\Modules\AcfGallery\Skins;
use Elementor\Controls_Manager;

class AeAcfGallery extends Widget_Base {

	protected $_has_template_content = false;

	protected $_access_level = 2;

	public function get_name() {
		return 'ae-acf-gallery';
	}

	public function is_enabled() {

		if ( AE_ACF_PRO ) {
			return true;
		}

		return false;
	}

	public function get_title() {
		return __( 'AE - ACF Gallery', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'acf',
			'fields',
			'custom fields',
			'meta',
			'gallery',
			'image',
		];
	}

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Grid( $this ) );
		$this->add_skin( new Skins\Skin_Carousel( $this ) );
		$this->add_skin( new Skins\Skin_Smart_Grid( $this ) );
		$this->add_skin( new Skins\Skin_Justified( $this ) );
	}

	public function get_script_depends() {
		return [ 'jquery-masonry', 'swiper', 'justifiedGallery' ];
	}

	public function get_style_depends() {
		return [ 'justifiedGallery-css' ];
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_overlay',
			[
				'label'     => __( 'Overlay', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'_skin' => [ 'grid', 'smart_grid', 'justified' ],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_overlay_style',
			[
				'label'     => __( 'Overlay Setting', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin' => [ 'grid', 'smart_grid', 'justified' ],
				],
			]
		);

		$this->end_controls_section();

		$this->get_widget_title_controls();

		$this->get_widget_title_style_controls();
	}
}
