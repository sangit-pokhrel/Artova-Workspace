<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Date_Archive extends RuleBase {


	public function get_group() {
		return 'archive';
	}

	public function get_name() {
		return 'date_archive';
	}


	public function get_title() {
		return __( 'Date Archive', 'ae-pro' );
	}

	public function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'     => __( 'Is Equal', 'ae-pro' ),
			'not_equal' => __( 'Is Not Equal', 'ae-pro' ),
			'less'      => __( 'Less Than', 'ae-pro' ),
			'greater'   => __( 'Greater Than', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function get_name_control() {
		return [
			'label'   => __( 'Apply To', 'ae-pro' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'year'  => __( 'Year Archive', 'ae-pro' ),
				'month' => __( 'Month Archive', 'ae-pro' ),
				'day'   => __( 'Day Archive', 'ae-pro' ),
			],
			'default' => 'year',
		];
	}


	public function get_value_control() {

		return [

			'label'          => __( 'Value', 'ae-pro' ),
			'type'           => Controls_Manager::DATE_TIME,
			'object_type'    => 'year',
			'default'        => gmdate( 'Y-m-d' ),
			'picker_options' => [
				'enableTime' => false,
				'dateFormat' => 'Y-m-d',
			],
		];
	}

	public function check( $operator, $value, $name = null ) {
		global $post;
		$archive_value = '';
		if ( ! is_date() ) {
			return;
		}
		if ( empty( $value ) ) {
			return;
		}
		$field_value = '';
		$timestamp   = strtotime( $value );
		switch ( $name ) {
			case 'year':
				if ( is_year() ) {
					$archive_value = get_query_var( 'year' );
					$field_value   = gmdate( 'Y', $timestamp );
				}
				break;
			case 'month':
				if ( is_month() ) {
					$archive_year  = get_query_var( 'year' );
					$archive_month = get_query_var( 'monthnum' );
					$archive_value = gmdate( 'Y/m', strtotime( $archive_year . '/' . $archive_month . '/01' ) );
					$field_value   = gmdate( 'Y/m', $timestamp );
					# code...
				}
				break;
			case 'day':
				if ( is_day() ) {
					$archive_year  = get_query_var( 'year' );
					$archive_month = get_query_var( 'monthnum' );
					$archive_day   = get_query_var( 'day' );
					$archive_value = gmdate( 'Y/m/d', strtotime( $archive_year . '/' . $archive_month . '/' . $archive_day ) );
					$field_value   = gmdate( 'Y/m/d', $timestamp );
				}
				break;
		}

		return $this->compare( $archive_value, $field_value, $operator );
	}
}
