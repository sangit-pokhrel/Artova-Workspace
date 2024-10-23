<?php

namespace Aepro\Modules\DynamicRules\Rules;

use AePro\AePro;
use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Default_Page extends RuleBase {



	public function get_group() {
		return 'single';
	}

	public function get_name() {
		return 'default_page';
	}


	public function get_title() {
		return __( 'Default Page', 'ae-pro' );
	}

	public function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'     => __( 'Is Equal', 'ae-pro' ),
			'not_equal' => __( 'Is Not Equal', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function get_value_control() {
		return [
			'label'   => __( 'Value', 'ae-pro' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'home',
			'options' => [
				'home'   => __( 'Homepage', 'ae-pro' ),
				'blog'   => __( 'Blog', 'ae-pro' ),
				'static' => __( 'Front Page', 'ae-pro' ),
				'404'    => __( '404 Page', 'ae-pro' ),
			],
		];
	}

	public function check( $operator, $value, $name = null ) {

		if ( 'home' === $value ) {
			return $this->compare( ( is_front_page() && is_home() ), true, $operator );
		} elseif ( 'blog' === $value ) {
			return $this->compare( ( ! is_front_page() && is_home() ), true, $operator );
		} elseif ( 'static' === $value ) {
			return $this->compare( ( is_front_page() && ! is_home() ), true, $operator );
		} elseif ( '404' === $value ) {
			return $this->compare( is_404(), true, $operator );
		}
	}
}
