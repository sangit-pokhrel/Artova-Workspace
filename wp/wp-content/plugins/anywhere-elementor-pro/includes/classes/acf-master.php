<?php

namespace Aepro\Classes;

use Aepro\Aepro;

class AcfMaster {


	private static $_instance = null;


	protected $post_id;

	protected $field_name;

	protected $field_list;

	protected $field_types;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected function set_field_types() {

		$acf_free = [

			'text'     => 'Text',
			'textarea' => 'Text Area',
			'number'   => 'Text Area',
			'range'    => 'Text Area',
			'email'    => 'Text Area',
			'url'      => 'Text Area',
			'password' => 'Text Area',
			'image'    => 'Text Area',
			'file'     => 'Text Area',
			'wysiwyg'  => 'Text Area',
			'oembed'   => 'Text Area',
			'gallery'  => 'Text Area',

		];

		$this->field_types = $acf_free;
	}

	/**
	 * @param $data
	 * @param $field_name
	 * @param $field_type
	 *
	 * $data -
	 * $field_name - Key for ACF Field
	 * $field_type - term, post, option, user
	 *
	 * @return mixed|string
	 */
	public function get_field_value( $field_args ) {

		$field_value = '';
		$format      = true;
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( isset( $field_args['acf_format'] ) && $field_args['acf_format'] == '0' ) {
			$format = false;
		}
		switch ( $field_args['field_type'] ) {

			case 'post':
				$post = Aepro::$_helper->get_demo_post_data();
				if ( $field_args['is_sub_field'] === 'repeater' ) {
					$field_value = $this->get_repeater_field_data( $field_args['field_name'], $field_args['parent_field'], $post->ID );
				} elseif ( $field_args['is_sub_field'] === 'group' ) {
					$field_value = $this->get_group_field_data( $field_args['field_name'], $field_args['parent_field'], $post->ID );
				} elseif ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_value = $this->get_flexible_field_data( $field_args, $post->ID );
				} else {
					$field_value = get_field( $field_args['field_name'], $post->ID, $format );
				}

				break;

			case 'term':
				$term = Aepro::$_helper->get_preview_term_data();
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				//if ( isset( $field_args['acf_format'] ) && $field_args['acf_format'] == '1' ) {
				//	$field_value = get_field( $field_args['field_name'], $term['taxonomy'] . '_' . $term['prev_term_id'], true );
				//} else {
					$field_value = get_field( $field_args['field_name'], $term['taxonomy'] . '_' . $term['prev_term_id'], $format );
				//}

				break;

			case 'user':   // Get current author of current archive using queries object
							$author      = Aepro::$_helper->get_preview_author_data();
							$field_value = get_field( $field_args['field_name'], 'user_' . $author['prev_author_id'], $format );

				break;

			case 'option': // Get Option page's field value
				if ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_value = $this->get_flexible_field_data( $field_args, 'option' );
				}if ( $field_args['is_sub_field'] === 'repeater' ) {
					$field_value = $this->get_repeater_field_data( $field_args['field_name'], $field_args['parent_field'], 'option' );
				} else {
					$field_value = get_field( $field_args['field_name'], 'option', $format );
				}
				break;

		}

		return $field_value;
	}


	public function get_flexible_field_data( $field_args, $data ) {
		$value      = '';
		$field_name = $field_args['field_name'];
		if ( empty( $field_name ) ) {
			return;
		}
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			if ( empty( $field_args['flexible_field'] ) ) {
				return;
			}
			$parent_field_data = explode( ':', $field_args['flexible_field'] );
			if ( $parent_field_data[0] === 'option' ) {
				$parent_field_name = $parent_field_data[2];
				$layout            = $parent_field_data[3];
				$data              = 'option';
			} else {
				$parent_field_name = $parent_field_data[1];
				$layout            = $parent_field_data[2];
			}
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( get_post_type() == 'ae_global_templates' ) {
				global $post;
				$ae_render_mode = get_post_meta( $post->ID, 'ae_render_mode', true );
				if ( $ae_render_mode === 'acf_repeater_layout' ) {
					$flexible_content = get_field( $parent_field_name, $data );
					foreach ( $flexible_content as $key => $fc ) {
						if ( ! is_array( $fc ) || ( ! array_key_exists( 'acf_fc_layout', $fc ) ) ) {
							return;
						}
						//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						if ( $fc['acf_fc_layout'] == $layout ) {
							$index = $key;
							break;
						}
					}
					$value = $flexible_content[ $index ][ $field_name ];
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


	public function get_group_field_data( $field_name, $group_field, $data_id ) {

		$group_fields_arr = explode( '.', $group_field );

		$main_field = get_field( $group_fields_arr[0], $data_id );

		$leaf = $main_field;

		foreach ( $group_fields_arr as $rf ) {

			if ( $rf === $group_fields_arr[0] ) {
				continue;
			}

			if ( isset( $leaf[0][ $rf ] ) ) {
				$leaf = $main_field[0][ $rf ];
			} else {
				break;
			}
		}

		if ( ! isset( $leaf[ $field_name ] ) ) {
			$value = '';
			return $value;
		}

		$value = $leaf[ $field_name ];
		return $value;
	}

	public function get_repeater_field_data( $field_name, $repeater_field, $data_id ) {
		$repeater = Aepro::$_helper->is_repeater_block_layout();

		if ( isset( $repeater['field'] ) ) {
			// editing a block layout. Return first item matched

			$repeater_fields_arr = explode( ':', $repeater_field );

			if ( $data_id === 'option' ) {
				$main_field = get_field( $repeater_fields_arr[1], $data_id );
				array_shift( $repeater_fields_arr );
				array_shift( $repeater_fields_arr );
			} else {
				$main_field = get_field( $repeater_fields_arr[0], $data_id );
				array_shift( $repeater_fields_arr );
			}

			$leaf  = $main_field;
			$value = $this->get_repeater_leaf_value( $main_field, $repeater_fields_arr, $field_name );

		} else {
			// fetch data using get_sub_field.
			$repeater_fields_arr = explode( ':', $repeater_field );
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( is_array( $repeater_fields_arr ) && count( $repeater_fields_arr ) == 1 ) {
				return get_sub_field( $field_name );
			} else {
				// Todo:: Nested Repeater Fields
				return get_sub_field( $field_name );
			}
		}
		return $value;
	}

	public function get_repeater_leaf_value( $data, $field_arr, $field_name ) {
		if ( count( $field_arr ) === 0 ) {
			return $data[0][ $field_name ];
		}

		$data = $data[0][ $field_arr[0] ];
		array_shift( $field_arr );
		return $this->get_repeater_leaf_value( $data, $field_arr, $field_name );
	}

	protected function get_sub_field_data() {
	}

	public function get_field_object( $field_args, $data ) {
		switch ( $field_args['field_type'] ) {
			case 'post':
				if ( $field_args['is_sub_field'] === 'repeater' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} elseif ( $field_args['is_sub_field'] === 'group' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} elseif ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} else {
					$field_object = get_field_object( $field_args['field_name'], $data );
				}
				break;

			case 'term':
				$term         = get_term_by( 'term_taxonomy_id', $data['prev_term_id'] );
				$field_object = get_field_object( $field_args['field_name'], $term );
				break;
			case 'option':
				if ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} else {
					$field_object = get_field_object( $field_args['field_name'], $data );
				}
				break;
			case 'user':
				$field_object = get_field_object( $field_args['field_name'], $data );
				break;
		}
		return $field_object;
	}

	public function get_repeater_parent_field( $field ) {
		$repeater_fields = $field;
		$splits          = explode( ':', $repeater_fields );
		if ( is_array( $splits ) && count( $splits ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $splits[0] == 'option' ) {
				array_shift( $splits );
				if ( count( $splits ) === 1 ) {
					$parent_field = $splits[0];
				} else {
					array_shift( $splits );
					$parent_field = $this->get_repeater_parent_field( $splits );
				}
				// options field - get data accordingly
			} else {
				if ( count( $splits ) === 1 ) {
					$parent_field = $splits[0];
				} else {
					array_shift( $splits );
					$parent_field = $this->get_repeater_parent_field( implode( $splits ) );
				}
				// get sub fields of root field
			}
			return $parent_field;
		}
	}

	// 2.25 Repeater Field Object,(Nested Repeater)
	public function get_nested_repeater_parent_field_obj( $main_field, $parent_field ) {
		$splits = explode( ':', $main_field );
		if ( is_array( $splits ) && count( $splits ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $splits[0] == 'option' ) {
				// options field - get data accordingly
				$fields = acf_get_field( $splits[1] );
				array_shift( $splits );
				$sub_field = $this->get_nested_repeater_fields( $fields, $splits, $parent_field );

			} else {
				// get sub fields of root field
				$fields    = acf_get_field( $splits[0] );
				$sub_field = $this->get_nested_repeater_fields( $fields, $splits, $parent_field );
			}
			return $sub_field;
		}
	}
	// 2.25 Repeater Parent Field(Nested Repeater)
	public function get_nested_repeater_fields( $fields, $splits, $parent_field ) {
		if ( count( $splits ) === 1 ) {
			$sub_field = $fields['sub_fields'];
			foreach ( $fields['sub_fields'] as $field ) {
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $field['name'] == $parent_field ) {
					$sub_field = $field;
				}
			}
		} else {
			array_shift( $splits );
			foreach ( $fields['sub_fields'] as $field ) {
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $field['name'] == $splits[0] ) {
					$sub_field = $this->get_nested_repeater_fields( $field, $splits, $parent_field );
				}
			}
		}
		return $sub_field;
	}


	public function get_sub_field_object( $field_args, $data ) {
		if ( empty( $field_args['parent_field'] ) ) {
			return [];
		}
		$choices      = [];
		$parent_field = $field_args['parent_field'];
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $field_args['is_sub_field'] == 'flexible' ) {
			$parent_field_data = explode( ':', $field_args['flexible_field'] );
			if ( $parent_field_data[0] === 'option' ) {
				$data = 'option';
			}
		}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $field_args['is_sub_field'] == 'repeater' ) {
			$parent_field        = $this->get_repeater_parent_field( $field_args['parent_field'] );
			$parent_field_object = $this->get_nested_repeater_parent_field_obj( $field_args['parent_field'], $parent_field );
		} else {
			$fields_arr = get_field_object( $parent_field, $data );
		}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $field_args['is_sub_field'] == 'flexible' ) {
			$layouts = $fields_arr['layouts'];
			foreach ( $layouts as $key => $layout ) {
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $layout['name'] == $field_args['layout'] ) {
					if ( ! array_key_exists( 'sub_fields', $layout ) ) {
						return;
					}
					$sub_fields = $layout['sub_fields'];
				}
			}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		} elseif ( $field_args['is_sub_field'] == 'repeater' ) {
			$sub_fields = $parent_field_object;
		} else {
			$sub_fields = $fields_arr['sub_fields'];
		}
		foreach ( $sub_fields as $sfield ) {
			if ( $sfield['type'] === $field_args['_skin'] ) {
				if ( $sfield['name'] === $field_args['field_name'] ) {
					$choices = $sfield;
				}
			}
		}
		return $choices;
	}
}

AcfMaster::instance();
