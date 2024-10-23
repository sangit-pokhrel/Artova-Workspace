<?php

namespace Aepro\Modules\DynamicRules\Rules;

use AePro\AePro;
use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post extends RuleBase {



	public function get_group() {
		return 'single';
	}

	public function get_name() {
		return 'post';
	}


	public function get_title() {
		return __( 'Post', 'ae-pro' );
	}

	public function get_multiple_value_control() {
		$post_types        = Aepro::$_helper->get_rule_post_types();
		$multiple_controls = [];
		foreach ( $post_types as $key => $post_type ) {
			$multiple_controls[] = [
				'condition_name' => 'ae_rule_post_types',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => 'aep-query',
				'label_block'    => true,
				'query_type'     => 'post',
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
		if ( ! is_page() && ! is_single() ) {
			return;
		}
		return $this->compare( $post->ID, $value, $operator );
	}
}
