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

class Acf_Taxonomy extends RuleBase {



	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_taxonomy';
	}


	public function get_title() {
		return __( 'ACF Taxonomy', 'ae-pro' );
	}

	public function get_multiple_name_control() {
		$acf_fields      = Aepro::$_helper->ae_get_acf_fields( [ 'taxonomy' ] );
		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$group_field     = Aepro::$_helper->get_group_fields();
		$supported_field = [ 'taxonomy' ];
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
					'ae_rule_acf_taxonomy_parent_type_name' => 'none',
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
					'ae_rule_acf_taxonomy_parent_type_name' => 'repeater',
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
					'ae_rule_acf_taxonomy_parent_type_name' => 'group',
				],
			],
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'ae_rule_acf_taxonomy_acf_repeater_field_name',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'object_type'  => 'acf_repeater_sub_field',
				'condition'    => [
					'ae_rule_acf_taxonomy_parent_type_name' => 'repeater',
				],
			],
			//group field
			[
				'label'           => __( 'Sub Field', 'ae-pro' ),
				'type'            => 'aep-query',
				'parent_field'    => 'ae_rule_acf_taxonomy_acf_group_field_name',
				'query_type'      => 'group-sub-fields',
				'supported_field' => implode( ' ', $supported_field ),
				'placeholder'     => 'Select',
				'object_type'     => 'acf_group_sub_field',
				'condition'       => [
					'ae_rule_acf_taxonomy_parent_type_name' => 'group',
				],
			],
		];
		return $controls;
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
				'condition_name' => 'ae_rule_acf_taxonomy_types',
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
		global $post;
		$field_value = null;
		if ( is_array( $name ) && array_key_exists( 'parent_type', $name ) ) {
			$parent = $name['parent_type'];
		} else {
			$parent = 'none';
		}
		switch ( $parent ) {
			case 'repeater':
				if ( empty( $name['parent_type'] ) || empty( $name['sub_field'] ) ) {
									return;
				}
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( Frontend::$_in_repeater_block == true ) {
					$sub_field_obj = get_sub_field_object( $name['sub_field'] );
					if ( ! empty( $sub_field_obj ) ) {
						$return_format = $sub_field_obj['return_format'];
						$multiple      = $sub_field_obj['multiple'];
						$posts         = $sub_field_obj['value'];
						if ( empty( $posts ) ) {
							return;
						}
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $multiple == 0 ) {
							$selected_posts[] = $posts;
						} else {
							$selected_posts = $posts;
						}
						if ( ( ! empty( $selected_posts ) ) && 'object' === $return_format ) {
							foreach ( $selected_posts as $selected_post ) {

								$field_value[] = $selected_post->ID;
							}
						} else {
							$field_value = get_sub_field( $name['sub_field'] );
						}
					}
				}
				break;
			case 'group':
				if ( empty( $name['parent_type'] ) || empty( $name['sub_field'] ) ) {
									return;
				}
								$selected_post = null;
								$field_data    = explode( ':', $name['parent_field'] );
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( isset( $field_data[0] ) && $field_data[0] == 'option' ) {
					$data            = 'option';
					$grp_field_value = get_field( $field_data[1], $data );
					$grp_field_obj   = get_field_object( $field_data[1], $data );
					if ( ! $grp_field_obj ) {
						return;
					}
					$sub_fields = $grp_field_obj['sub_fields'];
					foreach ( $sub_fields as $sub_field ) {
						if ( $sub_field['name'] === $name['sub_field'] ) {
							$sub_field_obj = $sub_field;
						}
					}
					$return_format = $sub_field_obj['return_format'];
					$taxonomies    = $grp_field_value[ $name['sub_field'] ];
					if ( empty( $taxonomies ) ) {
						return;
					}
					if ( ! empty( $taxonomies ) && 'object' === $return_format ) {
						foreach ( $taxonomies as $key => $taxonomy ) {
							$field_value[ $key ] = $taxonomy->term_id;
						}
					} else {
						$field_value = $grp_field_value[ $name['sub_field'] ];
					}
				} else {
					$post_data       = Aepro::$_helper->get_demo_post_data();
					$data            = $post_data->ID;
					$grp_field_value = get_field( $name['parent_field'], $data );
					$grp_field_obj   = get_field_object( $name['parent_field'], $data );
					if ( ! $grp_field_obj ) {
						return;
					}
					$sub_fields = $grp_field_obj['sub_fields'];
					foreach ( $sub_fields as $sub_field ) {
						if ( $sub_field['name'] === $name['sub_field'] ) {
							$sub_field_obj = $sub_field;
						}
					}
					$return_format = $sub_field_obj['return_format'];
					$taxonomies    = $grp_field_value[ $name['sub_field'] ];
					if ( empty( $taxonomies ) ) {
						return;
					}
					if ( ! empty( $taxonomies ) && 'object' === $return_format ) {
						foreach ( $taxonomies as $key => $taxonomy ) {
							$field_value[ $key ] = $taxonomy->term_id;
						}
					} else {
						$field_value = $grp_field_value[ $name['sub_field'] ];
					}
				}
				break;
			case 'none':
				if ( empty( $name ) ) {
									return;
				}
								$field_data = explode( ':', $name );
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( isset( $field_data[0] ) && $field_data[0] == 'options' ) {
					$data         = 'option';
					$field_object = get_field_object( $field_data[1], $data );
					if ( ! empty( $field_object ) ) {
						$return_format = $field_object['return_format'];
						if ( ! empty( $field_object['value'] ) && 'object' === $return_format ) {
							$taxonomies = $field_object['value'];
							foreach ( $taxonomies as $key => $taxonomy ) {
								# code...
								$field_value[ $key ] = $taxonomy->term_id;
							}
						} else {
							$field_value = get_field( $field_data[1], $data, true );
						}
					}
				} else {
					$post_data    = Aepro::$_helper->get_demo_post_data();
					$data         = $post_data->ID;
					$field_object = get_field_object( $name, $data );
					if ( ! empty( $field_object ) ) {
						$return_format = $field_object['return_format'];
						if ( ! empty( $field_object['value'] ) && 'object' === $return_format ) {
							$taxonomies = $field_object['value'];
							foreach ( $taxonomies as $key => $taxonomy ) {
								# code...
								$field_value[ $key ] = $taxonomy->term_id;
							}
						} else {
							$field_value = get_field( $name, $data, true );
						}
					}
				}
				break;
		}
		return $this->compare( $field_value, $value, $operator );
	}
}
