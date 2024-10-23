<?php
namespace Aepro\Modules\AcfRepeater\Skins;

use Aepro\Frontend;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Base\Widget_Base;
use Aepro\Aepro;
use Elementor\Core\Schemes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		add_action( 'elementor/element/ae-acf-repeater/general/before_section_end', [ $this, 'register_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;
	}

	public function generate_tabs_output( $settings ) {
		$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );

		$with_css = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}

		$post_data = Aepro::$_helper->get_demo_post_data();

		$this->parent->add_render_attribute(
			'ae-acf-repeater-tabs',
			[
				'class'       => 'ae-acf-repeater-tabs',
				'role'        => 'tablist',
				'data-tab-id' => wp_rand( 1234 ),
			]
		);

		if ( $settings['tab_state'] === 'open_specific' ) {
			$this->parent->add_render_attribute( 'ae-acf-repeater-tabs', 'data-active-tab', $settings['specific_tab'] );
		}

		$tabs_content_space = $this->get_instance_value( 'tabs_content_space' );
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $tabs_content_space['size'] != 0 ) {
			$this->parent->add_render_attribute(
				'ae-acf-repeater-tabs',
				[
					'class' => 'space-between-tab-content',
				]
			);
		};

		if ( $this->get_instance_value( 'advance_style' ) === 'yes' ) {
			$this->parent->add_render_attribute(
				'ae-acf-repeater-tabs',
				[
					'class' => 'advance-style',
				]
			);
		}

		$this->parent->add_render_attribute(
			'ae-acf-repeater-tabs-wrapper',
			[
				'class' => 'ae-acf-repeater-tabs-wrapper',
				'role'  => 'tab',
			]
		);

		$this->parent->add_render_attribute(
			'ae-acf-repeater-tabs-content-wrapper',
			[
				'class' => 'ae-acf-repeater-tabs-content-wrapper',
				'role'  => 'tabpanel',
			]
		);

		$repeater_data = Aepro::$_helper->get_repeater_data( $settings, $post_data->ID );

		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() && isset( $repeater_data['parents'] ) ) {
			$loop = acf_get_loop();

			if ( $loop === false ) {

				foreach ( $repeater_data['parents'] as $parent ) {
					have_rows( $parent, $repeater_data['repeater_type'] );
					the_row();
				}
			}
		}

		if ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {

			$this->parent->get_widget_title_html();

			Frontend::$_in_repeater_block = true; ?>
			<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tabs' ); ?>>
				<?php
				$counter = 1;
				?>
				<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tabs-wrapper' ); ?>>
					<?php
					while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
						the_row();
						$this->parent->set_render_attribute(
							'ae-acf-repeater-tab-desktop-title',
							[
								'class'    => 'ae-acf-repeater-tab-title ae-acf-repeater-tab-desktop-title',
								'data-tab' => $counter,
							]
						);
						$data_hashtag = '';
						if ( $settings['enable_url_hashtag'] === 'yes' ) {
							$hashtag_type = $settings['fragment_type'];
							switch ( $hashtag_type ) {
								case 'custom_field':
									$data_hashtag = get_sub_field( $settings['fragment_custom_field'] );
									break;
								default:
									$data_hashtag = 'tab-' . $counter;
							}

							$this->parent->set_render_attribute(
								'ae-acf-repeater-tab-desktop-title',
								[
									'data-hashtag' => $data_hashtag,
								]
							);
						}
						?>
					<<?php echo $settings['title_html_tag']; ?> <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tab-desktop-title' ); ?>>
						<?php echo get_sub_field( $settings['tab_title'], $repeater_data['repeater_type'] ); ?>
				</<?php echo $settings['title_html_tag']; ?>>
						<?php
						$counter++;
					}
					?>
			</div>

			<?php
			$counter = 1;
			?>
			<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tabs-content-wrapper' ); ?>>
				<?php
				while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
					the_row();
					$this->parent->set_render_attribute(
						'ae-acf-repeater-tab-mobile-title',
						[
							'class'    => 'ae-acf-repeater-tab-title ae-acf-repeater-tab-mobile-title',
							'data-tab' => $counter,
						]
					);
					$this->parent->set_render_attribute(
						'ae-acf-repeater-tab-content',
						[
							'class'    => 'ae-acf-repeater-tab-content elementor-clearfix',
							'data-tab' => $counter,
						]
					);

					if ( $settings['enable_url_hashtag'] === 'yes' ) {

						$hashtag_type = $settings['fragment_type'];
						switch ( $hashtag_type ) {
							case 'custom_field':
								$data_hashtag = get_sub_field( $settings['fragment_custom_field'] );
								break;
							default:
								$data_hashtag = 'tab-' . $counter;
						}
						if ( $data_hashtag !== '' ) {
							$this->parent->set_render_attribute(
								'ae-acf-repeater-tab-mobile-title',
								[
									'data-hashtag' => $data_hashtag,
								]
							);
						}
					}
					?>
					<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tab-mobile-title' ); ?>>
						<?php echo get_sub_field( $settings['tab_title'], $repeater_data['repeater_type'] ); ?>
					</div>
					<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-tab-content' ); ?>>
						<?php echo Plugin::instance()->frontend->get_builder_content( $settings['template'], $with_css ); ?>
					</div>
					<?php
					$counter++;
				}
				?>
			</div>
			</div>
			<?php
			Frontend::$_in_repeater_block = false; }
	}

	public function generate_accordion_output( $settings ) {

		$post_data            = Aepro::$_helper->get_demo_post_data();
		$index                = wp_rand();
		$tab_count            = 0;
		$accordion_state      = $settings['accordion_state'];
		$has_icon             = ( ! empty( $settings['selected_icon']['value'] ) );
		$toggle_button        = $settings['enable_toggle_button'];
		$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );
		$with_css             = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}
		$transition_speed = $this->get_instance_value( 'accordion_transition_speed' );

		$this->parent->add_render_attribute(
			'ae-acf-repeater-accordion',
			[
				'class'                 => 'elementor-accordion ae-accordion',
				'role'                  => 'tablist',
				'data-transition-speed' => $transition_speed['size'],
				'data-tab-id'           => wp_rand( 1234 ),
			]
		);
		if ( $toggle_button === 'yes' ) {
			$this->parent->add_render_attribute(
				'ae-acf-repeater-accordion',
				[
					'data-toggle-button' => 'yes',
				]
			);
		}
		$repeater_data = Aepro::$_helper->get_repeater_data( $settings, $post_data->ID );

		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() && isset( $repeater_data['parents'] ) ) {
			$loop = acf_get_loop();

			if ( $loop === false ) {

				foreach ( $repeater_data['parents'] as $parent ) {
					have_rows( $parent, $repeater_data['repeater_type'] );
					the_row();
				}
			}
		}

		if ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {

			$this->parent->get_widget_title_html();

			Frontend::$_in_repeater_block = true;
			?>
		<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-accordion' ); ?>>
			<?php
			if ( $toggle_button === 'yes' ) {
				?>
						<div class="ae-accordion-tb-wrapper">
							<button class="ae-accordion-toggle-button expand" data-role="expand"><?php echo $settings['expand_button_text']; ?></button>
							<span> <?php echo $settings['toggle_button_separator']; ?> </span>
							<button class="ae-accordion-toggle-button collapse" data-role="collapse"><?php echo $settings['collapse_button_text']; ?></button>
						</div>
					<?php
			}
			?>
			<?php
			while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
				the_row();

				$tab_no = $index + 1;
				++$tab_count;

				$title_class   = 'elementor-tab-title ae-tab-title ae-acf-repeater-accordion';
				$content_class = 'elementor-tab-content elementor-clearfix ae-tab-content ae-acf-repeater-accordion';

				if ( $tab_count === 1 && $accordion_state === 'default' ) {
					$title_class   = $title_class . ' ae-active';
					$content_class = $content_class . ' ae-active';
				} elseif ( $accordion_state === 'all_open' ) {
					$title_class   = $title_class . ' ae-active';
					$content_class = $content_class . ' ae-active';
				} elseif ( $accordion_state === 'open_specific' ) {
					$specific_tab = $settings['specific_tab'];
					if ( $tab_count === $specific_tab ) {
						$title_class   = $title_class . ' ae-active';
						$content_class = $content_class . ' ae-active';
					}
				}

				$this->parent->set_render_attribute(
					'ae-acf-repeater-accordion-title',
					[
						'id'            => 'elementor-tab-title-' . $tab_no . $tab_count,
						't_id'          => $tab_no . $tab_count,
						'class'         => $title_class,
						'data-tab'      => $tab_count,
						'role'          => 'tab',
						'aria-controls' => 'elementor-tab-content-' . $tab_no . $tab_count,
					]
				);

				$this->parent->set_render_attribute(
					'ae-acf-repeater-accordion-content',
					[
						'id'              => 'elementor-tab-content-' . $tab_no . $tab_count,
						't_id'            => $tab_no . $tab_count,
						'class'           => $content_class,
						'data-tab'        => $tab_count,
						'role'            => 'tabpanel',
						'aria-labelledby' => 'elementor-tab-title-' . $tab_no . $tab_count,
					]
				);

				if ( $settings['enable_url_hashtag'] === 'yes' ) {
					$data_hashtag = '';
					$hashtag_type = $settings['fragment_type'];
					switch ( $hashtag_type ) {
						case 'custom_field':
							$data_hashtag = get_sub_field( $settings['fragment_custom_field'] );
							break;
						default:
							$data_hashtag = 'tab-' . $tab_count;
					}
					if ( $data_hashtag !== '' ) {
						$this->parent->set_render_attribute(
							'ae-acf-repeater-accordion-title',
							[
								'data-hashtag' => $data_hashtag,
							]
						);
					}
				}
				?>
				<div class="elementor-accordion-item ae-accordion-item">
				<<?php echo $settings['title_html_tag']; ?> <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-accordion-title' ); ?>>
				<?php if ( $has_icon ) : ?>
					<span class="elementor-accordion-icon elementor-accordion-icon-<?php echo esc_attr( $this->get_instance_value( 'icon_align' ) ); ?>" aria-hidden="true">
								<span class="elementor-accordion-icon-closed ae-accordion-icon-closed"><?php Icons_Manager::render_icon( $settings['selected_icon'] ); ?></span>
								<span class="elementor-accordion-icon-opened ae-accordion-icon-opened"><?php Icons_Manager::render_icon( $settings['selected_active_icon'] ); ?></span>
							</span>
				<?php endif; ?>
				<a href="#"><?php echo get_sub_field( $settings['tab_title'], $repeater_data['repeater_type'] ); ?></a>
				</<?php echo $settings['title_html_tag']; ?>>
				<div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-accordion-content' ); ?>>
					<?php echo Plugin::instance()->frontend->get_builder_content( $settings['template'], $with_css ); ?>
				</div>
				</div>
			<?php } ?>
			</div>
			<?php
			Frontend::$_in_repeater_block = false;
		}
	}


}
