<?php

namespace Aepro\Modules\PodsFields\Skins;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Classes\PodsMaster;
use Elementor\Plugin;
use Elementor\Icons_Manager;


class Skin_File_Gallery extends Skin_Base {


	public function get_id() {
		return 'file_gallery';
	}

	public function get_title() {
		return __( 'File - Gallery', 'ae-pro' );
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		add_action( 'elementor/element/ae-pods/general/after_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		parent::gallery_field_control();
		parent::register_gallery_type();
	}

	public function register_style_controls() {

		//Carousel Controls
		parent::gallery_image_carousel_control();
		parent::gallery_pagination_controls();
		parent::gallery_common_style_control();

		//Grid Controls
		parent::grid_view();
		parent::grid_style();
		parent::grid_overlay_controls();
		parent::grid_overlay_style_control();
	}

	public function render() {

		$settings  = $this->parent->get_settings_for_display();
		$link_text = '';

		$field_args = [
			'field_name' => $settings['field_name'],
			'field_type' => $settings['field_type'],

		];

		if ( $settings['pods_option_name'] !== '' ) {
			$field_args['pods_option_name'] = $settings['pods_option_name'];
		}

		$images_array = PodsMaster::instance()->get_field_object( $field_args );

		if ( isset( $images_array ) && ! empty( $images_array ) ) {
			$field_options = PodsMaster::instance()->get_field_options( $field_args );
			if ( $field_options['file_format_type'] === 'single' ) {
				$images[0] = $images_array;
			} else {
				$images = $images_array;
			}
		}

		switch ( $this->get_instance_value( 'gallery_type' ) ) {

			case 'carousel':
					$image_size = $this->get_instance_value( 'thumbnail_size' );

					$swiper_data = $this->get_swiper_data();

				if ( ! empty( $images ) ) {

					$this->parent->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper ae-acf-file-gallery' );

					$this->parent->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );

					?>
					<?php
					if ( $this->get_instance_value( 'open_lightbox' ) !== 'no' ) {
						$this->parent->add_render_attribute(
							'link',
							[
								'data-elementor-open-lightbox' => $this->get_instance_value( 'open_lightbox' ),
								'data-elementor-lightbox-slideshow' => 'ae-acf-gallery-' . wp_rand( 0, 99999 ),
							]
						);
						if ( Plugin::$instance->editor->is_edit_mode() ) {
							$this->parent->add_render_attribute(
								'link',
								[
									'class' => 'elementor-clickable',
								]
							);
						}
					}

					$this->parent->add_render_attribute( 'swiper_wrapper', 'class', 'ae-swiper-wrapper swiper-wrapper' );

					if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) {
						$this->parent->add_render_attribute( 'swiper_wrapper', 'class', 'ae_image_ratio_yes' );
					}
					?>
						<div <?php echo $this->parent->get_render_attribute_string( 'outer-wrapper' ); ?> >
							<div class="ae-swiper-container swiper-container">
								<div class="ae-swiper-wrapper swiper-wrapper">

								<?php
								foreach ( $images as $image ) {
									?>
										<div class="ae-swiper-slide swiper-slide">
											<div <?php echo $this->parent->get_render_attribute_string( 'swiper_wrapper' ); ?>>
											<?php if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) { ?>
													<div class="ae-pods-gallery-image">
												<?php } ?>

											<?php if ( $this->get_instance_value( 'open_lightbox' ) !== 'no' ) { ?>
													<a <?php echo $this->parent->get_render_attribute_string( 'link' ); ?>
													href="<?php echo wp_get_attachment_url( $image['id'], 'full' ); ?>">
												<?php } ?>

												<?php echo wp_get_attachment_image( $image['ID'], $image_size ); ?>

											<?php if ( $this->get_instance_value( 'open_lightbox' ) !== 'no' ) { ?>
													</a>
												<?php } ?>

											<?php if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) { ?>
													</div>
												<?php } ?>
											</div>
										</div>
									<?php } ?>
								</div>

								<?php if ( $swiper_data['ptype'] !== '' ) { ?>
									<div class="ae-swiper-pagination swiper-pagination"></div>
								<?php } ?>

								<?php if ( $swiper_data['navigation'] === 'yes' ) { ?>
									<div class="ae-swiper-button-prev swiper-button-prev"></div>
									<div class="ae-swiper-button-next swiper-button-next"></div>
								<?php } ?>

								<?php if ( $swiper_data['scrollbar'] === 'yes' ) { ?>
									<div class="ae-swiper-scrollbar swiper-scrollbar"></div>

								<?php } ?>

							</div>
						</div>

					<?php
				}
				break;
			case 'grid':
				$image_size = $this->get_instance_value( 'thumbnail_size' );
				$masonry    = $this->get_instance_value( 'masonry' );
				$animation  = $this->get_instance_value( 'animation' );
				$icon       = $this->get_instance_value( 'selected_icon' );
				$caption    = $this->get_instance_value( 'caption' );

				if ( $masonry === 'yes' ) {
					$this->parent->add_render_attribute( 'grid-wrapper', 'class', 'ae-masonry-yes' );
				} else {
					$this->parent->add_render_attribute( 'grid-wrapper', 'class', 'ae-masonry-no' );
				}

				$this->parent->add_render_attribute( 'grid-wrapper', 'class', 'ae-grid-wrapper' );

				if ( $this->get_instance_value( 'hover_direction_aware' ) === 'yes' && $this->get_instance_value( 'show_overlay' ) === 'hover' ) {
					$overlay_speed = $this->get_instance_value( 'overlay_speed' );
					$overlay_speed = $overlay_speed['size'];
					$this->parent->add_render_attribute( 'grid-wrapper', 'class', 'ae-hover-direction-effect' );
					$this->parent->add_render_attribute( 'grid-wrapper', 'data-overlay-speed', $overlay_speed );
				}
				?>
				<?php
				$this->parent->add_render_attribute(
					'link',
					[
						'data-elementor-open-lightbox'  => $this->get_instance_value( 'open_lightbox' ),
						'data-elementor-lightbox-slideshow' => 'ae-acf-gallery-' . wp_rand( 0, 99999 ),
						'data-elementor-lightbox-title' =>
															'testing',
					]
				);
				if ( Plugin::$instance->editor->is_edit_mode() ) {
					$this->parent->add_render_attribute(
						'link',
						[
							'class' => 'elementor-clickable',
						]
					);
				}
				$this->parent->add_render_attribute( 'grid_item_inner', 'class', 'ae-grid-item-inner' );

				if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) {
					$this->parent->add_render_attribute( 'grid_item_inner', 'class', 'ae_image_ratio_yes' );
				}
				?>

				<div <?php echo $this->parent->get_render_attribute_string( 'grid-wrapper' ); ?>>
					<div class="ae-grid">
						<?php
						if ( ! empty( $images ) ) {
							foreach ( $images as $image ) {
								$image_caption = wp_get_attachment_caption( $image['ID'] );
								?>
								<div class="ae-grid-item">
									<div <?php echo $this->parent->get_render_attribute_string( 'grid_item_inner' ); ?>>
										<a href="<?php echo wp_get_attachment_url( $image['ID'], 'full' ); ?>" <?php echo $this->parent->get_render_attribute_string( 'link' ); ?>>
											<?php if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) { ?>
												<div class="ae-pods-gallery-image">
											<?php } ?>
											<?php
											echo wp_get_attachment_image(
												$image['ID'],
												$image_size,
												false,
												[
													'alt' => '$image_caption',
													'class' => 'ae-pods-galley-item',
												]
											);
											?>
											<?php if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) { ?>
												</div>
											<?php } ?>
											<div class="ae-grid-overlay <?php echo $animation; ?>">
												<div class="ae-grid-overlay-inner">
													<div class="ae-icon-wrapper">
														<?php if ( ! empty( $icon ) ) { ?>
															<div class="ae-overlay-icon">
																<?php Icons_Manager::render_icon( $this->get_instance_value( 'selected_icon' ), [ 'aria-hidden' => 'true' ] ); ?>
															</div>
														<?php } ?>
													</div>
													<?php if ( $image_caption !== '' && $caption === 'yes' ) { ?>
														<div class="ae-overlay-caption"><?php echo $image_caption; ?></div>
													<?php } ?>
												</div>
											</div>
										</a>

									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
		}
	}
	public function get_swiper_data() {
		$swiper_data['speed']     = $this->get_instance_value( 'speed' )['size'];
		$swiper_data['direction'] = 'horizontal';

		if ( $this->get_instance_value( 'autoplay' ) === 'yes' ) {
			$duration                                        = $this->get_instance_value( 'duration' );
			$swiper_data['autoplay']['delay']                = $duration['size'];
			$swiper_data['autoplay']['disableOnInteraction'] = true;
		} else {
			$swiper_data['autoplay'] = false;
		}

		$swiper_data['effect'] = $this->get_instance_value( 'effect' );

		$swiper_data['spaceBetween'] = [
			'default' => $this->get_instance_value( 'space_mobile' )['size'],
			'tablet'  => $this->get_instance_value( 'space' )['size'],
			'mobile'  => $this->get_instance_value( 'space_tablet' )['size'],
		];

		$swiper_data['loop']       = $this->get_instance_value( 'loop' );
		$swiper_data['autoHeight'] = ( $this->get_instance_value( 'auto_height' ) === 'yes' );

		$swiper_data['slidesPerView'] = [
			'default' => $this->get_instance_value( 'slide_per_view_mobile' ),
			'tablet'  => $this->get_instance_value( 'slide_per_view' ),
			'mobile'  => $this->get_instance_value( 'slide_per_view_tablet' ),
		];

		$swiper_data['slidesPerGroup'] = [
			'default' => $this->get_instance_value( 'slides_per_group_mobile' ),
			'tablet'  => $this->get_instance_value( 'slides_per_group' ),
			'mobile'  => $this->get_instance_value( 'slides_per_group_tablet' ),
		];

		$swiper_data['ptype'] = $this->get_instance_value( 'ptype' );
		if ( $swiper_data['ptype'] !== '' ) {
			if ( $swiper_data['ptype'] === 'progress' ) {
				$swiper_data['ptype'] = 'progressbar';
			}
		}

		$swiper_data['clickable']  = $this->get_instance_value( 'clickable' );
		$swiper_data['navigation'] = $this->get_instance_value( 'navigation_button' );
		$swiper_data['scrollbar']  = $this->get_instance_value( 'scrollbar' );

		return $swiper_data;
	}
}
