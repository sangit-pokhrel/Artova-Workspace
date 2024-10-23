<?php

namespace Aepro\Modules\AcfDynamic\Flexible;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Base_Tag;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Aepro\Frontend;

class AcfFlexibleDynamicHelper {
	public static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public function get_acf_field_value( Base_Tag $tag ) {
		$settings = $tag->get_settings();
		if ( empty( $settings['key'] ) || empty( $settings['flex_sub_field'] ) ) {
			return;
		}
		$parent_field_data = explode( ':', $settings['key'] );
		if ( $parent_field_data[0] === 'option' ) {
			$parent_field_name     = $parent_field_data[2];
			$c_layout              = $parent_field_data[3];
			$data                  = 'option';
			$field                 = get_field_object( $parent_field_data[1] );
			$flexible_content_data = get_field( $parent_field_name, $data );
		} else {
			$parent_field_name     = $parent_field_data[1];
			$c_layout              = $parent_field_data[2];
			$field                 = get_field_object( $parent_field_data[0], get_queried_object() );
			$post_data             = Aepro::$_helper->get_demo_post_data();
			$post_id               = $post_data->ID;
			$data                  = $post_id;
			$flexible_content_data = get_field( $parent_field_name, $data );
		}
		$field_name = $settings['flex_sub_field'];
		$sub_field  = $this->ae_flex_sub_field_object( $field, $c_layout, $field_name );
		if ( empty( $sub_field ) ) {
			return;
		}
		switch ( $sub_field['type'] ) {

			case 'google_map':
			case 'text':
				$value = $this->ae_get_flexible_field_value( $flexible_content_data, $c_layout, $field_name );
				break;
			case 'oembed':
				$value = $this->ae_get_flexible_field_raw_value( $flexible_content_data, $parent_field_name, $field_name, $c_layout, $data, $sub_field );
				break;
			case 'radio':
			case 'checkbox':
			case 'select':
				if ( $sub_field['type'] === 'radio' ) {
									$selected   = [];
									$selected[] = $this->ae_get_flexible_field_value( $flexible_content_data, $c_layout, $field_name );
				} else {
					$selected = $this->ae_get_flexible_field_value( $flexible_content_data, $c_layout, $field_name );
				}
								$value = [];
				if ( ! empty( $selected ) ) {
					switch ( $sub_field['return_format'] ) {
						case 'value':
							foreach ( $sub_field['choices'] as $key => $label ) {
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
							foreach ( $sub_field['choices'] as $key => $label ) {
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
				$value = $this->ae_get_flexible_field_value( $flexible_content_data, $c_layout, $field_name );
		}
		return [ $sub_field, $value ];
	}

	public function ae_flex_sub_field_object( $field, $c_layout, $field_name ) {
		if ( ! empty( $field ) ) {
			$layouts = $field['layouts'];
			foreach ( $layouts as $layout ) {
				if ( $layout['name'] === $c_layout ) {
					$sub_fields = $layout['sub_fields'];
				}
			}
			foreach ( $sub_fields as $sfield ) {
				if ( $sfield['name'] === $field_name ) {
					$sub_field_obj = $sfield;
				}
			}
			return $sub_field_obj;
		}
	}

	public function ae_get_flexible_field_raw_value( $flexible_content_data, $parent_field_name, $field_name, $c_layout, $data, $sub_field ) {
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( Frontend::$_in_flexible_block == false ) {
			if ( get_post_type() === 'ae_global_templates' ) {
				global $post;
				$index          = '';
				$ae_render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
				if ( $ae_render_mode === 'acf_repeater_layout' ) {
					foreach ( $flexible_content_data as $key => $fc ) {
						if ( ! is_array( $fc ) || ( ! array_key_exists( 'acf_fc_layout', $fc ) ) ) {
							return;
						}
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $fc['acf_fc_layout'] == $c_layout ) {
							$index = $key;
							break;
						}
					}
					//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $data != 'option' ) {
						$value = get_post_meta( $data, $parent_field_name . '_' . $index . '_' . $field_name, true );
					} else {
						$value = get_option( 'options_' . $parent_field_name . '_' . $index . '_' . $field_name );
					}
				} else {
					$sub_field_object = get_sub_field_object( $sub_field['name'], false );
					$value            = $sub_field_object['value'];
				}
			} else {
				$sub_field_object = get_sub_field_object( $sub_field['name'], false );
				$value            = $sub_field_object['value'];
			}
		} else {
			$sub_field_object = get_sub_field_object( $sub_field['name'], false );
			$value            = $sub_field_object['value'];
		}
			return $value;
	}

	protected function ae_get_flexible_field_value( $flexible_content_data, $c_layout, $field_name ) {
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( Frontend::$_in_flexible_block == false ) {
			if ( get_post_type() === 'ae_global_templates' ) {
				global $post;
				$index          = '';
				$ae_render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
				if ( $ae_render_mode === 'acf_repeater_layout' ) {
					if ( ! empty( $flexible_content_data ) ) {
						foreach ( $flexible_content_data as $key => $fc ) {
							if ( ! is_array( $fc ) || ( ! array_key_exists( 'acf_fc_layout', $fc ) ) ) {
								return;
							}
							//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							if ( $fc['acf_fc_layout'] == $c_layout ) {
								$index = $key;
								break;
							}
						}
						$value = $flexible_content_data[ $index ][ $field_name ];
					}
				} else {
					$value = get_sub_field( $field_name );
				}
			} else {
				$value = get_sub_field( $field_name );
			}
		} else {
			$value = get_sub_field( $field_name );
		}
		return $value;
	}

}
