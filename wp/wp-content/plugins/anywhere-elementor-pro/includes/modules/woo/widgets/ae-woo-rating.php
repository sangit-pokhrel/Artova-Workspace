<?php

namespace Aepro\Modules\Woo\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;


class AeWooRating extends Widget_Base {

	protected $_access_level = 2;

	public function get_name() {
		return 'ae-woo-rating';
	}

	public function is_enabled() {

		if ( AE_WOO ) {
			return true;
		}

		return false;
	}

	public function get_title() {
		return __( 'AE - Woo Rating', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-woocommerce';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'woocommerce',
			'shop',
			'store',
			'rating',
			'review',
			'comments',
			'stars',
			'product',
		];
	}

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'General', 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => __( 'Alignment', 'ae-pro' ),
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
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'show_review_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_review_count_text',
			[
				'label'        => __( 'Show Review Count', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_rating_style',
			[
				'label' => __( 'General', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'star_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .star-rating span::before' => 'color: {{VALUE}}',
					'{{WRAPPER}} .star-rating::before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'star_size',
			[
				'label'     => __( 'Star Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 5,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .star-rating, {{WRAPPER}} p.stars a, {{WRAPPER}} .review-count-text' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-element-woo-rating.woocommerce-product-rating' => 'color: {{VALUE}};',
					'{{WRAPPER}} .ae-element-woo-rating .woocommerce-review-link' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .ae-element-woo-rating.woocommerce-product-rating, {{WRAPPER}} .review-count-text',
			]
		);
		$this->end_controls_section();
	}

	public function render() {

		if ( $this->is_debug_on() ) {
			return;
		}

		global $product;
		$settings = $this->get_settings();
		$product  = Aepro::$_helper->get_ae_woo_product_data();
		if ( ! $product ) {
			return '';
		}

		$this->add_render_attribute( 'woo-rating-class', 'class', 'ae-element-woo-rating' );
		$this->add_render_attribute( 'woo-rating-class', 'class', 'woocommerce-product-rating' );
		$this->add_render_attribute( 'woo-rating-star-class', 'class', 'star-rating' );

		$this->add_render_attribute( 'woo-rating-wrapper', 'class', 'woocommerce' );
		$this->add_render_attribute( 'woo-rating-wrapper', 'class', 'ae-woo-rating-wrapper' );

		if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
			return;
		}

		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();

		if ( $rating_count > 0 ) { ?>

			<div <?php echo $this->get_render_attribute_string( 'woo-rating-wrapper' ); ?>>
				<?php if ( comments_open( $product->get_id() ) && $settings['show_review_link'] === 'yes' ) : ?>
					<a href="<?php echo get_permalink(); ?>#reviews" class="woocommerce-review-link" rel="nofollow">
				<?php endif; ?>
						<div <?php echo $this->get_render_attribute_string( 'woo-rating-class' ); ?> itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
							<?php //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.TextDomainMismatch ?>
							<div <?php echo $this->get_render_attribute_string( 'woo-rating-star-class' ); ?> title="<?php printf( __( 'Rated %s out of 5', 'woocommerce' ), $average ); ?>">
								<span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%">
									<?php //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.TextDomainMismatch ?>
									<strong itemprop="ratingValue" class="rating"><?php echo esc_html( $average ); ?></strong> <?php printf( __( 'out of %1$s5%2$s', 'woocommerce' ), '<span itemprop="bestRating">', '</span>' ); ?>
									<?php //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.TextDomainMismatch ?>
									<?php printf( _n( 'based on %s customer rating', 'based on %s customer ratings', $rating_count, 'woocommerce' ), '<span itemprop="ratingCount" class="rating">' . $rating_count . '</span>' ); ?>
								</span>
							</div>

							<?php
							if ( $settings['show_review_count_text'] ) {
								?>
								<span class="review-count-text">
								<?php
								//phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.TextDomainMismatch
								printf( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ), '<span itemprop="reviewCount" class="count">' . $review_count . '</span>' );
								?>
								</span>
								<?php
							}
							?>
							<div class="ae-clr"></div>
						</div>


					<?php if ( comments_open( $product->get_id() ) && $settings['show_review_link'] === 'yes' ) : ?>
					</a>
					<?php endif ?>

			</div>

			<?php
		}
	}
}
