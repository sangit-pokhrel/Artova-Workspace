<?php

namespace Aepro\Modules\QueryControl\Types;

use Aepro\Modules\QueryControl\TypeBase;

class RepeaterSubFields extends TypeBase {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'repeater-sub-fields';
	}

	public function get_autocomplete_values( array $request ) {

		$fields = [];

		$sub_fields = $this->get_field_data( $request );
		foreach ( $sub_fields as $sub_field ) {
			if ( isset( $sub_field['sub_fields'] ) ) {
				continue;
			}
			$fields[] = [
				'id'   => $sub_field['name'],
				'text' => $sub_field['label'],
			];
		}
		// GET Sub Fields and return in following format.
		return $fields;
	}

	public function get_value_titles( array $request ) {
		$selected_field = $request['id'];

		$sub_fields = $this->get_field_data( $request );

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

		$repeater_fields = $request['repeater_parent_field'];

		// split sub repeater fields
		$splits = explode( ':', $repeater_fields );

		if ( is_array( $splits ) && count( $splits ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $splits[0] == 'option' ) {
				// options field - get data accordingly

				$fields = acf_get_field( $splits[1] );
				array_shift( $splits );
				$sub_fields = $this->get_sub_fields( $fields, $splits );
			} else {
				// get sub fields of root field

				$fields = acf_get_field( $splits[0] );

				$sub_fields = $this->get_sub_fields( $fields, $splits );
			}

			return $sub_fields;

		}
	}

	public function get_sub_fields( $fields, $splits ) {
		if ( count( $splits ) === 1 ) {
			if ( isset( $fields['sub_fields'] ) ) {
				return $fields['sub_fields'];
			} else {
				$sub_fields = [];
			}
		} else {
			array_shift( $splits );
			foreach ( $fields['sub_fields'] as $field ) {
				//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $field['name'] == $splits[0] ) {
					$sub_fields = $this->get_sub_fields( $field, $splits );
				}
			}
		}

		return $sub_fields;
	}

}
