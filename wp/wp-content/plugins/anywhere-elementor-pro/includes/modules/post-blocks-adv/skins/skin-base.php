<?php
namespace Aepro\Modules\PostBlocksAdv\Skins;

use Aepro\Aepro;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Modules\PostBlocksAdv\Classes;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;
	}

	public function if_layout_is_blank() {
		$settings = $this->parent->get_settings_for_display();
		if ( ! isset( $settings['layout'] ) || empty( $settings['layout'] ) || get_post_status( $settings['layout'] ) !== 'publish' ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' ) ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Content > Layout > Primary Block Layout'", 'ae-pro' ) ) );
			}
		}
	}

	public function render_item( $layout, $wid = 0 ) {

		$with_css = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}
		$settings  = $this->parent->get_settings_for_display();
		$post_data = Aepro::$_helper->get_demo_post_data();

		// Add Dynamic Classes Hook
		$custom_classes = apply_filters( 'aepro/postblocksadv/post-class', [], $wid );

		$item_classes = array_merge( [ 'ae-post-item', 'ae-post-item-' . $wid ], $custom_classes );
		
		if ( $settings['_skin'] === 'carousel' ) {
			$item_classes[] = 'ae-swiper-slide swiper-slide';
		}

		// Post Item Attributess
		$this->parent->set_render_attribute( 'item', 'class', $item_classes );

		$item_inner_classes = [ 'ae-post-item-inner' ];

		//WooCommerce Sales Badge
		if ( isset( $settings['sale_badge_switcher'] ) && $settings['sale_badge_switcher'] === 'yes' ) {
			$item_inner_classes[] = 'badge-type-' . $settings['sale_badge_type'];
		}

		if ( $settings['_skin'] === 'carousel' ) {
			$item_inner_classes[] = 'ae-swiper-slide-wrapper';
		}

		// Item Inner Attribute
		$this->parent->set_render_attribute( 'item-inner', 'class', $item_inner_classes );

		if ( isset( $settings['sale_badge_switcher'] ) && $settings['sale_badge_switcher'] === 'yes' ) {
			$this->parent->set_render_attribute( 'on-sale', 'class', 'onsale' );
			$sale_badge_img  = '';
			$sale_badge_text = '';
			if ( $settings['sale_badge_type'] === 'image' ) {
				$this->parent->add_render_attribute( 'on-sale', 'class', 'sale-badge-image' );
				switch ( $settings['sale_badge_source'] ) {
					case 'default':
						$sale_badge_img = $settings['sale_badge_icon']['url'];
						break;
					case 'custom_field':
						$custom_field   = get_post_meta( $post_data->ID, $settings['sale_badge_custom_field_image'], true );
						$sale_badge_img = wp_get_attachment_url( $custom_field );
						if ( $sale_badge_img === '' ) {
							$sale_badge_img = $settings['sale_badge_custom_field_image_fallback']['url'];
						}
						break;
				}
				$this->parent->set_render_attribute( 'on-sale', 'style', "background-image: url('" . $sale_badge_img . "');" );
			} else {
				switch ( $settings['sale_badge_source'] ) {
					case 'default':
						$sale_badge_text = do_shortcode( $settings['sale_badge_title'] );
						break;
					case 'custom_field':
						$sale_badge_text = get_post_meta( $post_data->ID, $settings['sale_badge_custom_field_text'], true );
						if ( $sale_badge_text === '' ) {
							$sale_badge_text = $settings['sale_badge_custom_field_text_fallback'];
						}
						break;
				}

				if ( $sale_badge_text !== '' ) {
					$sale_badge_text = $settings['sale_badge_prefix'] . $sale_badge_text . $settings['sale_badge_suffix'];
				}
			}
		}

		if ( ! empty( $settings['query_filter'] ) ) {
			$new_template = apply_filters( "aepro/post_blocks_adv/layout/{$settings['query_filter']}", $layout );

			if ( $new_template !== $layout ) {
				$layout   = $new_template;
				$with_css = true;
			}
		}

		?>

		<article <?php echo $this->parent->get_render_attribute_string( 'item' ); ?> >
			<div <?php echo $this->parent->get_render_attribute_string( 'item-inner' ); ?>>
				<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
				<div class="ae_data elementor elementor-<?php echo $layout; ?>">
				<?php } ?>
					<?php
					//WooCommerce Sales Badge
					if ( class_exists( 'WooCommerce' ) ) {
						if ( $settings['sale_badge_switcher'] === 'yes' ) {
							if ( Aepro::$_helper->ae_is_product_on_sale( get_the_ID() ) ) {
								if ( $sale_badge_text !== '' || $sale_badge_img !== '' ) {
									?>
								<div class="ae-sale-badge-wrapper">
									<span <?php echo $this->parent->get_render_attribute_string( 'on-sale' ); ?> >
										<?php echo $sale_badge_text; ?>
									</span>
								</div>
									<?php
								}
							}
						}
					}

					echo Plugin::instance()->frontend->get_builder_content( $layout, $with_css );

					?>
				<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
				</div>
				<?php } ?>
			</div>
		</article>

		<?php
	}

	public function get_layout( $seq, $settings, $sg_layout = 0 ) {

		$layout          = $settings['layout'];
		$alt_layout      = $this->get_instance_value( 'alt_layout' );
		$infinite_scroll = $this->get_instance_value( 'show_infinite_scroll' );
		$grid_layout     = $this->get_instance_value( 'grid_layout' );

		if ( is_paged() && $infinite_scroll === 'yes' && $grid_layout === 'smart-grid' ) {
			return $layout;
		}

		switch ( $grid_layout ) {

			case 'smart-grid':
				if ( in_array( $seq, $sg_layout, true ) && $alt_layout !== '' ) {
									$layout = $alt_layout;
				}
				break;

			case 'checker-board':
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $this->get_instance_value( 'columns' ) % 2 != 0 ) {
					//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $seq % 2 == 0 ) {
						$layout = $alt_layout;
					}
				} else {
					// more complex
					$row = ceil( $seq / $this->get_instance_value( 'columns' ) );
					//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $row % 2 == 0 ) {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $seq % 2 == 0 ) {
							$layout = $alt_layout;
						}
					} else {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $seq % 2 == 1 ) {
							$layout = $alt_layout;
						}
					}
				}
		}

		return $layout;
	}

	public function get_filters_html( $settings ) {
		$show_filters = true;
		if ( $settings['show_filters'] === 'yes' ) {
			if ( $settings['source'] === 'relation' && AE_ACF_PRO ) {
				if ( $settings['acf_relation_field'] === '' ) {
					$show_filters = false;
				} else {
					//phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.PHP.StrictComparisons.LooseComparison
					if ( \Aepro\Plugin::show_acf() && is_plugin_active( 'pods/init.php' ) ) {
						if ( $settings['relationship_type'] === 'pods' ) {
							if ( isset( $_POST['queried_page']['ID'] ) && $_POST['queried_page']['ID'] != '' ) {
								//phpcs:ignore WordPress.Security.NonceVerification.Missing
									$relationship_data = get_field( $settings['acf_relation_field'], $_POST['queried_page']['ID'] );
							} else {
									$relationship_data = get_field( $settings['acf_relation_field'] );
							}
							if ( ! $relationship_data ) {
								$show_filters = false;
							}
						}
					}
				}
			}
		}
		if ( $settings['show_filters'] === 'yes' && $settings['source'] !== 'current_loop' && $show_filters ) {
			if ( $settings['filter_taxonomy'] === '' ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select taxonomy from 'Content > Filters > Taxonomy'", 'ae-pro' ) ) );
				return false;
			}
			$collapse_filter_after = [];
			$filter_terms          = Aepro::$_helper->get_filter_bar_filters( $settings );
			if ( is_array( $filter_terms ) && count( $filter_terms ) ) {
				$cuurent_settings                 = \Elementor\Plugin::$instance->kits_manager->get_current_settings();
				$active_breakpoints               = $cuurent_settings['active_breakpoints'];
				$collapse_filter_after['desktop'] = $settings['collapse_filter_after'];
				foreach ( $active_breakpoints as $breakpoint ) {
					if ( isset( $settings[ 'collapse_filter_after_' . str_replace( 'viewport_', '', $breakpoint ) ] ) ) {
						$collapse_filter_after[ str_replace( 'viewport_', '', $breakpoint ) ] = $settings[ 'collapse_filter_after_' . str_replace( 'viewport_', '', $breakpoint ) ];
					}
				}

				$collapse_after       = [];
				$collapse_after_class = [];

				foreach ( $collapse_filter_after as $key => $value ) {
					if ( $key === 'desktop' ) {
						$default_collapse_after = $value;
					}
					if ( ! in_array( $value, $collapse_after, true ) ) {
						$collapse_after[]               = $value;
						$collapse_after_class[ $value ] = $key;
					} else {
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $value != 0 || $value == '' ) {
							$value = $default_collapse_after;
						}
						$collapse_after_class[ $value ] = $collapse_after_class[ $value ] . ' ' . $key;
					}
				}

				foreach ( $collapse_after as $key => $value ) {
					?>
				<div class="aep-filter-bar <?php echo $settings['collapse_filter'] === 'yes' ? 'collapse' : ''; ?> <?php echo $collapse_after_class[ $value ]; ?>">
					<?php
					if ( $settings['filter_label'] !== '' ) {
						?>
						<div class="filter-label"><?php echo $settings['filter_label']; ?></div>
						<?php
					}
					$first_term_element  = 0;
					$active_filter_class = '';

					// Check if Default Term is selected
					if ( $settings[ $settings['filter_taxonomy'] . '_filter_default_term' ] !== '' ) {
						$first_term_element = $settings[ $settings['filter_taxonomy'] . '_filter_default_term' ];
					}

					// Check if there is any Term ID in $_POST
					//phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( isset( $_POST['term_id'] ) ) {
						//phpcs:ignore WordPress.Security.NonceVerification.Missing
						$first_term_element = $_POST['term_id'];
					}

					// Check if 'All' Tab is Enabled
					if ( $settings['show_all'] === 'yes' ) {
						?>
						<?php //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
						<div class="filter-items <?php echo ( ! $first_term_element ) ? 'active' : ''; ?>"><a  href="#" data-term-id="0"><?php echo __( $settings['tab_all_text'], 'ae-pro' ); ?></a></div>
						<?php
					} else {
						if ( ! $first_term_element ) {
							$first_term_element = $filter_terms[array_key_first($filter_terms)]->term_id;
						}
					}
					$index   = 1;
					$counter = 1;
					foreach ( $filter_terms as $term ) {
						if ( $settings['collapse_filter'] === 'yes' ) {
							if ( $index > $collapse_after[ $key ] ) {
								continue;
							}
							$index++;
						}
						if ( $settings['enable_url_hashtag'] === 'yes' ) {
							$data_hashtag = $this->get_data_hashtag( $settings, $term, $counter );
							$this->parent->set_render_attribute(
								'ae-filter-item',
								[
									'data-hashtag' => $data_hashtag,
								]
							);
						}
						?>
						<?php //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.PHP.StrictComparisons.LooseComparison ?>
						<div class="filter-items <?php echo ( ( isset( $_POST['term_id'] ) && $_POST['term_id'] == $term->term_id ) || ( $term->term_id == $first_term_element && ! isset( $_POST['term_id'] ) ) ) ? 'active' : ''; ?>" <?php echo $this->parent->get_render_attribute_string( 'ae-filter-item' ); ?> >
							<a href="#" data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
						</div>
						<?php
						$counter++;
					}
					if ( $settings['collapse_filter'] === 'yes' ) {
						?>
						<div class="filter-items ae-dropdown">
							<a href="#" data-term-id="other">
								<span class="dropdown-filter-text"><?php echo $settings['collapse_filter_text']; ?></span>
								<?php Icons_Manager::render_icon( $settings['collapse_filter_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							</a>

							<?php
							$index = 1;
							?>
							<ul class="ae-menu hide">
							<?php
							foreach ( $filter_terms as $term ) {
								if ( $index <= $collapse_after[ $key ] ) {
									$index++;
									continue;
								}
								if ( $settings['enable_url_hashtag'] === 'yes' ) {
									$data_hashtag = $this->get_data_hashtag( $settings, $term, $counter );
									$this->parent->set_render_attribute(
										'ae-filter-item',
										[
											'data-hashtag' => $data_hashtag,
										]
									);
									// 
								}
								?>
									<li>
										<?php //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.PHP.StrictComparisons.LooseComparison ?>
										<div class="filter-items <?php echo ( ( isset( $_POST['term_id'] ) && $_POST['term_id'] == $term->term_id ) || ( $term->term_id == $first_term_element && ! isset( $_POST['term_id'] ) ) ) ? 'active' : ''; ?>" <?php echo $this->parent->get_render_attribute_string( 'ae-filter-item' ); ?>>
											<a href="#" data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
										</div>
									</li>
									<?php
									$counter++;
							}
							?>
							</ul>
						</div>
						<?php
					}
					?>
				</div>
					<?php
				}
			}
		}
	}

	public function ae_no_post_message( $settings ) {
		if ( trim( $settings['no_posts_message'] ) === '' ) {
			return false;
		}
			return '<div class="ae-no-posts">' . do_shortcode( $settings['no_posts_message'] ) . '</div>';
	}

	public function get_data_hashtag( $settings, $term, $counter ) {
		$data_hashtag    = '';
		$filter_taxonomy = $settings['filter_taxonomy'];
		$hashtag_type    = $settings['fragment_type'];
		switch ( $hashtag_type ) {
			case 'term_id':
				$data_hashtag = 'term-' . $term->term_id;
				break;
			case 'term_slug':
				$data_hashtag = $term->slug;
				break;
			case 'custom_field':
				$data_hashtag = get_term_meta( $term->term_id, $settings['fragment_custom_field'] );
				break;
			default:
				$data_hashtag = 'tab-' . $counter;
		}
		return $data_hashtag;
	}
}
