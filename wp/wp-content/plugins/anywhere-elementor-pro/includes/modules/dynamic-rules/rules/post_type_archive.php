<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;
use Aepro\Aepro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Type_Archive extends RuleBase {



	public function get_group() {
		return 'archive';
	}

	public function get_name() {
		return 'post_type_archive';
	}


	public function get_title() {
		return __( 'Post Type Archive', 'ae-pro' );
	}



	public function get_value_control() {
		$ae_post_types = Aepro::$_helper->get_post_types_with_archive();

		return [

			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'label_block' => true,
			'options'     => $ae_post_types,
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

		$post_type = '';

		if ( is_post_type_archive() ) {
			$post_object = get_queried_object();
			$post_type   = $post_object->name;
		}

		return $this->compare( $value, $post_type, $operator );
	}
}
