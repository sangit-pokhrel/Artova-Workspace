<?php

namespace Aepro\Modules\QueryControl\Types;

use Aepro\Modules\QueryControl\TypeBase;

class FlexSubFields extends TypeBase {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'flex-sub-fields';
	}

	public function get_autocomplete_values( array $request ) {
		$fields     = [];
		$sub_fields = $this->get_field_data( $request );
		if ( array_key_exists( 'supported_field', $request ) ) {
			$supported_field = explode( ' ', $request['supported_field'] );
			foreach ( $sub_fields as $sub_field ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $sub_field['type'], $supported_field ) ) {
					$fields[] = [
						'id'   => $sub_field['name'],
						'text' => $sub_field['label'],
					];
				}
			}
		} else {
			foreach ( $sub_fields as $sub_field ) {
				$fields[] = [
					'id'   => $sub_field['name'],
					'text' => $sub_field['label'],
				];
			}
		}

		// GET Sub Fields and return in following format.
		return $fields;
	}

	public function get_value_titles( array $request ) {

		$selected_field = $request['id'];
		$sub_fields     = $this->get_field_data( $request );
		foreach ( $sub_fields as $sub_field ) {
			if ( $sub_field['name'] === $selected_field ) {
				$result[ $sub_field['name'] ] = $sub_field['label'];
				break;
			}
		}
		// Put proper validation for missing data
		// get subfield and return in following format.
		return $result;
	}

	public function get_field_data( $request ) {

		$parent_field_data = explode( ':', $request['flex_parent_field'] );
		if ( $parent_field_data[0] === 'option' ) {
			$field_id          = $parent_field_data[1];
			$parent_field_name = $parent_field_data[2];
			$ulayout           = $parent_field_data[3];
		} else {
			$field_id          = $parent_field_data[0];
			$parent_field_name = $parent_field_data[1];
			$ulayout           = $parent_field_data[2];
		}

		$field = acf_get_field( $field_id );

		if ( ! empty( $field ) ) {
			$layouts = $field['layouts'];
			foreach ( $layouts as $layout ) {
				if ( $layout['name'] === $ulayout ) {
					$sub_fields = $layout['sub_fields'];
				}
			}
		}
		return $sub_fields;
	}

}
