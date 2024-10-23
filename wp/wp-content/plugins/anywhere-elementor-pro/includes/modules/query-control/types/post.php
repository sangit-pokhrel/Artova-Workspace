<?php

namespace Aepro\Modules\QueryControl\Types;

use Aepro\Modules\QueryControl\TypeBase;

class Post extends TypeBase {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'post';
	}

	public function get_autocomplete_values( array $request ) {

		$object_type = ( ! empty( $request['object_type'] ) ) ? $request['object_type'] : 'any';

		$args = [
			'post_type'      => $object_type,
			's'              => $request['q'],
			'posts_per_page' => -1,
		];

		$query = new \WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$post_title = $post->post_title;

			if ( empty( $darequestta['object_type'] ) || $request === 'any' ) {
				$post_title = '[' . $post->ID . '] ' . $post_title . ' (' . $post->post_type . ')';
			}
			if ( ! empty( $request['object_type'] ) && $object_type === 'elementor_library' ) {
				$etype      = get_post_meta( $post->ID, '_elementor_template_type', true );
				$post_title = '[' . $post->ID . '] ' . $post_title . ' (' . $post->post_type . ' > ' . $etype . ')';
			}

			$results[] = [
				'id'   => $post->ID,
				'text' => $post_title,
			];
		}

		return $results;
	}

	public function get_value_titles( array $request ) {

		$ids     = (array) $request['id'];
		$results = [];

		$args = [
			'post_type'      => 'any',
			'post__in'       => $ids,
			'posts_per_page' => -1,
		];

		$query = new \WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$results[ $post->ID ] = $post->post_title;
		}

		return $results;
	}
}
