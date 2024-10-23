<?php

namespace Aepro\Modules\QueryControl\Types;

use Aepro\Modules\QueryControl\TypeBase;

class Taxonomy extends TypeBase {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'taxonomy';
	}

	public function get_autocomplete_values( array $request ) {
		$object_type = ( ! empty( $request['object_type'] ) ) ? $request['object_type'] : 'any';
		$results     = [];
		$args        = [
			'taxonomy'   => $object_type,
			'search'     => $request['q'],
			'hide_empty' => false,
		];

		$terms = new \WP_Term_Query( $args );
		foreach ( $terms->get_terms() as $term ) {

			$results[] = [
				'id'   => $term->term_id,
				'text' => $term->name,
			];
		}

		return $results;
	}

	public function get_value_titles( array $request ) {
		$ids     = (array) $request['id'];
		$results = [];

		$args = [
			'taxonomy'   => $request['object_type'],
			'include'    => $ids,
			'hide_empty' => false,
		];

		$terms = new \WP_Term_Query( $args );
		foreach ( $terms->get_terms() as $term ) {
			$results[ $term->term_id ] = $term->name;
		}

		return $results;
	}
}
