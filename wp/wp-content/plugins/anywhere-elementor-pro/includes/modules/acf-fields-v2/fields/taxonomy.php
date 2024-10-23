<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Choice;
use ElementorPro\Modules\DynamicTags\ACF\Tags\ACF_COLOR;

class ACF_Taxonomy extends ACF_Choice {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	//All Styles Controls are from ACF_Choice

	public static function render($widget, $settings) {
		$selected = $widget->get_raw_acf_field_value($settings);
		if ( empty( $selected ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}
		$separator = $settings['separator'];
		$divider   = $settings['divider'];
		$layout    = $settings['layout'];

		$widget->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );
		$widget->add_render_attribute( 'wrapper', 'class', 'ae-list-' . $layout );
		$widget->add_render_attribute( 'wrapper', 'class', 'ae-icon-list-items' );

		if ( $separator !== '' && $divider === '' ) {
			$widget->add_render_attribute( 'wrapper', 'class', 'ae-custom-sep' );
		}

		if ( $layout === 'vertical' ) {
			$separator = '';
		}

		?>
		<ul <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			$icon = $settings['icon'];
			if ( is_array( $selected ) ) {
				// multi items are selected

				foreach ( $selected as $label ) {
					$striked    = false;  // just assuming
					$icon_class = '';
					$term       = get_term( $label );
					// Selected/Checked item
					$icon_class = $icon;
					$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					if ( $settings['enable_link'] === 'yes' ) {
						$link = get_term_link( $term );
						$widget->set_render_attribute( 'anchor', 'href', $link );
					}

					?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
							<?php if ( $settings['enable_link'] === 'yes' ) { ?>
								<a <?php echo $widget->get_render_attribute_string( 'anchor' ); ?>>
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
							<?php if ( $settings['enable_link'] === 'yes' ) { ?>
								</a>
							<?php } ?>
						</div>
					</li>
					<?php
				}
			} else {

				$term = get_term( $selected );

				$icon_class = $icon;
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
				if ( $settings['enable_link'] === 'yes' ) {
					$link = get_term_link( $term );
					$widget->set_render_attribute( 'anchor', 'href', $link );
				}
				?>

				<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
					<div class="ae-icon-list-item-inner">
						<?php if ( $settings['enable_link'] === 'yes' ) { ?>
							<a <?php echo $widget->get_render_attribute_string( 'anchor' ); ?>>
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
						<?php if ( $settings['enable_link'] === 'yes' ) { ?>
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