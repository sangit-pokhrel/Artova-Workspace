<?php

namespace Aepro\Modules\DynamicRules\Rules;

use AePro\AePro;
use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Term extends RuleBase {



	public function get_group() {
		return 'single';
	}

	public function get_name() {
		return 'post_term';
	}


	public function get_title() {
		return __( 'Post Term', 'ae-pro' );
	}

	public function get_multiple_value_control() {
		$taxonomies              = [];
		$ae_taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];
		$taxonomies              = get_taxonomies( $ae_taxonomy_filter_args, 'names' );

		$multiple_controls = [];

		foreach ( $taxonomies as $key => $taxonomy ) {
			$multiple_controls[] = [
				'condition_name' => 'ae_rule_post_term_types',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => 'aep-query',
				'label_block'    => true,
				'query_type'     => 'taxonomy',
				'object_type'    => $key,
				'multiple'       => true,
			];
		}

		return $multiple_controls;
	}

	public function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'        => __( 'Is Equal', 'ae-pro' ),
			'not_equal'    => __( 'Is Not Equal', 'ae-pro' ),
			'contains'     => __( 'Contains', 'ae-pro' ),
			'not_contains' => __( 'Does Not Contains', 'ae-pro' ),
		];

		return $rule_operators;
	}



	public function check( $operator, $value, $name = null ) {
		if( empty($value) ){
			return false;
		}
		global $post;
		$taxonomies = get_object_taxonomies( [ 'post_type' => $post->post_type ] );
		$term       = get_term( $value[0] );
		if ( empty( $term ) ) {
			return;
		}
		$taxonomy = $term->taxonomy;
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ! in_array( $taxonomy, $taxonomies ) ) {
			return;
		}
		$terms    = wp_get_post_terms( $post->ID, $taxonomy );
		$term_ids = [];
		foreach ( $terms as $term ) {
			$term_ids[] = $term->term_id;
		}

		return $this->compare( $term_ids, $value, $operator );
	}
}
