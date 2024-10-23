<?php
namespace Aepro\Modules\DynamicCalendar\Classes;

use Aepro\Classes\QueryMaster;
use Elementor\Plugin;

class Query extends QueryMaster {

	public $filter_slug = 'dynamic-calendar';

	public function build_query() {
		$query_args = parent::build_query();

		$settings = $this->settings;

		if ( $settings['calendar_source'] == 'custom_month' ) {
			$month = $settings['calendar_month'];
			$year  = $settings['calendar_year'];

			$date  = apply_filters(
				'aepro/dynamic_calendar/date',
				[
					'month' => $month,
					'year'  => $year,
				]
			);
			$month = $date['month'];
			$year  = $date['year'];

			if ( isset( $_POST['month'] ) ) {
				$month = $_POST['month'];
			}

			if ( isset( $_POST['year'] ) ) {
				$year = $_POST['year'];
			}

			$first_date_of_month = $this->firstDay( $month, $year );
			$last_date_of_month  = $this->lastday( $month, $year );
		} else {
			$month = date( 'n' ); //0-12
			$year  = date( 'Y' ); //four digit

			$date  = apply_filters(
				'aepro/dynamic_calendar/date',
				[
					'month' => $month,
					'year'  => $year,
				]
			);
			$month = $date['month'];
			$year  = $date['year'];

			if ( isset( $_POST['month'] ) ) {
				$month = $_POST['month'];
			}

			if ( isset( $_POST['year'] ) ) {
				$year = $_POST['year'];
			}

			$first_date_of_month = $this->firstDay( $month, $year );
			$last_date_of_month  = $this->lastday( $month, $year );
		}

		if ( $settings['date_source'] == 'modified_date' ) {
			$date_column              = 'post_modified_gmt';
			$query_args['date_query'] = [
				[
					'column' => $date_column,
					'year'   => $year,
					'month'  => $month,
				],
			];
		} elseif ( $settings['date_source'] == 'post_date' ) {
			$date_column                  = 'post_date_gmt';
				$query_args['date_query'] = [
					[
						'column' => $date_column,
						'year'   => $year,
						'month'  => $month,
					],
				];
		} elseif ( $settings['date_source'] == 'custom_field' ) {
			if ( $settings['field_type'] == 'acf_field' ) {
				$query_args['meta_query'] = [
					[
						'key'     => $settings['acf_date_field'], //post meta
						'compare' => '>=',
						'value'   => $first_date_of_month,
						'type'    => 'DATE',
					],
					[
						'key'     => $settings['acf_date_field'], //post meta
						'compare' => '<=',
						'value'   => $last_date_of_month,
						'type'    => 'DATE',
					],
				];
			} elseif ( $settings['field_type'] == 'custom_field' ) {
				$query_args['meta_query'] = [
					[
						'key'     => $settings['custom_date_field'], //post meta
						'compare' => '>=',
						'value'   => $first_date_of_month,
						'type'    => 'DATE',
					],
					[
						'key'     => $settings['custom_date_field'], //post meta
						'compare' => '<=',
						'value'   => $last_date_of_month,
						'type'    => 'DATE',
					],
				];
			}
		}

		return $query_args;
	}

	public function lastday( $month = '', $year = '' ) {
		if ( empty( $month ) ) {
			$month = date( 'm' );
		}
		if ( empty( $year ) ) {
			$year = date( 'Y' );
		}
		$result = strtotime( "{$year}-{$month}-01" );
		$result = strtotime( '-1 second', strtotime( '+1 month', $result ) );
		return date( 'Y-m-d', $result );
	}
	public function firstDay( $month = '', $year = '' ) {
		if ( empty( $month ) ) {
			$month = date( 'm' );
		}
		if ( empty( $year ) ) {
			$year = date( 'Y' );
		}
		$result = strtotime( "{$year}-{$month}-01" );
		return date( 'Y-m-d', $result );
	}
}
