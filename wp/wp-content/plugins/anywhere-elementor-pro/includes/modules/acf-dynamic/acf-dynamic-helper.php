<?php

namespace Aepro\Modules\AcfDynamic;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Base_Tag;

class AcfDynamicHelper {

	public static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function ae_get_acf_group( $sup_field ) {
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
				if ( in_array( $field['type'], $sup_field, true ) ) {
					if ( $only_on_option_page ) {
						$options[ 'options:' . $field['name'] ] = 'Option:' . $field['label'];
					} else {
						if ( $is_on_option_page === true ) {
							$options[ 'options:' . $field['name'] ] = 'Option:' . $field['label'];
						}

						$options[ $field['key'] . ':' . $field['name'] ] = $field['label'];
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

	// For use by ACF tags
	public static function get_acf_field_value( Base_Tag $tag ) {

		$key = $tag->get_settings( 'key' );
		if ( empty( $key ) ) {
			return;
		}
		if ( ! empty( $key ) ) {
			list($field_key, $meta_key) = explode( ':', $key );
			if ( $field_key === 'options' ) {
				$data    = 'option';
				$field   = get_field_object( $meta_key, 'option' );
				$post_id = 'option';
			} else {
				$field     = get_field_object( $field_key, get_queried_object() );
				$post_data = Aepro::$_helper->get_demo_post_data();
				$post_id   = $post_data->ID;
			}
			if ( empty( $field ) ) {
				return;
			}
			switch ( $field['type'] ) {
				case 'oembed':
				case 'google_map':
					$value = get_post_meta( $post_id, $field['name'], true );
					break;
				case 'radio':
				case 'checkbox':
				case 'select':
					if ( $field['type'] === 'radio' ) {
						$selected   = [];
						$selected[] = get_field( $field['name'], $post_id );
					} else {
						$selected = get_field( $field['name'], $post_id );
					}

					$value = [];
					if ( ! empty( $selected ) ) {
						switch ( $field['return_format'] ) {
							case 'value':
								foreach ( $field['choices'] as $key => $label ) {
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
								foreach ( $field['choices'] as $key => $label ) {
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
					$value = get_field( $field['name'], $post_id );
			}

			return [ $field, $meta_key, $value ];
		}

		return [];
	}
}
