<?php

namespace Aepro\Modules\AcfDynamic\Group;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Base_Tag;
use Elementor\Controls_Manager;

class AcfGroupDynamicHelper {

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

	public function ae_get_group_fields() {

		$groups     = [];
		$acf_groups = acf_get_field_groups();
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
			$fields  = acf_get_fields( $acf_group );
			$options = [];
			foreach ( $fields as $field ) {
				if ( $field['type'] === 'group' ) {
					if ( $only_on_option_page ) {
						$options[ 'option:' . $field['key'] . ':' . $field['name'] ] = 'Option: ' . $field['label'];
					} else {
						if ( $is_on_option_page === true ) {
							$options[ 'option:' . $field['key'] . ':' . $field['name'] ] = 'Option: ' . $field['label'];
						}

						$options[ 'post:' . $field['key'] . ':' . $field['name'] ] = $field['label'];
					}
				}
			}
			if ( empty( $options ) ) {
				continue;
			}

			if ( 1 === count( $options ) ) {
				$options = [ -1 => ' -- ' ] + $options;
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

	public function register_ae_dynamic_group_controls( $tag, $sup_fields ) {

		$acf_groups   = $this->ae_get_acf_field_groups();
		$group_fields = $this->ae_get_group_fields();

		$tag->add_control(
			'key',
			[
				'label'   => __( 'Group Field', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'groups'  => $group_fields,
				'default' => '',
			]
		);
		$tag->add_control(
			'group_sub_field',
			[
				'label'           => __( 'Sub Field', 'ae-pro' ),
				'type'            => 'aep-query',
				'parent_field'    => 'key',
				'supported_field' => implode( ' ', $sup_fields ),
				'query_type'      => 'group-sub-fields',
				'placeholder'     => 'Select',
			]
		);
	}

	public function ae_acf_get_group_sub_fields( $field_id, $sup_fields ) {
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

	public static function get_acf_field_value( Base_Tag $tag ) {

		$settings = $tag->get_settings();

		if ( empty( $settings['key'] ) ) {
			return;
		}

		$group_data = explode( ':', $settings['key'] );

		if ( ! empty( $group_data[0] ) && ! empty( $group_data[1] ) && ! empty( $group_data[2] ) ) {
			$field_loc = $group_data[0];

			$group_field = $group_data[2];
			if ( 'option' === $field_loc ) {

				$field            = get_field_object( $group_data[1] );
				$group_field_data = get_field( $group_field, 'option' );
			} else {
				$field            = get_field_object( $group_data[1], get_queried_object() );
				$post_data        = Aepro::$_helper->get_demo_post_data();
				$post_id          = $post_data->ID;
				$group_field_data = get_field( $group_field, $post_id );
			}
			$sub_field = $settings['group_sub_field'];
		}

		if ( empty( $sub_field ) ) {
			return;
		}

		$sub_fields = $field['sub_fields'];

		if( !is_array($sub_fields) ){
			return;
		}
		
		foreach ( $sub_fields as $sfield ) {
			if ( $sfield['name'] === $sub_field ) {
				$sub_field_obj = $sfield;
			}
		}

		if ( is_array($group_field_data) && array_key_exists( $sub_field, $group_field_data ) ) {

			switch ( $sub_field_obj['type'] ) {
				case 'oembed':
				case 'google_map':
					if ( $field_loc === 'option' ) {
						$value = get_option( 'options_' . $group_data[2] . '_' . $sub_field );
					} else {
						$value = $group_field_data[ $sub_field ];
						$value = get_post_meta( $post_id, $group_field . '_' . $sub_field, true );
					}
					break;
				case 'radio':
				case 'checkbox':
				case 'select':
					if ( $sub_field_obj['type'] === 'radio' ) {
						$selected   = [];
						$selected[] = $group_field_data[ $sub_field ];
					} else {
						$selected = $group_field_data[ $sub_field ];
					}
					$value = [];
					if ( ! empty( $selected ) ) {
						switch ( $sub_field_obj['return_format'] ) {
							case 'value':
								foreach ( $sub_field_obj['choices'] as $key => $label ) {
									if ( is_array( $selected ) ) {
										if ( in_array( $key, $selected, true ) ) {
											$value[ $key ] = $label;
										}
									} else {
										if ( $key === $selected ) {
											$value[ $key ] = $label;
										}
									}
								}
								break;
							case 'label':
								foreach ( $sub_field_obj['choices'] as $key => $label ) {
									if ( is_array( $selected ) ) {
										if ( in_array( $label, $selected, true ) ) {
											$value[ $key ] = $label;
										}
									} else {
										if ( $label === $selected ) {
											$value[ $key ] = $label;
										}
									}
								}
								break;
							case 'array':
								$is_nested_array = false;
								if ( array_key_exists( 0, $selected ) ) {
									$is_nested_array = true;
								}
								$selected_size = count( $selected );
								if ( $is_nested_array ) {
									foreach ( $selected as $select ) {
										$value[ $select['value'] ] = $select['label'];
									}
								} else {
									$value[ $selected['value'] ] = $selected['label'];
								}

								break;
						}
					}
					break;
				default:
					$value = $group_field_data[ $sub_field ];

			}
			return [ $sub_field_obj, $value ];
		}
	}
}
