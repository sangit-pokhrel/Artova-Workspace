<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User extends RuleBase {


	public function get_group() {
		return 'user';
	}

	public function get_name() {
		return 'user';
	}


	public function get_title() {
		return __( 'User', 'ae-pro' );
	}

	public function get_value_control() {

		return [
			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => 'aep-query',
			'label_block' => true,
			'query_type'  => 'user',
			'multiple'    => true,
		];
	}

	protected function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'        => __( 'Is Equal', 'ae-pro' ),
			'not_equal'    => __( 'Is Not Equal', 'ae-pro' ),
			'contains'     => __( 'Contains', 'ae-pro' ),
			'not_contains' => __( 'Does Not Contains', 'ae-pro' ),
			'empty'        => __( 'Is Empty', 'ae-pro' ),
			'not_empty'    => __( 'Is Not Empty', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function check( $operator, $value, $name = null ) {
		$current_user = get_current_user_id();
		return $this->compare( $current_user, $value, $operator );
	}
}
