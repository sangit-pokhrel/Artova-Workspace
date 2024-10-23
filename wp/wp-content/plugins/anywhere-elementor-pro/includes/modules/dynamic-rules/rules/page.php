<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Page extends RuleBase {



	public function get_group() {
		return 'single';
	}

	public function get_name() {
		return 'page';
	}


	public function get_title() {
		return __( 'Page', 'ae-pro' );
	}

	public function get_value_control() {
		return [
			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => 'aep-query',
			'label_block' => true,
			'query_type'  => 'post',
			'object_type' => 'page',
			'multiple'    => true,
		];
	}

	public function check( $operator, $value, $name = null ) {
		global $post;
		return $this->compare( $value, $post->ID, $operator );
	}
}
