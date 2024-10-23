<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;

class ACF_URL {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$url = $widget->get_raw_acf_field_value($settings);

		if( $settings['field_type'] === 'file' ){
			$file_data = self::get_file_data( $url );
			$url = $file_data['url'];
		}

		if( $settings['field_type'] === 'email' ){
			$field_args = $widget->get_field_args($settings);
			$subject = self::get_mailto_subject( $field_args, $settings );
			$link_text = $url;
			$url = self::get_mailto_href( $url, $subject );
		}

		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}
		if ( $url === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback' ] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}
		
		$widget->add_render_attribute( 'anchor', 'href', $url );
		$widget->add_render_attribute( 'anchor', 'class', 'ae-acf-content-wrapper' );

		$new_tab = $settings['new_tab'];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $new_tab == 1 ) {
			$widget->add_render_attribute( 'anchor', 'target', '_blank' );
		}
		
		$no_follow = $settings['nofollow'];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $no_follow == 1 ) {
			$widget->add_render_attribute( 'anchor', 'rel', 'nofollow' );
		}

		$enable_download = $settings['enable_download'];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $enable_download == 1 ) {
			$widget->add_render_attribute( 'anchor', 'download', 'download' );
		}
		// Get Link Text
		$links_to = $settings['url_links_to'];

		if( $settings['field_type'] === 'file' ){
			$links_to = $settings['file_links_to'];
		}

		if( $settings['field_type'] === 'email' ){
			$links_to = $settings['email_links_to'];
		}

		switch ( $links_to ) {

			case 'email':
				$link_text = $link_text;
				break;

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
				$link_text = $settings['static_text'];
				break;

			case 'post':
				$curr_post = Aepro::$_helper->get_demo_post_data();
				if ( isset( $curr_post ) && isset( $curr_post->ID ) ) {
					$link_text = get_the_title( $curr_post->ID );
				}
				break;

			case 'dynamic_text':
				$custom_field = $settings['custom_field_text'];

				if ( $custom_field !== '' ) {

					$field_args['field_name'] = $custom_field;
					$link_text                = AcfMaster::instance()->get_field_value( $field_args );
				}
				break;

		}

		$widget->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );

		if ( $url === '' || is_null( $url ) ) {
			$widget->add_render_attribute( 'wrapper', 'class', 'ae-hide' );
		}
		?>

		<div <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
			<a <?php echo $widget->get_render_attribute_string( 'anchor' ); ?>><?php echo esc_html( $link_text ); ?></a>
		</div>

		<?php

	}

	public static function register_controls($widget){			

		$widget->add_control(
			'url_links_to',
			[
				'label'   => __( 'Links To', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'static'       => __( 'Static Text', 'ae-pro' ),
					'post'         => __( 'Post Title', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'static',
				'condition' => [
					'field_type' => ['url']
				],
			]
		);

		$widget->add_control(
			'file_links_to',
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
				'condition' => [
					'field_type' => ['file']
				],
			]
		);

		$widget->add_control(
			'email_links_to',
			[
				'label'   => __( 'Links To', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'email'       => __( 'Email', 'ae-pro' ),
					'static'       => __( 'Static Text', 'ae-pro' ),
					'post'         => __( 'Post Title', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default' => 'static',
				'condition' => [
					'field_type' => ['email']
				],
			]
		);

		$widget->add_control(
			'static_text',
			[
				'label'     => __( 'Static Text', 'ae-pro' ), 
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Click Here', 'ae-pro' ),
				'condition' => [
					'field_type' => ['url', 'file', 'email'],
					'url_links_to' => 'static',
				],
				'conditions'          => [
					'relation' => 'and',
					'terms'    => [
						[
							'relation' => 'or',
							'terms' => [
								[
									'name'     => 'url_links_to',
									'operator' => '==',
									'value'    => 'static',
								],
								[
									'name'     => 'file_links_to',
									'operator' => '==',
									'value'    => 'static',
								],
								[
									'name'     => 'email_links_to',
									'operator' => '==',
									'value'    => 'static',
								],
							]
							],
							[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'field_type',
									'operator' => 'in',
									'value'    => ['url', 'file', 'email'],
								],
							]
						]
					],
				],
			]
		);

		$widget->add_control(
			'custom_field_text',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'conditions'          => [
					'relation' => 'and',
					'terms'    => [
						[
							'relation' => 'or',
							'terms' => [
								[
									'name'     => 'url_links_to',
									'operator' => '==',
									'value'    => 'dynamic_text',
								],
								[
									'name'     => 'file_links_to',
									'operator' => '==',
									'value'    => 'dynamic_text',
								],
								[
									'name'     => 'email_links_to',
									'operator' => '==',
									'value'    => 'dynamic_text',
								],
							]
							],
							[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'field_type',
									'operator' => 'in',
									'value'    => ['url', 'file', 'email'],
								],
							]
						]
					],
				],
			]
		);

		$widget->add_control(
			'enable_subject',
			[
				'label'        => __( 'Add Subject', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => __( 'label_off', 'ae-pro' ),
				'return_value' => 'yes',
				'condition' => [
					'field_type' => ['email']
				]
			]
		);

		$widget->add_control(
			'subject_source',
			[
				'label'     => __( 'Links To', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'static'       => __( 'Static Text', 'ae-pro' ),
					'dynamic_text' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'static',
				'condition' => [
					'field_type' => ['email'],
					'enable_subject' => 'yes',
				],
			]
		);

		$widget->add_control(
			'subject_static',
			[
				'label'     => __( 'Subject', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					'field_type' => ['email'],
					'enable_subject' => 'yes',
					'subject_source' => 'static',
				],
			]
		);

		$widget->add_control(
			'subject_dynamic',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'field_type' => ['email'],
					'enable_subject' => 'yes',
					'subject_source' => 'dynamic_text',
				],
			]
		);

		$widget->add_control(
			'new_tab',
			[
				'label'        => __( 'Open in new tab1', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition' => [
					'field_type' => ['url', 'file']
				]
			]
		);

		$widget->add_control(
			'enable_download',
			[
				'label'        => __( 'Enable Download', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition' => [
					'field_type' => ['url', 'file']
				]
			]
		);

		$widget->add_control(
			'nofollow',
			[
				'label'        => __( 'Add nofollow', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition' => [
					'field_type' => ['url', 'file']
				]
			]
		);
	}

	public static function get_file_data( $file ) {
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

	public static function get_mailto_href( $email, $subject ) {

		$parts = [];
		$href  = 'mailto:' . $email;

		if ( $subject !== '' ) {
			$parts['subject'] = 'subject=' . $subject;
		}

		if ( is_array( $parts ) && count( $parts ) ) {
			$href = $href . '?' . implode( '&', $parts );
		}

		return $href;
	}

	public static function get_mailto_subject( $field_args, $settings ) {

		$subject = '';

		$enable_subject = $settings['enable_subject'];

		if ( $enable_subject ) {

			// subject source
			$subject_source = $settings['subject_source'];
			if ( $subject_source === 'static' ) {

				$subject = $settings['subject_static'];

			} elseif ( $subject_source === 'dynamic_text' ) {

				$field_args['field_name'] = $settings['subject_dynamic'];

				$subject = AcfMaster::instance()->get_field_value( $field_args );

			}
		}

		return $subject;
	}
}