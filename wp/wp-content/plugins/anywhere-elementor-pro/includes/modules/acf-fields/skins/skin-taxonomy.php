<?php

namespace Aepro\Modules\AcfFields\Skins;

use Aepro\Modules\AcfFields;
use Aepro\Classes\AcfMaster;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;


class Skin_Taxonomy extends Skin_Select {

	public function get_id() {
		return 'taxonomy';
	}

	public function get_title() {
		return __( 'Taxonomy', 'ae-pro' );
	}

	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		remove_action( 'elementor/element/ae-acf/select_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
		add_action( 'elementor/element/ae-acf/taxonomy_general-style/after_section_end', [ $this, 'register_fallback_style' ] );
	}


	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		if(!$this->load_skin_controls(['select', 'button_group', 'checkbox', 'radio', 'taxonomy' ])){
			return;
		}
		
		parent::register_select_controls();

		$this->remove_control(
			'data_type'
		);

		$this->remove_control(
			'show_all_choices'
		);

		$this->remove_control(
			'icon_unchecked'
		);

		$this->add_control(
			'enable_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);
	}

	public function render() {

		$list_items = [];

		$settings = $this->parent->get_settings();

		$field_args = [
			'field_type'   => $settings['field_type'],
			'is_sub_field' => $settings['is_sub_field'],
		];

		$accepted_parent_fields = [ 'repeater', 'group', 'flexible' ];
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $settings['is_sub_field'], $accepted_parent_fields ) ) {
			$field_args['_skin'] = $settings['_skin'];
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

		$selected = AcfMaster::instance()->get_field_value( $field_args );
		if ( empty( $selected ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $this->get_instance_value( 'enable_fallback' ) != 'yes' ) {
				return;
			} else {
				$this->render_fallback_content( $settings );
				return;
			}
		}
		$separator = $this->get_instance_value( 'separator' );
		$divider   = $this->get_instance_value( 'divider' );
		$layout    = $this->get_instance_value( 'layout' );

		$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );
		$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-list-' . $layout );
		$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-icon-list-items' );

		if ( $separator !== '' && $divider === '' ) {
			$this->parent->add_render_attribute( 'wrapper', 'class', 'ae-custom-sep' );
		}

		if ( $layout === 'vertical' ) {
			$separator = '';
		}

		?>
		<ul <?php echo $this->parent->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			$icon = $this->get_instance_value( 'icon' );
			if ( is_array( $selected ) ) {
				// multi items are selected

				foreach ( $selected as $label ) {
					$striked    = false;  // just assuming
					$icon_class = '';
					$term       = get_term( $label );
					// Selected/Checked item
					$icon_class = $icon;
					$this->parent->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) {
						$link = get_term_link( $term );
						$this->parent->set_render_attribute( 'anchor', 'href', $link );
					}

					?>

					<li <?php echo $this->parent->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
							<?php if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) { ?>
								<a <?php echo $this->parent->get_render_attribute_string( 'anchor' ); ?>>
							<?php } ?>
							<?php
							if ( $icon_class !== '' ) {
								?>
								<span class="ae-icon-list-icon">
									<i class="<?php echo esc_html( $icon_class ); ?>"></i>
								</span>
								<?php
							}
							?>
							<span class="ae-icon-list-text">
								<?php echo esc_html( $term->name ); ?>
							</span>
							<?php if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) { ?>
								</a>
							<?php } ?>
						</div>
					</li>
					<?php
				}
			} else {

				$term = get_term( $selected );

				$icon_class = $icon;
				$this->parent->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
				if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) {
					$link = get_term_link( $term );
					$this->parent->set_render_attribute( 'anchor', 'href', $link );
				}
				?>

				<li <?php echo $this->parent->get_render_attribute_string( 'item_wrapper' ); ?>>
					<div class="ae-icon-list-item-inner">
						<?php if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) { ?>
							<a <?php echo $this->parent->get_render_attribute_string( 'anchor' ); ?>>
						<?php } ?>
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
								<i class="<?php echo esc_html( $icon_class ); ?>"></i>
							</span>
							<?php
						}
						?>
						<span class="ae-icon-list-text">
							<?php echo esc_html( $term->name ); ?>
						</span>
						<?php if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) { ?>
							</a>
						<?php } ?>
					</div>
				</li>

				<?php

			}
			?>
		</ul>
		<?php
	}

}
