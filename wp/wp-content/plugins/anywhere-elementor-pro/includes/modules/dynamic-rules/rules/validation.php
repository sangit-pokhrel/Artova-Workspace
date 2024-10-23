<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Validation extends RuleBase {


	public function get_group() {
		return 'user';
	}

	public function get_name() {
		return 'validation';
	}


	public function get_title() {
		return __( 'Validation', 'ae-pro' );
	}

	public function get_value_control() {
		return [
			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => Controls_Manager::SELECT,
			'description' => __( 'Warning: This condition applies only to logged in visitors.', 'ae-pro' ),
			'default'     => 'logged_in',
			'options'     => [
				'logged_in' => __( 'Logged In', 'ae-pro' ),
			],
		];
	}

	protected function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'     => __( 'Is Equal', 'ae-pro' ),
			'not_equal' => __( 'Is Not Equal', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function check( $operator, $value, $name = null ) {
		$is_user_logged_in = 0;
		if ( is_user_logged_in() ) {
			$is_user_logged_in = 1;
		}
		return $this->compare( $is_user_logged_in, 1, $operator );
	}
}
