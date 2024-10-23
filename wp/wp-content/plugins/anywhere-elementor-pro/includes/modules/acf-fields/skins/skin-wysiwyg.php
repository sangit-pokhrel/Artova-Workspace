<?php

namespace Aepro\Modules\AcfFields\Skins;

use Aepro\Modules\AcfFields;
use Aepro\Classes\AcfMaster;
use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;


class Skin_Wysiwyg extends Skin_Text_Area {

	public function get_id() {
		return 'wysiwyg';
	}

	public function get_title() {
		return __( 'WYSIWYG', 'ae-pro' );
	}
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {

		parent::_register_controls_actions();
		remove_action( 'elementor/element/ae-acf/text_area_section_unfold_style/after_section_end', [ $this, 'register_fallback_style' ] );
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'add_unfold_section' ] );
		add_action( 'elementor/element/ae-acf/general/after_section_end', [ $this, 'manage_controls' ] );
		add_action( 'elementor/element/ae-acf/wysiwyg_section_unfold_style/after_section_end', [ $this, 'register_fallback_style' ] );
	}

	public function manage_controls() {
		$this->remove_control( 'prefix' );
		$this->remove_control( 'suffix' );
		$this->remove_control( 'links_to' );
	}

	public function render() {

		$settings = $this->parent->get_settings();

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

		$unfold      = $this->get_instance_value( 'enable_unfold' );
		$placeholder = $this->get_instance_value( 'placeholder' );
		$text        = AcfMaster::instance()->get_field_value( $field_args );
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'preview_fallback' ) == 'yes' ) {
				$this->render_fallback_content( $settings );
			}
		}
		if ( $text === '' && $placeholder === '' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'enable_fallback' ) != 'yes' ) {
				return;
			} else {
				$this->render_fallback_content( $settings );
				return;
			}
		}
		if ( ( $text === '' || is_null( $text ) ) && ( $placeholder !== '' && ! is_null( $placeholder ) ) ) {
			$text   = $placeholder;
			$unfold = '';
		}

		$html_tag = $this->get_instance_value( 'html_tag' );

		$this->parent->add_render_attribute( 'title-class', 'class', 'ae-acf-content-wrapper' );

		$this->parent->add_render_attribute( 'wrapper-class', 'class', 'ae-acf-wrapper' );

		$this->parent->add_render_attribute( 'wrapper-class', 'class', 'ae-acf-unfold-' . $unfold );

		if ( $text === '' ) {
			$this->parent->add_render_attribute( 'wrapper-class', 'class', 'ae-hide' );
		}

		// Process Content
		$text = $this->process_content( $text );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->get_instance_value( 'strip_text' ) == 'yes' ) {
			$strip_mode   = $this->get_instance_value( 'strip_mode' );
			$strip_size   = $this->get_instance_value( 'strip_size' );
			$strip_append = $this->get_instance_value( 'strip_append' );
			if ( $strip_mode === 'word' ) {
				$text = wp_trim_words( $text, $strip_size, $strip_append );
			} else {
				$text = Aepro::$_helper->ae_trim_letters( $text, 0, $strip_size, $strip_append );
			}
			$text = wp_strip_all_tags($text);
		}

		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'wrapper-class' ); ?>>
		<?php
			echo sprintf( '<%1$s itemprop="name" %2$s>%3$s</%1$s>', esc_html( $html_tag ), $this->parent->get_render_attribute_string( 'title-class' ), $text );

		if ( $unfold === 'yes' ) {
			$this->getFoldUnfoldButtonHtml();
		}

		?>
		</div>
		<?php
	}

}
