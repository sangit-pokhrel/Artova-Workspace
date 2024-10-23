<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Taxonomy_Archive extends RuleBase {



	public function get_group() {
		return 'archive';
	}

	public function get_name() {
		return 'taxonomy_archive';
	}


	public function get_title() {
		return __( 'Taxonomy Archive', 'ae-pro' );
	}



	public function get_value_control() {
		$taxonomies              = [];
		$ae_taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];
		$ae_taxonomies           = get_taxonomies( $ae_taxonomy_filter_args, 'objects' );
		foreach ( $ae_taxonomies as $key => $taxonomy ) {
			$taxonomies[ $key ] = $taxonomy->label;
		}
		return [

			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'label_block' => true,
			'options'     => $taxonomies,

		];
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

		if ( is_post_type_archive() || ! is_archive() ) {
			return;
		}
		$term_taxonomy = '';
			$term      = get_queried_object();
		if ( ! empty( $term ) ) {
			$term_taxonomy = $term->taxonomy;
		}
		return $this->compare( $term_taxonomy, $value, $operator );
	}
}
