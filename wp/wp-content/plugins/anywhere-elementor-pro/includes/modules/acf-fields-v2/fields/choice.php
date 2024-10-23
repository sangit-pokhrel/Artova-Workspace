<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class ACF_Choice {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function render($widget, $settings) {

		$list_items = [];

		$field_args = [
			'field_type'   => $settings['source'],
			'is_sub_field' => $settings['is_sub_field'],
		];

		$accepted_parent_fields = [ 'repeater', 'group', 'flexible' ];
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $settings['is_sub_field'], $accepted_parent_fields ) ) {
			$field_args['_skin'] = $settings['_skin'];
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['is_sub_field'] == 'flexible' ) {
				$field_args['field_name']     = $settings['flex_sub_field'];
				$field_args['flexible_field'] = $settings['flexible_field'];
				if ( ! empty( $settings['flexible_field'] ) ) {
					$field_data = explode( ':', $field_args['flexible_field'] );
					if ( $field_data[0] === 'option' ) {
						$parent_field_name = $field_data[2];
						$layout            = $field_data[3];
					} else {
						$parent_field_name = $field_data[1];
						$layout            = $field_data[2];
					}
					$field_args['parent_field'] = $parent_field_name;
					$field_args['layout']       = $layout;
				}
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			} elseif ( $settings['is_sub_field'] == 'repeater' ) {
				$field_args['field_name']   = $settings['repeater_sub_field'];
				$field_args['parent_field'] = $settings['repeater_field'];
			} else {
				$field_args['field_name']   = $settings['field_name'];
				$field_args['parent_field'] = $settings['parent_field'];
			}
		} else {
			$field_args['field_name'] = $settings['field_name'];
		}

		if ( $settings['source'] === 'term' ) {
			$term         = Aepro::$_helper->get_preview_term_data();
			$field_object = AcfMaster::instance()->get_field_object( $field_args, $term );
		} elseif ( $settings['source'] === 'post' ) {
			$post         = Aepro::$_helper->get_demo_post_data();
			$field_object = AcfMaster::instance()->get_field_object( $field_args, $post->ID );
		} elseif ( $settings['source'] === 'option' ) {
			$field_object = AcfMaster::instance()->get_field_object( $field_args, 'option' );
		} elseif ( $settings['source'] === 'user' ) {
			$author       = Aepro::$_helper->get_preview_author_data();
			$field_object = AcfMaster::instance()->get_field_object( $field_args, 'user_' . $author['prev_author_id'] );
		}

		if ( empty( $field_object ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}

		$return_format = $field_object['return_format'];
		$selected      = AcfMaster::instance()->get_field_value( $field_args );
		$value         = [];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $field_object['type'] === 'checkbox' && $field_object['allow_custom'] == 1 && $field_object['save_custom'] == 0 ) {
			if ( ! empty( $selected ) ) {
				switch ( $return_format ) {
					case 'array':
						$is_nested_array = false;
						if ( array_key_exists( 0, $selected ) ) {
							$is_nested_array = true;
						}

						if ( $is_nested_array ) {
							foreach ( $selected as $select ) {
								$value[] = $select['value'];
							}
						} else {
							$value[] = $selected['value'];
						}
						break;
					default:
						foreach ( $selected as $key => $select ) {
									$value[] = $select;
						}
				}
			}
		} else {
			switch ( $return_format ) {
				case 'label':
					foreach ( $field_object['choices'] as $key => $label ) {
						if ( is_array( $selected ) ) {
							if ( in_array( $label, $selected, true ) ) {
								$value[] = $key;
							}
						} else {
							if ( $label === $selected ) {
								$value[] = $key;
							}
						}
					}

					break;
				case 'array':
					if ( empty( $selected ) ) {
						$value = [];
						break;
					}

					$is_nested_array = false;
					if ( array_key_exists( 0, $selected ) ) {
						$is_nested_array = true;
					}

					if ( $is_nested_array ) {
						foreach ( $selected as $select ) {
							$value[] = $select['value'];
						}
					} else {
						$value[] = $selected['value'];
					}
					break;
				default:
					foreach ( $field_object['choices'] as $key => $label ) {
						if ( is_array( $selected ) ) {
							if ( in_array( $key, $selected, true ) ) {
								$value[] = $key;
							}
						} else {
							if ( $key === $selected ) {
								$value[] = $key;
							}
						}
					}
			}
		}

		$data_type        = $settings['data_type'];
		$show_all_choices = $settings['show_all_choices'];
		$separator        = $settings['separator'];
		$divider          = $settings['divider'];
		$layout           = $settings['layout'];

		$widget->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );
		$widget->add_render_attribute( 'wrapper', 'class', 'ae-list-' . $layout );
		$widget->add_render_attribute( 'wrapper', 'class', 'ae-icon-list-items' );

		if ( $separator !== '' && $divider === '' ) {
			$widget->add_render_attribute( 'wrapper', 'class', 'ae-custom-sep' );
		}

		if ( $layout === 'vertical' ) {
			$separator = '';
		}
		if ( EPlugin::$instance->editor->is_edit_mode() ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['preview_fallback'] == 'yes' ) {
				$widget->render_fallback_content( $settings );
			}
		}

		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( empty( $value ) && $show_all_choices != 'yes' ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}

		if ( ! empty( $field_object ) ) {

			?>
		<ul <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $field_object['type'] === 'checkbox' && $field_object['allow_custom'] == 1 && $field_object['save_custom'] == 0 ) {
					self::show_custom_options( $value, $widget, $settings );
			} else {
					// show key
				if ( $data_type === 'key' ) {

					self::show_key( $value, $show_all_choices, $field_object, $widget, $settings );
				} else {
					self::show_label( $value, $show_all_choices, $field_object, $widget, $settings );
				}
			}

			?>
		</ul>
			<?php
		}
	}

	public static function show_key( $selected, $show_all_choices, $field_object, $widget, $settings ) {

		$icon           = $settings['icon'];
		$icon_unchecked = $settings['icon_unchecked'];

		$list_items = [];
		if ( $show_all_choices === 'yes' ) {

			if ( is_array( $selected ) ) {
				// multi items are selected

				foreach ( $field_object['choices'] as $key => $label ) {
					$striked    = false;  // just assuming
					$icon_class = '';
					if ( in_array( $key, $selected, true ) ) {
						// Selected/Checked item
						$icon_class = $icon;
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					} else {
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-no-select ae-icon-list-item' );
						$icon_class = $icon_unchecked;
					}

					?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
								<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
								<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $key; ?>
						</span>
						</div>
					</li>

					<?php

				}
			} else {

				foreach ( $field_object['choices'] as $key => $label ) {

					if ( $key === $selected ) {
						// Selected/Checked item
						$icon_class = $icon;
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					} else {
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-no-select ae-icon-list-item' );
						$icon_class = $icon_unchecked;
					}

					?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
									<i class="<?php echo ( $icon_class ); ?>"></i>
								</span>
							<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $key; ?>
						</span>
						</div>
					</li>

					<?php
					if ( $key === $selected ) {
						$list_items[] = '<span>' . $key . '</span>';
					} else {
						$list_items[] = '<span class="ae-no-select">' . $key . '</span>';
					}
				}
			}
		} else {

			if ( is_array( $selected ) ) {
				// multi items are selected

				$icon_class = $icon;
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );

				foreach ( $field_object['choices'] as $key => $label ) {
					if ( in_array( $key, $selected, true ) ) {

						?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
							<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $key; ?>
						</span>
						</div>
					</li>

						<?php
					}
				}
			} else {

				$icon_class = $icon;
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );

				?>

				<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
					<div class="ae-icon-list-item-inner">
					<?php
					if ( $icon_class !== '' ) {
						?>
						<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
						<?php
					}
					?>

					<span class="ae-icon-list-text">
							<?php echo $selected; ?>
					</span>
					</div>
				</li>

					<?php

			}
		}

		return $list_items;
	}

	public static function show_label( $selected, $show_all_choices, $field_object, $widget, $settings ) {
		$icon           = $settings['icon'];
		$icon_unchecked = $settings['icon_unchecked'];

		$list_items = [];

		if ( $show_all_choices === 'yes' ) {

			if ( is_array( $selected ) ) {
				// multi items are selected

				foreach ( $field_object['choices'] as $key => $label ) {

					$striked    = false;  // just assuming
					$icon_class = '';
					if ( in_array( $key, $selected, true ) ) {
						// Selected/Checked item
						$icon_class = $icon;
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					} else {
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-no-select ae-icon-list-item' );
						$icon_class = $icon_unchecked;
					}

					?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
							<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $label; ?>
						</span>
						</div>
					</li>

					<?php

				}
			} else {

				foreach ( $field_object['choices'] as $key => $label ) {

					$icon_class = '';
					if ( $key === $selected ) {
						// Selected/Checked item
						$icon_class = $icon;
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
					} else {
						$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-no-select ae-icon-list-item' );
						$icon_class = $icon_unchecked;
					}

					?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
							<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $label; ?>
						</span>
						</div>
					</li>

					<?php

				}
			}
		} else {

			if ( is_array( $selected ) ) {
				// multi items are selected

				$icon_class = $icon;
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );

				foreach ( $field_object['choices'] as $key => $label ) {
					if ( in_array( $key, $selected, true ) ) {
						?>

					<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="ae-icon-list-item-inner">
						<?php
						if ( $icon_class !== '' ) {
							?>
							<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
							<?php
						}
						?>

						<span class="ae-icon-list-text">
							<?php echo $label; ?>
						</span>
						</div>
					</li>

						<?php
					}
				}
			} else {

				$icon_class = $icon;
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );

				?>

				<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
					<div class="ae-icon-list-item-inner">
					<?php
					if ( $icon_class !== '' ) {
						?>
						<span class="ae-icon-list-icon">
									<i class="<?php echo $icon_class; ?>"></i>
								</span>
						<?php
					}
					?>

					<span class="ae-icon-list-text">
						<?php
						foreach ( $field_object['choices'] as $key => $label ) {
							if ( $key === $selected ) {
								echo $label;
							}
						}
						?>	
					</span>
					</div>
				</li>

					<?php

			}
		}

		return $list_items;
	}

	public static function show_custom_options( $value, $widget, $settings) {
		$list_items = [];
		$icon       = $settings['icon'];
		$icon_class = $icon;
		$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item' );
		foreach ( $value as $val ) {
			?>
			<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
				<div class="ae-icon-list-item-inner">
				<?php
				if ( $icon_class !== '' ) {
					?>
					<span class="ae-icon-list-icon">
								<i class="<?php echo $icon_class; ?>"></i>
							</span>
					<?php
				}
				?>
				<span class="ae-icon-list-text">
						<?php echo $val; ?>
				</span>
				</div>
			</li>
			<?php
		}
		return $list_items;
	}

	public static function register_controls($widget) {

		$widget->add_control(
			'data_type',
			[
				'label'     => __( 'Display Data', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'label' => __( 'Label', 'ae-pro' ),
					'key'   => __( 'Key', 'ae-pro' ),
				],
				'separator' => 'before',
				'default'   => 'label',
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group'],
				],
			]
		);

		$widget->add_control(
			'show_all_choices',
			[
				'label'       => __( 'Show All Options/Choices', 'ae-pro' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_off'   => __( 'No', 'ae-pro' ),
				'label_on'    => __( 'Yes', 'ae-pro' ),
				'default'     => __( 'label_off', 'ae-pro' ),
				'description' => __( 'This will even display choices that were not selected. You can style them separately.', 'ae-pro' ),
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group'],
				],
			]
		);

		$widget->add_control(
			'layout',
			[
				'label'       => __( 'Layout', 'ae-pro' ),
				'label_block' => false,
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'vertical' => [
						'title' => __( 'Vertical', 'ae-pro' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'horizontal' => [
						'title' => __( 'Horizontal', 'ae-pro' ),
						'icon'  => 'eicon-ellipsis-h',
					],
				],
				'default'     => 'horizontal',
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group', 'taxonomy', 'post_object', 'relationship'],
				],
			]
		);

		$widget->add_responsive_control(
			'horizontal_align',
			[
				'label'        => __( 'Align', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => false,
				'options'      => [
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
				'prefix_class' => 'ae-icl-align-',
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group', 'taxonomy', 'post_object', 'relationship'],
				],
			]
		);

		$widget->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'ae-pro' ),
				'type'  => Controls_Manager::ICON,
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group', 'taxonomy', 'post_object', 'relationship'],
				],
			]
		);

		$widget->add_control(
			'icon_unchecked',
			[
				'label' => __( 'Icon (Unchecked)', 'ae-pro' ),
				'type'  => Controls_Manager::ICON,
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group'],
				],
			]
		);

		$widget->add_control(
			'divider',
			[
				'label'        => __( 'Enable Divider', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'Off', 'ae-pro' ),
				'label_on'     => __( 'On', 'ae-pro' ),
				'return_value' => 'yes',
				'render_type'  => 'template',
				'prefix_class' => 'ae-sep-divider-',
				'selectors'    => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'content: ""',
				],
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group', 'taxonomy', 'post_object', 'relationship'],
				],
			]
		);

		$widget->add_control(
			'separator',
			[
				'label'       => __( 'Separator', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'render_type' => 'template',
				'condition'   => [
					'layout'  => 'horizontal',
					'divider!' => 'yes',
				],
				'selectors'   => [
					'{{WRAPPER}} .ae-custom-sep .ae-icon-list-item:not(:last-child):after' => 'content:"{{VALUE}}"; white-space:pre;',
				],
				'condition'   => [
					'field_type' => ['select', 'checkbox', 'radio', 'button_group', 'taxonomy', 'post_object', 'relationship'],
					'layout'   => 'horizontal',
					'divider!' => 'yes',
				],
			]
		);

		$widget->add_control(
			'enable_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'   => [
					'field_type' => ['taxonomy', 'post_object', 'relationship'],
				],
			]
		);
	}

	public static function register_style_controls($widget) {

		$widget->start_controls_section(
			'list_styles',
			[
				'label'     => __( 'List Styles', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'divider' => 'yes',
				],
			]
		);

		$widget->add_responsive_control(
			'space_between',
			[
				'label'     => __( 'Space Between', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 8,
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-items:not(.ae-list-horizontal) .ae-icon-list-item:not(:last-child)' => 'padding-bottom: calc({{SIZE}}{{UNIT}}/2); margin-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .ae-icon-list-items:not(.ae-list-horizontal) .ae-icon-list-item:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .ae-icon-list-items:not(.ae-list-horizontal) .ae-icon-list-item:after' => 'bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .ae-icon-list-items.ae-list-horizontal .ae-icon-list-item' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .ae-icon-list-items.ae-list-horizontal' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body.rtl {{WRAPPER}} .ae-icon-list-items.ae-list-horizontal .ae-icon-list-item:after' => 'left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body:not(.rtl) {{WRAPPER}} .ae-icon-list-items.ae-list-horizontal .ae-icon-list-item:after' => 'right: calc(-{{SIZE}}{{UNIT}}/2)',
				],
			]
		);

		$widget->add_control(
			'divider_style',
			[
				'label'     => __( 'Style', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'options'   => [
					'solid'  => __( 'Solid', 'ae-pro' ),
					'double' => __( 'Double', 'ae-pro' ),
					'dotted' => __( 'Dotted', 'ae-pro' ),
					'dashed' => __( 'Dashed', 'ae-pro' ),
				],
				'default'   => 'solid',
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-items:not(.ae-list-horizontal) .ae-icon-list-item:not(:last-child):after' => 'border-top-style: {{VALUE}}',
					'{{WRAPPER}} .ae-icon-list-items.ae-list-horizontal .ae-icon-list-item:not(:last-child):after' => 'border-left-style: {{VALUE}}',
				],
			]
		);

		$widget->add_control(
			'divider_weight',
			[
				'label'     => __( 'Weight', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-items:not(.ae-list-horizontal) .ae-icon-list-item:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-list-horizontal .ae-icon-list-item:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$widget->add_control(
			'divider_width',
			[
				'label'     => __( 'Width', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'unit' => '%',
				],
				'condition' => [
					'divider' => 'yes',
					'layout!' => 'horizontal',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$widget->add_control(
			'divider_height',
			[
				'label'      => __( 'Height', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'default'    => [
					'unit' => '%',
				],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition'  => [
					'divider' => 'yes',
					'layout'  => 'horizontal',
				],
				'selectors'  => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$widget->add_control(
			'divider_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ddd',
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'border-color: {{VALUE}}',
				],
			]
		);

		$widget->end_controls_section();

	}


}