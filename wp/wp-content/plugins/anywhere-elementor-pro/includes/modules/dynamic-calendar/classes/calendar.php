<?php
namespace Aepro\Modules\DynamicCalendar\Classes;

use Elementor\Icons_Manager;
use Stripe\Event;

class Calendar {

	/**
	 * Constructor
	 */
	public function __construct( $month = null, $year = null, $settings = [] ) {
		$this->currentMonth = $month;
		$this->currentYear  = $year;
		$this->settings     = $settings;
		$this->skin         = $settings['_skin'];
	}

	/********************* PROPERTY ********************/

	private $currentYear = 0;

	private $currentMonth = 0;

	private $currentDay = 0;

	private $currentDate = null;

	private $daysInMonth = 0;

	private $naviHref = null;

	private $dateCellText = '';

	private $settings = [];

	private $skin = '';

	/********************* PUBLIC **********************/

	/**
	* print out the calendar
	*/
	public function show( $post_data ) {

		$this->daysInMonth = $this->_daysInMonth( $this->currentMonth, $this->currentYear );

		$content                          = '<div id="ae-dynamic-calendar" class="ae-dc-render">' .
						'<div class="ae-dc-navigation">' .
						$this->create_navi() .
						'</div>' .
						'<div class="ae-dc-content">' .
								'<div class="ae-dc-days-label">' . $this->_createLabels() . '</div>';
								$content .= '<div class="clear"></div>';
								$content .= '<div class="ae-dc-dates">';

								$weeksInMonth = $this->_weeksInMonth( $this->currentMonth, $this->currentYear );
								$weekDays     = 7;

								// Create weeks in a month
		for ( $i = 0; $i < $weeksInMonth; $i++ ) {

			//Create days in a week
			for ( $j = 1;$j <= 7;$j++ ) {
				$content .= $this->show_day( $i * $weekDays + $j, $post_data );
			}
		}

								$content .= '</div>';

								$content .= '<div class="clear"></div>';

						$content .= '</div>';

		$content .= '</div>';
		return $content;
	}

	/********************* PRIVATE **********************/
	/**
	* create the li element for ul
	*/

	private function show_day( $cellNumber, $post_data = [] ) {
		$activeCell = '';
		$blankCell  = '';

		if ( $this->currentDay === 0 ) {
			$firstDayOfTheWeek = gmdate( 'w', strtotime( $this->currentYear . '-' . $this->currentMonth . '-01' ) );
			if ( intval( $cellNumber ) === intval( $firstDayOfTheWeek + 1 ) ) {
				$this->currentDay = 1;
			}
		}

		if ( ( $this->currentDay !== 0 ) && ( $this->currentDay <= $this->daysInMonth ) ) {

			$this->currentDate = gmdate( 'Y-m-d', strtotime( $this->currentYear . '-' . $this->currentMonth . '-' . ( $this->currentDay ) ) );

			$cellContent = $this->currentDay;

			if ( count( $post_data ) && isset( $post_data[ $this->currentDate ] ) ) {
				$cellContent .= '*';
				$cellContent  = sprintf( '<%1$s itemprop="name" %2$s %3$s %4$s>%5$s</%1$s>', 'a', 'href=""', 'class="ae-dc-post-date"', 'data-dc-date="' . $this->currentDate . '"', $cellContent );
				$activeCell   = ' active';
			}

			$this->currentDay++;

		} else {

			$this->currentDate = null;

			$cellContent = null;

			$blankCell = ' blank';
		}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$this->dateCellText = '<span id="ae-dc-date-' . $this->currentDate . '" class="' . ( $cellNumber % 7 == 1 ? ' start ' : ( $cellNumber % 7 == 0 ? ' end ' : ' ' ) ) . ( $cellContent == null ? 'mask' : '' ) . ' ae-dc-date-cell' . $activeCell . $blankCell . '">' . $cellContent . '</span>';

		return $this->dateCellText;
	}

	/**
	* create navigation
	*/
	private function create_navi() {

		$nextMonth = $this->currentMonth === 12 ? 1 : intval( $this->currentMonth ) + 1;

		$nextYear = $this->currentMonth === 12 ? intval( $this->currentYear ) + 1 : $this->currentYear;

		$preMonth = $this->currentMonth === 1 ? 12 : intval( $this->currentMonth ) - 1;

		$preYear = $this->currentMonth === 1 ? intval( $this->currentYear ) - 1 : $this->currentYear;

		$prev_text = 'Prev';

		$next_text = 'Next';

		$prev_icon = '';

		$next_icon = '';

		if ( isset( $this->settings[ $this->skin . '_navigation_prev_icon' ] ) && $this->settings[ $this->skin . '_navigation_prev_icon' ]['value'] !== '' ) {
			if ( $this->settings[ $this->skin . '_navigation_prev_icon' ]['library'] == 'svg' ) {
				$prev_icon = Icons_Manager::render_uploaded_svg_icon( $this->settings[ $this->skin . '_navigation_prev_icon' ]['value'] );
			} else {
				$prev_icon = Icons_Manager::render_font_icon( $this->settings[ $this->skin . '_navigation_prev_icon' ], [], 'i' );
			}

			if ( $this->settings[ $this->skin . '_navigation_icon_position' ] == 'right' ) {
				$prev_text = $prev_text . '<span class="navigation-icon prev-icon icon-after">' . $prev_icon . '</span>';
			} else {
				$prev_text = '<span class="navigation-icon prev-icon icon-before">' . $prev_icon . '</span>' . $prev_text;
			}
		}
		if ( isset( $this->settings[ $this->skin . '_navigation_next_icon' ] ) && $this->settings[ $this->skin . '_navigation_next_icon' ]['value'] != '' ) {
			if ( $this->settings[ $this->skin . '_navigation_next_icon' ]['library'] == 'svg' ) {
				$next_icon = Icons_Manager::render_uploaded_svg_icon( $this->settings[ $this->skin . '_navigation_next_icon' ]['value'] );
			} else {
				$next_icon = Icons_Manager::render_font_icon( $this->settings[ $this->skin . '_navigation_next_icon' ], [], 'i' );
			}

			if ( $this->settings[ $this->skin . '_navigation_icon_position' ] == 'right' ) {
				$next_text = '<span class="navigation-icon next-icon icon-after">' . $next_icon . '</span>' . $next_text;
			} else {
				$next_text .= '<span class="navigation-icon next-icon icon-before">' . $next_icon . '</span>';
			}
		}

		if ( $this->settings[ $this->skin . '_navigation_order' ] == 'title_prev_next' || $this->settings[ $this->skin . '_navigation_order' ] == 'prev_next_title' ) {
			return '<div class="ae-dc-header">' .
				'<span class="ae-dc-title">' . date( 'Y M', strtotime( $this->currentYear . '-' . $this->currentMonth . '-1' ) ) . '</span>' .
				'<span class="ae-dc-prev-next">' .
					'<a class="ae-dc-prev" href="' . $this->naviHref . '?month=' . sprintf( '%02d', $preMonth ) . '&year=' . $preYear . '" data-prev-month="' . $preMonth . '" data-prev-year="' . $preYear . '">' . $prev_text . '</a>' .
					'<a class="ae-dc-next" href="' . $this->naviHref . '?month=' . sprintf( '%02d', $nextMonth ) . '&year=' . $nextYear . '" data-next-month="' . $nextMonth . '" data-next-year="' . $nextYear . '">' . $next_text . '</a>' .
				'</span>' .
			'</div>';
		} else {
			return '<div class="ae-dc-header">' .
				'<a class="ae-dc-prev" href="' . $this->naviHref . '?month=' . sprintf( '%02d', $preMonth ) . '&year=' . $preYear . '" data-prev-month="' . $preMonth . '" data-prev-year="' . $preYear . '">' . $prev_text . '</a>' .
				'<span class="ae-dc-title">' . date( 'Y M', strtotime( $this->currentYear . '-' . $this->currentMonth . '-1' ) ) . '</span>' .
				'<a class="ae-dc-next" href="' . $this->naviHref . '?month=' . sprintf( '%02d', $nextMonth ) . '&year=' . $nextYear . '" data-next-month="' . $nextMonth . '" data-next-year="' . $nextYear . '">' . $next_text . '</a>' .
			'</div>';
		}
	}

	/**
	* create calendar week labels
	*/
	private function _createLabels() {

		$content    = '';
		$day_labels = $this->get_day_labels();

		foreach ( $day_labels as $index => $label ) {

			$content .= '<div class="' . ( $index == 6 ? 'end title' : 'start title' ) . ' title day ae-dc-day-cell">' . $label . '</div>';

		}

		return $content;
	}



	/**
	* calculate number of weeks in a particular month
	*/
	private function _weeksInMonth( $month = null, $year = null ) {

		if ( null == ( $year ) ) {
			$year = date( 'Y', time() );
		}

		if ( null == ( $month ) ) {
			$month = date( 'm', time() );
		}

		// find number of days in this month
		$daysInMonths = $this->_daysInMonth( $month, $year );

		$numOfweeks     = ( $daysInMonths % 7 == 0 ? 0 : 1 ) + intval( $daysInMonths / 7 );
		$monthEndingDay = date( 'w', strtotime( $year . '-' . $month . '-' . $daysInMonths ) );

		$monthStartDay = date( 'w', strtotime( $year . '-' . $month . '-01' ) );
		if ( $monthEndingDay < $monthStartDay ) {

			$numOfweeks++;

		}

		return $numOfweeks;
	}

	/**
	* calculate number of days in a particular month
	*/
	private function _daysInMonth( $month = null, $year = null ) {

		if ( null == ( $year ) ) {
			$year = date( 'Y', time() );
		}

		if ( null == ( $month ) ) {
			$month = date( 'm', time() );
		}
		return date( 't', strtotime( $year . '-' . $month . '-01' ) );
	}

	private function get_day_labels() {

		return [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];
	}

}

//$calendar = new Calendar();

//echo $calendar->show(2022, 6);



