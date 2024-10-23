<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User_Role extends RuleBase {


	public function get_group() {
		return 'user';
	}

	public function get_name() {
		return 'user_role';
	}


	public function get_title() {
		return __( 'User Role', 'ae-pro' );
	}

	public function get_value_control() {
		return [
			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'label_block' => true,
			'description' => __( 'Warning: This condition applies only to logged in visitors.', 'ae-pro' ),
			'default'     => 'subscriber',
			'options'     => $this->ae_get_user_roles(),
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

	public function ae_get_user_roles() {
		$roles = [];
		global $wp_roles;
		$roles_arr = $wp_roles->roles;

		foreach ( $roles_arr as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}

		return $roles;
	}

	public function check( $operator, $value, $name = null ) {
		$user = wp_get_current_user();
		return $this->compare( $user->roles, $value, $operator );
	}
}
