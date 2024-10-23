<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search_Results extends RuleBase {


	public function get_group() {
		return 'archive';
	}

	public function get_name() {
		return 'search_results';
	}


	public function get_title() {
		return __( 'Search Archive', 'ae-pro' );
	}


	public function get_name_control() {
		return [
			'label'   => 'Search',
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'term'          => __( 'Search Term', 'ae-pro' ),
				'search_result' => __( 'Search Result', 'ae-pro' ),
			],
			'default' => 'term',
		];
	}

	public function get_value_control() {
		return [
			'label'       => 'Value',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Value', 'ae-pro' ),
		];
	}


	public function check( $operator, $value, $name = null ) {
		global $post;
		$archive_value = '';
		if ( ! is_search() ) {
			return;
		}
		if ( $name === 'term' ) {
			$archive_value = get_search_query();
		} else {
			global $wp_query;
			$archive_value = $wp_query->found_posts;
		}
		return $this->compare( $archive_value, $value, $operator );
	}
}
