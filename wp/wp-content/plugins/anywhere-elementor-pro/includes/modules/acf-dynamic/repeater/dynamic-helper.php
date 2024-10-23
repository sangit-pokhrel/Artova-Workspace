<?php

namespace Aepro\Modules\AcfDynamic\Repeater;

use Aepro\Aepro;
use Elementor;
use Elementor\Controls_Manager;
use Aepro\Frontend;
use function is_a;
use function is_array;

class DynamicHelper {

	public static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function ae_get_acf_field_groups() {

		$acf_groups = acf_get_field_groups();
		return $acf_groups;
	}

	public function ae_get_acf_fields( $acf_group = [] ) {
		$group_fields = acf_get_fields( $acf_group );
		return $group_fields;
	}

	public function ae_acf_get_repeater() {
		$groups     = [];
		$acf_groups = $this->ae_get_acf_field_groups();
		$blank      = [
			' ' => __( 'Select', 'ae-pro' ),
		];
		$groups[]   = [
			'label'   => 'Select',
			'options' => $blank,
		];
		foreach ( $acf_groups as $acf_group ) {
			$is_on_option_page = false;
			foreach ( $acf_group['location'] as $locations ) {
				foreach ( $locations as $location ) {
					if ( $location['param'] === 'options_page' ) {
						$is_on_option_page = true;
					}
				}
			}
			$only_on_option_page = '';
			if ( $is_on_option_page === true && ( is_array( $acf_group['location'] ) && 1 === count( $acf_group['location'] ) ) ) {
				$only_on_option_page = true;
			}
			$fields  = $this->ae_get_acf_fields( $acf_group );
			$options = [];
			foreach ( $fields as $field ) {
				if ( $field['type'] === 'repeater' ) {
					$u_id = '';
					if ( ! empty( $field['ID'] ) ) {
						$u_id = $field['ID'];
					} else {
						$u_id = $field['key'];
					}
					if ( $only_on_option_page ) {
						$options[ 'option:' . $u_id . ':' . $field['name'] ] = 'Option:' . $field['label'];
					} else {
						if ( $is_on_option_page === true ) {
							$options[ 'option:' . $u_id . ':' . $field['name'] ] = 'Option:' . $field['label'];
						}

						$options[ 'post:' . $u_id . ':' . $field['name'] ] = $field['label'];
					}
				}
			}
			if ( ! empty( $options ) ) {
				$groups[] = [
					'label'   => $acf_group['title'],
					'options' => $options,
				];
			}
		}
		return $groups;
	}

	public function ae_get_group_fields( $tag, $sup_fields ) {
		global $post;
		if ( ! $post ) {
			return false;
		}
		$post_id   = $post->ID;
		$post_type = $post->post_type;
		if ( $post_type === 'ae_global_templates' ) {
			$rule_type         = get_post_meta( $post_id, 'ae_rule_post_type', true );
			$selected_repeater = get_post_meta( $post_id, 'ae_acf_repeater_name', true );
		}

		$default    = ' ';
		$acf_groups = $this->ae_get_acf_field_groups();
		$repeaters  = $this->ae_acf_get_repeater();
		$tag->add_control(
			'acf_repeater',
			[
				'label'   => __( 'Repeater', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'groups'  => $repeaters,
				'default' => $default,
			]
		);

		foreach ( $acf_groups as $acf_group ) {
			$fields = $this->ae_get_acf_fields( $acf_group );

			foreach ( $fields as $field ) {
				$u_id = '';
				if ( ! empty( $field['ID'] ) ) {
					$u_id = $field['ID'];
				} else {
					$u_id = $field['key'];
				}
				if ( $field['type'] === 'repeater' ) {

					$tag->add_control(
						$u_id . ':' . $field['name'],
						[
							'label'     => __( 'Sub Field', 'ae-pro' ),
							'type'      => Controls_Manager::SELECT,
							'options'   => $this->ae_acf_get_group_fields( $u_id, $sup_fields ),
							'condition' => [
								'acf_repeater' => [
									'post:' . $u_id . ':' . $field['name'],
									'option:' . $u_id . ':' . $field['name'],
								],
							],
						]
					);
				}
			}
		}
	}

	public function ae_acf_get_group_fields( $field_id, $sup_fields ) {
		$options = [
			'' => __( '-- Select --', 'ae-pro' ),
		];
		$field   = acf_get_field( $field_id );
		if ( $field !== '' ) {
			$sub_fields = $field['sub_fields'];
			if ( is_array( $sub_fields ) ) {
				foreach ( $sub_fields as $sub_field ) {
					if ( in_array( $sub_field['type'], $sup_fields, true ) ) {
						$options[ $sub_field['name'] ] = $sub_field['label'];
					}
				}
			}
		}
		return $options;
	}

	public function key_name( $settings, $key ) {
		return 'acf_repeater_field_' . $key;
	}

	public function get_repeater_data( $settings ) {
		$value = '';
		if ( ! empty( $settings['acf_repeater'] ) ) {
			$repeater_data = explode( ':', $settings['acf_repeater'] );
		}
		if ( ! empty( $repeater_data[0] ) && ! empty( $repeater_data[1] ) && ! empty( $repeater_data[2] ) ) {
			$repeater_is = $repeater_data[0];
			$repeater    = $repeater_data[2];
			$field_name  = $settings[ $repeater_data[1] . ':' . $repeater_data[2] ];
		}

		if ( ! empty( $repeater ) && ! empty( $field_name ) ) {
			if ( ! Frontend::$_in_repeater_block ) {
				if ( $repeater_is === 'option' ) {
					$repeater_field = get_field( $repeater, 'option' );
					if ( is_array( $repeater_field ) && ! empty( $repeater_field[0][ $field_name ] ) ) {
						$value = $repeater_field[0][ $field_name ];
					}
				} else {
					$post_data      = Aepro::$_helper->get_demo_post_data();
					$post_id        = $post_data->ID;
					$repeater_field = get_field( $repeater, $post_id );
					if ( is_array( $repeater_field ) && ! empty( $repeater_field[0][ $field_name ] ) ) {
						$value = $repeater_field[0][ $field_name ];
					}
				}
			} else {
				if ( ! empty( $field_name ) ) {
					$value = get_sub_field( $field_name );
				}
			}
			return $value;
		}
	}
}
