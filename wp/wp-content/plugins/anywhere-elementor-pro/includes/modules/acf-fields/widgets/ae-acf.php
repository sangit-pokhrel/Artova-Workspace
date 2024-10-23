<?php
namespace Aepro\Modules\AcfFields\Widgets;

use Aepro\Modules\AcfFields\Skins;
use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;

class AeAcf extends Widget_Base {

	protected $_has_template_content = false;

	protected $_access_level = 2;

	public function get_name() {
		return 'ae-acf';
	}

	public function is_enabled() {

		if ( AE_ACF ) {
			return true;
		}

		return false;
	}


	public function get_title() {
		return __( 'AE - ACF Fields', 'ae-pro' );
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
			'group',
			'repeater',
			'flexible content',
		];
	}

	protected function register_skins() {

		$this->add_skin( new Skins\Skin_Text( $this ) );
		$this->add_skin( new Skins\Skin_Text_Area( $this ) );
		$this->add_skin( new Skins\Skin_Wysiwyg( $this ) );
		$this->add_skin( new Skins\Skin_Number( $this ) );
		$this->add_skin( new Skins\Skin_Url( $this ) );
		$this->add_skin( new Skins\Skin_Date( $this ) );
		$this->add_skin( new Skins\Skin_Select( $this ) );
		$this->add_skin( new Skins\Skin_Checkbox( $this ) );
		$this->add_skin( new Skins\Skin_Radio( $this ) );
		$this->add_skin( new Skins\Skin_Button_Group( $this ) );
		$this->add_skin( new Skins\Skin_True_False( $this ) );
		$this->add_skin( new Skins\Skin_File( $this ) );
		$this->add_skin( new Skins\Skin_Email( $this ) );
		$this->add_skin( new Skins\Skin_Image( $this ) );
		$this->add_skin( new Skins\Skin_Taxonomy( $this ) );
	}

	public function register_controls() {

		$post      = get_post();
		$post_type = get_post_type();

		if ( ! empty( $post ) ) {
			$post_meta   = get_post_meta( $post->ID );
			$render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
			$field_type  = get_post_meta( $post->ID, 'ae_acf_field_type', true );

		}
		$repeater_arr = Aepro::$_helper->is_repeater_block_layout();
		$repeater     = '';
		$is_repeater  = '';
		if ( isset( $repeater_arr['field'] ) ) {
			$repeater = $repeater_arr['field'];
			if ( $repeater_arr['is_repeater'] ) {
				$is_repeater = 'repeater';
			}
		}
		$this->start_controls_section(
			'general',
			[
				'label' => __( 'General', 'ae-pro' ),
			]
		);

		$this->add_control(
			'field_type',
			[
				'label'   => __( 'Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'post'   => __( 'Post Field', 'ae-pro' ),
					'term'   => __( 'Term Field', 'ae-pro' ),
					'user'   => __( 'User', 'ae-pro' ),
					'option' => __( 'Option', 'ae-pro' ),
				],
				'default' => 'post',
			]
		);

		$parent_field_type = [
			''         => __( 'None', 'ae-pro' ),
			'repeater' => __( 'Repeater Field', 'ae-pro' ),
			'group'    => __( 'Group Field', 'ae-pro' ),
		];
		if ( AE_ACF_PRO ) {
			if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
				$parent_field_type['flexible'] = __( 'Flexible Field', 'ae-pro' );
			}
		}
		$this->add_control(
			'is_sub_field',
			[
				'label'       => __( 'Parent Field Type', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $parent_field_type,
				'description' => __( 'Choose if you want to fetch data from a sub field', 'ae-pro' ),
				'condition'   => [
					'field_type' => [ 'post', 'option' ],
				],
			]
		);
		if ( $field_type === 'flexible_content' && $render_mode === 'acf_repeater_layout' ) {
			$this->add_control(
				'option_alert',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'ae-editor-note',
					'raw'             => __( '<span style="color:red; font-weight:bold;">Note: </span> Only Flexible Field is supported for Option’s field.', 'ae-pro' ),
					'separator'       => 'none',
					'condition'       => [
						'field_type' => 'option',
					],
				]
			);
		}

		$this->add_control(
			'parent_field',
			[
				'label'     => __( 'Parent Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'is_sub_field' => [ 'group' ],
					'field_type'   => [ 'post', 'option' ],
				],
			]
		);

		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$this->add_control(
			'repeater_field',
			[
				'label'     => __( 'Repeater Field', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'groups'    => $repeater_fields,
				'condition' => [
					'is_sub_field' => [ 'repeater' ],
					'field_type'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'repeater_sub_field',
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'repeater_field',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'is_sub_field' => [ 'repeater' ],
					'field_type'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'flexible_field',
			[
				'label'       => __( 'Parent Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => Aepro::$_helper->ae_get_flexible_content_fields(),
				'description' => __( 'Choose parent flexible field', 'ae-pro' ),
				'condition'   => [
					'is_sub_field' => [ 'flexible' ],
					'field_type'   => [ 'post', 'option' ],
				],
			]
		);

		$this->add_control(
			'field_name',
			[
				'label'       => __( 'Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'Enter your acf field name',
				'condition'   => [
					'is_sub_field!' => [ 'flexible', 'repeater' ],
				],
			]
		);

		$this->add_control(
			'flex_sub_field',
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'flexible_field',
				'query_type'   => 'flex-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'is_sub_field' => [ 'flexible' ],
					'field_type'   => [ 'post', 'option' ],
				],
			]
		);
		$this->end_controls_section();
	}


}
