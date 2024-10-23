<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;
use Aepro\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_Choice extends RuleBase {



	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_choice';
	}


	public function get_title() {
		return __( 'ACF Choice', 'ae-pro' );
	}

	public function get_multiple_name_control() {
		$acf_fields      = Aepro::$_helper->ae_get_acf_fields( [ 'select', 'radio', 'checkbox' ] );
		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$group_field     = Aepro::$_helper->get_group_fields();
		$supported_field = [ 'select', 'radio', 'checkbox' ];
		$controls        = [
			[
				'label'       => 'Parent',
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'none'     => __( 'None', 'ae-pro' ),
					'repeater' => __( 'Repeater', 'ae-pro' ),
					'group'    => __( 'Group', 'ae-pro' ),
				],
				'default'     => 'none',
				'object_type' => 'parent_type',
			],
			//normal field
			[
				'label'       => 'ACF Name',
				'type'        => Controls_Manager::SELECT,
				'groups'      => $acf_fields,
				'placeholder' => __( 'Name', 'ae-pro' ),
				'object_type' => '',
				'condition'   => [
					'ae_rule_acf_choice_parent_type_name' => 'none',
				],
			],
			//repeater field
			[
				'label'       => __( 'Repeater Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $repeater_fields,
				'placeholder' => __( 'Repeater Field', 'ae-pro' ),
				'default'     => '',
				'object_type' => 'acf_repeater_field',
				'condition'   => [
					'ae_rule_acf_choice_parent_type_name' => 'repeater',
				],
			],

			//Group field
			[
				'label'       => __( 'Group Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $group_field,
				'placeholder' => __( 'Group Field', 'ae-pro' ),
				'default'     => '',
				'object_type' => 'acf_group_field',
				'condition'   => [
					'ae_rule_acf_choice_parent_type_name' => 'group',
				],
			],

			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'ae_rule_acf_choice_acf_repeater_field_name',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'object_type'  => 'acf_repeater_sub_field',
				'condition'    => [
					'ae_rule_acf_choice_parent_type_name' => 'repeater',
				],
			],
			//group field
			[
				'label'           => __( 'Sub Field', 'ae-pro' ),
				'type'            => 'aep-query',
				'parent_field'    => 'ae_rule_acf_choice_acf_group_field_name',
				'query_type'      => 'group-sub-fields',
				'supported_field' => implode( ' ', $supported_field ),
				'placeholder'     => 'Select',
				'object_type'     => 'acf_group_sub_field',
				'condition'       => [
					'ae_rule_acf_choice_parent_type_name' => 'group',
				],
			],
		];
		return $controls;
	}

	public function get_value_control() {
		return [
			'label'       => 'Value',
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Value', 'ae-pro' ),
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
		$field_value = $this->ae_get_acf_field_value( $name, $value );
		return $this->compare( $field_value, $value, $operator );
	}
}
