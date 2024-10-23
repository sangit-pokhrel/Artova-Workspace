<?php
namespace Aepro\Modules\DynamicCalendar\Skins;

use Aepro\Aepro;
use Aepro\Frontend;
use Aepro\Modules\DynamicCalendar\Classes\Calendar;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Modules\DynamicCalendar\Classes\Query;


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

	public function get_posts_by_date_arr( $settings ) {

		// get posts
		$query = new Query( $settings );
		$posts = $query->get_posts();

		// Securing Current post of Parent Query
		$prev_post = get_post();

		global $post;
		global $wp_query;
		$old_queried_object = $wp_query->queried_object;

		$post_arr = [];

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				Frontend::$_ae_post_block = get_the_ID();
				$post_ID                  = get_the_ID();
				if ( $settings['date_source'] === 'post_date' ) {
					$date = get_the_date();
				} elseif ( $settings['date_source'] === 'modified_date' ) {
					$date = get_the_modified_date();
				} elseif ( $settings['date_source'] === 'custom_field' ) {
					if ( $settings['field_type'] === 'acf_field' ) {
						$date = get_field( $settings['acf_date_field'], $post_ID );
					} elseif ( $settings['field_type'] === 'custom_field' ) {
						$date = get_post_meta( $post_ID, $settings['custom_date_field'], 1 );
					}
				}
				$wp_query->queried_object = $post;

				$post_arr['post_by_date'][ gmdate( 'Y-m-d', strtotime( $date ) ) ][ $post_ID ] = (array) get_post();
				$post_arr['posts'][ $post_ID ]['post_date']                                    = gmdate( 'Y-m-d', strtotime( $date ) );
				$post_arr['posts'][ $post_ID ]['template']                                     = Plugin::instance()->frontend->get_builder_content( $settings['listing_block_layout'], true );

			}

			$wp_query->queried_object = $old_queried_object;
			Frontend::$_ae_post_block = 0;
			wp_reset_postdata();
			setup_postdata( $prev_post );
		}

		return $post_arr;
	}

	public function ae_no_post_message( $settings ) {
		if ( trim( $settings['no_posts_message'] ) === '' ) {
			return false;
		}
			return '<div class="ae-no-posts">' . do_shortcode( $settings['no_posts_message'] ) . '</div>';
	}
}
