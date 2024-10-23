<?php

namespace Aepro\Modules\AcfFields\Skins;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Classes\AcfMaster;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as EPlugin;



class Skin_File extends Skin_Url {

	public function get_id() {
		return 'file';
	}

	public function get_title() {
		return __( 'File', 'ae-pro' );
	}
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		remove_action( 'elementor/element/ae-acf/url_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
		add_action( 'elementor/element/ae-acf/file_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->add_control(
			'links_to',
			[
				'label'   => __( 'Links To', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'title'        => __( 'Title', 'ae-pro' ),
					'caption'      => __( 'Caption', 'ae-pro' ),
					'filename'     => __( 'File Name', 'ae-pro' ),
					'static'       => __( 'Static Text', 'ae-pro' ),
					'post'         => __( 'Post Title', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'static',
			]
		);

		$this->add_control(
			'static_text',
			[
				'label'     => __( 'Static Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Download Now', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'links_to' ) => 'static',
				],
			]
		);

		$this->add_control(
			'custom_field_text',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					$this->get_control_id( 'links_to' ) => 'dynamic_text',
				],
			]
		);

		$this->add_control(
			'new_tab',
			[
				'label'        => __( 'Open in new tab', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
			]
		);

		$this->add_control(
			'enable_download',
			[
				'label'        => __( 'Enable Download', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
			]
		);

		$this->add_control(
			'nofollow',
			[
				'label'        => __( 'Add nofollow', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label'     => __( 'Align', 'ae-pro' ),
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
					'{{WRAPPER}}'   => 'text-align: {{VALUE}}',
					'{{WRAPPER}} a' => 'display: inline-block',
				],
			]
		);
	}

	public function render() {

		$settings  = $this->parent->get_settings_for_display();
		$link_text = '';

		$field_args = [
			'field_type'   => $settings['field_type'],
			'is_sub_field' => $settings['is_sub_field'],
		];

		$accepted_parent_fields = [ 'repeater', 'group', 'flexible' ];
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $settings['is_sub_field'], $accepted_parent_fields ) ) {
			switch ( $settings['is_sub_field'] ) {

				case 'flexible':
					$field_args['field_name']                     = $settings['flex_sub_field'];
									$field_args['flexible_field'] = $settings['flexible_field'];
					break;

				case 'repeater':
					$field_args['field_name']                   = $settings['repeater_sub_field'];
									$field_args['parent_field'] = $settings['repeater_field'];
					break;

				case 'group':
					$field_args['field_name']                   = $settings['field_name'];
									$field_args['parent_field'] = $settings['parent_field'];
					break;
			}
		} else {
			$field_args['field_name'] = $settings['field_name'];
		}

		$file = AcfMaster::instance()->get_field_value( $field_args );

		$file_data = $this->get_file_data( $file );
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'preview_fallback' ) == 'yes' ) {
				$this->render_fallback_content( $settings );
			}
		}
		if ( empty( $file_data ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'enable_fallback' ) != 'yes' ) {
				return;
			} else {
				$this->render_fallback_content( $settings );
				return;
			}
		}
		$this->parent->add_render_attribute( 'anchor', 'href', $file_data['url'] );

		$new_tab = $this->get_instance_value( 'new_tab' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $new_tab == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'target', '_blank' );
		}

		$no_follow = $this->get_instance_value( 'nofollow' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $no_follow == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'rel', 'nofollow' );
		}

		$enable_download = $this->get_instance_value( 'enable_download' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $enable_download == 1 ) {
			$this->parent->add_render_attribute( 'anchor', 'download', '' );
		}

		// Get Link Text
		$links_to = $this->get_instance_value( 'links_to' );

		switch ( $links_to ) {

			case 'title':
				$link_text = $file_data['title'];
				break;

			case 'caption':
				$link_text = $file_data['caption'];
				break;

			case 'filename':
				$link_text = $file_data['filename'];
				break;

			case 'static':
				$link_text = $this->get_instance_value( 'static_text' );
				break;

			case 'post':
				$curr_post = Aepro::$_helper->get_demo_post_data();
				if ( isset( $curr_post ) && isset( $curr_post->ID ) ) {
					$link_text = get_the_title( $curr_post->ID );
				}
				break;

			case 'dynamic_text':
				$custom_field = $this->get_instance_value( 'custom_field_text' );

				if ( $custom_field !== '' ) {

					$field_args['field_name'] = $custom_field;
					$link_text                = AcfMaster::instance()->get_field_value( $field_args );
				}
				break;

		}

		?>

		<a <?php echo $this->parent->get_render_attribute_string( 'anchor' ); ?>><?php echo esc_html( $link_text ); ?></a>
		<?php
	}

	public function get_file_data( $file ) {
		if ( empty( $file ) ) {
			return;
		}
		$file_data = false;
		// Get attachemnt info
		if ( is_numeric( $file ) ) {
			$file_data = acf_get_attachment( $file );
		} elseif ( is_string( $file ) ) {
			$file_id   = attachment_url_to_postid( $file );
			$file_data = acf_get_attachment( $file_id );
		} else {
			$file_id   = $file['ID'];
			$file_data = acf_get_attachment( $file_id );
		}

		return $file_data;
	}






}
