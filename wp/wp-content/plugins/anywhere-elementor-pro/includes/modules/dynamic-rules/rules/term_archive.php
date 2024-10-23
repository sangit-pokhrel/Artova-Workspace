<?php

namespace Aepro\Modules\DynamicRules\Rules;

use AePro\AePro;
use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Term_Archive extends RuleBase {



	public function get_group() {
		return 'archive';
	}

	public function get_name() {
		return 'term_archive';
	}


	public function get_title() {
		return __( 'Term Archive', 'ae-pro' );
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
				'condition_name' => 'ae_rule_term_archive_types',
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
		global $post;
		if ( ! is_archive() ) {
			return;
		}
		if ( ! is_admin() ) {
			$term = get_queried_object();
		}
		if ( empty( $term->term_id ) ) {
			return;
		}
		return $this->compare( $term->term_id, $value, $operator );
	}
}
